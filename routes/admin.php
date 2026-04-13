<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CarsController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Http\Controllers\Admin\ClientsController;
use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\PaymentMethodsController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SupportController;

Route::middleware(['auth', 'verified', 'active', 'admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // Redirect '/admin' to '/admin/cars' with a named route we can reference
        Route::redirect('/', '/admin/cars')->name('home');

        // Cars
        Route::post('cars/{car}/quick-status', [CarsController::class, 'quickUpdate'])->name('cars.quick-update');
        Route::resource('cars', CarsController::class)->except(['show']);

        // Reservations
        Route::resource('reservations', ReservationsController::class)->only(['index', 'show', 'edit', 'update']);
        Route::get('reservations/{reservation}/print', [ReservationsController::class, 'print'])->name('reservations.print');
        Route::post('reservations/{reservation}/mark-as-paid', [ReservationsController::class, 'markAsPaid'])->name('reservations.mark-as-paid');
        Route::post('reservations/{reservation}/quick-status', [ReservationsController::class, 'quickUpdate'])->name('reservations.quick-update');

        // Clients
        Route::resource('clients', ClientsController::class)->only(['index', 'show']);
        Route::patch('clients/{client}/suspend', [ClientsController::class, 'suspend'])->name('clients.suspend');
        Route::patch('clients/{client}/activate', [ClientsController::class, 'activate'])->name('clients.activate');

        // Payments
        Route::resource('payments', PaymentsController::class)->only(['index']);

        // Payment Methods Settings
        Route::get('payment-methods', [PaymentMethodsController::class, 'index'])->name('payment-methods.index');
        Route::put('payment-methods/{method}', [PaymentMethodsController::class, 'update'])->name('payment-methods.update');
        Route::post('payment-methods/{method}/toggle', [PaymentMethodsController::class, 'toggle'])->name('payment-methods.toggle');

        // Reports
        Route::resource('reports', ReportsController::class)->except(['show']);

        // Support
        Route::resource('support', SupportController::class)->only(['index']);
        Route::get('/support/tickets/{ticket}', [SupportController::class, 'show'])
        ->name('support.show');
        Route::post('/support/tickets/{ticket}/reply', [SupportController::class, 'reply'])
        ->name('support.reply');
        Route::post('/support/tickets/{ticket}/close', [SupportController::class, 'close'])
        ->name('support.close');

        // Site Settings
        Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

        // Locations
        Route::resource('locations', \App\Http\Controllers\Admin\LocationsController::class)->except(['create', 'show', 'edit']);

    });
