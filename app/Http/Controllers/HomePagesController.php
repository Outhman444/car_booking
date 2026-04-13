<?php

namespace App\Http\Controllers;

use App\Enums\CarStatus;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\ReservationStatus;

class HomePagesController extends Controller
{
    public function index()
    {
        $this->cleanupExpiredCashReservations();

        $homeCars = Car::where('status', CarStatus::AVAILABLE)
            ->select('id', 'make', 'model', 'year', 'price_per_day', 'description', 'fuel_type')
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $makes = Car::where('status', CarStatus::AVAILABLE)
            ->distinct()
            ->pluck('make')
            ->toArray();

        // Common locations for the search form
        $locations = [
            'Airport Terminal 1',
            'Airport Terminal 2',
            'City Center',
            'Main Train Station',
            'Beach Resort Area',
            'Business District'
        ];

        return inertia('Welcome', compact('homeCars', 'makes', 'locations'));
    }

    public function fleet(Request $request)
    {
        $this->cleanupExpiredCashReservations();

        $query = Car::where('status', CarStatus::AVAILABLE)
            ->select('id', 'make', 'model', 'year', 'price_per_day', 'description', 'fuel_type', 'transmission', 'seats', 'color', 'mileage');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('make', 'like', "%{$searchTerm}%")
                    ->orWhere('model', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Make filter
        if ($request->filled('make')) {
            $query->where('make', $request->make);
        }

        // Fuel type filter
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Transmission filter
        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        // Seats filter
        if ($request->filled('seats')) {
            $query->where('seats', '>=', $request->seats);
        }

        // Color filter
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }

        // Mileage filter
        if ($request->filled('max_mileage')) {
            $query->where('mileage', '<=', $request->max_mileage);
        }

        // Date range availability filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $query->whereDoesntHave('reservations', function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) {
                    $q->whereIn('status', [ReservationStatus::CONFIRMED, ReservationStatus::ACTIVE])
                      ->orWhere(function ($q2) {
                          $q2->where('status', ReservationStatus::PENDING)
                             ->where('notes', 'like', '%Pay at Agency%');
                      });
                })->betweenDates($startDate, $endDate);
            });
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', $request->max_price);
        }

        $cars = $query->paginate(10)->withQueryString();

        // Get filter options
        $makes = Car::where('status', CarStatus::AVAILABLE)
            ->distinct()
            ->pluck('make')
            ->toArray();

        $fuelTypes = Car::where('status', CarStatus::AVAILABLE)
            ->distinct()
            ->pluck('fuel_type')
            ->toArray();

        $years = Car::where('status', CarStatus::AVAILABLE)
            ->distinct()
            ->pluck('year')
            ->toArray();

        $transmissions = Car::where('status', CarStatus::AVAILABLE)
            ->distinct()
            ->pluck('transmission')
            ->toArray();

        $seats = Car::where('status', CarStatus::AVAILABLE)
            ->distinct()
            ->pluck('seats')
            ->toArray();

        $colors = \App\Enums\CarColor::forFrontend();

        $maxMileage = Car::where('status', CarStatus::AVAILABLE)->max('mileage') ?? 100000;

        $filters = $request->only(['search', 'make', 'fuel_type', 'min_price', 'max_price', 'year', 'start_date', 'end_date', 'pickup_location', 'transmission', 'seats', 'color', 'max_mileage']);

        return inertia('Fleet', compact('cars', 'makes', 'fuelTypes', 'years', 'transmissions', 'seats', 'colors', 'maxMileage', 'filters'));
    }

    public function about()
    {
        return inertia('About');
    }

    public function contact()
    {
        return inertia('Contact');
    }

    public function guestContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'guest_name' => $request->name,
            'guest_email' => $request->email,
            'subject' => $request->subject,
        ]);

        $ticket->messages()->create([
            'message' => $request->message,
        ]);

        return redirect()->route('contact')->with('success', 'Message sent successfully!');
    }

    /**
     * Lazy execution to clean up expired Cash reservations
     * without needing a cron job or schedule.
     */
    private function cleanupExpiredCashReservations(): void
    {
        $timeoutHours = (int) \App\Models\Setting::getValue('cash_reservation_timeout', 24);
        
        $expiredReservations = Reservation::where('status', ReservationStatus::PENDING)
            ->where('notes', 'like', '%Pay at Agency%')
            ->where('updated_at', '<', now()->subHours($timeoutHours))
            ->with('car')
            ->get();

        foreach ($expiredReservations as $reservation) {
            $reservation->update([
                'status' => ReservationStatus::CANCELLED,
                'cancellation_reason' => "Auto-cancelled: Customer did not pay cash within {$timeoutHours} hours.",
                'cancelled_at' => now(),
            ]);

            if ($reservation->car && $reservation->car->status === CarStatus::PENDING) {
                $reservation->car->update(['status' => CarStatus::AVAILABLE]);
            }
        }
    }
}
