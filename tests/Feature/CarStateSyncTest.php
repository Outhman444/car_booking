<?php

use App\Enums\CarStatus;
use App\Enums\ReservationStatus;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ReservationStateService::class);
    $this->user = User::factory()->client()->create();
    $this->car = Car::factory()->available()->create();
});

describe('Full Reservation Lifecycle', function () {
    test('complete happy path: Pending → Confirmed → Active → Completed', function () {
        // 1. Create reservation (Pending)
        $reservation = Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
        ]);
        $this->service->syncCarStatus($reservation);
        expect($this->car->fresh()->status)->toBe(CarStatus::PENDING);

        // 2. Payment confirmed (Pending → Confirmed)
        $this->service->transition($reservation, ReservationStatus::CONFIRMED, 'Payment received');
        expect($reservation->fresh()->status)->toBe(ReservationStatus::CONFIRMED);
        expect($this->car->fresh()->status)->toBe(CarStatus::RESERVED);

        // 3. Car picked up (Confirmed → Active)
        $this->service->transition($reservation, ReservationStatus::ACTIVE, 'Car picked up');
        expect($reservation->fresh()->status)->toBe(ReservationStatus::ACTIVE);
        expect($this->car->fresh()->status)->toBe(CarStatus::RENTED);

        // 4. Car returned (Active → Completed)
        $this->service->transition($reservation, ReservationStatus::COMPLETED, 'Car returned');
        expect($reservation->fresh()->status)->toBe(ReservationStatus::COMPLETED);
        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('cancellation path: Pending → Cancelled', function () {
        $reservation = Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
        ]);
        $this->service->syncCarStatus($reservation);
        expect($this->car->fresh()->status)->toBe(CarStatus::PENDING);

        $this->service->transition($reservation, ReservationStatus::CANCELLED, 'User cancelled');
        expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('no show path: Confirmed → No Show', function () {
        $reservation = Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
        ]);
        $this->service->syncCarStatus($reservation);
        expect($this->car->fresh()->status)->toBe(CarStatus::RESERVED);

        $this->service->transition($reservation, ReservationStatus::NO_SHOW, 'Customer did not show');
        expect($reservation->fresh()->status)->toBe(ReservationStatus::NO_SHOW);
        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });
});

describe('Double Booking Prevention', function () {
    test('cannot create second reservation on car with Pending reservation for same dates', function () {
        $reservation1 = Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $isAvailable = $this->service->isCarAvailableForBooking(
            $this->car,
            now()->addDay()->toDateString(),
            now()->addDays(3)->toDateString()
        );

        expect($isAvailable)->toBeFalse();
    });

    test('cannot create second reservation on car with Confirmed reservation for overlapping dates', function () {
        $user2 = User::factory()->client()->create();
        Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        // Try overlapping dates
        $isAvailable = $this->service->isCarAvailableForBooking(
            $this->car,
            now()->addDays(3)->toDateString(),
            now()->addDays(7)->toDateString()
        );

        expect($isAvailable)->toBeFalse();
    });

    test('can create reservation on car with completed reservation for same dates', function () {
        Reservation::factory()->completed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
        ]);

        $isAvailable = $this->service->isCarAvailableForBooking(
            $this->car,
            now()->addDay()->toDateString(),
            now()->addDays(3)->toDateString()
        );

        expect($isAvailable)->toBeTrue();
    });

    test('can create reservation for non-overlapping dates on same car', function () {
        $user2 = User::factory()->client()->create();
        Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        // Different dates (no overlap)
        $isAvailable = $this->service->isCarAvailableForBooking(
            $this->car,
            now()->addDays(10)->toDateString(),
            now()->addDays(13)->toDateString()
        );

        expect($isAvailable)->toBeTrue();
    });
});

describe('Multi-Reservation Car Status', function () {
    test('car with one Active and one Pending reservation shows Rented', function () {
        $user2 = User::factory()->client()->create();
        $activeReservation = Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);
        $pendingReservation = Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $user2->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        $computedStatus = $this->service->computeCarStatusFromReservations($this->car);
        expect($computedStatus)->toBe(CarStatus::RENTED);
    });

    test('cancelling one of two reservations keeps car at highest priority status', function () {
        $user2 = User::factory()->client()->create();
        $activeReservation = Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);
        $confirmedReservation = Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $user2->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        // Cancel the confirmed one
        $this->service->transition($confirmedReservation, ReservationStatus::CANCELLED, 'Cancelled');

        // Car should still be Rented (Active reservation)
        expect($this->car->fresh()->status)->toBe(CarStatus::RENTED);
    });

    test('completing active reservation with pending reservation keeps car at Pending', function () {
        $user2 = User::factory()->client()->create();
        $activeReservation = Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);
        $pendingReservation = Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $user2->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        // Complete the active one
        $this->service->transition($activeReservation, ReservationStatus::COMPLETED, 'Completed');

        // Car should be Pending (still has pending reservation)
        expect($this->car->fresh()->status)->toBe(CarStatus::PENDING);
    });
});

describe('Reservation Model Methods', function () {
    test('canBeCancelled returns true for Pending', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        expect($reservation->canBeCancelled())->toBeTrue();
    });

    test('canBeCancelled returns true for Confirmed', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        expect($reservation->canBeCancelled())->toBeTrue();
    });

    test('canBeCancelled returns false for Active', function () {
        $reservation = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        expect($reservation->canBeCancelled())->toBeFalse();
    });

    test('canBeCancelled returns false for Completed', function () {
        $reservation = Reservation::factory()->completed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        expect($reservation->canBeCancelled())->toBeFalse();
    });

    test('isTerminal returns true for Completed, Cancelled, No Show', function () {
        $completed = Reservation::factory()->completed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $cancelled = Reservation::factory()->cancelled()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $noShow = Reservation::factory()->noShow()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($completed->isTerminal())->toBeTrue();
        expect($cancelled->isTerminal())->toBeTrue();
        expect($noShow->isTerminal())->toBeTrue();
    });

    test('isTerminal returns false for Pending, Confirmed, Active', function () {
        $pending = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $confirmed = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $active = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($pending->isTerminal())->toBeFalse();
        expect($confirmed->isTerminal())->toBeFalse();
        expect($active->isTerminal())->toBeFalse();
    });

    test('allowedTransitions returns correct list for each status', function () {
        $pending = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        expect($pending->allowedTransitions())->toContain(ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW);

        $confirmed = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        expect($confirmed->allowedTransitions())->toContain(ReservationStatus::ACTIVE, ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW);

        $active = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        expect($active->allowedTransitions())->toContain(ReservationStatus::COMPLETED);
    });
});

describe('Car Model Methods', function () {
    test('isAdminControlled returns true for Maintenance', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);
        expect($this->car->isAdminControlled())->toBeTrue();
    });

    test('isAdminControlled returns true for Out of Service', function () {
        $this->car->update(['status' => CarStatus::OUT_OF_SERVICE]);
        expect($this->car->isAdminControlled())->toBeTrue();
    });

    test('isAdminControlled returns false for Available', function () {
        expect($this->car->isAdminControlled())->toBeFalse();
    });

    test('computedStatus returns Available when no active reservations', function () {
        expect($this->car->computedStatus())->toBe(CarStatus::AVAILABLE);
    });

    test('computedStatus returns Rented when active reservation exists', function () {
        Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        expect($this->car->computedStatus())->toBe(CarStatus::RENTED);
    });
});
