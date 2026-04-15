<?php

use App\Enums\CarStatus;
use App\Enums\ReservationStatus;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->client()->create();
    $this->car = Car::factory()->available()->create();
});

describe('Booking Page Access', function () {
    test('guest cannot book a car', function () {
        $response = $this->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('authenticated user can view booking page for available car', function () {
        $response = $this->actingAs($this->user)->get("/fleet/{$this->car->id}");

        $response->assertInertia(fn ($page) => $page->has('car'));
    });

    test('user cannot view booking page for car in maintenance', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);

        $response = $this->actingAs($this->user)->get("/fleet/{$this->car->id}");

        $response->assertRedirect(route('fleet'));
        $response->assertSessionHas('error');
    });
});

describe('Booking Creation', function () {
    test('authenticated user can book an available car', function () {
        $response = $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertRedirect();

        $reservation = Reservation::first();
        expect($reservation)->not->toBeNull();
        expect($reservation->status)->toBe(ReservationStatus::PENDING);
        expect($reservation->car_id)->toBe($this->car->id);
        expect($reservation->user_id)->toBe($this->user->id);
    });

    test('booking sets car status to Pending', function () {
        $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        expect($this->car->fresh()->status)->toBe(CarStatus::PENDING);
    });

    test('booking sets pending_expires_at', function () {
        $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $reservation = Reservation::first();
        expect($reservation->pending_expires_at)->not->toBeNull();
        expect($reservation->pending_expires_at)->toBeGreaterThan(now());
    });

    test('user cannot book car that already has active reservation', function () {
        $otherUser = User::factory()->client()->create();
        Reservation::factory()->confirmed()->create([
            'car_id' => $this->car->id,
            'user_id' => $otherUser->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);
        $this->car->update(['status' => CarStatus::RESERVED]);

        $response = $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        expect(Reservation::count())->toBe(1);
    });

    test('user cannot book same car twice for same dates', function () {
        Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    test('booking requires valid dates', function () {
        $response = $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => 'invalid-date',
            'end_date' => 'invalid-date',
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertSessionHasErrors(['start_date', 'end_date']);
    });

    test('end date must be after start date', function () {
        $response = $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertSessionHasErrors(['end_date']);
    });

    test('start date must be today or later', function () {
        $response = $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertSessionHasErrors(['start_date']);
    });

    test('cannot book car in maintenance', function () {
        $this->car->update(['status' => CarStatus::MAINTENANCE]);

        $response = $this->actingAs($this->user)->post("/fleet/{$this->car->id}", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'pickup_location' => 'Airport',
            'return_location' => 'Airport',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });
});

describe('Booking Confirmation', function () {
    test('user can view their own booking confirmation', function () {
        $reservation = Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/booking/{$reservation->id}");

        $response->assertInertia(fn ($page) => $page->has('reservation'));
    });

    test('user cannot view other users booking confirmation', function () {
        $otherUser = User::factory()->client()->create();
        $reservation = Reservation::factory()->pending()->create([
            'car_id' => $this->car->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->get("/booking/{$reservation->id}");

        $response->assertRedirect(route('fleet'));
    });
});
