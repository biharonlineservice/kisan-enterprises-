@extends('layouts.app')

@section('title', $purchase->exists ? __('procurement.purchases.edit') . ' ' . $purchase->purchase_number : __('procurement.purchases.add_new'))

@section('content')
{{-- Executive Weighbridge Entry Banner --}}
<div class="erp-hero-banner">
    <div class="position-relative z-1 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-receipt-cutoff text-warning"></i>
                    <strong>{{ $purchase->exists ? $purchase->purchase_number : 'Auto Tulai Parchi (PUR...)' }}</strong>
                </span>
                <span class="erp-hero-chip">
                    <i class="bi bi-speedometer2"></i> {{ __('procurement.purchases.weighment_section') }}
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">
                {{ $purchase->exists ? __('procurement.purchases.edit') . ': ' . $purchase->purchase_number : __('procurement.purchases.add_new') }}
            </h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                {{ __('procurement.purchases.banner_subtitle') }}
            </p>
        </div>
        <div>
            <a href="{{ $purchase->exists ? route('purchases.show', $purchase) : route('purchases.index') }}" class="erp-hero-btn">
                <i class="bi bi-arrow-left"></i>
                <span>{{ __('procurement.purchases.back_to_purchases') }}</span>
            </a>
        </div>
    </div>
</div>

<form method="POST" action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}" id="purchaseForm">
    @csrf
    @if($purchase->exists)
        @method('PUT')
    @endif
    <x-form-token />

    <div class="row g-4">
        {{-- Left Column: Primary Details, Weighment & Logistics --}}
        <div class="col-12 col-lg-8">
            {{-- Section 1: Party, Crop & Destination Godown --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 mb-0 fw-bold d-flex align-items-center gap-2">
                        <span class="kpi-badge-icon" style="width:28px;height:28px;font-size:.85rem;background:#E8F3EC;color:#21864B;">
                            <i class="bi bi-person-check-fill"></i>
                        </span>
                        {{ __('procurement.purchases.details') }}
                    </h5>
                    @can('farmers.create')
                        <a href="{{ route('farmers.create') }}" class="btn btn-xs btn-light border text-success">
                            <i class="bi bi-person-plus"></i> {{ __('procurement.farmers.add_new') }}
                        </a>
                    @endcan
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label required">{{ __('procurement.purchases.purchase_date') }}</label>
                            <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                                value="{{ old('purchase_date', $purchase->purchase_date ? \Illuminate\Support\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                            @error('purchase_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label required">{{ __('procurement.purchases.farmer') }}</label>
                            <select name="farmer_id" id="farmer_id" class="form-select @error('farmer_id') is-invalid @enderror" required>
                                <option value="">{{ __('procurement.farmers.search_placeholder') }}</option>
                                @foreach($farmers as $f)
                                    <option value="{{ $f->id }}"
                                            data-village="{{ $f->village }}"
                                            data-mobile="{{ $f->formattedMobile() }}"
                                            data-bonus="{{ $f->isDelayedBonusEnabled() ? ($f->effective_bonus_rate . '% ' . ucfirst($f->effective_bonus_mode)) : 'Off' }}"
                                            @selected(old('farmer_id', $selectedFarmerId ?? $purchase->farmer_id) == $f->id)>
                                        {{ $f->name }} ({{ $f->farmer_id }}) — {{ $f->village ?? $f->mobile }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="farmer_meta_hint" class="small text-muted mt-1 d-none"></div>
                            @error('farmer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required">{{ __('procurement.purchases.crop') }}</label>
                            <select name="crop_id" id="crop_id" class="form-select @error('crop_id') is-invalid @enderror" required>
                                <option value="">{{ __('app.select_option') }}</option>
                                @foreach($crops as $c)
                                    <option value="{{ $c->id }}"
                                            data-rate="{{ $c->current_purchase_rate ? bc_round((string) $c->current_purchase_rate, 2) : '' }}"
                                            data-rate-label="{{ $c->formattedPurchaseRate() ?? '' }}"
                                            @selected(old('crop_id', $purchase->crop_id) == $c->id)>
                                        {{ $c->name }} ({{ $c->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('crop_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">{{ __('procurement.purchases.variety') }}</label>
                            <select name="crop_variety_id" id="crop_variety_id" class="form-select @error('crop_variety_id') is-invalid @enderror">
                                <option value="">Standard / Mixed</option>
                                @foreach($crops as $c)
                                    @foreach($c->varieties as $v)
                                        <option value="{{ $v->id }}"
                                                data-crop="{{ $c->id }}"
                                                data-moisture="{{ $v->standard_moisture_pct ?? '' }}"
                                                data-grade="{{ $v->default_grade ?? '' }}"
                                                @selected(old('crop_variety_id', $purchase->crop_variety_id) == $v->id)>
                                            {{ $c->name }} - {{ $v->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('crop_variety_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required">{{ __('procurement.purchases.warehouse') }}</label>
                            <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                                <option value="">{{ __('app.select_option') }}</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" @selected(old('warehouse_id', $purchase->warehouse_id) == $w->id)>
                                        {{ $w->name }} ({{ $w->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Weighbridge & Quality Inspection --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 mb-0 fw-bold d-flex align-items-center gap-2">
                        <span class="kpi-badge-icon" style="width:28px;height:28px;font-size:.85rem;background:#E0F2FE;color:#0284C7;">
                            <i class="bi bi-speedometer2"></i>
                        </span>
                        {{ __('procurement.purchases.weighment_section') }}
                    </h5>
                    <span class="badge bg-light text-muted border">{{ __('procurement.purchases.auto_calculated') }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('procurement.purchases.gross_weight') }}</label>
                            <div class="input-group">
                                <input type="number" step="0.001" min="0.001" inputmode="decimal" name="gross_weight_kg" id="gross_weight_kg"
                                    class="form-control text-end fw-semibold @error('gross_weight_kg') is-invalid @enderror"
                                    value="{{ old('gross_weight_kg', $purchase->gross_weight_kg) }}" required placeholder="0.000">
                                <span class="input-group-text">Kg</span>
                            </div>
                            @error('gross_weight_kg') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('procurement.purchases.tare_weight') }}</label>
                            <div class="input-group">
                                <input type="number" step="0.001" min="0" inputmode="decimal" name="tare_weight_kg" id="tare_weight_kg"
                                    class="form-control text-end @error('tare_weight_kg') is-invalid @enderror"
                                    value="{{ old('tare_weight_kg', $purchase->tare_weight_kg ?? '0.000') }}" placeholder="0.000">
                                <span class="input-group-text">Kg</span>
                            </div>
                            @error('tare_weight_kg') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <span class="text-muted small d-block">{{ __('procurement.purchases.net_weight_kg') }}</span>
                                <span class="fs-4 fw-bold text-dark" id="display_net_kg">0.000</span>
                                <small class="text-muted d-block">Kg (Gross - Tare)</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="p-3 rounded-3 border border-success-subtle h-100" style="background: #F4FBF6;">
                                <span class="text-success small fw-semibold d-block">{{ __('procurement.purchases.net_weight_qtl') }}</span>
                                <span class="fs-4 fw-bold text-success" id="display_net_qtl">0.000</span>
                                <small class="text-muted d-block">1 Qtl = 100 Kg</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('procurement.purchases.moisture') }}</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" inputmode="decimal" name="moisture_pct" id="moisture_pct"
                                    class="form-control text-end @error('moisture_pct') is-invalid @enderror"
                                    value="{{ old('moisture_pct', $purchase->moisture_pct) }}" placeholder="e.g. 14.00">
                                <span class="input-group-text">%</span>
                            </div>
                            <small id="variety_moisture_hint" class="text-muted d-none"></small>
                            @error('moisture_pct') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('procurement.purchases.quality_grade') }}</label>
                            <select name="quality_grade" id="quality_grade" class="form-select @error('quality_grade') is-invalid @enderror">
                                <option value="Grade A" @selected(old('quality_grade', $purchase->quality_grade) === 'Grade A')>Grade A (Premium)</option>
                                <option value="Grade B" @selected(old('quality_grade', $purchase->quality_grade) === 'Grade B')>Grade B (Standard)</option>
                                <option value="Standard" @selected(old('quality_grade', $purchase->quality_grade) === 'Standard')>Standard</option>
                                <option value="Substandard" @selected(old('quality_grade', $purchase->quality_grade) === 'Substandard')>Substandard</option>
                            </select>
                            @error('quality_grade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Logistics & Transport --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 mb-0 fw-bold d-flex align-items-center gap-2">
                        <span class="kpi-badge-icon" style="width:28px;height:28px;font-size:.85rem;background:#FEF3C7;color:#B45309;">
                            <i class="bi bi-truck"></i>
                        </span>
                        {{ __('procurement.purchases.transport_section') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('procurement.purchases.vehicle_number') }}</label>
                            <input type="text" name="vehicle_number" class="form-control text-uppercase @error('vehicle_number') is-invalid @enderror"
                                value="{{ old('vehicle_number', $purchase->vehicle_number) }}" placeholder="e.g. BR 24 GA 4512">
                            @error('vehicle_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('procurement.purchases.driver_name') }}</label>
                            <input type="text" name="driver_name" class="form-control @error('driver_name') is-invalid @enderror"
                                value="{{ old('driver_name', $purchase->driver_name) }}" placeholder="Driver / Transporter">
                            @error('driver_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('app.remarks') }}</label>
                            <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                placeholder="{{ __('app.remarks') }}...">{{ old('remarks', $purchase->remarks) }}</textarea>
                            @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Rate, Live Mandi Auto-Fill & Settlement Summary --}}
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 1.5rem;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #123D26, #21864B);">
                    <h5 class="card-title h6 mb-0 text-white fw-bold">
                        <i class="bi bi-calculator me-1"></i> {{ __('procurement.purchases.financial_section') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label required mb-0">{{ __('procurement.purchases.rate_per_qtl') }}</label>
                            <button type="button" id="apply_mandi_rate_btn" class="btn btn-xs btn-outline-success d-none">
                                <i class="bi bi-lightning-charge-fill"></i> <span id="mandi_rate_btn_label"></span>
                            </button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="1" inputmode="decimal" name="rate_per_qtl" id="rate_per_qtl"
                                class="form-control text-end fw-bold @error('rate_per_qtl') is-invalid @enderror"
                                value="{{ old('rate_per_qtl', $purchase->rate_per_qtl) }}" required placeholder="0.00">
                        </div>
                        @error('rate_per_qtl') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between text-dark small fw-semibold mb-1">
                            <span>{{ __('procurement.purchases.base_amount') }}:</span>
                            <span id="display_base_amount">₹0.00</span>
                        </div>
                        <small class="text-muted d-block">{{ __('procurement.purchases.net_weight_qtl') }} &times; {{ __('procurement.purchases.rate_per_qtl') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('procurement.purchases.deductions') }}</label>
                        <div class="input-group mb-1">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="0" inputmode="decimal" name="deductions_amount" id="deductions_amount"
                                class="form-control text-end text-danger @error('deductions_amount') is-invalid @enderror"
                                value="{{ old('deductions_amount', $purchase->deductions_amount ?? '0.00') }}" placeholder="0.00">
                        </div>
                        <input type="text" name="deduction_reason" class="form-control form-control-sm"
                            placeholder="{{ __('procurement.purchases.deduction_reason') }}" value="{{ old('deduction_reason', $purchase->deduction_reason) }}">
                        @error('deductions_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('procurement.purchases.additions') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="0" inputmode="decimal" name="additions_amount" id="additions_amount"
                                class="form-control text-end text-success @error('additions_amount') is-invalid @enderror"
                                value="{{ old('additions_amount', $purchase->additions_amount ?? '0.00') }}" placeholder="0.00">
                        </div>
                        @error('additions_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="p-3 bg-success-subtle border border-success-subtle rounded-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success">{{ __('procurement.purchases.net_payable') }}:</span>
                            <span class="fs-4 fw-bold text-success" id="display_net_payable">₹0.00</span>
                        </div>
                    </div>

                    @if(!$purchase->exists)
                        <div class="border-top pt-3 mb-4">
                            <h6 class="text-muted small fw-bold text-uppercase mb-2">{{ __('procurement.purchases.instant_settlement_heading') }}</h6>
                            <div class="mb-2">
                                <label class="form-label small">{{ __('procurement.purchases.paid_amount') }}</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" name="initial_paid_amount" id="initial_paid_amount"
                                        class="form-control text-end" value="{{ old('initial_paid_amount', '0.00') }}">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small">{{ __('procurement.payments.payment_mode') }}</label>
                                    <select name="payment_method" class="form-select form-select-sm">
                                        <option value="cash">{{ __('procurement.payments.mode_cash') }}</option>
                                        <option value="bank_transfer">{{ __('procurement.payments.mode_bank') }}</option>
                                        <option value="upi">{{ __('procurement.payments.mode_upi') }}</option>
                                        <option value="cheque">{{ __('procurement.payments.mode_cheque') }}</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">{{ __('app.reference') }}</label>
                                    <input type="text" name="payment_reference" class="form-control form-control-sm" placeholder="UTR / Chq #">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-none d-md-grid gap-2">
                        @can('purchases.finalize')
                            <button type="submit" name="action" value="finalize" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> {{ __('procurement.purchases.finalize') }}
                            </button>
                            @can('sales.create')
                                <button type="submit" name="action" value="finalize_sell" class="btn btn-outline-success">
                                    <i class="bi bi-truck"></i> {{ __('procurement.purchases.finalize_and_sell') }}
                                </button>
                            @endcan
                        @endcan
                        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                            <i class="bi bi-save"></i> {{ __('procurement.purchases.save_draft') }}
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-link text-muted btn-sm">{{ __('app.cancel') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Sticky Action Bar (< 768px) --}}
    <div class="mobile-sticky-actions">
        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
            <i class="bi bi-save"></i> {{ __('procurement.purchases.save_draft') }}
        </button>
        @can('purchases.finalize')
            <button type="submit" name="action" value="finalize" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> {{ __('procurement.purchases.finalize') }}
            </button>
            @can('sales.create')
                <button type="submit" name="action" value="finalize_sell" class="btn btn-outline-success">
                    <i class="bi bi-truck"></i> {{ __('procurement.purchases.direct_sale') }}
                </button>
            @endcan
        @endcan
    </div>
    <div class="pb-5 mb-4 d-md-none"></div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const grossEl = document.getElementById('gross_weight_kg');
    const tareEl = document.getElementById('tare_weight_kg');
    const rateEl = document.getElementById('rate_per_qtl');
    const dedEl = document.getElementById('deductions_amount');
    const addEl = document.getElementById('additions_amount');

    const displayNetKg = document.getElementById('display_net_kg');
    const displayNetQtl = document.getElementById('display_net_qtl');
    const displayBase = document.getElementById('display_base_amount');
    const displayNetPayable = document.getElementById('display_net_payable');

    function calculateLive() {
        const gross = parseFloat(grossEl.value) || 0;
        const tare = parseFloat(tareEl.value) || 0;
        const rate = parseFloat(rateEl.value) || 0;
        const ded = parseFloat(dedEl.value) || 0;
        const add = parseFloat(addEl.value) || 0;

        let netKg = gross - tare;
        if (netKg < 0) netKg = 0;

        const netQtl = netKg / 100.0;
        const baseAmount = netQtl * rate;
        let netPayable = baseAmount + add - ded;
        if (netPayable < 0) netPayable = 0;

        displayNetKg.textContent = netKg.toLocaleString('en-IN', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
        displayNetQtl.textContent = netQtl.toLocaleString('en-IN', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
        displayBase.textContent = '₹' + baseAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        displayNetPayable.textContent = '₹' + netPayable.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    [grossEl, tareEl, rateEl, dedEl, addEl].forEach(el => {
        if (el) el.addEventListener('input', calculateLive);
    });
    calculateLive();

    // Selected Farmer Metadata Hint
    const farmerSelect = document.getElementById('farmer_id');
    const farmerHint = document.getElementById('farmer_meta_hint');
    if (farmerSelect && farmerHint) {
        function syncFarmerMeta() {
            const opt = farmerSelect.options[farmerSelect.selectedIndex];
            if (!opt || !opt.value) {
                farmerHint.classList.add('d-none');
                return;
            }
            const parts = [];
            if (opt.dataset.village) parts.push('<i class="bi bi-geo-alt text-success"></i> ' + opt.dataset.village);
            if (opt.dataset.mobile) parts.push('<i class="bi bi-telephone text-primary"></i> ' + opt.dataset.mobile);
            if (opt.dataset.bonus) parts.push('<i class="bi bi-gift text-success"></i> Bonus: <strong>' + opt.dataset.bonus + '</strong>');
            farmerHint.innerHTML = parts.join(' &nbsp;&bull;&nbsp; ');
            farmerHint.classList.toggle('d-none', parts.length === 0);
        }
        farmerSelect.addEventListener('change', syncFarmerMeta);
        syncFarmerMeta();
    }

    // Crop variety dynamic filtering + Live Mandi Rate Auto-Fill
    const cropSelect = document.getElementById('crop_id');
    const varietySelect = document.getElementById('crop_variety_id');
    const applyMandiBtn = document.getElementById('apply_mandi_rate_btn');
    const mandiBtnLabel = document.getElementById('mandi_rate_btn_label');
    const moistureHint = document.getElementById('variety_moisture_hint');
    let rateManuallyEdited = rateEl && rateEl.value !== '';

    if (rateEl) {
        rateEl.addEventListener('input', function() {
            rateManuallyEdited = rateEl.value !== '';
        });
    }

    if (cropSelect) {
        function syncCropAndVarieties(autoFillRate) {
            const cropId = cropSelect.value;
            const selectedCropOpt = cropSelect.options[cropSelect.selectedIndex];
            const liveRate = selectedCropOpt ? selectedCropOpt.dataset.rate : '';
            const liveRateLabel = selectedCropOpt ? selectedCropOpt.dataset.rateLabel : '';

            if (varietySelect) {
                varietySelect.querySelectorAll('option').forEach(opt => {
                    if (!opt.value) return;
                    opt.hidden = Boolean(cropId && opt.dataset.crop !== cropId);
                });
            }

            if (applyMandiBtn && mandiBtnLabel) {
                if (liveRate) {
                    mandiBtnLabel.textContent = liveRateLabel || ('₹' + liveRate);
                    applyMandiBtn.dataset.rate = liveRate;
                    applyMandiBtn.classList.remove('d-none');
                    if (autoFillRate && !rateManuallyEdited && rateEl) {
                        rateEl.value = liveRate;
                        calculateLive();
                    }
                } else {
                    applyMandiBtn.classList.add('d-none');
                }
            }
        }

        cropSelect.addEventListener('change', function() {
            syncCropAndVarieties(true);
        });
        syncCropAndVarieties(false);

        if (applyMandiBtn && rateEl) {
            applyMandiBtn.addEventListener('click', function() {
                if (applyMandiBtn.dataset.rate) {
                    rateEl.value = applyMandiBtn.dataset.rate;
                    rateManuallyEdited = true;
                    calculateLive();
                }
            });
        }
    }

    if (varietySelect && moistureHint) {
        function syncVarietyHint() {
            const opt = varietySelect.options[varietySelect.selectedIndex];
            if (opt && opt.value && opt.dataset.moisture) {
                moistureHint.textContent = 'Standard Moisture: ' + opt.dataset.moisture + '%';
                moistureHint.classList.remove('d-none');
            } else {
                moistureHint.classList.add('d-none');
            }
        }
        varietySelect.addEventListener('change', syncVarietyHint);
        syncVarietyHint();
    }
});
</script>
@endpush
@endsection
