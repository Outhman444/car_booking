<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class PaymentsController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $payments = Payment::query()
            ->with(['user:id,name,email', 'reservation:id,reservation_number'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('payment_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($qu) use ($search) {
                            $qu->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('reservation', function ($qr) use ($search) {
                            $qr->where('reservation_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $statusCounts = Payment::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = collect(PaymentStatus::cases())->map(function ($status) use ($statusCounts) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
                'count' => $statusCounts[$status->value] ?? 0,
                'color' => $status->color(),
            ];
        })->values()->toArray();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'statuses' => $statuses,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'currency' => [
                'symbol' => config('app.currency_symbol'),
                'code' => config('app.currency_code'),
            ],
        ]);
    }
}
