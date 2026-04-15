<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $startDate = now()->addDays(fake()->numberBetween(1, 10));
        $endDate = (clone $startDate)->addDays(fake()->numberBetween(1, 5));
        $dailyRate = fake()->randomFloat(2, 30, 200);
        $days = $startDate->diffInDays($endDate) + 1;
        $subtotal = $dailyRate * $days;
        $taxRate = 0.07;
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = $subtotal + $taxAmount;

        return [
            'reservation_number' => 'RES-' . strtoupper(fake()->bothify('????????')),
            'user_id' => User::factory(),
            'car_id' => Car::factory(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'pickup_time' => '09:00',
            'return_time' => '18:00',
            'pickup_location' => fake()->city(),
            'return_location' => fake()->city(),
            'total_days' => $days,
            'daily_rate' => $dailyRate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'status' => ReservationStatus::PENDING->value,
            'pending_expires_at' => now()->addMinutes(60),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::PENDING->value,
            'pending_expires_at' => now()->addMinutes(60),
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::CONFIRMED->value,
            'pending_expires_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::ACTIVE->value,
            'pending_expires_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::COMPLETED->value,
            'pending_expires_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::CANCELLED->value,
            'cancellation_reason' => 'Test cancellation',
            'cancelled_at' => now(),
            'pending_expires_at' => null,
        ]);
    }

    public function noShow(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::NO_SHOW->value,
            'cancellation_reason' => 'Customer did not show up',
            'cancelled_at' => now(),
            'pending_expires_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::PENDING->value,
            'pending_expires_at' => now()->subMinutes(5),
            'created_at' => now()->subMinutes(90),
        ]);
    }
}
