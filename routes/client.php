<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ReservationsController;
use App\Http\Controllers\Client\SupportController;
use App\Http\Controllers\Client\PaymentController;

Route::middleware(['auth', 'verified', 'active', 'client'])
    ->prefix('client')
    ->as('client.')
    ->group(function () {
        // Redirect '/client' to '/client/reservations' with a named route we can reference
        Route::redirect('/', '/client/reservations')->name('home');
        Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{id}', [ReservationsController::class, 'show'])->name('reservations.show');
        Route::get('/reservations/{id}/print', [ReservationsController::class, 'print'])->name('reservations.print');

        // Payment (with rate limiting)
        Route::middleware(['throttle:5,1'])->group(function () {
            Route::get('/payment/{reservation}', [PaymentController::class, 'show'])->name('payment.show');
            Route::post('/payment/{reservation}/stripe', [PaymentController::class, 'processStripe'])->name('payment.stripe');
            Route::post('/payment/{reservation}/paypal', [PaymentController::class, 'processPayPal'])->name('payment.paypal');
        });
        Route::get('/payment/{reservation}/paypal/success', [PaymentController::class, 'paypalSuccess'])->name('payment.paypal.success');
        Route::get('/payment/{reservation}/paypal/cancel', [PaymentController::class, 'paypalCancel'])->name('payment.paypal.cancel');

        // Support
        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::get('/support/create', [SupportController::class, 'create'])->name('support.create');
        Route::post('/support', [SupportController::class, 'store'])->name('support.store');
        Route::get('/support/{id}', [SupportController::class, 'show'])->name('support.show');
        Route::post('/support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');

    });
