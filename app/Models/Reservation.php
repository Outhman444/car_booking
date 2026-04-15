<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Services\ReservationStateService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_number',
        'user_id',
        'car_id',
        'start_date',
        'end_date',
        'pickup_time',
        'return_time',
        'pickup_location',
        'return_location',
        'total_days',
        'daily_rate',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'pending_expires_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_paid',
        'payment_method_label',
        'payment_status',
        'deposit_amount',
        'remaining_amount',
        'security_deposit_amount',
    ];

    /**
     * Check if the reservation is paid.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->payments()->where('status', \App\Enums\PaymentStatus::COMPLETED)->exists();
    }

    /**
     * Get the payment status label.
     */
    public function getPaymentStatusAttribute(): string
    {
        if ($this->getIsPaidAttribute()) {
            return 'paid';
        }

        // If reservation is confirmed, active, or completed - payment is due at pickup
        if (in_array($this->status, [
            ReservationStatus::CONFIRMED,
            ReservationStatus::ACTIVE,
            ReservationStatus::COMPLETED,
        ])) {
            return 'pay_at_pickup';
        }

        return 'unpaid';
    }

    /**
     * Get the deposit amount from settings (default 20%).
     */
    public function getDepositAmountAttribute(): float
    {
        $settings = \App\Models\Setting::where('key', 'booking_deposit_percentage')->first();
        $percentage = $settings ? (float) $settings->value : 20;

        return round($this->total_amount * ($percentage / 100), 2);
    }

    /**
     * Get the remaining amount after deposit.
     */
    public function getRemainingAmountAttribute(): float
    {
        return round($this->total_amount - $this->deposit_amount, 2);
    }

    /**
     * Get the security deposit amount from settings (informational only).
     */
    public function getSecurityDepositAmountAttribute(): float
    {
        $settings = \App\Models\Setting::where('key', 'security_deposit_amount')->first();
        return $settings ? (float) $settings->value : 0;
    }

    /**
     * Get the payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        $payment = $this->payments()->where('status', \App\Enums\PaymentStatus::COMPLETED)->first();
        
        if ($payment) {
            return $payment->payment_method->label();
        }

        if ($this->status === ReservationStatus::CONFIRMED) {
            return 'Paiement à l\'agence';
        }

        return 'Non payé';
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pickup_time' => 'datetime:H:i',
        'return_time' => 'datetime:H:i',
        'total_days' => 'integer',
        'daily_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'status' => ReservationStatus::class,
        'cancelled_at' => 'datetime',
        'pending_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the pending expiration time with fallback logic.
     */
    public function getPendingExpiresAtAttribute($value)
    {
        if (!$value) {
            $holdMinutes = (int) \App\Models\Setting::getValue('reservation_hold_time_minutes', 60);
            return $this->created_at ? $this->created_at->addMinutes($holdMinutes)->toIso8601ZuluString() : null;
        }

        // Convert to UTC/Zulu for frontend consistency
        return \Carbon\Carbon::parse($value)->toIso8601ZuluString();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (empty($reservation->reservation_number)) {
                $reservation->reservation_number = 'RES-' . strtoupper(Str::random(8));
            }

            // Set pending expiration time when creating a new pending reservation
            $status = $reservation->status ?? ReservationStatus::PENDING;
            if ($status === ReservationStatus::PENDING && empty($reservation->pending_expires_at)) {
                $holdMinutes = (int) \App\Models\Setting::getValue('reservation_hold_time_minutes', 60);
                $reservation->pending_expires_at = now()->addMinutes($holdMinutes);
            }
        });
    }

    /**
     * Get the user that owns the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the car that is reserved.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the payments for the reservation.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the formatted total amount attribute.
     *
     * @return string
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return config('app.currency_symbol') . number_format($this->total_amount, 2);
    }

    /**
     * Get the duration in days.
     *
     * @return int
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if reservation is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === ReservationStatus::ACTIVE;
    }

    /**
     * Check if reservation can be cancelled.
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            ReservationStatus::PENDING,
            ReservationStatus::CONFIRMED
        ]);
    }

    /**
     * Check if the reservation can transition to a given status.
     *
     * @param ReservationStatus $newStatus
     * @return bool
     */
    public function canTransitionTo(ReservationStatus $newStatus): bool
    {
        return app(ReservationStateService::class)->canTransition($this, $newStatus);
    }

    /**
     * Get all allowed next statuses for this reservation.
     *
     * @return array
     */
    public function allowedTransitions(): array
    {
        $transitions = [
            ReservationStatus::PENDING->value   => [ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW],
            ReservationStatus::CONFIRMED->value => [ReservationStatus::ACTIVE, ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW],
            ReservationStatus::ACTIVE->value    => [ReservationStatus::COMPLETED],
            ReservationStatus::COMPLETED->value => [],
            ReservationStatus::CANCELLED->value => [],
            ReservationStatus::NO_SHOW->value   => [],
        ];

        return $transitions[$this->status->value] ?? [];
    }

    /**
     * Check if the reservation is in a terminal (final) state.
     *
     * @return bool
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, [
            ReservationStatus::COMPLETED,
            ReservationStatus::CANCELLED,
            ReservationStatus::NO_SHOW,
        ]);
    }

    /**
     * Scope for active reservations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', ReservationStatus::ACTIVE);
    }

    /**
     * Scope for reservations by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }
}
