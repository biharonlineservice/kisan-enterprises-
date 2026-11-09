@extends('layouts.app')

@section('title', __('inventory.adjustments.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'crop_id', 'warehouse_id', 'type', 'status', 'flow', 'period', 'from', 'to']);
    $activeStatus = request('status');
    $activeFlow = request('flow');
    $activePeriod = request('period');
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                @if($kpis['pendingCount'] > 0)
                    <span class="erp-hero-chip" style="background: rgba(245, 158, 11, .25); border-color: rgba(245, 158, 11, .5); color: #fef08a; white-space: nowrap;">
                        <i class="bi bi-clock-history text-warning"></i>
                        <strong>{{ __('inventory.adjustments.banner_pending', ['count' => $kpis['pendingCount']]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-sliders"></i>
                    <strong>{{ __('inventory.adjustments.banner_total', ['count' => number_format($kpis['totalCount'])]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('inventory.adjustments.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 720px;">
                {{ __('inventory.adjustments.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('inventory.index') }}" class="erp-hero-btn" title="{{ __('inventory.stock_ledger') }}">
                <i class="bi bi-box-seam"></i>
                <span>{{ __('inventory.adjustments.hero_ledger') }}</span>
            </a>
            <a href="{{ route('inventory.movements') }}" class="erp-hero-btn" title="{{ __('inventory.view_movements') }}">
                <i class="bi bi-clock-history"></i>
                <span>{{ __('inventory.adjustments.hero_movements') }}</span>
            </a>
            <a href="{{ route('stock-transfers.index') }}" class="erp-hero-btn" title="{{ __('inventory.transfers.title') }}">
                <i class="bi bi-truck-flatbed"></i>
                <span>{{ __('inventory.adjustments.hero_transfers') }}</span>
            </a>
            @can('inventory.adjust')
                <a href="{{ route('stock-adjustments.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('inventory.adjustments.hero_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Net Adjustment Flow --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-adjustments.index', ['status' => 'approved']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.adjustments.kpi_net_adjustment') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi {{ $kpis['netApprovedQtl'] >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i>
                    </span>
                </div>
                <div class="kpi-value {{ $kpis['netApprovedQtl'] >= 0 ? 'text-success' : 'text-danger' }} text-truncate">
                    {{ ($kpis['netApprovedQtl'] > 0 ? '+' : '') . number_format($kpis['netApprovedQtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('inventory.adjustments.kpi_net_sub', [
                        'inc' => '+' . number_format($kpis['approvedIncreaseQtl'], 1),
                        'dec' => '-' . number_format($kpis['approvedDecreaseQtl'], 1)
                    ]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Pending Approvals --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-adjustments.index', ['status' => 'pending']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.adjustments.kpi_pending') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-clock-history"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">
                    {{ number_format($kpis['pendingCount']) }}
                </div>
                <div class="kpi-sub text-truncate">
                    @if($kpis['pendingCount'] > 0)
                        {{ __('inventory.adjustments.kpi_pending_sub', ['count' => $kpis['pendingCount']]) }}
                    @else
                        All adjustments up to date
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Stock Additions / Surplus (+) --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-adjustments.index', ['status' => 'approved', 'flow' => 'increase']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.adjustments.kpi_increases') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-plus-circle"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">
                    +{{ number_format($kpis['approvedIncreaseQtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    +{{ number_format($kpis['approvedIncreaseBags']) }} Bags Surplus
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Shrinkage & Losses (-) --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-adjustments.index', ['status' => 'approved', 'flow' => 'decrease']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #D94848;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.adjustments.kpi_decreases') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEE2E2; color: #DC2626;">
                        <i class="bi bi-dash-circle"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #DC2626;">
                    -{{ number_format($kpis['approvedDecreaseQtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    -{{ number_format($kpis['approvedDecreaseBags']) }} Bags Shrinkage
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Status & Flow Tab Pills Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a href="{{ route('stock-adjustments.index', request()->except(['status', 'flow', 'page'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ empty($activeStatus) && empty($activeFlow) ? 'active' : '' }}">
                    <i class="bi bi-collection"></i>
                    <span>{{ __('inventory.adjustments.tab_all') }}</span>
                    <span class="badge {{ empty($activeStatus) && empty($activeFlow) ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['all'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-adjustments.index', array_merge(request()->except(['status', 'flow', 'page']), ['status' => 'pending'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeStatus === 'pending' ? 'active' : '' }}">
                    <i class="bi bi-hourglass-split text-warning"></i>
                    <span>{{ __('inventory.adjustments.tab_pending') }}</span>
                    <span class="badge {{ $activeStatus === 'pending' ? 'bg-warning text-dark' : ($counts['pending'] > 0 ? 'bg-danger text-white' : 'bg-light text-dark border') }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['pending'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-adjustments.index', array_merge(request()->except(['status', 'flow', 'page']), ['status' => 'approved'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeStatus === 'approved' && empty($activeFlow) ? 'active' : '' }}">
                    <i class="bi bi-check-circle text-success"></i>
                    <span>{{ __('inventory.adjustments.tab_approved') }}</span>
                    <span class="badge {{ $activeStatus === 'approved' && empty($activeFlow) ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['approved'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-adjustments.index', array_merge(request()->except(['status', 'flow', 'page']), ['status' => 'rejected'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeStatus === 'rejected' ? 'active' : '' }}">
                    <i class="bi bi-x-circle text-danger"></i>
                    <span>{{ __('inventory.adjustments.tab_rejected') }}</span>
                    <span class="badge {{ $activeStatus === 'rejected' ? 'bg-danger text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['rejected'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-adjustments.index', array_merge(request()->except(['status', 'flow', 'page']), ['flow' => 'increase'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeFlow === 'increase' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle text-success"></i>
                    <span>{{ __('inventory.adjustments.tab_increases') }}</span>
                    <span class="badge {{ $activeFlow === 'increase' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['increase'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-adjustments.index', array_merge(request()->except(['status', 'flow', 'page']), ['flow' => 'decrease'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeFlow === 'decrease' ? 'active' : '' }}">
                    <i class="bi bi-dash-circle text-danger"></i>
                    <span>{{ __('inventory.adjustments.tab_decreases') }}</span>
                    <span class="badge {{ $activeFlow === 'decrease' ? 'bg-danger text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['decrease'] }}
                    </span>
                </a>
            </li>
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Audit Reconciled
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-sliders me-1"></i>Weight Variance
            </span>
        </div>
    </div>
</div>

{{-- 4. Advanced Filter Bar & Listing Card --}}
<div class="card border-0 shadow-sm mb-4 bg-white erp-filter-collapsible" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('stock-adjustments.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if($activeStatus)
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            @if($activeFlow)
                <input type="hidden" name="flow" value="{{ $activeFlow }}">
            @endif

            {{-- Search Input --}}
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0"
                           placeholder="{{ __('inventory.adjustments.search_placeholder') }}"
                           value="{{ request('q') }}">
                </div>
            </div>

            {{-- Warehouse Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('inventory.warehouse') }}</label>
                <select name="warehouse_id" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.adjustments.filter_all_warehouses') }}</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Crop Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('inventory.commodity') }}</label>
                <select name="crop_id" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.adjustments.filter_all_crops') }}</option>
                    @foreach($crops as $c)
                        <option value="{{ $c->id }}" @selected(request('crop_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Adjustment Type Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('inventory.adj_type') }}</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.adjustments.filter_all_types') }}</option>
                    @foreach($types as $k => $label)
                        <option value="{{ $k }}" @selected(request('type') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.date') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>

            {{-- Action Buttons --}}
            <div class="col-12 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1" title="{{ __('app.filter') }}">
                    <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                </button>
                @if($hasFilters)
                    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Mobile Card Feed (< 768px, Mobile-First Optimized for 390px) --}}
<div class="d-md-none">
    @if($adjustments->count() > 0)
        <div class="mobile-card-list">
            @foreach($adjustments as $a)
                <div class="mobile-record-card mb-3 border rounded-3 p-3 bg-white shadow-sm">
                    {{-- Card Header --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <a href="{{ route('stock-adjustments.show', $a) }}" class="fw-bold text-success text-decoration-none font-monospace fs-6">
                                <i class="bi bi-sliders"></i> {{ $a->adjustment_number }}
                            </a>
                            <div class="text-muted fs-xs mt-0.5">
                                <i class="bi bi-calendar3"></i> {{ fmt_date($a->adjustment_date) }}
                            </div>
                        </div>
                        <div>
                            @if($a->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-xs">
                                    <i class="bi bi-check-circle-fill"></i> {{ __('inventory.adjustments.status_approved') }}
                                </span>
                            @elseif($a->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fs-xs">
                                    <i class="bi bi-hourglass-split"></i> {{ __('inventory.adjustments.status_pending') }}
                                </span>
                            @elseif($a->status === 'rejected')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fs-xs">
                                    <i class="bi bi-x-circle"></i> {{ __('inventory.adjustments.status_rejected') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body (Vertical Stack) --}}
                    <div class="py-2 my-2 border-top border-bottom">
                        {{-- Commodity Profile --}}
                        <div class="mb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fw-bold text-dark fs-6">
                                    {{ $a->crop->name }}
                                    @if($a->cropVariety)
                                        <span class="text-muted fw-normal fs-xs">({{ $a->cropVariety->name }})</span>
                                    @endif
                                </div>
                                <span class="badge {{ $a->isIncrease() ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill px-2 py-0.5 fs-xs">
                                    {{ $a->isIncrease() ? __('inventory.adjustments.badge_in') : __('inventory.adjustments.badge_out') }}
                                </span>
                            </div>
                        </div>

                        {{-- Warehouse & Adjustment Details Box --}}
                        <div class="bg-light p-2.5 rounded-3 mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted fs-xs">{{ __('inventory.warehouse') }}:</span>
                                <span class="small fw-semibold text-dark">
                                    <i class="bi bi-building text-muted me-1"></i>{{ $a->warehouse->name }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted fs-xs">{{ __('inventory.adj_type') }}:</span>
                                <span class="badge bg-white text-dark border">
                                    {{ $a->type_name }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted fs-xs">{{ __('inventory.col_bags') }}:</span>
                                <span class="small font-monospace text-dark">
                                    ~{{ $a->est_bags }} Bags
                                </span>
                            </div>
                            @if($a->reason)
                                <div class="text-muted fs-xs mt-1.5 pt-1.5 border-top">
                                    <i class="bi bi-chat-left-text me-1"></i>{{ $a->reason }}
                                </div>
                            @endif
                            @if($a->creator)
                                <div class="text-muted fs-xs mt-1">
                                    <i class="bi bi-person me-1"></i>{{ __('inventory.adjustments.requested_by') }}: <strong>{{ $a->creator->name }}</strong>
                                </div>
                            @endif
                        </div>

                        {{-- Quantity Position --}}
                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <span class="text-muted small fw-semibold">{{ __('inventory.adjustments.col_quantity_qtl') }}:</span>
                            <div class="text-end">
                                <span class="font-monospace fw-bold fs-5 {{ $a->isIncrease() ? 'text-success' : 'text-danger' }}">
                                    {{ $a->isIncrease() ? '+' : '' }}{{ bc_round($a->quantity_qtl, 3) }} Qtl
                                </span>
                                <div class="text-muted fs-xs font-monospace">
                                    ({{ $a->isIncrease() ? '+' : '' }}{{ bc_round($a->quantity_kg, 1) }} Kg)
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="d-flex align-items-center justify-content-between pt-1">
                        <div>
                            <a href="{{ route('stock-adjustments.show', $a) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye"></i> {{ __('app.view') }}
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            @if($a->isPending())
                                @can('inventory.approve')
                                    <form method="POST" action="{{ route('stock-adjustments.approve', $a) }}" class="d-inline"
                                          onsubmit="return confirm('{{ __('inventory.adjustments.confirm_approve_msg') }}');">
                                        @csrf
                                        <x-form-token />
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-check-lg"></i> {{ __('inventory.adjustments.approve_action') }}
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm border-0 text-center py-5 bg-white rounded-3">
            <div class="text-muted mb-2">
                <i class="bi bi-sliders display-6 text-secondary" style="opacity: 0.4;"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">{{ __('inventory.adjustments.empty_title') }}</h6>
            <p class="text-muted small mb-0">{{ __('inventory.adjustments.empty_sub') }}</p>
        </div>
    @endif
</div>

{{-- 6. Desktop Adjustments Table (>= 768px) --}}
<div class="card shadow-sm border-0 d-none d-md-block bg-white rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-compact-erp align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('inventory.adjustments.col_adj_no') }}</th>
                    <th>{{ __('inventory.adjustments.col_warehouse') }}</th>
                    <th>{{ __('inventory.adjustments.col_commodity') }}</th>
                    <th>{{ __('inventory.adjustments.col_type') }}</th>
                    <th class="text-center">{{ __('inventory.adjustments.col_bags') }}</th>
                    <th class="text-end">{{ __('inventory.adjustments.col_weight_kg') }}</th>
                    <th class="text-end">{{ __('inventory.adjustments.col_quantity_qtl') }}</th>
                    <th class="text-center">{{ __('inventory.adjustments.col_status') }}</th>
                    <th>{{ __('inventory.adjustments.col_created_by') }}</th>
                    <th class="text-end">{{ __('inventory.adjustments.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $a)
                    <tr class="align-middle">
                        {{-- Adjustment Number & Date --}}
                        <td>
                            <a href="{{ route('stock-adjustments.show', $a) }}" class="fw-bold text-success text-decoration-none">
                                {{ $a->adjustment_number }}
                            </a>
                            <div class="small text-muted">
                                <i class="bi bi-calendar3 me-1"></i>{{ fmt_date($a->adjustment_date) }}
                            </div>
                        </td>

                        {{-- Warehouse --}}
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-building me-1 text-muted"></i>{{ $a->warehouse->name }}
                            </span>
                        </td>

                        {{-- Commodity & Variety --}}
                        <td>
                            <div class="fw-semibold text-dark">{{ $a->crop->name }}</div>
                            @if($a->cropVariety)
                                <small class="text-muted">{{ $a->cropVariety->name }}</small>
                            @else
                                <small class="text-muted fst-italic">Standard</small>
                            @endif
                        </td>

                        {{-- Adjustment Category & Note --}}
                        <td>
                            <span class="badge {{ $a->isIncrease() ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} mb-1">
                                {{ $a->type_name }}
                            </span>
                            @if($a->reason)
                                <div class="small text-muted text-truncate" style="max-width: 180px;" title="{{ $a->reason }}">
                                    {{ $a->reason }}
                                </div>
                            @endif
                        </td>

                        {{-- Est. Bags --}}
                        <td class="text-center font-monospace">
                            <span class="badge bg-light text-dark border">
                                ~{{ $a->est_bags }}
                            </span>
                        </td>

                        {{-- Net Weight Kg --}}
                        <td class="text-end font-monospace {{ $a->isIncrease() ? 'text-success' : 'text-danger' }}">
                            {{ $a->isIncrease() ? '+' : '' }}{{ bc_round($a->quantity_kg, 1) }} <span class="small text-muted">Kg</span>
                        </td>

                        {{-- Net Quantity Qtl --}}
                        <td class="text-end font-monospace fw-bold {{ $a->isIncrease() ? 'text-success' : 'text-danger' }}">
                            {{ $a->isIncrease() ? '+' : '' }}{{ bc_round($a->quantity_qtl, 3) }} <span class="small text-muted">Qtl</span>
                        </td>

                        {{-- Status Badge --}}
                        <td class="text-center">
                            @if($a->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('inventory.adjustments.status_approved') }}
                                </span>
                            @elseif($a->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i>{{ __('inventory.adjustments.status_pending') }}
                                </span>
                            @elseif($a->status === 'rejected')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('inventory.adjustments.status_rejected') }}
                                </span>
                            @endif
                        </td>

                        {{-- Created By --}}
                        <td>
                            <div class="small text-dark">{{ $a->creator?->name ?? 'System' }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ fmt_date($a->created_at, 'd M Y, h:i A') }}</small>
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('stock-adjustments.show', $a) }}" class="btn btn-outline-secondary" title="{{ __('app.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($a->isPending())
                                    @can('inventory.approve')
                                        <form method="POST" action="{{ route('stock-adjustments.approve', $a) }}" class="d-inline"
                                              onsubmit="return confirm('{{ __('inventory.adjustments.confirm_approve_msg') }}');">
                                            @csrf
                                            <x-form-token />
                                            <button type="submit" class="btn btn-outline-success" title="{{ __('inventory.adjustments.approve_action') }}">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger" title="{{ __('inventory.adjustments.reject_action') }}"
                                                data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $a->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>

                            {{-- Row Reject Modal for quick access --}}
                            @if($a->isPending())
                                @can('inventory.approve')
                                    <div class="modal fade text-start" id="rejectModal-{{ $a->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('stock-adjustments.reject', $a) }}">
                                                @csrf
                                                <x-form-token />
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title h6 text-white mb-0">
                                                            <i class="bi bi-x-circle me-1"></i>{{ __('inventory.adjustments.reject_modal_title') }} ({{ $a->adjustment_number }})
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label required">{{ __('inventory.adjustments.reject_reason_label') }}</label>
                                                            <textarea name="reason" rows="3" class="form-control"
                                                                      placeholder="{{ __('inventory.adjustments.reject_reason_placeholder') }}" required minlength="5"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('inventory.adjustments.cancel_btn') }}</button>
                                                        <button type="submit" class="btn btn-sm btn-danger">{{ __('inventory.adjustments.confirm_reject') }}</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-sliders fs-1 d-block mb-2 text-secondary" style="opacity: 0.4;"></i>
                            <h6 class="fw-bold text-dark mb-1">{{ __('inventory.adjustments.empty_title') }}</h6>
                            <p class="text-muted small mb-0">{{ __('inventory.adjustments.empty_sub') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($adjustments->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $adjustments->links() }}
        </div>
    @endif
</div>

@if($adjustments->hasPages())
    <div class="d-md-none mt-3">
        {{ $adjustments->links() }}
    </div>
@endif

{{-- 7. Mobile Floating Action Button (FAB) --}}
@can('inventory.adjust')
    <a href="{{ route('stock-adjustments.create') }}" class="mobile-fab" aria-label="{{ __('inventory.adjustments.new_adjustment') }}">
        <i class="bi bi-plus-lg"></i>
        <span>{{ __('inventory.adjustments.add_new') }}</span>
    </a>
@endcan
@endsection