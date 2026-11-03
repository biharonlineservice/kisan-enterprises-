<?php

use App\Http\Middleware\EnforceSessionSecurity;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\PreventDuplicateSubmission;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            SecurityHeaders::class,
            PreventDuplicateSubmission::class,
        ]);

        $middleware->alias([
            'session.secure' => EnforceSessionSecurity::class,
            'password.fresh' => ForcePasswordChange::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        // Hostinger and most shared hosts terminate SSL at a proxy.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handle419 = function (\Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => app()->getLocale() === 'hi'
                        ? 'सुरक्षा सत्र समाप्त हो गया था। कृपया पुनः प्रयास करें।'
                        : 'Your session expired. Please refresh and try again.',
                ], 419);
            }

            if ($request->hasSession()) {
                try {
                    $request->session()->regenerateToken();
                } catch (\Throwable) {
                    // Session store unavailable, continue
                }
            }

            if ($request->user() && ($request->routeIs('login.attempt') || $request->is('login') || $request->is('*/login'))) {
                return redirect()->route('dashboard');
            }

            if ($request->routeIs('logout') || $request->is('logout') || $request->is('*/logout')) {
                $redirect = redirect()->route('login');
                if ($request->hasSession()) {
                    $redirect->with(
                        'info',
                        app()->getLocale() === 'hi'
                            ? 'आपका सत्र पहले ही समाप्त हो चुका था।'
                            : 'Your session had already ended. Please sign in again if needed.'
                    );
                }
                return $redirect;
            }

            $warning = app()->getLocale() === 'hi'
                ? 'पेज का सुरक्षा सत्र समाप्त हो गया था — नया टोकन लोड कर दिया गया है, कृपया अब पुनः सबमिट करें।'
                : 'Your page session had expired and has been refreshed automatically. Please try again.';

            $isLogin = $request->routeIs('login.attempt') || $request->is('login') || $request->is('*/login');
            $target = $isLogin ? redirect()->route('login') : redirect()->back(fallback: route('login'));

            if ($request->hasSession()) {
                try {
                    $target->withInput($request->except(['password', 'password_confirmation', '_token', '_form_token']))
                        ->with('warning', $warning);
                } catch (\Throwable) {
                    // Fallback to plain redirect if session flashing fails
                }
            }

            return $target;
        };

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) use ($handle419) {
            return $handle419($request);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) use ($handle419) {
            if ($e->getStatusCode() === 419) {
                return $handle419($request);
            }
        });

        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) use ($handle419) {
            if ($response->getStatusCode() === 419 && ! $request->expectsJson()) {
                return $handle419($request);
            }

            return $response;
        });
    })->create();
