<?php

namespace App\Http\Middleware;

use App\Services\ExpiredReservationChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CancelExpiredReservations
{
    public function handle(Request $request, Closure $next): Response
    {
        app(ExpiredReservationChecker::class)->checkAndCancelExpired();

        return $next($request);
    }
}
