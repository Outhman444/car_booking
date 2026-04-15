<?php

namespace Database\Factories;

use App\Enums\CarColor;
use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {
        return [
            'make' => fake()->randomElement(['Toyota', 'Honda', 'BMW', 'Mercedes', 'Audi']),
            'model' => fake()->randomElement(['Corolla', 'Civic', 'X5', 'C-Class', 'A4']),
            'year' => fake()->numberBetween(2020, 2025),
            'license_plate' => strtoupper(fake()->unique()->bothify('??-####-??')),
            'color' => CarColor::WHITE->value,
            'price_per_day' => fake()->randomFloat(2, 30, 200),
            'mileage' => fake()->numberBetween(1000, 80000),
            'transmission' => fake()->randomElement(['automatic', 'manual']),
            'seats' => fake()->numberBetween(2, 7),
            'fuel_type' => FuelType::GASOLINE->value,
            'description' => fake()->sentence(),
            'status' => CarStatus::AVAILABLE->value,
        ];
    }

    public function available(): static
    {
        return $this->state(fn () => ['status' => CarStatus::AVAILABLE->value]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => CarStatus::PENDING->value]);
    }

    public function reserved(): static
    {
        return $this->state(fn () => ['status' => CarStatus::RESERVED->value]);
    }

    public function rented(): static
    {
        return $this->state(fn () => ['status' => CarStatus::RENTED->value]);
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => CarStatus::MAINTENANCE->value]);
    }

    public function outOfService(): static
    {
        return $this->state(fn () => ['status' => CarStatus::OUT_OF_SERVICE->value]);
    }
}
