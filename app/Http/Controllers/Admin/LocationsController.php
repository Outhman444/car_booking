<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Locations/Index', [
            'locations' => Location::orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Location::create($validated);

        return redirect()->back()->with('success', 'Location added successfully.');
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $location->update($validated);

        return redirect()->back()->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->back()->with('success', 'Location deleted successfully.');
    }
}
