@extends('layouts.app')

@section('title', __('inventory.transfers.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'crop_id', 'from_warehouse_id', 'to_warehouse_id', 'from', 'to', 'segment']);
    $activeSegment = request('segment');
@endphp

{{-- 1. Executive Multi-Warehouse Transfer Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-clock-history text-warning"></i>
                    <strong>{{ __('inventory.transfers.banner_today', ['count' => $todayTransfersCount, 'qtl' => bc_round($todayTransfersQtl, 1)]) }}</strong>
                </span>
                <span class="erp-hero-chip">
                    <i class="bi bi-truck-flatbed"></i>
                    <strong>{{ __('inventory.transfers.banner_total', ['count' => number_format($totalTransfersCount)]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('inventory.transfers.title') }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82); max-width: 720px;">
                {{ __('inventory.transfers.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('inventory.index') }}" class="erp-hero-btn">
                <i class="bi bi-box-seam"></i>
                <span>{{ __('inventory.stock_ledger') }}</span>
            </a>
            <a href="{{ route('inventory.movements') }}" class="erp-hero-btn">
                <i class="bi bi-clock-history"></i>
                <span>{{ __('inventory.view_movements') }}</span>
            </a>
            @can('inventory.transfer')
                <a href="{{ route('stock-transfers.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('inventory.transfers.new_transfer') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Transferred Volume --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-transfers.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.transfers.kpi_transferred_volume') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-box-seam"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">
                    {{ bc_round($totalQuantityQtl, 2) }} <span class="fs-6 text-muted fw-normal">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ number_format($totalBags) }} Bags &bull; {{ number_format($totalTransfersCount) }} Dispatches
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Participating Godowns --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-transfers.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.transfers.kpi_active_routes') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-building"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">
                    {{ $activeGodownsCount }} <span class="fs-6 text-muted fw-normal">{{ __('inventory.godowns') }}</span>
                </div>
                <div class="kpi-sub text-truncate">
                    Active Storage Locations
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Today's Transfers --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-transfers.index', ['segment' => 'today']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.transfers.kpi_today_transfers') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-truck"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">
                    {{ $todayTransfersCount }} <span class="fs-6 text-muted fw-normal">Orders</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ bc_round($todayTransfersQtl, 1) }} Qtl Today
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Total Transfer Orders --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('stock-transfers.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('inventory.transfers.kpi_records') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-file-earmark-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">
                    {{ number_format($totalTransfersCount) }}
                </div>
                <div class="kpi-sub text-truncate">
                    Total Movement Logs
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Quick Segment Filter Tabs --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a href="{{ route('stock-transfers.index', request()->except(['segment', 'page'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ !$activeSegment ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>{{ __('inventory.transfers.tab_all') }}</span>
                    <span class="badge {{ !$activeSegment ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['all'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-transfers.index', array_merge(request()->except(['page']), ['segment' => 'today'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeSegment === 'today' ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>{{ __('inventory.transfers.tab_today') }}</span>
                    <span class="badge {{ $activeSegment === 'today' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['today'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-transfers.index', array_merge(request()->except(['page']), ['segment' => 'month'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeSegment === 'month' ? 'active' : '' }}">
                    <i class="bi bi-calendar-month"></i>
                    <span>{{ __('inventory.transfers.tab_this_month') }}</span>
                    <span class="badge {{ $activeSegment === 'month' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $tabCounts['month'] }}
                    </span>
                </a>
            </li>
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Internal Transfer
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-truck me-1"></i>Challan Verified
            </span>
        </div>
    </div>
</div>

{{-- 4. Filters & Search Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('stock-transfers.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if($activeSegment)
                <input type="hidden" name="segment" value="{{ $activeSegment }}">
            @endif

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           class="form-control border-start-0"
                           placeholder="{{ __('inventory.transfers.search_placeholder') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('inventory.transfers.source_godown') }}</label>
                <select name="from_warehouse_id" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.transfers.source_godown') }} (All)</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(request('from_warehouse_id') == $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('inventory.transfers.dest_godown') }}</label>
                <select name="to_warehouse_id" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.transfers.dest_godown') }} (All)</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(request('to_warehouse_id') == $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('inventory.commodity') }}</label>
                <select name="crop_id" class="form-select form-select-sm">
                    <option value="">{{ __('inventory.movements.filter_all_crops') }}</option>
                    @foreach($crops as $c)
                        <option value="{{ $c->id }}" @selected(request('crop_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.date') }}</label>
                <input type="date"
                       name="from"
                       value="{{ request('from') }}"
                       class="form-control form-control-sm"
                       title="{{ __('app.date') }} (From)">
            </div>

            <div class="col-12 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                </button>
                @if($hasFilters)
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Mobile Feed (< 768px) --}}
<div class="d-md-none mb-4">
    @if($transfers->count() > 0)
        <div class="mobile-card-list">
            @foreach($transfers as $t)
                @php
                    $estBags = (int) round(((float) $t->quantity_qtl) * 2);
                @endphp
                <div class="mobile-record-card mb-3 border rounded-3 p-3 bg-white shadow-sm">
                    {{-- Card Header --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <a href="{{ route('stock-transfers.show', $t) }}" class="fw-bold text-success text-decoration-none font-monospace fs-6">
                                <i class="bi bi-truck-flatbed"></i> {{ $t->transfer_number }}
                            </a>
                            <div class="text-muted fs-xs mt-0.5">
                                <i class="bi bi-calendar3"></i> {{ fmt_date($t->transfer_date, 'd M Y') }}
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-xs">
                                <i class="bi bi-check-circle-fill"></i> {{ __('app.completed') }}
                            </span>
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
                                    ~{{ number_format($estBags) }} Bags
                                </span>
                            </div>
                        </div>

                        {{-- Route & Logistics Box --}}
                        <div class="bg-light p-2.5 rounded-3 mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="text-truncate" style="max-width: 45%;">
                                    <span class="text-muted d-block fs-xs">{{ __('inventory.transfers.source_godown') }}</span>
                                    <strong class="text-dark small"><i class="bi bi-building me-1"></i>{{ $t->fromWarehouse->name }}</strong>
                                </div>
                                <div class="text-primary px-1">
                                    <i class="bi bi-arrow-right fs-6"></i>
                                </div>
                                <div class="text-truncate text-end" style="max-width: 45%;">
                                    <span class="text-muted d-block fs-xs">{{ __('inventory.transfers.dest_godown') }}</span>
                                    <strong class="text-primary small"><i class="bi bi-building me-1"></i>{{ $t->toWarehouse->name }}</strong>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-muted fs-xs mt-1.5 pt-1.5 border-top">
                                <span>
                                    @if($t->vehicle_number)
                                        <i class="bi bi-truck me-1"></i>{{ $t->vehicle_number }}
                                    @else
                                        <i class="bi bi-box-arrow-right me-1"></i>Direct Handover
                                    @endif
                                </span>
                                <span><i class="bi bi-person me-1"></i>{{ $t->creator?->name ?? 'System' }}</span>
                            </div>
                            @if($t->notes)
                                <div class="text-muted fs-xs mt-1 pt-1 border-top">
                                    <i class="bi bi-chat-left-text me-1"></i>{{ $t->notes }}
                                </div>
                            @endif
                        </div>

                        {{-- Quantity Transferred --}}
                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <span class="text-muted small fw-semibold">{{ __('inventory.quantity_qtl') }}:</span>
                            <div class="text-end">
                                <span class="font-monospace fw-bold text-success fs-5">
                                    {{ bc_round($t->quantity_qtl, 3) }} Qtl
                                </span>
                                <div class="text-muted fs-xs font-monospace">
                                    ({{ bc_round($t->quantity_kg, 1) }} Kg)
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="d-flex align-items-center justify-content-between pt-1">
                        <div>
                            <a href="{{ route('stock-transfers.show', $t) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye"></i> {{ __('app.view') }}
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <a href="{{ route('stock-transfers.challan', $t) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-truck"></i> Challan
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm text-center py-5">
            <i class="bi bi-truck-flatbed fs-1 text-muted mb-2"></i>
            <h6 class="fw-bold mb-1">{{ __('inventory.transfers.empty_title') }}</h6>
            <p class="text-muted small mb-3">{{ __('inventory.transfers.empty_sub') }}</p>
            @if($hasFilters)
                <a href="{{ route('stock-transfers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('app.reset') }}
                </a>
            @endif
        </div>
    @endif
</div>

{{-- 6. Desktop Transfers Table (>= 768px) --}}
<div class="card shadow-sm border d-none d-md-block mb-4">
    <div class="table-responsive">
        <table class="table table-hover table-compact-erp align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 160px;">{{ __('inventory.transfers.col_transfer_no') }}</th>
                    <th style="min-width: 260px;">{{ __('inventory.transfers.col_route') }}</th>
                    <th>{{ __('inventory.transfers.col_commodity') }}</th>
                    <th class="text-end">{{ __('inventory.transfers.col_bags') }}</th>
                    <th class="text-end">{{ __('inventory.transfers.col_weight_kg') }}</th>
                    <th class="text-end">{{ __('inventory.transfers.col_quantity_qtl') }}</th>
                    <th>{{ __('inventory.transfers.col_vehicle') }}</th>
                    <th class="text-center">{{ __('inventory.transfers.col_status') }}</th>
                    <th class="text-end" style="min-width: 140px;">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $t)
                    @php
                        $estBags = (int) round(((float) $t->quantity_qtl) * 2);
                    @endphp
                    <tr>
                        <td>
                            <div>
                                <a href="{{ route('stock-transfers.show', $t) }}" class="fw-bold font-monospace text-decoration-none text-dark">
                                    {{ $t->transfer_number }}
                                </a>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ fmt_date($t->transfer_date, 'd M Y') }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1 text-secondary"></i>{{ $t->fromWarehouse->name }}
                                </span>
                                <i class="bi bi-arrow-right text-primary"></i>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi bi-building me-1"></i>{{ $t->toWarehouse->name }}
                                </span>
                            </div>
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
                        <td class="text-end small text-muted">
                            <span class="badge bg-light text-dark border">~{{ number_format($estBags) }}</span>
                        </td>
                        <td class="text-end font-monospace small">
                            {{ bc_round($t->quantity_kg, 1) }}
                        </td>
                        <td class="text-end font-monospace fw-bold text-success">
                            {{ bc_round($t->quantity_qtl, 3) }}
                        </td>
                        <td>
                            @if($t->vehicle_number)
                                <span class="badge bg-light text-dark border font-monospace">
                                    <i class="bi bi-truck me-1"></i>{{ $t->vehicle_number }}
                                </span>
                            @else
                                <span class="text-muted small">Direct Handover</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ __('app.completed') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('stock-transfers.show', $t) }}" class="btn btn-outline-secondary" title="{{ __('inventory.transfers.view_details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('stock-transfers.challan', $t) }}" target="_blank" class="btn btn-outline-primary" title="{{ __('inventory.transfers.print_challan') }}">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <a href="{{ route('stock-transfers.challan.pdf', $t) }}" class="btn btn-outline-secondary" title="{{ __('inventory.transfers.pdf_challan') }}">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-truck-flatbed fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">{{ __('inventory.transfers.empty_title') }}</h6>
                            <p class="text-muted small mb-3">{{ __('inventory.transfers.empty_sub') }}</p>
                            @if($hasFilters)
                                <a href="{{ route('stock-transfers.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('app.reset') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transfers->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-3">
            <span class="small text-muted">
                Showing {{ $transfers->firstItem() }} to {{ $transfers->lastItem() }} of {{ $transfers->total() }} transfers
            </span>
            <div>
                {{ $transfers->links() }}
            </div>
        </div>
    @endif
</div>

@if($transfers->hasPages())
    <div class="d-md-none mb-4">
        {{ $transfers->links() }}
    </div>
@endif

{{-- 7. Mobile Floating Action Button (FAB) --}}
@can('inventory.transfer')
    <a href="{{ route('stock-transfers.create') }}" class="mobile-fab" aria-label="{{ __('inventory.transfers.new_transfer') }}">
        <i class="bi bi-plus-lg"></i>
        <span>{{ __('inventory.transfers.add_new') }}</span>
    </a>
@endcan
@endsection
