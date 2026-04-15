<?php

use App\Enums\CarStatus;
use App\Enums\ReservationStatus;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationStateService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = app(ReservationStateService::class);
    $this->user = User::factory()->client()->create();
    $this->car = Car::factory()->available()->create();
});

describe('State Transitions', function () {
    test('Pending can transition to Confirmed', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::CONFIRMED))->toBeTrue();
    });

    test('Pending can transition to Cancelled', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::CANCELLED))->toBeTrue();
    });

    test('Pending can transition to No Show', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::NO_SHOW))->toBeTrue();
    });

    test('Pending cannot transition to Active', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::ACTIVE))->toBeFalse();
    });

    test('Pending cannot transition to Completed', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::COMPLETED))->toBeFalse();
    });

    test('Confirmed can transition to Active', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::ACTIVE))->toBeTrue();
    });

    test('Confirmed can transition to Cancelled', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::CANCELLED))->toBeTrue();
    });

    test('Confirmed cannot transition to Pending', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::PENDING))->toBeFalse();
    });

    test('Active can transition to Completed', function () {
        $reservation = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::COMPLETED))->toBeTrue();
    });

    test('Active cannot transition to Pending', function () {
        $reservation = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::PENDING))->toBeFalse();
    });

    test('Active cannot transition to Cancelled', function () {
        $reservation = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->canTransition($reservation, ReservationStatus::CANCELLED))->toBeFalse();
    });

    test('Completed cannot transition to anything', function () {
        $reservation = Reservation::factory()->completed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        foreach (ReservationStatus::cases() as $status) {
            expect($this->service->canTransition($reservation, $status))->toBeFalse();
        }
    });

    test('Cancelled cannot transition to anything', function () {
        $reservation = Reservation::factory()->cancelled()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        foreach (ReservationStatus::cases() as $status) {
            expect($this->service->canTransition($reservation, $status))->toBeFalse();
        }
    });
});

describe('Transition Execution', function () {
    test('transition updates reservation status', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->transition($reservation, ReservationStatus::CONFIRMED, 'Payment received');

        expect($reservation->fresh()->status)->toBe(ReservationStatus::CONFIRMED);
    });

    test('transition to Cancelled sets cancellation metadata', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->transition($reservation, ReservationStatus::CANCELLED, 'Test reason');

        $fresh = $reservation->fresh();
        expect($fresh->status)->toBe(ReservationStatus::CANCELLED);
        expect($fresh->cancellation_reason)->toBe('Test reason');
        expect($fresh->cancelled_at)->not->toBeNull();
    });

    test('invalid transition throws exception', function () {
        $reservation = Reservation::factory()->completed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->transition($reservation, ReservationStatus::PENDING);
    })->throws(\InvalidArgumentException::class);
});

describe('Car Status Sync', function () {
    test('Pending reservation sets car to Pending', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->syncCarStatus($reservation);

        expect($this->car->fresh()->status)->toBe(CarStatus::PENDING);
    });

    test('Confirmed reservation sets car to Reserved', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->syncCarStatus($reservation);

        expect($this->car->fresh()->status)->toBe(CarStatus::RESERVED);
    });

    test('Active reservation sets car to Rented', function () {
        $reservation = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->syncCarStatus($reservation);

        expect($this->car->fresh()->status)->toBe(CarStatus::RENTED);
    });

    test('Completed reservation sets car to Available', function () {
        $reservation = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $this->car->update(['status' => CarStatus::RENTED]);

        $this->service->transition($reservation, ReservationStatus::COMPLETED, 'Rental completed');

        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('Cancelled reservation sets car to Available when no other active reservations', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->transition($reservation, ReservationStatus::CANCELLED, 'Cancelled by user');

        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('Cancelled reservation does NOT set car to Available when another reservation is Active', function () {
        $user2 = User::factory()->client()->create();
        $reservation1 = Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $user2->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);
        $reservation2 = Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        $this->service->transition($reservation2, ReservationStatus::CANCELLED, 'Cancelled');

        // Car should still be Rented because reservation1 is Active
        expect($this->car->fresh()->status)->toBe(CarStatus::RENTED);
    });

    test('syncCarStatus does not override Maintenance status', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->syncCarStatus($reservation);

        expect($this->car->fresh()->status)->toBe(CarStatus::MAINTENANCE);
    });

    test('syncCarStatus does not override Out of Service status', function () {
        $this->car->update(['status' => CarStatus::OUT_OF_SERVICE]);
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->service->syncCarStatus($reservation);

        expect($this->car->fresh()->status)->toBe(CarStatus::OUT_OF_SERVICE);
    });
});

describe('Car Availability', function () {
    test('available car with no reservations is available for booking', function () {
        expect($this->service->isCarAvailableForBooking($this->car, now()->toDateString(), now()->addDays(3)->toDateString()))->toBeTrue();
    });

    test('car in maintenance is not available', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);

        expect($this->service->isCarAvailableForBooking($this->car, now()->toDateString(), now()->addDays(3)->toDateString()))->toBeFalse();
    });

    test('car with pending reservation on same dates is not available', function () {
        Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        expect($this->service->isCarAvailableForBooking($this->car, now()->addDay()->toDateString(), now()->addDays(3)->toDateString()))->toBeFalse();
    });

    test('car with confirmed reservation on overlapping dates is not available', function () {
        Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        expect($this->service->isCarAvailableForBooking($this->car, now()->addDays(3)->toDateString(), now()->addDays(7)->toDateString()))->toBeFalse();
    });

    test('car with completed reservation on same dates is available', function () {
        Reservation::factory()->completed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
        ]);

        expect($this->service->isCarAvailableForBooking($this->car, now()->toDateString(), now()->addDays(3)->toDateString()))->toBeTrue();
    });

    test('car is available when excluding own reservation', function () {
        $reservation = Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        expect($this->service->isCarAvailableForBooking($this->car, now()->addDay()->toDateString(), now()->addDays(3)->toDateString(), $reservation->id))->toBeTrue();
    });
});

describe('Pending Expiration', function () {
    test('expired pending reservations are cancelled', function () {
        $reservation = Reservation::factory()->expired()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $this->car->update(['status' => CarStatus::PENDING]);

        $count = $this->service->expirePendingReservations();

        expect($count)->toBe(1);
        expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
        expect($reservation->fresh()->cancellation_reason)->toContain('auto-cancelled');
    });

    test('expired pending reservation releases car to Available', function () {
        $reservation = Reservation::factory()->expired()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $this->car->update(['status' => CarStatus::PENDING]);

        $this->service->expirePendingReservations();

        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('non-expired pending reservations are NOT cancelled', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $count = $this->service->expirePendingReservations();

        expect($count)->toBe(0);
        expect($reservation->fresh()->status)->toBe(ReservationStatus::PENDING);
    });

    test('expired pending reservation does NOT release car if another active reservation exists', function () {
        $user2 = User::factory()->client()->create();
        $activeReservation = Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $user2->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);
        $expiredReservation = Reservation::factory()->expired()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);
        $this->car->update(['status' => CarStatus::RENTED]);

        $this->service->expirePendingReservations();

        // Car should still be Rented because of the active reservation
        expect($this->car->fresh()->status)->toBe(CarStatus::RENTED);
        expect($expiredReservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
    });
});

describe('Admin Car Status Management', function () {
    test('admin cannot set car to Pending manually', function () {
        $result = $this->service->canAdminSetCarStatus($this->car, CarStatus::PENDING);

        expect($result['allowed'])->toBeFalse();
    });

    test('admin cannot set car to Reserved manually', function () {
        $result = $this->service->canAdminSetCarStatus($this->car, CarStatus::RESERVED);

        expect($result['allowed'])->toBeFalse();
    });

    test('admin cannot set car to Rented manually', function () {
        $result = $this->service->canAdminSetCarStatus($this->car, CarStatus::RENTED);

        expect($result['allowed'])->toBeFalse();
    });

    test('admin can set car to Maintenance', function () {
        $result = $this->service->canAdminSetCarStatus($this->car, CarStatus::MAINTENANCE);

        expect($result['allowed'])->toBeTrue();
    });

    test('admin can set car to Out of Service', function () {
        $result = $this->service->canAdminSetCarStatus($this->car, CarStatus::OUT_OF_SERVICE);

        expect($result['allowed'])->toBeTrue();
    });

    test('admin can set car to Available when no active reservations', function () {
        $result = $this->service->canAdminSetCarStatus($this->car, CarStatus::AVAILABLE);

        expect($result['allowed'])->toBeTrue();
    });

    test('admin cannot set car to Available when active reservation exists', function () {
        Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $result = $this->service->canAdminSetCarStatus($this->car, CarStatus::AVAILABLE);

        expect($result['allowed'])->toBeFalse();
    });

    test('admin setting car to Maintenance cancels all active reservations', function () {
        $reservation1 = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        $user2 = User::factory()->client()->create();
        $reservation2 = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $user2->id]);

        $this->service->adminSetCarUnavailable($this->car, CarStatus::MAINTENANCE, 'Scheduled maintenance');

        expect($this->car->fresh()->status)->toBe(CarStatus::MAINTENANCE);
        expect($reservation1->fresh()->status)->toBe(ReservationStatus::CANCELLED);
        expect($reservation2->fresh()->status)->toBe(ReservationStatus::CANCELLED);
    });

    test('admin releasing car from Maintenance computes correct status', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);

        $this->service->adminReleaseCar($this->car);

        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('admin releasing car from Maintenance with active reservation sets Rented', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);
        // Create an active reservation (normally would be cancelled when car went to maintenance,
        // but testing the compute logic)
        Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->service->adminReleaseCar($this->car);

        expect($this->car->fresh()->status)->toBe(CarStatus::RENTED);
    });
});

describe('Compute Car Status from Reservations', function () {
    test('no reservations = Available', function () {
        expect($this->service->computeCarStatusFromReservations($this->car))->toBe(CarStatus::AVAILABLE);
    });

    test('only completed/cancelled reservations = Available', function () {
        Reservation::factory()->completed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);
        Reservation::factory()->cancelled()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        expect($this->service->computeCarStatusFromReservations($this->car))->toBe(CarStatus::AVAILABLE);
    });

    test('Active reservation takes priority over Confirmed', function () {
        $user2 = User::factory()->client()->create();
        Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
        ]);
        Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $user2->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        expect($this->service->computeCarStatusFromReservations($this->car))->toBe(CarStatus::RENTED);
    });

    test('Confirmed reservation takes priority over Pending', function () {
        $user2 = User::factory()->client()->create();
        Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
        ]);
        Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $user2->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        expect($this->service->computeCarStatusFromReservations($this->car))->toBe(CarStatus::RESERVED);
    });
});
