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
            if (!PaymentMethodSetting::isConfigured($method)) {
                return back()->with('error', "Cannot enable {$method}: API keys are not configured in .env file.");
            }
        }

        $setting->update($validated);

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
