@extends('layouts.website')

@section('title', (app()->getLocale() === 'hi' ? 'संपर्क एवं व्यापारिक सहायता केंद्र — ' : 'Contact Us & Trade Desk Inquiries — ') . setting('website.brand_name', 'KisanHub'))
@section('meta_description', 'Get in touch with KisanHub for bulk grain procurement, rice mill supply, farmer weighbridge appointments, and general trading inquiries.')

@section('content')
@php
    $isHindi = app()->getLocale() === 'hi';
    $bizName = setting('website.brand_name', 'KisanHub');
    $bizAddress = setting('business.address', 'Agricultural Trading Hub, G.T. Road, Chandauli, Uttar Pradesh - 232104');
    $helplinePhone = \App\Models\WebsiteContent::get('helpline_phone', setting('business.phone', '+91 98765 43210')) ?: '+91 98765 43210';
    $helplineHref = preg_replace('/[^0-9+]/', '', $helplinePhone);
    $contactEmail = \App\Models\WebsiteContent::get('contact_email', setting('business.email', 'trade@kisanenterprises.com')) ?: 'trade@kisanenterprises.com';
    $gstin = setting('business.gstin');
    $pan = setting('business.pan');
    $selectedType = old('inquiry_type', 'bulk_buyer');
@endphp

{{-- Botanical Hero + Main Trading Hub Snapshot --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-start g-4 g-lg-5">
            {{-- Left Column --}}
            <div class="col-12 col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill cnt-eyebrow mb-3">
                    <i class="bi bi-headset text-success"></i>
                    <span>{{ $isHindi ? 'संपर्क एवं व्यापारिक सहायता केंद्र' : 'Mandi Trade Desk & Weighbridge Support' }}</span>
                </div>

                <h1 class="cnt-hero-title fw-bold text-dark mb-3">
                    @if($isHindi)
                        हमारी कृषि व्यापार डेस्क, धर्मकांटा केंद्र एवं गोदाम कार्यालय से सीधे जुड़ें
                    @else
                        Connect Directly With Our Agricultural Trade Desk & Weighbridge Hub
                    @endif
                </h1>

                <p class="cnt-lead text-muted mb-4">
                    @if($isHindi)
                        चाहे आप अपनी फसल ट्रॉली लाने वाले किसान हों या थोक अनाज खरीदने वाली राइस/फ्लोर मिल — {{ $bizName }} की समर्पित मंडी टीम आपकी त्वरित सहायता के लिए उपलब्ध है।
                    @else
                        Whether you are a farmer bringing a harvest load or an institutional mill buyer seeking bulk grain quotes, our commercial trading desk at {{ $bizName }} is ready to assist you.
                    @endif
                </p>

                <div class="ke-cta-group mb-4">
                    <a href="#inquiry-form" class="btn btn-primary rounded-pill shadow-sm ke-cta-btn">
                        <i class="bi bi-envelope-paper-fill"></i>
                        <span>{{ $isHindi ? 'ऑनलाइन संदेश / पूछताछ भेजें' : 'Send Trade Inquiry Online' }}</span>
                    </a>
                    <a href="tel:{{ $helplineHref }}" class="btn btn-outline-primary bg-white rounded-pill ke-cta-btn">
                        <i class="bi bi-telephone-outbound-fill"></i>
                        <span>{{ $isHindi ? 'सीधी कॉल: ' : 'Call Desk: ' }}{{ $helplinePhone }}</span>
                    </a>
                </div>

                {{-- Dedicated Assistance Channels Strip --}}
                <div class="p-3 rounded-4 bg-white border shadow-sm">
                    <div class="small fw-bold text-dark mb-2">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        {{ $isHindi ? 'त्वरित सहायता विभाग:' : 'Dedicated Support Desks:' }}
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-tree-fill text-success me-1"></i>{{ $isHindi ? 'किसान तौल व भुगतान डेस्क' : 'Farmer Weighbridge & Payouts' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-buildings-fill text-primary me-1"></i>{{ $isHindi ? 'मिल एवं थोक बिक्री कोटेशन' : 'Mill & Bulk Supply Quotes' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                            <i class="bi bi-truck text-warning-emphasis me-1"></i>{{ $isHindi ? 'गोदाम लोडिंग व गेट पास' : 'Godown Dispatch & E-Way Bills' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Head Office & Weighbridge Timings Card --}}
            <div class="col-12 col-lg-5">
                <div class="card cnt-office-card border-0 overflow-hidden">
                    <div class="cnt-office-header text-white py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <span class="ke-header-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </span>
                            <div class="ke-header-text min-w-0">
                                <div class="fw-bold text-white lh-sm">{{ $isHindi ? 'मुख्य मंडी कार्यालय व धर्मकांटा' : 'Trading Hub & Head Office' }}</div>
                                <small class="text-white-50 d-block" style="font-size: 0.75rem;">
                                    {{ $bizName }}
                                </small>
                            </div>
                        </div>
                        <span class="ke-live-badge flex-shrink-0">
                            <span class="live-pulse-dot"></span>
                            <span>{{ $isHindi ? 'सक्रिय केंद्र' : 'Active Hub' }}</span>
                        </span>
                    </div>

                    <div class="card-body p-3 p-sm-4 bg-white">
                        <div class="d-grid gap-3">
                            {{-- Address --}}
                            <div class="d-flex align-items-start gap-3">
                                <span class="cnt-info-icon bg-success-subtle text-success">
                                    <i class="bi bi-building"></i>
                                </span>
                                <div>
                                    <div class="small fw-bold text-dark">{{ $isHindi ? 'मुख्य कार्यालय एवं धर्मकांटा पता' : 'Main Office & Weighbridge Address' }}</div>
                                    <div class="small text-muted">{{ $bizAddress }}</div>
                                </div>
                            </div>

                            {{-- Direct Phone & Email Grid --}}
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <a href="tel:{{ $helplineHref }}" class="cnt-quick-tile text-decoration-none h-100">
                                        <span class="small text-muted d-block" style="font-size: 0.71rem;">
                                            <i class="bi bi-telephone-fill text-success me-1"></i>{{ $isHindi ? 'व्यापार हेल्पलाइन' : 'Trade Helpline' }}
                                        </span>
                                        <span class="fw-bold text-dark small tabular-nums d-block mt-1">{{ $helplinePhone }}</span>
                                    </a>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <a href="mailto:{{ $contactEmail }}" class="cnt-quick-tile text-decoration-none h-100">
                                        <span class="small text-muted d-block" style="font-size: 0.71rem;">
                                            <i class="bi bi-envelope-fill text-primary me-1"></i>{{ $isHindi ? 'आधिकारिक ईमेल' : 'Official Email' }}
                                        </span>
                                        <span class="fw-bold text-dark small text-truncate d-block mt-1">{{ $contactEmail }}</span>
                                    </a>
                                </div>
                            </div>

                            {{-- Weighbridge & Office Timings --}}
                            <div class="rounded-3 bg-light border p-3 small">
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-clock-history text-success me-1"></i>
                                    {{ $isHindi ? 'कार्यालय एवं धर्मकांटा समय:' : 'Weighbridge & Trading Hours:' }}
                                </div>
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>{{ $isHindi ? 'सोमवार – शनिवार:' : 'Monday – Saturday:' }}</span>
                                    <strong class="text-dark">8:00 AM – 7:30 PM</strong>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>{{ $isHindi ? 'रविवार (पीक सीजन):' : 'Sunday (Peak Harvest):' }}</span>
                                    <strong class="text-dark">9:00 AM – 2:00 PM</strong>
                                </div>
                            </div>

                            @if($gstin || $pan)
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @if($gstin)
                                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                                            GSTIN: <strong class="font-monospace">{{ $gstin }}</strong>
                                        </span>
                                    @endif
                                    @if($pan)
                                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
                                            PAN: <strong class="font-monospace">{{ $pan }}</strong>
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer bg-light border-top px-3 px-sm-4 py-2 d-flex justify-content-between align-items-center small">
                        <span class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-patch-check-fill text-success me-1"></i>
                            {{ $isHindi ? 'प्रमाणित डिजिटल धर्मकांटा सक्रिय' : 'Certified Digital Weighbridge Active' }}
                        </span>
                        <a href="{{ route('home') }}#price-trend" class="fw-semibold text-primary text-decoration-none">
                            {{ $isHindi ? 'आज का भाव →' : 'Live Rates →' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3 Interactive Department Cards --}}
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container py-2">
        <div class="row g-3 g-md-4">
            <div class="col-12 col-md-4">
                <div class="feature-card cnt-accent-green p-4 d-flex flex-column">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="cnt-pill-icon bg-success-subtle text-success">
                            <i class="bi bi-tree-fill"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'किसान फसल खरीद सहायता' : 'Farmer Procurement Desk' }}</h5>
                    </div>
                    <p class="text-muted small mb-3 flex-grow-1">
                        {{ $isHindi ? 'फसल का आज का भाव जानने, ट्रॉली तौल समय पूछने या भुगतान व देरी बोनस से जुड़ी जानकारी के लिए संपर्क करें।' : 'Inquire about live mandi buying rates, weighbridge arrival slots, moisture norms, or farmer payout settlements.' }}
                    </p>
                    <a href="#inquiry-form" class="btn btn-sm btn-outline-success rounded-pill align-self-start px-3 fw-semibold js-pick-inquiry" data-type="farmer">
                        {{ $isHindi ? 'किसान पूछताछ चुनें →' : 'Select Farmer Inquiry →' }}
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card cnt-accent-blue p-4 d-flex flex-column">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="cnt-pill-icon bg-primary-subtle text-primary">
                            <i class="bi bi-buildings-fill"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'राइस व फ्लोर मिल थोक डेस्क' : 'Mill & Bulk Buyer Desk' }}</h5>
                    </div>
                    <p class="text-muted small mb-3 flex-grow-1">
                        {{ $isHindi ? '150 से 2,500+ क्विंटल धान, गेहूं, मक्का व सरसों के एक्स-गोदाम कोटेशन, नमी सर्टिफिकेट और डिस्पैच हेतु।' : 'Request ex-godown or FOR destination pricing for 150–2,500+ Qtl graded grain lots, moisture specs, and recurring contracts.' }}
                    </p>
                    <a href="#inquiry-form" class="btn btn-sm btn-outline-primary rounded-pill align-self-start px-3 fw-semibold js-pick-inquiry" data-type="bulk_buyer">
                        {{ $isHindi ? 'थोक कोटेशन चुनें →' : 'Select Bulk Buyer →' }}
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="feature-card cnt-accent-amber p-4 d-flex flex-column">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="cnt-pill-icon bg-warning-subtle text-warning-emphasis">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h5 class="h6 fw-bold text-dark mb-0">{{ $isHindi ? 'गोदाम, बिलिंग व ट्रांसपोर्ट' : 'Logistics, Accounts & General' }}</h5>
                    </div>
                    <p class="text-muted small mb-3 flex-grow-1">
                        {{ $isHindi ? 'वाहन लोडिंग, गेट पास, जीएसटी ई-वे बिल, भुगतान UTR सत्यापन या सामान्य व्यावसायिक साझेदारी हेतु।' : 'Reach our logistics and accounts team for truck loading schedules, E-Way bills, UTR verification, or general trade.' }}
                    </p>
                    <a href="#inquiry-form" class="btn btn-sm btn-outline-dark rounded-pill align-self-start px-3 fw-semibold js-pick-inquiry" data-type="general">
                        {{ $isHindi ? 'सामान्य पूछताछ चुनें →' : 'Select General Inquiry →' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Depot Directory + Interactive Inquiry Form --}}
<section id="inquiry-form" class="py-4 py-md-5 bg-light">
    <div class="container py-2 py-md-3">
        <div class="row g-4 g-lg-5 align-items-start">
            {{-- Left Column: Warehouse Depots Directory + Farmer Fast-Track Card --}}
            <div class="col-12 col-lg-5">
                <span class="cnt-section-eyebrow">
                    <i class="bi bi-house-gear-fill me-1"></i>{{ $isHindi ? 'हमारे गोदाम व तौल केंद्र' : 'Storage Depots & Weighbridges' }}
                </span>
                <h2 class="h4 fw-bold mt-1 mb-3">
                    {{ $isHindi ? 'मंडी गोदाम एवं डिस्पैच केंद्र निर्देशिका' : 'Active Godowns & Dispatch Locations' }}
                </h2>

                <div class="d-grid gap-3 mb-4">
                    @forelse($warehouses as $wh)
                        <div class="cnt-depot-card bg-white p-3 rounded-4 border shadow-sm">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">{{ $wh->code }}</span>
                                    <h6 class="fw-bold text-dark mb-0">{{ $wh->name }}</h6>
                                </div>
                                @if($wh->capacity_quintals)
                                    <span class="badge bg-light text-muted border small tabular-nums">
                                        {{ indian_number($wh->capacity_quintals, 0) }} {{ $isHindi ? 'क्विंटल' : 'Qtl' }}
                                    </span>
                                @endif
                            </div>

                            @if($wh->address)
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt text-success me-1"></i>{{ $wh->address }}
                                </p>
                            @endif

                            @if($wh->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $wh->phone) }}" class="small fw-semibold text-decoration-none text-primary">
                                    <i class="bi bi-telephone-outbound me-1"></i>{{ $isHindi ? 'गोदाम संपर्क:' : 'Depot Line:' }} {{ $wh->phone }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white p-3 rounded-4 border text-muted small">
                            <i class="bi bi-geo-alt-fill text-success me-1"></i>{{ $bizAddress }}
                        </div>
                    @endforelse
                </div>

                {{-- Farmer Fast-Track Registration Card --}}
                <div class="cnt-farmer-cta rounded-4 p-4 text-white shadow-sm">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                        <h6 class="fw-bold mb-0">
                            {{ $isHindi ? 'क्या आप अपनी फसल बेचने आ रहे हैं?' : 'Are You a Farmer Bringing a Crop Load?' }}
                        </h6>
                    </div>
                    <p class="small text-white-50 mb-3">
                        {{ $isHindi ? 'धर्मकांटा गेट पर तुरंत पर्ची बनवाने के लिए पहले से निःशुल्क ऑनलाइन पंजीकरण कर अपनी किसान आईडी (FRM...) प्राप्त करें, या आधार व बैंक पासबुक साथ लाएं।' : 'Register online beforehand in 1 minute to get your instant Farmer ID (FRM...) for fast-track weighbridge entry, or bring your Aadhaar and bank passbook directly.' }}
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('website.farmer.register') }}" class="btn btn-light btn-sm fw-bold text-success px-3">
                            <i class="bi bi-person-plus-fill me-1"></i>{{ $isHindi ? 'किसान पंजीकरण करें' : 'Register Online' }}
                        </a>
                        <a href="{{ route('website.procurement') }}" class="btn btn-outline-light btn-sm px-3">
                            {{ $isHindi ? 'फसल विक्रय नियम →' : 'Procurement Guide →' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column: Interactive Online Inquiry Form --}}
            <div class="col-12 col-lg-7">
                <div class="card cnt-form-card border-0 rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-white border-bottom p-3 p-sm-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h3 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                    <i class="bi bi-chat-left-text-fill text-success"></i>
                                    {{ $isHindi ? 'हमें ऑनलाइन संदेश / पूछताछ भेजें' : 'Send Us an Inquiry' }}
                                </h3>
                                <p class="text-muted small mb-0">
                                    {{ $isHindi ? 'नीचे अपना विवरण भरें — हमारी संबंधित मंडी टीम शीघ्र ही आपसे संपर्क करेगी।' : 'Fill out the details below and our trading desk will respond promptly.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-sm-4 p-md-5">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                <h6 class="alert-heading fw-bold mb-1 small">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    {{ $isHindi ? 'कृपया फॉर्म की त्रुटियाँ सुधारें:' : 'Please check the form errors:' }}
                                </h6>
                                <ul class="mb-0 small ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('website.contact.store') }}" method="POST">
                            @csrf

                            {{-- Interactive Inquiry Type Visual Pills + Synced Select --}}
                            <div class="mb-4">
                                <label for="contactInquiryType" class="form-label required small fw-semibold mb-2">
                                    {{ $isHindi ? 'पूछताछ का प्रकार चुनें' : 'Select Inquiry Nature' }} <span class="text-danger">*</span>
                                </label>

                                <div class="row g-2 mb-2" role="group" aria-label="Inquiry Type">
                                    <div class="col-12 col-sm-4">
                                        <button type="button"
                                                class="cnt-type-pill w-100 {{ $selectedType === 'bulk_buyer' ? 'active' : '' }}"
                                                data-type="bulk_buyer">
                                            <i class="bi bi-buildings-fill"></i>
                                            <span>{{ $isHindi ? 'थोक खरीदार / मिल' : 'Bulk Buyer / Mill' }}</span>
                                        </button>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <button type="button"
                                                class="cnt-type-pill w-100 {{ $selectedType === 'farmer' ? 'active' : '' }}"
                                                data-type="farmer">
                                            <i class="bi bi-tree-fill"></i>
                                            <span>{{ $isHindi ? 'किसान फसल बिक्री' : 'Farmer Crop Sale' }}</span>
                                        </button>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <button type="button"
                                                class="cnt-type-pill w-100 {{ $selectedType === 'general' ? 'active' : '' }}"
                                                data-type="general">
                                            <i class="bi bi-chat-dots-fill"></i>
                                            <span>{{ $isHindi ? 'सामान्य पूछताछ' : 'General Inquiry' }}</span>
                                        </button>
                                    </div>
                                </div>

                                <select id="contactInquiryType" name="inquiry_type" class="form-select form-select-sm bg-light @error('inquiry_type') is-invalid @enderror" required>
                                    <option value="bulk_buyer" {{ $selectedType === 'bulk_buyer' ? 'selected' : '' }}>
                                        {{ $isHindi ? 'थोक खरीदार / राइस व फ्लोर मिल / व्यापारी खरीद' : 'Bulk Buyer / Rice Mill / Wholesaler Purchase' }}
                                    </option>
                                    <option value="farmer" {{ $selectedType === 'farmer' ? 'selected' : '' }}>
                                        {{ $isHindi ? 'किसान फसल विक्रय / धर्मकांटा एवं भुगतान पूछताछ' : 'Farmer Selling Crops / Procurement Inquiry' }}
                                    </option>
                                    <option value="general" {{ $selectedType === 'general' ? 'selected' : '' }}>
                                        {{ $isHindi ? 'सामान्य व्यावसायिक / ट्रांसपोर्ट पूछताछ' : 'General Business & Transport Inquiry' }}
                                    </option>
                                </select>
                                @error('inquiry_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="contactName" class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'आपका नाम / फर्म या मिल का नाम' : 'Full Name / Enterprise Name' }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person-fill text-muted"></i></span>
                                        <input type="text"
                                               id="contactName"
                                               name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}"
                                               placeholder="{{ $isHindi ? 'आपका नाम या मिल/फर्म का नाम' : 'Your Name or Mill / Firm Name' }}"
                                               required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="contactPhone" class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'संपर्क मोबाइल नंबर' : 'Contact Phone / Mobile' }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-telephone-fill text-muted"></i></span>
                                        <input type="tel"
                                               id="contactPhone"
                                               name="phone"
                                               inputmode="tel"
                                               class="form-control @error('phone') is-invalid @enderror"
                                               value="{{ old('phone') }}"
                                               placeholder="{{ $isHindi ? '10-अंकीय मोबाइल नंबर' : '10-digit mobile number' }}"
                                               required>
                                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="contactEmail" class="form-label small fw-semibold">
                                        {{ $isHindi ? 'ईमेल पता (वैकल्पिक)' : 'Email Address (Optional)' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope-fill text-muted"></i></span>
                                        <input type="email"
                                               id="contactEmail"
                                               name="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}"
                                               placeholder="name@domain.com">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="contactSubject" class="form-label small fw-semibold">
                                        {{ $isHindi ? 'विषय / फसल संदर्भ' : 'Subject / Commodity Reference' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-tag-fill text-muted"></i></span>
                                        <input type="text"
                                               id="contactSubject"
                                               name="subject"
                                               class="form-control @error('subject') is-invalid @enderror"
                                               value="{{ old('subject') }}"
                                               placeholder="{{ $isHindi ? 'जैसे: 500 क्विंटल धान / गेहूं थोक आवश्यकता' : 'e.g. Bulk Paddy / Wheat Requirement (500 Qtl)' }}">
                                        @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-1">
                                    <label for="contactMessage" class="form-label required small fw-semibold mb-0">
                                        {{ $isHindi ? 'संदेश / आवश्यकता का विवरण' : 'Your Message / Requirement Details' }} <span class="text-danger">*</span>
                                    </label>
                                    @if($crops->isNotEmpty())
                                        <span class="text-muted" style="font-size: 0.72rem;">
                                            {{ $isHindi ? 'फसल जोड़ें:' : 'Add Crop:' }}
                                            @foreach($crops as $qc)
                                                <button type="button"
                                                        class="btn btn-link btn-sm p-0 ms-1 text-decoration-none fw-semibold js-contact-crop"
                                                        style="font-size: 0.72rem;"
                                                        data-crop="{{ $qc->name }}">
                                                    +{{ strtok($qc->name, ' ') }}
                                                </button>
                                            @endforeach
                                        </span>
                                    @endif
                                </div>
                                <textarea id="contactMessage"
                                          name="message"
                                          rows="4"
                                          minlength="10"
                                          class="form-control @error('message') is-invalid @enderror"
                                          placeholder="{{ $isHindi ? 'कृपया फसल की किस्म, अनुमानित मात्रा (क्विंटल/टन), गंतव्य शहर या अपना प्रश्न विस्तार से लिखें (न्यूनतम 10 अक्षर)...' : 'Please specify crop variety, estimated quantity (Quintals/MT), delivery location, or your question (minimum 10 characters)...' }}"
                                          required>{{ old('message') }}</textarea>
                                @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 pt-2 border-top">
                                <small class="text-muted">
                                    <i class="bi bi-clock text-success me-1"></i>
                                    {{ $isHindi ? 'सोम–शनि कार्य समय में त्वरित उत्तर।' : 'Prompt callback during Mon–Sat mandi hours.' }}
                                </small>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm cnt-submit-btn d-inline-flex align-items-center justify-content-center gap-2 text-nowrap">
                                    <i class="bi bi-send-fill"></i>
                                    <span>{{ $isHindi ? 'संदेश भेजें' : 'Send Trade Message' }}</span>
                                </button>
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
    .cnt-eyebrow {
        background: rgba(33, 134, 75, 0.11);
        border: 1px solid rgba(33, 134, 75, 0.24);
        color: #155b36;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .cnt-section-eyebrow {
        display: inline-block;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #1e8449;
    }
    .cnt-hero-title {
        font-size: clamp(1.7rem, 3.3vw, 2.55rem);
        line-height: 1.2;
        letter-spacing: -0.02em;
    }
    .cnt-lead {
        font-size: clamp(0.95rem, 1.35vw, 1.08rem);
        line-height: 1.65;
    }
    @media (max-width: 575.98px) {
        .cnt-cta-btn,
        .cnt-submit-btn {
            width: 100%;
            justify-content: center;
            font-size: 0.96rem;
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
        }
    }
    .cnt-office-card {
        border-radius: 18px;
        box-shadow: 0 16px 38px rgba(17, 68, 39, 0.12);
        border: 1px solid rgba(33, 134, 75, 0.22) !important;
    }
    .cnt-office-header {
        background: linear-gradient(135deg, #1b7a43 0%, #114b2a 100%);
    }
    .cnt-header-icon {
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
    .cnt-info-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .cnt-quick-tile {
        display: block;
        background: #f6fbf8;
        border: 1px solid rgba(33, 134, 75, 0.16);
        border-radius: 12px;
        padding: 0.65rem 0.8rem;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .cnt-quick-tile:hover {
        background: #edf7f1;
        border-color: rgba(33, 134, 75, 0.4);
    }
    .cnt-pill-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .cnt-accent-green { border-left: 4px solid #1e8449 !important; }
    .cnt-accent-blue { border-left: 4px solid #0d6efd !important; }
    .cnt-accent-amber { border-left: 4px solid #e6a800 !important; }

    .cnt-depot-card {
        transition: border-color 0.2s ease, transform 0.2s ease;
    }
    .cnt-depot-card:hover {
        border-color: rgba(33, 134, 75, 0.4) !important;
        transform: translateY(-2px);
    }
    .cnt-farmer-cta {
        background: linear-gradient(135deg, #186a3b 0%, #0f4225 100%);
    }
    .cnt-form-card {
        box-shadow: 0 14px 36px rgba(17, 68, 39, 0.08);
        border: 1px solid rgba(33, 134, 75, 0.2) !important;
    }
    .cnt-type-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.6rem 0.75rem;
        border-radius: 12px;
        border: 1px solid #d7e3db;
        background: #f8fbf9;
        color: #2c3e33;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .cnt-type-pill:hover {
        border-color: #1e8449;
        background: #eef7f1;
    }
    .cnt-type-pill.active {
        background: #186a3b;
        border-color: #186a3b;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(24, 106, 59, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('contactInquiryType');
    const pills = document.querySelectorAll('.cnt-type-pill');
    const subjectInput = document.getElementById('contactSubject');
    const msgInput = document.getElementById('contactMessage');
    const nameInput = document.getElementById('contactName');
    const isHindi = @json($isHindi);

    const placeholders = {
        bulk_buyer: {
            subject: isHindi ? 'जैसे: 500 क्विंटल धान / गेहूं थोक कोटेशन' : 'e.g. Bulk Paddy / Wheat Requirement (500 Qtl)',
            message: isHindi
                ? 'आवश्यक अनाज, किस्म, मात्रा (क्विंटल/टन) और डिलीवरी शहर लिखें...'
                : 'Specify required commodity, variety, tonnage, and destination mill city...'
        },
        farmer: {
            subject: isHindi ? 'जैसे: ट्रॉली तौल स्लॉट / आज का धान-गेहूं भाव' : 'e.g. Harvest Weighbridge Arrival / Crop Rate Query',
            message: isHindi
                ? 'अपना गाँव, फसल का नाम, अनुमानित वजन (क्विंटल) या भुगतान संबंधी प्रश्न लिखें...'
                : 'Share your village, crop name, estimated quintals, or settlement query...'
        },
        general: {
            subject: isHindi ? 'जैसे: ट्रांसपोर्ट / गेट पास / व्यापारिक साझेदारी' : 'e.g. Logistics, Billing or General Business Inquiry',
            message: isHindi
                ? 'कृपया अपना संदेश या प्रश्न विस्तार से लिखें (न्यूनतम 10 अक्षर)...'
                : 'Please describe your inquiry in detail (minimum 10 characters)...'
        }
    };

    function syncType(val) {
        if (!typeSelect) return;
        typeSelect.value = val;
        pills.forEach(function (p) {
            p.classList.toggle('active', p.getAttribute('data-type') === val);
        });
        if (placeholders[val]) {
            if (subjectInput) subjectInput.setAttribute('placeholder', placeholders[val].subject);
            if (msgInput) msgInput.setAttribute('placeholder', placeholders[val].message);
        }
    }

    pills.forEach(function (p) {
        p.addEventListener('click', function () {
            syncType(p.getAttribute('data-type'));
        });
    });

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            syncType(typeSelect.value);
        });
    }

    document.querySelectorAll('.js-pick-inquiry').forEach(function (btn) {
        btn.addEventListener('click', function () {
            syncType(btn.getAttribute('data-type'));
            setTimeout(function () {
                if (nameInput && !nameInput.value) nameInput.focus();
            }, 350);
        });
    });

    document.querySelectorAll('.js-contact-crop').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!msgInput) return;
            const crop = btn.getAttribute('data-crop') || '';
            const prefix = msgInput.value.trim() ? msgInput.value.trim() + ' | ' : '';
            msgInput.value = prefix + crop;
            msgInput.focus();
        });
    });
});
</script>
@endpush
