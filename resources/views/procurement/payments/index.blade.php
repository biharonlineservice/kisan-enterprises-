@extends('layouts.app')

@section('title', __('procurement.payments.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'farmer_id', 'method', 'status', 'bonus', 'from', 'to']);
    $activeTab = request('status') ?: (request('bonus') ? 'bonus' : 'all');
@endphp

{{-- 1. Executive Farmer Payments Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-calendar2-check-fill text-warning"></i>
                    <strong>{{ __('procurement.payments.today_chip', ['amount' => money($kpis['today_disbursed']), 'count' => $kpis['today_count']]) }}</strong>
                </span>
                @if(bccomp((string) $kpis['total_bonus_paid'], '0.00', 2) > 0)
                    <span class="erp-hero-chip">
                        <i class="bi bi-gift-fill text-warning"></i>
                        <strong>{{ __('procurement.payments.bonus_paid_chip', ['amount' => money($kpis['total_bonus_paid'])]) }}</strong>
                    </span>
                @endif
                @if(bccomp((string) $kpis['pending_purchase_dues'], '0.00', 2) > 0)
                    <span class="erp-hero-chip">
                        <i class="bi bi-clock-history text-warning"></i>
                        <strong>{{ __('procurement.payments.pending_dues_chip', ['amount' => money($kpis['pending_purchase_dues'])]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('procurement.payments.title') }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                {{ __('procurement.payments.banner_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 flex-shrink-0">
            <a href="{{ route('purchases.index', ['payment' => 'due']) }}" class="erp-hero-btn">
                <i class="bi bi-receipt"></i>
                <span>{{ __('procurement.payments.purchases_directory') }}</span>
            </a>
            <a href="{{ route('farmers.index', ['due' => 'pending']) }}" class="erp-hero-btn">
                <i class="bi bi-people"></i>
                <span>{{ __('procurement.payments.farmers_directory') }}</span>
            </a>
            @can('farmer_payments.create')
                <a href="{{ route('farmer-payments.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>{{ __('procurement.payments.add_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Total Disbursed --}}
    <div class="col-6 col-lg-3">
        <div class="erp-kpi-card emerald h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="erp-kpi-label">{{ __('procurement.payments.kpi_total_disbursed') }}</span>
                <div class="erp-kpi-icon emerald">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="erp-kpi-value text-success">{{ money($kpis['total_disbursed']) }}</div>
            <div class="erp-kpi-sub text-muted">
                {{ __('procurement.payments.kpi_disbursed_sub', ['count' => $kpis['active_count']]) }}
            </div>
        </div>
    </div>

    {{-- Card 2: Bonus Incentives Paid --}}
    <div class="col-6 col-lg-3">
        <div class="erp-kpi-card gold h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="erp-kpi-label">{{ __('procurement.payments.kpi_bonus_paid') }}</span>
                <div class="erp-kpi-icon gold">
                    <i class="bi bi-gift-fill"></i>
                </div>
            </div>
            <div class="erp-kpi-value text-warning-emphasis">{{ money($kpis['total_bonus_paid']) }}</div>
            <div class="erp-kpi-sub text-muted">
                {{ __('procurement.payments.kpi_bonus_sub', ['count' => $kpis['bonus_count']]) }}
            </div>
        </div>
    </div>

    {{-- Card 3: Pending Purchase Dues --}}
    <div class="col-6 col-lg-3">
        <div class="erp-kpi-card crimson h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="erp-kpi-label">{{ __('procurement.payments.kpi_pending_dues') }}</span>
                <div class="erp-kpi-icon crimson">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="erp-kpi-value text-danger">{{ money($kpis['pending_purchase_dues']) }}</div>
            <div class="erp-kpi-sub text-muted">
                {{ __('procurement.payments.kpi_pending_sub', ['count' => $kpis['pending_purchases_count']]) }}
            </div>
        </div>
    </div>

    {{-- Card 4: Disbursed Today --}}
    <div class="col-6 col-lg-3">
        <div class="erp-kpi-card blue h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="erp-kpi-label">{{ __('procurement.payments.kpi_today_disbursed') }}</span>
                <div class="erp-kpi-icon blue">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
            </div>
            <div class="erp-kpi-value text-primary">{{ money($kpis['today_disbursed']) }}</div>
            <div class="erp-kpi-sub text-muted">
                {{ __('procurement.payments.kpi_today_sub', ['count' => $kpis['today_count']]) }}
            </div>
        </div>
    </div>
</div>

{{-- 3. Segment Tabs & Search Bar Header --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div class="nav erp-tab-pills flex-nowrap overflow-auto py-1">
        <a href="{{ route('farmer-payments.index', request()->except(['status', 'bonus', 'page'])) }}"
           class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}">
            <i class="bi bi-collection me-1"></i>
            <span>{{ __('procurement.payments.tab_all') }}</span>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $kpis['total_count'] }}</span>
        </a>
        <a href="{{ route('farmer-payments.index', array_merge(request()->except(['status', 'bonus', 'page']), ['status' => 'active'])) }}"
           class="nav-link {{ request('status') === 'active' ? 'active' : '' }}">
            <i class="bi bi-check2-circle text-success me-1"></i>
            <span>{{ __('procurement.payments.tab_active') }}</span>
            <span class="badge bg-success-subtle text-success rounded-pill ms-1">{{ $kpis['active_count'] }}</span>
        </a>
        <a href="{{ route('farmer-payments.index', array_merge(request()->except(['status', 'bonus', 'page']), ['bonus' => 'with_bonus'])) }}"
           class="nav-link {{ request('bonus') === 'with_bonus' ? 'active' : '' }}">
            <i class="bi bi-gift text-warning me-1"></i>
            <span>{{ __('procurement.payments.tab_bonus') }}</span>
            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill ms-1">{{ $kpis['bonus_count'] }}</span>
        </a>
        <a href="{{ route('farmer-payments.index', array_merge(request()->except(['status', 'bonus', 'page']), ['status' => 'reversed'])) }}"
           class="nav-link {{ request('status') === 'reversed' ? 'active' : '' }}">
            <i class="bi bi-arrow-counterclockwise text-danger me-1"></i>
            <span>{{ __('procurement.payments.tab_reversed') }}</span>
            <span class="badge bg-danger-subtle text-danger rounded-pill ms-1">{{ $kpis['reversed_count'] }}</span>
        </a>
    </div>

    {{-- Mobile Filter Trigger Button --}}
    <button class="btn btn-sm btn-outline-secondary d-md-none d-flex align-items-center justify-content-center gap-1"
            type="button" data-bs-toggle="collapse" data-bs-target="#paymentFilterDrawer" aria-expanded="false">
        <i class="bi bi-funnel"></i>
        <span>{{ __('app.filter') }}</span>
        @if($hasFilters)
            <span class="badge bg-success rounded-pill ms-1">Active</span>
        @endif
    </button>
</div>

{{-- 4. Collapsible Mobile & Inline Desktop Filters Card --}}
<div class="collapse d-md-block" id="paymentFilterDrawer">
    <div class="card shadow-sm border-0 mb-4 bg-white" style="border-radius: 12px;">
        <div class="p-3 bg-light bg-opacity-50 border-bottom">
            <form method="GET" action="{{ route('farmer-payments.index') }}" data-autosubmit>
                {{-- Row 1: Search (6 cols), Farmer (3 cols), Payment Mode (3 cols) --}}
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 col-lg-6">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   class="form-control form-control-sm border-start-0"
                                   placeholder="{{ __('procurement.purchases.search_placeholder') }}">
                        </div>
                    </div>

                    <div class="col-6 col-md-3 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.payments.farmer') }}</label>
                        <select name="farmer_id" class="form-select form-select-sm">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($farmers as $f)
                                <option value="{{ $f->id }}" @selected(request('farmer_id') == $f->id)>{{ $f->name }} ({{ $f->farmer_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.payments.payment_mode') }}</label>
                        <select name="method" class="form-select form-select-sm">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($methods as $key => $lbl)
                                <option value="{{ $key }}" @selected(request('method') === $key)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 2: Status (3 cols), Bonus (3 cols), From Date (2 cols), To Date (2 cols), Actions (2 cols) --}}
                <div class="row g-2 align-items-end mt-1">
                    <div class="col-6 col-md-3 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.payments.filter_status') }}</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">{{ __('app.all') }}</option>
                            <option value="active" @selected(request('status') === 'active')>{{ __('procurement.payments.tab_active') }}</option>
                            <option value="reversed" @selected(request('status') === 'reversed')>{{ __('procurement.payments.tab_reversed') }}</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ app()->getLocale() === 'hi' ? 'बोनस स्थिति' : 'Bonus Incentive' }}</label>
                        <select name="bonus" class="form-select form-select-sm">
                            <option value="">{{ __('app.all') }}</option>
                            <option value="with_bonus" @selected(request('bonus') === 'with_bonus')>{{ __('procurement.payments.tab_bonus') }}</option>
                            <option value="none" @selected(request('bonus') === 'none')>{{ app()->getLocale() === 'hi' ? 'बिना बोनस' : 'Standard (No Bonus)' }}</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.purchases.from_date') }}</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.purchases.to_date') }}</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                    </div>

                    <div class="col-12 col-md-12 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1 d-none d-lg-block">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary fw-semibold flex-grow-1 shadow-sm text-nowrap">
                                <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                            </button>
                            @if($hasFilters)
                                <a href="{{ route('farmer-payments.index') }}" class="btn btn-sm btn-light border px-2.5 text-nowrap" title="{{ __('app.reset') }}">
                                    <i class="bi bi-x-circle text-danger"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 5. Desktop Payment Vouchers Table (>= 768px) --}}
<div class="card shadow-sm border-0 d-none d-md-block mb-4" style="border-radius: 0.75rem; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-compact-erp">
            <thead class="table-light text-muted small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                <tr>
                    <th class="ps-3">{{ __('procurement.payments.col_voucher') }}</th>
                    <th>{{ __('procurement.payments.col_farmer') }}</th>
                    <th>{{ __('procurement.payments.col_purchase') }}</th>
                    <th>{{ __('procurement.payments.col_method') }}</th>
                    <th class="text-end">{{ __('procurement.payments.col_principal') }}</th>
                    <th class="text-end">{{ __('procurement.payments.col_bonus') }}</th>
                    <th class="text-end">{{ __('procurement.payments.col_total') }}</th>
                    <th class="text-center">{{ __('procurement.payments.col_status') }}</th>
                    <th class="text-end pe-3">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr class="{{ $p->isReversed() ? 'table-danger-subtle opacity-75' : '' }}">
                        {{-- Voucher # & Date --}}
                        <td class="ps-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success p-2 rounded-circle">
                                    <i class="bi bi-cash-coin fs-6"></i>
                                </span>
                                <div>
                                    <a href="{{ route('farmer-payments.show', $p) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $p->voucher_number }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.78rem;">
                                        <i class="bi bi-calendar3 me-1"></i>{{ fmt_date($p->payment_date) }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Farmer Info --}}
                        <td class="py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary-subtle text-secondary rounded-circle px-2 py-1 small fw-bold">
                                    {{ strtoupper(substr($p->farmer->name, 0, 1)) }}
                                </span>
                                <div>
                                    <a href="{{ route('farmers.show', $p->farmer) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                        {{ $p->farmer->name }}
                                    </a>
                                    <div class="d-flex align-items-center gap-2" style="font-size: 0.76rem;">
                                        <code class="text-muted">{{ $p->farmer->farmer_id }}</code>
                                        @if($p->farmer->phone)
                                            <a href="tel:{{ $p->farmer->phone }}" class="text-muted text-decoration-none">
                                                <i class="bi bi-telephone-fill text-success" style="font-size: 0.7rem;"></i> {{ $p->farmer->phone }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Related Purchase Receipt --}}
                        <td class="py-2">
                            @if($p->purchase)
                                <a href="{{ route('purchases.show', $p->purchase) }}" class="text-decoration-none d-inline-flex align-items-center gap-1 badge bg-light text-dark border">
                                    <i class="bi bi-receipt text-muted"></i>
                                    <strong>{{ $p->purchase->purchase_number }}</strong>
                                </a>
                                <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                                    {{ $p->purchase->crop?->name ?? 'Crop' }} • {{ fmt_date($p->purchase->purchase_date) }}
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        {{-- Payment Method & Reference --}}
                        <td class="py-2">
                            <div class="d-flex flex-column gap-1">
                                <span class="badge bg-light text-dark border align-self-start">
                                    @if($p->payment_method === 'cash')
                                        <i class="bi bi-cash text-success me-1"></i>
                                    @elseif($p->payment_method === 'bank_transfer')
                                        <i class="bi bi-bank text-primary me-1"></i>
                                    @elseif($p->payment_method === 'upi')
                                        <i class="bi bi-qr-code text-info me-1"></i>
                                    @else
                                        <i class="bi bi-credit-card text-secondary me-1"></i>
                                    @endif
                                    {{ $p->method_label }}
                                </span>
                                @if($p->transaction_reference)
                                    <code class="text-muted small" style="font-size: 0.74rem;">{{ $p->transaction_reference }}</code>
                                @endif
                            </div>
                        </td>

                        {{-- Principal Paid --}}
                        <td class="text-end py-2">
                            <span class="fw-semibold text-dark">{{ money($p->amount) }}</span>
                        </td>

                        {{-- Delayed Bonus --}}
                        <td class="text-end py-2">
                            @if(bccomp((string) $p->bonus_amount, '0.00', 2) > 0)
                                <div class="fw-bold text-success">+{{ money($p->bonus_amount) }}</div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">
                                    <i class="bi bi-gift-fill me-1"></i>{{ $p->days_delayed }}d @ {{ bc_round($p->bonus_percentage, 2) }}%
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        {{-- Total Effective Payout --}}
                        <td class="text-end py-2">
                            <div class="fs-6 fw-bold text-success">{{ money($p->effective_payout) }}</div>
                        </td>

                        {{-- Status --}}
                        <td class="text-center py-2">
                            @if($p->isReversed())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('procurement.purchases.status_reversed') }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ app()->getLocale() === 'hi' ? 'पूर्ण चुकता' : 'Paid' }}
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-end pe-3 py-2">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('farmer-payments.show', $p) }}" class="btn btn-outline-secondary" title="{{ __('procurement.payments.view_voucher') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('farmer-payments.print', $p) }}" target="_blank" class="btn btn-outline-success" title="{{ __('procurement.payments.print_voucher') }}">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-cash-stack fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="fw-semibold">{{ __('app.no_records') }}</div>
                            <small class="text-muted">{{ __('procurement.purchases.no_vouchers_yet') }}</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 6. Mobile App Card Feed View (< 768px) --}}
<div class="d-md-none mobile-card-list mb-4">
    @forelse($payments as $p)
        <div class="mobile-record-card">
            {{-- Header --}}
            <div class="mobile-card-header">
                <div>
                    <a href="{{ route('farmer-payments.show', $p) }}" class="mobile-card-title text-decoration-none">
                        <i class="bi bi-cash-stack text-success"></i> {{ $p->voucher_number }}
                    </a>
                    <div class="mobile-card-meta">
                        <i class="bi bi-calendar3 me-1"></i>{{ fmt_date($p->payment_date) }}
                    </div>
                </div>
                <div>
                    @if($p->isReversed())
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ __('procurement.purchases.status_reversed') }}</span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ app()->getLocale() === 'hi' ? 'पूर्ण चुकता' : 'Paid' }}</span>
                    @endif
                </div>
            </div>

            {{-- Body --}}
            <div class="mobile-card-body">
                <div class="mobile-card-main">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-secondary-subtle text-secondary rounded-circle px-2 py-1 small fw-bold">
                            {{ strtoupper(substr($p->farmer->name, 0, 1)) }}
                        </span>
                        <div>
                            <a href="{{ route('farmers.show', $p->farmer) }}" class="mobile-card-name text-decoration-none text-dark">
                                {{ $p->farmer->name }}
                            </a>
                            <div class="mobile-card-sub">
                                <code>{{ $p->farmer->farmer_id }}</code>
                                @if($p->farmer->phone)
                                    • <a href="tel:{{ $p->farmer->phone }}" class="text-success text-decoration-none">
                                        <i class="bi bi-telephone-fill"></i> {{ $p->farmer->phone }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 d-flex flex-wrap gap-1">
                        <span class="mobile-chip">
                            @if($p->payment_method === 'cash')
                                <i class="bi bi-cash text-success"></i>
                            @elseif($p->payment_method === 'bank_transfer')
                                <i class="bi bi-bank text-primary"></i>
                            @elseif($p->payment_method === 'upi')
                                <i class="bi bi-qr-code text-info"></i>
                            @else
                                <i class="bi bi-credit-card text-secondary"></i>
                            @endif
                            {{ $p->method_label }}
                        </span>
                        @if($p->purchase)
                            <a href="{{ route('purchases.show', $p->purchase) }}" class="mobile-chip text-decoration-none">
                                <i class="bi bi-receipt"></i> {{ $p->purchase->purchase_number }}
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="mobile-card-stats text-end">
                    <div class="mobile-stat-primary success">{{ money($p->effective_payout) }}</div>
                    @if(bccomp((string) $p->bonus_amount, '0.00', 2) > 0)
                        <div class="mobile-stat-secondary text-success fw-bold">
                            <i class="bi bi-gift-fill me-1"></i>+{{ money($p->bonus_amount) }} bonus
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem;">
                            {{ $p->days_delayed }}d @ {{ bc_round($p->bonus_percentage, 2) }}%
                        </div>
                    @else
                        <div class="mobile-stat-secondary text-muted">Principal: {{ money($p->amount) }}</div>
                    @endif
                    @if($p->transaction_reference)
                        <div class="mobile-stat-secondary mt-1"><code>{{ $p->transaction_reference }}</code></div>
                    @endif
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="mobile-card-footer">
                <div class="text-muted small">
                    {{ __('procurement.payments.payment_mode') }}: <strong>{{ $p->method_label }}</strong>
                </div>
                <div class="mobile-card-actions">
                    <a href="{{ route('farmer-payments.show', $p) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye"></i> {{ __('app.view') }}
                    </a>
                    <a href="{{ route('farmer-payments.print', $p) }}" target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-printer"></i> {{ __('app.print') }}
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="card p-4 text-center text-muted shadow-sm">
            <i class="bi bi-cash-stack fs-1 d-block mb-2 text-secondary opacity-50"></i>
            <div class="fw-semibold">{{ __('app.no_records') }}</div>
        </div>
    @endforelse
</div>

{{-- 7. Pagination --}}
@if($payments->hasPages())
    <div class="card shadow-sm border-0 p-3 bg-white mb-4" style="border-radius: 0.75rem;">
        {{ $payments->links() }}
    </div>
@endif

{{-- 8. Mobile Floating Action Button (FAB) --}}
@can('farmer_payments.create')
    <a href="{{ route('farmer-payments.create') }}" class="mobile-fab" aria-label="{{ __('procurement.payments.add_new') }}">
        <i class="bi bi-plus-lg fs-5"></i>
        <span>{{ __('procurement.payments.add_new') }}</span>
    </a>
@endcan
@endsection
