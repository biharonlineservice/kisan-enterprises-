@extends('layouts.app')

@section('title', __('warehouses.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-house-door-fill"></i>
                    <strong>{{ __('warehouses.banner_active_count', ['count' => $metrics['activeWarehouses']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-box-seam"></i>
                    {{ __('warehouses.banner_capacity', ['capacity' => number_format($metrics['totalCapacity'], 0)]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-stack text-success"></i>
                    <strong>{{ __('warehouses.banner_stock', ['stock' => number_format($metrics['currentStockHeld'], 1)]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('warehouses.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('warehouses.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('warehouses.create') }}" class="btn btn-sm btn-light text-success fw-bold shadow-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-plus-circle-fill"></i>
                <span>{{ __('warehouses.btn_add_warehouse') }}</span>
            </a>
            @if(\Illuminate\Support\Facades\Route::has('inventory.index'))
                <a href="{{ route('inventory.index') }}" class="erp-hero-btn" title="{{ __('warehouses.btn_stock_ledger') }}">
                    <i class="bi bi-boxes"></i>
                    <span>{{ __('warehouses.btn_stock_ledger') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('inventory.movements'))
                <a href="{{ route('inventory.movements') }}" class="erp-hero-btn" title="{{ __('warehouses.btn_stock_movements') }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>{{ __('warehouses.btn_stock_movements') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('stock-transfers.index'))
                <a href="{{ route('stock-transfers.index') }}" class="erp-hero-btn" title="{{ __('warehouses.btn_stock_transfers') }}">
                    <i class="bi bi-truck"></i>
                    <span>{{ __('warehouses.btn_stock_transfers') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Storage Capacity --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('warehouses.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('warehouses.kpi_total_capacity') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-building"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($metrics['totalCapacity'], 0) }} <span class="fs-6 fw-normal text-muted">Qtl</span></div>
                <div class="kpi-sub text-truncate">
                    {{ __('warehouses.kpi_total_capacity_sub', ['count' => $metrics['activeWarehouses']]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Active Warehouses --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('warehouses.index', ['tab' => 'active']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('warehouses.kpi_active_warehouses') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-check2-circle"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format($metrics['activeWarehouses']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('warehouses.kpi_active_warehouses_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Current Stock Held --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('warehouses.kpi_current_stock') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-box-seam"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($metrics['currentStockHeld'], 1) }} <span class="fs-6 fw-normal text-muted">Qtl</span></div>
                <div class="kpi-sub text-truncate">
                    {{ __('warehouses.kpi_current_stock_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Storage Occupancy --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('warehouses.index', ['tab' => 'with_stock']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('warehouses.kpi_capacity_utilization') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ $metrics['overallOccupancyPct'] }}%</div>
                <div class="kpi-sub text-truncate">
                    {{ __('warehouses.kpi_capacity_utilization_sub') }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Filter Tabs & Search Bar --}}
<div class="card shadow-sm border-0 bg-white rounded-3 mb-4">
    <div class="card-header bg-white border-bottom p-2">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
            <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'all' ? 'active' : '' }}" 
                       href="{{ route('warehouses.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}">
                        <i class="bi bi-grid-fill"></i>
                        <span>{{ __('warehouses.tab_all') }}</span>
                        <span class="badge {{ $tab === 'all' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['totalWarehouses'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'active' ? 'active' : '' }}" 
                       href="{{ route('warehouses.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'active'])) }}">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>{{ __('warehouses.tab_active') }}</span>
                        <span class="badge {{ $tab === 'active' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['activeWarehouses'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'inactive' ? 'active' : '' }}" 
                       href="{{ route('warehouses.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'inactive'])) }}">
                        <i class="bi bi-pause-circle text-secondary"></i>
                        <span>{{ __('warehouses.tab_inactive') }}</span>
                        <span class="badge {{ $tab === 'inactive' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['inactiveWarehouses'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'with_stock' ? 'active' : '' }}" 
                       href="{{ route('warehouses.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'with_stock'])) }}">
                        <i class="bi bi-box-seam text-warning"></i>
                        <span>{{ __('warehouses.tab_with_stock') }}</span>
                        <span class="badge {{ $tab === 'with_stock' ? 'bg-warning text-dark' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['withStockCount'] }}
                        </span>
                    </a>
                </li>
            </ul>

            <div class="pb-1 pb-md-0 flex-shrink-0 pe-2">
                <a href="{{ route('warehouses.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-download"></i>
                    <span>{{ __('warehouses.btn_export_csv') }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card-body p-3 bg-light-subtle border-bottom">
        <form method="GET" action="{{ route('warehouses.index') }}" class="row g-2 align-items-end" data-autosubmit>
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('warehouses.search_placeholder') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('warehouses.search_placeholder') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('warehouses.filter_status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('warehouses.status_all') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('warehouses.status_active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('warehouses.status_inactive') }}</option>
                </select>
            </div>

            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('reports.apply_filter') ?? 'Filter' }}
                </button>
                @if(request()->anyFilled(['q', 'status']))
                    <a href="{{ route('warehouses.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('reports.clear_filter') ?? 'Clear' }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="min-width: 180px;">{{ __('warehouses.col_name') }}</th>
                    <th style="width: 90px;">{{ __('warehouses.col_code') }}</th>
                    <th style="width: 110px;">{{ __('warehouses.col_capacity') }}</th>
                    <th style="min-width: 160px;">{{ __('warehouses.col_stock') }}</th>
                    <th style="width: 140px;">{{ __('warehouses.col_supervisor') }}</th>
                    <th style="width: 110px;">{{ __('warehouses.col_phone') }}</th>
                    <th style="width: 85px;">{{ __('warehouses.col_status') }}</th>
                    <th class="text-end pe-3" style="width: 85px;">{{ __('warehouses.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $wh)
                    @php
                        $stock = (float) ($stocks[$wh->id] ?? 0);
                        $occ = (float) $wh->capacity_quintals > 0 ? min(100, round(($stock / (float) $wh->capacity_quintals) * 100, 1)) : 0;
                        $progressColor = $occ > 85 ? 'bg-danger' : ($occ > 60 ? 'bg-warning' : 'bg-success');
                    @endphp
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-house-door text-success"></i>
                                <span>{{ $wh->name }}</span>
                            </div>
                            @if($wh->address)
                                <small class="text-muted d-block text-truncate ps-4" style="max-width: 260px;" title="{{ $wh->address }}">
                                    {{ $wh->address }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <code class="fw-bold text-primary font-monospace">{{ $wh->code }}</code>
                        </td>
                        <td>
                            <span class="fw-bold text-dark font-monospace">{{ number_format((float) $wh->capacity_quintals, 1) }}</span>
                            <small class="text-muted">Qtl</small>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ number_format($stock, 1) }} Qtl
                                </span>
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">
                                    {{ $occ }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ max($occ, 2) }}%;" aria-valuenow="{{ $occ }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </td>
                        <td>
                            @if($wh->supervisor)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs bg-light text-dark fw-bold rounded-circle border" style="width: 26px; height: 26px; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;">
                                        {{ strtoupper(substr($wh->supervisor->name, 0, 1)) }}
                                    </div>
                                    <div class="text-truncate" style="max-width: 130px;">
                                        <div class="fw-semibold small text-dark text-truncate">{{ $wh->supervisor->name }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">{{ $wh->supervisor->role?->display_name }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="small text-muted fst-italic">{{ __('warehouses.unassigned') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="small font-monospace">{{ $wh->phone ?: '—' }}</span>
                        </td>
                        <td>
                            @if($wh->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('warehouses.status_active_pill') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>{{ __('warehouses.status_inactive_pill') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('warehouses.edit', $wh) }}" class="btn btn-sm btn-light border" title="{{ __('warehouses.btn_edit') }}">
                                    <i class="bi bi-pencil text-dark"></i>
                                </a>
                                <form method="POST" action="{{ route('warehouses.toggle', $wh) }}" class="d-inline" onsubmit="return confirm('{{ __('warehouses.confirm_toggle') }}');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-light border text-{{ $wh->is_active ? 'danger' : 'success' }}" title="{{ $wh->is_active ? __('warehouses.btn_deactivate') : __('warehouses.btn_activate') }}">
                                        <i class="bi bi-{{ $wh->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state py-5 text-center">
                                <div class="avatar avatar-lg bg-light text-muted mx-auto mb-2" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                    <i class="bi bi-house-door fs-3"></i>
                                </div>
                                <h6 class="fw-bold mb-1">{{ __('warehouses.empty_title') }}</h6>
                                <p class="small text-muted mb-0">{{ __('warehouses.empty_desc') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile First Cards Feed (< 768px) --}}
    <div class="d-md-none p-2">
        @forelse($warehouses as $wh)
            @php
                $stock = (float) ($stocks[$wh->id] ?? 0);
                $occ = (float) $wh->capacity_quintals > 0 ? min(100, round(($stock / (float) $wh->capacity_quintals) * 100, 1)) : 0;
                $progressColor = $occ > 85 ? 'bg-danger' : ($occ > 60 ? 'bg-warning' : 'bg-success');
            @endphp
            <div class="card shadow-sm border rounded-3 mb-3 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-1">
                                <i class="bi bi-house-door text-success"></i>
                                <span>{{ $wh->name }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <code class="small text-primary font-monospace">{{ $wh->code }}</code>
                                @if($wh->phone)
                                    <span class="small text-muted font-monospace"><i class="bi bi-telephone me-1"></i>{{ $wh->phone }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($wh->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ __('warehouses.status_active_pill') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    {{ __('warehouses.status_inactive_pill') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($wh->address)
                        <p class="small text-muted mb-2 text-truncate">{{ $wh->address }}</p>
                    @endif

                    {{-- Stock & Capacity Progress Box --}}
                    <div class="p-2 rounded-2 bg-light border mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">{{ __('warehouses.col_capacity') }}</small>
                                <strong class="text-dark font-monospace">{{ number_format((float) $wh->capacity_quintals, 1) }} Qtl</strong>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">{{ __('warehouses.kpi_current_stock') }}</small>
                                <strong class="text-success font-monospace">{{ number_format($stock, 1) }} Qtl</strong>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ max($occ, 2) }}%;" aria-valuenow="{{ $occ }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="text-end mt-1">
                            <span class="badge bg-white text-dark border font-monospace" style="font-size: 0.68rem;">
                                Occupancy: {{ $occ }}%
                            </span>
                        </div>
                    </div>

                    {{-- Supervisor & Actions --}}
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div class="small text-muted">
                            @if($wh->supervisor)
                                <i class="bi bi-person-badge text-secondary me-1"></i>{{ $wh->supervisor->name }}
                            @else
                                <span class="fst-italic">{{ __('warehouses.unassigned') }}</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('warehouses.edit', $wh) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-pencil"></i>
                                <span>{{ __('warehouses.btn_edit') }}</span>
                            </a>
                            <form method="POST" action="{{ route('warehouses.toggle', $wh) }}" class="d-inline" onsubmit="return confirm('{{ __('warehouses.confirm_toggle') }}');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-light border text-{{ $wh->is_active ? 'danger' : 'success' }}">
                                    <i class="bi bi-{{ $wh->is_active ? 'eye-slash' : 'eye' }}"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state py-5 text-center bg-white rounded-3">
                <i class="bi bi-house-door fs-1 text-muted mb-2 d-block"></i>
                <h6 class="fw-bold mb-1">{{ __('warehouses.empty_title') }}</h6>
                <p class="small text-muted mb-0">{{ __('warehouses.empty_desc') }}</p>
            </div>
        @endforelse
    </div>

    @if($warehouses->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $warehouses->links() }}
        </div>
    @endif
</div>
@endsection
