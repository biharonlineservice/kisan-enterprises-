<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.dashboard')) — {{ setting('business.name', 'Kisan Enterprises') }} ERP</title>

    {{-- PWA Meta Tags & Icons --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#21864B">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Kisan ERP">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <script>
        (function () {
            try {
                if (localStorage.getItem('ke_sidebar_collapsed') === '1' && window.innerWidth >= 992) {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
    @stack('styles')
</head>
<body>
    <div class="sidebar-backdrop"></div>

    {{-- Desktop / Tablet Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            @php
                $sidebarLogo = setting('business.logo');
                $sidebarLogoUrl = null;
                if ($sidebarLogo && file_exists(public_path('uploads/' . $sidebarLogo))) {
                    $sidebarLogoUrl = asset('uploads/' . $sidebarLogo);
                } elseif (file_exists(public_path('uploads/branding/kisanhub-logo.png'))) {
                    $sidebarLogoUrl = asset('uploads/branding/kisanhub-logo.png');
                } elseif (file_exists(public_path('images/kisanhub-logo.png'))) {
                    $sidebarLogoUrl = asset('images/kisanhub-logo.png');
                }
            @endphp
            @if($sidebarLogoUrl)
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none flex-grow-1 overflow-hidden" title="{{ setting('business.name', 'Kisan Enterprises') }}">
                    <img src="{{ $sidebarLogoUrl }}" alt="{{ setting('business.name') }}">
                </a>
            @else
                <div class="brand-mark overflow-hidden p-0" title="{{ setting('business.name', 'Kisan Enterprises') }}">
                    <img src="{{ asset('images/kisanhub-emblem.png') }}" alt="{{ setting('business.name') }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="overflow-hidden flex-grow-1" style="min-width: 0;">
                    <div class="brand-name text-truncate">{{ setting('business.name', 'Kisan Enterprises') }}</div>
                    <div class="brand-sub text-truncate">{{ setting('business.tagline', __('app.tagline')) }}</div>
                </div>
            @endif
            <button type="button" class="sidebar-hide-btn ms-auto d-lg-none" data-sidebar-toggle aria-label="{{ __('app.hide_sidebar') }}" title="{{ __('app.hide_sidebar') }}">
                <i class="bi bi-x-lg fs-6"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            @if(!empty($menu))
                @foreach($menu as $section => $items)
                    <div class="nav-section">{{ $section }}</div>
                    @foreach($items as $item)
                        @php $isActive = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ route($item['route']) }}" class="nav-item-link {{ $isActive ? 'active' : '' }}" title="{{ $item['label'] }}" data-label="{{ $item['label'] }}">
                            <i class="bi bi-{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            @else
                <div class="p-3 text-muted small">{{ __('app.no_records') }}</div>
            @endif
        </nav>

        <div class="sidebar-footer d-flex align-items-center justify-content-between">
            <span class="text-truncate">{{ __('app.financial_year') }}: <strong>{{ \App\Services\FinancialYear::label() }}</strong></span>
            <span class="badge bg-light text-dark border">{{ __('app.version') }} 1.1</span>
        </div>
    </aside>

    <div class="app-main">
        {{-- Top Application Header --}}
        <header class="app-header">
            @if(!request()->routeIs('dashboard'))
                <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('dashboard') }}'" class="btn btn-light btn-icon d-lg-none me-1" aria-label="{{ __('app.back') }}">
                    <i class="bi bi-chevron-left fs-5"></i>
                </button>
            @endif

            <button type="button" class="btn btn-light btn-icon me-2 {{ !request()->routeIs('dashboard') ? 'd-none d-lg-inline-flex' : '' }}" data-sidebar-toggle aria-label="{{ __('app.toggle_sidebar') }}" title="{{ __('app.toggle_sidebar') }} (Ctrl+B)">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div class="d-flex align-items-center gap-1.5 overflow-hidden flex-grow-1" style="min-width: 0;">
                <h1 class="h6 mb-0 text-truncate" style="min-width: 0;">@yield('title', __('app.dashboard'))</h1>
            </div>

            <div class="header-actions flex-shrink-0 d-flex align-items-center flex-nowrap gap-2">
                {{-- Language Switcher Component --}}
                @include('components.language-switcher')

                {{-- Desktop Quick Shortcuts based on permissions --}}
                <div class="header-quick-actions d-none d-md-flex align-items-center flex-nowrap gap-2 flex-shrink-0">
                    @can('purchases.create')
                        @if(\Illuminate\Support\Facades\Route::has('purchases.create'))
                            <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary text-nowrap d-inline-flex align-items-center gap-1" style="white-space: nowrap !important; flex-shrink: 0;">
                                <i class="bi bi-plus-circle"></i> <span style="white-space: nowrap !important;">{{ __('procurement.purchases.add_new') }}</span>
                            </a>
                        @endif
                    @endcan

                    @can('sales.create')
                        @if(\Illuminate\Support\Facades\Route::has('sales-invoices.create'))
                            <a href="{{ route('sales-invoices.create') }}" class="btn btn-sm btn-outline-primary text-nowrap d-inline-flex align-items-center gap-1" style="white-space: nowrap !important; flex-shrink: 0;">
                                <i class="bi bi-receipt"></i> <span style="white-space: nowrap !important;">{{ __('sales.invoices.add_new') }}</span>
                            </a>
                        @endif
                    @endcan
                </div>

                {{-- User Profile Dropdown --}}
                <div class="dropdown">
                    <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Menu">
                        <div class="avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="meta text-start d-none d-md-block">
                            <div class="fw-semibold small text-truncate" style="max-width: 140px;">{{ auth()->user()->name }}</div>
                            <small class="text-muted d-block">{{ auth()->user()->role?->display_name ?? 'User' }}</small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-2 border-bottom d-md-none">
                            <div class="fw-bold">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->role?->display_name }}</small>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('password.change') }}">
                                <i class="bi bi-key text-muted"></i> {{ __('app.change_password') }}
                            </a>
                        </li>
                        @can('settings.manage')
                            <li>
                                <a class="dropdown-item" href="{{ route('settings.edit') }}">
                                    <i class="bi bi-gear text-muted"></i> {{ __('app.business_settings') }}
                                </a>
                            </li>
                        @endcan
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right"></i> {{ __('app.sign_out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Discreet PWA Installation Banner --}}
        <div id="pwaInstallBanner" class="pwa-install-banner alert alert-light border shadow-sm d-none">
            <div class="d-flex align-items-center gap-3 w-100 flex-wrap flex-sm-nowrap">
                <div class="brand-mark flex-shrink-0" style="width: 38px; height: 38px;">
                    <i class="bi bi-phone"></i>
                </div>
                <div class="flex-grow-1">
                    <strong class="d-block text-dark">{{ __('app.install_app') }}</strong>
                    <small class="text-muted">{{ __('app.install_app_desc') }}</small>
                </div>
                <div class="d-flex gap-2 flex-shrink-0 mt-2 mt-sm-0">
                    <button id="pwaInstallBtn" type="button" class="btn btn-sm btn-primary">
                        <i class="bi bi-download"></i> {{ __('app.install') }}
                    </button>
                    <button id="pwaDismissBtn" type="button" class="btn btn-sm btn-light border">
                        {{ __('app.dismiss') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Live Network Status Notification --}}
        <div id="networkStatusToast" class="network-toast alert alert-danger d-none" role="alert">
            <i class="bi bi-wifi-off fs-5"></i>
            <span id="networkStatusMsg">{{ __('app.offline_notice') }}</span>
        </div>

        <main class="app-content">
            {{-- Global Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div class="flex-grow-1">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div class="flex-grow-1">{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-x-circle-fill"></i>
                    <div class="flex-grow-1">{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <div class="flex-grow-1">
                        <strong>{{ __('app.check_form_errors') }}</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('app.close') }}"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="app-footer d-flex flex-wrap justify-content-between align-items-center">
            <div>
                &copy; {{ date('Y') }} <strong>{{ setting('business.name', 'Kisan Enterprises') }}</strong>. {{ __('app.all_rights_reserved') }}
            </div>
            <div class="text-muted small">
                {{ __('app.timezone') }}: {{ config('app.timezone') }} &bull; {{ __('app.currency') }}: {{ setting('locale.currency_symbol', '₹') }}
            </div>
        </footer>
    </div>

    {{-- Mobile Bottom Navigation Bar (Screens < 768px) --}}
    <nav class="mobile-bottom-nav d-md-none" aria-label="Mobile Navigation">
        {{-- 1. Home --}}
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>{{ __('menu.mobile.home') }}</span>
        </a>

        {{-- 2. Purchase --}}
        @canany(['purchases.view', 'purchases.create'])
            <a href="{{ route('purchases.index') }}" class="bottom-nav-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i class="bi bi-basket-fill"></i>
                <span>{{ __('menu.mobile.purchases') }}</span>
            </a>
        @endcanany

        {{-- 3. Sales --}}
        @canany(['sales.view', 'sales.create'])
            <a href="{{ route('sales-invoices.index') }}" class="bottom-nav-item {{ request()->routeIs(['sales-invoices.*', 'sales-orders.*']) ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>{{ __('menu.mobile.sales') }}</span>
            </a>
        @endcanany

        {{-- 4. Stock --}}
        @can('inventory.view')
            <a href="{{ route('inventory.index') }}" class="bottom-nav-item {{ request()->routeIs(['inventory.*', 'stock-*']) ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i>
                <span>{{ __('menu.mobile.stock') }}</span>
            </a>
        @endcan

        {{-- 5. More --}}
        <button type="button" class="bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#moreModulesOffcanvas" aria-controls="moreModulesOffcanvas">
            <i class="bi bi-grid-fill"></i>
            <span>{{ __('menu.mobile.more') }}</span>
        </button>
    </nav>

    {{-- Offcanvas Drawer for "More" Menu on Mobile --}}
    <div class="offcanvas offcanvas-bottom offcanvas-more h-auto" tabindex="-1" id="moreModulesOffcanvas" aria-labelledby="moreModulesLabel">
        <div class="offcanvas-header border-bottom py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar" style="width: 38px; height: 38px;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h6 class="offcanvas-title fw-bold mb-0" id="moreModulesLabel">{{ auth()->user()->name }}</h6>
                    <small class="text-muted">{{ auth()->user()->role?->display_name }}</small>
                </div>
            </div>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('app.close') }}"></button>
        </div>
        <div class="offcanvas-body p-3" style="max-height: 70vh; overflow-y: auto;">
            <div class="mb-3 d-flex align-items-center justify-content-between p-2 bg-light rounded">
                <span class="fw-semibold text-muted small"><i class="bi bi-translate me-1"></i> {{ __('app.language') }}</span>
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('locale.switch', 'hi') }}" class="btn {{ app()->getLocale() === 'hi' ? 'btn-success' : 'btn-outline-secondary' }}">हिंदी</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="btn {{ app()->getLocale() === 'en' ? 'btn-success' : 'btn-outline-secondary' }}">English</a>
                </div>
            </div>

            @if(!empty($menu))
                @foreach($menu as $section => $items)
                    <div class="text-uppercase text-muted fw-bold small px-2 pt-2 pb-1" style="font-size: .75rem; letter-spacing: .05em;">
                        {{ $section }}
                    </div>
                    <div class="list-group list-group-flush mb-2">
                        @foreach($items as $item)
                            <a href="{{ route($item['route']) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 border-0 rounded {{ request()->routeIs($item['pattern']) ? 'active bg-light text-success fw-bold' : '' }}">
                                <i class="bi bi-{{ $item['icon'] }} fs-5 text-muted"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            @endif

            <div class="border-top pt-3 mt-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-right"></i> {{ __('app.sign_out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Universal Confirmation Modal --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">{{ __('app.confirm_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-secondary">{{ __('app.confirm_question') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="button" class="btn btn-primary" data-confirm-ok>{{ __('app.confirm') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.kisanI18nOffline = "{{ __('app.offline_notice') }}";
        window.kisanI18nOnline = "{{ app()->getLocale() === 'hi' ? 'इंटरनेट कनेक्ट हो गया!' : 'Back online!' }}";
    </script>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
