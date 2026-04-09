<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethodSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentMethodsController extends Controller
{
    /**
     * Display payment methods settings.
     */
    public function index(): Response
    {
        $methods = PaymentMethodSetting::orderBy('sort_order')->get()->map(function ($method) {
            $isConfigured = match ($method->method) {
                'paypal' => PaymentMethodSetting::isPayPalConfigured(),
                'stripe' => PaymentMethodSetting::isStripeConfigured(),
                default => false,
            };

            return [
                'id' => $method->id,
                'method' => $method->method,
                'is_enabled' => $method->is_enabled,
                'is_sandbox' => $method->is_sandbox,
                'display_name' => $method->display_name,
                'description' => $method->description,
                'sort_order' => $method->sort_order,
                'is_configured' => $isConfigured,
            ];
        });

        return Inertia::render('Admin/PaymentMethods/Index', [
            'methods' => $methods,
        ]);
    }

    /**
     * Update payment method settings.
     */
    public function update(Request $request, string $method)
    {
        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
            'is_sandbox' => 'required|boolean',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $setting = PaymentMethodSetting::where('method', $method)->first();

        if (!$setting) {
            return back()->with('error', 'Payment method not found.');
        }

        // Check if API keys are configured before enabling
        if ($validated['is_enabled']) {
            $isConfigured = match ($method) {
                'paypal' => PaymentMethodSetting::isPayPalConfigured(),
                'stripe' => PaymentMethodSetting::isStripeConfigured(),
                default => false,
            };

            if (!$isConfigured) {
                return back()->with('error', "Cannot enable {$method}: API keys are not configured in .env file.");
            }
        }

        $setting->update($validated);

        return back()->with('success', ucfirst($method) . ' settings updated successfully.');
    }

    /**
     * Toggle payment method status.
     */
    public function toggle(Request $request, string $method)
    {
        $setting = PaymentMethodSetting::where('method', $method)->first();

        if (!$setting) {
            return back()->with('error', 'Payment method not found.');
        }

        // Check if API keys are configured before enabling
        if (!$setting->is_enabled) {
            $isConfigured = match ($method) {
                'paypal' => PaymentMethodSetting::isPayPalConfigured(),
                'stripe' => PaymentMethodSetting::isStripeConfigured(),
                default => false,
            };

            if (!$isConfigured) {
                return back()->with('error', "Cannot enable {$method}: API keys are not configured in .env file.");
            }
        }

        $newStatus = !$setting->is_enabled;
        $setting->update(['is_enabled' => $newStatus]);

        // Clear cached payment methods
        PaymentMethodSetting::clearCache();

        $status = $newStatus ? 'enabled' : 'disabled';

        return back()->with('success', ucfirst($method) . " has been {$status}.");
    }
}
