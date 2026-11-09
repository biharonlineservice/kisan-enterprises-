@extends('layouts.app')

@section('title', __('inventory.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'warehouse_id', 'status']);
    $estBagsTotal = (int) round((float) $totalStockQtl * 2);
@endphp

{{-- 1. Executive Multi-Warehouse Inventory Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-box-seam text-warning"></i>
                    <strong>{{ __('inventory.banner_today_stock', ['qtl' => bc_round($totalStockQtl, 2), 'bags' => number_format($estBagsTotal)]) }}</strong>
                </span>
                <span class="erp-hero-chip">
                    <i class="bi bi-building"></i>
                    <strong>{{ __('inventory.banner_godowns', ['count' => $warehouses->count()]) }}</strong>
                </span>
                @if($lowStockCount > 0)
                    <span class="erp-hero-chip">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        <strong>{{ __('inventory.banner_low_stock', ['count' => $lowStockCount]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('inventory.title') }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82); max-width: 720px;">
                {{ __('inventory.banner_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('inventory.movements') }}" class="erp-hero-btn">
                <i class="bi bi-clock-history"></i>
                <span>{{ __('inventory.view_movements') }}</span>
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
    {{-- Card 1: Total Physical Stock --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.available_stock') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-box-seam"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">
                    {{ bc_round($totalStockQtl, 2) }} <span class="fs-6 text-muted fw-normal">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    ~{{ number_format($estBagsTotal) }} standard bags (50kg)
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Estimated Stock Valuation --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.est_valuation') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-cash-coin"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">
                    {{ money($estimatedValuation) }}
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('inventory.valuation_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Active Godowns & Storage Capacity --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.godowns') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-building"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">
                    {{ $warehouses->count() }} <span class="fs-6 text-muted fw-normal">{{ __('inventory.godowns') }}</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('inventory.capacity_sub', ['cap' => number_format($totalCapacityQtl)]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Low Stock Alert Items --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('reports.inventory', ['tab' => 'low_stock']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: {{ $lowStockCount > 0 ? '#D94848' : '#21864B' }};">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.low_stock') }}</span>
                    <span class="kpi-badge-icon" style="background: {{ $lowStockCount > 0 ? '#FEE2E2' : '#E8F3EC' }}; color: {{ $lowStockCount > 0 ? '#DC2626' : '#21864B' }};">
                        <i class="bi {{ $lowStockCount > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' }}"></i>
                    </span>
                </div>
                <div class="kpi-value {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }} text-truncate">
                    {{ $lowStockCount }} <span class="fs-6 text-muted fw-normal">Items</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('inventory.low_stock_sub', ['threshold' => setting('inventory.low_stock_threshold_qtl', 10)]) }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Warehouse Quick-Filter Tabs --}}
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2 mb-3">
    <div class="nav erp-tab-pills flex-nowrap overflow-x-auto py-1 flex-grow-1" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
        <a href="{{ route('inventory.index', request()->except(['warehouse_id', 'page'])) }}"
           class="nav-link {{ !$selectedWarehouseId ? 'active' : '' }}">
            <i class="bi bi-buildings me-1"></i>
            <span>{{ __('inventory.tab_all') }}</span>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ bc_round($totalStockQtl, 1) }} Qtl</span>
        </a>
        @foreach($warehouses as $wh)
            @php
                $whTotal = $warehouseStockTotals[$wh->id] ?? '0.000';
            @endphp
            <a href="{{ route('inventory.index', array_merge(request()->except(['page']), ['warehouse_id' => $wh->id])) }}"
               class="nav-link {{ $selectedWarehouseId === $wh->id ? 'active' : '' }}">
                <i class="bi bi-building me-1"></i>
                <span>{{ $wh->name }}</span>
                <span class="badge bg-success-subtle text-success rounded-pill ms-1">{{ bc_round($whTotal, 1) }} Qtl</span>
            </a>
        @endforeach
    </div>

    {{-- Mobile Filter Trigger Button --}}
    <button class="btn btn-sm btn-outline-secondary d-md-none d-flex align-items-center justify-content-center gap-1"
            type="button" data-bs-toggle="collapse" data-bs-target="#inventoryFilterDrawer" aria-expanded="false">
        <i class="bi bi-funnel"></i>
        <span>{{ __('app.filter') }}</span>
        @if($hasFilters)
            <span class="badge bg-success rounded-pill ms-1">Active</span>
        @endif
    </button>
</div>

{{-- 4. Collapsible Mobile & Inline Desktop Filters Card --}}
<div class="collapse d-md-block" id="inventoryFilterDrawer">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem; background: #ffffff;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('inventory.index') }}" class="row g-2 align-items-end">
                @if($selectedWarehouseId)
                    <input type="hidden" name="warehouse_id" value="{{ $selectedWarehouseId }}">
                @endif

                {{-- Search query --}}
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted mb-1">{{ __('app.search') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0"
                               placeholder="{{ __('inventory.search_placeholder') }}">
                    </div>
                </div>

                {{-- Warehouse Dropdown (if not chosen via tab) --}}
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">{{ __('inventory.warehouse') }}</label>
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">{{ __('inventory.all_warehouses') }}</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected($selectedWarehouseId == $wh->id)>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Stock Status Filter --}}
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">{{ __('inventory.col_status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ __('inventory.status_all') }}</option>
                        <option value="in_stock" @selected(request('status') === 'in_stock')>{{ __('inventory.status_in_stock') }}</option>
                        <option value="low_stock" @selected(request('status') === 'low_stock')>{{ __('inventory.status_low_stock') }}</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>{{ __('app.filter') }}
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-danger" title="{{ __('app.reset') }}">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 5. Desktop Stock Balances Table (>= 768px) --}}
<div class="card shadow-sm border-0 d-none d-md-block mb-4" style="border-radius: 0.75rem; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-compact-erp">
            <thead class="table-light text-muted small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                <tr>
                    <th class="ps-3">{{ __('inventory.col_warehouse') }}</th>
                    <th>{{ __('inventory.col_crop') }}</th>
                    <th class="text-end">{{ __('inventory.col_bags') }}</th>
                    <th class="text-end">{{ __('inventory.col_kg') }}</th>
                    <th class="text-end">{{ __('inventory.col_qtl') }}</th>
                    <th class="text-end">{{ __('inventory.col_val') }}</th>
                    <th class="text-center">{{ __('inventory.col_status') }}</th>
                    <th class="text-end pe-3">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockSummary as $row)
                    @php
                        $estBags = (int) round((float) $row->total_qtl * 2);
                    @endphp
                    <tr>
                        {{-- Warehouse Column --}}
                        <td class="ps-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border p-2 rounded-circle">
                                    <i class="bi bi-building fs-6 text-success"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-dark">{{ $row->warehouse_name }}</div>
                                    <code class="text-muted" style="font-size: 0.74rem;">{{ $row->warehouse_code }}</code>
                                </div>
                            </div>
                        </td>

                        {{-- Crop & Variety Column --}}
                        <td class="py-2">
                            <div class="fw-semibold text-dark">{{ $row->crop_name }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1" style="font-size: 0.76rem;">
                                <span class="badge bg-light text-secondary border">{{ $row->crop_code }}</span>
                                <span class="text-muted">{{ $row->variety_name ?: 'Standard / Mixed' }}</span>
                            </div>
                        </td>

                        {{-- Est. Bags Column --}}
                        <td class="text-end py-2">
                            <span class="badge bg-light text-dark border font-monospace">
                                ~{{ number_format($estBags) }} Bags
                            </span>
                        </td>

                        {{-- Net Balance (Kg) --}}
                        <td class="text-end py-2 font-monospace text-muted" style="font-size: 0.85rem;">
                            {{ bc_round($row->total_kg_rounded, 1) }} Kg
                        </td>

                        {{-- Available Stock (Qtl) --}}
                        <td class="text-end py-2">
                            <div class="font-monospace fw-bold fs-6 text-success">
                                {{ bc_round($row->total_qtl_rounded, 3) }} <span class="small fw-normal text-muted">Qtl</span>
                            </div>
                        </td>

                        {{-- Est. Value --}}
                        <td class="text-end py-2">
                            @if(isset($row->estimated_value) && bccomp((string) $row->estimated_value, '0.00', 2) > 0)
                                <div class="fw-semibold text-dark">{{ money($row->estimated_value) }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">@ {{ money($row->rate) }}/Qtl</small>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        {{-- Status Column --}}
                        <td class="text-center py-2">
                            @if($row->is_low_stock)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ __('inventory.low_stock') }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('inventory.in_stock') }}
                                </span>
                            @endif
                        </td>

                        {{-- Actions Column --}}
                        <td class="text-end pe-3 py-2">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('inventory.movements', ['warehouse_id' => $row->warehouse_id, 'crop_id' => $row->crop_id]) }}"
                                   class="btn btn-outline-secondary" title="{{ __('inventory.movements.title') }}">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                @can('inventory.transfer')
                                    <a href="{{ route('stock-transfers.create', ['from_warehouse_id' => $row->warehouse_id, 'crop_id' => $row->crop_id]) }}"
                                       class="btn btn-outline-success" title="{{ __('inventory.transfer_stock') }}">
                                        <i class="bi bi-truck-flatbed"></i>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="fw-semibold">{{ __('app.no_records') }}</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 6. Mobile App Card Feed View (< 768px) --}}
<div class="d-md-none mobile-card-list mb-4">
    @forelse($stockSummary as $row)
        @php
            $estBags = (int) round((float) $row->total_qtl * 2);
        @endphp
        <div class="mobile-record-card">
            {{-- Header --}}
            <div class="mobile-card-header">
                <div>
                    <span class="mobile-card-title">
                        <i class="bi bi-box-seam text-success"></i> {{ $row->crop_name }}
                    </span>
                    <div class="mobile-card-meta">
                        {{ $row->variety_name ?: 'Standard / Mixed' }} • <code>{{ $row->crop_code }}</code>
                    </div>
                </div>
                <div>
                    @if($row->is_low_stock)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                            {{ __('inventory.low_stock') }}
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle small">
                            {{ __('inventory.in_stock') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Body --}}
            <div class="mobile-card-body">
                <div class="mobile-card-main">
                    <div class="mobile-card-name">{{ $row->warehouse_name }}</div>
                    <div class="mobile-card-sub mb-2">
                        <span class="mobile-chip"><i class="bi bi-building"></i> {{ $row->warehouse_code }}</span>
                        <span class="mobile-chip">~{{ number_format($estBags) }} Bags</span>
                    </div>
                    @if(isset($row->estimated_value) && bccomp((string) $row->estimated_value, '0.00', 2) > 0)
                        <div class="text-muted small">
                            Value: <strong>{{ money($row->estimated_value) }}</strong>
                        </div>
                    @endif
                </div>

                <div class="mobile-card-stats text-end">
                    <div class="mobile-stat-primary success font-monospace">
                        {{ bc_round($row->total_qtl_rounded, 2) }} Qtl
                    </div>
                    <div class="mobile-stat-secondary text-muted font-monospace">
                        {{ bc_round($row->total_kg_rounded, 1) }} Kg
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="mobile-card-footer">
                <div class="text-muted small">
                    {{ __('inventory.warehouse') }}: <strong>{{ $row->warehouse_code }}</strong>
                </div>
                <div class="mobile-card-actions">
                    <a href="{{ route('inventory.movements', ['warehouse_id' => $row->warehouse_id, 'crop_id' => $row->crop_id]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-clock-history"></i> {{ __('inventory.movements.title') }}
                    </a>
                    @can('inventory.transfer')
                        <a href="{{ route('stock-transfers.create', ['from_warehouse_id' => $row->warehouse_id, 'crop_id' => $row->crop_id]) }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-truck-flatbed"></i>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    @empty
        <div class="card p-4 text-center text-muted shadow-sm">
            <i class="bi bi-box fs-1 d-block mb-2 text-secondary opacity-50"></i>
            <div class="fw-semibold">{{ __('app.no_records') }}</div>
        </div>
    @endforelse
</div>

{{-- 7. Mobile Floating Action Button (FAB) --}}
@can('inventory.transfer')
    <a href="{{ route('stock-transfers.create') }}" class="mobile-fab" aria-label="{{ __('inventory.transfers.add_new') }}">
        <i class="bi bi-truck-flatbed fs-5"></i>
        <span>{{ __('inventory.transfers.add_new') }}</span>
    </a>
@endcan
@endsection
