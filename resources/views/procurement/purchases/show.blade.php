@extends('layouts.app')

@section('title', __('procurement.purchases.receipt') . ': ' . $purchase->purchase_number)

@section('content')
@php
    $hasDue = $purchase->isFinalized() && bccomp((string) $purchase->balance_due, '0', 2) > 0;
    $daysElapsed = $purchase->purchase_date ? (int) max(0, \Illuminate\Support\Carbon::parse($purchase->purchase_date)->startOfDay()->diffInDays(now()->startOfDay(), false)) : 0;
@endphp

{{-- 1. Executive Tulai Parchi Command Banner --}}
<div class="erp-hero-banner">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <a href="{{ route('purchases.index') }}" class="erp-hero-chip text-decoration-none" style="background: rgba(255, 255, 255, 0.22); border-color: rgba(255, 255, 255, 0.4);">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ __('procurement.purchases.back_to_purchases') }}</span>
                </a>
                <span class="erp-hero-chip">
                    <span style="width:7px;height:7px;border-radius:50%;background:{{ $purchase->isFinalized() ? '#4ADE80' : ($purchase->isDraft() ? '#FBBF24' : '#F87171') }};display:inline-block;"></span>
                    <strong>
                        @if($purchase->isFinalized())
                            {{ __('procurement.purchases.status_finalized') }}
                        @elseif($purchase->isDraft())
                            {{ __('procurement.purchases.status_draft') }}
                        @else
                            {{ __('procurement.purchases.status_reversed') }}
                        @endif
                    </strong>
                </span>
                <span class="erp-hero-chip">
                    <i class="bi bi-calendar-event"></i> {{ fmt_date($purchase->purchase_date) }}
                </span>
                <span class="erp-hero-chip">
                    <i class="bi bi-building"></i> {{ $purchase->warehouse->name }}
                </span>
                @if($purchase->vehicle_number)
                    <span class="erp-hero-chip">
                        <i class="bi bi-truck text-warning"></i> {{ $purchase->vehicle_number }}
                    </span>
                @endif
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ $purchase->purchase_number }} &mdash; {{ $purchase->farmer->name }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                {{ $purchase->crop->name }}
                @if($purchase->cropVariety) ({{ $purchase->cropVariety->name }}) @endif
                &bull; {{ bc_round((string) $purchase->net_weight_qtl, 3) }} Qtl @ {{ money($purchase->rate_per_qtl) }}/Qtl
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end gap-2">
            @if($purchase->isDraft())
                @can('purchases.finalize')
                    <button type="button" class="erp-hero-btn gold border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#finalizeModal">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ __('procurement.purchases.finalize') }}</span>
                    </button>
                @endcan
                @can('purchases.create')
                    <a href="{{ route('purchases.edit', $purchase) }}" class="erp-hero-btn">
                        <i class="bi bi-pencil"></i>
                        <span>{{ __('procurement.purchases.edit') }}</span>
                    </a>
                @endcan
            @elseif($purchase->isFinalized())
                @can('farmer_payments.create')
                    @if($hasDue)
                        <a href="{{ route('farmer-payments.create', ['purchase_id' => $purchase->id]) }}" class="erp-hero-btn gold">
                            <i class="bi bi-cash-coin"></i>
                            <span>{{ __('procurement.payments.add_new') }}</span>
                        </a>
                    @endif
                @endcan
                @can('sales.create')
                    <a href="{{ route('sales-invoices.create', ['purchase_id' => $purchase->id]) }}" class="erp-hero-btn">
                        <i class="bi bi-truck"></i>
                        <span>{{ __('procurement.purchases.direct_sale') }}</span>
                    </a>
                @endcan
            @endif

            <a href="{{ route('purchases.receipt', $purchase) }}" target="_blank" class="erp-hero-btn">
                <i class="bi bi-printer"></i>
                <span>{{ __('procurement.purchases.print_parchi') }}</span>
            </a>
            <a href="{{ route('purchases.pdf', $purchase) }}" class="erp-hero-btn">
                <i class="bi bi-file-earmark-pdf"></i>
                <span>PDF</span>
            </a>
            @if(!empty($purchase->farmer?->mobile))
                @php
                    $waService = app(\App\Services\WhatsAppService::class);
                    $waMsg = $waService->formatPurchaseMessage($purchase);
                    $waLink = $waService->formatClickToChatUrl($purchase->farmer->mobile, $waMsg);
                @endphp
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="erp-hero-btn" style="background: rgba(37, 211, 102, 0.2); border-color: rgba(37, 211, 102, 0.5); color: #ffffff;" title="Send WhatsApp Receipt to Farmer">
                    <i class="bi bi-whatsapp text-success"></i>
                    <span>WhatsApp</span>
                </a>
            @endif
        </div>
    </div>
</div>

@if($purchase->isReversed())
    <div class="alert alert-danger shadow-sm mb-4">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <div class="fw-semibold">
                This purchase was reversed on {{ fmt_date($purchase->reversed_at, 'd M Y, h:i A') }} by {{ $purchase->reverser?->name ?? 'System' }}.
            </div>
            <div class="mt-1 small">
                <strong>{{ __('procurement.purchases.reversal_reason') }}:</strong> {{ $purchase->reversal_reason }}
            </div>
        </div>
    </div>
@endif

{{-- 2. Summary KPI Cards --}}
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #21864B;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.col_net_wt') }}</span>
                <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                    <i class="bi bi-speedometer2"></i>
                </span>
            </div>
            <div class="kpi-value text-dark">{{ bc_round((string) $purchase->net_weight_qtl, 3) }} <span class="fs-6 fw-normal text-muted">Qtl</span></div>
            <div class="kpi-sub text-truncate">{{ bc_round((string) $purchase->net_weight_kg, 3) }} Kg</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #0284C7;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.col_rate') }}</span>
                <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                    <i class="bi bi-tag-fill"></i>
                </span>
            </div>
            <div class="kpi-value text-truncate" style="color: #0284C7;">{{ money($purchase->rate_per_qtl) }}</div>
            <div class="kpi-sub text-truncate">{{ $purchase->crop->name }} ({{ $purchase->quality_grade ?? 'Standard' }})</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #16A34A;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.col_payable') }}</span>
                <span class="kpi-badge-icon" style="background: #DCFCE7; color: #15803D;">
                    <i class="bi bi-calculator"></i>
                </span>
            </div>
            <div class="kpi-value text-success text-truncate">{{ money($purchase->net_payable_amount) }}</div>
            <div class="kpi-sub text-truncate">{{ __('procurement.purchases.base_amount') }}: {{ money($purchase->base_amount) }}</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: {{ $hasDue ? '#D94848' : '#0F766E' }};">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.balance_due') }}</span>
                <span class="kpi-badge-icon" style="background: {{ $hasDue ? '#FBECEC' : '#CCFBF1' }}; color: {{ $hasDue ? '#D94848' : '#0F766E' }};">
                    <i class="bi bi-wallet2"></i>
                </span>
            </div>
            <div class="kpi-value {{ $hasDue ? 'text-danger' : 'text-success' }} text-truncate">{{ money($purchase->balance_due) }}</div>
            <div class="kpi-sub text-truncate">
                {{ __('accounts.paid') }}: {{ money($purchase->paid_amount) }}
                @if($hasDue && $daysElapsed > 0)
                    &bull; {{ __('procurement.purchases.days_ago', ['days' => $daysElapsed]) }}
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Weighment Details & Payment Vouchers --}}
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0 fw-bold">
                    <i class="bi bi-file-earmark-text text-primary me-1"></i> {{ __('procurement.purchases.particulars_heading') }}
                </h5>
                <code class="badge bg-light text-primary border">{{ $purchase->purchase_number }}</code>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light text-muted fw-semibold" style="width: 25%;">{{ __('procurement.purchases.purchase_date') }}</th>
                                <td style="width: 25%;">{{ fmt_date($purchase->purchase_date) }}</td>
                                <th class="bg-light text-muted fw-semibold" style="width: 25%;">{{ __('procurement.purchases.warehouse') }}</th>
                                <td style="width: 25%;"><span class="badge bg-light text-dark border">{{ $purchase->warehouse->name }}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.farmer') }}</th>
                                <td>
                                    <a href="{{ route('farmers.show', $purchase->farmer) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $purchase->farmer->name }}
                                    </a>
                                    <div class="text-muted small">{{ $purchase->farmer->farmer_id }}</div>
                                </td>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.farmers.village') }} / {{ __('procurement.farmers.mobile') }}</th>
                                <td>
                                    <div>{{ $purchase->farmer->village ?: '—' }}</div>
                                    <small class="text-muted">{{ $purchase->farmer->formattedMobile() ?: '—' }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.crop') }} & {{ __('procurement.purchases.variety') }}</th>
                                <td>
                                    <span class="fw-semibold">{{ $purchase->crop->name }}</span>
                                    @if($purchase->cropVariety)
                                        <span class="text-muted">({{ $purchase->cropVariety->name }})</span>
                                    @endif
                                </td>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.quality_grade') }}</th>
                                <td><span class="badge bg-info-subtle text-primary border border-info-subtle">{{ $purchase->quality_grade ?? 'Standard' }}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.gross_weight') }}</th>
                                <td class="fw-semibold">{{ bc_round((string) $purchase->gross_weight_kg, 3) }} Kg</td>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.tare_weight') }}</th>
                                <td>{{ bc_round((string) $purchase->tare_weight_kg, 3) }} Kg</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.net_weight_kg') }}</th>
                                <td class="fw-bold text-dark">{{ bc_round((string) $purchase->net_weight_kg, 3) }} Kg</td>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.net_weight_qtl') }}</th>
                                <td class="fw-bold text-success">{{ bc_round((string) $purchase->net_weight_qtl, 3) }} Qtl</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.moisture') }}</th>
                                <td>{{ $purchase->moisture_pct ? bc_round((string) $purchase->moisture_pct, 2) . '%' : '—' }}</td>
                                <th class="bg-light text-muted fw-semibold">{{ __('procurement.purchases.vehicle_number') }}</th>
                                <td>
                                    @if($purchase->vehicle_number)
                                        <i class="bi bi-truck text-primary"></i> {{ $purchase->vehicle_number }}
                                        @if($purchase->driver_name)
                                            <span class="text-muted small">({{ $purchase->driver_name }})</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @if($purchase->remarks)
                                <tr>
                                    <th class="bg-light text-muted fw-semibold">{{ __('app.remarks') }}</th>
                                    <td colspan="3">{{ $purchase->remarks }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payment Vouchers Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0 fw-bold">
                    <i class="bi bi-cash-stack text-success me-1"></i> {{ __('procurement.payments.title') }} ({{ $purchase->payments->count() }})
                </h5>
                @if($hasDue)
                    @can('farmer_payments.create')
                        <a href="{{ route('farmer-payments.create', ['purchase_id' => $purchase->id]) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> {{ __('procurement.payments.add_new') }}
                        </a>
                    @endcan
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">{{ __('procurement.payments.payment_number') }}</th>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('procurement.payments.payment_mode') }}</th>
                            <th>{{ __('app.reference') }}</th>
                            <th class="num">{{ __('procurement.payments.total_payout') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="text-end pe-3">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchase->payments as $pmt)
                            <tr class="{{ $pmt->isReversed() ? 'opacity-50' : '' }}">
                                <td class="ps-3">
                                    <a href="{{ route('farmer-payments.show', $pmt) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $pmt->voucher_number }}
                                    </a>
                                </td>
                                <td>{{ fmt_date($pmt->payment_date) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $pmt->method_label }}</span></td>
                                <td><span class="small text-muted">{{ $pmt->transaction_reference ?: '—' }}</span></td>
                                <td class="num">
                                    <div class="fw-bold text-success">{{ money($pmt->effective_payout) }}</div>
                                    @if(bccomp((string) $pmt->bonus_amount, '0.00', 2) > 0)
                                        <small class="text-success d-block">+{{ money($pmt->bonus_amount) }} bonus ({{ $pmt->days_delayed }}d)</small>
                                    @endif
                                </td>
                                <td>
                                    @if($pmt->isReversed())
                                        <span class="badge-status badge-danger">{{ __('procurement.purchases.status_reversed') }}</span>
                                    @else
                                        <span class="badge-status badge-success">{{ __('accounts.paid') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('farmer-payments.show', $pmt) }}" class="btn btn-xs btn-light border">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-wallet2 d-block fs-3 mb-1 opacity-50"></i>
                                    {{ __('procurement.purchases.no_vouchers_yet') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Column: Financial Statement & Audit --}}
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0 fw-bold">
                    <i class="bi bi-calculator text-primary me-1"></i> {{ __('procurement.purchases.financial_statement') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">{{ __('procurement.purchases.net_weight_qtl') }}:</span>
                    <span class="fw-semibold">{{ bc_round((string) $purchase->net_weight_qtl, 3) }} Qtl</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">{{ __('procurement.purchases.rate_per_qtl') }}:</span>
                    <span class="fw-semibold">{{ money($purchase->rate_per_qtl) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom small">
                    <span class="fw-semibold">{{ __('procurement.purchases.base_amount') }}:</span>
                    <span class="fw-semibold">{{ money($purchase->base_amount) }}</span>
                </div>

                @if(bccomp((string) $purchase->deductions_amount, '0', 2) > 0)
                    <div class="d-flex justify-content-between text-danger mb-2 small">
                        <span>
                            {{ __('procurement.purchases.deductions') }}:
                            @if($purchase->deduction_reason)
                                <small class="text-muted d-block">({{ $purchase->deduction_reason }})</small>
                            @endif
                        </span>
                        <span>- {{ money($purchase->deductions_amount) }}</span>
                    </div>
                @endif

                @if(bccomp((string) $purchase->additions_amount, '0', 2) > 0)
                    <div class="d-flex justify-content-between text-success mb-2 small">
                        <span>{{ __('procurement.purchases.additions') }}:</span>
                        <span>+ {{ money($purchase->additions_amount) }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between py-2 border-top border-bottom mb-3">
                    <span class="fw-bold">{{ __('procurement.purchases.net_payable') }}:</span>
                    <span class="fw-bold text-success">{{ money($purchase->net_payable_amount) }}</span>
                </div>

                <div class="d-flex justify-content-between mb-3 small">
                    <span class="text-muted">{{ __('accounts.paid') }}:</span>
                    <span class="fw-semibold text-dark">{{ money($purchase->paid_amount) }}</span>
                </div>

                <div class="p-3 {{ $hasDue ? 'bg-danger-subtle border-danger-subtle text-danger' : 'bg-success-subtle border-success-subtle text-success' }} border rounded-3 text-center">
                    <span class="small fw-semibold d-block text-uppercase">{{ __('procurement.purchases.balance_due') }}</span>
                    <span class="fs-4 fw-bold">{{ money($purchase->balance_due) }}</span>
                </div>

                @if($purchase->isFinalized())
                    @can('purchases.reverse')
                        <div class="mt-3 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#reverseModal">
                                <i class="bi bi-arrow-counterclockwise"></i> {{ __('procurement.purchases.reverse') }}
                            </button>
                        </div>
                    @endcan
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0 fw-bold">
                    <i class="bi bi-clock-history text-muted me-1"></i> {{ __('procurement.purchases.audit_heading') }}
                </h5>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Created By:</span>
                    <span class="fw-medium">{{ $purchase->creator?->name ?? 'System' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Created At:</span>
                    <span>{{ fmt_date($purchase->created_at, 'd M Y, h:i A') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Last Updated:</span>
                    <span>{{ fmt_date($purchase->updated_at, 'd M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Finalize Modal --}}
@can('purchases.finalize')
@if($purchase->isDraft())
<div class="modal fade" id="finalizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('purchases.finalize', $purchase) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title h6 text-white"><i class="bi bi-check2-circle me-1"></i> {{ __('procurement.purchases.finalize') }} — {{ $purchase->purchase_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 small">
                        Finalizing this purchase locks weighment particulars and posts <strong>{{ bc_round((string) $purchase->net_weight_qtl, 3) }} Qtl of {{ $purchase->crop->name }}</strong> into <strong>{{ $purchase->warehouse->name }}</strong>.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">{{ __('procurement.purchases.paid_amount') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="0" max="{{ $purchase->net_payable_amount }}" inputmode="decimal" name="payment_amount"
                                class="form-control" value="0.00">
                        </div>
                        <small class="text-muted">Leave 0.00 if paying later via farmer payment voucher.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('procurement.payments.payment_mode') }}</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">{{ __('procurement.payments.mode_cash') }}</option>
                            <option value="bank_transfer">{{ __('procurement.payments.mode_bank') }}</option>
                            <option value="upi">{{ __('procurement.payments.mode_upi') }}</option>
                            <option value="cheque">{{ __('procurement.payments.mode_cheque') }}</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">{{ __('procurement.payments.transaction_ref') }}</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="UTR / Cheque #">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('procurement.purchases.finalize') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- Reverse Modal --}}
@can('purchases.reverse')
@if($purchase->isFinalized())
<div class="modal fade" id="reverseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('purchases.reverse', $purchase) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title h6 text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ __('procurement.purchases.reverse') }} — {{ $purchase->purchase_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <span>{{ __('procurement.purchases.reverse_warning') }} (-{{ bc_round((string) $purchase->net_weight_qtl, 3) }} Qtl)</span>
                    </div>

                    <div class="mb-0">
                        <label class="form-label required">{{ __('procurement.purchases.reversal_reason') }}</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="Minimum 5 characters..." required minlength="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('procurement.purchases.reverse') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

@endsection
