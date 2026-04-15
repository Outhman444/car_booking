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

describe('Admin Car Status Quick Update', function () {
    test('admin can set car to Maintenance', function () {
        $response = $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::MAINTENANCE->value,
            ]);

        $response->assertRedirect();
        expect($this->car->fresh()->status)->toBe(CarStatus::MAINTENANCE);
    });

    test('admin can set car to Out of Service', function () {
        $response = $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::OUT_OF_SERVICE->value,
            ]);

        $response->assertRedirect();
        expect($this->car->fresh()->status)->toBe(CarStatus::OUT_OF_SERVICE);
    });

    test('admin cannot set car to Pending manually', function () {
        $response = $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::PENDING->value,
            ]);

        $response->assertSessionHas('error');
        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('admin cannot set car to Reserved manually', function () {
        $response = $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::RESERVED->value,
            ]);

        $response->assertSessionHas('error');
        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('admin cannot set car to Rented manually', function () {
        $response = $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::RENTED->value,
            ]);

        $response->assertSessionHas('error');
        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });

    test('admin cannot set car to Available when it has active reservation', function () {
        $reservation = Reservation::factory()->active()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::AVAILABLE->value,
            ]);

        $response->assertSessionHas('error');
        expect($this->car->fresh()->status)->not->toBe(CarStatus::AVAILABLE);
    });

    test('setting car to Maintenance cancels active reservations', function () {
        $reservation = Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::MAINTENANCE->value,
            ]);

        expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
        expect($this->car->fresh()->status)->toBe(CarStatus::MAINTENANCE);
    });

    test('admin can release car from Maintenance to Available', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/cars/{$this->car->id}/quick-status", [
                'status' => CarStatus::AVAILABLE->value,
            ]);

        expect($this->car->fresh()->status)->toBe(CarStatus::AVAILABLE);
    });
});

describe('Admin Car CRUD', function () {
    test('admin can view cars list', function () {
        $response = $this->actingAs($this->admin)->get('/admin/cars');

        $response->assertInertia(fn ($page) => $page->has('cars'));
    });

    test('non-admin cannot view cars list', function () {
        $response = $this->actingAs($this->user)->get('/admin/cars');

        $response->assertForbidden();
    });

    test('admin can create a car', function () {
        $response = $this->actingAs($this->admin)->post('/admin/cars', [
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2024,
            'license_plate' => 'TEST-1234-AB',
            'color' => 'white',
            'price_per_day' => 50,
            'mileage' => 10000,
            'transmission' => 'automatic',
            'seats' => 5,
            'fuel_type' => 'gasoline',
            'status' => 'available',
        ]);

        $response->assertRedirect(route('admin.cars.index'));
        expect(Car::where('license_plate', 'TEST-1234-AB')->exists())->toBeTrue();
    });
});
