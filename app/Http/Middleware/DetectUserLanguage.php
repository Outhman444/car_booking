<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DetectUserLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getPreferredLocale($request);

        if ($locale && in_array($locale, config('app.available_locales', ['en', 'ar', 'fr']))) {
            App::setLocale($locale);
            
            // Persist for authenticated users if different from their profile
            if ($request->user() && $request->user()->locale !== $locale) {
                $request->user()->update(['locale' => $locale]);
            }
        }

        return $next($request);
    }

    /**
     * Determine the preferred locale.
     */
    protected function getPreferredLocale(Request $request): ?string
    {
        // 1. Check authenticated user's profile
        if ($request->user() && $request->user()->locale) {
            return $request->user()->locale;
        }

        // 2. Check session
        if (Session::has('locale')) {
            return Session::get('locale');
        }

        // 3. Check cookie
        if ($request->hasCookie('locale')) {
            return $request->cookie('locale');
        }

        // 4. Check Browser Accept-Language header
        return $request->getPreferredLanguage(['en', 'ar', 'fr']);
    }
}
