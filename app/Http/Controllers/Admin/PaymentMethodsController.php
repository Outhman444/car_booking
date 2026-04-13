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
            return [
                'id' => $method->id,
                'method' => $method->method,
                'is_enabled' => $method->is_enabled,
                'is_sandbox' => $method->is_sandbox,
                'display_name' => $method->display_name,
                'description' => $method->description,
                'sort_order' => $method->sort_order,
                'is_configured' => PaymentMethodSetting::isConfigured($method->method),
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
        \Log::info('=== PAYMENT METHOD UPDATE START ===', [
            'method' => $method,
            'request_all' => $request->all(),
            'request_method' => $request->method(),
        ]);

        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
            'is_sandbox' => 'sometimes|boolean',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // Default is_sandbox to false for methods that don't use it (e.g. agency)
        if (!isset($validated['is_sandbox'])) {
            $validated['is_sandbox'] = false;
        }

        \Log::info('Validated data:', $validated);

        $setting = PaymentMethodSetting::where('method', $method)->first();

        if (!$setting) {
            \Log::error('Payment method not found: ' . $method);
            return back()->with('error', 'Payment method not found.');
        }

        \Log::info('BEFORE update:', $setting->toArray());

        // Check if API keys are configured before enabling (skip for agency)
        if ($validated['is_enabled'] && $method !== 'agency') {
            if (!PaymentMethodSetting::isConfigured($method)) {
                \Log::warning("Cannot enable {$method}: not configured");
                return back()->with('error', "Cannot enable {$method}: API keys are not configured in .env file.");
            }
        }

        $result = $setting->update($validated);

        \Log::info('Update result: ' . ($result ? 'true' : 'false'));
        \Log::info('AFTER update:', $setting->fresh()->toArray());
        \Log::info('=== PAYMENT METHOD UPDATE END ===');

        // Clear cached payment methods so frontend updates immediately
        PaymentMethodSetting::clearCache();

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
            if (!PaymentMethodSetting::isConfigured($method)) {
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
