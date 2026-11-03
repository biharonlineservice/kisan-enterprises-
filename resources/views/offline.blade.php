<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.name') }} — {{ __('app.offline_notice') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; background: #F6F8F6; padding: 1.5rem; text-align: center; }
        .offline-card { max-width: 440px; background: #fff; border-radius: 16px; padding: 2.5rem 2rem; border: 1px solid #E3E9E4; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .offline-icon { width: 72px; height: 72px; border-radius: 50%; background: #FBECEC; color: #D94848; display: grid; place-items: center; font-size: 2rem; margin: 0 auto 1.5rem; }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon">
            <i class="bi bi-wifi-off"></i>
        </div>
        <h3 class="fw-bold mb-2">{{ app()->getLocale() === 'hi' ? 'इंटरनेट कनेक्शन उपलब्ध नहीं है' : 'No Internet Connection' }}</h3>
        <p class="text-secondary mb-4">
            {{ __('app.offline_notice') }}
        </p>
        <div class="d-flex flex-column gap-2">
            <button onclick="window.location.reload();" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-arrow-clockwise"></i> {{ __('app.retry') }}
            </button>
            <a href="/" class="btn btn-light w-100">
                <i class="bi bi-house"></i> {{ __('app.home') }}
            </a>
        </div>
        <div class="mt-4 pt-3 border-top text-muted small">
            {{ setting('business.name', 'Kisan Enterprises') }} &bull; {{ __('app.tagline') }}
        </div>
    </div>
</body>
</html>
