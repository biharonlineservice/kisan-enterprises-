@extends('layouts.website')

@section('title', (app()->getLocale() === 'hi' ? 'हमारे बारे में — ' : 'About Us — ') . setting('website.brand_name', 'KisanHub'))

@section('content')
@php
    $isHindi = app()->getLocale() === 'hi';
    $bizName = setting('website.brand_name', 'KisanHub');
    $customAboutSnippet = \App\Models\WebsiteContent::get('about_snippet');
    $bonusEnabled = (string) setting('procurement.delayed_bonus_enabled', '1') === '1' && (float) setting('procurement.delayed_bonus_rate', '1.00') > 0;
    $bonusRateFormatted = number_format((float) setting('procurement.delayed_bonus_rate', '1.00'), 2);
@endphp

{{-- Hero Banner + Live Key Stats --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-start g-4 g-lg-5">
            <div class="col-12 col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill about-eyebrow mb-3">
                    <i class="bi bi-award-fill text-success"></i>
                    <span>{{ $isHindi ? 'संस्था का परिचय एवं विश्वास' : 'Company Profile & Heritage' }}</span>
                </div>

                <h1 class="about-hero-title fw-bold text-dark mb-3">
                    @if($isHindi)
                        किसानों का सच्चा साथी, मिलों का विश्वसनीय आपूर्तिकर्ता
                    @else
                        About {{ $bizName }} — Transparent Grain Trading
                    @endif
                </h1>

                <p class="about-lead text-muted mb-4">
                    @if($customAboutSnippet)
                        {{ $customAboutSnippet }}
                    @elseif($isHindi)
                        {{ $bizName }} एक आधुनिक कृषि व्यापारिक प्रतिष्ठान है जो क्षेत्रीय किसान भाइयों को प्रमाणित कंप्यूटर धर्मकांटा तौल, पारदर्शी गुणवत्ता जांच और तुरंत भुगतान के साथ देश की प्रमुख राइस मिलों, आटा मिलों एवं खाद्य प्रसंस्करण इकाइयों से सीधे जोड़ता है।
                    @else
                        {{ $bizName }} is a dedicated agricultural trading enterprise built to create a fair, transparent, and modern bridge between hardworking grain farmers and commercial rice mills, flour processors, and bulk buyers.
                    @endif
                </p>

                <div class="about-cta-group mb-3">
                    <a href="{{ route('home') }}#price-trend" class="btn btn-primary about-cta-btn shadow-sm">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>{{ $isHindi ? 'आज का फसल खरीद भाव देखें' : "Check Today's Crop Rates" }}</span>
                    </a>
                    <a href="{{ route('website.farmer.register') }}" class="btn btn-outline-primary bg-white about-cta-btn">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>{{ __('website.nav.farmer_register') }}</span>
                    </a>
                    <a href="{{ route('website.contact') }}" class="btn btn-light border about-cta-btn">
                        <i class="bi bi-telephone-fill text-success"></i>
                        <span>{{ __('website.nav.contact') }}</span>
                    </a>
                </div>
            </div>

            {{-- 2x2 Impact Metrics Grid --}}
            <div class="col-12 col-lg-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="about-stat-card">
                            <div class="about-stat-icon bg-success-subtle text-success">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div class="about-stat-val">100%</div>
                            <div class="about-stat-lbl">{{ $isHindi ? 'डिजिटल धर्मकांटा तौल' : 'Computerized Weighbridge' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card">
                            <div class="about-stat-icon bg-primary-subtle text-primary">
                                <i class="bi bi-layers-fill"></i>
                            </div>
                            <div class="about-stat-val tabular-nums">
                                {{ $totalCapacityQtl > 0 ? indian_number($totalCapacityQtl, 0) : '24,000' }}+
                            </div>
                            <div class="about-stat-lbl">{{ $isHindi ? 'क्विंटल गोदाम क्षमता' : 'Quintals Storage Capacity' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card">
                            <div class="about-stat-icon bg-info-subtle text-info-emphasis">
                                <i class="bi bi-basket2-fill"></i>
                            </div>
                            <div class="about-stat-val tabular-nums">
                                {{ $crops->count() }} {{ $isHindi ? 'फसलें' : 'Crops' }}
                            </div>
                            <div class="about-stat-lbl">
                                {{ $varietiesCount > 0 ? ($isHindi ? "{$varietiesCount}+ प्रमाणित किस्में" : "{$varietiesCount}+ Traded Varieties") : ($isHindi ? 'प्रमुख अनाज व तिलहन' : 'Major Grains & Oilseeds') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card">
                            <div class="about-stat-icon bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div class="about-stat-val">
                                {{ $bonusEnabled ? '+' . $bonusRateFormatted . '%' : ($isHindi ? 'तुरंत' : 'Same-Day') }}
                            </div>
                            <div class="about-stat-lbl">
                                {{ $bonusEnabled ? ($isHindi ? 'देरी भुगतान पर अतिरिक्त बोनस' : 'Delayed Payment Bonus') : ($isHindi ? 'सीधा बैंक/नकद भुगतान' : 'Direct Bank Settlement') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Our Mission & The Kisan Promise --}}
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container py-2 py-md-3">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-12 col-lg-6">
                <span class="about-section-eyebrow">
                    <i class="bi bi-compass me-1"></i>{{ $isHindi ? 'हमारा उद्देश्य एवं कार्यप्रणाली' : 'Our Mission & Approach' }}
                </span>
                <h2 class="h3 fw-bold mt-1 mb-3">
                    {{ $isHindi ? 'पारंपरिक मंडी की समस्याओं का आधुनिक व पारदर्शी समाधान' : 'Solving Traditional Mandi Challenges with Fair Digital Systems' }}
                </h2>
                <p class="text-muted mb-3">
                    @if($isHindi)
                        पारंपरिक अनाज मंडियों में किसानों को अक्सर तौल में गड़बड़ी, मनमानी कटौती और भुगतान के लिए हफ्तों इंतजार जैसी कठिनाइयों का सामना करना पड़ता है। दूसरी ओर राइस मिलों और औद्योगिक खरीदारों को एक समान गुणवत्ता और समय पर डिलीवरी मिलने में दिक्कत आती है।
                    @else
                        In traditional grain markets, farmers frequently face opaque weighment, uncalibrated scales, arbitrary moisture deductions, and long payment delays. Meanwhile, rice mills and processors struggle with inconsistent batch quality and unpredictable dispatch schedules.
                    @endif
                </p>
                <p class="text-muted mb-4">
                    @if($isHindi)
                        <strong>{{ $bizName }}</strong> ने इस अंतर को खत्म करने के लिए प्रमाणित इलेक्ट्रॉनिक धर्मकांटा, डिजिटल मॉइस्चर मीटर, आधुनिक गोदाम और कंप्यूटरीकृत बिलिंग/चालान प्रणाली अपनाई है — ताकि किसान और मिलर्स दोनों पूरे भरोसे के साथ व्यापार कर सकें।
                    @else
                        <strong>{{ $bizName }}</strong> bridges this gap with legal-metrology certified digital weighbridges, calibrated moisture meters, clean multi-godown storage, and automated ERP billing & transport challans — giving both farmers and industrial buyers complete peace of mind.
                    @endif
                </p>

                {{-- Mini Comparison Strip --}}
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="fw-bold small text-danger mb-1">
                                <i class="bi bi-x-circle-fill me-1"></i>{{ $isHindi ? 'पुरानी व्यवस्था की दिक्कतें' : 'Old Market Pain Points' }}
                            </div>
                            <div class="small text-muted">
                                {{ $isHindi ? 'कच्ची पर्ची, अंदाज़े से नमी कटौती, बिचौलियों का कमीशन और देर से भुगतान।' : 'Handwritten slips, guesswork moisture cuts, middleman commissions & delayed payouts.' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 border border-success-subtle bg-success-subtle bg-opacity-50 h-100">
                            <div class="fw-bold small text-success mb-1">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ $isHindi ? 'किसानहब मानक' : 'The KisanHub Standard' }}
                            </div>
                            <div class="small text-dark">
                                {{ $isHindi ? 'कंप्यूटर तौल पर्ची, लाइव वेबसाइट भाव, शून्य छिपा शुल्क और तुरंत भुगतान।' : 'Printed digital weigh slips, live public crop rates, zero hidden charges & fast settlement.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Elevated Kisan Promise Card --}}
            <div class="col-12 col-lg-6">
                <div class="card promise-card border-0 overflow-hidden">
                    <div class="promise-header text-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check fs-4"></i>
                            <span class="fw-bold">{{ $isHindi ? 'हमारा पक्का वादा (The Kisan Promise)' : 'The Kisan Promise' }}</span>
                        </div>
                        <span class="badge bg-white text-success rounded-pill px-2 py-1 small fw-bold">
                            {{ $isHindi ? '100% पारदर्शी' : '100% Verified' }}
                        </span>
                    </div>
                    <div class="card-body p-3 p-sm-4 bg-white">
                        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                            <div class="promise-icon bg-success-subtle text-success">
                                <i class="bi bi-printer-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? '100% कंप्यूटरीकृत तौल पर्ची' : '100% Computerized Weighment' }}</h6>
                                <p class="small text-muted mb-0">
                                    {{ $isHindi ? 'धर्मकांटा सेंसर से सीधे प्रिंटेड तौल पर्ची — एक-एक किलो का पारदर्शी और पक्का हिसाब।' : 'Digital gross & tare slips printed directly from calibrated weighbridge sensors with zero manual tampering.' }}
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                            <div class="promise-icon bg-info-subtle text-info-emphasis">
                                <i class="bi bi-moisture"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'मानक डिजिटल नमी व ग्रेड जांच' : 'Zero Arbitrary Deductions' }}</h6>
                                <p class="small text-muted mb-0">
                                    {{ $isHindi ? 'किसान की उपस्थिति में इलेक्ट्रॉनिक मीटर से नमी जांच और वेबसाइट पर लाइव खरीद भाव का प्रकाशन।' : 'Calibrated digital moisture testing in your presence alongside publicly published daily mandi buying rates.' }}
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                            <div class="promise-icon bg-primary-subtle text-primary">
                                <i class="bi bi-bank2"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'तुरंत बैंक भुगतान अथवा देरी पर बोनस' : 'Instant Bank Payout or Delayed Bonus' }}</h6>
                                <p class="small text-muted mb-0">
                                    @if($isHindi)
                                        NEFT/RTGS/UPI या नकद द्वारा तुरंत भुगतान — और यदि किसान बाद में भुगतान लेते हैं तो निर्धारित प्रतिशत से अतिरिक्त बोनस लाभ।
                                    @else
                                        Immediate NEFT/RTGS/UPI or cash voucher settlement — plus an extra incentive bonus for farmers who opt to collect payment later.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="promise-icon bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'मिलों के लिए ग्रेड-अनुसार थोक डिस्पैच' : 'Graded Bulk Supply & Auto-Challan' }}</h6>
                                <p class="small text-muted mb-0">
                                    {{ $isHindi ? 'राइस व आटा मिलों को नमी-परीक्षित बैच और ऑटो-जनरेटेड ट्रांसपोर्ट चालान के साथ समयबद्ध डिलीवरी।' : 'Batch-segregated, moisture-certified grain lots dispatched with automated transport challans and GST documentation.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3 Core Pillars: Vision, Farmer Empowerment & Industry Supply --}}
<section class="py-4 py-md-5 bg-light border-bottom">
    <div class="container py-2 py-md-3">
        <div class="text-center mx-auto mb-4 mb-md-5" style="max-width: 680px;">
            <span class="about-section-eyebrow">{{ $isHindi ? 'हमारे मूल सिद्धांत' : 'Core Pillars' }}</span>
            <h2 class="h3 fw-bold mt-1">{{ $isHindi ? 'ईमानदारी, किसान हित और औद्योगिक गुणवत्ता' : 'Built on Vision, Farmer Respect & Industrial Reliability' }}</h2>
        </div>

        <div class="row g-3 g-md-4">
            <div class="col-12 col-md-4">
                <div class="feature-card p-4">
                    <div class="pillar-badge bg-primary-subtle text-primary mb-3">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <h5 class="h6 fw-bold mb-2">{{ $isHindi ? 'हमारी दूरदृष्टि (Our Vision)' : 'Our Vision' }}</h5>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'क्षेत्र का सबसे भरोसेमंद और किसान-हितैषी अनाज खरीद नेटवर्क बनना, जहाँ हर किसान को बिना किसी संकोच अपनी उपज का सर्वोच्च दाम मिले।' : 'To be the most trusted and farmer-friendly grain procurement network across regional mandis, recognized for 100% digital honesty.' }}
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card p-4">
                    <div class="pillar-badge bg-success-subtle text-success mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="h6 fw-bold mb-2">{{ $isHindi ? 'किसान सशक्तिकरण' : 'Farmer Empowerment' }}</h5>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'बिना बिचौलियों के सीधी खरीद, ऑनलाइन किसान आईडी पंजीकरण, लाइव मंडी भाव ग्राफ और समय पर सुरक्षित भुगतान द्वारा किसानों की आय बढ़ाना।' : 'Eliminating middlemen fees through direct weighbridge procurement, online Farmer ID enrollment, live price transparency, and guaranteed payouts.' }}
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card p-4">
                    <div class="pillar-badge bg-warning-subtle text-warning-emphasis mb-3">
                        <i class="bi bi-building-fill-check"></i>
                    </div>
                    <h5 class="h6 fw-bold mb-2">{{ $isHindi ? 'मिल एवं उद्योग आपूर्ति' : 'Mill & Processor Supply' }}</h5>
                    <p class="text-muted small mb-0">
                        {{ $isHindi ? 'राइस मिलों, फ्लोर मिलों, स्टार्च व तेल इकाइयों को अनुबंधित नमी और शुद्धता मानकों के अनुसार वर्षभर हजारों क्विंटल अनाज की आपूर्ति।' : 'Supplying rice mills, flour processors, feed plants, and oil expellers with multi-thousand quintal grain pipelines matching strict purity specs.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Trade Supply Chain Bridge + Godown Infrastructure --}}
<section class="py-4 py-md-5 bg-white">
    <div class="container py-2 py-md-3">
        <div class="row g-4 align-items-center mb-4 pb-2">
            <div class="col-12 col-lg-6">
                <span class="about-section-eyebrow">{{ $isHindi ? 'मंडी एवं गोदाम नेटवर्क' : 'Storage & Logistics Network' }}</span>
                <h2 class="h3 fw-bold mt-1 mb-2">{{ $isHindi ? 'सुरक्षित भंडारण एवं सीधा डिस्पैच नेटवर्क' : 'Verified Godowns & Direct Mill Dispatch' }}</h2>
                <p class="text-muted small mb-3">
                    {{ $isHindi ? 'खेत से गोदाम और गोदाम से सीधे मिल तक — हमारी प्रत्येक इकाई आधुनिक सुरक्षा, नमी नियंत्रण और त्वरित लोडिंग सुविधाओं से युक्त है।' : 'From farm gate arrival to clean warehouse stacking and direct commercial truck dispatch — every depot is managed on our real-time ERP ledger.' }}
                </p>

                <div class="d-flex flex-wrap gap-2">
                    @foreach($crops as $c)
                        <span class="badge bg-light text-dark border px-3 py-2 fw-medium">
                            <i class="bi bi-check2-circle text-success me-1"></i>{{ $c->name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="d-flex flex-column gap-2">
                    @foreach($warehouses as $wh)
                        <div class="d-flex justify-content-between align-items-center gap-3 p-3 rounded-3 border bg-light">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <div class="rounded-3 bg-white border text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="bi bi-house-check-fill fs-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate small">{{ $wh->name }}</div>
                                    <small class="text-muted d-block text-truncate">
                                        <code>{{ $wh->code }}</code> &bull; {{ $wh->address ?: ($isHindi ? 'मुख्य मंडी गोदाम' : 'Mandi Storage Complex') }}
                                    </small>
                                </div>
                            </div>
                            <span class="badge bg-white text-dark border px-2 py-1 flex-shrink-0 tabular-nums">
                                {{ (float) $wh->capacity_quintals > 0 ? indian_number($wh->capacity_quintals, 0) . ($isHindi ? ' क्विंटल' : ' Qtl') : ($isHindi ? 'गोदाम' : 'Depot') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Bottom Conversion CTA Banner --}}
        <div class="about-cta-banner rounded-4 p-4 p-md-5 text-white mt-3">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-8">
                    <h3 class="h4 fw-bold mb-1">
                        {{ $isHindi ? 'अपनी फसल बेचने या थोक अनाज खरीदने के लिए तैयार हैं?' : 'Ready to Sell Your Harvest or Source Bulk Grain?' }}
                    </h3>
                    <p class="small text-white-50 mb-0">
                        {{ $isHindi ? 'ऑनलाइन किसान पंजीकरण करके धर्मकांटा पर प्राथमिकता पाएं, या थोक आपूर्ति कोटेशन के लिए हमारे व्यापार कक्ष से जुड़ें।' : 'Register online for priority weighbridge service as a farmer, or contact our trading desk for bulk mill supply quotes.' }}
                    </p>
                </div>
                <div class="col-12 col-lg-4 d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-lg-end gap-2">
                    <a href="{{ route('website.farmer.register') }}" class="btn btn-light text-success fw-bold px-4">
                        <i class="bi bi-person-plus-fill"></i> {{ $isHindi ? 'किसान पंजीकरण' : 'Farmer Signup' }}
                    </a>
                    <a href="{{ route('website.contact') }}" class="btn btn-outline-light px-4">
                        <i class="bi bi-chat-dots-fill"></i> {{ $isHindi ? 'संपर्क करें' : 'Contact Desk' }}
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
    .about-eyebrow {
        background: rgba(33, 134, 75, 0.10);
        border: 1px solid rgba(33, 134, 75, 0.22);
        color: #145a32;
        font-weight: 600;
        font-size: 0.82rem;
    }
    .about-section-eyebrow {
        display: inline-block;
        color: var(--ke-primary);
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .about-hero-title {
        font-size: clamp(1.7rem, 3.3vw, 2.55rem);
        line-height: 1.22;
        letter-spacing: -0.02em;
    }
    .about-lead {
        font-size: clamp(0.95rem, 1.25vw, 1.06rem);
        line-height: 1.68;
    }
    .about-cta-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    @media (min-width: 576px) {
        .about-cta-group {
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0.55rem;
        }
    }
    .about-cta-btn {
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
    .about-cta-btn:hover {
        transform: translateY(-1px);
    }
    .about-cta-btn i {
        font-size: 0.95rem;
    }
    @media (min-width: 1200px) {
        .about-cta-group {
            gap: 0.65rem;
        }
        .about-cta-btn {
            font-size: 0.92rem;
            padding: 0.55rem 1.15rem;
            gap: 0.5rem;
        }
    }
    @media (max-width: 575.98px) {
        .about-cta-btn {
            width: 100%;
            font-size: 0.92rem;
            padding: 0.65rem 1rem;
        }
    }
    .about-stat-card {
        background: #ffffff;
        border: 1px solid var(--ke-border);
        border-radius: 16px;
        padding: 1.15rem;
        height: 100%;
        box-shadow: 0 8px 22px rgba(21, 91, 54, 0.06);
        transition: transform 0.2s ease;
    }
    .about-stat-card:hover {
        transform: translateY(-3px);
    }
    .about-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 0.65rem;
    }
    .about-stat-val {
        font-size: 1.4rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.15;
    }
    .about-stat-lbl {
        font-size: 0.76rem;
        color: #64748b;
        margin-top: 0.2rem;
        line-height: 1.3;
    }
    .promise-card {
        border-radius: 20px;
        box-shadow: 0 16px 36px rgba(17, 76, 42, 0.11);
        border: 1px solid rgba(33, 134, 75, 0.18) !important;
    }
    .promise-header {
        background: linear-gradient(135deg, #1d7e46 0%, #114c2a 100%);
    }
    .promise-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .pillar-badge {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .about-cta-banner {
        background: linear-gradient(135deg, #186a3b 0%, #0e3f23 100%);
        box-shadow: 0 14px 32px rgba(17, 76, 42, 0.18);
    }
</style>
@endpush
