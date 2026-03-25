<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale && in_array($locale, ['it', 'en'])) {
            app()->setLocale($locale);
            session(['locale' => $locale]);
        } elseif (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }

        return $next($request);
    }
}
