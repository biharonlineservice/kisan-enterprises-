@extends('layouts.app')

@section('title', 'Record Farmer Payment')

@section('content')
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <a href="{{ route('farmer-payments.index') }}" class="erp-hero-chip text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ __('procurement.payments.title') }}</span>
                </a>
                <span class="erp-hero-chip">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ fmt_date(now()) }}</span>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">
                <i class="bi bi-cash-coin me-2"></i>{{ __('procurement.payments.add_new') }}
            </h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                Record disbursement against finalized purchase receipt with automatic delayed payment bonus calculation.
            </p>
        </div>
        <div>
            <a href="{{ route('farmer-payments.index') }}" class="erp-hero-btn">
                <i class="bi bi-x-circle"></i>
                <span>{{ __('app.cancel') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-cash text-success me-1"></i> Payment Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('farmer-payments.store') }}">
                    @csrf
                    <x-form-token />

                    <div class="mb-3">
                        <label class="form-label required">Select Purchase (Pending Balance)</label>
                        <select name="purchase_id" id="purchase_id" class="form-select @error('purchase_id') is-invalid @enderror" required>
                            <option value="">Choose a finalized purchase with pending balance...</option>
                            @foreach($pendingPurchases as $p)
                                <option value="{{ $p->id }}"
                                    data-farmer="{{ $p->farmer->name }} ({{ $p->farmer->farmer_id }})"
                                    data-crop="{{ $p->crop->name }}"
                                    data-payable="{{ $p->net_payable_amount }}"
                                    data-balance="{{ $p->balance_due }}"
                                    data-purchase-date="{{ $p->purchase_date->format('Y-m-d') }}"
                                    data-purchase-date-fmt="{{ fmt_date($p->purchase_date) }}"
                                    data-bonus-enabled="{{ $p->farmer->isDelayedBonusEnabled() ? '1' : '0' }}"
                                    data-bonus-rate="{{ $p->farmer->effective_bonus_rate }}"
                                    data-bonus-mode="{{ $p->farmer->effective_bonus_mode }}"
                                    data-custom-bonus="{{ $p->farmer->hasCustomBonusSetting() ? '1' : '0' }}"
                                    @selected(old('purchase_id', $selectedPurchase?->id) == $p->id)>
                                    {{ $p->purchase_number }} ({{ fmt_date($p->purchase_date) }}) — {{ $p->farmer->name }} ({{ $p->crop->name }}) — Balance: {{ money($p->balance_due) }}
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Purchase Snapshot Preview --}}
                    <div id="purchase_preview" class="p-3 bg-light rounded border mb-4 d-none">
                        <div class="row g-2 small">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Farmer:</span>
                                <strong id="prev_farmer">-</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Crop / Purchase Date:</span>
                                <strong id="prev_crop">-</strong>
                                <span id="prev_date" class="text-muted d-block"></span>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Total Payable:</span>
                                <strong id="prev_payable" class="text-dark">-</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Current Balance Due:</span>
                                <strong id="prev_balance" class="text-danger fs-6">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('procurement.payments.amount') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                                <input type="number" step="0.01" min="0.01" inputmode="decimal" name="amount" id="amount"
                                    class="form-control text-end fw-bold @error('amount') is-invalid @enderror"
                                    value="{{ old('amount', $selectedPurchase ? $selectedPurchase->balance_due : '') }}" required placeholder="0.00">
                                <button class="btn btn-outline-secondary" type="button" id="btn_pay_full">Pay Full</button>
                            </div>
                            @error('amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('procurement.payments.payment_mode') }}</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                @foreach($methods as $key => $lbl)
                                    <option value="{{ $key }}" @selected(old('payment_method') === $key)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('procurement.payments.transaction_ref') }}</label>
                            <input type="text" name="transaction_reference" class="form-control @error('transaction_reference') is-invalid @enderror"
                                value="{{ old('transaction_reference') }}" placeholder="e.g. UTR12345678, Chq #000123">
                            <small class="text-muted">Bank UTR, UPI transaction ID, or Cheque number</small>
                            @error('transaction_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('app.notes') }}</label>
                            <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                value="{{ old('notes') }}" placeholder="Any payment memo or receipt note">
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Delayed Payment Bonus Section --}}
                    @if(($bonusEnabled ?? '1') == '1')
                        <input type="hidden" name="apply_bonus_present" value="1">
                        <div id="bonus_panel" class="card border-success border-opacity-50 bg-success-subtle bg-opacity-10 mt-4 d-none">
                            <div class="card-body p-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div>
                                        <h6 class="mb-0 text-success fw-bold">
                                            <i class="bi bi-gift-fill me-1"></i> {{ __('procurement.payments.delayed_bonus') }}
                                            <span id="farmer_bonus_badge" class="badge bg-success text-white ms-1 small d-none"></span>
                                        </h6>
                                        <small class="text-muted">{{ __('procurement.payments.delayed_bonus_desc') }}</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span id="bonus_days_pill" class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-calendar-check me-1"></i> <span id="bonus_days_text">0 days</span>
                                        </span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="apply_bonus" name="apply_bonus" value="1"
                                                @checked(old('apply_bonus', '1') == '1')>
                                            <label class="form-check-label small fw-semibold" for="apply_bonus">{{ __('procurement.payments.apply_bonus') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div id="no_bonus_notice" class="small text-muted fst-italic py-1 d-none">
                                    <i class="bi bi-info-circle me-1"></i> {{ __('procurement.payments.no_bonus_instant') }}
                                </div>

                                <div id="bonus_controls" class="row g-3 mt-1 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-semibold mb-1">{{ __('procurement.payments.bonus_mode') }}</label>
                                        <select name="bonus_calculation_mode" id="bonus_calculation_mode" class="form-select form-select-sm">
                                            <option value="monthly" @selected(old('bonus_calculation_mode', $defaultBonusMode ?? 'monthly') === 'monthly')>{{ __('procurement.payments.mode_monthly') }}</option>
                                            <option value="flat" @selected(old('bonus_calculation_mode', $defaultBonusMode ?? 'monthly') === 'flat')>{{ __('procurement.payments.mode_flat') }}</option>
                                            <option value="custom" @selected(old('bonus_calculation_mode', $defaultBonusMode ?? 'monthly') === 'custom')>{{ __('procurement.payments.mode_custom') }}</option>
                                        </select>
                                    </div>

                                    <div class="col-6 col-md-3" id="bonus_rate_wrap">
                                        <label class="form-label small fw-semibold mb-1">{{ __('procurement.payments.bonus_rate') }}</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" min="0" max="100" name="bonus_percentage" id="bonus_percentage"
                                                class="form-control text-end fw-semibold"
                                                value="{{ old('bonus_percentage', $defaultBonusRate ?? '1.00') }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <label class="form-label small fw-semibold mb-1">{{ __('procurement.payments.bonus_amount') }}</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text text-success fw-bold">+{{ setting('locale.currency_symbol', '₹') }}</span>
                                            <input type="number" step="0.01" min="0" name="bonus_amount" id="bonus_amount"
                                                class="form-control text-end fw-bold text-success"
                                                value="{{ old('bonus_amount', '0.00') }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-success border-opacity-25">
                                    <span class="fw-semibold text-dark">{{ __('procurement.payments.total_payout') }}:</span>
                                    <span id="total_payout_display" class="fs-5 fw-bold text-success">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-none d-md-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('farmer-payments.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> {{ __('procurement.payments.record_payment') }}
                        </button>
                    </div>

                    {{-- Mobile Sticky Action Bar (< 768px) --}}
                    <div class="mobile-sticky-actions">
                        <a href="{{ route('farmer-payments.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> {{ __('app.save') }}
                        </button>
                    </div>
                    <div class="pb-5 mb-4 d-md-none"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        {{-- Live Payout Summary Card --}}
        <div id="live_summary_card" class="card shadow-sm border-start border-4 border-success mb-3 d-none">
            <div class="card-body">
                <h6 class="card-title text-success fw-bold mb-3"><i class="bi bi-calculator me-1"></i> Payout Summary</h6>
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">{{ __('procurement.payments.days_delayed') }}:</span>
                    <strong id="sum_days">0 days</strong>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Principal Payment:</span>
                    <strong id="sum_principal">₹0.00</strong>
                </div>
                <div class="d-flex justify-content-between small mb-2 text-success">
                    <span>{{ __('procurement.payments.delayed_bonus') }}:</span>
                    <strong id="sum_bonus">+ ₹0.00</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
                    <span class="fw-bold">{{ __('procurement.payments.total_payout') }}:</span>
                    <span id="sum_total" class="fs-5 fw-bold text-success">₹0.00</span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <h6 class="card-title text-info"><i class="bi bi-info-circle me-1"></i> Payment Verification</h6>
                <p class="small text-muted mb-2">
                    - Payment disbursements reduce the farmer's outstanding purchase balance immediately.
                </p>
                <p class="small text-muted mb-2">
                    - Delayed payment bonus is paid on top of the principal balance as an incentive for deferred collection.
                </p>
                <p class="small text-muted mb-0">
                    - A computer-generated payment voucher will be issued immediately upon saving.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const todayStr = @json(now()->format('Y-m-d'));
    const currency = @json(setting('locale.currency_symbol', '₹'));
    const hasOldInput = @json(old('purchase_id') !== null);

    const purchaseSelect = document.getElementById('purchase_id');
    const previewBox = document.getElementById('purchase_preview');
    const prevFarmer = document.getElementById('prev_farmer');
    const prevCrop = document.getElementById('prev_crop');
    const prevDate = document.getElementById('prev_date');
    const prevPayable = document.getElementById('prev_payable');
    const prevBalance = document.getElementById('prev_balance');
    const amountInput = document.getElementById('amount');
    const payFullBtn = document.getElementById('btn_pay_full');

    const bonusPanel = document.getElementById('bonus_panel');
    const farmerBonusBadge = document.getElementById('farmer_bonus_badge');
    const applyBonusToggle = document.getElementById('apply_bonus');
    const bonusDaysText = document.getElementById('bonus_days_text');
    const noBonusNotice = document.getElementById('no_bonus_notice');
    const bonusControls = document.getElementById('bonus_controls');
    const bonusModeSelect = document.getElementById('bonus_calculation_mode');
    const bonusRateWrap = document.getElementById('bonus_rate_wrap');
    const bonusRateInput = document.getElementById('bonus_percentage');
    const bonusAmountInput = document.getElementById('bonus_amount');
    const totalPayoutDisplay = document.getElementById('total_payout_display');

    const liveSummaryCard = document.getElementById('live_summary_card');
    const sumDays = document.getElementById('sum_days');
    const sumPrincipal = document.getElementById('sum_principal');
    const sumBonus = document.getElementById('sum_bonus');
    const sumTotal = document.getElementById('sum_total');

    let currentDaysDelayed = 0;

    function fmtMoney(val) {
        return currency + (parseFloat(val) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function computeDaysDelayed(dateStr) {
        if (!dateStr) return 0;
        const pDate = new Date(dateStr + 'T00:00:00');
        const tDate = new Date(todayStr + 'T00:00:00');
        const diff = Math.round((tDate - pDate) / 86400000);
        return Math.max(0, diff);
    }

    function recalculateBonus() {
        const opt = purchaseSelect ? purchaseSelect.selectedOptions[0] : null;
        const hasPurchase = !!(opt && opt.value);
        const amount = Math.max(0, parseFloat(amountInput ? amountInput.value : 0) || 0);

        if (!hasPurchase) {
            if (bonusPanel) bonusPanel.classList.add('d-none');
            if (liveSummaryCard) liveSummaryCard.classList.add('d-none');
            return;
        }

        currentDaysDelayed = computeDaysDelayed(opt.dataset.purchaseDate);
        const months = (currentDaysDelayed / 30).toFixed(2);
        const daysLabel = currentDaysDelayed > 0
            ? `${currentDaysDelayed} days (${months} mo)`
            : '0 days (Same Day)';

        if (bonusDaysText) bonusDaysText.textContent = daysLabel;
        if (sumDays) sumDays.textContent = daysLabel;
        if (liveSummaryCard) liveSummaryCard.classList.remove('d-none');

        let bonusAmt = 0;

        if (bonusPanel) {
            bonusPanel.classList.remove('d-none');
            const apply = applyBonusToggle ? applyBonusToggle.checked : false;
            const mode = bonusModeSelect ? bonusModeSelect.value : 'monthly';
            const rate = Math.max(0, parseFloat(bonusRateInput ? bonusRateInput.value : 0) || 0);

            if (!apply) {
                if (bonusControls) bonusControls.classList.add('d-none');
                if (noBonusNotice) {
                    if (currentDaysDelayed === 0) {
                        noBonusNotice.classList.remove('d-none');
                    } else {
                        noBonusNotice.classList.add('d-none');
                    }
                }
                bonusAmt = 0;
                if (bonusAmountInput) bonusAmountInput.value = '0.00';
            } else {
                if (bonusControls) bonusControls.classList.remove('d-none');
                if (noBonusNotice) {
                    if (currentDaysDelayed === 0 && mode !== 'custom') {
                        noBonusNotice.classList.remove('d-none');
                    } else {
                        noBonusNotice.classList.add('d-none');
                    }
                }

                if (mode === 'custom') {
                    if (bonusAmountInput) bonusAmountInput.readOnly = false;
                    if (bonusRateWrap) bonusRateWrap.classList.add('opacity-50');
                    if (bonusRateInput) bonusRateInput.readOnly = true;
                    bonusAmt = Math.max(0, parseFloat(bonusAmountInput ? bonusAmountInput.value : 0) || 0);
                    if (bonusRateInput) {
                        bonusRateInput.value = amount > 0 ? ((bonusAmt / amount) * 100).toFixed(2) : '0.00';
                    }
                } else {
                    if (bonusAmountInput) bonusAmountInput.readOnly = true;
                    if (bonusRateWrap) bonusRateWrap.classList.remove('opacity-50');
                    if (bonusRateInput) bonusRateInput.readOnly = false;

                    if (currentDaysDelayed > 0) {
                        if (mode === 'monthly') {
                            bonusAmt = Math.round((amount * (rate / 100) * (currentDaysDelayed / 30)) * 100) / 100;
                        } else if (mode === 'flat') {
                            bonusAmt = Math.round((amount * (rate / 100)) * 100) / 100;
                        }
                    } else {
                        bonusAmt = 0;
                    }
                    if (bonusAmountInput) bonusAmountInput.value = bonusAmt.toFixed(2);
                }
            }

            if (totalPayoutDisplay) {
                totalPayoutDisplay.textContent = fmtMoney(amount + bonusAmt);
            }
        }

        if (sumPrincipal) sumPrincipal.textContent = fmtMoney(amount);
        if (sumBonus) sumBonus.textContent = '+ ' + fmtMoney(bonusAmt);
        if (sumTotal) sumTotal.textContent = fmtMoney(amount + bonusAmt);
    }

    function updatePreview(isInitialLoad = false) {
        const opt = purchaseSelect.selectedOptions[0];
        if (opt && opt.value) {
            prevFarmer.textContent = opt.dataset.farmer || '-';
            prevCrop.textContent = opt.dataset.crop || '-';
            if (prevDate) prevDate.textContent = opt.dataset.purchaseDateFmt ? ('Purchased: ' + opt.dataset.purchaseDateFmt) : '';
            prevPayable.textContent = fmtMoney(opt.dataset.payable);
            prevBalance.textContent = fmtMoney(opt.dataset.balance);
            previewBox.classList.remove('d-none');

            const days = computeDaysDelayed(opt.dataset.purchaseDate);
            const farmerBonusEnabled = opt.dataset.bonusEnabled !== '0';

            if (farmerBonusBadge) {
                if (!farmerBonusEnabled) {
                    farmerBonusBadge.textContent = 'Disabled for Farmer';
                    farmerBonusBadge.className = 'badge bg-secondary text-white ms-1 small';
                } else if (opt.dataset.customBonus === '1') {
                    farmerBonusBadge.textContent = 'Farmer Rate: ' + (opt.dataset.bonusRate || '0') + '%';
                    farmerBonusBadge.className = 'badge bg-success text-white ms-1 small';
                } else {
                    farmerBonusBadge.classList.add('d-none');
                }
            }

            if (!isInitialLoad || !hasOldInput) {
                if (bonusRateInput && opt.dataset.bonusRate) {
                    bonusRateInput.value = opt.dataset.bonusRate;
                }
                if (bonusModeSelect && opt.dataset.bonusMode) {
                    bonusModeSelect.value = opt.dataset.bonusMode;
                }
                if (applyBonusToggle) {
                    applyBonusToggle.checked = days > 0 && farmerBonusEnabled;
                }
            }
        } else {
            previewBox.classList.add('d-none');
        }
        recalculateBonus();
    }

    if (payFullBtn) {
        payFullBtn.addEventListener('click', function() {
            const opt = purchaseSelect.selectedOptions[0];
            if (opt && opt.dataset.balance) {
                amountInput.value = parseFloat(opt.dataset.balance).toFixed(2);
                recalculateBonus();
            }
        });
    }

    if (purchaseSelect) {
        purchaseSelect.addEventListener('change', function() {
            const opt = purchaseSelect.selectedOptions[0];
            if (opt && opt.dataset.balance) {
                amountInput.value = parseFloat(opt.dataset.balance).toFixed(2);
            }
            updatePreview(false);
        });
        updatePreview(true);
    }

    [amountInput, bonusRateInput, bonusAmountInput].forEach(function(el) {
        if (el) el.addEventListener('input', recalculateBonus);
    });

    [applyBonusToggle, bonusModeSelect].forEach(function(el) {
        if (el) el.addEventListener('change', recalculateBonus);
    });
});
</script>
@endpush
@endsection
