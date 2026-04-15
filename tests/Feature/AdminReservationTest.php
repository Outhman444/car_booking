<?php

use App\Enums\CarStatus;
use App\Enums\ReservationStatus;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->user = User::factory()->client()->create();
    $this->car = Car::factory()->available()->create();
});

describe('Admin Reservation Index', function () {
    test('admin can view reservations list', function () {
        $response = $this->actingAs($this->admin)->get('/admin/reservations');

        $response->assertInertia(fn ($page) => $page->has('reservations'));
    });

    test('non-admin cannot view reservations list', function () {
        $response = $this->actingAs($this->user)->get('/admin/reservations');

        $response->assertForbidden();
    });
});

describe('Admin Reservation Status Update', function () {
    test('admin can transition Pending to Confirmed', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/reservations/{$reservation->id}/quick-status", [
                'status' => ReservationStatus::CONFIRMED->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        expect($reservation->fresh()->status)->toBe(ReservationStatus::CONFIRMED);
        expect($this->car->fresh()->status)->toBe(CarStatus::RESERVED);
    });

    test('admin can transition Confirmed to Active', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/reservations/{$reservation->id}/quick-status", [
                'status' => ReservationStatus::ACTIVE->value,
            ]);

        expect($reservation->fresh()->status)->toBe(ReservationStatus::ACTIVE);
        expect($this->car->fresh()->status)->toBe(CarStatus::RENTED);
    });

    test('admin can transition Active to Completed', function () {
        $reservation = Reservation::factory()->active()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/reservations/{$reservation->id}/quick-status", [
                'status' => ReservationStatus::COMPLETED->value,
            ]);

        expect($reservation->fresh()->status)->toBe(ReservationStatus::COMPLETED);
        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('admin cannot transition Completed to Pending', function () {
        $reservation = Reservation::factory()->completed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/reservations/{$reservation->id}/quick-status", [
                'status' => ReservationStatus::PENDING->value,
            ]);

        $response->assertSessionHas('error');
        expect($reservation->fresh()->status)->toBe(ReservationStatus::COMPLETED);
    });

    test('admin cannot transition Cancelled to Active', function () {
        $reservation = Reservation::factory()->cancelled()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/reservations/{$reservation->id}/quick-status", [
                'status' => ReservationStatus::ACTIVE->value,
            ]);

        $response->assertSessionHas('error');
        expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
    });

    test('admin cancelling reservation sets car to Available', function () {
        $reservation = Reservation::factory()->confirmed()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $this->actingAs($this->admin)
            ->post("/admin/reservations/{$reservation->id}/quick-status", [
                'status' => ReservationStatus::CANCELLED->value,
            ]);

        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('admin can view reservation details', function () {
        $reservation = Reservation::factory()->pending()->create(['car_id' => $this->car->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)->get("/admin/reservations/{$reservation->id}");

        $response->assertInertia(fn ($page) => $page->has('reservation'));
    });
});
