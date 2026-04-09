<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentMethodSetting extends Model
{
    protected $table = 'payment_methods_settings';

    protected $fillable = [
        'method',
        'is_enabled',
        'is_sandbox',
        'display_name',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_sandbox' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get enabled payment methods for frontend.
     */
    public static function getEnabledMethods(): array
    {
        return Cache::remember('payment_methods_enabled', 3600, function () {
            $settings = self::where('is_enabled', true)
                ->orderBy('sort_order')
                ->get();

            return $settings->map(function ($setting) {
                $method = PaymentMethod::from($setting->method);
                return [
                    'value' => $setting->method,
                    'label' => $setting->display_name ?? $method->label(),
                    'description' => $setting->description ?? $method->description(),
                    'icon' => $method->icon(),
                    'is_sandbox' => $setting->is_sandbox,
                ];
            })->toArray();
        });
    }

    /**
     * Check if a payment method is enabled.
     */
    public static function isEnabled(string $method): bool
    {
        return Cache::remember("payment_method_{$method}_enabled", 3600, function () use ($method) {
            return self::where('method', $method)
                ->where('is_enabled', true)
                ->exists();
        });
    }

    /**
     * Clear payment methods cache (call after admin updates settings).
     */
    public static function clearCache(): void
    {
        Cache::forget('payment_methods_enabled');
        foreach (PaymentMethod::cases() as $method) {
            Cache::forget("payment_method_{$method->value}_enabled");
        }
    }

    /**
     * Get settings for a specific method.
     */
    public static function getSettings(string $method): ?self
    {
        return self::where('method', $method)->first();
    }

    /**
     * Check if PayPal is configured.
     */
    public static function isPayPalConfigured(): bool
    {
        return config('services.paypal.client_id') && config('services.paypal.secret');
    }

    /**
     * Check if Stripe is configured.
     */
    public static function isStripeConfigured(): bool
    {
        return config('services.stripe.key') && config('services.stripe.secret');
    }
}
