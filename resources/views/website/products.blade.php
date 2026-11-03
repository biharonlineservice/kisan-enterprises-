@extends('layouts.website')

@section('title', (app()->getLocale() === 'hi' ? 'उत्पाद एवं थोक अनाज आपूर्ति (राइस व फ्लोर मिल्स) — ' : 'Bulk Grain Supply for Rice Mills & Institutional Buyers — ') . setting('business.name', 'Kisan Enterprises'))

@section('content')
@php
    $isHindi = app()->getLocale() === 'hi';
    $bizName = setting('business.name', 'Kisan Enterprises');
    $bizPhone = setting('business.phone', '+91 98765 43210');
    $bizAddress = setting('business.address', 'Agricultural Trading Hub, Uttar Pradesh');

    $cropMeta = function (\App\Models\Crop $c) use ($isHindi): array {
        $lower = strtolower($c->name . ' ' . $c->code);
        $moistures = $c->varieties
            ->pluck('standard_moisture_pct')
            ->filter(fn ($v) => $v !== null && (float) $v > 0)
            ->map(fn ($v) => (float) $v);

        if ($moistures->isNotEmpty()) {
            $minM = number_format($moistures->min(), 1);
            $maxM = number_format($moistures->max(), 1);
            $moistureText = $minM === $maxM ? "≤ {$minM}%" : "≤ {$minM}% – {$maxM}%";
        } else {
            $moistureText = '≤ 14.0%';
        }

        if (str_contains($lower, 'paddy') || str_contains($lower, 'rice')) {
            return [
                'icon' => 'bi-flower3',
                'theme' => 'emerald',
                'moisture' => $moistureText,
                'season' => $isHindi ? 'खरीफ पीक व वर्षभर गोदाम स्टॉक' : 'Kharif Peak & Year-Round Godown Lots',
                'packaging' => $isHindi ? '50/60 Kg जूट बोरे या बल्क लोडिंग' : '50/60 Kg Jute Gunny Bags or Loose Bulk',
                'industry' => $isHindi
                    ? 'राइस मिल्स, पारबॉइल्ड प्लांट्स व निर्यातक'
                    : 'Rice Mills, Parboiled Processors & Exporters',
            ];
        }

        if (str_contains($lower, 'wheat')) {
            return [
                'icon' => 'bi-brightness-high-fill',
                'theme' => 'amber',
                'moisture' => $moistureText,
                'season' => $isHindi ? 'रबी हार्वेस्ट व वर्षभर ग्रेड स्टॉक' : 'Rabi Harvest & Year-Round Graded Stock',
                'packaging' => $isHindi ? '50 Kg जूट/PP बोरे या ट्रक लोड' : '50 Kg Jute/PP Bags or Full Truckload',
                'industry' => $isHindi
                    ? 'रोलर फ्लोर मिल्स, आटा चक्की, बिस्किट व बेकरी प्लांट्स'
                    : 'Roller Flour Mills, Chakki Plants, Biscuit & Bakery Units',
            ];
        }

        if (str_contains($lower, 'maize') || str_contains($lower, 'corn')) {
            return [
                'icon' => 'bi-grid-3x3-gap-fill',
                'theme' => 'gold',
                'moisture' => $moistureText,
                'season' => $isHindi ? 'खरीफ व रबी दोनों चक्र उपलब्ध' : 'Dual-Season Kharif & Spring Supply',
                'packaging' => $isHindi ? '50/60 Kg HDPE/जूट बोरे या बल्क' : '50/60 Kg HDPE/Jute Bags or Bulk Dispatch',
                'industry' => $isHindi
                    ? 'स्टार्च मैन्युफैक्चरर, पोल्ट्री व कैटल फीड प्लांट्स'
                    : 'Starch Manufacturers, Poultry & Cattle Feed Plants',
            ];
        }

        if (str_contains($lower, 'mustard') || str_contains($lower, 'sarson')) {
            return [
                'icon' => 'bi-droplet-half',
                'theme' => 'olive',
                'moisture' => $moistureText,
                'season' => $isHindi ? 'रबी तेलहन सीजन व सुरक्षित गोदाम लॉट' : 'Rabi Oilseed Window & Dry Godown Lots',
                'packaging' => $isHindi ? '50 Kg नए जूट बोरे (उच्च तेल ग्रेड)' : '50 Kg Sound Jute Bags (High-Oil Grade)',
                'industry' => $isHindi
                    ? 'कच्ची घानी एक्सपेलर व सॉल्वेंट एक्सट्रैक्शन प्लांट्स'
                    : 'Kachchi Ghani Oil Expellers & Solvent Extraction Plants',
            ];
        }

        return [
            'icon' => 'bi-box-seam-fill',
            'theme' => 'emerald',
            'moisture' => $moistureText,
            'season' => $isHindi ? 'वर्षभर व्यावसायिक आपूर्ति' : 'Year-Round Commercial Availability',
            'packaging' => $isHindi ? '50 Kg मानक बोरे या बल्क डिस्पैच' : '50 Kg Standard Bags or Loose Bulk',
            'industry' => $isHindi ? 'थोक व्यापारी एवं फूड प्रोसेसिंग यूनिट्स' : 'Wholesalers & Food Processing Plants',
        ];
    };
@endphp

{{-- Hero Banner + Commercial Dispatch Snapshot --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            {{-- Left Column --}}
            <div class="col-12 col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill prod-eyebrow mb-3">
                    <i class="bi bi-buildings-fill text-success"></i>
                    <span>{{ $isHindi ? 'व्यावसायिक थोक अनाज आपूर्ति (B2B)' : 'Commercial Bulk Grain Supply (B2B)' }}</span>
                </div>

                <h1 class="prod-hero-title fw-bold text-dark mb-3">
                    @if($isHindi)
                        राइस मिल, फ्लोर मिल एवं प्रोसेसिंग प्लांट्स के लिए ग्रेड-प्रमाणित थोक अनाज आपूर्ति
                    @else
                        Moisture-Tested Bulk Grain Supply for Mills, Processors & Institutional Buyers
                    @endif
                </h1>

                <p class="prod-lead text-muted mb-4">
                    @if($isHindi)
                        {{ $bizName }} सीधे किसानों से खरीदे गए उच्च गुणवत्ता वाले धान, गेहूं, मक्का और सरसों को किस्म-वार अलग गोदामों से प्रमाणित धर्मकांटा पर्ची, नमी जांच और GST/ई-वे बिल के साथ देशभर की मिलों को सप्लाई करता है।
                    @else
                        {{ $bizName }} supplies graded, variety-segregated Paddy, Wheat, Maize, and Mustard directly from our mandi godowns to rice mills, roller flour mills, feed plants, and oilseed processors with certified weighment and compliant GST dispatch.
                    @endif
                </p>

                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 gap-sm-3 mb-4">
                    <a href="#bulk-quote-form" class="btn btn-primary btn-lg px-4 shadow-sm prod-cta-btn">
                        <i class="bi bi-send-check-fill"></i>
                        {{ $isHindi ? 'थोक कोटेशन मंगवाएं' : 'Request Bulk Quotation' }}
                    </a>
                    <a href="#commodity-catalogue" class="btn btn-outline-primary bg-white btn-lg px-4 prod-cta-btn">
                        <i class="bi bi-grid-1x2-fill"></i>
                        {{ $isHindi ? 'उपलब्ध अनाज व ग्रेड देखें' : 'View Commodity Catalogue' }}
                    </a>
                </div>

                {{-- Buyer Industries Quick Pill Strip --}}
                <div class="p-3 rounded-4 bg-white border shadow-sm">
                    <div class="small fw-bold text-dark mb-2">
                        <i class="bi bi-building-gear text-success me-1"></i>
                        {{ $isHindi ? 'हमारे प्रमुख औद्योगिक खरीदार:' : 'Key Processing Industries We Serve:' }}
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-check2-circle text-success me-1"></i>{{ $isHindi ? 'राइस व पारबॉइल्ड मिल्स' : 'Rice & Parboiled Mills' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-check2-circle text-primary me-1"></i>{{ $isHindi ? 'रोलर फ्लोर मिल्स व बेकरी' : 'Roller Flour & Bakery Units' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-check2-circle text-warning-emphasis me-1"></i>{{ $isHindi ? 'पोल्ट्री/कैटल फीड व स्टार्च प्लांट' : 'Feed & Starch Plants' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-check2-circle text-success me-1"></i>{{ $isHindi ? 'ऑयल एक्सपेलर प्लांट्स' : 'Oil Expeller Units' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right Column: B2B Supply Readiness & Logistics Card --}}
            <div class="col-12 col-lg-5">
                <div class="card prod-snapshot-card border-0 overflow-hidden">
                    <div class="prod-snapshot-header text-white py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="prod-header-icon">
                                <i class="bi bi-truck-front-fill"></i>
                            </span>
                            <div>
                                <div class="fw-bold lh-sm">{{ $isHindi ? 'थोक डिस्पैच एवं लॉट क्षमता' : 'Commercial Supply Snapshot' }}</div>
                                <small class="text-white-50 d-block" style="font-size: 0.72rem;">
                                    {{ $isHindi ? 'गोदाम स्टॉक व डायरेक्ट मंडी ट्रांजिट' : 'Ex-Godown & Direct Mandi Transit Dispatch' }}
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-white text-success rounded-pill px-2 py-1 small fw-bold">
                            {{ $isHindi ? 'GST व ई-वे बिल' : 'GST Ready' }}
                        </span>
                    </div>

                    <div class="card-body p-3 p-sm-4 bg-white">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="prod-metric-tile h-100">
                                    <div class="prod-metric-val text-success tabular-nums">
                                        {{ $totalCapacityQtl > 0 ? indian_number($totalCapacityQtl, 0) . '+' : '24,000+' }}
                                    </div>
                                    <div class="prod-metric-lbl">
                                        {{ $isHindi ? 'क्विंटल गोदाम क्षमता' : 'Qtl Segregated Storage' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="prod-metric-tile h-100">
                                    <div class="prod-metric-val text-dark tabular-nums">
                                        150 – 2,500+
                                    </div>
                                    <div class="prod-metric-lbl">
                                        {{ $isHindi ? 'क्विंटल प्रति लॉट / अनुबंध' : 'Qtl Lot & Contract Size' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="prod-metric-tile h-100">
                                    <div class="prod-metric-val text-primary tabular-nums">
                                        {{ $crops->count() }} / {{ max($varietiesCount, $crops->count()) }}
                                    </div>
                                    <div class="prod-metric-lbl">
                                        {{ $isHindi ? 'फसलें व प्रमुख किस्में' : 'Commodities & Varieties' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="prod-metric-tile h-100">
                                    <div class="prod-metric-val text-warning-emphasis tabular-nums">
                                        100%
                                    </div>
                                    <div class="prod-metric-lbl">
                                        {{ $isHindi ? 'नमी व धर्मकांटा प्रमाणित' : 'Moisture & Slip Verified' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3 bg-light border p-3 small">
                            <div class="fw-bold text-dark mb-2">
                                <i class="bi bi-clipboard2-check-fill text-success me-1"></i>
                                {{ $isHindi ? 'हर डिस्पैच के साथ मिलने वाले दस्तावेज़:' : 'Included With Every Commercial Dispatch:' }}
                            </div>
                            <ul class="list-unstyled mb-0 d-grid gap-1 text-muted">
                                <li><i class="bi bi-check2 text-success fw-bold me-1"></i>{{ $isHindi ? 'कंप्यूटराइज्ड धर्मकांटा ग्रॉस/टेयर व नेट पर्ची' : 'Computerized Weighbridge Gross/Tare/Net Slip' }}</li>
                                <li><i class="bi bi-check2 text-success fw-bold me-1"></i>{{ $isHindi ? 'बैच नमी प्रतिशत व ग्रेड सत्यापन रिपोर्ट' : 'Batch Moisture Percentage & Grade Certificate' }}</li>
                                <li><i class="bi bi-check2 text-success fw-bold me-1"></i>{{ $isHindi ? 'पक्का टैक्स/सेल इनवॉइस, गेट पास एवं ई-वे बिल' : 'Official Tax/Sale Invoice, Gate Pass & E-Way Bill' }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer bg-light border-top px-3 px-sm-4 py-2 d-flex justify-content-between align-items-center small">
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $bizPhone) }}" class="text-decoration-none text-dark fw-semibold">
                            <i class="bi bi-telephone-outbound-fill text-success me-1"></i>{{ $bizPhone }}
                        </a>
                        <a href="#bulk-quote-form" class="fw-semibold text-primary text-decoration-none">
                            {{ $isHindi ? 'रेट पूछें →' : 'Get Rate Quote →' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3 Commercial Supply Capabilities --}}
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container py-2">
        <div class="row g-3 g-md-4">
            <div class="col-12 col-md-4">
                <div class="feature-card prod-accent-green p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="prod-pill-icon bg-success-subtle text-success">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'सिंगल ट्रक से बड़े अनुबंध तक' : 'Commercial Bulk Batches' }}</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? '150–250 क्विंटल के सिंगल ट्रक लोड से लेकर हज़ारों क्विंटल के नियमित मिल सप्लाई अनुबंध तक समयबद्ध डिलीवरी।' : 'Supplying single truckloads (150–250 Qtl) up to multi-thousand quintal recurring institutional mill contracts.' }}
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card prod-accent-blue p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="prod-pill-icon bg-primary-subtle text-primary">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'किस्म-वार अलग गोदाम व ग्रेडिंग' : 'Strict Variety & Moisture Grading' }}</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'प्रत्येक किस्म (जैसे बासमती 1121, शरबती, पूसा बोल्ड) की अलग स्टैकिंग, मशीन क्लीनिंग और प्रमाणित नमी स्तर।' : 'Segregated godown stacks for distinct varieties, machine-winnowed purity, and verified laboratory moisture levels.' }}
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card prod-accent-amber p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="prod-pill-icon bg-warning-subtle text-warning-emphasis">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'तेज़ लोडिंग, गेट पास व ई-वे बिल' : 'Fleet Logistics & E-Way Bills' }}</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'गोदाम व रेलवे साइडिंग से तेज़ वाहन लोडिंग, कंप्यूटराइज्ड गेट पास, जीएसटी बिल और ई-वे बिल की पूर्ण सुविधा।' : 'Rapid loading from mandi godowns or direct transit with computerized gate passes, sale invoices, and compliant E-Way bills.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Visual Commodity Supply Cards + Institutional Spec Matrix --}}
<section id="commodity-catalogue" class="py-4 py-md-5 bg-light border-bottom">
    <div class="container py-2 py-md-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
            <div>
                <span class="prod-section-eyebrow">
                    <i class="bi bi-collection-fill me-1"></i>{{ $isHindi ? 'थोक कमोडिटी कैटलॉग' : 'Commodity Supply Catalogue' }}
                </span>
                <h2 class="h3 fw-bold mt-1 mb-0">
                    {{ $isHindi ? 'मिल एवं प्रोसेसिंग के लिए उपलब्ध अनाज व ग्रेड स्पेसिफिकेशन' : 'Graded Commodities Available for Bulk Dispatch' }}
                </h2>
            </div>
            <span class="badge bg-white text-dark border px-3 py-2 align-self-start align-self-md-end">
                <i class="bi bi-shield-check text-success me-1"></i>
                {{ $isHindi ? 'मशीन क्लीन एवं नमी प्रमाणित लॉट' : 'Machine-Cleaned & Moisture Verified Lots' }}
            </span>
        </div>

        {{-- Interactive 2-Column Visual Commodity Cards (Great on both Desktop & Mobile!) --}}
        <div class="row g-3 g-lg-4 mb-4">
            @forelse($crops as $c)
                @php $meta = $cropMeta($c); @endphp
                <div class="col-12 col-lg-6">
                    <div class="crop-card prod-commodity-card h-100">
                        <div class="prod-commodity-top prod-theme-{{ $meta['theme'] }} px-3 px-sm-4 py-3 d-flex justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <span class="prod-crop-avatar">
                                    <i class="bi {{ $meta['icon'] }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="h5 fw-bold text-dark mb-0 text-truncate">{{ $c->name }}</h3>
                                    <div class="small text-muted">
                                        {{ $isHindi ? 'कोड:' : 'Code:' }} <span class="fw-semibold">{{ $c->code }}</span>
                                        @if($c->hsn_code)
                                            · <span class="fw-semibold">HSN {{ $c->hsn_code }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="badge rounded-pill bg-white text-dark border px-3 py-2 fw-semibold flex-shrink-0">
                                <i class="bi bi-droplet-half text-info me-1"></i>{{ $isHindi ? 'नमी' : 'Moisture' }} {{ $meta['moisture'] }}
                            </span>
                        </div>

                        <div class="p-3 p-sm-4 d-flex flex-column flex-grow-1">
                            @if($c->description)
                                <p class="text-muted small mb-3">{{ $c->description }}</p>
                            @endif

                            {{-- Varieties & Grades --}}
                            <div class="mb-3">
                                <div class="text-muted small fw-semibold mb-2" style="font-size: 0.75rem;">
                                    {{ $isHindi ? 'उपलब्ध प्रमुख किस्में एवं ग्रेड:' : 'Available Varieties & Commercial Grades:' }}
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($c->varieties as $v)
                                        <span class="prod-variety-chip">
                                            <span class="fw-semibold text-dark">{{ $v->name }}</span>
                                            @if($v->default_grade)
                                                <span class="prod-grade-dot">· {{ $v->default_grade }}</span>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="prod-variety-chip">
                                            <span class="fw-semibold text-dark">{{ $isHindi ? 'मानक कमर्शियल ग्रेड (FAQ)' : 'Standard Commercial Grade (FAQ)' }}</span>
                                        </span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Specs Grid --}}
                            <div class="row g-2 mt-auto pt-3 border-top small">
                                <div class="col-12 col-sm-6">
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $isHindi ? 'पैकेजिंग व डिस्पैच' : 'Packaging & Loading' }}</div>
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-box-seam text-success me-1"></i>{{ $meta['packaging'] }}
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $isHindi ? 'प्रमुख उपयोग उद्योग' : 'Target Processing Units' }}</div>
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-buildings text-primary me-1"></i>{{ $meta['industry'] }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-2 mt-3 pt-2 border-top">
                                <span class="text-muted small" style="font-size: 0.75rem;">
                                    <i class="bi bi-calendar3 text-success me-1"></i>{{ $meta['season'] }}
                                </span>
                                <a href="#bulk-quote-form"
                                   class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold js-select-crop-quote"
                                   data-crop="{{ $c->name }}"
                                   data-moisture="{{ $meta['moisture'] }}"
                                   data-varieties="{{ $c->varieties->pluck('name')->take(2)->join(', ') }}">
                                    <i class="bi bi-chat-square-quote me-1"></i>{{ $isHindi ? 'कोटेशन लें' : 'Get Quote' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border rounded-4 p-4 text-center text-muted bg-white">
                        {{ $isHindi ? 'थोक आपूर्ति सूची अपडेट की जा रही है।' : 'Commodity supply catalogue is being updated.' }}
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Desktop Institutional Quick-Comparison Table (>= 992px) --}}
        @if($crops->isNotEmpty())
            <div class="card shadow-sm border rounded-4 overflow-hidden bg-white d-none d-lg-block">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        <i class="bi bi-table text-success me-2"></i>{{ $isHindi ? 'त्वरित तकनीकी तुलना तालिका (मिल परचेज डेस्क हेतु)' : 'Quick Technical Specification Matrix (For Mill Purchase Desks)' }}
                    </span>
                    <span class="text-muted small">{{ $isHindi ? 'इकाई: क्विंटल (100 Kg) / मीट्रिक टन' : 'Unit: Quintal (100 Kg) / Metric Ton' }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">{{ $isHindi ? 'कमोडिटी' : 'Commodity' }}</th>
                                <th>HSN</th>
                                <th>{{ $isHindi ? 'उपलब्ध किस्में' : 'Varieties Available' }}</th>
                                <th>{{ $isHindi ? 'अधिकतम नमी' : 'Moisture Spec' }}</th>
                                <th>{{ $isHindi ? 'आपूर्ति चक्र' : 'Supply Window' }}</th>
                                <th class="pe-4">{{ $isHindi ? 'लक्षित उद्योग' : 'Target Industry' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($crops as $c)
                                @php $meta = $cropMeta($c); @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $c->name }}</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">{{ $c->hsn_code ?: '—' }}</span></td>
                                    <td class="small">{{ $c->varieties->isNotEmpty() ? $c->varieties->pluck('name')->join(', ') : 'Standard Commercial' }}</td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $meta['moisture'] }}</span></td>
                                    <td class="small text-muted">{{ $meta['season'] }}</td>
                                    <td class="pe-4 small text-dark">{{ $meta['industry'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- Institutional Dispatch Process + Bulk Quotation Request Form --}}
<section id="bulk-quote-form" class="py-4 py-md-5 bg-white">
    <div class="container py-2 py-md-3">
        <div class="row g-4 g-lg-5 align-items-start">
            {{-- Left Column: 4-Step B2B Dispatch Workflow & Godown Network --}}
            <div class="col-12 col-lg-5">
                <span class="prod-section-eyebrow">{{ $isHindi ? 'आसान ऑर्डर एवं डिस्पैच प्रक्रिया' : 'Order-to-Dispatch Workflow' }}</span>
                <h2 class="h3 fw-bold mt-1 mb-3">
                    {{ $isHindi ? 'थोक ऑर्डर कैसे बुक और डिस्पैच होता है?' : 'How Institutional Bulk Dispatch Works' }}
                </h2>
                <p class="text-muted small mb-4">
                    {{ $isHindi ? 'हमारी कमर्शियल ट्रेडिंग डेस्क मिल खरीदारों को लॉट उपलब्धता, सैंपल ग्रेड और वाहन डिस्पैच की तेज़ सुविधा देती है।' : 'Our commercial trading desk coordinates lot allocation, moisture verification, and fleet loading with complete transparency.' }}
                </p>

                <div class="d-grid gap-3 mb-4">
                    <div class="prod-step-item">
                        <div class="prod-step-badge">1</div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'आवश्यकता व ग्रेड पूछताछ' : 'Requirement & Grade Inquiry' }}</h6>
                            <p class="text-muted small mb-0">{{ $isHindi ? 'आवश्यक फसल, किस्म, नमी सीमा और मात्रा (क्विंटल/टन) फॉर्म या फोन द्वारा साझा करें।' : 'Share required crop, variety, moisture limit, and tonnage via the quotation desk or phone.' }}</p>
                        </div>
                    </div>

                    <div class="prod-step-item">
                        <div class="prod-step-badge">2</div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'गोदाम लॉट कोटेशन व रेट लॉक' : 'Ex-Godown Quote & Lot Allocation' }}</h6>
                            <p class="text-muted small mb-0">{{ $isHindi ? 'ताज़ा स्टॉक उपलब्धता के आधार पर एक्स-गोदाम या डिलीवरी दर तय कर लॉट रिज़र्व किया जाता है।' : 'Receive live ex-godown or FOR destination pricing and lock your dedicated warehouse batch.' }}</p>
                        </div>
                    </div>

                    <div class="prod-step-item">
                        <div class="prod-step-badge">3</div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'नमी परीक्षण व धर्मकांटा लोडिंग' : 'Moisture Check & Weighbridge Loading' }}</h6>
                            <p class="text-muted small mb-0">{{ $isHindi ? 'लोडिंग से पहले बैच की नमी जांच और प्रमाणित धर्मकांटा पर खाली व भरी गाड़ी की तौल होती है।' : 'Pre-dispatch batch moisture check followed by calibrated tare and gross truck weighment.' }}</p>
                        </div>
                    </div>

                    <div class="prod-step-item prod-step-highlight">
                        <div class="prod-step-badge bg-success text-white">4</div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'गेट पास, GST बिल व रवानगी' : 'Gate Pass, GST Invoice & Dispatch' }}</h6>
                            <p class="text-muted small mb-0">{{ $isHindi ? 'कंप्यूटराइज्ड गेट पास, सेल इनवॉइस और ई-वे बिल के साथ वाहन तुरंत आपकी मिल के लिए रवाना।' : 'Immediate dispatch with printed gate pass, official tax/sale invoice, and E-Way bill.' }}</p>
                        </div>
                    </div>
                </div>

                @if($warehouses->isNotEmpty())
                    <div class="p-3 rounded-4 bg-light border">
                        <div class="small fw-bold text-dark mb-2">
                            <i class="bi bi-house-gear-fill text-success me-1"></i>
                            {{ $isHindi ? 'सक्रिय लोडिंग गोदाम व डिस्पैच केंद्र:' : 'Active Loading Depots & Sidings:' }}
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($warehouses as $wh)
                                <span class="badge bg-white text-dark border px-3 py-2 fw-normal">
                                    <strong class="text-success">{{ $wh->code }}</strong> · {{ $wh->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Request a Bulk Supply Quotation Form --}}
            <div class="col-12 col-lg-7">
                <div class="card prod-quote-card border-0 rounded-4 overflow-hidden">
                    <div class="prod-quote-header text-white p-3 p-sm-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="badge bg-white text-success rounded-pill px-3 py-1 small fw-bold mb-2">
                                    <i class="bi bi-lightning-charge-fill me-1"></i>{{ $isHindi ? 'सीधे ट्रेडिंग डेस्क से जुड़ें' : 'Direct Commercial Desk' }}
                                </span>
                                <h3 class="h4 fw-bold mb-1">
                                    {{ $isHindi ? 'थोक अनाज आपूर्ति कोटेशन का अनुरोध करें' : 'Request a Bulk Supply Quotation' }}
                                </h3>
                                <p class="small text-white-50 mb-0">
                                    {{ $isHindi ? 'वर्तमान लॉट उपलब्धता, एक्स-गोदाम भाव और डिस्पैच शेड्यूल के लिए नीचे विवरण भरें।' : 'Connect with our commercial trading desk for current lot availability, ex-godown rates, and dispatch timelines.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-sm-4 p-md-5 bg-white">
                        @if(session('success'))
                            <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger rounded-3 small mb-4">
                                <div class="fw-bold mb-1">{{ $isHindi ? 'कृपया फॉर्म विवरण जांचें:' : 'Please check the form details:' }}</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('website.contact.store') }}">
                            @csrf
                            <input type="hidden" name="inquiry_type" value="bulk_buyer">

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="bulkQuoteName" class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'कंपनी / मिल / फर्म का नाम' : 'Company / Mill Name' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-building text-muted"></i></span>
                                        <input type="text"
                                               id="bulkQuoteName"
                                               name="name"
                                               value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror"
                                               placeholder="{{ $isHindi ? 'जैसे: गंगा फ्लोर मिल्स प्रा. लि.' : 'e.g. Ganga Flour Mills Pvt Ltd' }}"
                                               required>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="bulkQuotePhone" class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'संपर्क मोबाइल नंबर' : 'Phone / WhatsApp Number' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-telephone text-muted"></i></span>
                                        <input type="tel"
                                               id="bulkQuotePhone"
                                               name="phone"
                                               value="{{ old('phone') }}"
                                               class="form-control @error('phone') is-invalid @enderror"
                                               placeholder="{{ $isHindi ? '10-अंकीय मोबाइल नंबर' : '10-digit mobile number' }}"
                                               inputmode="tel"
                                               required>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="bulkQuoteEmail" class="form-label small fw-semibold">
                                        {{ $isHindi ? 'आधिकारिक ईमेल (वैकल्पिक)' : 'Official Email Address' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                        <input type="email"
                                               id="bulkQuoteEmail"
                                               name="email"
                                               value="{{ old('email') }}"
                                               class="form-control @error('email') is-invalid @enderror"
                                               placeholder="purchase@mill.com">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="bulkQuoteSubject" class="form-label small fw-semibold">
                                        {{ $isHindi ? 'डिलीवरी शहर / गंतव्य स्थान' : 'Target Delivery Destination' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt text-muted"></i></span>
                                        <input type="text"
                                               id="bulkQuoteSubject"
                                               name="subject"
                                               value="{{ old('subject') }}"
                                               class="form-control @error('subject') is-invalid @enderror"
                                               placeholder="{{ $isHindi ? 'जैसे: वाराणसी, कानपुर, बरेली, दिल्ली NCR' : 'e.g. Varanasi, Kanpur, Bareilly, Delhi NCR' }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-1">
                                        <label for="bulkQuoteMessage" class="form-label required small fw-semibold mb-0">
                                            {{ $isHindi ? 'आवश्यक फसल, किस्म एवं मात्रा (क्विंटल / टन में)' : 'Crop, Variety & Volume Required (in Quintals / MT)' }}
                                        </label>
                                        <span class="text-muted" style="font-size: 0.72rem;">
                                            {{ $isHindi ? 'त्वरित चयन:' : 'Quick Fill:' }}
                                            @foreach($crops as $qc)
                                                <button type="button"
                                                        class="btn btn-link btn-sm p-0 ms-1 text-decoration-none fw-semibold js-quick-crop-tag"
                                                        style="font-size: 0.72rem;"
                                                        data-crop="{{ $qc->name }}">
                                                    +{{ strtok($qc->name, ' ') }}
                                                </button>
                                            @endforeach
                                        </span>
                                    </div>
                                    <textarea id="bulkQuoteMessage"
                                              name="message"
                                              rows="4"
                                              class="form-control @error('message') is-invalid @enderror"
                                              placeholder="{{ $isHindi ? 'आवश्यक अनाज व मात्रा लिखें (जैसे: 500 क्विंटल शरबती गेहूं, नमी ≤ 12%, 50 Kg जूट बोरे, एक्स-गोदाम या मिल डिलीवरी)...' : 'Specify required crop & quantity (e.g. 500 Qtl Sharbati Wheat, moisture <= 12%, 50 Kg Jute bags, payment & delivery schedule)...' }}"
                                              required
                                              minlength="10">{{ old('message') }}</textarea>
                                </div>

                                <div class="col-12 pt-2">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                        <small class="text-muted">
                                            <i class="bi bi-shield-lock text-success me-1"></i>
                                            {{ $isHindi ? 'हमारी टीम कार्य दिवसों में शीघ्र कोटेशन साझा करती है।' : 'Prompt response during mandi trading hours.' }}
                                        </small>
                                        <button type="submit" class="btn btn-primary btn-lg px-4 fw-semibold shadow-sm">
                                            <i class="bi bi-send-fill me-1"></i>
                                            {{ $isHindi ? 'थोक कोटेशन भेजें' : 'Submit Bulk Request' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .prod-eyebrow {
        background: rgba(33, 134, 75, 0.11);
        border: 1px solid rgba(33, 134, 75, 0.24);
        color: #155b36;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .prod-section-eyebrow {
        display: inline-block;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #1e8449;
    }
    .prod-hero-title {
        font-size: clamp(1.7rem, 3.3vw, 2.55rem);
        line-height: 1.2;
        letter-spacing: -0.02em;
    }
    .prod-lead {
        font-size: clamp(0.95rem, 1.35vw, 1.08rem);
        line-height: 1.65;
    }
    @media (max-width: 575.98px) {
        .prod-cta-btn {
            width: 100%;
            justify-content: center;
            font-size: 0.96rem;
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
        }
    }
    .prod-snapshot-card {
        border-radius: 18px;
        box-shadow: 0 16px 38px rgba(17, 68, 39, 0.12);
        border: 1px solid rgba(33, 134, 75, 0.22) !important;
    }
    .prod-snapshot-header {
        background: linear-gradient(135deg, #1b7a43 0%, #114b2a 100%);
    }
    .prod-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.16);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .prod-metric-tile {
        background: #f6fbf8;
        border: 1px solid rgba(33, 134, 75, 0.16);
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
    }
    .prod-metric-val {
        font-size: 1.18rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .prod-metric-lbl {
        font-size: 0.73rem;
        color: #5b6d62;
        margin-top: 0.15rem;
    }
    .prod-pill-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .prod-accent-green { border-left: 4px solid #1e8449 !important; }
    .prod-accent-blue { border-left: 4px solid #0d6efd !important; }
    .prod-accent-amber { border-left: 4px solid #e6a800 !important; }

    .prod-commodity-card {
        border-radius: 18px;
    }
    .prod-commodity-top {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .prod-theme-emerald { background: linear-gradient(90deg, rgba(33, 134, 75, 0.12) 0%, rgba(33, 134, 75, 0.03) 100%); }
    .prod-theme-amber { background: linear-gradient(90deg, rgba(244, 183, 64, 0.2) 0%, rgba(244, 183, 64, 0.04) 100%); }
    .prod-theme-gold { background: linear-gradient(90deg, rgba(255, 193, 7, 0.16) 0%, rgba(255, 193, 7, 0.03) 100%); }
    .prod-theme-olive { background: linear-gradient(90deg, rgba(116, 159, 45, 0.16) 0%, rgba(116, 159, 45, 0.03) 100%); }

    .prod-crop-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #ffffff;
        color: #186a3b;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .prod-variety-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: #f8faf8;
        border: 1px solid #dce7df;
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
        font-size: 0.76rem;
    }
    .prod-grade-dot {
        color: #186a3b;
        font-weight: 600;
        font-size: 0.72rem;
    }
    .prod-step-item {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        padding: 0.95rem 1.05rem;
        border-radius: 14px;
        background: #f8fbf9;
        border: 1px solid #e2ece5;
    }
    .prod-step-highlight {
        background: #eef8f1;
        border-color: rgba(30, 132, 73, 0.3);
    }
    .prod-step-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #1e8449;
        color: #1e8449;
        font-weight: 800;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .prod-quote-card {
        box-shadow: 0 16px 40px rgba(17, 68, 39, 0.10);
        border: 1px solid rgba(33, 134, 75, 0.22) !important;
    }
    .prod-quote-header {
        background: linear-gradient(135deg, #186a3b 0%, #0f4225 100%);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const msgInput = document.getElementById('bulkQuoteMessage');
    const nameInput = document.getElementById('bulkQuoteName');
    const isHindi = @json($isHindi);

    document.querySelectorAll('.js-select-crop-quote').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!msgInput) return;
            const crop = btn.getAttribute('data-crop') || '';
            const moisture = btn.getAttribute('data-moisture') || '';
            const varieties = btn.getAttribute('data-varieties') || '';

            const line = isHindi
                ? 'आवश्यक फसल: ' + crop + (varieties ? ' (' + varieties + ')' : '') + ', मानक नमी ' + moisture + ' — अनुमानित मात्रा: ___ क्विंटल।'
                : 'Required Commodity: ' + crop + (varieties ? ' (' + varieties + ')' : '') + ', Moisture ' + moisture + ' — Required Quantity: ___ Quintals.';

            msgInput.value = line;
            setTimeout(function () {
                if (nameInput && !nameInput.value) {
                    nameInput.focus();
                } else {
                    msgInput.focus();
                }
            }, 350);
        });
    });

    document.querySelectorAll('.js-quick-crop-tag').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!msgInput) return;
            const crop = btn.getAttribute('data-crop') || '';
            const prefix = msgInput.value.trim() ? msgInput.value.trim() + '\n' : '';
            msgInput.value = prefix + crop + ' — ___ Qtl';
            msgInput.focus();
        });
    });
});
</script>
@endpush
