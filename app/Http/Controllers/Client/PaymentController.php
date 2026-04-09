<?php

namespace App\Http\Controllers\Client;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethodSetting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Show payment page for a reservation.
     */
    public function show(Reservation $reservation): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        // Ensure user owns this reservation
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if already paid
        $existingPayment = Payment::where('reservation_id', $reservation->id)
            ->where('status', PaymentStatus::COMPLETED)
            ->first();

        if ($existingPayment) {
            return redirect()->route('client.reservations.show', $reservation->id)
                ->with('info', 'This reservation is already paid.');
        }

        $enabledMethods = PaymentMethodSetting::getEnabledMethods();

        if (empty($enabledMethods)) {
            return redirect()->route('client.reservations.show', $reservation->id)
                ->with('error', 'No payment methods are currently available.');
        }

        return Inertia::render('Client/Payment/Show', [
            'reservation' => $reservation->load('car'),
            'paymentMethods' => $enabledMethods,
            'stripeKey' => config('services.stripe.key'),
            'currency' => [
                'symbol' => config('app.currency_symbol'),
                'code' => config('app.currency_code'),
            ],
        ]);
    }

    /**
     * Process payment with Stripe.
     */
    public function processStripe(Request $request, Reservation $reservation)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        // Check if Stripe is enabled
        if (!PaymentMethodSetting::isEnabled('stripe')) {
            return back()->with('error', 'Stripe payment is not available.');
        }

        // Verify car is still available
        if (!$reservation->car->isAvailable($reservation->start_date, $reservation->end_date, $reservation->id)) {
            $reservation->update(['status' => \App\Enums\ReservationStatus::CANCELLED, 'cancellation_reason' => 'Car was booked by another user during payment process.']);
            return back()->with('error', 'Sorry, the car was just booked by someone else for these dates.');
        }

        // Ensure user owns this reservation
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        $setting = PaymentMethodSetting::getSettings('stripe');

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($reservation->total_amount * 100), // Convert to cents
                'currency' => config('app.currency_code', 'USD'),
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'reservation_id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'user_id' => auth()->id(),
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // Create payment record
                $payment = Payment::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => auth()->id(),
                    'amount' => $reservation->total_amount,
                    'currency' => config('app.currency_code', 'USD'),
                    'payment_method' => PaymentMethod::STRIPE,
                    'status' => PaymentStatus::COMPLETED,
                    'transaction_id' => $paymentIntent->id,
                    'gateway_response' => $paymentIntent->status,
                    'gateway_data' => $paymentIntent->toArray(),
                    'processed_at' => now(),
                ]);

                // Update reservation status and reserve car
                $reservation->update(['status' => \App\Enums\ReservationStatus::CONFIRMED]);
                $reservation->car->update(['status' => \App\Enums\CarStatus::RESERVED]);

                return redirect()->route('client.reservations.show', $reservation->id)
                    ->with('success', 'Payment completed successfully!');
            } else {
                return back()->with('error', 'Payment failed. Please try again.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Payment error: ' . $e->getMessage());
        }
    }

    /**
     * Get PayPal access token using Laravel HTTP Client.
     *
     * @throws \Exception
     */
    private function getPayPalAccessToken(bool $isSandbox): string
    {
        $baseUrl = $isSandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $response = Http::withBasicAuth(
            config('services.paypal.client_id'),
            config('services.paypal.secret')
        )->asForm()->timeout(30)->post($baseUrl . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to authenticate with PayPal.');
        }

        return $response->json('access_token');
    }

    /**
     * Get PayPal base URL based on sandbox mode.
     */
    private function getPayPalBaseUrl(bool $isSandbox): string
    {
        return $isSandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    /**
     * Process payment with PayPal (creates order).
     */
    public function processPayPal(Request $request, Reservation $reservation)
    {
        // Check if PayPal is enabled
        if (!PaymentMethodSetting::isEnabled('paypal')) {
            return response()->json(['error' => 'PayPal payment is not available.'], 400);
        }

        // Verify car is still available
        if (!$reservation->car->isAvailable($reservation->start_date, $reservation->end_date, $reservation->id)) {
            $reservation->update(['status' => \App\Enums\ReservationStatus::CANCELLED, 'cancellation_reason' => 'Car was booked by another user during payment process.']);
            return response()->json(['error' => 'Sorry, the car was just booked by someone else for these dates.'], 400);
        }

        // Ensure user owns this reservation
        if ($reservation->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $setting = PaymentMethodSetting::getSettings('paypal');
        $isSandbox = $setting->is_sandbox;
        $baseUrl = $this->getPayPalBaseUrl($isSandbox);

        try {
            $accessToken = $this->getPayPalAccessToken($isSandbox);

            // Create order
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => config('app.currency_code', 'USD'),
                        'value' => number_format($reservation->total_amount, 2, '.', ''),
                    ],
                    'description' => 'Car Rental - ' . $reservation->reservation_number,
                    'custom_id' => $reservation->id,
                ]],
                'application_context' => [
                    'return_url' => route('client.payment.paypal.success', $reservation->id),
                    'cancel_url' => route('client.payment.paypal.cancel', $reservation->id),
                ],
            ];

            $orderResponse = Http::withToken($accessToken)
                ->timeout(30)
                ->post($baseUrl . '/v2/checkout/orders', $orderData);

            if ($orderResponse->failed()) {
                return response()->json(['error' => 'Failed to create PayPal order.'], 500);
            }

            $orderResult = $orderResponse->json();

            // Find approval URL
            $approvalUrl = null;
            foreach ($orderResult['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            // Store order ID in session for later verification
            session(['paypal_order_id' => $orderResult['id']]);

            return response()->json([
                'approval_url' => $approvalUrl,
                'order_id' => $orderResult['id'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'PayPal error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle PayPal success callback.
     */
    public function paypalSuccess(Request $request, Reservation $reservation)
    {
        $orderId = $request->query('token') ?? session('paypal_order_id');

        if (!$orderId) {
            return redirect()->route('client.reservations.show', $reservation->id)
                ->with('error', 'Invalid PayPal transaction.');
        }

        $setting = PaymentMethodSetting::getSettings('paypal');
        $isSandbox = $setting->is_sandbox;
        $baseUrl = $this->getPayPalBaseUrl($isSandbox);

        try {
            $accessToken = $this->getPayPalAccessToken($isSandbox);

            // Capture the order
            $captureResponse = Http::withToken($accessToken)
                ->timeout(30)
                ->post($baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');

            if ($captureResponse->failed()) {
                return redirect()->route('client.payment.show', $reservation->id)
                    ->with('error', 'Failed to capture PayPal payment.');
            }

            $captureResult = $captureResponse->json();

            if ($captureResult['status'] === 'COMPLETED') {
                // Create payment record
                $payment = Payment::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => auth()->id(),
                    'amount' => $reservation->total_amount,
                    'currency' => config('app.currency_code', 'USD'),
                    'payment_method' => PaymentMethod::PAYPAL,
                    'status' => PaymentStatus::COMPLETED,
                    'transaction_id' => $orderId,
                    'gateway_response' => $captureResult['status'],
                    'gateway_data' => $captureResult,
                    'processed_at' => now(),
                ]);

                session()->forget('paypal_order_id');

                // Update reservation status and reserve car
                $reservation->update(['status' => \App\Enums\ReservationStatus::CONFIRMED]);
                $reservation->car->update(['status' => \App\Enums\CarStatus::RESERVED]);

                return redirect()->route('client.reservations.show', $reservation->id)
                    ->with('success', 'Payment completed successfully!');
            } else {
                return redirect()->route('client.payment.show', $reservation->id)
                    ->with('error', 'Payment was not completed. Please try again.');
            }
        } catch (\Exception $e) {
            return redirect()->route('client.payment.show', $reservation->id)
                ->with('error', 'PayPal error: ' . $e->getMessage());
        }
    }

    /**
     * Handle PayPal cancel callback.
     */
    public function paypalCancel(Request $request, Reservation $reservation)
    {
        session()->forget('paypal_order_id');

        return redirect()->route('client.payment.show', $reservation->id)
            ->with('info', 'Payment was cancelled.');
    }
}
