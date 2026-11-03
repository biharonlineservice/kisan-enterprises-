<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Users created/reset by Admin with a temporary password must set their own password first.
 */
class ForcePasswordChange
{
    private const ALLOWED = ['password.change', 'password.change.update', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs(...self::ALLOWED)) {
            return redirect()->route('password.change')
                ->with('warning', 'Please set a new password before continuing.');
        }

        return $next($request);
    }
}
