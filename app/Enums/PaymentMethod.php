<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case CASH = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::PAYPAL => 'PayPal',
            self::STRIPE => 'Credit/Debit Card (Stripe)',
            self::CASH => 'Cash (Pay at Agency)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PAYPAL => 'Pay securely with your PayPal account',
            self::STRIPE => 'Pay with credit or debit card via Stripe',
            self::CASH => 'Pay in cash when you arrive to pick up your car',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PAYPAL => 'paypal',
            self::STRIPE => 'credit-card',
            self::CASH => 'banknotes',
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
