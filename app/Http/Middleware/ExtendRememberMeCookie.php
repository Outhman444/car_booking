<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ExtendRememberMeCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if the user is authenticated and "remember me" is active
        if (Auth::check() && $request->hasCookie(Auth::getRecallerName())) {
            $recaller = $request->cookie(Auth::getRecallerName());
            
            // Refresh the "remember_web_*" cookie to extend its life (e.g., to 5 years / 2628000 minutes)
            if ($recaller) {
                $response->withCookie(
                    Cookie::make(
                        Auth::getRecallerName(),
                        $recaller,
                        2628000, // 5 years in minutes
                        '/',
                        null,
                        true, // secure
                        true, // httpOnly
                        false,
                        'Lax'
                    )
                );
            }
        }

        return $next($request);
    }
}
