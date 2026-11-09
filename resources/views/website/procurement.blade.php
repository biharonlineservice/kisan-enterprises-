@extends('layouts.website')

@section('title', (app()->getLocale() === 'hi' ? 'फसल कैसे बेचें (किसान मार्गदर्शिका) — ' : 'Crop Procurement Guide for Farmers — ') . setting('website.brand_name', 'KisanHub'))

@section('content')
@php
    $isHindi = app()->getLocale() === 'hi';
    $bizName = setting('website.brand_name', 'KisanHub');
    $bonusEnabled = (string) setting('procurement.delayed_bonus_enabled', '1') === '1' && (float) setting('procurement.delayed_bonus_rate', '1.00') > 0;
    $bonusRate = (float) setting('procurement.delayed_bonus_rate', '1.00');
    $bonusRateFormatted = number_format($bonusRate, 2);
    $bonusMode = setting('procurement.delayed_bonus_mode', 'monthly');
    $fmtRate = function ($v) {
        $v = (float) $v;
        return setting('locale.currency_symbol', '₹') . indian_number($v, fmod($v, 1) ? 2 : 0);
    };
@endphp

{{-- Hero Banner + Interactive Farmer Crop Value Estimator --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-start g-4 g-lg-5">
            {{-- Left Column --}}
            <div class="col-12 col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill proc-eyebrow mb-3">
                    <i class="bi bi-tree-fill text-success"></i>
                    <span>{{ $isHindi ? 'किसान फसल विक्रय मार्गदर्शिका' : 'Farmer Harvest Sale Guide' }}</span>
                </div>

                <h1 class="proc-hero-title fw-bold text-dark mb-3">
                    @if($isHindi)
                        अपनी मेहनत की उपज बेचें सही तौल, लाइव मंडी भाव और पक्के भुगतान के साथ
                    @else
                        Sell Your Harvest with Fair Digital Weighment & Guaranteed Payout
                    @endif
                </h1>

                <p class="proc-lead text-muted mb-4">
                    @if($isHindi)
                        {{ $bizName }} के धर्मकांटा केंद्र पर अपनी फसल लाने से लेकर नमी जांच और सीधे बैंक खाते या नकद भुगतान तक की पूरी पारदर्शी प्रक्रिया यहाँ जानें।
                    @else
                        Everything you need to know about bringing your harvest to {{ $bizName }} for certified digital weighment, transparent moisture grading, and instant bank or bonus-backed settlement.
                    @endif
                </p>

                <div class="ke-cta-group mb-4">
                    <a href="{{ route('website.farmer.register') }}" class="btn btn-primary rounded-pill shadow-sm ke-cta-btn">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>{{ $isHindi ? 'किसान ऑनलाइन पंजीकरण करें' : 'Register as Farmer Online' }}</span>
                    </a>
                    <a href="#accepted-crops" class="btn btn-outline-primary bg-white rounded-pill ke-cta-btn">
                        <i class="bi bi-list-check"></i>
                        <span>{{ $isHindi ? 'स्वीकृत फसलें व आज का भाव' : 'Accepted Crops & Rates' }}</span>
                    </a>
                </div>

                {{-- Documents Required Quick Pill Strip --}}
                <div class="p-3 rounded-4 bg-white border shadow-sm">
                    <div class="small fw-bold text-dark mb-2">
                        <i class="bi bi-card-checklist text-success me-1"></i>
                        {{ $isHindi ? 'फसल लाते समय साथ रखें (आवश्यक दस्तावेज़):' : 'Documents to Carry on Arrival:' }}
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-person-vcard text-primary me-1"></i>{{ $isHindi ? 'आधार कार्ड / पहचान पत्र' : 'Aadhaar / Govt ID' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-bank text-success me-1"></i>{{ $isHindi ? 'बैंक पासबुक (खाता व IFSC)' : 'Bank Passbook / IFSC' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-qr-code text-warning-emphasis me-1"></i>{{ $isHindi ? 'किसान आईडी (या गेट पर स्पॉट रजिस्ट्रेशन)' : 'Farmer ID (or Spot Gate Entry)' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Interactive Harvest Value & Bonus Estimator --}}
            <div class="col-12 col-lg-5">
                <div class="card estimator-card border-0 overflow-hidden">
                    <div class="estimator-header text-white py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <span class="ke-header-icon">
                                <i class="bi bi-calculator-fill"></i>
                            </span>
                            <div class="ke-header-text min-w-0">
                                <div class="fw-bold text-white lh-sm">{{ $isHindi ? 'फसल मूल्य एवं बोनस कैलकुलेटर' : 'Harvest Value & Bonus Estimator' }}</div>
                                <small class="text-white-50 d-block" style="font-size: 0.75rem;">
                                    {{ $isHindi ? 'आज के लाइव मंडी भाव के अनुसार अनुमानित राशि' : "Estimate payout using today's live buying rates" }}
                                </small>
                            </div>
                        </div>
                        <span class="ke-live-badge flex-shrink-0">
                            <span class="live-pulse-dot"></span>
                            <span>{{ $isHindi ? 'लाइव कैलकुलेटर' : 'Instant' }}</span>
                        </span>
                    </div>

                    <div class="card-body p-3 p-sm-4 bg-white">
                        <div class="mb-3">
                            <label for="calcCropSelect" class="form-label small fw-semibold">
                                {{ $isHindi ? 'अपनी फसल चुनें' : 'Select Your Crop' }}
                            </label>
                            <select id="calcCropSelect" class="form-select">
                                @forelse($rateCrops as $rc)
                                    <option value="{{ $rc->current_purchase_rate }}"
                                            data-max="{{ ((float) $rc->max_purchase_rate > (float) $rc->current_purchase_rate) ? $rc->max_purchase_rate : $rc->current_purchase_rate }}"
                                            data-unit="{{ $isHindi ? $rc->shortUnitLabel('hi') : $rc->shortUnitLabel('en') }}">
                                        {{ $rc->name }} — {{ $rc->formattedPurchaseRate() }}
                                    </option>
                                @empty
                                    <option value="2300" data-max="2450" data-unit="{{ $isHindi ? 'क्विंटल' : 'Qtl' }}">
                                        Paddy (Dhan) — ₹2,300 / Qtl
                                    </option>
                                @endforelse
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="calcWeightInput" class="form-label small fw-semibold">
                                    {{ $isHindi ? 'अनुमानित वजन (क्विंटल)' : 'Est. Weight (Quintals)' }}
                                </label>
                                <div class="input-group">
                                    <input type="number" id="calcWeightInput" class="form-control tabular-nums fw-bold" value="25" min="1" max="5000" step="0.5" inputmode="decimal">
                                    <span class="input-group-text small" id="calcUnitAddon">{{ $isHindi ? 'क्विंटल' : 'Qtl' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="calcDaysInput" class="form-label small fw-semibold">
                                    {{ $isHindi ? 'भुगतान समय' : 'Payout Timing' }}
                                </label>
                                <select id="calcDaysInput" class="form-select">
                                    <option value="0">{{ $isHindi ? 'तुरंत (उसी दिन)' : 'Instant (Same-Day)' }}</option>
                                    @if($bonusEnabled)
                                        <option value="15">{{ $isHindi ? '15 दिन बाद (+बोनस)' : 'After 15 Days (+Bonus)' }}</option>
                                        <option value="30" selected>{{ $isHindi ? '30 दिन बाद (+बोनस)' : 'After 30 Days (+Bonus)' }}</option>
                                        <option value="60">{{ $isHindi ? '60 दिन बाद (+बोनस)' : 'After 60 Days (+Bonus)' }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="estimator-result rounded-3 p-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="text-muted">{{ $isHindi ? 'आधार फसल राशि:' : 'Base Crop Amount:' }}</span>
                                <span class="fw-bold text-dark tabular-nums" id="calcBaseAmount">₹0</span>
                            </div>
                            @if($bonusEnabled)
                                <div class="d-flex justify-content-between align-items-center small mb-2 pb-2 border-bottom" id="calcBonusRow">
                                    <span class="text-success fw-semibold">
                                        <i class="bi bi-gift-fill me-1"></i>{{ $isHindi ? "देरी भुगतान बोनस ({$bonusRateFormatted}%):" : "Delayed Payout Bonus ({$bonusRateFormatted}%):" }}
                                    </span>
                                    <span class="fw-bold text-success tabular-nums" id="calcBonusAmount">+₹0</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="fw-bold text-dark">{{ $isHindi ? 'कुल अनुमानित भुगतान:' : 'Estimated Total Payout:' }}</span>
                                <span class="h4 fw-bold text-success mb-0 tabular-nums" id="calcTotalAmount">₹0</span>
                            </div>
                            <small class="text-muted d-block mt-1" id="calcGradeHint" style="font-size: 0.72rem;"></small>
                        </div>
                    </div>

                    <div class="card-footer bg-light border-top px-3 px-sm-4 py-2 d-flex justify-content-between align-items-center small">
                        <span class="text-muted" style="font-size: 0.74rem;">
                            <i class="bi bi-info-circle text-primary me-1"></i>
                            {{ $isHindi ? 'अंतिम दर गुणवत्ता व नमी जांच पर आधारित' : 'Final rate subject to moisture & grade check' }}
                        </span>
                        <a href="{{ route('home') }}#price-trend" class="fw-semibold text-primary text-decoration-none" style="font-size: 0.78rem;">
                            {{ $isHindi ? 'भाव ग्राफ →' : 'Price Graph →' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3 Key Procurement Highlights --}}
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container py-2">
        <div class="row g-3 g-md-4">
            <div class="col-12 col-md-4">
                <div class="feature-card proc-accent-green p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="proc-pill-icon bg-success-subtle text-success">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'खरीफ व रबी सीजन खरीद' : 'Kharif & Rabi Seasons' }}</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'हम पूरे वर्ष खरीफ एवं रबी फसल चक्र में धान, गेहूं, मक्का, सरसों और मौसमी अनाजों की सीधी खरीद करते हैं।' : 'Year-round procurement across Kharif and Rabi harvest cycles for Paddy, Wheat, Maize, Mustard, and seasonal grains.' }}
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card proc-accent-blue p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="proc-pill-icon bg-primary-subtle text-primary">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'प्रमाणित डिजिटल धर्मकांटा' : 'Certified Weighbridge Accuracy' }}</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'विधिक माप विज्ञान द्वारा प्रमाणित इलेक्ट्रॉनिक धर्मकांटा — भरी और खाली गाड़ी का वजन आपकी पर्ची पर साफ प्रिंट होता है।' : 'Legal-metrology calibrated electronic weighbridge where gross, tare, and net weights are printed live on your slip.' }}
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card proc-accent-amber p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="proc-pill-icon bg-warning-subtle text-warning-emphasis">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'तुरंत भुगतान व देरी पर बोनस' : 'Flexible & Bonus-Backed Payouts' }}</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'किसान अपनी सुविधा अनुसार तुरंत NEFT/RTGS/UPI/नकद भुगतान लें, या बाद में भुगतान चुनकर अतिरिक्त बोनस कमाएं।' : 'Receive instant NEFT/RTGS/UPI or cash voucher payout, or choose delayed settlement to earn extra incentive bonus.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Accepted Crops Specification Matrix (Desktop Table + Mobile Cards) --}}
<section id="accepted-crops" class="py-4 py-md-5 bg-light border-bottom">
    <div class="container py-2 py-md-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
            <div>
                <span class="proc-section-eyebrow">
                    <i class="bi bi-patch-check-fill me-1"></i>{{ $isHindi ? 'फसल मानक एवं लाइव खरीद दरें' : 'Commodity Standards & Live Rates' }}
                </span>
                <h2 class="h3 fw-bold mt-1 mb-0">{{ $isHindi ? 'हमारे केंद्रों पर स्वीकृत फसलें व गुणवत्ता मानदंड' : 'Crops Currently Accepted at Our Depots' }}</h2>
            </div>
            @if($latestRateUpdate)
                <span class="badge bg-white text-dark border px-3 py-2 align-self-start align-self-md-end">
                    <i class="bi bi-clock-history text-success me-1"></i>
                    {{ $isHindi ? 'ताज़ा भाव अपडेट:' : 'Rates Updated:' }} <strong>{{ fmt_date($latestRateUpdate, true) }}</strong>
                </span>
            @endif
        </div>

        {{-- Desktop View (>= 992px): Clean Rich Table --}}
        <div class="card shadow-sm border rounded-4 overflow-hidden bg-white d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">{{ $isHindi ? 'फसल (Crop)' : 'Crop' }}</th>
                            <th>{{ $isHindi ? 'आज का खरीद भाव' : 'Current Buying Rate' }}</th>
                            <th>{{ $isHindi ? 'प्रमुख किस्में (Varieties)' : 'Accepted Varieties' }}</th>
                            <th>{{ $isHindi ? 'मानक नमी सीमा' : 'Moisture Benchmark' }}</th>
                            <th>{{ $isHindi ? 'गुणवत्ता एवं पैकिंग मानक' : 'Quality & Packaging' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($crops as $c)
                            @php
                                $cTrend = $priceTrends->get($c->id);
                                $moistures = $c->varieties->pluck('standard_moisture_pct')->filter(fn ($m) => (float) $m > 0);
                                $moistureLabel = $moistures->isNotEmpty()
                                    ? ($moistures->min() == $moistures->max()
                                        ? number_format((float) $moistures->min(), 1) . '%'
                                        : number_format((float) $moistures->min(), 1) . '% – ' . number_format((float) $moistures->max(), 1) . '%')
                                    : ($isHindi ? 'मंडी मानक अनुसार' : 'Standard Mandi Limit');
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $c->name }}</div>
                                    <code class="small text-primary">{{ $c->code }}</code>
                                </td>
                                <td>
                                    @if($c->show_rate_on_website && $c->formattedPurchaseRate())
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-success tabular-nums">{{ $c->formattedPurchaseRate() }}</span>
                                            @if($cTrend)
                                                @include('website.partials.rate-change', ['trend' => $cTrend, 'isHindi' => $isHindi])
                                            @endif
                                        </div>
                                        @if($c->rate_note)
                                            <small class="text-muted d-block">{{ $c->rate_note }}</small>
                                        @endif
                                    @else
                                        <span class="small text-muted">{{ $isHindi ? 'मंडी काउंटर पर संपर्क करें' : 'At Mandi Counter Rate' }}</span>
                                    @endif
                                </td>
                                <td style="max-width: 260px;">
                                    @if($c->varieties->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($c->varieties as $v)
                                                <span class="badge bg-light text-dark border fw-normal">{{ $v->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="small text-muted">{{ $isHindi ? 'मानक / मिश्रित किस्म' : 'Standard / Mixed' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 tabular-nums">
                                        <i class="bi bi-moisture me-1"></i>&le; {{ $moistureLabel }}
                                    </span>
                                </td>
                                <td class="pe-4 small text-muted">
                                    <div>{{ $isHindi ? 'साफ, पके व मिट्टी/डंठल रहित दाने' : 'Clean, mature grain free from foreign matter' }}</div>
                                    <div class="text-dark fw-medium mt-1">
                                        <i class="bi bi-box-seam text-success me-1"></i>{{ $isHindi ? 'खुली ट्रॉली अथवा जूट/HDPE बोरे' : 'Loose trolley or gunny/HDPE bags' }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile & Tablet View (< 992px): Clean Native Cards --}}
        <div class="row g-3 d-lg-none">
            @foreach($crops as $c)
                @php
                    $cTrend = $priceTrends->get($c->id);
                    $moistures = $c->varieties->pluck('standard_moisture_pct')->filter(fn ($m) => (float) $m > 0);
                    $moistureLabel = $moistures->isNotEmpty()
                        ? ($moistures->min() == $moistures->max()
                            ? number_format((float) $moistures->min(), 1) . '%'
                            : number_format((float) $moistures->min(), 1) . '% – ' . number_format((float) $moistures->max(), 1) . '%')
                        : ($isHindi ? 'मंडी मानक' : 'Standard');
                @endphp
                <div class="col-12 col-md-6">
                    <div class="card border rounded-4 shadow-sm h-100 bg-white overflow-hidden">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="h6 fw-bold text-dark mb-0">{{ $c->name }}</h5>
                                    <span class="badge bg-light text-dark border">{{ $c->code }}</span>
                                </div>
                                @if($c->rate_note)
                                    <small class="text-muted d-block mt-1">{{ $c->rate_note }}</small>
                                @endif
                            </div>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle tabular-nums">
                                <i class="bi bi-moisture me-1"></i>&le; {{ $moistureLabel }}
                            </span>
                        </div>

                        <div class="p-3">
                            @if($c->show_rate_on_website && $c->formattedPurchaseRate())
                                <div class="p-2 px-3 rounded-3 bg-success-subtle bg-opacity-50 border border-success-subtle d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $isHindi ? 'आज का खरीद भाव' : 'Current Buying Rate' }}</small>
                                        <span class="fw-bold text-success tabular-nums">{{ $c->formattedPurchaseRate() }}</span>
                                    </div>
                                    @if($cTrend)
                                        @include('website.partials.rate-change', ['trend' => $cTrend, 'isHindi' => $isHindi])
                                    @endif
                                </div>
                            @endif

                            <div class="mb-2">
                                <small class="text-muted fw-semibold d-block mb-1">{{ $isHindi ? 'स्वीकृत किस्में:' : 'Varieties:' }}</small>
                                @if($c->varieties->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($c->varieties as $v)
                                            <span class="badge bg-light text-dark border fw-normal">{{ $v->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="small text-muted">{{ $isHindi ? 'मानक मंडी ग्रेड' : 'Standard Grade' }}</span>
                                @endif
                            </div>

                            <div class="small text-muted pt-2 border-top mt-2">
                                <i class="bi bi-truck text-success me-1"></i>
                                {{ $isHindi ? 'खुली ट्रॉली या बोरे दोनों स्वीकार्य' : 'Accepted loose in trolley or gunny/HDPE bags' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 4-Step Arrival & Settlement Walkthrough + FAQs --}}
<section class="py-4 py-md-5 bg-white">
    <div class="container py-2 py-md-3">
        <div class="row g-4 g-lg-5 align-items-start">
            {{-- Left: 4-Step Arrival Timeline --}}
            <div class="col-12 col-lg-5">
                <span class="proc-section-eyebrow">{{ $isHindi ? 'चरण-दर-चरण प्रक्रिया' : 'Step-by-Step Arrival' }}</span>
                <h3 class="h4 fw-bold mt-1 mb-3">{{ $isHindi ? 'मंडी केंद्र पर फसल बिक्री कैसे होती है?' : 'How Weighbridge Procurement Works' }}</h3>

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3 p-3 rounded-4 border bg-light">
                        <div class="proc-step-num bg-primary text-white">1</div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ $isHindi ? 'गेट आगमन व भरी गाड़ी तौल' : 'Gate Entry & Gross Weighment' }}</h6>
                            <p class="small text-muted mb-0">{{ $isHindi ? 'धर्मकांटा पर वाहन खड़ा होते ही डिजिटल डिस्प्ले पर वजन देखें और ग्रॉस पर्ची प्राप्त करें।' : 'Park on the digital platform, verify the live LED weight display, and collect your printed gross slip.' }}</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 p-3 rounded-4 border bg-light">
                        <div class="proc-step-num bg-primary text-white">2</div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ $isHindi ? 'नमी परीक्षण व रेट निर्धारण' : 'Moisture Check & Rate Lock' }}</h6>
                            <p class="small text-muted mb-0">{{ $isHindi ? 'आपके सामने सैंपल की इलेक्ट्रॉनिक नमी जांच कर ग्रेड के अनुसार सर्वोत्तम खरीद दर लॉक की जाती है।' : 'Instant digital moisture reading in your presence to lock the highest grade rate for your lot.' }}</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 p-3 rounded-4 border bg-light">
                        <div class="proc-step-num bg-primary text-white">3</div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ $isHindi ? 'गोदाम उतराई व खाली तौल (Tare)' : 'Godown Unloading & Tare Weight' }}</h6>
                            <p class="small text-muted mb-0">{{ $isHindi ? 'निर्धारित गोदाम में त्वरित उतराई के तुरंत बाद खाली गाड़ी का वजन कर शुद्ध क्विंटल निकाला जाता है।' : 'Quick unloading at our clean depot followed by empty vehicle weighment to compute exact net quintals.' }}</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 p-3 rounded-4 border border-success-subtle bg-success-subtle bg-opacity-50">
                        <div class="proc-step-num bg-success text-white">4</div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ $isHindi ? 'कंप्यूटर बिल व तुरंत भुगतान' : 'Printed Bill & Payout' }}</h6>
                            <p class="small text-muted mb-0">{{ $isHindi ? 'पक्की खरीद रसीद लें और तुरंत बैंक ट्रांसफर/नकद या बोनस योजना के साथ भुगतान प्राप्त करें।' : 'Collect your official purchase voucher and receive instant NEFT/UPI/cash or bonus-backed delayed settlement.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Farmer FAQs --}}
            <div class="col-12 col-lg-7">
                <span class="proc-section-eyebrow">{{ $isHindi ? 'सामान्य प्रश्नोत्तर' : 'Farmer FAQs' }}</span>
                <h3 class="h4 fw-bold mt-1 mb-3">{{ $isHindi ? 'किसानों द्वारा अक्सर पूछे जाने वाले प्रश्न' : 'Frequently Asked Questions by Farmers' }}</h3>

                <div class="accordion proc-accordion" id="farmerFaq">
                    <div class="accordion-item rounded-4 border mb-2 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                {{ $isHindi ? 'क्या फसल लाने से पहले ऑनलाइन पंजीकरण ज़रूरी है?' : 'Do I need to register online before bringing my harvest?' }}
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#farmerFaq">
                            <div class="accordion-body small text-muted">
                                {{ $isHindi ? 'धर्मकांटा गेट पर स्पॉट रजिस्ट्रेशन की सुविधा उपलब्ध है, लेकिन पहले से ऑनलाइन पंजीकरण करने पर आपको तुरंत किसान आईडी (FRM...) मिल जाती है जिससे भीड़ के समय आपकी तौल पर्ची बिना रुके तुरंत बनती है।' : 'Spot registration at the weighbridge gate is always available, but registering online beforehand gives you an instant Farmer ID (FRM...) so your weighment slip is generated immediately without waiting in queue.' }}
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item rounded-4 border mb-2 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                {{ $isHindi ? 'फसल बेचते समय कौन-कौन से कागजात साथ लाने चाहिए?' : 'What documents should I carry when bringing my crop?' }}
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#farmerFaq">
                            <div class="accordion-body small text-muted">
                                {{ $isHindi ? 'कृपया अपना आधार कार्ड (या सरकारी पहचान पत्र) और बैंक पासबुक की प्रति (खाता संख्या व IFSC कोड) साथ लाएं, ताकि आपका भुगतान सीधे आपके बैंक खाते में बिना किसी देरी के भेजा जा सके।' : 'Please carry your Aadhaar card (or government ID) and bank passbook copy (account number & IFSC code) so direct bank remittance can be processed accurately without delay.' }}
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item rounded-4 border mb-2 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                {{ $isHindi ? 'गीले या नमी वाले अनाज की जांच कैसे की जाती है?' : 'How are moisture levels checked if the harvest has dampness?' }}
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#farmerFaq">
                            <div class="accordion-body small text-muted">
                                {{ $isHindi ? 'हम आपकी उपस्थिति में कैलिब्रेटेड डिजिटल मॉइस्चर मीटर से नमी जांचते हैं। किसी भी प्रकार का अंदाज़ा या मनमानी कटौती नहीं की जाती — सब कुछ मानक मंडी नियमों के अनुसार पारदर्शी रहता है।' : 'We test grain samples right in front of you using calibrated electronic moisture meters. Any moisture adjustment follows transparent mandi benchmarks with zero guesswork.' }}
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item rounded-4 border overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                {{ $isHindi ? 'बाद में भुगतान लेने पर अतिरिक्त बोनस कैसे मिलता है?' : 'How does the Delayed Payment Bonus work for farmers?' }}
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#farmerFaq">
                            <div class="accordion-body small text-muted">
                                {{ $isHindi ? 'यदि कोई किसान तौल के दिन तुरंत भुगतान न लेकर कुछ समय बाद भुगतान लेना चुनते हैं, तो भुगतान वाउचर बनाते समय सिस्टम स्वचालित रूप से निर्धारित प्रतिशत के अनुसार अतिरिक्त प्रोत्साहन बोनस जोड़कर भुगतान करता है।' : 'Farmers who choose to collect their crop payment at a later date instead of instant same-day payout automatically earn an incentive bonus percentage on their settlement voucher.' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom CTA Card --}}
                <div class="proc-cta-strip rounded-4 p-4 text-white mt-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div>
                        <h5 class="h6 fw-bold mb-1">{{ $isHindi ? 'ऑनलाइन किसान आईडी मात्र 1 मिनट में बनाएं' : 'Get Your Official Farmer ID in 1 Minute' }}</h5>
                        <small class="text-white-50">{{ $isHindi ? 'निःशुल्क पंजीकरण — त्वरित तौल और सीधे बैंक भुगतान के लिए।' : 'Free enrollment for faster weighbridge entry & direct bank payouts.' }}</small>
                    </div>
                    <a href="{{ route('website.farmer.register') }}" class="btn btn-light text-success fw-bold rounded-pill text-nowrap px-4 py-2 d-inline-flex align-items-center justify-content-center gap-2 flex-shrink-0 shadow-sm">
                        <i class="bi bi-pencil-square"></i>
                        <span>{{ $isHindi ? 'अभी पंजीकरण करें' : 'Register Now' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .tabular-nums { font-variant-numeric: tabular-nums; }
    .proc-eyebrow {
        background: rgba(33, 134, 75, 0.10);
        border: 1px solid rgba(33, 134, 75, 0.22);
        color: #145a32;
        font-weight: 600;
        font-size: 0.82rem;
    }
    .proc-section-eyebrow {
        display: inline-block;
        color: var(--ke-primary);
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .proc-hero-title {
        font-size: clamp(1.7rem, 3.3vw, 2.5rem);
        line-height: 1.22;
        letter-spacing: -0.02em;
    }
    .proc-lead {
        font-size: clamp(0.95rem, 1.25vw, 1.05rem);
        line-height: 1.65;
    }
    @media (max-width: 575.98px) {
        .proc-cta-btn {
            width: 100%;
            font-size: 0.95rem;
            padding: 0.68rem 1rem;
        }
    }
    .estimator-card {
        border-radius: 20px;
        box-shadow: 0 18px 40px rgba(17, 76, 42, 0.13);
        border: 1px solid rgba(33, 134, 75, 0.18) !important;
    }
    .estimator-header {
        background: linear-gradient(135deg, #1d7e46 0%, #114c2a 100%);
    }
    .estimator-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.16);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .estimator-result {
        background: linear-gradient(180deg, #f3faf5 0%, #eaf6ee 100%);
        border: 1px solid #c9e6d3;
    }
    .proc-pill-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .proc-step-num {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }
    .proc-accordion .accordion-button:not(.collapsed) {
        background-color: #f2f9f4;
        color: #145a32;
        box-shadow: none;
    }
    .proc-accordion .accordion-button:focus {
        box-shadow: none;
    }
    .proc-cta-strip {
        background: linear-gradient(135deg, #186a3b 0%, #0f4225 100%);
    }
    .proc-accent-green { border-left: 4px solid #1e8449 !important; }
    .proc-accent-blue { border-left: 4px solid #0d6efd !important; }
    .proc-accent-amber { border-left: 4px solid #e6a800 !important; }
    .rate-change {
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
        border-radius: 999px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        font-size: 0.7rem;
        padding: 0.15rem 0.48rem;
    }
    .rate-change .rate-pct { font-weight: 600; opacity: 0.85; }
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cropSelect = document.getElementById('calcCropSelect');
    const weightInput = document.getElementById('calcWeightInput');
    const daysInput = document.getElementById('calcDaysInput');
    const unitAddon = document.getElementById('calcUnitAddon');
    const baseEl = document.getElementById('calcBaseAmount');
    const bonusRow = document.getElementById('calcBonusRow');
    const bonusEl = document.getElementById('calcBonusAmount');
    const totalEl = document.getElementById('calcTotalAmount');
    const gradeHint = document.getElementById('calcGradeHint');

    if (!cropSelect || !weightInput || !baseEl || !totalEl) return;

    const sym = @json(setting('locale.currency_symbol', '₹'));
    const bonusEnabled = @json($bonusEnabled);
    const bonusRate = @json($bonusRate);
    const bonusMode = @json($bonusMode);
    const isHindi = @json($isHindi);

    function formatINR(num) {
        return sym + Math.round(num).toLocaleString('en-IN');
    }

    function recalculate() {
        const opt = cropSelect.options[cropSelect.selectedIndex];
        const baseRate = parseFloat(opt ? opt.value : '0') || 0;
        const maxRate = parseFloat(opt ? opt.getAttribute('data-max') : '0') || baseRate;
        const unitLbl = opt ? (opt.getAttribute('data-unit') || '') : '';
        const qty = Math.max(0, parseFloat(weightInput.value) || 0);
        const days = daysInput ? (parseInt(daysInput.value, 10) || 0) : 0;

        if (unitAddon && unitLbl) unitAddon.textContent = unitLbl;

        const baseAmt = baseRate * qty;
        let bonusAmt = 0;
        if (bonusEnabled && days > 0 && bonusRate > 0) {
            const effectivePct = bonusMode === 'flat' ? bonusRate : (bonusRate * (days / 30));
            bonusAmt = (baseAmt * effectivePct) / 100;
        }

        baseEl.textContent = formatINR(baseAmt);
        if (bonusEl) bonusEl.textContent = '+' + formatINR(bonusAmt);
        if (bonusRow) bonusRow.style.opacity = days > 0 ? '1' : '0.45';
        totalEl.textContent = formatINR(baseAmt + bonusAmt);

        if (gradeHint) {
            if (maxRate > baseRate && qty > 0) {
                const topTotal = (maxRate * qty) + bonusAmt;
                gradeHint.textContent = isHindi
                    ? 'उत्तम ग्रेड-A भाव (' + sym + maxRate.toLocaleString('en-IN') + ') पर अधिकतम अनुमानित राशि: ' + formatINR(topTotal)
                    : 'Up to ' + formatINR(topTotal) + ' for top Grade-A quality (' + sym + maxRate.toLocaleString('en-IN') + ')';
            } else {
                gradeHint.textContent = '';
            }
        }
    }

    cropSelect.addEventListener('change', recalculate);
    weightInput.addEventListener('input', recalculate);
    if (daysInput) daysInput.addEventListener('change', recalculate);
    recalculate();
});
</script>
@endpush
