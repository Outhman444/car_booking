<?php

namespace App\Http\Controllers\Client;

use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Setting;
use App\Services\ReservationStateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
    public function index(Request $request)
    {
        $stateService = app(ReservationStateService::class);
        $stateService->expirePendingReservations();

        $reservations = Reservation::where('user_id', auth()->user()->id)
            ->where(function($query) {
                // Strictly exclude PENDING reservations that have expired
                // Only show active PENDING reservations
                $query->where('status', '!=', ReservationStatus::PENDING)
                      ->orWhere(function($q) {
                          $q->where('status', ReservationStatus::PENDING)
                            ->where('pending_expires_at', '>', now());
                      });
            })
            ->with('car')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return inertia('Client/Reservations/Index', [
            'reservations' => $reservations,
        ]);
    }

    public function show($id)
    {
        $stateService = app(ReservationStateService::class);
        $stateService->expirePendingReservations();

        $reservation = Reservation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Redirect if pending and expired
        if ($reservation->status === ReservationStatus::PENDING && $reservation->pending_expires_at <= now()) {
            return redirect()->route('fleet')->with('error', 'Votre session de réservation a expiré. Veuillez recommencer.');
        }

        $reservation->load(['user', 'car', 'payments']);

        $hasPayment = $reservation->payments()
            ->where('status', PaymentStatus::COMPLETED)
            ->exists();

        return inertia('Client/Reservations/Show', [
            'reservation' => $reservation,
            'statusMeta' => ReservationStatus::getMeta(),
            'paymentStatusMeta' => PaymentStatus::getMeta(),
            'currency' => [
                'symbol' => config('app.currency_symbol'),
                'code' => config('app.currency_code'),
            ],
            'hasPayment' => $hasPayment,
            'settings' => [
                'booking_deposit_percentage' => Setting::getValue('booking_deposit_percentage', 20),
                'security_deposit_amount' => Setting::getValue('security_deposit_amount', 0),
                'reservation_hold_time_minutes' => Setting::getValue('reservation_hold_time_minutes', 60),
            ],
        ]);
    }

    public function print($id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $reservation->load(['user', 'car', 'payments']);

        $pdf = Pdf::loadView('admin.reservations.print', [
            'reservation' => $reservation,
            'statusMeta' => ReservationStatus::getMeta(),
            'paymentStatusMeta' => PaymentStatus::getMeta(),
            'currency' => config('app.currency_symbol'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($reservation->reservation_number . '.pdf');
    }
}
