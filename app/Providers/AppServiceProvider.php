<?php

namespace App\Providers;

use App\Models\User;
use App\Services\SettingsService;
use App\Support\Menu;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Every permission key from config/kisan.php is usable as a Gate ability:
        //   Route middleware  ->middleware('can:purchases.create')
        //   Blade             @can('purchases.create')
        // Returning null lets explicitly-defined gates/policies decide other abilities.
        Gate::before(function (User $user, string $ability) {
            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability) ? true : null;
            }

            return null;
        });

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        View::composer('layouts.app', function ($view) {
            $view->with('menu', Menu::for(auth()->user()));
        });
    }
}
