<?php

namespace App\Http\Controllers;

use App\Enums\CarStatus;
use App\Enums\ReservationStatus;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\Location;
use App\Services\ReservationStateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function show(Car $car)
    {
        // Check if car is available for booking using the service
        $stateService = app(ReservationStateService::class);

        if ($car->isAdminControlled() || !$stateService->isCarAvailableForBooking($car, now()->toDateString(), now()->addYear()->toDateString())) {
            return redirect()->route('fleet')->with('error', 'This car is not available for booking.');
        }

        return inertia('Booking', [
            'car' => $car,
            'locations' => Location::where('is_active', true)->get(),
            'taxRate' => ((float) Setting::getValue('tax_rate', config('app.tax_rate', 7))) / 100,
            'settings' => [
                'booking_deposit_percentage' => (int) Setting::getValue('booking_deposit_percentage', 20),
                'security_deposit_amount' => (float) Setting::getValue('security_deposit_amount', 0),
            ],
        ]);
    }

    public function book(Car $car, Request $request)
    {
        // Check car is available for booking using the service
        $stateService = app(ReservationStateService::class);

        if ($car->isAdminControlled()) {
            return redirect()->route('fleet')->with('error', 'This car is not available for booking.');
        }

        // check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to book a car.');
        }

        // Check only active/pending bookings, not completed/cancelled ones
        $hasActiveBooking = Reservation::where('car_id', $car->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
                ReservationStatus::ACTIVE,
            ])
            ->exists();

        if ($hasActiveBooking) {
            return redirect()->route('fleet')
                ->with('error', 'You already have an active booking for this car.');
        }

        // Check global availability (any user's active booking blocks this car)
        if (!$stateService->isCarAvailableForBooking($car, $request->start_date ?? now()->toDateString(), $request->end_date ?? now()->addDay()->toDateString())) {
            return redirect()->route('fleet')
                ->with('error', 'This car is not available for booking.');
        }

        // form validation
        $request->validate([
            'start_date'       => 'required|date|after_or_equal:today',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'pickup_location'  => 'required|string|max:255',
            'return_location'  => 'required|string|max:255',
        ]);

        // check availability for dates (preliminary check before transaction)
        if (!$car->isAvailable($request->start_date, $request->end_date)) {
            return back()->with('error', 'This car is already reserved for the selected dates.');
        }

        // convert dates to Carbon
        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);

        // Unified day calculation — diffInDays + 1 (inclusive of both start and end day)
        $days = max(1, $startDate->diffInDays($endDate) + 1);

        // Check min/max rental days
        $minDays = (int) Setting::getValue('min_rental_days', 1);
        $maxDays = (int) Setting::getValue('max_rental_days', 30);

        if ($days < $minDays) {
            return back()->with('error', "The minimum rental duration is {$minDays} day(s).");
        }

        if ($days > $maxDays) {
            return back()->with('error', "The maximum rental duration is {$maxDays} day(s).");
        }

        // ensure daily rate is positive
        $dailyRate = abs($car->price_per_day);

        // Fetch tax rate from settings
        $taxRate    = ((float) Setting::getValue('tax_rate', config('app.tax_rate', 7))) / 100;
        $subtotal   = $dailyRate * $days;
        $taxAmount  = round($subtotal * $taxRate, 2);
        $discount   = 0;
        $total      = $subtotal + $taxAmount - $discount;

        // Atomic transaction with pessimistic locking to prevent race conditions.
        // lockForUpdate() ensures that if two users try to book the same car at the exact
        // same millisecond, only one will succeed — the other waits and then fails gracefully.
        $reservation = \Illuminate\Support\Facades\DB::transaction(function () use ($car, $startDate, $endDate, $request, $days, $dailyRate, $subtotal, $taxAmount, $discount, $total, $stateService) {
            // Lock the car row so no other transaction can read/write it simultaneously
            $lockedCar = Car::lockForUpdate()->find($car->id);

            // Re-verify availability INSIDE the lock (the authoritative check)
            if ($lockedCar->isAdminControlled() || !$stateService->isCarAvailableForBooking($lockedCar, $request->start_date, $request->end_date)) {
                return null; // Will be caught below
            }

            // create reservation
            $reservation = Reservation::create([
                'car_id'          => $lockedCar->id,
                'user_id'         => Auth::id(),
                'start_date'      => $startDate,
                'end_date'        => $endDate,
                'pickup_location' => $request->pickup_location,
                'return_location' => $request->return_location,
                'total_days'      => $days,
                'daily_rate'      => $dailyRate,
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discount,
                'total_amount'    => $total,
                'status'          => ReservationStatus::PENDING,
            ]);

            $reservation->refresh();

            // Sync car status to Pending (Payment) — this is the correct state sync.
            // If the user abandons checkout, the expiration logic will auto-cancel
            // the reservation and release the car.
            $stateService->syncCarStatus($reservation);

            return $reservation;
        });

        // Handle race condition: another user booked the same car at the same time
        if (!$reservation) {
            return back()->with('error', 'Sorry, this car was just booked by another customer for the selected dates. Please choose different dates or another car.');
        }

        return redirect()->route('booking.confirmation', $reservation);
    }


    public function confirmation(Reservation $reservation)
    {
        // Make sure user can only see their own reservations
        if ($reservation->user_id !== Auth::user()->id) {
            return redirect()->route('fleet');
        }

        return inertia('BookingConfirmation', [
            'reservation' => $reservation->load(['car', 'user']),
            'settings' => [
                'booking_deposit_percentage' => Setting::getValue('booking_deposit_percentage', 20),
                'security_deposit_amount' => Setting::getValue('security_deposit_amount', 0),
            ],
        ]);
    }
}
