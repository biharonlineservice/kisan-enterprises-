@extends('layouts.website')

@php
    $isHindi = app()->getLocale() === 'hi';
    $bizName = setting('website.brand_name', 'KisanHub');
    $helplinePhone = setting('business.phone', '+91 98765 43210');
    $helplineHref = preg_replace('/[^0-9+]/', '', (string) $helplinePhone) ?: '+919876543210';
@endphp

@section('title', ($isHindi ? 'किसान ऑनलाइन पंजीकरण (Farmer ID) — ' : 'Farmer Online Registration — Direct Crop Procurement | ') . $bizName)
@section('meta_description', $isHindi
    ? $bizName . ' पर निःशुल्क ऑनलाइन किसान पंजीकरण करें और स्थायी किसान आईडी (Farmer ID), धर्मकांटा पर त्वरित तौल तथा सीधे बैंक खाते में भुगतान की सुविधा प्राप्त करें।'
    : 'Register online with ' . $bizName . ' to get your permanent Farmer ID for priority electronic weighment, fair mandi pricing, and direct RTGS/NEFT bank settlement.')
@section('meta_keywords', 'farmer registration online, kisan id registration, sell crop directly, mandi farmer portal, ' . $bizName)

@section('content')
<section class="hero-section">
    <div class="container">
        <div class="row align-items-start justify-content-between g-4">
            <div class="col-12 col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(33, 134, 75, 0.10); border: 1px solid rgba(33, 134, 75, 0.22); color: #145a32; font-weight: 600; font-size: 0.82rem;">
                    <i class="bi bi-person-check-fill text-success"></i>
                    <span>{{ $isHindi ? 'आधिकारिक किसान नामांकन पोर्टल' : 'Official Farmer Enrollment Portal' }}</span>
                </div>
                <h1 class="fw-bold text-dark mb-2" style="font-size: 1.9rem; line-height: 1.24; letter-spacing: -0.02em;">
                    {{ $isHindi ? 'किसान ऑनलाइन पंजीकरण (Farmer ID)' : 'Farmer Online Registration' }}
                </h1>
                <p class="text-muted mb-0" style="max-width: 660px; font-size: 0.98rem;">
                    {{ $isHindi
                        ? $bizName . ' के साथ एक बार निःशुल्क पंजीकरण करें और स्थायी किसान आईडी, धर्मकांटा पर प्राथमिकता से तौल तथा सीधे बैंक खाते में भुगतान की सुविधा पाएं।'
                        : 'Enroll once with ' . $bizName . ' to receive a permanent Farmer ID, priority weighbridge unloading, and direct bank account settlement.' }}
                </p>
            </div>
            <div class="col-12 col-lg-4 d-flex flex-wrap justify-content-lg-end align-items-start gap-2">
                <span class="badge bg-white text-success px-3 py-2 rounded-pill border shadow-sm fw-semibold" style="font-size: 0.84rem;">
                    <i class="bi bi-shield-fill-check me-1"></i>
                    {{ $isHindi ? 'निःशुल्क पंजीकरण (₹0 शुल्क)' : 'Zero Registration Fee' }}
                </span>
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill border shadow-sm fw-semibold" style="font-size: 0.84rem;">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                    {{ $isHindi ? 'तुरंत किसान आईडी' : 'Instant Farmer ID' }}
                </span>
            </div>
        </div>
    </div>
</section>

<div class="container py-4 pb-5">
    <div class="row g-4 align-items-start">
        {{-- Registration Form --}}
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="ke-card-header-dark py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <span class="ke-header-icon">
                            <i class="bi bi-person-vcard-fill"></i>
                        </span>
                        <div class="ke-header-text min-w-0">
                            <h5 class="mb-0 fw-bold text-white" style="font-size: 1.05rem;">
                                {{ $isHindi ? 'किसान नामांकन फॉर्म' : 'Farmer Enrollment Form' }}
                            </h5>
                            <small class="text-white-50 d-block" style="font-size: 0.76rem;">
                                {{ $isHindi ? 'कृपया आधार कार्ड और बैंक पासबुक के अनुसार सही विवरण भरें' : 'Please fill in accurate details matching your Aadhaar and bank passbook' }}
                            </small>
                        </div>
                    </div>
                    <span class="ke-live-badge flex-shrink-0">
                        <span class="live-pulse-dot"></span>
                        <span>{{ $isHindi ? 'सुरक्षित KYC' : 'Verified KYC' }}</span>
                    </span>
                </div>

                <div class="card-body p-3 p-sm-4 bg-white">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
                            <h6 class="alert-heading fw-bold mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                {{ $isHindi ? 'कृपया नीचे दी गई त्रुटियों को सुधारें:' : 'Please correct the errors below:' }}
                            </h6>
                            <ul class="mb-0 small ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('website.farmer.register.store') }}" method="POST">
                        @csrf

                        {{-- Section 1: Personal Details --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-primary">1</span>
                                <span>{{ $isHindi ? 'व्यक्तिगत विवरण (Personal Details)' : 'Personal Details' }}</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'किसान का पूरा नाम' : 'Farmer Full Name' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" placeholder="{{ $isHindi ? 'आधार कार्ड के अनुसार नाम' : 'As per Aadhaar Card' }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'पिता / पति का नाम' : "Father's / Husband's Name" }}
                                    </label>
                                    <input type="text" name="father_name" class="form-control @error('father_name') is-invalid @enderror"
                                           value="{{ old('father_name') }}" placeholder="{{ $isHindi ? 'पिता या पति का नाम' : 'Father / Husband Name' }}">
                                    @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'मोबाइल नंबर (WhatsApp/SMS)' : 'Mobile Number (WhatsApp/SMS)' }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-semibold">+91</span>
                                        <input type="text" name="mobile" inputmode="numeric" class="form-control @error('mobile') is-invalid @enderror"
                                               value="{{ old('mobile') }}" placeholder="{{ $isHindi ? '10-अंकीय मोबाइल नंबर' : '10-digit mobile' }}" maxlength="10" required>
                                    </div>
                                    @error('mobile') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'वैकल्पिक मोबाइल नंबर' : 'Alternate Mobile Number' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-semibold">+91</span>
                                        <input type="text" name="alt_mobile" inputmode="numeric" class="form-control @error('alt_mobile') is-invalid @enderror"
                                               value="{{ old('alt_mobile') }}" placeholder="{{ $isHindi ? 'दूसरा नंबर (वैकल्पिक)' : 'Optional second number' }}" maxlength="10">
                                    </div>
                                    @error('alt_mobile') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Address & Location --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-primary">2</span>
                                <span>{{ $isHindi ? 'गाँव एवं तहसील का पता (Village & Location)' : 'Village & Tehsil Location' }}</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'गाँव / ग्राम' : 'Village / Gram' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="village" class="form-control @error('village') is-invalid @enderror"
                                           value="{{ old('village') }}" placeholder="{{ $isHindi ? 'गाँव का नाम' : 'Village Name' }}" required>
                                    @error('village') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'डाकघर (Post Office)' : 'Post Office' }}
                                    </label>
                                    <input type="text" name="post_office" class="form-control @error('post_office') is-invalid @enderror"
                                           value="{{ old('post_office') }}" placeholder="{{ $isHindi ? 'पोस्ट ऑफिस' : 'Post Office' }}">
                                    @error('post_office') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'तहसील / ब्लॉक' : 'Tehsil / Block' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="tehsil" class="form-control @error('tehsil') is-invalid @enderror"
                                           value="{{ old('tehsil') }}" placeholder="{{ $isHindi ? 'तहसील का नाम' : 'Tehsil Name' }}" required>
                                    @error('tehsil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'जिला (District)' : 'District' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="district" class="form-control @error('district') is-invalid @enderror"
                                           value="{{ old('district', 'Chandauli') }}" required>
                                    @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required small fw-semibold">
                                        {{ $isHindi ? 'राज्य (State)' : 'State' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                                           value="{{ old('state', 'Uttar Pradesh') }}" required>
                                    @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'पिन कोड (PIN Code)' : 'PIN Code' }}
                                    </label>
                                    <input type="text" name="pincode" inputmode="numeric" class="form-control @error('pincode') is-invalid @enderror"
                                           value="{{ old('pincode') }}" placeholder="{{ $isHindi ? '6-अंकीय पिन' : '6-digit PIN' }}" maxlength="6">
                                    @error('pincode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Crops & Acreage --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-primary">3</span>
                                <span>{{ $isHindi ? 'फसल एवं कृषि भूमि विवरण (Produce Profile)' : 'Agricultural Produce Profile' }}</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'उगाई जाने वाली प्रमुख फसलें' : 'Major Crops Cultivated' }}
                                    </label>
                                    <input type="text" name="crops_grown" class="form-control @error('crops_grown') is-invalid @enderror"
                                           value="{{ old('crops_grown') }}" placeholder="{{ $isHindi ? 'जैसे: धान (1509/1121), गेहूं, सरसों, मक्का' : 'e.g. Paddy (1509/1121), Wheat, Mustard' }}">
                                    <small class="text-muted">{{ $isHindi ? 'उन फसलों का नाम लिखें जिन्हें आप विक्रय के लिए लाना चाहते हैं।' : 'Mention the crops or varieties you plan to bring for procurement.' }}</small>
                                    @error('crops_grown') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'अनुमानित कृषि भूमि' : 'Estimated Land Acreage' }}
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.5" min="0" inputmode="decimal" name="estimated_acreage"
                                               class="form-control @error('estimated_acreage') is-invalid @enderror"
                                               value="{{ old('estimated_acreage') }}" placeholder="e.g. 5.0">
                                        <span class="input-group-text bg-light">{{ $isHindi ? 'एकड़' : 'Acres' }}</span>
                                    </div>
                                    @error('estimated_acreage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Bank Details & KYC --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-primary">4</span>
                                <span>{{ $isHindi ? 'सीधे बैंक भुगतान हेतु विवरण (वैकल्पिक पर अनुशंसित)' : 'Direct Bank Settlement Details (Optional but Recommended)' }}</span>
                            </h6>
                            <div class="alert alert-light border small text-muted mb-3 rounded-3">
                                <i class="bi bi-shield-lock-fill text-success me-1"></i>
                                {{ $isHindi
                                    ? 'आपका बैंक विवरण पूर्णतः सुरक्षित है और इसका उपयोग केवल आपकी फसल बिक्री की राशि सीधे RTGS/NEFT द्वारा आपके खाते में भेजने के लिए किया जाता है।'
                                    : 'Bank account details are encrypted and securely used solely for direct RTGS/NEFT payment transfers when you sell crops.' }}
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'आधार कार्ड नंबर (12 अंक)' : 'Aadhaar Card Number (12 Digits)' }}
                                    </label>
                                    <input type="password" name="aadhaar_number" inputmode="numeric" class="form-control @error('aadhaar_number') is-invalid @enderror"
                                           value="{{ old('aadhaar_number') }}" placeholder="{{ $isHindi ? '12-अंकीय आधार नंबर' : '12-digit Aadhaar number' }}" maxlength="12">
                                    <small class="text-muted">{{ $isHindi ? 'गोपनीयता के लिए सुरक्षित। केंद्र पर पहचान सत्यापन हेतु।' : 'Masked for privacy. Used for KYC verification at procurement center.' }}</small>
                                    @error('aadhaar_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'बैंक का नाम' : 'Bank Name' }}
                                    </label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                           value="{{ old('bank_name') }}" placeholder="{{ $isHindi ? 'जैसे: स्टेट बैंक ऑफ इंडिया, बड़ौदा यूपी बैंक' : 'e.g. State Bank of India, Baroda UP Bank' }}">
                                    @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'बैंक खाता संख्या (Account No.)' : 'Bank Account Number' }}
                                    </label>
                                    <input type="text" name="bank_account_number" inputmode="numeric" class="form-control @error('bank_account_number') is-invalid @enderror"
                                           value="{{ old('bank_account_number') }}" placeholder="{{ $isHindi ? 'बैंक खाता संख्या' : 'Account Number' }}">
                                    @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $isHindi ? 'बैंक IFSC कोड' : 'Bank IFSC Code' }}
                                    </label>
                                    <input type="text" name="bank_ifsc" class="form-control text-uppercase @error('bank_ifsc') is-invalid @enderror"
                                           value="{{ old('bank_ifsc') }}" placeholder="11-character IFSC (e.g. SBIN0001234)" maxlength="11">
                                    @error('bank_ifsc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Declaration --}}
                        <div class="form-check mb-4 bg-light p-3 rounded-3 border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label small text-muted" for="termsCheck">
                                {{ $isHindi
                                    ? 'मैं पुष्टि करता/करती हूँ कि दी गई जानकारी सही है। मैं ' . $bizName . ' के धर्मकांटा तौल मानकों और खरीद प्रक्रिया से सहमत हूँ।'
                                    : "I confirm that the details provided are correct and belong to me. I agree to " . $bizName . "'s weighment standards and procurement procedures." }}
                            </label>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pt-2 border-top">
                            <a href="{{ route('website.procurement') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 order-2 order-sm-1 d-inline-flex align-items-center justify-content-center gap-2 text-nowrap">
                                <i class="bi bi-arrow-left"></i>
                                <span>{{ $isHindi ? 'फसल विक्रय जानकारी पर वापस' : 'Back to Procurement Info' }}</span>
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm order-1 order-sm-2 d-inline-flex align-items-center justify-content-center gap-2 text-nowrap">
                                <i class="bi bi-check2-circle"></i>
                                <span>{{ $isHindi ? 'किसान पंजीकरण सबमिट करें' : 'Submit Farmer Registration' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar Information --}}
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="ke-card-header-dark py-3 px-3 px-sm-4 d-flex align-items-center gap-3">
                    <span class="ke-header-icon">
                        <i class="bi bi-award-fill"></i>
                    </span>
                    <div class="ke-header-text">
                        <h6 class="mb-0 fw-bold text-white">
                            {{ $isHindi ? 'पंजीकरण के प्रमुख लाभ' : 'Why Register with Us?' }}
                        </h6>
                        <small class="text-white-50 d-block" style="font-size: 0.74rem;">
                            {{ $isHindi ? 'पंजीकृत किसानों के लिए विशेष सुविधाएं' : 'Exclusive benefits for enrolled farmers' }}
                        </small>
                    </div>
                </div>
                <div class="card-body p-3 p-sm-4 bg-white">
                    <ul class="list-unstyled mb-0 d-grid gap-3">
                        <li class="d-flex gap-3">
                            <div class="rounded-3 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="bi bi-speedometer2 fs-5"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">{{ $isHindi ? 'त्वरित अनलोडिंग व तौल' : 'Fast Unloading' }}</strong>
                                <small class="text-muted">{{ $isHindi ? 'पंजीकृत किसानों को गेट पर लंबी कागजी प्रक्रिया के बिना सीधे धर्मकांटा तौल की सुविधा मिलती है।' : 'Registered farmers bypass entry queue paperwork with their pre-issued Farmer ID card.' }}</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <div class="rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="bi bi-receipt-cutoff fs-5"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">{{ $isHindi ? 'कम्प्यूटराइज्ड धर्मकांटा पर्ची' : 'Computerized Weighment' }}</strong>
                                <small class="text-muted">{{ $isHindi ? 'भरी व खाली गाड़ी का वजन, नमी जांच और शुद्ध देय राशि की पक्की डिजिटल पर्ची तुरंत प्राप्त करें।' : 'Gross and tare weighment slip issued instantly with gross weight, moisture deduction, and net payable amount.' }}</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <div class="rounded-3 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="bi bi-bank2 fs-5"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">{{ $isHindi ? 'सीधे बैंक खाते में भुगतान' : 'Direct Account Settlement' }}</strong>
                                <small class="text-muted">{{ $isHindi ? 'RTGS / NEFT / IMPS या नकद भुगतान वाउचर द्वारा उसी दिन पारदर्शी भुगतान।' : 'Direct transfer to your bank account via RTGS/NEFT or instant settlement voucher as per your preference.' }}</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <div class="rounded-3 bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="bi bi-bell-fill fs-5"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">{{ $isHindi ? 'दैनिक मंडी भाव सूचना' : 'Mandi Rate Updates' }}</strong>
                                <small class="text-muted">{{ $isHindi ? 'पंजीकृत मोबाइल नंबर पर दैनिक फसल खरीद भाव और बोनस योजनाओं की जानकारी।' : 'Receive daily mandi procurement opening rates directly on your registered mobile number.' }}</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Help Desk Card --}}
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px;">
                        <i class="bi bi-headset fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">{{ $isHindi ? 'पंजीकरण में सहायता चाहिए?' : 'Need Help Registering?' }}</h6>
                    <p class="small text-muted mb-3">
                        {{ $isHindi
                            ? 'आप हमारे मंडी धर्मकांटा कार्यालय पर आकर या नीचे दिए गए हेल्पलाइन नंबर पर कॉल करके भी तुरंत अपना पंजीकरण करवा सकते हैं।'
                            : 'Visit our procurement hub weighbridge office in person or call our trading desk. Our staff will register your profile on the spot.' }}
                    </p>
                    <a href="tel:{{ $helplineHref }}" class="btn btn-outline-primary rounded-pill w-100 fw-semibold py-2 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-telephone-fill"></i>
                        <span>{{ $helplinePhone }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
