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
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Enums\ReservationStatus;
use App\Enums\CarStatus;

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

        if ($existingPayment || $reservation->status === \App\Enums\ReservationStatus::CONFIRMED) {
            $msg = $existingPayment ? 'This reservation has already been paid.' : 'This reservation is already confirmed for payment at the agency.';
            return redirect()->route('client.reservations.show', $reservation->id)
                ->with('info', $msg . ' You can safely print your invoice below.');
        }

        $enabledMethods = PaymentMethodSetting::getEnabledMethods();

        if (empty($enabledMethods)) {
            return redirect()->route('client.reservations.show', $reservation->id)
                ->with('error', 'We apologize, but no payment methods are currently available. Please contact our support team for assistance.');
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
     * Create Stripe Payment Intent for frontend confirmation.
     */
    public function createStripeIntent(Request $request, Reservation $reservation)
    {
        // Check if Stripe is enabled
        if (!PaymentMethodSetting::isEnabled('stripe')) {
            return response()->json(['error' => 'Stripe payment is not available.'], 400);
        }

        // Verify car is still available
        if (!$reservation->car->isAvailable($reservation->start_date, $reservation->end_date, $reservation->id)) {
            $reservation->update(['status' => \App\Enums\ReservationStatus::CANCELLED, 'cancellation_reason' => 'Car was booked by another user during payment process.']);
            return response()->json(['error' => 'Sorry, the car was just booked by someone else for these dates.'], 400);
        }

        if ($reservation->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($reservation->total_amount * 100),
                'currency' => config('app.currency_code', 'USD'),
                'payment_method_types' => ['card'],
                'metadata' => [
                    'reservation_id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'user_id' => auth()->id(),
                ],
            ]);

            return response()->json(['client_secret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            \Log::error('Stripe Intent Error: ' . $e->getMessage());
            return response()->json(['error' => 'Stripe error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Confirm Stripe Payment synchronously.
     */
    public function confirmStripe(Request $request, Reservation $reservation)
    {
        $request->validate(['payment_intent_id' => 'required|string']);

        if ($reservation->user_id !== auth()->id()) abort(403);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                if (!Payment::where('transaction_id', $paymentIntent->id)->exists()) {
                    Payment::create([
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

                    $reservation->update(['status' => \App\Enums\ReservationStatus::CONFIRMED]);
                    // Only update car if it's not in an admin-controlled state
                    if (!in_array($reservation->car->status, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
                        $reservation->car->update(['status' => CarStatus::RESERVED]);
                    }
                }

                return redirect()->route('client.reservations.show', $reservation->id)
                    ->with('success', 'Payment completed successfully! Please print your reservation invoice and present it at the agency to pick up your car.');
            }

            return back()->with('error', 'Your payment was not completed successfully. Please try a different card or payment method.');
        } catch (\Exception $e) {
            \Log::error('Stripe Confirm Error: ' . $e->getMessage());
            return back()->with('error', 'An unexpected payment error occurred. Please contact support if the issue persists.');
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
                    'custom_id' => (string) $reservation->id,
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'return_url' => route('client.payment.paypal.success', $reservation->id),
                            'cancel_url' => route('client.payment.paypal.cancel', $reservation->id),
                        ]
                    ]
                ],
            ];

            $orderResponse = Http::withToken($accessToken)
                ->timeout(30)
                ->post($baseUrl . '/v2/checkout/orders', $orderData);

            if ($orderResponse->failed()) {
                \Log::error('PayPal Order Failed', [
                    'status' => $orderResponse->status(),
                    'response' => $orderResponse->json()
                ]);
                $errorMsg = $orderResponse->json()['message'] ?? 'Unknown error occurred.';
                return response()->json(['error' => 'Failed to create PayPal order: ' . $errorMsg], 500);
            }

            $orderResult = $orderResponse->json();

            // Find approval URL
            $approvalUrl = null;
            if (isset($orderResult['links'])) {
                foreach ($orderResult['links'] as $link) {
                    if ($link['rel'] === 'approve' || $link['rel'] === 'payer-action') {
                        $approvalUrl = $link['href'];
                        break;
                    }
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
                ->withBody('{}', 'application/json')
                ->timeout(30)
                ->post($baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');

            if ($captureResponse->failed()) {
                \Log::error('PayPal Capture Failed', [
                    'status' => $captureResponse->status(),
                    'response' => $captureResponse->json()
                ]);
                $errorMsg = $captureResponse->json()['message'] ?? 'Unknown Error';
                return redirect()->route('client.payment.show', $reservation->id)
                    ->with('error', 'Failed to capture PayPal payment: ' . $errorMsg);
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
                // Only update car if it's not in an admin-controlled state
                if (!in_array($reservation->car->status, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
                    $reservation->car->update(['status' => CarStatus::RESERVED]);
                }

                return redirect()->route('client.reservations.show', $reservation->id)
                    ->with('success', 'Payment completed successfully! Please print your reservation invoice and present it at the agency to pick up your car.');
            } else {
                return redirect()->route('client.payment.show', $reservation->id)
                    ->with('error', 'Your payment could not be finalized. Please try again or use a different payment method.');
            }
        } catch (\Exception $e) {
            return redirect()->route('client.payment.show', $reservation->id)
                ->with('error', 'An unexpected PayPal error occurred. Please try again or contact our support team.');
        }
    }

    /**
     * Handle PayPal cancel callback.
     */
    public function paypalCancel(Request $request, Reservation $reservation)
    {
        session()->forget('paypal_order_id');

        return redirect()->route('client.payment.show', $reservation->id)
            ->with('info', 'Your payment process was cancelled. You can securely try again whenever you are ready.');
    }

    /**
     * Handle Stripe Webhooks
     */
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        $event = null;

        try {
            if ($endpointSecret) {
                $event = Webhook::constructEvent(
                    $payload, $sigHeader, $endpointSecret
                );
            } else {
                $event = json_decode($payload);
            }
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if (isset($event->type) && $event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $this->processWebhookPayment($paymentIntent->id, $paymentIntent, PaymentMethod::STRIPE);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle PayPal Webhooks
     */
    public function handlePayPalWebhook(Request $request)
    {
        $payload = $request->getContent();
        $event = json_decode($payload);

        if (isset($event->event_type) && $event->event_type === 'CHECKOUT.ORDER.APPROVED') {
            $order = $event->resource;
        } elseif (isset($event->event_type) && $event->event_type === 'PAYMENT.CAPTURE.COMPLETED') {
            $capture = $event->resource;
            $transactionId = $capture->supplementary_data->related_ids->order_id ?? $capture->id;
            $this->processWebhookPayment($transactionId, $capture, PaymentMethod::PAYPAL);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Process payment selection for "Pay at Agency".
     */
    public function processAgency(Request $request, Reservation $reservation)
    {
        // Check if Agency payment is enabled
        if (!PaymentMethodSetting::isEnabled('agency')) {
            return response()->json(['error' => 'Pay at agency is not available.'], 400);
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

        $reservation->update([
            'status' => ReservationStatus::PENDING,
            'notes' => 'Chosen Payment Method: Pay at Agency (Cash). Payment is still pending.'
        ]);
        // Only update car if it's not in an admin-controlled state
        if (!in_array($reservation->car->status, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
            $reservation->car->update(['status' => CarStatus::PENDING]);
        }

        $timeout = \App\Models\Setting::getValue('cash_reservation_timeout', 24);
        
        session()->flash('success', "Your reservation request is submitted! You must attend the agency to pay the total amount in cash within {$timeout} hours, otherwise your reservation will be automatically cancelled.");

        return response()->json([
            'success' => true,
            'redirect_url' => route('client.reservations.show', $reservation->id),
        ]);
    }

    /**
     * Process completing a payment idempotently (Webhooks)
     */
    private function processWebhookPayment($transactionId, $gatewayData, $method)
    {
        if (Payment::where('transaction_id', $transactionId)->exists()) {
            return;
        }

        $reservationId = null;

        if ($method === PaymentMethod::STRIPE && isset($gatewayData->metadata->reservation_id)) {
            $reservationId = $gatewayData->metadata->reservation_id;
        }

        if ($method === PaymentMethod::PAYPAL && isset($gatewayData->custom_id)) {
             $reservationId = $gatewayData->custom_id;
        }

        if (!$reservationId) {
            \Log::warning('Webhook matched payment but missing reservation ID for Tx: ' . $transactionId);
            return;
        }

        $reservation = Reservation::find($reservationId);
        if (!$reservation) return;

        Payment::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'amount' => $reservation->total_amount,
            'currency' => config('app.currency_code', 'USD'),
            'payment_method' => $method,
            'status' => PaymentStatus::COMPLETED,
            'transaction_id' => $transactionId,
            'gateway_response' => 'succeeded',
            'gateway_data' => is_array($gatewayData) ? $gatewayData : (array) $gatewayData,
            'processed_at' => now(),
        ]);

        $reservation->update(['status' => ReservationStatus::CONFIRMED]);
        // Only update car if it's not in an admin-controlled state
        if (!in_array($reservation->car->status, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
            $reservation->car->update(['status' => CarStatus::RESERVED]);
        }
    }
}
