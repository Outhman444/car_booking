<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): Response
    {
        $settings = Setting::orderBy('group')->orderBy('sort_order')->get();
        
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'groups' => ['general', 'contact', 'booking', 'appearance', 'social'],
        ]);
    }

    /**
     * Update multiple settings.
     */
    public function update(Request $request)
    {
        $input = $request->except(['_token', '_method']);
        
        // Get all valid keys from the database to filter out internal Inertia/Laravel keys
        $validKeys = Setting::pluck('key')->toArray();

        foreach ($input as $key => $value) {
            if (in_array($key, $validKeys)) {
                Setting::where('key', $key)->update(['value' => $value]);
            }
        }

        Setting::clearCache();

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
