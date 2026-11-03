<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', __('auth.login_title')) — {{ setting('business.name', 'Kisan Enterprises') }}</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#21864B">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-light">
    <div class="auth-wrap">
        <aside class="auth-aside">
            <div>
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="brand-mark">
                        <i class="bi bi-tree"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 text-white fw-bold">{{ setting('business.name', 'Kisan Enterprises') }}</h4>
                        <small class="text-white-50">{{ setting('business.tagline', __('app.tagline')) }}</small>
                    </div>
                </div>
                <h2 class="mb-3">{{ app()->getLocale() === 'hi' ? 'पारदर्शी कृषि व्यापार एवं डिजिटल मंडी प्रबंधन' : 'Transparent Agricultural Trading & Management' }}</h2>
                <p class="mb-4">{{ app()->getLocale() === 'hi' ? 'किसानों से सीधे फसल खरीद, डिजिटल कंप्यूटर धर्मकांटा तौल, राइस मिलों को थोक आपूर्ति एवं समय पर पारदर्शी भुगतान प्रणाली।' : 'Streamlining procurement directly from farmers, managing grain inventory, tracking dispatch to mills, and maintaining accurate financial ledgers.' }}</p>
                <ul class="points list-unstyled">
                    <li><i class="bi bi-check-circle-fill text-warning"></i> {{ app()->getLocale() === 'hi' ? 'सटीक धर्मकांटा तौल एवं तत्काल कंप्यूटरीकृत तौल पर्ची' : 'Accurate weighment and instant purchase receipts' }}</li>
                    <li><i class="bi bi-check-circle-fill text-warning"></i> {{ app()->getLocale() === 'hi' ? 'गोदाम-वार रीयल-टाइम स्टॉक एवं आवक-जावक लेजर' : 'Real-time multi-warehouse stock ledgers' }}</li>
                    <li><i class="bi bi-check-circle-fill text-warning"></i> {{ app()->getLocale() === 'hi' ? 'किसानों को शत-प्रतिशत त्वरित एवं सुरक्षित भुगतान' : 'Clear farmer payment and customer receipt tracking' }}</li>
                </ul>
            </div>
            <div class="text-white-50 small">
                &copy; {{ date('Y') }} {{ setting('business.name', 'Kisan Enterprises') }}. {{ __('app.all_rights_reserved') }}
            </div>
        </aside>

        <main class="auth-panel">
            <header class="auth-header w-100 p-3 px-sm-4 d-flex justify-content-between align-items-center flex-shrink-0">
                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary bg-white shadow-sm rounded-pill px-3 text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>{{ app()->getLocale() === 'hi' ? 'वेबसाइट' : 'Website' }}
                </a>
                @include('components.language-switcher')
            </header>

            <div class="auth-content-wrap flex-grow-1 d-flex align-items-center justify-content-center p-3 p-sm-4">
                <div class="auth-card my-auto">
                    <div class="text-center d-lg-none mb-4">
                        <div class="brand-mark mx-auto mb-2">
                            <i class="bi bi-tree"></i>
                        </div>
                        <h4 class="fw-bold mb-0">{{ setting('business.name', 'Kisan Enterprises') }}</h4>
                        <small class="text-muted">{{ setting('business.tagline', __('app.tagline')) }}</small>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <div class="small">{{ session('success') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                            <i class="bi bi-info-circle-fill"></i>
                            <div class="small">{{ session('info') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div class="small">{{ session('warning') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                            <div class="small">{{ session('error') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
    (function () {
        const csrfUrl = @json(route('csrf.refresh'));
        let lastRefresh = Date.now();
        let refreshing = false;

        function refreshCsrfToken(force) {
            if (refreshing) return;
            // Allow refresh if forced or if at least 30 seconds have passed since last refresh
            if (!force && (Date.now() - lastRefresh) < 30 * 1000) return;

            refreshing = true;
            fetch(csrfUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (data && data.token) {
                        document.querySelectorAll('input[name="_token"]').forEach(function (el) {
                            el.value = data.token;
                        });
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.setAttribute('content', data.token);
                        lastRefresh = Date.now();
                    }
                })
                .catch(function () {})
                .finally(function () { refreshing = false; });
        }

        // Force-refresh when restored from back/forward cache
        window.addEventListener('pageshow', function (e) {
            refreshCsrfToken(true);
        });

        // Refresh when tab becomes visible or gains focus
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') refreshCsrfToken(false);
        });
        window.addEventListener('focus', function () {
            refreshCsrfToken(false);
        });

        // Periodic background refresh every 10 minutes
        setInterval(function () {
            refreshCsrfToken(true);
        }, 10 * 60 * 1000);
    })();
    </script>
    @stack('scripts')
</body>
</html>
