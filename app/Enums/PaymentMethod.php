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
            self::STRIPE => 'Carte bancaire (Stripe)',
            self::CASH => 'Espèces (Paiement à l\'agence)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PAYPAL => 'Payez en toute sécurité avec votre compte PayPal',
            self::STRIPE => 'Payez par carte bancaire via Stripe',
            self::CASH => 'Payez en espèces lors du retrait du véhicule',
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
