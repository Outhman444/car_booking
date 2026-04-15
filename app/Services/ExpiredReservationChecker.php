<?php

namespace App\Services;

class ExpiredReservationChecker
{
    /**
     * Check and cancel expired pending reservations.
     * Delegates to ReservationStateService for proper state synchronization.
     * Call this on key requests (checkout, dashboard, booking page).
     */
    public function checkAndCancelExpired(): int
    {
        return app(ReservationStateService::class)->expirePendingReservations();
    }
}
