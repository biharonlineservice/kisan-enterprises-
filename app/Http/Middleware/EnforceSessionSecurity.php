<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out users who have been deactivated or have been idle longer than
 * the configured session timeout (Settings → Security).
 */
class EnforceSessionSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if (! $user->is_active) {
            return $this->logout($request, 'Your account has been deactivated. Please contact the administrator.', 'auth.deactivated_logout');
        }

        $timeout = max(5, (int) setting('security.session_timeout_minutes', 30));
        $last = (int) $request->session()->get('last_activity_at', time());

        if (time() - $last > $timeout * 60) {
            return $this->logout($request, 'Your session expired after '.$timeout.' minutes of inactivity. Please sign in again.', 'auth.session_timeout');
        }

        $request->session()->put('last_activity_at', time());

        return $next($request);
    }

    private function logout(Request $request, string $message, string $action): Response
    {
        AuditLogger::log($action, $request->user());
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()->route('login')->with('warning', $message);
    }
}
