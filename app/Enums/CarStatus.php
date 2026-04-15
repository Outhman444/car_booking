<?php

namespace App\Enums;

enum CarStatus: string
{
    case AVAILABLE      = 'available';
    case PENDING        = 'pending';
    case RESERVED       = 'reserved';
    case RENTED         = 'rented';
    case MAINTENANCE    = 'maintenance';
    case OUT_OF_SERVICE = 'out_of_service';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE      => 'Available',
            self::PENDING        => 'Pending (Payment)',
            self::RESERVED       => 'Reserved',
            self::RENTED         => 'Rented / Active',
            self::MAINTENANCE    => 'Maintenance',
            self::OUT_OF_SERVICE => 'Out of Service',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AVAILABLE      => 'The car is ready for booking and rental.',
            self::PENDING        => 'The car is booked but awaiting payment confirmation.',
            self::RESERVED       => 'The car is reserved for a customer and awaiting pickup.',
            self::RENTED         => 'The car is currently rented by a customer.',
            self::MAINTENANCE    => 'The car is undergoing service, repair, or cleaning.',
            self::OUT_OF_SERVICE => 'The car is temporarily or permanently out of service.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE      => '#10B981', // Green-500
            self::PENDING        => '#F59E0B', // Amber-500
            self::RESERVED       => '#3B82F6', // Blue-500
            self::RENTED         => '#8B5CF6', // Violet-500
            self::MAINTENANCE    => '#EF4444', // Red-500
            self::OUT_OF_SERVICE => '#4B5563', // Gray-600
        };
    }
}
