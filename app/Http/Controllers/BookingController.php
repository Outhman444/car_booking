<?php

namespace App\Http\Controllers;

use App\Enums\CarStatus;
use App\Enums\ReservationStatus;
use App\Models\Car;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function show(Car $car)
    {
        // Check if car is available for booking
        if ($car->status !== CarStatus::AVAILABLE) {
            return redirect()->route('fleet')->with('error', 'This car is not available for booking.');
        }

        return inertia('Booking', compact('car'));
    }

    public function book(Car $car, Request $request)
    {
        // check car is available for booking
        if ($car->status !== CarStatus::AVAILABLE) {
            return redirect()->route('fleet')->with('error', 'This car is not available for booking.');
        }

        // check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to book a car.');
        }

        // Fix 3.3: Check only active/pending bookings, not completed/cancelled ones
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

        // form validation
        $request->validate([
            'start_date'       => 'required|date|after_or_equal:today',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'pickup_location'  => 'required|string|max:255',
            'return_location'  => 'required|string|max:255',
        ]);

        // check availability for dates
        if (!$car->isAvailable($request->start_date, $request->end_date)) {
            return back()->with('error', 'This car is already reserved for the selected dates.');
        }

        // convert dates to Carbon
        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);

        // Fix 3.6: Unified day calculation — diffInDays + 1 (inclusive of both start and end day)
        $days = max(1, $startDate->diffInDays($endDate) + 1);

        // ensure daily rate is positive
        $dailyRate = abs($car->price_per_day);

        // Fix 3.4: Use centralized tax rate from config
        $taxRate    = config('app.tax_rate', 0.07);
        $subtotal   = $dailyRate * $days;
        $taxAmount  = round($subtotal * $taxRate, 2);
        $discount   = 0;
        $total      = $subtotal + $taxAmount - $discount;

        // Fix 6.7: Use DB transaction for atomicity
        $reservation = \Illuminate\Support\Facades\DB::transaction(function () use ($car, $startDate, $endDate, $request, $days, $dailyRate, $subtotal, $taxAmount, $discount, $total) {
            // create reservation
            $reservation = Reservation::create([
                'car_id'          => $car->id,
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
            ]);

            return $reservation;
        });

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
        ]);
    }
}
