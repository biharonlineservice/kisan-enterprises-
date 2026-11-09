@extends('layouts.website')

@php
    $isHindi = app()->getLocale() === 'hi';
    $seoBrand = setting('website.brand_name', 'KisanHub');
    $customSeoTitle = \App\Models\WebsiteContent::get('seo_meta_title');
    $customSeoDesc = \App\Models\WebsiteContent::get('seo_meta_description');
@endphp

@section('title', $customSeoTitle ?: ($isHindi ? 'किसानों से सीधी फसल खरीद, आज के लाइव मंडी भाव एवं थोक अनाज आपूर्ति — ' . $seoBrand : 'Direct Farmer Crop Procurement, Live Mandi Rates & Bulk Grain Supply — ' . $seoBrand))
@section('meta_description', $customSeoDesc ?: ($isHindi
    ? $seoBrand . ' पर आज के ताजा धान, गेहूं, मक्का और सरसों के मंडी खरीद भाव देखें। प्रमाणित इलेक्ट्रॉनिक धर्मकांटा तौल, पारदर्शी नमी जांच और उसी दिन बैंक/नकद भुगतान।'
    : 'Check today\'s live mandi purchase prices for Paddy, Wheat, Maize, and Mustard at ' . $seoBrand . '. Certified digital weighbridge, transparent moisture testing, same-day farmer payment, and bulk grain supply for rice & flour mills.'))
@section('meta_keywords', \App\Models\WebsiteContent::get('seo_meta_keywords', 'today mandi rate, paddy rate today, wheat price per quintal, maize mandi bhav, mustard भाव, direct farmer crop procurement, bulk grain supplier for rice mills, ' . $seoBrand))

@push('structured_data')
@if($rateCrops->isNotEmpty())
@php
    $cropOffersSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'OfferCatalog',
        'name' => $seoBrand . ' — Today\'s Live Crop Purchase Prices (Mandi Rates)',
        'url' => route('home'),
        'itemListElement' => $rateCrops->values()->map(function ($crop, $idx) {
            return [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'item' => [
                    '@type' => 'Product',
                    'name' => $crop->name . ' (' . $crop->code . ')',
                    'description' => $crop->rate_note ?: 'Standard Fair Average Quality (FAQ) agricultural grain procurement',
                    'sku' => $crop->code,
                    'offers' => [
                        '@type' => 'Offer',
                        'priceCurrency' => 'INR',
                        'price' => number_format((float) $crop->current_purchase_rate, 2, '.', ''),
                        'availability' => 'https://schema.org/InStock',
                        'priceValidUntil' => now()->addDays(7)->toDateString(),
                    ],
                ],
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($cropOffersSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
@endpush

@section('content')
@php
    $isHindi = app()->getLocale() === 'hi';
    $fmtRate = function ($v) {
        $v = (float) $v;
        return setting('locale.currency_symbol', '₹') . indian_number($v, fmod($v, 1) ? 2 : 0);
    };
    $customHeadline = \App\Models\WebsiteContent::get('hero_headline');
    $customSubtitle = \App\Models\WebsiteContent::get('hero_subtitle', \App\Models\WebsiteContent::get('hero_subheadline'));
    $bonusEnabled = (string) setting('procurement.delayed_bonus_enabled', '1') === '1' && (float) setting('procurement.delayed_bonus_rate', '1.00') > 0;
    $bonusRateFormatted = number_format((float) setting('procurement.delayed_bonus_rate', '1.00'), 2);
    $totalCapacityQtl = $warehouses->sum(fn ($w) => (float) ($w->capacity_quintals ?? 0));
@endphp

{{-- Hero Banner --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-start g-4 g-lg-5">
            {{-- Left Column: Headline, CTAs & Trust Strip --}}
            <div class="col-12 col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill hero-eyebrow mb-2">
                    <i class="bi bi-shield-fill-check text-success"></i>
                    <span>{{ __('website.hero.badge') }}</span>
                </div>

                <h1 class="hero-display-title fw-bold text-dark mb-3">
                    {{ $customHeadline ?: __('website.hero.title') }}
                </h1>

                <p class="hero-subtitle text-muted mb-3">
                    {{ $customSubtitle ?: __('website.hero.subtitle') }}
                </p>

                <div class="hero-cta-group mb-3">
                    <a href="{{ route('website.farmer.register') }}" class="btn btn-primary hero-cta-btn shadow-sm">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>{{ __('website.hero.register_btn') }}</span>
                    </a>
                    @if($priceTrends->isNotEmpty())
                        <a href="#price-trend" class="btn btn-outline-primary hero-cta-btn bg-white">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>{{ $isHindi ? 'भाव ग्राफ देखें' : 'Price Trend Graph' }}</span>
                        </a>
                    @endif
                    <a href="{{ route('website.products') }}" class="btn btn-light hero-cta-btn border">
                        <i class="bi bi-building"></i>
                        <span>{{ __('website.hero.products_btn') }}</span>
                    </a>
                </div>

                {{-- Compact Trust Highlights (3-up on mobile & desktop) --}}
                <div class="row g-2 g-sm-3 hero-trust-grid">
                    <div class="col-4">
                        <div class="hero-trust-tile">
                            <div class="hero-trust-icon bg-success-subtle text-success">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div>
                                <div class="hero-trust-val">{{ $isHindi ? '100% सटीक' : '100% Fair' }}</div>
                                <div class="hero-trust-lbl">{{ $isHindi ? 'कंप्यूटर धर्मकांटा' : 'Digital Scale' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="hero-trust-tile">
                            <div class="hero-trust-icon bg-primary-subtle text-primary">
                                <i class="bi bi-bank"></i>
                            </div>
                            <div>
                                <div class="hero-trust-val">{{ $isHindi ? 'तुरंत भुगतान' : 'Same-Day' }}</div>
                                <div class="hero-trust-lbl">{{ $isHindi ? 'NEFT / UPI / नकद' : 'Bank & Cash' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="hero-trust-tile">
                            <div class="hero-trust-icon bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <div>
                                <div class="hero-trust-val">{{ $bonusEnabled ? '+' . $bonusRateFormatted . '%' : ($isHindi ? 'प्रीमियम' : 'Top Rate') }}</div>
                                <div class="hero-trust-lbl">{{ $bonusEnabled ? ($isHindi ? 'देरी भुगतान बोनस' : 'Delayed Bonus') : ($isHindi ? 'ग्रेड बोनस' : 'Grade Value') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live Weighbridge & Trading Desk Operations Bar --}}
                <div class="mt-3 p-3 rounded-4 bg-white border shadow-2xs d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="live-pulse-dot"></span>
                        <span class="small fw-semibold text-dark">
                            {{ $isHindi ? 'धर्मकांटा व खरीद समय:' : 'Weighbridge Open:' }}
                            <span class="text-success fw-bold">8:00 AM – 7:30 PM</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        @if($totalCapacityQtl > 0)
                            <span class="badge bg-light text-dark border fw-medium px-2 py-1" style="font-size: 0.74rem;">
                                <i class="bi bi-layers-fill text-success me-1"></i>{{ indian_number($totalCapacityQtl, 0) }}+ {{ $isHindi ? 'क्विंटल क्षमता' : 'Qtl Storage' }}
                            </span>
                        @endif
                        @if(setting('business.phone'))
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('business.phone')) }}" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none fw-bold px-2 py-1" style="font-size: 0.74rem;">
                                <i class="bi bi-telephone-fill me-1"></i>{{ setting('business.phone') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Live Crop Purchase Price Board --}}
            <div class="col-12 col-lg-5">
                <div class="card hero-price-card border-0 overflow-hidden" id="hero-crop-price-board">
                    <div class="hero-price-header text-white">
                        <div class="d-flex justify-content-between align-items-center gap-2 gap-sm-3">
                            <div class="d-flex align-items-center min-w-0 flex-grow-1">
                                <span class="hero-header-icon flex-shrink-0">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </span>
                                <div class="min-w-0 flex-grow-1 hero-header-text">
                                    <div class="hero-price-title text-truncate">
                                        {{ $isHindi ? 'आज का फसल खरीद भाव' : "Today's Crop Purchase Prices" }}
                                    </div>
                                    <small class="hero-price-sub d-block text-truncate">
                                        {{ $isHindi ? 'किसान भाइयों के लिए ताज़ा मंडी दरें' : 'Live mandi buying rates for farmers' }}
                                    </small>
                                </div>
                            </div>
                            <span class="hero-live-badge">
                                <span class="live-pulse-dot"></span>
                                <span>{{ $isHindi ? 'लाइव भाव' : 'Live Rates' }}</span>
                            </span>
                        </div>
                    </div>

                    @if($rateCrops->isNotEmpty())
                        <div class="card-body p-3 bg-white">
                            @foreach($rateCrops as $rateCrop)
                                @php
                                    $heroTrend = $priceTrends->get($rateCrop->id);
                                @endphp
                                <div class="hero-crop-row d-flex justify-content-between align-items-center gap-2 {{ ! $loop->last ? 'mb-2 pb-2 border-bottom' : '' }}"
                                     data-hero-crop="{{ $rateCrop->id }}"
                                     role="button"
                                     tabindex="0"
                                     title="{{ $isHindi ? 'पिछले भाव का ग्राफ देखने के लिए क्लिक करें' : 'Click to view past price graph' }}">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <div class="crop-avatar flex-shrink-0">
                                            <i class="bi bi-basket2-fill"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.91rem;">{{ $rateCrop->name }}</div>
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">
                                                {{ $rateCrop->rate_note ?: ($rateCrop->varieties->isNotEmpty() ? $rateCrop->varieties->take(2)->pluck('name')->join(', ') : ($isHindi ? 'मानक मंडी ग्रेड' : 'Standard Mandi Grade')) }}
                                            </small>
                                            @if($heroTrend && $heroTrend['previous'] !== null)
                                                <small class="text-muted d-block" style="font-size: 0.70rem;">
                                                    {{ $isHindi ? 'पिछला भाव:' : 'Prev:' }}
                                                    <span class="fw-semibold text-dark">{{ $fmtRate($heroTrend['previous']) }}</span>
                                                </small>
                                            @endif
                                        </div>
                                    </div>

                                    @if($heroTrend && !empty($heroTrend['sparkline']))
                                        <svg class="rate-sparkline flex-shrink-0 ms-auto d-none d-sm-block" viewBox="0 0 100 28" preserveAspectRatio="none" aria-hidden="true">
                                            <polyline points="{{ $heroTrend['sparkline'] }}" fill="none"
                                                      stroke="{{ $heroTrend['direction'] === 'down' ? '#dc3545' : '#21864B' }}"
                                                      stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                                        </svg>
                                    @endif

                                    <div class="text-end flex-shrink-0">
                                        <div class="fw-bold text-success tabular-nums" style="font-size: 0.95rem;">
                                            {{ $rateCrop->formattedPurchaseRate(false) }}
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.70rem;">
                                            {{ $isHindi ? 'प्रति ' . $rateCrop->shortUnitLabel('hi') : 'per ' . $rateCrop->shortUnitLabel('en') }}
                                        </small>
                                        @if($heroTrend)
                                            <div class="mt-1">
                                                @include('website.partials.rate-change', ['trend' => $heroTrend, 'isHindi' => $isHindi])
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if($bonusEnabled)
                                <div class="mt-2 px-3 py-2 rounded-3 bonus-callout small d-flex align-items-center gap-2" style="font-size: 0.76rem;">
                                    <i class="bi bi-gift-fill text-success flex-shrink-0"></i>
                                    <span>
                                        @if($isHindi)
                                            बाद में भुगतान पर <strong>+{{ $bonusRateFormatted }}%</strong> अतिरिक्त बोनस!
                                        @else
                                            Delayed settlement offers <strong>+{{ $bonusRateFormatted }}%</strong> extra bonus!
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-light border-top px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <small class="text-muted" style="font-size: 0.72rem;">
                                <i class="bi bi-patch-check-fill text-success me-1"></i>
                                {{ $isHindi ? 'एडमिन द्वारा अपडेट:' : 'Updated by Admin:' }}
                                <strong class="text-dark">{{ $latestRateUpdate ? fmt_date($latestRateUpdate, true) : fmt_date(now()) }}</strong>
                            </small>
                            <a href="#price-trend" class="small fw-bold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                <i class="bi bi-bar-chart-line-fill"></i>
                                <span>{{ $isHindi ? 'ग्राफ देखें' : 'View Graph' }}</span>
                                <i class="bi bi-arrow-down-short"></i>
                            </a>
                        </div>
                    @else
                        <div class="card-body p-4 bg-white">
                            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-speedometer2 fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ __('website.features.f1_title') }}</h6>
                                    <small class="text-muted">{{ __('website.features.f1_desc') }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-cash-stack fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ __('website.features.f3_title') }}</h6>
                                    <small class="text-muted">{{ __('website.features.f3_desc') }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-truck fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ __('website.features.f4_title') }}</h6>
                                    <small class="text-muted">{{ __('website.features.f4_desc') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Crop Price Trend Graph Section --}}
@if($priceTrends->isNotEmpty())
@php
    $firstTrendId = $priceTrends->keys()->first();
@endphp
<section id="price-trend" class="py-4 py-md-5 bg-white border-bottom">
    <div class="container py-2 py-md-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-3 mb-md-4">
            <div class="min-w-0 flex-grow-1">
                <span class="section-eyebrow">
                    <i class="bi bi-activity me-1"></i>{{ $isHindi ? 'भाव का उतार-चढ़ाव' : 'Live Price Analytics' }}
                </span>
                <h2 class="h3 fw-bold mt-1 mb-1">{{ $isHindi ? 'फसल भाव ग्राफ — पिछले भाव से तुलना करें' : 'Crop Price Graph — Compare with Earlier Prices' }}</h2>
                <p class="text-muted small mb-0">
                    {{ $isHindi ? 'हर बार भाव अपडेट होने पर ग्राफ में अंतर जुड़ता है। हरा = भाव बढ़ा, लाल = भाव घटा।' : 'Updated automatically whenever Administration revises mandi buying prices. Green = up, Red = down.' }}
                </p>
            </div>
            <div class="btn-group btn-group-sm flex-shrink-0 trend-range-btn-group align-self-md-end" role="group" aria-label="Range">
                <button type="button" class="btn btn-outline-success px-3 text-nowrap" data-trend-range="14">{{ $isHindi ? '15 दिन' : '15 Days' }}</button>
                <button type="button" class="btn btn-outline-success px-3 text-nowrap" data-trend-range="30">{{ $isHindi ? '1 महीना' : '1 Month' }}</button>
                <button type="button" class="btn btn-outline-success px-3 active text-nowrap" data-trend-range="90">{{ $isHindi ? '3 महीने' : '3 Months' }}</button>
            </div>
        </div>

        {{-- Touch-scrollable Crop Selector Pills --}}
        <div class="d-flex flex-nowrap flex-md-wrap gap-2 mb-4 overflow-auto pb-1 trend-pills" role="tablist">
            @foreach($priceTrends as $t)
                <button type="button"
                        class="trend-pill {{ $t['id'] === $firstTrendId ? 'active' : '' }}"
                        data-trend-crop="{{ $t['id'] }}">
                    <span class="fw-semibold">{{ $t['name'] }}</span>
                    <span class="trend-pill-price tabular-nums">{{ $fmtRate($t['current']) }}</span>
                    @if($t['direction'] === 'up')
                        <span class="trend-pill-delta up"><i class="bi bi-caret-up-fill"></i>{{ $fmtRate(abs($t['change'])) }}</span>
                    @elseif($t['direction'] === 'down')
                        <span class="trend-pill-delta down"><i class="bi bi-caret-down-fill"></i>{{ $fmtRate(abs($t['change'])) }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        <div class="row g-3 g-lg-4">
            {{-- Chart Canvas Card --}}
            <div class="col-12 col-lg-8">
                <div class="card border rounded-4 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-graph-up"></i>
                                </span>
                                <h3 class="h6 fw-bold mb-0" id="trendChartTitle">{{ $priceTrends[$firstTrendId]['name'] }}</h3>
                            </div>
                            <div class="d-flex gap-3 small text-muted flex-wrap">
                                <span><span class="legend-dot" style="background:#21864B"></span>{{ $isHindi ? 'न्यूनतम खरीद भाव' : 'Base buying rate' }}</span>
                                <span><span class="legend-dot legend-dash"></span>{{ $isHindi ? 'अधिकतम (ग्रेड-A)' : 'Max (Grade-A)' }}</span>
                            </div>
                        </div>

                        <div class="trend-chart-wrap flex-grow-1">
                            <canvas id="cropPriceTrendChart" aria-label="{{ $isHindi ? 'फसल भाव ग्राफ' : 'Crop price graph' }}" role="img"></canvas>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-2 border-top small text-muted">
                            <span>
                                <i class="bi bi-hand-index-thumb me-1 text-success"></i>
                                {{ $isHindi ? 'ग्राफ के किसी भी बिंदु पर टैप/माउस रखकर उस दिन का भाव व अंतर देखें।' : 'Tap or hover any point on the chart to inspect that date\'s exact rate & change.' }}
                            </span>
                            <span class="fw-semibold text-dark">{{ $isHindi ? 'इकाई: ₹ / क्विंटल' : 'Unit: ₹ / Quintal' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Side Summary & Historical Log Card --}}
            <div class="col-12 col-lg-4">
                @foreach($priceTrends as $t)
                    <div class="card border rounded-4 shadow-sm h-100 trend-panel {{ $t['id'] === $firstTrendId ? '' : 'd-none' }}" data-trend-panel="{{ $t['id'] }}">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="small text-muted">{{ $isHindi ? 'आज का ताज़ा भाव' : "Today's Active Rate" }} &bull; <strong>{{ $t['name'] }}</strong></div>
                                    <div class="d-flex align-items-baseline gap-1 mt-1">
                                        <span class="h2 fw-bold text-dark mb-0 tabular-nums">{{ $fmtRate($t['current']) }}</span>
                                        @if($t['current_max'] && $t['current_max'] > $t['current'])
                                            <span class="fw-semibold text-success tabular-nums">– {{ $fmtRate($t['current_max']) }}</span>
                                        @endif
                                        <span class="text-muted small">/ {{ $isHindi ? $t['unit_hi'] : $t['unit_en'] }}</span>
                                    </div>
                                </div>
                                @include('website.partials.rate-change', ['trend' => $t, 'isHindi' => $isHindi, 'size' => 'md'])
                            </div>

                            <div class="row g-2 my-3 text-center">
                                <div class="col-4">
                                    <div class="trend-stat">
                                        <div class="trend-stat-label">{{ $isHindi ? 'पिछला भाव' : 'Previous' }}</div>
                                        <div class="trend-stat-value tabular-nums">{{ $t['previous'] !== null ? $fmtRate($t['previous']) : '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="trend-stat">
                                        <div class="trend-stat-label">{{ $isHindi ? 'उच्चतम' : 'Highest' }}</div>
                                        <div class="trend-stat-value text-success tabular-nums">{{ $fmtRate($t['high']) }}</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="trend-stat">
                                        <div class="trend-stat-label">{{ $isHindi ? 'न्यूनतम' : 'Lowest' }}</div>
                                        <div class="trend-stat-value text-danger tabular-nums">{{ $fmtRate($t['low']) }}</div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="small fw-bold text-uppercase text-muted mb-2" style="font-size: 0.74rem; letter-spacing: 0.4px;">
                                <i class="bi bi-clock-history me-1"></i>{{ $isHindi ? 'पिछले भाव के आँकड़े' : 'Recent Price History' }}
                            </h4>
                            <ul class="list-unstyled mb-0 trend-timeline">
                                @foreach($t['recent'] as $r)
                                    <li class="trend-timeline-item d-flex justify-content-between align-items-center py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="timeline-dot {{ $r['direction'] }}"></span>
                                            <span class="small text-muted tabular-nums">{{ fmt_date($r['date']) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold small text-dark tabular-nums">{{ $fmtRate($r['rate']) }}</span>
                                            @include('website.partials.rate-change', ['trend' => ['direction' => $r['direction'], 'change' => $r['change'], 'change_pct' => null], 'isHindi' => $isHindi])
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            @if($t['updates_count'] < 2)
                                <div class="small text-muted mt-3 p-2 rounded-3 bg-light">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    {{ $isHindi ? 'अगली बार भाव अपडेट होने पर यहाँ पिछले भाव से अंतर दिखेगा।' : 'The difference from the previous price will appear here after the next price update.' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- Key Pillars / Value Proposition (2x2 on Mobile, 4-across on Desktop) --}}
<section class="py-4 py-md-5 bg-light border-bottom">
    <div class="container py-2 py-md-3">
        <div class="text-center mx-auto mb-4 mb-md-5" style="max-width: 680px;">
            <span class="section-eyebrow">{{ $isHindi ? 'हमारी विशेषताएं' : 'Why Choose KisanHub' }}</span>
            <h2 class="h3 fw-bold mt-1">{{ __('website.features.title') }}</h2>
            <p class="text-muted small mb-0">{{ __('website.features.subtitle') }}</p>
        </div>

        <div class="row g-3 g-md-4">
            <div class="col-6 col-lg-3">
                <div class="feature-card p-3 p-md-4">
                    <div class="pillar-icon bg-success-subtle text-success mb-3">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <h5 class="h6 fw-bold mb-2">{{ __('website.features.f1_title') }}</h5>
                    <p class="text-muted small mb-0">{{ __('website.features.f1_desc') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="feature-card p-3 p-md-4">
                    <div class="pillar-icon bg-info-subtle text-info-emphasis mb-3">
                        <i class="bi bi-moisture"></i>
                    </div>
                    <h5 class="h6 fw-bold mb-2">{{ __('website.features.f2_title') }}</h5>
                    <p class="text-muted small mb-0">{{ __('website.features.f2_desc') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="feature-card p-3 p-md-4">
                    <div class="pillar-icon bg-primary-subtle text-primary mb-3">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h5 class="h6 fw-bold mb-2">{{ __('website.features.f3_title') }}</h5>
                    <p class="text-muted small mb-0">{{ __('website.features.f3_desc') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="feature-card p-3 p-md-4">
                    <div class="pillar-icon bg-warning-subtle text-warning-emphasis mb-3">
                        <i class="bi bi-truck-flatbed"></i>
                    </div>
                    <h5 class="h6 fw-bold mb-2">{{ __('website.features.f4_title') }}</h5>
                    <p class="text-muted small mb-0">{{ __('website.features.f4_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Crops We Trade Section --}}
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container py-2 py-md-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-2 mb-3 mb-md-4">
            <div>
                <span class="section-eyebrow">{{ $isHindi ? 'प्रमुख कृषि उपज' : 'Commodity Catalogue' }}</span>
                <h2 class="h3 fw-bold mt-1 mb-1">{{ $isHindi ? 'हम किन फसलों की खरीद व आपूर्ति करते हैं' : 'Crops We Procure & Supply' }}</h2>
                <p class="text-muted small mb-0">{{ $isHindi ? 'किसानों से सीधी खरीद व उत्तम गुणवत्ता मानकों के साथ थोक आपूर्ति।' : 'Direct farmer procurement & bulk supply with strict mandi quality standards.' }}</p>
            </div>
            <div class="mt-2 mt-md-0 flex-shrink-0">
                <a href="{{ route('website.products') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 shadow-none d-inline-flex align-items-center gap-2">
                    <span>{{ $isHindi ? 'सभी उत्पाद व गुणवत्ता मानक देखें' : 'View Full Specifications' }}</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            @forelse($crops as $crop)
                @php
                    $cardTrend = $priceTrends->get($crop->id);
                @endphp
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="crop-card">
                        <div class="p-3 bg-white border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h5 class="h6 fw-bold text-dark mb-0">{{ $crop->name }}</h5>
                                <span class="badge bg-light text-dark border">{{ $crop->code }}</span>
                            </div>

                            @if($crop->show_rate_on_website && $crop->formattedPurchaseRate())
                                <div class="crop-rate-box mt-2">
                                    <div class="d-flex justify-content-between align-items-center gap-1">
                                        <span class="small text-muted">{{ $isHindi ? 'खरीद भाव:' : 'Buying Price:' }}</span>
                                        @if($cardTrend)
                                            @include('website.partials.rate-change', ['trend' => $cardTrend, 'isHindi' => $isHindi])
                                        @endif
                                    </div>
                                    <div class="fw-bold text-success mt-1 tabular-nums">{{ $crop->formattedPurchaseRate() }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="p-3 bg-light flex-grow-1 d-flex flex-column justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-2">
                                    {{ $isHindi ? 'प्रमुख किस्में (Varieties):' : 'Key Varieties Traded:' }}
                                </span>
                                @if($crop->varieties->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($crop->varieties as $v)
                                            <span class="badge bg-white text-dark border fw-normal">{{ $v->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="small text-muted">{{ $isHindi ? 'मानक मंडी ग्रेड' : 'Standard Commercial Grade' }}</span>
                                @endif
                            </div>

                            @if($cardTrend)
                                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $crop->rate_note ?: ($isHindi ? 'मंडी मानक' : 'Mandi Standard') }}</small>
                                    <a href="#price-trend" class="small fw-semibold text-primary text-decoration-none" data-hero-crop="{{ $crop->id }}">
                                        {{ $isHindi ? 'भाव ग्राफ' : 'Trend' }} &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted">
                    {{ $isHindi ? 'फसल विवरण लोड हो रहा है।' : 'Crop master data is being populated.' }}
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- 4-Step Procurement Process for Farmers --}}
<section class="py-4 py-md-5 bg-light border-bottom">
    <div class="container py-2 py-md-3">
        <div class="text-center mx-auto mb-4 mb-md-5" style="max-width: 680px;">
            <span class="section-eyebrow">{{ $isHindi ? 'सरल प्रक्रिया' : 'Farmer Centric' }}</span>
            <h2 class="h3 fw-bold mt-1">{{ $isHindi ? '4 आसान चरणों में अपनी फसल बेचें' : 'Simple 4-Step Harvest Sale Process' }}</h2>
            <p class="text-muted small mb-0">{{ $isHindi ? 'बिना किसी झंझट के पूरी पारदर्शिता और सम्मान के साथ फसल विक्रय।' : 'Zero hassle, completely transparent, designed for farmer convenience.' }}</p>
        </div>

        <div class="row g-3 g-md-4">
            <div class="col-6 col-lg-3">
                <div class="step-card text-center h-100">
                    <div class="step-badge bg-primary text-white">1</div>
                    <h6 class="fw-bold mb-2">{{ $isHindi ? 'आगमन एवं तौल' : 'Arrival & Weighment' }}</h6>
                    <p class="text-muted small mb-0">{{ $isHindi ? 'धर्मकांटा पर ट्रॉली/वाहन की आपके सामने डिजिटल तौल व तुरंत पर्ची।' : 'Bring your harvest to our digital weighbridge for instant gross weighment slip.' }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="step-card text-center h-100">
                    <div class="step-badge bg-primary text-white">2</div>
                    <h6 class="fw-bold mb-2">{{ $isHindi ? 'नमी व गुणवत्ता जांच' : 'Quality & Moisture Check' }}</h6>
                    <p class="text-muted small mb-0">{{ $isHindi ? 'डिजिटल मीटर से नमी जांच कर उत्तम ग्रेड का सर्वोत्तम भाव तय।' : 'Instant sample moisture test and grading to establish top market value.' }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="step-card text-center h-100">
                    <div class="step-badge bg-primary text-white">3</div>
                    <h6 class="fw-bold mb-2">{{ $isHindi ? 'उतराई व खाली तौल' : 'Unloading & Tare Weight' }}</h6>
                    <p class="text-muted small mb-0">{{ $isHindi ? 'गोदाम में त्वरित उतराई के बाद खाली वाहन तौल कर शुद्ध वजन की पुष्टि।' : 'Clean godown unloading followed by immediate empty-vehicle tare weighment.' }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="step-card text-center h-100">
                    <div class="step-badge bg-success text-white">4</div>
                    <h6 class="fw-bold mb-2">{{ $isHindi ? 'तुरंत पक्का भुगतान' : 'Instant Settlement' }}</h6>
                    <p class="text-muted small mb-0">{{ $isHindi ? 'पक्की कंप्यूटर रसीद के साथ बैंक ट्रांसफर, नकद या बोनस सहित बाद में भुगतान।' : 'Computerized bill with immediate bank transfer, cash, or bonus-backed delayed payout.' }}</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 pt-1">
            <a href="{{ route('website.farmer.register') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> {{ $isHindi ? 'आज ही किसान पंजीकरण करें' : 'Register as a Partner Farmer Today' }}
            </a>
        </div>
    </div>
</section>

{{-- Storage Godowns Network & Commercial Inquiry --}}
<section class="py-4 py-md-5 bg-white">
    <div class="container py-2 py-md-3">
        <div class="row align-items-center g-3 g-lg-5">
            <div class="col-12 col-lg-6">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                    <span class="section-eyebrow">{{ $isHindi ? 'भंडारण क्षमता' : 'Infrastructure' }}</span>
                    @if($totalCapacityQtl > 0)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                            <i class="bi bi-layers-fill me-1"></i>{{ indian_number($totalCapacityQtl, 0) }} {{ $isHindi ? 'क्विंटल कुल क्षमता' : 'Qtl Total Capacity' }}
                        </span>
                    @endif
                </div>
                <h2 class="h3 fw-bold mt-1 mb-2">{{ $isHindi ? 'आधुनिक गोदाम एवं भंडारण केंद्र' : 'Modern Storage Warehouses & Depots' }}</h2>
                <p class="text-muted small mb-4">
                    {{ $isHindi ? 'हवादार, नमी-रहित व सुरक्षित गोदामों का नेटवर्क जिससे राइस मिलों, आटा मिलों एवं थोक खरीदारों को वर्षभर निर्बाध आपूर्ति मिलती है।' : 'Clean, aerated, pest-controlled multi-godown storage capacity ready for rapid bulk dispatch to industrial buyers and mills.' }}
                </p>

                <div class="d-flex flex-column gap-2">
                    @foreach($warehouses as $wh)
                        <div class="godown-item d-flex justify-content-between align-items-center gap-2 p-2 p-sm-3 rounded-3 border bg-light" style="min-width: 0; max-width: 100%; overflow: hidden;">
                            <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1" style="min-width: 0;">
                                <div class="rounded-3 bg-white border text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                    <i class="bi bi-building-check fs-6"></i>
                                </div>
                                <div class="min-w-0 flex-grow-1" style="min-width: 0;">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem; line-height: 1.2;">{{ $wh->name }}</div>
                                    <div class="text-muted d-flex align-items-center gap-1 mt-1 min-w-0" style="font-size: 0.70rem; line-height: 1.2; min-width: 0;">
                                        <span class="badge bg-white text-secondary border py-0 px-1 font-monospace flex-shrink-0" style="font-size: 0.65rem;">{{ $wh->code }}</span>
                                        <span class="text-truncate" style="min-width: 0;">{{ $wh->address ?: ($isHindi ? 'मुख्य मंडी परिसर' : 'Operational Depot') }}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border px-2 py-1 flex-shrink-0 tabular-nums ms-2 shadow-2xs text-nowrap" style="font-size: 0.72rem; white-space: nowrap;">
                                <i class="bi bi-layers-fill text-success me-1"></i>{{ (float) $wh->capacity_quintals > 0 ? indian_number($wh->capacity_quintals, 0) . ($isHindi ? ' क्विंटल' : ' Qtl') : ($isHindi ? 'गोदाम' : 'Depot') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border rounded-4 p-3 p-sm-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-chat-square-text-fill"></i>
                        </div>
                        <div>
                            <h5 class="h6 fw-bold mb-0">{{ $isHindi ? 'मिलर्स व थोक खरीदार पूछताछ' : 'Quick Commercial Buyer Inquiry' }}</h5>
                            <small class="text-muted">{{ $isHindi ? 'थोक अनाज खरीद व रेट कोटेशन के लिए विवरण भेजें' : 'Request bulk grain quotes & dispatch schedules' }}</small>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('website.contact.store') }}">
                        @csrf
                        <input type="hidden" name="inquiry_type" value="bulk_buyer">
                        <div class="mb-3">
                            <label class="form-label small required">{{ $isHindi ? 'कंपनी / राइस या आटा मिल का नाम' : 'Company / Mill Name' }}</label>
                            <input type="text" name="name" class="form-control" placeholder="{{ $isHindi ? 'उदा. बालाजी राइस मिल्स प्रा. लि.' : 'e.g. Balaji Rice Mills' }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small required">{{ $isHindi ? 'संपर्क मोबाइल नंबर' : 'Contact Phone' }}</label>
                                <input type="tel" name="phone" class="form-control" placeholder="{{ $isHindi ? '10 अंकों का मोबाइल नंबर' : '10-digit mobile' }}" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label small">{{ $isHindi ? 'ईमेल (वैकल्पिक)' : 'Email Address' }}</label>
                                <input type="email" name="email" class="form-control" placeholder="{{ $isHindi ? 'वैकल्पिक ईमेल' : 'Optional email' }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small required">{{ $isHindi ? 'आवश्यक फसल व मात्रा का विवरण' : 'Grain Requirements' }}</label>
                            <textarea name="message" rows="3" class="form-control" placeholder="{{ $isHindi ? 'फसल का नाम, आवश्यक मात्रा (क्विंटल/टन में) और डिलीवरी स्थान लिखें...' : 'Specify crop, quantity needed in Quintals, and delivery destination...' }}" required minlength="10"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-send-fill me-1"></i> {{ $isHindi ? 'व्यापारिक पूछताछ भेजें' : 'Submit Commercial Inquiry' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .hero-section {
        padding-top: 1rem !important;
        padding-bottom: 2rem !important;
    }
    @media (max-width: 767.98px) {
        .hero-section {
            padding-top: 0.75rem !important;
            padding-bottom: 1.5rem !important;
        }
    }
    .tabular-nums {
        font-variant-numeric: tabular-nums;
    }
    .section-eyebrow {
        display: inline-block;
        color: var(--ke-primary);
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .hero-eyebrow {
        background: rgba(33, 134, 75, 0.10);
        border: 1px solid rgba(33, 134, 75, 0.22);
        color: #145a32;
        font-weight: 600;
        font-size: 0.82rem;
    }
    .hero-display-title {
        font-size: clamp(1.9rem, 4.5vw, 2.75rem);
        line-height: 1.22;
        letter-spacing: -0.02em;
        word-break: normal;
        overflow-wrap: break-word;
    }
    @media (max-width: 575.98px) {
        .hero-display-title {
            font-size: 1.9rem !important;
            line-height: 1.24;
        }
    }
    .hero-subtitle {
        font-size: clamp(0.92rem, 1.3vw, 1.08rem);
        line-height: 1.6;
        max-width: 620px;
        word-break: normal;
        overflow-wrap: break-word;
    }
    @media (min-width: 992px) {
        .hero-display-title {
            font-size: clamp(2.15rem, 2.8vw, 2.85rem);
            line-height: 1.18;
            letter-spacing: -0.03em;
            font-weight: 800;
        }
        .hero-subtitle {
            font-size: 0.98rem;
            line-height: 1.6;
            max-width: 560px;
        }
        .hero-trust-tile {
            padding: 0.65rem 0.75rem;
            border-radius: 12px;
        }
    }
    @media (max-width: 767.98px) {
        .trend-range-btn-group {
            width: 100%;
            display: flex;
        }
        .trend-range-btn-group .btn {
            flex: 1;
        }
    }
    @media (min-width: 768px) {
        .trend-range-btn-group {
            width: auto;
            display: inline-flex;
        }
    }
    .hero-trust-grid {
        max-width: 100%;
        margin-left: -0.35rem;
        margin-right: -0.35rem;
    }
    .hero-trust-grid > [class*="col-"] {
        padding-left: 0.35rem;
        padding-right: 0.35rem;
        min-width: 0;
    }
    .hero-trust-tile {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid var(--ke-border);
        border-radius: 12px;
        padding: 0.65rem 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        height: 100%;
        min-width: 0;
        box-shadow: 0 2px 6px rgba(21, 91, 54, 0.03);
    }
    .hero-trust-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .hero-trust-val {
        font-weight: 700;
        font-size: 0.84rem;
        color: #1e293b;
        line-height: 1.15;
        word-break: break-word;
    }
    .hero-trust-lbl {
        font-size: 0.7rem;
        color: #64748b;
        line-height: 1.2;
        word-break: break-word;
    }
    .hero-cta-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    @media (min-width: 576px) {
        .hero-cta-group {
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0.55rem;
        }
    }
    .hero-cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.42rem;
        font-size: 0.88rem;
        font-weight: 600;
        padding: 0.52rem 0.95rem;
        border-radius: 999px !important;
        white-space: nowrap;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        line-height: 1.25;
        flex-shrink: 0;
    }
    .hero-cta-btn:hover {
        transform: translateY(-1px);
    }
    .hero-cta-btn i {
        font-size: 0.95rem;
    }
    @media (min-width: 1200px) {
        .hero-cta-group {
            gap: 0.65rem;
        }
        .hero-cta-btn {
            font-size: 0.92rem;
            padding: 0.55rem 1.15rem;
            gap: 0.5rem;
        }
    }
    @media (max-width: 575.98px) {
        .hero-cta-btn {
            width: 100%;
            font-size: 0.92rem;
            padding: 0.65rem 1rem;
        }
        .hero-trust-tile {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
            padding: 0.45rem 0.45rem;
        }
        .hero-trust-icon {
            width: 26px;
            height: 26px;
            font-size: 0.82rem;
        }
        .hero-trust-val {
            font-size: 0.74rem;
        }
        .hero-trust-lbl {
            font-size: 0.62rem;
            line-height: 1.15;
        }
    }
    .hero-price-card {
        border-radius: 20px;
        box-shadow: 0 18px 40px rgba(17, 76, 42, 0.13), 0 3px 10px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(33, 134, 75, 0.18) !important;
        overflow: hidden;
        max-width: 100%;
    }
    .hero-price-header {
        background: linear-gradient(135deg, #1b7340 0%, #104b28 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 0.85rem 1.15rem;
    }
    .hero-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
    .hero-header-text {
        margin-left: 0.75rem;
    }
    .hero-price-title {
        font-size: 0.98rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        line-height: 1.24;
        color: #ffffff;
    }
    .hero-price-sub {
        font-size: 0.74rem;
        color: rgba(255, 255, 255, 0.88);
        font-weight: 400;
        line-height: 1.25;
        margin-top: 3px;
    }
    .hero-live-badge {
        background: #ffffff;
        color: #145732;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.2px;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        flex-shrink: 0;
        line-height: 1;
        white-space: nowrap;
    }
    .live-pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #198754;
        display: inline-block;
        flex-shrink: 0;
        position: relative;
    }
    .live-pulse-dot::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 50%;
        background: rgba(25, 135, 84, 0.35);
        animation: pulseRing 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulseRing {
        0% { transform: scale(0.9); opacity: 0.8; }
        70% { transform: scale(1.9); opacity: 0; }
        100% { transform: scale(1.9); opacity: 0; }
    }
    @media (max-width: 420px) {
        .hero-price-header {
            padding: 0.75rem 0.85rem;
        }
        .hero-header-icon {
            width: 34px;
            height: 34px;
            font-size: 1rem;
        }
        .hero-header-text {
            margin-left: 0.55rem;
        }
        .hero-price-title {
            font-size: 0.88rem;
        }
        .hero-price-sub {
            font-size: 0.68rem;
        }
        .hero-live-badge {
            font-size: 0.68rem;
            padding: 0.3rem 0.55rem;
            gap: 0.35rem;
        }
    }
    .crop-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #e9f6ee;
        color: #1c7c44;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
    .hero-crop-row {
        cursor: pointer;
        border-radius: 10px;
        padding: 0.4rem 0.5rem;
        margin-left: 0;
        margin-right: 0;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    @media (min-width: 576px) {
        .hero-crop-row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
    }
    .hero-crop-row:hover {
        background-color: rgba(33, 134, 75, 0.06);
    }
    .bonus-callout {
        background: linear-gradient(90deg, #eaf7ef 0%, #f5fbf7 100%);
        border: 1px solid #c6e6d2;
        color: #164e2e;
    }
    .rate-sparkline {
        width: 64px;
        height: 26px;
        opacity: 0.92;
    }
    @media (max-width: 400px) {
        .rate-sparkline {
            width: 46px;
            height: 22px;
        }
    }
    .rate-change {
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
        border-radius: 999px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }
    .rate-change.rate-sm {
        font-size: 0.7rem;
        padding: 0.15rem 0.48rem;
    }
    .rate-change.rate-md {
        font-size: 0.82rem;
        padding: 0.28rem 0.68rem;
    }
    .rate-change .rate-pct {
        font-weight: 600;
        opacity: 0.85;
    }
    .rate-change.rate-up {
        background-color: rgba(25, 135, 84, 0.13);
        color: #146c43;
        border: 1px solid rgba(25, 135, 84, 0.25);
    }
    .rate-change.rate-down {
        background-color: rgba(220, 53, 69, 0.12);
        color: #b02a37;
        border: 1px solid rgba(220, 53, 69, 0.25);
    }
    .rate-change.rate-same {
        background-color: #f1f3f5;
        color: #495057;
        border: 1px solid #dee2e6;
    }
    .rate-change.rate-new {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0a58ca;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }
    .trend-pills {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .trend-pills::-webkit-scrollbar {
        display: none;
    }
    .trend-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.48rem 0.9rem;
        border-radius: 999px;
        border: 1px solid #dce4de;
        background: #ffffff;
        color: #1e293b;
        font-size: 0.86rem;
        white-space: nowrap;
        transition: all 0.18s ease;
    }
    .trend-pill:hover {
        border-color: #21864B;
        background: rgba(33, 134, 75, 0.05);
    }
    .trend-pill.active {
        background: linear-gradient(135deg, #21864B 0%, #155B36 100%);
        border-color: #155B36;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(33, 134, 75, 0.25);
    }
    .trend-pill-price {
        font-weight: 700;
        color: #21864B;
    }
    .trend-pill.active .trend-pill-price {
        color: #ffffff;
    }
    .trend-pill-delta {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.1rem 0.4rem;
        border-radius: 999px;
    }
    .trend-pill-delta.up {
        background: rgba(25, 135, 84, 0.14);
        color: #146c43;
    }
    .trend-pill-delta.down {
        background: rgba(220, 53, 69, 0.14);
        color: #b02a37;
    }
    .trend-pill.active .trend-pill-delta {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }
    .trend-chart-wrap {
        position: relative;
        height: 310px;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    @media (max-width: 767.98px) {
        .trend-chart-wrap {
            height: 235px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
    }
    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }
    .legend-dash {
        width: 16px;
        height: 0;
        border-radius: 0;
        border-top: 2px dashed #0d6efd;
        background: transparent;
    }
    .trend-stat {
        background: #f6faf7;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        padding: 0.55rem 0.35rem;
    }
    .trend-stat-label {
        font-size: 0.71rem;
        color: #64748b;
        margin-bottom: 0.15rem;
    }
    .trend-stat-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1e293b;
    }
    .timeline-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #94a3b8;
        flex-shrink: 0;
    }
    .timeline-dot.up { background: #198754; }
    .timeline-dot.down { background: #dc3545; }
    .timeline-dot.new { background: #0d6efd; }
    .pillar-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .crop-rate-box {
        background: #f3faf5;
        border: 1px solid #d6ecde;
        border-radius: 10px;
        padding: 0.55rem 0.7rem;
    }
    .step-card {
        background: #ffffff;
        border: 1px solid var(--ke-border);
        border-radius: 16px;
        padding: 1.35rem 1rem;
        box-shadow: 0 2px 8px rgba(21, 91, 54, 0.03);
    }
    .step-badge {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 0.85rem;
        box-shadow: 0 4px 12px rgba(33, 134, 75, 0.22);
    }
    .godown-item {
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
        transition: border-color 0.18s ease, background-color 0.18s ease;
    }
    .godown-item:hover {
        background-color: #f4faf6 !important;
        border-color: rgba(33, 134, 75, 0.35) !important;
    }
</style>
@endpush

@if($priceTrends->isNotEmpty())
@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trendsById = @json($priceTrends);
    const currencySymbol = @json(setting('locale.currency_symbol', '₹'));
    const isHindi = @json($isHindi);
    const canvas = document.getElementById('cropPriceTrendChart');
    if (!canvas || typeof Chart === 'undefined') return;

    let activeCropId = String(@json($priceTrends->keys()->first()));
    let activeRangeDays = 90;
    let chartInstance = null;

    function filterPoints(points, maxDays) {
        if (!Array.isArray(points) || points.length === 0) return [];
        const within = points.filter(p => (typeof p.days_ago === 'number' ? p.days_ago <= maxDays : true));
        if (within.length >= 2) return within;
        return points.slice(-Math.min(points.length, 2));
    }

    function renderChart() {
        const trend = trendsById[activeCropId];
        if (!trend) return;

        const titleEl = document.getElementById('trendChartTitle');
        if (titleEl) {
            const diffBadge = trend.change !== null && trend.change !== 0
                ? ' (' + (trend.change > 0 ? '+' : '-') + currencySymbol + Math.abs(trend.change).toLocaleString('en-IN') + ')'
                : '';
            titleEl.textContent = trend.name + diffBadge;
        }

        const pts = filterPoints(trend.points, activeRangeDays);
        const labels = pts.map(p => p.label);
        const baseRates = pts.map(p => Number(p.rate) || 0);
        const maxRates = pts.map(p => (p.max !== null && p.max !== undefined && Number(p.max) >= Number(p.rate)) ? Number(p.max) : null);
        const hasMax = maxRates.some(v => v !== null && v > 0);

        const isDown = trend.direction === 'down';
        const mainColor = isDown ? '#dc3545' : '#21864B';
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, isDown ? 'rgba(220, 53, 69, 0.22)' : 'rgba(33, 134, 75, 0.24)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0.01)');

        const datasets = [
            {
                label: isHindi ? 'न्यूनतम खरीद भाव (₹)' : 'Base Buying Rate (₹)',
                data: baseRates,
                borderColor: mainColor,
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.32,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: mainColor,
                pointBorderWidth: 2.5,
            }
        ];

        if (hasMax) {
            datasets.push({
                label: isHindi ? 'अधिकतम / ग्रेड-A भाव (₹)' : 'Max / Grade-A Rate (₹)',
                data: maxRates,
                borderColor: '#0d6efd',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [6, 4],
                fill: false,
                tension: 0.32,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2,
                spanGaps: true,
            });
        }

        const allVals = baseRates.concat(hasMax ? maxRates.filter(v => v !== null) : []);
        const minVal = Math.min(...allVals);
        const maxVal = Math.max(...allVals);
        const pad = Math.max(50, Math.round((maxVal - minVal) * 0.25));

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 10,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function (context) {
                                if (context.parsed.y === null) return '';
                                return ' ' + context.dataset.label + ': ' + currencySymbol + Number(context.parsed.y).toLocaleString('en-IN');
                            },
                            afterBody: function (items) {
                                if (!items.length) return '';
                                const idx = items[0].dataIndex;
                                if (idx > 0 && baseRates[idx - 1] !== undefined) {
                                    const diff = Math.round((baseRates[idx] - baseRates[idx - 1]) * 100) / 100;
                                    const sign = diff > 0 ? '+' : '';
                                    return (isHindi ? 'पिछले भाव से अंतर: ' : 'Change vs prior: ') + sign + currencySymbol + diff.toLocaleString('en-IN');
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#64748b' }
                    },
                    y: {
                        suggestedMin: Math.max(0, minVal - pad),
                        suggestedMax: maxVal + pad,
                        grid: { color: 'rgba(15, 23, 42, 0.06)' },
                        ticks: {
                            font: { size: 11 },
                            color: '#475569',
                            callback: function (value) {
                                return currencySymbol + Number(value).toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });
    }

    function selectCrop(cropId, scrollIntoView = false) {
        activeCropId = String(cropId);
        document.querySelectorAll('[data-trend-crop]').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-trend-crop') === activeCropId);
        });
        document.querySelectorAll('[data-trend-panel]').forEach(panel => {
            panel.classList.toggle('d-none', panel.getAttribute('data-trend-panel') !== activeCropId);
        });
        renderChart();

        if (scrollIntoView) {
            const section = document.getElementById('price-trend');
            if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    document.querySelectorAll('[data-trend-crop]').forEach(btn => {
        btn.addEventListener('click', () => selectCrop(btn.getAttribute('data-trend-crop'), false));
    });

    document.querySelectorAll('[data-hero-crop]').forEach(el => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            selectCrop(el.getAttribute('data-hero-crop'), true);
        });
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectCrop(el.getAttribute('data-hero-crop'), true);
            }
        });
    });

    document.querySelectorAll('[data-trend-range]').forEach(btn => {
        btn.addEventListener('click', () => {
            activeRangeDays = parseInt(btn.getAttribute('data-trend-range'), 10) || 90;
            document.querySelectorAll('[data-trend-range]').forEach(b => b.classList.toggle('active', b === btn));
            renderChart();
        });
    });

    renderChart();
});
</script>
@endpush
@endif
