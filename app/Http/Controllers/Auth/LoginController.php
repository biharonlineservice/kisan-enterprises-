<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /** Failed attempts allowed per mobile+IP before lockout. */
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 300;

    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $normalizedMobile = $this->normalizeMobile((string) $request->input('mobile', ''));
        $request->merge(['mobile' => $normalizedMobile]);

        $data = $request->validate([
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'password' => ['required', 'string', 'max:200'],
        ], [
            'mobile.required' => __('auth.mobile_required'),
            'mobile.regex' => __('auth.mobile_invalid'),
        ], [
            'mobile' => __('auth.mobile_number'),
        ]);

        $mobile = $data['mobile'];
        $throttleKey = $mobile.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'mobile' => __('auth.throttle_minutes', ['minutes' => (int) ceil($seconds / 60)]),
            ]);
        }

        $user = User::where('mobile', $mobile)->first();

        // Same generic message whether the account exists or not.
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            AuditLogger::log('auth.login_failed', $user, [], ['mobile' => $mobile], 'Failed mobile login attempt', $user?->id);
            throw ValidationException::withMessages(['mobile' => __('auth.failed')]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            AuditLogger::log('auth.login_blocked', $user, [], ['mobile' => $mobile], 'Login attempt on deactivated account', $user->id);
            throw ValidationException::withMessages(['mobile' => __('auth.inactive_account')]);
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('last_activity_at', time());

        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();
        AuditLogger::log('auth.login', $user);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($request->user()) {
            AuditLogger::log('auth.logout', $request->user());
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out.');
    }

    /**
     * Strip spaces/hyphens and optional +91 / 0 country-trunk prefixes when a 10-digit
     * Indian mobile number was pasted with formatting.
     */
    private function normalizeMobile(string $raw): string
    {
        $trimmed = trim($raw);
        if ($trimmed === '' || preg_match('/[A-Za-z@]/', $trimmed)) {
            return $trimmed;
        }

        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
    }
}
