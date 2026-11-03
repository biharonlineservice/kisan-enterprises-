@extends('layouts.app')

@section('title', __('procurement.farmers.details') . ': ' . $farmer->name)

@section('content')
@php
    $cleanDigits = $farmer->mobile ? preg_replace('/\D+/', '', (string) $farmer->mobile) : '';
@endphp

{{-- 1. Executive Farmer Profile Command Banner --}}
<div class="erp-hero-banner">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex align-items-start align-items-sm-center gap-3">
            <div class="avatar flex-shrink-0 border border-white border-opacity-25" style="width: 56px; height: 56px; font-size: 1.4rem; background: rgba(255,255,255,.16); color: #fff;">
                {{ mb_strtoupper(mb_substr($farmer->name, 0, 1)) }}
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="erp-hero-chip">
                        <i class="bi bi-hash text-warning"></i>
                        <strong>{{ $farmer->farmer_id }}</strong>
                    </span>
                    <span class="erp-hero-chip">
                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $farmer->is_active ? '#4ADE80' : '#F87171' }};display:inline-block;"></span>
                        {{ $farmer->is_active ? __('app.active') : __('app.inactive') }}
                    </span>
                    @if($farmer->mobile)
                        <span class="erp-hero-chip">
                            <i class="bi bi-telephone-fill"></i> {{ $farmer->formattedMobile() }}
                        </span>
                    @endif
                </div>
                <h2 class="h4 fw-bold mb-1 text-white">{{ $farmer->name }}</h2>
                <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                    <i class="bi bi-geo-alt me-1"></i>{{ $farmer->full_location ?: __('procurement.farmers.location_unset') }}
                    &bull; {{ __('procurement.farmers.profile_registered_on', ['date' => fmt_date($farmer->registration_date)]) }}
                </p>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @can('purchases.create')
                @if($farmer->is_active)
                    <a href="{{ route('purchases.create', ['farmer_id' => $farmer->id]) }}" class="erp-hero-btn gold">
                        <i class="bi bi-basket-fill"></i>
                        <span>{{ __('procurement.farmers.quick_buy') }}</span>
                    </a>
                @endif
            @endcan
            @can('farmer_payments.create')
                @if($ledgerStats['purchases_due'] > 0)
                    <a href="{{ route('farmer-payments.create', ['farmer_id' => $farmer->id]) }}" class="erp-hero-btn">
                        <i class="bi bi-cash-coin"></i>
                        <span>{{ __('procurement.farmers.quick_pay') }}</span>
                    </a>
                @endif
            @endcan
            <a href="{{ route('farmers.print', $farmer) }}" class="erp-hero-btn" target="_blank">
                <i class="bi bi-printer"></i>
                <span>{{ __('procurement.farmers.print_profile') }}</span>
            </a>
            @can('farmers.edit')
                <a href="{{ route('farmers.edit', $farmer) }}" class="erp-hero-btn">
                    <i class="bi bi-pencil"></i>
                    <span>{{ __('app.edit') }}</span>
                </a>
            @endcan
            <a href="{{ route('farmers.index') }}" class="erp-hero-btn">
                <i class="bi bi-arrow-left"></i>
                <span>{{ __('procurement.farmers.all_farmers_back') }}</span>
            </a>
        </div>
    </div>
</div>

{{-- 2. Farmer Ledger KPI Cards --}}
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #21864B;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.farmers.kpi_supplied_volume') }}</span>
                <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                    <i class="bi bi-basket-fill"></i>
                </span>
            </div>
            <div class="kpi-value text-dark">{{ bc_round((string) $ledgerStats['total_qtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span></div>
            <div class="kpi-sub text-truncate">{{ __('procurement.farmers.kpi_slips_count', ['count' => $ledgerStats['purchases_count']]) }}</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #0284C7;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.farmers.ledger_gross_purchases') }}</span>
                <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                    <i class="bi bi-receipt"></i>
                </span>
            </div>
            <div class="kpi-value text-truncate" style="color: #0284C7;">{{ money($ledgerStats['gross_purchases']) }}</div>
            <div class="kpi-sub text-truncate">{{ __('procurement.farmers.total_purchases') }}</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #16A34A;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.farmers.ledger_paid_total') }}</span>
                <span class="kpi-badge-icon" style="background: #DCFCE7; color: #15803D;">
                    <i class="bi bi-check2-circle"></i>
                </span>
            </div>
            <div class="kpi-value text-truncate" style="color: #15803D;">{{ money($ledgerStats['total_payout']) }}</div>
            <div class="kpi-sub text-truncate">{{ __('procurement.farmers.ledger_bonus_earned', ['bonus' => money($ledgerStats['total_bonus_paid'])]) }}</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: {{ $ledgerStats['net_payable'] > 0 ? '#D94848' : '#0F766E' }};">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.farmers.ledger_net_payable') }}</span>
                <span class="kpi-badge-icon" style="background: {{ $ledgerStats['net_payable'] > 0 ? '#FBECEC' : '#CCFBF1' }}; color: {{ $ledgerStats['net_payable'] > 0 ? '#D94848' : '#0F766E' }};">
                    <i class="bi bi-wallet2"></i>
                </span>
            </div>
            <div class="kpi-value {{ $ledgerStats['net_payable'] > 0 ? 'text-danger' : 'text-success' }} text-truncate">
                {{ money($ledgerStats['net_payable']) }}
            </div>
            <div class="kpi-sub text-truncate">{{ __('procurement.farmers.ledger_opening_note', ['amount' => money($farmer->opening_balance)]) }}</div>
        </div>
    </div>
</div>

{{-- 3. Profile Details & Live Procurement / Payment Ledger --}}
<div class="row g-4">
    <div class="col-lg-4">
        {{-- Quick Contact & Bonus Policy Card --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <span class="fw-bold"><i class="bi bi-person-vcard text-primary me-1"></i> {{ __('procurement.farmers.details') }}</span>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($farmer->mobile)
                        <a href="tel:+91{{ $cleanDigits }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                            <i class="bi bi-telephone-fill"></i> {{ $farmer->formattedMobile() }}
                        </a>
                        @if(strlen($cleanDigits) === 10)
                            <a href="https://wa.me/91{{ $cleanDigits }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-top small">
                    <span class="text-muted">{{ __('procurement.farmers.opening_balance') }}</span>
                    <span class="fw-bold {{ $farmer->opening_balance > 0 ? 'text-danger' : ($farmer->opening_balance < 0 ? 'text-primary' : 'text-muted') }}">
                        {{ money($farmer->opening_balance) }}
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-top small">
                    <span class="text-muted"><i class="bi bi-gift text-success me-1"></i> {{ __('procurement.farmers.col_delayed_bonus') }}</span>
                    @if($farmer->isDelayedBonusEnabled())
                        <span class="fw-bold text-success">
                            {{ $farmer->effective_bonus_rate }}% ({{ ucfirst($farmer->effective_bonus_mode) }})
                            @if($farmer->hasCustomBonusSetting())
                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">{{ __('procurement.farmers.custom_badge') }}</span>
                            @endif
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border">{{ __('procurement.farmers.bonus_disabled') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Village & Agricultural Location --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <span class="fw-bold"><i class="bi bi-geo-alt text-primary me-1"></i> {{ __('procurement.farmers.land_info') }}</span>
            </div>
            <div class="card-body p-3">
                <dl class="row mb-0 small g-2">
                    <dt class="col-5 text-muted">{{ __('procurement.farmers.village') }}</dt>
                    <dd class="col-7 fw-semibold mb-1">{{ $farmer->village ?: '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('procurement.farmers.panchayat') }}</dt>
                    <dd class="col-7 mb-1">{{ $farmer->panchayat ?: '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('procurement.farmers.block_tehsil') }}</dt>
                    <dd class="col-7 mb-1">{{ $farmer->block ?: '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('procurement.farmers.district') }}</dt>
                    <dd class="col-7 mb-1">{{ $farmer->district ?: '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('procurement.farmers.state') }}</dt>
                    <dd class="col-7 mb-1">{{ $farmer->state ?: '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('procurement.farmers.registered_by') }}</dt>
                    <dd class="col-7 mb-1">{{ $farmer->creator?->name ?: 'Online / System' }}</dd>

                    @if($farmer->address)
                        <dt class="col-12 text-muted pt-2 border-top">{{ __('procurement.farmers.full_address') }}</dt>
                        <dd class="col-12 mb-0">{{ $farmer->address }}</dd>
                    @endif

                    @if($farmer->notes)
                        <dt class="col-12 text-muted pt-2 border-top">{{ __('procurement.farmers.notes_label') }}</dt>
                        <dd class="col-12 text-secondary mb-0">{{ $farmer->notes }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Bank & Settlement KYC (Restricted) --}}
        @can('farmers.bank')
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="bi bi-bank text-primary me-1"></i> {{ __('procurement.farmers.bank_info') }}</span>
                </div>
                <div class="card-body p-3">
                    <dl class="row mb-0 small g-2">
                        <dt class="col-5 text-muted">{{ __('procurement.farmers.bank_name') }}</dt>
                        <dd class="col-7 fw-semibold mb-1">{{ $farmer->bank_name ?: '—' }}</dd>

                        <dt class="col-5 text-muted">Branch</dt>
                        <dd class="col-7 mb-1">{{ $farmer->bank_branch ?: '—' }}</dd>

                        <dt class="col-5 text-muted">{{ __('procurement.farmers.bank_account') }}</dt>
                        <dd class="col-7 font-monospace fw-bold mb-1">{{ $farmer->bank_account_number ?: '—' }}</dd>

                        <dt class="col-5 text-muted">{{ __('procurement.farmers.bank_ifsc') }}</dt>
                        <dd class="col-7 font-monospace mb-1">{{ $farmer->bank_ifsc ?: '—' }}</dd>

                        <dt class="col-5 text-muted">Govt. ID</dt>
                        <dd class="col-7 mb-0">{{ $farmer->id_type ? $farmer->id_type . ': ' . $farmer->id_number : '—' }}</dd>
                    </dl>
                </div>
            </div>
        @endcan
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                <ul class="nav erp-tab-pills gap-1" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0" id="tab-farmer-purchases" data-bs-toggle="tab" data-bs-target="#paneFarmerPurchases" type="button" role="tab" aria-selected="true">
                            <i class="bi bi-basket me-1"></i>{{ __('procurement.farmers.tab_purchases') }} ({{ $purchases->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0" id="tab-farmer-payments" data-bs-toggle="tab" data-bs-target="#paneFarmerPayments" type="button" role="tab" aria-selected="false">
                            <i class="bi bi-cash-coin me-1"></i>{{ __('procurement.farmers.tab_payments') }} ({{ $payments->count() }})
                        </button>
                    </li>
                </ul>

                @can('purchases.create')
                    @if($farmer->is_active)
                        <a href="{{ route('purchases.create', ['farmer_id' => $farmer->id]) }}" class="btn btn-xs btn-primary">
                            <i class="bi bi-plus-circle"></i> {{ __('procurement.purchases.add_new') }}
                        </a>
                    @endif
                @endcan
            </div>

            <div class="card-body p-0 tab-content">
                {{-- Tab 1: Farmer Crop Purchases --}}
                <div class="tab-pane fade show active" id="paneFarmerPurchases" role="tabpanel" aria-labelledby="tab-farmer-purchases">
                    @if($purchases->isEmpty())
                        <div class="empty-state py-5">
                            <i class="bi bi-basket"></i>
                            <h6 class="fw-semibold">{{ __('procurement.farmers.no_purchases_yet') }}</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead>
                                    <tr>
                                        <th class="ps-3">{{ __('procurement.purchases.purchase_number') }}</th>
                                        <th>{{ __('procurement.purchases.crop') }}</th>
                                        <th class="num">{{ __('procurement.purchases.net_weight_qtl') }}</th>
                                        <th class="num">{{ __('procurement.purchases.net_payable') }}</th>
                                        <th class="num">{{ __('procurement.purchases.balance_due') }}</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th class="text-end pe-3">{{ __('app.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $p)
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('purchases.show', $p) }}" class="fw-bold text-decoration-none">{{ $p->purchase_number }}</a>
                                                <div class="text-muted">{{ fmt_date($p->purchase_date) }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $p->crop?->name }}</div>
                                                <small class="text-muted">{{ $p->warehouse?->name }}</small>
                                            </td>
                                            <td class="num fw-semibold">{{ bc_round((string) $p->net_weight_qtl, 2) }} Qtl</td>
                                            <td class="num fw-semibold">{{ money($p->net_payable_amount) }}</td>
                                            <td class="num fw-bold {{ $p->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ money($p->balance_due) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $p->status === 'finalized' ? 'success' : ($p->status === 'reversed' ? 'danger' : 'warning') }}-subtle text-dark border text-capitalize">
                                                    {{ $p->status }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-inline-flex gap-1">
                                                    @can('farmer_payments.create')
                                                        @if($p->status === 'finalized' && $p->balance_due > 0)
                                                            <a href="{{ route('farmer-payments.create', ['purchase_id' => $p->id]) }}" class="btn btn-xs btn-success">
                                                                {{ __('procurement.farmers.quick_pay') }}
                                                            </a>
                                                        @endif
                                                    @endcan
                                                    <a href="{{ route('purchases.receipt', $p) }}" target="_blank" class="btn btn-xs btn-light border" title="{{ __('app.print') }}">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Tab 2: Farmer Payment Vouchers --}}
                <div class="tab-pane fade" id="paneFarmerPayments" role="tabpanel" aria-labelledby="tab-farmer-payments">
                    @if($payments->isEmpty())
                        <div class="empty-state py-5">
                            <i class="bi bi-cash-coin"></i>
                            <h6 class="fw-semibold">{{ __('procurement.farmers.no_payments_yet') }}</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead>
                                    <tr>
                                        <th class="ps-3">{{ __('procurement.payments.payment_number') }}</th>
                                        <th>{{ __('procurement.payments.purchase') }}</th>
                                        <th>{{ __('procurement.payments.payment_mode') }}</th>
                                        <th class="num">{{ __('procurement.payments.amount') }}</th>
                                        <th class="num">{{ __('procurement.payments.bonus_amount') }}</th>
                                        <th class="num">{{ __('procurement.payments.total_payout') }}</th>
                                        <th class="text-end pe-3">{{ __('app.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $pmt)
                                        <tr class="{{ $pmt->reversed_at ? 'opacity-50 text-decoration-line-through' : '' }}">
                                            <td class="ps-3">
                                                <a href="{{ route('farmer-payments.show', $pmt) }}" class="fw-bold text-decoration-none">{{ $pmt->voucher_number }}</a>
                                                <div class="text-muted">{{ fmt_date($pmt->payment_date) }}</div>
                                            </td>
                                            <td>
                                                @if($pmt->purchase)
                                                    <a href="{{ route('purchases.show', $pmt->purchase) }}" class="text-decoration-none">{{ $pmt->purchase->purchase_number }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-uppercase">{{ str_replace('_', ' ', (string) $pmt->payment_method) }}</td>
                                            <td class="num">{{ money($pmt->amount) }}</td>
                                            <td class="num text-success fw-semibold">+{{ money($pmt->bonus_amount) }}</td>
                                            <td class="num fw-bold">{{ money($pmt->total_payout ?? ($pmt->amount + $pmt->bonus_amount)) }}</td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('farmer-payments.print', $pmt) }}" target="_blank" class="btn btn-xs btn-light border" title="{{ __('app.print') }}">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
