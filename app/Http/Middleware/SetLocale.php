<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** Supported application locales. */
    public const SUPPORTED = ['hi', 'en'];

    /** Default fallback locale for first-time users. */
    public const DEFAULT_LOCALE = 'hi';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. Session preference
        if ($request->hasSession() && $request->session()->has('locale')) {
            $candidate = $request->session()->get('locale');
            if (in_array($candidate, self::SUPPORTED, true)) {
                $locale = $candidate;
            }
        }

        // 2. Authenticated user preference
        if (! $locale && $request->user() && ! empty($request->user()->locale)) {
            $candidate = $request->user()->locale;
            if (in_array($candidate, self::SUPPORTED, true)) {
                $locale = $candidate;
                if ($request->hasSession()) {
                    $request->session()->put('locale', $locale);
                }
            }
        }

        // 3. Persistent Cookie
        if (! $locale && $request->hasCookie('kisan_locale')) {
            $candidate = $request->cookie('kisan_locale');
            if (in_array($candidate, self::SUPPORTED, true)) {
                $locale = $candidate;
                if ($request->hasSession()) {
                    $request->session()->put('locale', $locale);
                }
            }
        }

        // 4. Default for first-time visitors / guests is Hindi ('hi')
        if (! $locale) {
            $locale = self::DEFAULT_LOCALE;
            if ($request->hasSession()) {
                $request->session()->put('locale', $locale);
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
