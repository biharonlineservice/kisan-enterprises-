<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects a second submission of the same form.
 *
 * Forms include <x-form-token /> which renders a random single-use token. The first
 * request carrying a token claims it atomically (Cache::add); any replay — double
 * click, browser refresh/resubmit, network retry — is redirected back with a notice.
 * Financial documents additionally store the token in a unique DB column.
 */
class PreventDuplicateSubmission
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $token = $request->input('_form_token');
        if (! is_string($token) || ! preg_match('/^[A-Za-z0-9\-]{20,64}$/', $token)) {
            return $next($request);
        }

        $key = 'form_token:'.$request->user()?->id.':'.$token;
        if (! Cache::add($key, true, now()->addHours(6))) {
            $message = 'This form was already submitted. Duplicate submission was ignored.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 409);
            }

            return redirect()->back()->with('warning', $message);
        }

        $response = $next($request);

        // If validation failed, release the token so the user can correct and resubmit
        // (the re-rendered form gets a fresh token anyway).
        if ($response->getStatusCode() === 302 && $request->session()->has('errors')) {
            Cache::forget($key);
        }

        return $response;
    }
}
