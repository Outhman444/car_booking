<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case AGENCY = 'agency';

    public function label(): string
    {
        return match ($this) {
            self::PAYPAL => 'PayPal',
            self::STRIPE => 'Credit/Debit Card (Stripe)',
            self::AGENCY => 'Pay at the Agency (Cash)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PAYPAL => 'Pay securely with your PayPal account',
            self::STRIPE => 'Pay with credit or debit card via Stripe',
            self::AGENCY => 'Reserve now and pay in person when you pick up your car',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PAYPAL => 'paypal',
            self::STRIPE => 'credit-card',
            self::AGENCY => 'banknote',
        };
    }

    public static function forFrontend(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
            'icon' => $case->icon(),
        ], self::cases());
    }
}
