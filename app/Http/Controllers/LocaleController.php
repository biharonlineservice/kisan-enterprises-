<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch application language and return to previous page without state loss.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetLocale::SUPPORTED, true)) {
            $locale = SetLocale::DEFAULT_LOCALE;
        }

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        if ($user = $request->user()) {
            $user->update(['locale' => $locale]);
        }

        $cookie = cookie('kisan_locale', $locale, 60 * 24 * 365, null, null, false, false);

        return back()->withCookie($cookie);
    }
}
