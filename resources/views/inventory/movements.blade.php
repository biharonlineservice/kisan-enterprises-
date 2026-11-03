@extends('layouts.app')

@section('title', __('inventory.movements.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'warehouse_id', 'crop_id', 'direction', 'source_type', 'from', 'to']);
    $activeDirection = request('direction');
    $activeSourceType = request('source_type');
@endphp

{{-- 1. Executive Multi-Warehouse Movement Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-clock-history text-warning"></i>
                    <strong>{{ __('inventory.movements.banner_today', ['in_qtl' => bc_round($todayInward, 1), 'out_qtl' => bc_round($todayOutward, 1)]) }}</strong>
                </span>
                <span class="erp-hero-chip">
                    <i class="bi bi-journal-text"></i>
                    <strong>{{ __('inventory.movements.banner_total', ['count' => number_format($totalCount)]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('inventory.movements.title') }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82); max-width: 720px;">
                {{ __('inventory.movements.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('inventory.index') }}" class="erp-hero-btn">
                <i class="bi bi-box-seam"></i>
                <span>{{ __('inventory.stock_ledger') }}</span>
            </a>
            @can('inventory.adjust')
                <a href="{{ route('stock-adjustments.create') }}" class="erp-hero-btn">
                    <i class="bi bi-sliders"></i>
                    <span>{{ __('inventory.adjust_stock') }}</span>
                </a>
            @endcan
            @can('inventory.transfer')
                <a href="{{ route('stock-transfers.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-truck-flatbed"></i>
                    <span>{{ __('inventory.transfer_stock') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Inward Stock --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.movements', ['direction' => 'in']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.movements.kpi_inward') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-arrow-down-left"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">
                    +{{ bc_round($totalInwardQtl, 2) }} <span class="fs-6 text-muted fw-normal">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ number_format($inwardBags) }} Bags &bull; {{ number_format($inwardCount) }} Entries
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Total Outward Stock --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.movements', ['direction' => 'out']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #D94848;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.movements.kpi_outward') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEE2E2; color: #DC2626;">
                        <i class="bi bi-arrow-up-right"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #DC2626;">
                    -{{ bc_round($totalOutwardQtl, 2) }} <span class="fs-6 text-muted fw-normal">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ number_format($outwardBags) }} Bags &bull; {{ number_format($outwardCount) }} Dispatches
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Net Movement Flow --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.movements') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.movements.kpi_net_flow') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-arrow-left-right"></i>
                    </span>
                </div>
                <div class="kpi-value {{ bccomp($netFlowQtl, '0', 3) >= 0 ? 'text-success' : 'text-danger' }} text-truncate">
                    {{ bccomp($netFlowQtl, '0', 3) >= 0 ? '+' : '' }}{{ bc_round($netFlowQtl, 2) }} <span class="fs-6 text-muted fw-normal">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    ~{{ number_format($netFlowBags) }} Bags Net Flow
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Total Movement Records --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.movements') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.movements.kpi_entries') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-journal-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">
                    {{ number_format($totalCount) }}
                </div>
                <div class="kpi-sub text-truncate">
                    Total Movement Logs
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Movement Quick Filter Tabs --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a href="{{ route('inventory.movements', request()->except(['direction', 'source_type', 'page'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ !$activeDirection && !$activeSourceType ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>{{ __('inventory.movements.tab_all') }}</span>
                    <span class="badge {{ !$activeDirection && !$activeSourceType ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['all'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('inventory.movements', array_merge(request()->except(['page']), ['direction' => 'in', 'source_type' => null])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeDirection === 'in' ? 'active' : '' }}">
                    <i class="bi bi-arrow-down-left text-success"></i>
                    <span>{{ __('inventory.movements.tab_in') }}</span>
                    <span class="badge {{ $activeDirection === 'in' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['in'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('inventory.movements', array_merge(request()->except(['page']), ['direction' => 'out', 'source_type' => null])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeDirection === 'out' ? 'active' : '' }}">
                    <i class="bi bi-arrow-up-right text-danger"></i>
                    <span>{{ __('inventory.movements.tab_out') }}</span>
                    <span class="badge {{ $activeDirection === 'out' ? 'bg-danger text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['out'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('inventory.movements', array_merge(request()->except(['page']), ['source_type' => 'purchase', 'direction' => null])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeSourceType === 'purchase' ? 'active' : '' }}">
                    <i class="bi bi-cart-plus"></i>
                    <span>{{ __('inventory.movements.tab_purchases') }}</span>
                    <span class="badge {{ $activeSourceType === 'purchase' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['purchase'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('inventory.movements', array_merge(request()->except(['page']), ['source_type' => 'sale', 'direction' => null])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeSourceType === 'sale' ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>{{ __('inventory.movements.tab_sales') }}</span>
                    <span class="badge {{ $activeSourceType === 'sale' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['sale'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('inventory.movements', array_merge(request()->except(['page']), ['source_type' => 'stock_transfer', 'direction' => null])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeSourceType === 'stock_transfer' ? 'active' : '' }}">
                    <i class="bi bi-truck-flatbed"></i>
                    <span>{{ __('inventory.movements.tab_transfers') }}</span>
                    <span class="badge {{ $activeSourceType === 'stock_transfer' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['stock_transfer'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('inventory.movements', array_merge(request()->except(['page']), ['source_type' => 'stock_adjustment', 'direction' => null])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeSourceType === 'stock_adjustment' ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    <span>{{ __('inventory.movements.tab_adjustments') }}</span>
                    <span class="badge {{ $activeSourceType === 'stock_adjustment' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['stock_adjustment'] }}
                    </span>
                </a>
            </li>
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Audit Reconciled
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-box-seam me-1"></i>Stock Trail
            </span>
        </div>
    </div>
</div>

{{-- 4. Filters & Search Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('inventory.movements') }}" class="row g-2 align-items-end" data-autosubmit>
            @if($activeDirection)
                <input type="hidden" name="direction" value="{{ $activeDirection }}">
            @endif
            @if($activeSourceType)
                <input type="hidden" name="source_type" value="{{ $activeSourceType }}">
            @endif

            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           class="form-control form-control-sm border-start-0 ps-0"
                           placeholder="{{ __('inventory.movements.search_placeholder') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <select name="warehouse_id" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.movements.filter_all_warehouses') }}</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="crop_id" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.movements.filter_all_crops') }}</option>
                    @foreach($crops as $c)
                        <option value="{{ $c->id }}" @selected(request('crop_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <input type="date"
                       name="from"
                       value="{{ request('from') }}"
                       class="form-control form-control-sm"
                       title="{{ __('app.date') }} (From)">
            </div>

            <div class="col-6 col-md-2">
                <input type="date"
                       name="to"
                       value="{{ request('to') }}"
                       class="form-control form-control-sm"
                       title="{{ __('app.date') }} (To)">
            </div>

            <div class="col-12 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                </button>
                @if($hasFilters)
                    <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Mobile Feed (< 768px) --}}
<div class="d-md-none mb-4">
    @if($transactions->count() > 0)
        <div class="mobile-card-list">
            @foreach($transactions as $t)
                @php
                    $isPositive = bccomp($t->quantity_qtl, '0', 3) > 0;
                    $absQtl = abs((float) $t->quantity_qtl);
                    $estBags = (int) round($absQtl * 2);
                @endphp
                <div class="mobile-record-card mb-3 border rounded-3 p-3 bg-white shadow-sm">
                    {{-- Card Header --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <span class="fw-bold text-success font-monospace fs-6">
                                <i class="bi bi-clock-history"></i> {{ $t->reference_number ?? '#' . $t->id }}
                            </span>
                            <div class="text-muted fs-xs mt-0.5">
                                <i class="bi bi-calendar3"></i> {{ fmt_date($t->transaction_date, 'd M, h:i A') }}
                            </div>
                        </div>
                        <div>
                            @if($t->direction === 'in')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-xs">
                                    <i class="bi bi-arrow-down-left me-1"></i>{{ __('inventory.movements.badge_in') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fs-xs">
                                    <i class="bi bi-arrow-up-right me-1"></i>{{ __('inventory.movements.badge_out') }}
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
                                    {{ $t->crop->name }}
                                    @if($t->cropVariety)
                                        <span class="text-muted fw-normal fs-xs">({{ $t->cropVariety->name }})</span>
                                    @endif
                                </div>
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5 fs-xs">
                                    {{ ucfirst(str_replace('_', ' ', $t->source_type)) }}
                                </span>
                            </div>
                        </div>

                        {{-- Details Box --}}
                        <div class="bg-light p-2.5 rounded-3 mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted fs-xs">{{ __('inventory.warehouse') }}:</span>
                                <span class="small fw-semibold text-dark">
                                    <i class="bi bi-building text-muted me-1"></i>{{ $t->warehouse->name }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted fs-xs">{{ __('inventory.col_bags') }}:</span>
                                <span class="small font-monospace text-dark">
                                    ~{{ $estBags }} Bags
                                </span>
                            </div>
                            @if($t->description)
                                <div class="text-muted fs-xs mt-1.5 pt-1.5 border-top">
                                    <i class="bi bi-chat-left-text me-1"></i>{{ $t->description }}
                                </div>
                            @endif
                            @if($t->creator)
                                <div class="text-muted fs-xs mt-1">
                                    <i class="bi bi-person me-1"></i>Logged by: <strong>{{ $t->creator->name }}</strong>
                                </div>
                            @endif
                        </div>

                        {{-- Quantity --}}
                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <span class="text-muted small fw-semibold">{{ __('inventory.quantity_qtl') }}:</span>
                            <div class="text-end">
                                <span class="font-monospace fw-bold fs-5 {{ $isPositive ? 'text-success' : 'text-danger' }}">
                                    {{ $isPositive ? '+' : '' }}{{ bc_round($t->quantity_qtl, 3) }} Qtl
                                </span>
                                <div class="text-muted fs-xs font-monospace">
                                    ({{ $isPositive ? '+' : '' }}{{ bc_round($t->quantity_kg, 1) }} Kg)
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="d-flex align-items-center justify-content-between pt-1">
                        <div>
                            @if($t->source_type === 'purchase' && $t->source_id)
                                <a href="{{ route('purchases.show', $t->source_id) }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-receipt me-1"></i>View Purchase
                                </a>
                            @elseif(($t->source_type === 'sale' || $t->source_type === 'sales_invoice') && $t->source_id)
                                <a href="{{ route('sales-invoices.show', $t->source_id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-receipt-cutoff me-1"></i>View Invoice
                                </a>
                            @elseif($t->source_type === 'stock_transfer' && $t->source_id)
                                <a href="{{ route('stock-transfers.show', $t->source_id) }}" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-truck-flatbed me-1"></i>View Transfer
                                </a>
                            @elseif($t->source_type === 'stock_adjustment' && $t->source_id)
                                <a href="{{ route('stock-adjustments.show', $t->source_id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-sliders me-1"></i>View Adj
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
            <h6 class="fw-bold mb-1">{{ __('inventory.movements.empty_title') }}</h6>
            <p class="text-muted small mb-3">{{ __('inventory.movements.empty_sub') }}</p>
            @if($hasFilters)
                <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('app.reset') }}
                </a>
            @endif
        </div>
    @endif
</div>

{{-- 6. Desktop Movements Table (>= 768px) --}}
<div class="card shadow-sm border d-none d-md-block mb-4">
    <div class="table-responsive">
        <table class="table table-hover table-compact-erp align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 140px;">{{ __('inventory.movements.col_datetime') }}</th>
                    <th style="min-width: 160px;">{{ __('inventory.movements.col_voucher') }}</th>
                    <th>{{ __('inventory.movements.col_warehouse') }}</th>
                    <th>{{ __('inventory.movements.col_crop') }}</th>
                    <th>{{ __('inventory.movements.col_type') }}</th>
                    <th class="text-center">{{ __('inventory.movements.col_flow') }}</th>
                    <th class="text-end">{{ __('inventory.movements.col_bags') }}</th>
                    <th class="text-end">{{ __('inventory.movements.col_weight_kg') }}</th>
                    <th class="text-end">{{ __('inventory.movements.col_qty_qtl') }}</th>
                    <th style="min-width: 180px;">{{ __('inventory.movements.col_notes') }}</th>
                    <th>{{ __('inventory.movements.col_user') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    @php
                        $isPositive = bccomp($t->quantity_qtl, '0', 3) > 0;
                        $absQtl = abs((float) $t->quantity_qtl);
                        $estBags = (int) round($absQtl * 2);
                    @endphp
                    <tr>
                        <td class="small text-nowrap">
                            <i class="bi bi-clock text-muted me-1"></i>{{ fmt_date($t->transaction_date, 'd M Y, h:i A') }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="fw-bold font-monospace text-dark">{{ $t->reference_number ?? '#' . $t->id }}</span>
                                @if($t->source_type === 'purchase' && $t->source_id)
                                    <a href="{{ route('purchases.show', $t->source_id) }}" class="text-success small ms-1" title="View Purchase">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @elseif(($t->source_type === 'sale' || $t->source_type === 'sales_invoice') && $t->source_id)
                                    <a href="{{ route('sales-invoices.show', $t->source_id) }}" class="text-primary small ms-1" title="View Sales Invoice">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @elseif($t->source_type === 'stock_transfer' && $t->source_id)
                                    <a href="{{ route('stock-transfers.show', $t->source_id) }}" class="text-warning-emphasis small ms-1" title="View Transfer Challan">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @elseif($t->source_type === 'stock_adjustment' && $t->source_id)
                                    <a href="{{ route('stock-adjustments.show', $t->source_id) }}" class="text-secondary small ms-1" title="View Adjustment Record">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-building me-1 text-secondary"></i>{{ $t->warehouse->name }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $t->crop->name }}</div>
                            <div class="small text-muted">
                                <span class="badge bg-secondary-subtle text-secondary py-0 px-1 font-monospace" style="font-size: 0.7rem;">{{ $t->crop->code }}</span>
                                @if($t->cropVariety)
                                    <span class="ms-1">{{ $t->cropVariety->name }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($t->source_type === 'purchase')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-cart-plus me-1"></i>Purchase
                                </span>
                            @elseif($t->source_type === 'sale' || $t->source_type === 'sales_invoice')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi bi-receipt me-1"></i>Sale Dispatch
                                </span>
                            @elseif($t->source_type === 'stock_transfer')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-truck-flatbed me-1"></i>Transfer
                                </span>
                            @elseif($t->source_type === 'stock_adjustment')
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <i class="bi bi-sliders me-1"></i>Adjustment
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    {{ ucfirst(str_replace('_', ' ', $t->source_type)) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($t->direction === 'in')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-arrow-down-left me-1"></i>{{ __('inventory.movements.badge_in') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-arrow-up-right me-1"></i>{{ __('inventory.movements.badge_out') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end small text-muted">
                            <span class="badge bg-light text-dark border">~{{ number_format($estBags) }}</span>
                        </td>
                        <td class="text-end font-monospace small {{ $isPositive ? 'text-success' : 'text-danger' }}">
                            {{ $isPositive ? '+' : '' }}{{ bc_round($t->quantity_kg, 1) }}
                        </td>
                        <td class="text-end font-monospace fw-bold {{ $isPositive ? 'text-success' : 'text-danger' }}">
                            {{ $isPositive ? '+' : '' }}{{ bc_round($t->quantity_qtl, 3) }}
                        </td>
                        <td class="small text-muted" style="max-width: 240px;">
                            <div class="text-truncate" title="{{ $t->description }}">{{ $t->description ?: '-' }}</div>
                        </td>
                        <td class="small text-muted text-nowrap">
                            <i class="bi bi-person me-1"></i>{{ $t->creator?->name ?? 'System' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">{{ __('inventory.movements.empty_title') }}</h6>
                            <p class="text-muted small mb-3">{{ __('inventory.movements.empty_sub') }}</p>
                            @if($hasFilters)
                                <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('app.reset') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-3">
            <span class="small text-muted">
                Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} records
            </span>
            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    @endif
</div>

@if($transactions->hasPages())
    <div class="d-md-none mb-4">
        {{ $transactions->links() }}
    </div>
@endif

{{-- 7. Mobile Floating Action Button (FAB) --}}
@can('inventory.transfer')
    <a href="{{ route('stock-transfers.create') }}" class="mobile-fab" aria-label="{{ __('inventory.transfers.add_new') }}">
        <i class="bi bi-plus-lg"></i>
        <span>{{ __('inventory.transfers.add_new') }}</span>
    </a>
@endcan
@endsection
