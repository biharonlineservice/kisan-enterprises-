<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ]);

        $user = $request->user();
        $user->forceFill(['password' => $request->password, 'must_change_password' => false])->save();

        // Sign out other devices using the old password.
        auth()->logoutOtherDevices($request->password);
        $request->session()->regenerate();

        AuditLogger::log('auth.password_changed', $user);

        return redirect()->route('dashboard')->with('success', 'Your password has been changed.');
    }
}
