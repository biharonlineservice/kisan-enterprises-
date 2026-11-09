@extends('layouts.app')

@section('title', __('settings.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-calendar3 text-success"></i>
                    <strong>{{ __('settings.banner_fy', ['fy' => $metrics['currentFy']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-building"></i>
                    {{ __('settings.banner_entity', ['name' => $metrics['businessName']]) }}
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-receipt"></i>
                    {{ __('settings.banner_tax', ['status' => $metrics['taxEnabled'] ? $metrics['taxLabel'].' ('.$metrics['taxRate'].'%)' : 'Exempt']) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-shield-lock-fill text-warning"></i>
                    <strong>{{ __('settings.banner_timeout', ['min' => $metrics['sessionTimeout']]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('settings.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 780px;">
                {{ __('settings.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            @if(\Illuminate\Support\Facades\Route::has('audit-logs.index'))
                <a href="{{ route('audit-logs.index') }}" class="erp-hero-btn" title="{{ __('settings.btn_audit_logs') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('settings.btn_audit_logs') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('users.index'))
                <a href="{{ route('users.index') }}" class="erp-hero-btn" title="{{ __('settings.btn_users') }}">
                    <i class="bi bi-people"></i>
                    <span>{{ __('settings.btn_users') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('roles.index'))
                <a href="{{ route('roles.index') }}" class="erp-hero-btn" title="{{ __('settings.btn_roles') }}">
                    <i class="bi bi-shield-check"></i>
                    <span>{{ __('settings.btn_roles') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('website.admin.index'))
                <a href="{{ route('website.admin.index') }}" class="erp-hero-btn" title="{{ __('settings.btn_website') }}">
                    <i class="bi bi-globe"></i>
                    <span>{{ __('settings.btn_website') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Active Fiscal Year --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('settings.edit', ['tab' => 'numbering']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('settings.kpi_fy') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-calendar3"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ $metrics['currentFy'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('settings.kpi_fy_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Tax & Invoicing Mode --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('settings.edit', ['tab' => 'documents']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('settings.kpi_tax') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-receipt"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">
                    {{ $metrics['taxEnabled'] ? $metrics['taxLabel'].' Active' : 'Exempt / Nil-Tax' }}
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('settings.kpi_tax_sub', ['rate' => $metrics['taxRate']]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Sequence Padding --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('settings.edit', ['tab' => 'numbering']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('settings.kpi_padding') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-hash"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ $metrics['padding'] }} Digits</div>
                <div class="kpi-sub text-truncate">
                    {{ __('settings.kpi_padding_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Session Security --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('settings.edit', ['tab' => 'system']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('settings.kpi_security') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ $metrics['sessionTimeout'] }} Min</div>
                <div class="kpi-sub text-truncate">
                    {{ __('settings.kpi_security_sub') }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Top Navigation Tabs Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            @foreach($tabs as $tabKey => $tabLabel)
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === $tabKey ? 'active' : '' }}" 
                       href="{{ route('settings.edit', ['tab' => $tabKey]) }}">
                        @if($tabKey === 'business') <i class="bi bi-building"></i>
                        @elseif($tabKey === 'documents') <i class="bi bi-file-earmark-text"></i>
                        @elseif($tabKey === 'numbering') <i class="bi bi-hash"></i>
                        @elseif($tabKey === 'system') <i class="bi bi-sliders"></i>
                        @elseif($tabKey === 'mail') <i class="bi bi-envelope-at"></i>
                        @elseif($tabKey === 'whatsapp') <i class="bi bi-whatsapp"></i>
                        @endif
                        <span>{{ __('settings.tab_' . $tabKey) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>AES-256 Encrypted
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-file-lock2 me-1"></i>Audit Trail Active
            </span>
        </div>
    </div>
</div>

{{-- 4. Main Settings Configuration Form --}}
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success p-2 rounded-2">
                    @if($tab === 'business') <i class="bi bi-building fs-6"></i>
                    @elseif($tab === 'documents') <i class="bi bi-file-earmark-text fs-6"></i>
                    @elseif($tab === 'numbering') <i class="bi bi-hash fs-6"></i>
                    @elseif($tab === 'system') <i class="bi bi-sliders fs-6"></i>
                    @elseif($tab === 'mail') <i class="bi bi-envelope-at fs-6"></i>
                    @elseif($tab === 'whatsapp') <i class="bi bi-whatsapp fs-6"></i>
                    @endif
                </span>
                <span>{{ __('settings.tab_' . $tab) }}</span>
            </h5>
            <small class="text-muted d-block mt-1">
                {{ __('settings.desc_' . $tab) }}
            </small>
        </div>
        <div class="text-muted small">
            <span class="badge bg-light text-secondary border font-monospace">Tab: {{ strtoupper($tab) }}</span>
        </div>
    </div>

    <div class="card-body p-4">
        <form method="POST" action="{{ route('settings.update', ['tab' => $tab]) }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <x-form-token />

            {{-- ==================== TAB 1: BUSINESS PROFILE ==================== --}}
            @if($tab === 'business')
                {{-- Group 1: Legal Entity & Mandi Location --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-building text-success"></i>
                        <span>{{ __('settings.section_identity') }} &amp; {{ __('settings.section_contact') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="business_name" class="form-label fw-bold text-dark">
                                {{ __('settings.label_business_name') }} (ERP / Invoicing Legal Entity) <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="business_name" 
                                   name="business_name" 
                                   value="{{ old('business_name', $s['business.name'] ?? 'Kisan Enterprises') }}" 
                                   class="form-control @error('business_name') is-invalid @enderror" 
                                   required>
                            @error('business_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small">Printed on tax invoices, receipts, and statutory trade records.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="website_brand_name" class="form-label fw-bold text-dark">
                                Public Website Brand Name (Customer-Facing)
                            </label>
                            <input type="text" 
                                   id="website_brand_name" 
                                   name="website_brand_name" 
                                   value="{{ old('website_brand_name', $s['website.brand_name'] ?? 'KisanHub') }}" 
                                   class="form-control @error('website_brand_name') is-invalid @enderror" 
                                   placeholder="e.g. KisanHub">
                            @error('website_brand_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small">Displayed on public website homepage, navbar, footer, and farmer registration.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="business_state" class="form-label fw-bold text-dark">
                                {{ __('settings.label_business_state') }}
                            </label>
                            <input type="text" 
                                   id="business_state" 
                                   name="business_state" 
                                   value="{{ old('business_state', $s['business.state'] ?? '') }}" 
                                   class="form-control" 
                                   placeholder="e.g. Uttar Pradesh, Bihar">
                        </div>

                        <div class="col-12">
                            <label for="business_tagline" class="form-label fw-bold text-dark">
                                {{ __('settings.label_business_tagline') }}
                            </label>
                            <input type="text" 
                                   id="business_tagline" 
                                   name="business_tagline" 
                                   value="{{ old('business_tagline', $s['business.tagline'] ?? '') }}" 
                                   class="form-control" 
                                   placeholder="e.g. Agricultural Crop Trading &amp; Grain Processing">
                        </div>

                        <div class="col-12">
                            <label for="business_address" class="form-label fw-bold text-dark">
                                {{ __('settings.label_business_address') }}
                            </label>
                            <textarea id="business_address" 
                                      name="business_address" 
                                      rows="3" 
                                      class="form-control" 
                                      placeholder="Office/Mandi premise, town, district, pin code">{{ old('business_address', $s['business.address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Group 2: Contact Communication & Statutory Tax IDs --}}
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="p-3 rounded-3 bg-light border h-100">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-telephone text-success"></i>
                                <span>{{ __('settings.section_contact') }}</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="business_phone" class="form-label fw-bold text-dark">
                                        {{ __('settings.label_business_phone') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                        <input type="text" 
                                               id="business_phone" 
                                               name="business_phone" 
                                               value="{{ old('business_phone', $s['business.phone'] ?? '') }}" 
                                               class="form-control font-monospace @error('business_phone') is-invalid @enderror" 
                                               placeholder="e.g. 9876543210">
                                    </div>
                                    @error('business_phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="business_alt_phone" class="form-label fw-bold text-dark">
                                        {{ __('settings.label_business_alt_phone') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-telephone-plus"></i></span>
                                        <input type="text" 
                                               id="business_alt_phone" 
                                               name="business_alt_phone" 
                                               value="{{ old('business_alt_phone', $s['business.alt_phone'] ?? '') }}" 
                                               class="form-control font-monospace @error('business_alt_phone') is-invalid @enderror" 
                                               placeholder="e.g. 05412-234567">
                                    </div>
                                    @error('business_alt_phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="business_email" class="form-label fw-bold text-dark">
                                        {{ __('settings.label_business_email') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                        <input type="email" 
                                               id="business_email" 
                                               name="business_email" 
                                               value="{{ old('business_email', $s['business.email'] ?? '') }}" 
                                               class="form-control font-monospace @error('business_email') is-invalid @enderror" 
                                               placeholder="contact@kisanenterprises.com">
                                    </div>
                                    @error('business_email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="p-3 rounded-3 bg-light border h-100">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-primary"></i>
                                <span>{{ __('settings.section_tax_ids') }}</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="business_gstin" class="form-label fw-bold text-dark">
                                        {{ __('settings.label_business_gstin') }}
                                    </label>
                                    <input type="text" 
                                           id="business_gstin" 
                                           name="business_gstin" 
                                           value="{{ old('business_gstin', $s['business.gstin'] ?? '') }}" 
                                           class="form-control text-uppercase font-monospace @error('business_gstin') is-invalid @enderror" 
                                           placeholder="e.g. 10IYLPK1340A1Z3" 
                                           autocapitalize="characters"
                                           spellcheck="false"
                                           oninput="this.value = this.value.toUpperCase().replace(/\s+/g, ''); if (this.value.length === 15) { var panEl = document.getElementById('business_pan'); if (panEl && !panEl.value) panEl.value = this.value.substring(2, 12); }"
                                           maxlength="15">
                                    @error('business_gstin')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">{{ __('settings.help_business_gstin') }}</div>
                                </div>

                                <div class="col-12">
                                    <label for="business_pan" class="form-label fw-bold text-dark">
                                        {{ __('settings.label_business_pan') }}
                                    </label>
                                    <input type="text" 
                                           id="business_pan" 
                                           name="business_pan" 
                                           value="{{ old('business_pan', $s['business.pan'] ?? '') }}" 
                                           class="form-control text-uppercase font-monospace @error('business_pan') is-invalid @enderror" 
                                           placeholder="e.g. IYLPK1340A" 
                                           autocapitalize="characters"
                                           spellcheck="false"
                                           oninput="this.value = this.value.toUpperCase().replace(/\s+/g, '')"
                                           maxlength="10">
                                    @error('business_pan')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">{{ __('settings.help_business_pan') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Group: Bank Account & Payment Remittance Details --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-bank2 text-success"></i>
                        <span>Bank Account & Payment Remittance Details (बैंक खाता एवं भुगतान विवरण)</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="business_account_name" class="form-label fw-bold text-dark">
                                Account Holder / Beneficiary Name (खाताधारक का नाम)
                            </label>
                            <input type="text"
                                   id="business_account_name"
                                   name="business_account_name"
                                   value="{{ old('business_account_name', $s['business.account_name'] ?? 'Kisan Enterprises') }}"
                                   class="form-control @error('business_account_name') is-invalid @enderror"
                                   placeholder="e.g. Kisan Enterprises"
                                   maxlength="150">
                            @error('business_account_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">Legal firm name matching the bank account records.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="business_bank_name" class="form-label fw-bold text-dark">
                                Bank Name (बैंक का नाम)
                            </label>
                            <input type="text"
                                   id="business_bank_name"
                                   name="business_bank_name"
                                   value="{{ old('business_bank_name', $s['business.bank_name'] ?? 'State Bank of India') }}"
                                   class="form-control @error('business_bank_name') is-invalid @enderror"
                                   placeholder="e.g. State Bank of India, HDFC Bank, Punjab National Bank"
                                   maxlength="120">
                            @error('business_bank_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="business_account_number" class="form-label fw-bold text-dark">
                                Bank Account Number (बैंक खाता संख्या)
                            </label>
                            <input type="text"
                                   id="business_account_number"
                                   name="business_account_number"
                                   value="{{ old('business_account_number', $s['business.account_number'] ?? '32109876543') }}"
                                   class="form-control font-monospace @error('business_account_number') is-invalid @enderror"
                                   placeholder="Current/Cash Credit Account Number"
                                   maxlength="50">
                            @error('business_account_number')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">Printed on sales invoices & receipts for buyer RTGS/NEFT/IMPS transfers.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="business_ifsc_code" class="form-label fw-bold text-dark">
                                IFSC Code (आईएफएससी कोड)
                            </label>
                            <input type="text"
                                   id="business_ifsc_code"
                                   name="business_ifsc_code"
                                   value="{{ old('business_ifsc_code', $s['business.ifsc_code'] ?? 'SBIN0001234') }}"
                                   class="form-control text-uppercase font-monospace @error('business_ifsc_code') is-invalid @enderror"
                                   placeholder="e.g. SBIN0001234"
                                   maxlength="20">
                            @error('business_ifsc_code')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">11-character Indian Financial System Code.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="business_bank_branch" class="form-label fw-bold text-dark">
                                Branch Name (बैंक शाखा)
                            </label>
                            <input type="text"
                                   id="business_bank_branch"
                                   name="business_bank_branch"
                                   value="{{ old('business_bank_branch', $s['business.bank_branch'] ?? 'Krishi Upaj Mandi Samiti, Chandauli') }}"
                                   class="form-control @error('business_bank_branch') is-invalid @enderror"
                                   placeholder="e.g. Mandi Branch, GT Road, Chandauli"
                                   maxlength="150">
                            @error('business_bank_branch')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="business_upi_id" class="form-label fw-bold text-dark">
                                UPI ID / VPA (यूपीआई आईडी)
                            </label>
                            <input type="text"
                                   id="business_upi_id"
                                   name="business_upi_id"
                                   value="{{ old('business_upi_id', $s['business.upi_id'] ?? 'kisanenterprises@sbi') }}"
                                   class="form-control font-monospace @error('business_upi_id') is-invalid @enderror"
                                   placeholder="e.g. kisanenterprises@sbi, 9876543210@paytm"
                                   maxlength="100">
                            @error('business_upi_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">Optional: Displayed on invoices for instant QR / UPI app transfers.</div>
                        </div>
                    </div>
                </div>

                {{-- Group 3: Business Logo & Branding --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-image text-success"></i>
                        <span>{{ __('settings.section_branding') }}</span>
                    </h6>
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                        @php $logo = $s['business.logo'] ?? null; @endphp
                        <div class="border rounded-3 p-2 bg-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 120px; height: 90px;">
                            <img id="logo-preview" 
                                 src="{{ $logo ? asset('uploads/' . $logo) : '' }}" 
                                 alt="Logo" 
                                 class="{{ $logo ? '' : 'd-none' }} mw-100 mh-100 object-fit-contain">
                            <i class="bi bi-image text-muted fs-2 {{ $logo ? 'd-none' : '' }}" id="logo-placeholder"></i>
                        </div>
                        <div class="flex-grow-1">
                            <label for="logo-input" class="form-label fw-bold text-dark mb-1">
                                {{ __('settings.label_logo') }}
                            </label>
                            <input type="file" 
                                   name="logo" 
                                   id="logo-input" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   accept="image/png,image/jpeg,image/webp" 
                                   data-preview="#logo-preview">
                            @error('logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">{{ __('settings.help_logo') }}</div>
                            @if($logo)
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo" value="1">
                                    <label class="form-check-label text-danger small fw-semibold" for="remove_logo">
                                        <i class="bi bi-trash3 me-1"></i>{{ __('settings.label_remove_logo') }}
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            {{-- ==================== TAB 2: INVOICES & RECEIPTS ==================== --}}
            @elseif($tab === 'documents')
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border">
                            <label for="invoice_header" class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-card-heading text-success"></i>
                                {{ __('settings.section_invoice_header') }}
                            </label>
                            <input type="text" 
                                   id="invoice_header" 
                                   name="invoice_header" 
                                   value="{{ old('invoice_header', $s['documents.invoice_header'] ?? '') }}" 
                                   class="form-control" 
                                   placeholder="e.g. Wholesale Agricultural Grain Trade &amp; Mandi Commission Agent">
                            <div class="form-text text-muted">{{ __('settings.help_invoice_header') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border h-100">
                            <label for="invoice_footer" class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-text-paragraph text-primary"></i>
                                {{ __('settings.section_invoice_footer') }}
                            </label>
                            <textarea id="invoice_footer" 
                                      name="invoice_footer" 
                                      rows="4" 
                                      class="form-control">{{ old('invoice_footer', $s['documents.invoice_footer'] ?? '') }}</textarea>
                            <div class="form-text text-muted">{{ __('settings.help_invoice_footer') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border h-100">
                            <label for="receipt_footer" class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-receipt-cutoff text-warning"></i>
                                {{ __('settings.section_receipt_footer') }}
                            </label>
                            <textarea id="receipt_footer" 
                                      name="receipt_footer" 
                                      rows="4" 
                                      class="form-control">{{ old('receipt_footer', $s['documents.receipt_footer'] ?? '') }}</textarea>
                            <div class="form-text text-muted">{{ __('settings.help_receipt_footer') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card p-3 bg-white border shadow-sm rounded-3 h-100">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="show_signature" 
                                       name="show_signature" 
                                       value="1" 
                                       role="switch"
                                       style="cursor: pointer;"
                                       {{ old('show_signature', $s['documents.show_signature'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="show_signature">
                                    {{ __('settings.label_show_signature') }}
                                </label>
                            </div>
                            <small class="text-muted d-block">{{ __('settings.help_show_signature') }}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card p-3 bg-white border shadow-sm rounded-3 h-100">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="tax_enabled" 
                                       name="tax_enabled" 
                                       value="1" 
                                       role="switch"
                                       style="cursor: pointer;"
                                       {{ old('tax_enabled', $s['tax.enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="tax_enabled">
                                    {{ __('settings.label_tax_enabled') }}
                                </label>
                            </div>
                            <small class="text-muted d-block">{{ __('settings.help_tax_enabled') }}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <label for="tax_label" class="form-label fw-bold text-dark">
                                {{ __('settings.label_tax_label') }}
                            </label>
                            <input type="text" 
                                   id="tax_label" 
                                   name="tax_label" 
                                   value="{{ old('tax_label', $s['tax.label'] ?? 'GST') }}" 
                                   class="form-control font-monospace">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <label for="tax_default_rate" class="form-label fw-bold text-dark">
                                {{ __('settings.label_tax_default_rate') }}
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       max="28" 
                                       id="tax_default_rate" 
                                       name="tax_default_rate" 
                                       value="{{ old('tax_default_rate', $s['tax.default_rate'] ?? '0.00') }}" 
                                       class="form-control font-monospace">
                                <span class="input-group-text bg-white fw-bold">%</span>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- ==================== TAB 3: DOCUMENT NUMBERING ==================== --}}
            @elseif($tab === 'numbering')
                <div class="alert alert-success bg-success-subtle border-success-subtle mb-4 d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle-fill text-success fs-5 flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Standard Document Sequence Format:</strong>
                        <p class="mb-0 small text-dark mt-1">
                            {{ __('settings.numbering_intro', ['fy' => $currentFy]) }}
                        </p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    @php
                        $numberFields = [
                            'purchase' => 'num_purchase',
                            'sales_order' => 'num_sales_order',
                            'sales_invoice' => 'num_sales_invoice',
                            'farmer_payment' => 'num_farmer_payment',
                            'customer_receipt' => 'num_customer_receipt',
                            'expense' => 'num_expense',
                            'farmer' => 'num_farmer',
                            'customer' => 'num_customer',
                        ];
                    @endphp

                    @foreach($numberFields as $key => $transKey)
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border">
                                <label for="num_{{ $key }}" class="form-label fw-bold text-dark">
                                    {{ __('settings.' . $transKey) }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           id="num_{{ $key }}" 
                                           name="{{ $key }}" 
                                           value="{{ old($key, $s['numbering.' . $key] ?? strtoupper($key)) }}" 
                                           class="form-control text-uppercase font-monospace fw-semibold @error($key) is-invalid @enderror" 
                                           required>
                                    <span class="input-group-text small bg-white text-muted font-monospace">
                                        @if(in_array($key, ['farmer', 'customer']))
                                            00001
                                        @else
                                            /{{ $currentFy }}/00001
                                        @endif
                                    </span>
                                </div>
                                @error($key)
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <label for="padding" class="form-label fw-bold text-dark">
                                {{ __('settings.label_padding') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   id="padding" 
                                   name="padding" 
                                   min="3" 
                                   max="8" 
                                   value="{{ old('padding', $s['numbering.padding'] ?? 5) }}" 
                                   class="form-control font-monospace fw-semibold" 
                                   required>
                            <div class="form-text text-muted">{{ __('settings.help_padding') }}</div>
                        </div>
                    </div>
                </div>

            {{-- ==================== TAB 4: SYSTEM & REGIONAL ==================== --}}
            @elseif($tab === 'system')
                {{-- Group 1: Locale & Date Format --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-globe text-success"></i>
                        <span>{{ __('settings.section_locale') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date_format" class="form-label fw-bold text-dark">
                                {{ __('settings.label_date_format') }} <span class="text-danger">*</span>
                            </label>
                            <select id="date_format" name="date_format" class="form-select font-monospace" required>
                                @foreach(config('kisan.date_formats') as $fmt => $sample)
                                    <option value="{{ $fmt }}" {{ old('date_format', $s['locale.date_format'] ?? 'd-m-Y') === $fmt ? 'selected' : '' }}>
                                        {{ $fmt }} &bull; (Sample: {{ $sample }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="financial_year_start_month" class="form-label fw-bold text-dark">
                                {{ __('settings.label_fy_start_month') }} <span class="text-danger">*</span>
                            </label>
                            <select id="financial_year_start_month" name="financial_year_start_month" class="form-select" required>
                                @for($m = 1; $m <= 12; $m++)
                                    @php $monthName = date('F', mktime(0, 0, 0, $m, 1)); @endphp
                                    <option value="{{ $m }}" {{ old('financial_year_start_month', $s['locale.financial_year_start_month'] ?? 4) == $m ? 'selected' : '' }}>
                                        {{ $monthName }} (Month {{ $m }}) {{ $m === 4 ? '— ' . __('settings.help_fy_india') : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Group 2: Security & Session Timeout --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-warning"></i>
                        <span>{{ __('settings.section_security') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="session_timeout_minutes" class="form-label fw-bold text-dark">
                                {{ __('settings.label_session_timeout') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       id="session_timeout_minutes" 
                                       name="session_timeout_minutes" 
                                       min="5" 
                                       max="480" 
                                       value="{{ old('session_timeout_minutes', $s['security.session_timeout_minutes'] ?? 30) }}" 
                                       class="form-control font-monospace" 
                                       required>
                                <span class="input-group-text bg-white">Minutes</span>
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_session_timeout') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Group 3: Inventory Safety & Guardrails --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary"></i>
                        <span>{{ __('settings.section_inventory') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="low_stock_threshold_qtl" class="form-label fw-bold text-dark">
                                {{ __('settings.label_low_stock') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       id="low_stock_threshold_qtl" 
                                       name="low_stock_threshold_qtl" 
                                       value="{{ old('low_stock_threshold_qtl', $s['inventory.low_stock_threshold_qtl'] ?? 10) }}" 
                                       class="form-control font-monospace" 
                                       required>
                                <span class="input-group-text bg-white">Quintals (Qtl)</span>
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_low_stock') }}</div>
                        </div>

                        <div class="col-12">
                            <div class="card p-3 bg-white border shadow-sm rounded-3">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="allow_negative_stock" 
                                           name="allow_negative_stock" 
                                           value="1" 
                                           role="switch"
                                           style="cursor: pointer;"
                                           {{ old('allow_negative_stock', $s['inventory.allow_negative_stock'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger" for="allow_negative_stock">
                                        {{ __('settings.label_allow_negative') }}
                                    </label>
                                </div>
                                <small class="text-muted d-block">{{ __('settings.help_allow_negative') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Group 4: Farmer Delayed Payment Bonus Scheme --}}
                <div class="p-3 rounded-3 bg-success-subtle bg-opacity-10 border border-success border-opacity-50 mb-4">
                    <input type="hidden" name="delayed_bonus_section_present" value="1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-success mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-gift-fill"></i>
                                <span>{{ __('settings.section_delayed_bonus') }}</span>
                            </h6>
                            <small class="text-muted">{{ __('settings.help_delayed_bonus') }}</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="delayed_bonus_enabled"
                                   name="delayed_bonus_enabled"
                                   value="1"
                                   role="switch"
                                   style="cursor: pointer;"
                                   {{ old('delayed_bonus_enabled', $s['procurement.delayed_bonus_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark" for="delayed_bonus_enabled">
                                {{ __('settings.label_delayed_bonus_enabled') }}
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="delayed_bonus_rate" class="form-label fw-bold text-dark">
                                {{ __('settings.label_delayed_bonus_rate') }}
                            </label>
                            <div class="input-group">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       id="delayed_bonus_rate"
                                       name="delayed_bonus_rate"
                                       value="{{ old('delayed_bonus_rate', $s['procurement.delayed_bonus_rate'] ?? '1.00') }}"
                                       class="form-control font-monospace">
                                <span class="input-group-text bg-white fw-bold">%</span>
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_delayed_bonus_rate') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="delayed_bonus_mode" class="form-label fw-bold text-dark">
                                {{ __('settings.label_delayed_bonus_mode') }}
                            </label>
                            <select id="delayed_bonus_mode" name="delayed_bonus_mode" class="form-select">
                                <option value="monthly" {{ old('delayed_bonus_mode', $s['procurement.delayed_bonus_mode'] ?? 'monthly') === 'monthly' ? 'selected' : '' }}>
                                    {{ __('settings.mode_monthly') }}
                                </option>
                                <option value="flat" {{ old('delayed_bonus_mode', $s['procurement.delayed_bonus_mode'] ?? 'monthly') === 'flat' ? 'selected' : '' }}>
                                    {{ __('settings.mode_flat') }}
                                </option>
                                <option value="custom" {{ old('delayed_bonus_mode', $s['procurement.delayed_bonus_mode'] ?? 'monthly') === 'custom' ? 'selected' : '' }}>
                                    {{ __('settings.mode_custom') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            @elseif($tab === 'mail')
                {{-- Quick Presets Banner --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success p-2 rounded-2">
                                <i class="bi bi-magic fs-6"></i>
                            </span>
                            <div>
                                <span class="fw-bold text-dark d-block">{{ __('settings.preset_quick_fill') }}</span>
                                <small class="text-muted">Auto-populate standard hosting and service provider configurations</small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success fw-semibold" onclick="applyMailPreset('hostinger')">
                                <i class="bi bi-hdd-network me-1"></i>{{ __('settings.preset_hostinger') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="applyMailPreset('gmail')">
                                <i class="bi bi-google me-1"></i>{{ __('settings.preset_gmail') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" onclick="applyMailPreset('log')">
                                <i class="bi bi-journal-text me-1"></i>{{ __('settings.preset_log') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Group 1: Driver Selection --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-gear-wide-connected text-success"></i>
                        <span>{{ __('settings.section_mail_driver') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="mailer" class="form-label fw-bold text-dark">
                                {{ __('settings.label_mailer') }} <span class="text-danger">*</span>
                            </label>
                            <select id="mailer" name="mailer" class="form-select font-monospace" onchange="toggleSmtpFields()" required>
                                <option value="smtp" {{ old('mailer', $s['mail.mailer'] ?? 'log') === 'smtp' ? 'selected' : '' }}>
                                    SMTP — {{ __('settings.mailer_smtp') }}
                                </option>
                                <option value="log" {{ old('mailer', $s['mail.mailer'] ?? 'log') === 'log' ? 'selected' : '' }}>
                                    LOG — {{ __('settings.mailer_log') }}
                                </option>
                            </select>
                            <div class="form-text text-muted mt-1">{{ __('settings.help_mailer') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-white h-100 d-flex align-items-center overflow-hidden">
                                @php $isSmtp = old('mailer', $s['mail.mailer'] ?? 'log') === 'smtp'; @endphp
                                <div id="driver-status-badge" class="badge {{ $isSmtp ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 text-wrap text-start d-inline-flex align-items-center gap-2" style="max-width: 100%; font-size: .85rem; line-height: 1.35; white-space: normal;">
                                    <i class="bi {{ $isSmtp ? 'bi-broadcast-pin' : 'bi-shield-check' }} fs-6 flex-shrink-0"></i>
                                    <span id="driver-status-text">{{ $isSmtp ? 'Live SMTP Outbound Active' : 'Safe Log Mode (No Real Emails)' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Group 2: SMTP Server Credentials --}}
                <div id="smtp-settings-group" class="p-3 rounded-3 bg-light border mb-4" style="{{ old('mailer', $s['mail.mailer'] ?? 'log') === 'log' ? 'display: none;' : '' }}">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-server text-primary"></i>
                        <span>{{ __('settings.section_smtp_server') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="host" class="form-label fw-bold text-dark">
                                {{ __('settings.label_smtp_host') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-hdd-network text-muted"></i></span>
                                <input type="text"
                                       id="host"
                                       name="host"
                                       value="{{ old('host', $s['mail.host'] ?? 'smtp.hostinger.com') }}"
                                       class="form-control font-monospace @error('host') is-invalid @enderror"
                                       placeholder="smtp.hostinger.com">
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_smtp_host') }}</div>
                            @error('host') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="port" class="form-label fw-bold text-dark">
                                {{ __('settings.label_smtp_port') }}
                            </label>
                            <input type="number"
                                   id="port"
                                   name="port"
                                   value="{{ old('port', $s['mail.port'] ?? 465) }}"
                                   min="1"
                                   max="65535"
                                   class="form-control font-monospace @error('port') is-invalid @enderror"
                                   placeholder="465">
                            <div class="form-text text-muted">e.g. 465 (SSL) or 587 (TLS)</div>
                            @error('port') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="encryption" class="form-label fw-bold text-dark">
                                {{ __('settings.label_smtp_encryption') }}
                            </label>
                            <select id="encryption" name="encryption" class="form-select font-monospace">
                                <option value="ssl" {{ old('encryption', $s['mail.encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' }}>
                                    SSL (Port 465)
                                </option>
                                <option value="tls" {{ old('encryption', $s['mail.encryption'] ?? 'ssl') === 'tls' ? 'selected' : '' }}>
                                    TLS (Port 587)
                                </option>
                                <option value="none" {{ old('encryption', $s['mail.encryption'] ?? 'ssl') === 'none' ? 'selected' : '' }}>
                                    None (Insecure)
                                </option>
                            </select>
                            <div class="form-text text-muted">{{ __('settings.help_smtp_encryption') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label fw-bold text-dark">
                                {{ __('settings.label_smtp_username') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                                <input type="text"
                                       id="username"
                                       name="username"
                                       value="{{ old('username', $s['mail.username'] ?? '') }}"
                                       class="form-control font-monospace @error('username') is-invalid @enderror"
                                       placeholder="info@kisanenterprises.in">
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_smtp_username') }}</div>
                            @error('username') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="smtp_password" class="form-label fw-bold text-dark">
                                {{ __('settings.label_smtp_password') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-key text-muted"></i></span>
                                <input type="password"
                                       id="smtp_password"
                                       name="password"
                                       class="form-control font-monospace @error('password') is-invalid @enderror"
                                       placeholder="{{ !empty($s['mail.password']) ? '••••••••  (Unchanged — leave blank to keep current)' : 'Enter SMTP mailbox password' }}"
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="btn-toggle-password" onclick="togglePasswordVisibility()" title="Toggle visibility">
                                    <i class="bi bi-eye" id="password-toggle-icon"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_smtp_password') }}</div>
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Group 3: Sender Identity --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge text-warning"></i>
                        <span>{{ __('settings.section_mail_sender') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="from_address" class="form-label fw-bold text-dark">
                                {{ __('settings.label_mail_from_address') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email"
                                       id="from_address"
                                       name="from_address"
                                       value="{{ old('from_address', $s['mail.from_address'] ?? 'noreply@kisanenterprises.in') }}"
                                       class="form-control font-monospace @error('from_address') is-invalid @enderror"
                                       required>
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_mail_from_address') }}</div>
                            @error('from_address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="from_name" class="form-label fw-bold text-dark">
                                {{ __('settings.label_mail_from_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="from_name"
                                   name="from_name"
                                   value="{{ old('from_name', $s['mail.from_name'] ?? ($s['business.name'] ?? 'Kisan Enterprises')) }}"
                                   class="form-control @error('from_name') is-invalid @enderror"
                                   required>
                            <div class="form-text text-muted">{{ __('settings.help_mail_from_name') }}</div>
                            @error('from_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

            {{-- ==================== TAB 6: WHATSAPP API ==================== --}}
            @elseif($tab === 'whatsapp')
                {{-- Quick Presets Banner --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success p-2 rounded-2">
                                <i class="bi bi-whatsapp fs-6"></i>
                            </span>
                            <div>
                                <span class="fw-bold text-dark d-block">{{ __('settings.preset_quick_fill') }}</span>
                                <small class="text-muted">Choose your WhatsApp integration architecture</small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success fw-semibold" onclick="applyWhatsAppPreset('meta')">
                                <i class="bi bi-meta me-1"></i>{{ __('settings.preset_meta_cloud') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="applyWhatsAppPreset('generic')">
                                <i class="bi bi-broadcast me-1"></i>{{ __('settings.preset_generic_api') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" onclick="applyWhatsAppPreset('log')">
                                <i class="bi bi-journal-text me-1"></i>{{ __('settings.preset_whatsapp_log') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Group 1: Gateway & Master Switch --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-success"></i>
                            <span>{{ __('settings.section_whatsapp_gateway') }}</span>
                        </h6>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="whatsapp_enabled"
                                   name="whatsapp_enabled"
                                   value="1"
                                   role="switch"
                                   style="cursor: pointer;"
                                   {{ old('whatsapp_enabled', $s['whatsapp.enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark" for="whatsapp_enabled">
                                {{ __('settings.label_whatsapp_enabled') }}
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="whatsapp_provider" class="form-label fw-bold text-dark">
                                {{ __('settings.label_whatsapp_provider') }} <span class="text-danger">*</span>
                            </label>
                            <select id="whatsapp_provider" name="whatsapp_provider" class="form-select font-monospace" onchange="toggleWhatsAppFields()" required>
                                <option value="meta" {{ old('whatsapp_provider', $s['whatsapp.provider'] ?? 'log') === 'meta' ? 'selected' : '' }}>
                                    {{ __('settings.provider_meta') }}
                                </option>
                                <option value="generic" {{ old('whatsapp_provider', $s['whatsapp.provider'] ?? 'log') === 'generic' ? 'selected' : '' }}>
                                    {{ __('settings.provider_generic') }}
                                </option>
                                <option value="log" {{ old('whatsapp_provider', $s['whatsapp.provider'] ?? 'log') === 'log' ? 'selected' : '' }}>
                                    {{ __('settings.provider_log') }}
                                </option>
                            </select>
                            <div class="form-text text-muted mt-1">{{ __('settings.help_whatsapp_provider') }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-white h-100 d-flex align-items-center overflow-hidden">
                                @php
                                    $wProv = old('whatsapp_provider', $s['whatsapp.provider'] ?? 'log');
                                    $wOn = old('whatsapp_enabled', $s['whatsapp.enabled'] ?? '0') == '1';
                                @endphp
                                <div id="wa-status-badge" class="badge {{ $wOn && $wProv !== 'log' ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 text-wrap text-start d-inline-flex align-items-center gap-2" style="max-width: 100%; font-size: .85rem; line-height: 1.35; white-space: normal;">
                                    <i class="bi {{ $wOn && $wProv !== 'log' ? 'bi-broadcast-pin' : 'bi-shield-check' }} fs-6 flex-shrink-0"></i>
                                    <span id="wa-status-text">
                                        @if(!$wOn)
                                            WhatsApp Service Disabled (Offline)
                                        @elseif($wProv === 'meta')
                                            Meta WhatsApp Cloud API Active
                                        @elseif($wProv === 'generic')
                                            Generic Webhook Gateway Active
                                        @else
                                            Safe Log Mode (No Real WhatsApp)
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Group 2: Meta Cloud API Settings --}}
                <div id="meta-settings-group" class="p-3 rounded-3 bg-light border mb-4" style="{{ old('whatsapp_provider', $s['whatsapp.provider'] ?? 'log') !== 'meta' ? 'display: none;' : '' }}">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-meta text-primary"></i>
                        <span>{{ __('settings.section_meta_config') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="meta_phone_number_id" class="form-label fw-bold text-dark">
                                {{ __('settings.label_meta_phone_id') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-phone text-muted"></i></span>
                                <input type="text"
                                       id="meta_phone_number_id"
                                       name="meta_phone_number_id"
                                       value="{{ old('meta_phone_number_id', $s['whatsapp.meta_phone_number_id'] ?? '') }}"
                                       class="form-control font-monospace @error('meta_phone_number_id') is-invalid @enderror"
                                       placeholder="e.g. 104598214589123">
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_meta_phone_id') }}</div>
                            @error('meta_phone_number_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="meta_waba_id" class="form-label fw-bold text-dark">
                                {{ __('settings.label_meta_waba_id') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-building-gear text-muted"></i></span>
                                <input type="text"
                                       id="meta_waba_id"
                                       name="meta_waba_id"
                                       value="{{ old('meta_waba_id', $s['whatsapp.meta_waba_id'] ?? '') }}"
                                       class="form-control font-monospace @error('meta_waba_id') is-invalid @enderror"
                                       placeholder="e.g. 109823471829341">
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_meta_waba_id') }}</div>
                            @error('meta_waba_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-9">
                            <label for="meta_access_token" class="form-label fw-bold text-dark">
                                {{ __('settings.label_meta_token') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-key text-muted"></i></span>
                                <input type="password"
                                       id="meta_access_token"
                                       name="meta_access_token"
                                       class="form-control font-monospace @error('meta_access_token') is-invalid @enderror"
                                       placeholder="{{ !empty($s['whatsapp.meta_access_token']) ? '••••••••••••••••••••  (Unchanged — leave blank to keep current)' : 'EAAG...' }}"
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility('meta_access_token', 'meta-token-icon')" title="Toggle visibility">
                                    <i class="bi bi-eye" id="meta-token-icon"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_meta_token') }}</div>
                            @error('meta_access_token') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="meta_api_version" class="form-label fw-bold text-dark">
                                {{ __('settings.label_meta_version') }}
                            </label>
                            <input type="text"
                                   id="meta_api_version"
                                   name="meta_api_version"
                                   value="{{ old('meta_api_version', $s['whatsapp.meta_api_version'] ?? 'v21.0') }}"
                                   class="form-control font-monospace"
                                   placeholder="v21.0">
                            <div class="form-text text-muted">Default: v21.0</div>
                        </div>
                    </div>
                </div>

                {{-- Group 3: Generic REST Webhook Settings --}}
                <div id="generic-settings-group" class="p-3 rounded-3 bg-light border mb-4" style="{{ old('whatsapp_provider', $s['whatsapp.provider'] ?? 'log') !== 'generic' ? 'display: none;' : '' }}">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-hdd-network text-primary"></i>
                        <span>{{ __('settings.section_generic_config') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="generic_endpoint" class="form-label fw-bold text-dark">
                                {{ __('settings.label_generic_endpoint') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-link-45deg text-muted"></i></span>
                                <input type="url"
                                       id="generic_endpoint"
                                       name="generic_endpoint"
                                       value="{{ old('generic_endpoint', $s['whatsapp.generic_endpoint'] ?? '') }}"
                                       class="form-control font-monospace @error('generic_endpoint') is-invalid @enderror"
                                       placeholder="https://api.yourgateway.com/v1/send">
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_generic_endpoint') }}</div>
                            @error('generic_endpoint') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="sender_phone" class="form-label fw-bold text-dark">
                                {{ __('settings.label_sender_phone') }}
                            </label>
                            <input type="text"
                                   id="sender_phone"
                                   name="sender_phone"
                                   value="{{ old('sender_phone', $s['whatsapp.sender_phone'] ?? '') }}"
                                   class="form-control font-monospace"
                                   placeholder="e.g. 919876543210">
                            <div class="form-text text-muted">{{ __('settings.help_sender_phone') }}</div>
                        </div>

                        <div class="col-12">
                            <label for="generic_api_key" class="form-label fw-bold text-dark">
                                {{ __('settings.label_generic_api_key') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-key text-muted"></i></span>
                                <input type="password"
                                       id="generic_api_key"
                                       name="generic_api_key"
                                       class="form-control font-monospace @error('generic_api_key') is-invalid @enderror"
                                       placeholder="{{ !empty($s['whatsapp.generic_api_key']) ? '••••••••••••••••••••  (Unchanged — leave blank to keep current)' : 'Enter API Key or Authorization Token' }}"
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility('generic_api_key', 'generic-key-icon')" title="Toggle visibility">
                                    <i class="bi bi-eye" id="generic-key-icon"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">{{ __('settings.help_generic_api_key') }}</div>
                            @error('generic_api_key') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Group 4: Automated Alerts & Notifications --}}
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-bell text-warning"></i>
                        <span>{{ __('settings.section_auto_notifications') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card p-3 bg-white border shadow-sm rounded-3 h-100">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="auto_notify_purchase"
                                           name="auto_notify_purchase"
                                           value="1"
                                           role="switch"
                                           style="cursor: pointer;"
                                           {{ old('auto_notify_purchase', $s['whatsapp.auto_notify_purchase'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="auto_notify_purchase">
                                        {{ __('settings.label_notify_purchase') }}
                                    </label>
                                </div>
                                <small class="text-muted d-block">{{ __('settings.help_notify_purchase') }}</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-3 bg-white border shadow-sm rounded-3 h-100">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="auto_notify_payment"
                                           name="auto_notify_payment"
                                           value="1"
                                           role="switch"
                                           style="cursor: pointer;"
                                           {{ old('auto_notify_payment', $s['whatsapp.auto_notify_payment'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="auto_notify_payment">
                                        {{ __('settings.label_notify_payment') }}
                                    </label>
                                </div>
                                <small class="text-muted d-block">{{ __('settings.help_notify_payment') }}</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-3 bg-white border shadow-sm rounded-3 h-100">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="auto_notify_sale"
                                           name="auto_notify_sale"
                                           value="1"
                                           role="switch"
                                           style="cursor: pointer;"
                                           {{ old('auto_notify_sale', $s['whatsapp.auto_notify_sale'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="auto_notify_sale">
                                        {{ __('settings.label_notify_sale') }}
                                    </label>
                                </div>
                                <small class="text-muted d-block">{{ __('settings.help_notify_sale') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Save Action Footer --}}
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i>
                    {{ __('settings.btn_save', ['tab' => $tabs[$tab]]) }}
                </button>
            </div>
        </form>
    </div>
</div>

@if($tab === 'mail')
    {{-- 5. Send Test Email Verification Card --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary p-2 rounded-2">
                    <i class="bi bi-send-check fs-6"></i>
                </span>
                <span>{{ __('settings.section_mail_test') }}</span>
            </h5>
            <span class="badge bg-light text-muted border">Diagnostic Tool</span>
        </div>
        <div class="card-body p-4">
            <p class="text-muted small mb-3">
                {{ __('settings.desc_mail_test') }}
            </p>

            @if(($s['mail.mailer'] ?? 'log') === 'log')
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                    <div class="small">
                        {{ __('settings.alert_log_mode') }}
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.test_email') }}" id="form-test-email">
                @csrf
                <x-form-token />
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="test_email" class="form-label fw-bold text-dark">
                            {{ __('settings.label_test_email') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-at text-muted"></i></span>
                            <input type="email"
                                   id="test_email"
                                   name="test_email"
                                   value="{{ old('test_email', auth()->user()?->email ?? ($s['business.email'] ?? '')) }}"
                                   class="form-control font-monospace"
                                   placeholder="you@yourdomain.com"
                                   required>
                        </div>
                        <div class="form-text text-muted">{{ __('settings.help_test_email') }}</div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-success fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" id="btn-submit-test-email">
                            <i class="bi bi-send"></i>
                            <span>{{ __('settings.btn_send_test') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Script for presets & password toggle --}}
    <script>
        function toggleSmtpFields() {
            var mailer = document.getElementById('mailer').value;
            var smtpGroup = document.getElementById('smtp-settings-group');
            var statusBadge = document.getElementById('driver-status-badge');
            var statusText = document.getElementById('driver-status-text');

            statusBadge.style.fontSize = '.85rem';
            statusBadge.style.lineHeight = '1.35';
            statusBadge.style.whiteSpace = 'normal';

            if (mailer === 'smtp') {
                smtpGroup.style.display = 'block';
                statusBadge.className = 'badge bg-success px-3 py-2 text-wrap text-start d-inline-flex align-items-center gap-2';
                statusText.innerText = 'Live SMTP Outbound Active';
            } else {
                smtpGroup.style.display = 'none';
                statusBadge.className = 'badge bg-warning text-dark px-3 py-2 text-wrap text-start d-inline-flex align-items-center gap-2';
                statusText.innerText = 'Safe Log Mode (No Real Emails)';
            }
        }

        function applyMailPreset(preset) {
            var mailer = document.getElementById('mailer');
            var host = document.getElementById('host');
            var port = document.getElementById('port');
            var enc = document.getElementById('encryption');

            if (preset === 'hostinger') {
                mailer.value = 'smtp';
                host.value = 'smtp.hostinger.com';
                port.value = '465';
                enc.value = 'ssl';
            } else if (preset === 'gmail') {
                mailer.value = 'smtp';
                host.value = 'smtp.gmail.com';
                port.value = '587';
                enc.value = 'tls';
            } else if (preset === 'log') {
                mailer.value = 'log';
            }
            toggleSmtpFields();
        }

        function togglePasswordVisibility() {
            var input = document.getElementById('smtp_password');
            var icon = document.getElementById('password-toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        document.getElementById('form-test-email')?.addEventListener('submit', function() {
            var btn = document.getElementById('btn-submit-test-email');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Sending...';
            }
        });
    </script>
@endif

@if($tab === 'whatsapp')
    {{-- 6. Send Test WhatsApp Verification Card --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success p-2 rounded-2">
                    <i class="bi bi-whatsapp fs-6"></i>
                </span>
                <span>{{ __('settings.section_whatsapp_test') }}</span>
            </h5>
            <span class="badge bg-light text-muted border">Diagnostic Tool</span>
        </div>
        <div class="card-body p-4">
            <p class="text-muted small mb-3">
                {{ __('settings.desc_whatsapp_test') }}
            </p>

            @if(($s['whatsapp.provider'] ?? 'log') === 'log')
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                    <div class="small">
                        {{ __('settings.alert_whatsapp_log_mode') }}
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.test_whatsapp') }}" id="form-test-whatsapp">
                @csrf
                <x-form-token />
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="test_mobile" class="form-label fw-bold text-dark">
                            {{ __('settings.label_test_mobile') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-telephone text-muted"></i></span>
                            <input type="text"
                                   id="test_mobile"
                                   name="test_mobile"
                                   value="{{ old('test_mobile', auth()->user()?->mobile ?? ($s['business.phone'] ?? '')) }}"
                                   class="form-control font-monospace"
                                   placeholder="9876543210"
                                   required>
                        </div>
                        <div class="form-text text-muted">{{ __('settings.help_test_mobile') }}</div>
                    </div>
                    <div class="col-md-5">
                        <label for="test_message" class="form-label fw-bold text-dark">
                            {{ __('settings.label_test_message') }}
                        </label>
                        <input type="text"
                               id="test_message"
                               name="test_message"
                               value="{{ old('test_message') }}"
                               class="form-control"
                               placeholder="Leave blank for standard verification ping">
                        <div class="form-text text-muted">Supports bold *text*, emojis 🌾, and line breaks.</div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-success fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" id="btn-submit-test-whatsapp">
                            <i class="bi bi-send-fill"></i>
                            <span>{{ __('settings.btn_send_test') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Script for WhatsApp presets & toggles --}}
    <script>
        function toggleWhatsAppFields() {
            var prov = document.getElementById('whatsapp_provider').value;
            var enabled = document.getElementById('whatsapp_enabled').checked;
            var metaGroup = document.getElementById('meta-settings-group');
            var genericGroup = document.getElementById('generic-settings-group');
            var statusBadge = document.getElementById('wa-status-badge');
            var statusText = document.getElementById('wa-status-text');

            if (metaGroup) metaGroup.style.display = (prov === 'meta') ? 'block' : 'none';
            if (genericGroup) genericGroup.style.display = (prov === 'generic') ? 'block' : 'none';

            statusBadge.style.fontSize = '.85rem';
            statusBadge.style.lineHeight = '1.35';
            statusBadge.style.whiteSpace = 'normal';

            var baseBadgeClass = 'badge px-3 py-2 text-wrap text-start d-inline-flex align-items-center gap-2 ';

            if (!enabled) {
                statusBadge.className = baseBadgeClass + 'bg-secondary';
                statusText.innerText = 'WhatsApp Service Disabled (Offline)';
            } else if (prov === 'meta') {
                statusBadge.className = baseBadgeClass + 'bg-success';
                statusText.innerText = 'Meta WhatsApp Cloud API Active';
            } else if (prov === 'generic') {
                statusBadge.className = baseBadgeClass + 'bg-info text-dark';
                statusText.innerText = 'Generic Webhook Gateway Active';
            } else {
                statusBadge.className = baseBadgeClass + 'bg-warning text-dark';
                statusText.innerText = 'Safe Log Mode (No Real WhatsApp)';
            }
        }

        function applyWhatsAppPreset(preset) {
            var prov = document.getElementById('whatsapp_provider');
            var enabled = document.getElementById('whatsapp_enabled');

            enabled.checked = true;
            if (preset === 'meta') {
                prov.value = 'meta';
            } else if (preset === 'generic') {
                prov.value = 'generic';
            } else if (preset === 'log') {
                prov.value = 'log';
            }
            toggleWhatsAppFields();
        }

        function toggleTokenVisibility(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        document.getElementById('whatsapp_enabled')?.addEventListener('change', toggleWhatsAppFields);

        document.getElementById('form-test-whatsapp')?.addEventListener('submit', function() {
            var btn = document.getElementById('btn-submit-test-whatsapp');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Sending...';
            }
        });
    </script>
@endif
@endsection
