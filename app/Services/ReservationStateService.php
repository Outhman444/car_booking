<?php

namespace App\Services;

use App\Enums\CarStatus;
use App\Enums\ReservationStatus;
use App\Models\Car;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationStateService
{
    /**
     * Valid reservation state transitions.
     * Each key maps to an array of allowed next states.
     */
    private const RESERVATION_TRANSITIONS = [
        ReservationStatus::PENDING->value   => [ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW],
        ReservationStatus::CONFIRMED->value => [ReservationStatus::ACTIVE, ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW],
        ReservationStatus::ACTIVE->value    => [ReservationStatus::COMPLETED],
        ReservationStatus::COMPLETED->value => [],
        ReservationStatus::CANCELLED->value => [],
        ReservationStatus::NO_SHOW->value   => [],
    ];

    /**
     * Mapping from reservation status to car status.
     */
    private const RESERVATION_TO_CAR_STATUS = [
        ReservationStatus::PENDING->value   => CarStatus::PENDING,
        ReservationStatus::CONFIRMED->value => CarStatus::RESERVED,
        ReservationStatus::ACTIVE->value    => CarStatus::RENTED,
    ];

    /**
     * Reservation statuses that make a car unavailable for new bookings.
     */
    private const ACTIVE_RESERVATION_STATUSES = [
        ReservationStatus::PENDING,
        ReservationStatus::CONFIRMED,
        ReservationStatus::ACTIVE,
    ];

    /**
     * Check if a reservation transition is valid.
     */
    public function canTransition(Reservation $reservation, ReservationStatus $newStatus): bool
    {
        $currentStatus = $reservation->status->value;
        $allowed = self::RESERVATION_TRANSITIONS[$currentStatus] ?? [];

        return in_array($newStatus, $allowed);
    }

    /**
     * Transition a reservation to a new status and sync car status.
     * Returns true on success, throws on invalid transition.
     */
    public function transition(Reservation $reservation, ReservationStatus $newStatus, ?string $reason = null): bool
    {
        if (!$this->canTransition($reservation, $newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition reservation #{$reservation->reservation_number} " .
                "from {$reservation->status->value} to {$newStatus->value}."
            );
        }

        return DB::transaction(function () use ($reservation, $newStatus, $reason) {
            $updateData = ['status' => $newStatus];

            if ($newStatus === ReservationStatus::CANCELLED) {
                $updateData['cancellation_reason'] = $reason ?? 'Cancelled by system.';
                $updateData['cancelled_at'] = now();
            }

            if ($newStatus === ReservationStatus::NO_SHOW) {
                $updateData['cancellation_reason'] = $reason ?? 'Customer did not show up.';
                $updateData['cancelled_at'] = now();
            }

            // Clear cancellation data if moving away from cancelled (shouldn't happen per transitions)
            if (!in_array($newStatus, [ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW])) {
                $updateData['cancellation_reason'] = null;
                $updateData['cancelled_at'] = null;
            }

            $reservation->update($updateData);

            $this->syncCarStatus($reservation);

            Log::info("Reservation #{$reservation->reservation_number} transitioned to {$newStatus->value}" . ($reason ? ": {$reason}" : ''));

            return true;
        });
    }

    /**
     * Sync the car's status based on the reservation state.
     * This is the SINGLE SOURCE OF TRUTH for car status.
     *
     * Rules:
     * - If car is in Maintenance or Out of Service (admin-controlled), don't override.
     * - Otherwise, derive car status from its most significant active reservation.
     * - If no active reservations, car becomes Available.
     */
    public function syncCarStatus(Reservation $reservation): void
    {
        $car = $reservation->car;
        if (!$car) {
            return;
        }

        // Never override admin-controlled states
        if (in_array($car->status, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
            return;
        }

        $newCarStatus = $this->computeCarStatusFromReservations($car);

        if ($car->status !== $newCarStatus) {
            $car->update(['status' => $newCarStatus]);
            Log::info("Car #{$car->id} ({$car->license_plate}) status synced to {$newCarStatus->value}");
        }
    }

    /**
     * Compute what a car's status SHOULD be based on its active reservations.
     * Priority: Active > Confirmed > Pending > Available
     */
    public function computeCarStatusFromReservations(Car $car): CarStatus
    {
        $mostSignificantReservation = $car->reservations()
            ->whereIn('status', self::ACTIVE_RESERVATION_STATUSES)
            ->orderByRaw("
                CASE status
                    WHEN '" . ReservationStatus::ACTIVE->value . "' THEN 1
                    WHEN '" . ReservationStatus::CONFIRMED->value . "' THEN 2
                    WHEN '" . ReservationStatus::PENDING->value . "' THEN 3
                    ELSE 4
                END ASC
            ")
            ->first();

        if ($mostSignificantReservation) {
            return self::RESERVATION_TO_CAR_STATUS[$mostSignificantReservation->status->value] ?? CarStatus::AVAILABLE;
        }

        return CarStatus::AVAILABLE;
    }

    /**
     * Check if a car is available for a new booking for a given date range.
     * A car is available if:
     * - Its status is Available (or Pending, which means a pending reservation that may expire)
     * - It has no conflicting active reservations for the date range
     * - It is not in Maintenance or Out of Service
     */
    public function isCarAvailableForBooking(Car $car, string $startDate, string $endDate, ?int $excludeReservationId = null): bool
    {
        // Car must not be in admin-controlled unavailable states
        if (in_array($car->status, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
            return false;
        }

        // Check for conflicting reservations
        $query = $car->reservations()
            ->whereIn('status', self::ACTIVE_RESERVATION_STATUSES)
            ->betweenDates($startDate, $endDate);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->count() === 0;
    }

    /**
     * Check and cancel expired pending reservations (lazy evaluation).
     * Called on page loads and API requests — NO cron job needed.
     */
    public function expirePendingReservations(): int
    {
        $expiredCount = 0;

        // Use pending_expires_at if available, otherwise fall back to created_at + hold_minutes
        $expired = Reservation::where('status', ReservationStatus::PENDING)
            ->where(function ($query) {
                $query->where('pending_expires_at', '<=', now())
                    ->orWhere(function ($q) {
                        // Fallback for reservations created before the pending_expires_at column was added
                        $holdMinutes = (int) \App\Models\Setting::getValue('reservation_hold_time_minutes', 60);
                        $q->whereNull('pending_expires_at')
                          ->where('created_at', '<', now()->subMinutes($holdMinutes));
                    });
            })
            ->get();

        foreach ($expired as $reservation) {
            try {
                $this->transition(
                    $reservation,
                    ReservationStatus::CANCELLED,
                    'Payment not completed within hold time. Reservation auto-cancelled.'
                );
                $expiredCount++;
            } catch (\Exception $e) {
                Log::error("Failed to expire reservation #{$reservation->reservation_number}: {$e->getMessage()}");
            }
        }

        return $expiredCount;
    }

    /**
     * Validate that an admin can manually set a car to a specific status.
     * Prevents breaking the reservation-car state synchronization.
     */
    public function canAdminSetCarStatus(Car $car, CarStatus $newStatus): array
    {
        // Always allow Maintenance and Out of Service
        if (in_array($newStatus, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
            // But warn if there are active reservations
            $activeReservations = $car->reservations()
                ->whereIn('status', self::ACTIVE_RESERVATION_STATUSES)
                ->count();

            if ($activeReservations > 0) {
                return [
                    'allowed' => true,
                    'warning' => "This car has {$activeReservations} active reservation(s) that will be cancelled.",
                    'requires_confirmation' => true,
                ];
            }

            return ['allowed' => true];
        }

        // Only allow Available if no active reservations
        if ($newStatus === CarStatus::AVAILABLE) {
            $activeReservations = $car->reservations()
                ->whereIn('status', self::ACTIVE_RESERVATION_STATUSES)
                ->get();

            if ($activeReservations->isNotEmpty()) {
                return [
                    'allowed' => false,
                    'error' => 'Cannot set car to Available while it has active reservations. Cancel or complete the reservations first.',
                ];
            }

            return ['allowed' => true];
        }

        // Prevent manually setting PENDING, RESERVED, RENTED — these are derived from reservations
        if (in_array($newStatus, [CarStatus::PENDING, CarStatus::RESERVED, CarStatus::RENTED])) {
            return [
                'allowed' => false,
                'error' => "Car status '{$newStatus->label()}' is automatically managed by reservations and cannot be set manually.",
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Handle admin setting a car to Maintenance or Out of Service.
     * Cancels all active reservations on that car.
     */
    public function adminSetCarUnavailable(Car $car, CarStatus $newStatus, string $reason): void
    {
        DB::transaction(function () use ($car, $newStatus, $reason) {
            $car->update(['status' => $newStatus]);

            $activeReservations = $car->reservations()
                ->whereIn('status', self::ACTIVE_RESERVATION_STATUSES)
                ->get();

            foreach ($activeReservations as $reservation) {
                $reservation->update([
                    'status' => ReservationStatus::CANCELLED,
                    'cancellation_reason' => $reason,
                    'cancelled_at' => now(),
                ]);
            }
        });
    }

    /**
     * Admin releases a car from Maintenance/Out of Service back to Available.
     */
    public function adminReleaseCar(Car $car): void
    {
        DB::transaction(function () use ($car) {
            if (!in_array($car->status, [CarStatus::MAINTENANCE, CarStatus::OUT_OF_SERVICE])) {
                return;
            }

            $computedStatus = $this->computeCarStatusFromReservations($car);
            $car->update(['status' => $computedStatus]);
        });
    }
}
