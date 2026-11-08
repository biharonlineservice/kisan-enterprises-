<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Agricultural Crop Trading & Bulk Grain Supply') — {{ setting('website.brand_name', 'KisanHub') }}</title>
    <meta name="description" content="@yield('meta_description', 'KisanHub is a premier agricultural trading business purchasing crops directly from farmers and supplying high-quality grains in bulk to rice mills, wholesalers, and agricultural markets.')">

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#196F3D">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        html, body.public-site {
            overflow-x: hidden !important;
            max-width: 100vw;
            width: 100%;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        @media (max-width: 575.98px) {
            .public-site .container {
                width: 100% !important;
                max-width: 100% !important;
                padding-left: 12px !important;
                padding-right: 12px !important;
                box-sizing: border-box !important;
            }
            .public-site .row {
                margin-left: -6px !important;
                margin-right: -6px !important;
            }
            .public-site .row > [class*="col-"] {
                padding-left: 6px !important;
                padding-right: 6px !important;
            }
        }
        @media (min-width: 992px) {
            .public-site .container {
                max-width: 960px;
            }
        }
        @media (min-width: 1200px) {
            .public-site .container {
                max-width: 1140px;
            }
        }
        @media (min-width: 1400px) {
            .public-site .container {
                max-width: 1240px;
            }
        }
        .top-utility-bar {
            background: linear-gradient(90deg, #0d3820 0%, #165532 100%);
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            line-height: 1.5;
        }
        .live-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #34d399;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
            animation: ke-pulse 1.8s infinite;
        }
        @keyframes ke-pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.65); }
            70% { box-shadow: 0 0 0 7px rgba(52, 211, 153, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
        }
        .site-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(227, 233, 228, 0.9);
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 4px 18px rgba(21, 91, 54, 0.05);
            padding-top: env(safe-area-inset-top, 0px);
            width: 100%;
            max-width: 100vw;
            overflow-x: clip;
        }
        .site-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            background: linear-gradient(135deg, #249253 0%, #145732 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(33, 134, 75, 0.25);
            flex-shrink: 0;
        }
        .site-header-logo {
            height: 48px;
            max-height: 50px;
            width: auto;
            max-width: 220px;
            object-fit: contain;
        }
        @media (max-width: 575.98px) {
            .site-header-logo {
                height: 38px;
                max-height: 40px;
                max-width: 170px;
            }
        }
        .public-nav-offcanvas {
            z-index: 1065 !important;
            border-left: 1px solid var(--ke-border);
            box-shadow: -4px 0 24px rgba(0,0,0,0.12);
        }
        .public-nav-offcanvas .offcanvas-backdrop {
            z-index: 1060 !important;
        }
        .public-drawer-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.85rem 1.25rem;
            color: #2D3748;
            text-decoration: none;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.15s ease;
        }
        .public-drawer-link:hover, .public-drawer-link.active {
            background: rgba(33, 134, 75, 0.08);
            color: var(--ke-primary);
            border-left-color: var(--ke-primary);
        }
        .public-drawer-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            color: var(--ke-primary);
        }
        .site-brand {
            min-width: 0;
            flex: 1 1 auto;
            max-width: calc(100% - 110px);
            overflow: hidden;
        }
        @media (max-width: 575.98px) {
            .site-brand {
                max-width: calc(100% - 105px);
            }
            .site-brand-logo {
                width: 34px;
                height: 34px;
            }
            .site-brand .site-brand-title {
                font-size: 0.98rem !important;
            }
            .site-brand small {
                font-size: 0.66rem !important;
            }
        }
        @media (min-width: 992px) {
            .site-brand {
                max-width: none !important;
                flex: 0 0 auto !important;
            }
        }
        .site-nav .nav-link {
            color: #2c3e33;
            font-weight: 500;
            font-size: 0.88rem;
            padding: 0.4rem 0.6rem;
            border-radius: 999px;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        @media (min-width: 1200px) {
            .site-nav .nav-link {
                font-size: 0.91rem;
                padding: 0.45rem 0.72rem;
            }
        }
        .site-nav .nav-link:hover {
            color: var(--ke-primary);
            background: rgba(33, 134, 75, 0.08);
        }
        .site-nav .nav-link.active {
            color: var(--ke-primary-dark);
            background: rgba(33, 134, 75, 0.13);
            font-weight: 600;
        }
        .hero-section {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 8% 18%, rgba(33, 134, 75, 0.11) 0%, transparent 42%),
                radial-gradient(circle at 92% 12%, rgba(244, 183, 64, 0.10) 0%, transparent 36%),
                linear-gradient(180deg, #eff7f2 0%, #f9fbf9 65%, #ffffff 100%);
            padding: 1.15rem 0 2.25rem 0;
            border-bottom: 1px solid var(--ke-border);
        }
        @media (max-width: 767.98px) {
            .hero-section {
                padding: 0.85rem 0 1.75rem 0;
            }
        }
        .feature-card {
            background: #ffffff;
            border: 1px solid var(--ke-border);
            border-radius: 16px;
            padding: 1.5rem;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
            height: 100%;
            box-shadow: 0 2px 10px rgba(21, 91, 54, 0.03);
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 26px rgba(21, 91, 54, 0.09);
            border-color: rgba(33, 134, 75, 0.45);
        }
        .crop-card {
            background: #ffffff;
            border: 1px solid var(--ke-border);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(21, 91, 54, 0.03);
        }
        .crop-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 26px rgba(21, 91, 54, 0.09);
            border-color: rgba(33, 134, 75, 0.45);
        }
        .site-footer {
            background: linear-gradient(180deg, #134e2e 0%, #0b331d 100%);
            color: rgba(255, 255, 255, 0.85);
            padding: 3.75rem 0 2rem 0;
            margin-top: 0;
        }
        .site-footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .site-footer a:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        .site-footer h6 {
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.82rem;
            letter-spacing: 0.6px;
            margin-bottom: 1.15rem;
        }
        .site-footer hr {
            border-color: rgba(255, 255, 255, 0.14);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light public-site">
    @php
        $layoutHindi = app()->getLocale() === 'hi';
        $bannerNotice = \App\Models\WebsiteContent::get(
            'procurement_banner',
            $layoutHindi
                ? 'खरीफ एवं रबी सीजन की फसल खरीद चालू है — प्रमाणित डिजिटल धर्मकांटा सक्रिय'
                : 'Kharif & Rabi Crop Procurement Open — Certified Digital Weighbridge Active'
        );
    @endphp

    {{-- Top Utility & Mandi Ticker Bar (Desktop) --}}
    <div class="top-utility-bar py-1 d-none d-lg-block">
        <div class="container d-flex justify-content-between align-items-center py-1">
            <div class="d-flex align-items-center gap-3 overflow-hidden text-nowrap" style="max-width: 48%;">
                <span class="text-truncate d-inline-block" title="{{ setting('business.address', 'Agricultural Trading Hub, Uttar Pradesh') }}">
                    <i class="bi bi-geo-alt-fill text-warning me-1 flex-shrink-0"></i>
                    {{ setting('business.address', 'Agricultural Trading Hub, Uttar Pradesh') }}
                </span>
                @if(setting('business.phone'))
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('business.phone')) }}" class="text-white text-decoration-none text-nowrap flex-shrink-0">
                        <i class="bi bi-telephone-fill text-success me-1"></i> {{ setting('business.phone') }}
                    </a>
                @endif
            </div>
            <div class="d-flex align-items-center justify-content-end gap-3 overflow-hidden text-nowrap" style="max-width: 50%;">
                <span class="d-inline-flex align-items-center gap-2 text-white-50 overflow-hidden text-truncate" title="{{ $bannerNotice }}">
                    <span class="live-pulse-dot flex-shrink-0"></span>
                    <span class="text-white text-truncate">{{ $bannerNotice }}</span>
                </span>
                <span class="text-white-50 flex-shrink-0">|</span>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-warning fw-semibold text-decoration-none text-nowrap flex-shrink-0">
                        <i class="bi bi-speedometer2 me-1"></i>{{ __('website.nav.portal') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-warning fw-semibold text-decoration-none text-nowrap flex-shrink-0">
                        <i class="bi bi-person-lock me-1"></i>{{ __('website.nav.login') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Compact Mobile Live Procurement Strip (< 992px) --}}
    <div class="top-utility-bar py-1 px-2 d-lg-none">
        <div class="container d-flex justify-content-between align-items-center gap-2 overflow-hidden">
            <span class="d-inline-flex align-items-center gap-2 min-w-0 flex-grow-1" style="font-size: 0.73rem;">
                <span class="live-pulse-dot flex-shrink-0"></span>
                <span class="text-truncate">{{ $bannerNotice }}</span>
            </span>
            @if(setting('business.phone'))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('business.phone')) }}" class="badge bg-success text-white text-decoration-none flex-shrink-0">
                    <i class="bi bi-telephone-fill me-1"></i>{{ $layoutHindi ? 'कॉल' : 'Call' }}
                </a>
            @endif
        </div>
    </div>

    {{-- Navigation Bar --}}
    <header class="site-header">
        <div class="container d-flex align-items-center justify-content-between flex-nowrap py-2" style="min-height: 60px;">
            {{-- Brand Link --}}
            <a class="navbar-brand site-brand d-flex align-items-center gap-2 m-0 text-decoration-none flex-shrink-0" href="{{ route('home') }}">
                @php
                    $siteLogo = setting('business.logo');
                    $logoUrl = null;
                    if ($siteLogo && file_exists(public_path('uploads/' . $siteLogo))) {
                        $logoUrl = asset('uploads/' . $siteLogo);
                    } elseif (file_exists(public_path('uploads/branding/kisanhub-logo.png'))) {
                        $logoUrl = asset('uploads/branding/kisanhub-logo.png');
                    } elseif (file_exists(public_path('images/kisanhub-logo.png'))) {
                        $logoUrl = asset('images/kisanhub-logo.png');
                    }
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ setting('website.brand_name', 'KisanHub') }}" class="site-header-logo">
                @else
                    <div class="site-brand-logo overflow-hidden p-0">
                        <img src="{{ asset('images/kisanhub-emblem.png') }}" alt="{{ setting('website.brand_name', 'KisanHub') }}" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div class="min-w-0">
                        <div class="site-brand-title fw-bold text-dark lh-1 text-truncate" style="font-size: 1.14rem; letter-spacing: -0.2px;">{{ setting('website.brand_name', 'KisanHub') }}</div>
                        <small class="text-muted d-block text-truncate mt-1" style="font-size: 0.71rem;" title="{{ setting('business.tagline', $layoutHindi ? 'प्रत्यक्ष फसल खरीद एवं थोक अनाज व्यापार' : 'Agricultural Crop Trading & Warehousing') }}">
                            {{ setting('business.tagline', $layoutHindi ? 'प्रत्यक्ष फसल खरीद एवं थोक अनाज व्यापार' : 'Agricultural Crop Trading & Warehousing') }}
                        </small>
                    </div>
                @endif
            </a>

            {{-- Desktop Horizontal Navigation (>= 992px) --}}
            <nav class="d-none d-lg-flex align-items-center ms-auto">
                <ul class="navbar-nav d-flex flex-row align-items-center gap-1 mb-0 site-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('website.nav.home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('website.about') ? 'active' : '' }}" href="{{ route('website.about') }}">{{ $layoutHindi ? 'हमारे बारे में' : 'About Us' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('website.procurement') ? 'active' : '' }}" href="{{ route('website.procurement') }}">{{ __('website.nav.procurement') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('website.products') ? 'active' : '' }}" href="{{ route('website.products') }}">{{ __('website.nav.products') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}" href="{{ route('website.contact') }}">{{ __('website.nav.contact') }}</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" href="{{ route('website.farmer.register') }}">
                            <i class="bi bi-person-plus-fill"></i> {{ __('website.nav.farmer_register') }}
                        </a>
                    </li>
                    <li class="nav-item ms-1">
                        @include('components.language-switcher')
                    </li>
                </ul>
            </nav>

            {{-- Mobile Actions Bar (< 992px) --}}
            <div class="d-flex align-items-center gap-1.5 d-lg-none flex-shrink-0 ms-auto">
                @include('components.language-switcher')
                <button class="btn btn-light border btn-sm d-flex align-items-center justify-content-center p-0 rounded-3 shadow-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#publicNavOffcanvas"
                        aria-controls="publicNavOffcanvas"
                        aria-label="Toggle navigation"
                        style="width: 38px; height: 38px;">
                    <i class="bi bi-list fs-3 text-dark"></i>
                </button>
            </div>
        </div>
    </header>

    {{-- Modern Public Mobile Navigation Offcanvas Drawer (< 992px) --}}
    <div class="offcanvas offcanvas-end public-nav-offcanvas d-lg-none" tabindex="-1" id="publicNavOffcanvas" aria-labelledby="publicNavOffcanvasLabel" style="width: 310px; max-width: 86vw;">
        <div class="offcanvas-header border-bottom bg-light py-3">
            <div class="d-flex align-items-center gap-2" id="publicNavOffcanvasLabel">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ setting('website.brand_name', 'KisanHub') }}" style="height: 38px; max-width: 190px; width: auto; object-fit: contain;">
                @else
                    <div class="site-brand-logo overflow-hidden p-0" style="width: 34px; height: 34px; border-radius: 9px;">
                        <img src="{{ asset('images/kisanhub-emblem.png') }}" alt="{{ setting('website.brand_name', 'KisanHub') }}" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div>
                        <div class="fw-bold text-dark lh-1" style="font-size: 1rem;">{{ setting('website.brand_name', 'KisanHub') }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ $layoutHindi ? 'कृषि व्यापार एवं फसल खरीद' : 'Crop Trading & Bulk Supply' }}</small>
                    </div>
                @endif
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body d-flex flex-column p-0">
            {{-- Language Selector Pills in Drawer --}}
            <div class="p-3 bg-light border-bottom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-translate me-1 text-success"></i> {{ $layoutHindi ? 'भाषा चुनें (Select Language)' : 'Select Language (भाषा)' }}
                    </span>
                </div>
                <div class="btn-group w-100" role="group">
                    <a href="{{ route('locale.switch', 'hi') }}" class="btn btn-sm {{ $layoutHindi ? 'btn-success text-white fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        हिंदी (Hindi)
                    </a>
                    <a href="{{ route('locale.switch', 'en') }}" class="btn btn-sm {{ !$layoutHindi ? 'btn-success text-white fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        English
                    </a>
                </div>
            </div>

            {{-- Main Navigation Links --}}
            <div class="flex-grow-1 overflow-auto py-2">
                <div class="nav flex-column">
                    <a href="{{ route('home') }}" class="public-drawer-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="bi bi-house-door-fill"></i>
                        <span>{{ __('website.nav.home') }}</span>
                    </a>
                    <a href="{{ route('home') }}#price-trend" class="public-drawer-link">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>{{ $layoutHindi ? 'आज का मंडी भाव व ग्राफ' : 'Live Crop Rates & Graph' }}</span>
                    </a>
                    <a href="{{ route('website.about') }}" class="public-drawer-link {{ request()->routeIs('website.about') ? 'active' : '' }}">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>{{ $layoutHindi ? 'हमारे बारे में' : 'About Us' }}</span>
                    </a>
                    <a href="{{ route('website.procurement') }}" class="public-drawer-link {{ request()->routeIs('website.procurement') ? 'active' : '' }}">
                        <i class="bi bi-tree-fill"></i>
                        <span>{{ __('website.nav.procurement') }}</span>
                    </a>
                    <a href="{{ route('website.products') }}" class="public-drawer-link {{ request()->routeIs('website.products') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill"></i>
                        <span>{{ __('website.nav.products') }}</span>
                    </a>
                    <a href="{{ route('website.farmer.register') }}" class="public-drawer-link {{ request()->routeIs('website.farmer.register') ? 'active' : '' }}">
                        <i class="bi bi-person-plus-fill text-success"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-success">{{ __('website.nav.farmer_register') }}</span>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $layoutHindi ? 'नया किसान पंजीकरण' : 'New Farmer Registration' }}</small>
                        </div>
                    </a>
                    <a href="{{ route('website.contact') }}" class="public-drawer-link {{ request()->routeIs('website.contact') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>{{ __('website.nav.contact') }}</span>
                    </a>
                    <hr class="my-2 mx-3 text-muted">
                    <a href="{{ route('website.terms') }}" class="public-drawer-link py-2 small text-muted">
                        <i class="bi bi-file-text"></i>
                        <span>{{ __('website.footer.terms') }}</span>
                    </a>
                    <a href="{{ route('website.privacy') }}" class="public-drawer-link py-2 small text-muted">
                        <i class="bi bi-shield-check"></i>
                        <span>{{ __('website.footer.privacy') }}</span>
                    </a>
                </div>
            </div>

            {{-- Quick Actions in Drawer Footer --}}
            <div class="p-3 border-top bg-light mt-auto">
                @if(setting('business.phone'))
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('business.phone')) }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center gap-2 mb-2 py-2">
                        <i class="bi bi-telephone-fill"></i>
                        <span class="fw-semibold">{{ $layoutHindi ? 'सीधे कॉल करें: ' : 'Call Office: ' }}{{ setting('business.phone') }}</span>
                    </a>
                @endif

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 shadow-sm">
                        <i class="bi bi-speedometer2"></i>
                        <span class="fw-semibold">{{ __('website.nav.portal') }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-dark w-100 d-flex align-items-center justify-content-center gap-2 py-2 shadow-sm">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span class="fw-semibold">{{ __('website.nav.login') }} / Staff ERP</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success') || session('error'))
        <div class="container mt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    @endif

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Public Footer --}}
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        @if($logoUrl)
                            <div class="bg-white rounded-3 px-2 py-1 shadow-sm d-inline-block">
                                <img src="{{ $logoUrl }}" alt="{{ setting('website.brand_name', 'KisanHub') }}" style="height: 38px; width: auto; max-width: 190px; object-fit: contain;">
                            </div>
                        @else
                            <div class="bg-white rounded-3 p-1 d-flex align-items-center justify-content-center overflow-hidden" style="width: 34px; height: 34px;">
                                <img src="{{ asset('images/kisanhub-emblem.png') }}" alt="{{ setting('website.brand_name', 'KisanHub') }}" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                            <h5 class="text-white mb-0 fw-bold">{{ setting('website.brand_name', 'KisanHub') }}</h5>
                        @endif
                    </div>
                    <p class="small text-white-50 mb-3">
                        {{ __('website.footer.about') }}
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-white bg-opacity-10 text-white fw-normal border border-light border-opacity-25">
                            <i class="bi bi-patch-check-fill text-warning me-1"></i>{{ $layoutHindi ? 'प्रमाणित धर्मकांटा' : 'Certified Weighbridge' }}
                        </span>
                        <span class="badge bg-white bg-opacity-10 text-white fw-normal border border-light border-opacity-25">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ $layoutHindi ? 'तुरंत भुगतान' : 'Same-Day Payout' }}
                        </span>
                    </div>
                    <div class="small text-white-50">
                        @if(setting('business.gstin'))
                            <div>GSTIN: <span class="text-white">{{ setting('business.gstin') }}</span></div>
                        @endif
                        @if(setting('business.pan'))
                            <div>PAN: <span class="text-white">{{ setting('business.pan') }}</span></div>
                        @endif
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <h6>{{ __('website.footer.quick_links') }}</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><a href="{{ route('home') }}">{{ __('website.nav.home') }}</a></li>
                        <li class="mb-2"><a href="{{ route('website.about') }}">{{ $layoutHindi ? 'हमारे बारे में' : 'About Us' }}</a></li>
                        <li class="mb-2"><a href="{{ route('website.procurement') }}">{{ __('website.nav.procurement') }}</a></li>
                        <li class="mb-2"><a href="{{ route('website.products') }}">{{ __('website.nav.products') }}</a></li>
                        <li class="mb-2"><a href="{{ route('website.farmer.register') }}">{{ __('website.nav.farmer_register') }}</a></li>
                        <li><a href="{{ route('website.contact') }}">{{ __('website.nav.contact') }}</a></li>
                    </ul>
                </div>

                <div class="col-6 col-md-3">
                    <h6>{{ $layoutHindi ? 'व्यापारिक सुविधाएं' : 'Trading Capabilities' }}</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><i class="bi bi-check2-circle text-warning me-1"></i> {{ $layoutHindi ? 'किसानों से सीधी फसल खरीद' : 'Direct Farmer Procurement' }}</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-warning me-1"></i> {{ $layoutHindi ? 'कंप्यूटर धर्मकांटा तौल' : 'Digital Computerized Weighbridge' }}</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-warning me-1"></i> {{ $layoutHindi ? 'धान, गेहूं, मक्का व सरसों आपूर्ति' : 'Paddy, Wheat & Maize Bulk Supply' }}</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-warning me-1"></i> {{ $layoutHindi ? 'नमी व गुणवत्ता परीक्षण' : 'Moisture & Grade Verification' }}</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-warning me-1"></i> {{ $layoutHindi ? 'सुरक्षित आधुनिक गोदाम' : 'Modern Storage Godowns' }}</li>
                        <li><i class="bi bi-check2-circle text-warning me-1"></i> {{ $layoutHindi ? 'समय पर ट्रांसपोर्ट डिस्पैच' : 'Timely Commercial Fleet Dispatch' }}</li>
                    </ul>
                </div>

                <div class="col-12 col-md-3">
                    <h6>{{ __('website.footer.contact_info') }}</h6>
                    <div class="small text-white-50 mb-2">
                        <i class="bi bi-geo-alt me-1 text-white"></i>
                        {{ setting('business.address', 'Agricultural Trading Hub, Uttar Pradesh') }}
                    </div>
                    @if(setting('business.phone'))
                        <div class="small text-white-50 mb-2">
                            <i class="bi bi-telephone me-1 text-white"></i>
                            {{ setting('business.phone') }}
                        </div>
                    @endif
                    @if(setting('business.email'))
                        <div class="small text-white-50 mb-3">
                            <i class="bi bi-envelope me-1 text-white"></i>
                            {{ setting('business.email') }}
                        </div>
                    @endif
                    <a href="{{ route('website.contact') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 mt-1">
                        <i class="bi bi-chat-dots me-1"></i> {{ $layoutHindi ? 'व्यापारिक पूछताछ भेजें' : 'Send Trade Inquiry' }}
                    </a>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-white-50">
                <div>
                    &copy; {{ date('Y') }} {{ setting('website.brand_name', 'KisanHub') }}. All rights reserved.
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ route('website.privacy') }}">{{ __('website.footer.privacy') }}</a>
                    <a href="{{ route('website.terms') }}">{{ __('website.footer.terms') }}</a>
                    <a href="{{ route('login') }}">{{ __('website.nav.portal') }}</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Public Mobile Bottom App Bar (< 768px) --}}
    <nav class="public-mobile-bottom-nav d-md-none" aria-label="Mobile Navigation">
        <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}" title="{{ __('website.nav.home') }}" aria-label="{{ __('website.nav.home') }}">
            <i class="bi bi-house-door-fill"></i>
            <span class="bottom-nav-label">{{ $layoutHindi ? 'होम' : 'Home' }}</span>
        </a>
        <a href="{{ request()->routeIs('home') ? '#price-trend' : route('home') . '#price-trend' }}" class="bottom-nav-item" title="{{ $layoutHindi ? 'मंडी भाव' : 'Live Rates' }}" aria-label="{{ $layoutHindi ? 'मंडी भाव' : 'Live Rates' }}">
            <i class="bi bi-graph-up-arrow"></i>
            <span class="bottom-nav-label">{{ $layoutHindi ? 'लाइव भाव' : 'Rates' }}</span>
        </a>
        <a href="{{ route('website.procurement') }}" class="bottom-nav-item {{ request()->routeIs('website.procurement') ? 'active' : '' }}" title="{{ __('website.nav.procurement') }}" aria-label="{{ __('website.nav.procurement') }}">
            <i class="bi bi-tree-fill"></i>
            <span class="bottom-nav-label">{{ $layoutHindi ? 'फसल बेचें' : 'Sell Crop' }}</span>
        </a>
        <a href="{{ route('website.products') }}" class="bottom-nav-item {{ request()->routeIs('website.products') ? 'active' : '' }}" title="{{ __('website.nav.products') }}" aria-label="{{ __('website.nav.products') }}">
            <i class="bi bi-box-seam-fill"></i>
            <span class="bottom-nav-label">{{ $layoutHindi ? 'थोक सप्लाई' : 'Supply' }}</span>
        </a>
        <a href="{{ route('website.farmer.register') }}" class="bottom-nav-item {{ request()->routeIs('website.farmer.register') ? 'active' : '' }}" title="{{ __('website.nav.farmer_register') }}" aria-label="{{ __('website.nav.farmer_register') }}">
            <i class="bi bi-person-plus-fill"></i>
            <span class="bottom-nav-label">{{ $layoutHindi ? 'पंजीकरण' : 'Register' }}</span>
        </a>
    </nav>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {});
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
