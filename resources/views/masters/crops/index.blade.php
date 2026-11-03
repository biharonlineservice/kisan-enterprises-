@extends('layouts.app')

@section('title', __('crops.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-flower1"></i>
                    <strong>{{ __('crops.banner_active_count', ['count' => $metrics['activeCrops']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-tags"></i>
                    {{ __('crops.banner_varieties_count', ['count' => $metrics['totalVarieties']]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-broadcast text-success"></i>
                    <strong>{{ __('crops.banner_live_rates_count', ['count' => $metrics['ratesLive']]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('crops.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('crops.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('crops.create') }}" class="btn btn-sm btn-light text-success fw-bold shadow-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-plus-circle-fill"></i>
                <span>{{ __('crops.btn_add_crop') }}</span>
            </a>
            @if(\Illuminate\Support\Facades\Route::has('website.admin.index'))
                <a href="{{ route('website.admin.index') }}" class="erp-hero-btn" title="{{ __('crops.btn_website_rates') }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>{{ __('crops.btn_website_rates') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('inventory.index'))
                <a href="{{ route('inventory.index') }}" class="erp-hero-btn" title="{{ __('crops.btn_stock_ledger') }}">
                    <i class="bi bi-boxes"></i>
                    <span>{{ __('crops.btn_stock_ledger') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Registered Crops --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('crops.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('crops.kpi_total_crops') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-flower1"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($metrics['totalCrops']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('crops.kpi_total_crops_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Active Commodities --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('crops.index', ['tab' => 'active']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('crops.kpi_active_crops') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-check2-circle"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format($metrics['activeCrops']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('crops.kpi_active_crops_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Crop Varieties --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('crops.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('crops.kpi_total_varieties') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-tags-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($metrics['totalVarieties']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('crops.kpi_total_varieties_sub', ['count' => $metrics['totalCrops']]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Website Live Rates --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('crops.index', ['tab' => 'rates_live']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('crops.kpi_live_rates') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-broadcast"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ number_format($metrics['ratesLive']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('crops.kpi_live_rates_sub') }}
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
                       href="{{ route('crops.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}">
                        <i class="bi bi-grid-fill"></i>
                        <span>{{ __('crops.tab_all') }}</span>
                        <span class="badge {{ $tab === 'all' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['totalCrops'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'active' ? 'active' : '' }}" 
                       href="{{ route('crops.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'active'])) }}">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>{{ __('crops.tab_active') }}</span>
                        <span class="badge {{ $tab === 'active' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['activeCrops'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'inactive' ? 'active' : '' }}" 
                       href="{{ route('crops.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'inactive'])) }}">
                        <i class="bi bi-pause-circle text-secondary"></i>
                        <span>{{ __('crops.tab_inactive') }}</span>
                        <span class="badge {{ $tab === 'inactive' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['inactiveCrops'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'rates_live' ? 'active' : '' }}" 
                       href="{{ route('crops.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'rates_live'])) }}">
                        <i class="bi bi-broadcast text-primary"></i>
                        <span>{{ __('crops.tab_rates_live') }}</span>
                        <span class="badge {{ $tab === 'rates_live' ? 'bg-primary text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['ratesLive'] }}
                        </span>
                    </a>
                </li>
            </ul>

            <div class="pb-1 pb-md-0 flex-shrink-0 pe-2">
                <a href="{{ route('crops.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-download"></i>
                    <span>{{ __('crops.btn_export_csv') }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card-body p-3 bg-light-subtle border-bottom">
        <form method="GET" action="{{ route('crops.index') }}" class="row g-2 align-items-end" data-autosubmit>
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('crops.search_placeholder') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('crops.search_placeholder') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('crops.filter_status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('crops.status_all') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('crops.status_active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('crops.status_inactive') }}</option>
                </select>
            </div>

            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('reports.apply_filter') ?? 'Filter' }}
                </button>
                @if(request()->anyFilled(['q', 'status']))
                    <a href="{{ route('crops.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('reports.clear_filter') ?? 'Clear' }}">
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
                    <th class="ps-3" style="min-width: 180px;">{{ __('crops.col_name') }}</th>
                    <th style="width: 110px;">{{ __('crops.col_code') }}</th>
                    <th style="width: 90px;">{{ __('crops.col_unit') }}</th>
                    <th style="min-width: 180px;">{{ __('crops.col_rate') }}</th>
                    <th style="width: 90px;">{{ __('crops.col_hsn') }}</th>
                    <th style="width: 120px;">{{ __('crops.col_varieties') }}</th>
                    <th style="width: 100px;">{{ __('crops.col_status') }}</th>
                    <th class="text-end pe-3" style="width: 120px;">{{ __('crops.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($crops as $crop)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('crops.show', $crop) }}" class="fw-bold text-dark text-decoration-none d-flex align-items-center gap-2">
                                <i class="bi bi-flower1 text-success"></i>
                                <span>{{ $crop->name }}</span>
                            </a>
                            @if($crop->description)
                                <div class="small text-muted text-truncate ps-4" style="max-width: 280px;">{{ $crop->description }}</div>
                            @endif
                        </td>
                        <td>
                            <code class="fw-bold text-primary font-monospace">{{ $crop->code }}</code>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border text-capitalize px-2 py-1">
                                {{ $crop->shortUnitLabel() }}
                            </span>
                        </td>
                        <td>
                            @if($crop->formattedPurchaseRate())
                                <div class="fw-bold text-success font-monospace">{{ $crop->formattedPurchaseRate() }}</div>
                                <div class="small text-muted d-flex align-items-center gap-1 mt-1">
                                    @if($crop->show_rate_on_website)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-1" style="font-size: 0.68rem;">
                                            <i class="bi bi-broadcast me-1"></i>{{ __('crops.badge_hero_live') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border py-0 px-1" style="font-size: 0.68rem;">
                                            {{ __('crops.badge_hidden') }}
                                        </span>
                                    @endif
                                    @if($crop->rate_note)
                                        <span class="text-truncate" style="max-width: 140px;" title="{{ $crop->rate_note }}">{{ $crop->rate_note }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="small text-muted fst-italic">{{ __('crops.rate_not_set') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-muted font-monospace">{{ $crop->hsn_code ?: '—' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('crops.show', $crop) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-2 py-1">
                                <i class="bi bi-tags me-1"></i>{{ $crop->varieties_count }} {{ __('crops.col_varieties') }}
                            </a>
                        </td>
                        <td>
                            @if($crop->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('crops.status_active_pill') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>{{ __('crops.status_inactive_pill') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('crops.show', $crop) }}" class="btn btn-sm btn-light border" title="{{ __('crops.btn_manage_varieties') }}">
                                    <i class="bi bi-list-check text-primary"></i>
                                </a>
                                <a href="{{ route('crops.edit', $crop) }}" class="btn btn-sm btn-light border" title="{{ __('crops.btn_edit_crop') }}">
                                    <i class="bi bi-pencil text-dark"></i>
                                </a>
                                <form method="POST" action="{{ route('crops.toggle', $crop) }}" class="d-inline" onsubmit="return confirm('{{ __('crops.confirm_toggle') }}');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-light border text-{{ $crop->is_active ? 'danger' : 'success' }}" title="{{ $crop->is_active ? __('crops.btn_deactivate') : __('crops.btn_activate') }}">
                                        <i class="bi bi-{{ $crop->is_active ? 'eye-slash' : 'eye' }}"></i>
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
                                    <i class="bi bi-flower1 fs-3"></i>
                                </div>
                                <h6 class="fw-bold mb-1">{{ __('crops.empty_title') }}</h6>
                                <p class="small text-muted mb-0">{{ __('crops.empty_desc') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile First Cards Feed (< 768px) --}}
    <div class="d-md-none p-2">
        @forelse($crops as $crop)
            <div class="card shadow-sm border rounded-3 mb-3 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <a href="{{ route('crops.show', $crop) }}" class="fw-bold text-dark text-decoration-none fs-6 d-flex align-items-center gap-1">
                                <i class="bi bi-flower1 text-success"></i>
                                <span>{{ $crop->name }}</span>
                            </a>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <code class="small text-primary font-monospace">{{ $crop->code }}</code>
                                <span class="badge bg-light text-dark border">{{ $crop->shortUnitLabel() }}</span>
                                @if($crop->hsn_code)
                                    <span class="small text-muted font-monospace">HSN: {{ $crop->hsn_code }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($crop->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ __('crops.status_active_pill') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    {{ __('crops.status_inactive_pill') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Rate & Varieties Box --}}
                    <div class="p-2 rounded-2 bg-light border d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ __('crops.col_rate') }}</small>
                            @if($crop->formattedPurchaseRate())
                                <strong class="text-success font-monospace">{{ $crop->formattedPurchaseRate() }}</strong>
                                @if($crop->show_rate_on_website)
                                    <span class="badge bg-success text-white ms-1" style="font-size: 0.65rem;">Live</span>
                                @endif
                            @else
                                <span class="small text-muted fst-italic">{{ __('crops.rate_not_set') }}</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ __('crops.col_varieties') }}</small>
                            <a href="{{ route('crops.show', $crop) }}" class="badge bg-primary text-white text-decoration-none">
                                {{ $crop->varieties_count }} {{ __('crops.col_varieties') }}
                            </a>
                        </div>
                    </div>

                    @if($crop->description)
                        <p class="small text-muted mb-2 text-truncate">{{ $crop->description }}</p>
                    @endif

                    {{-- Actions Toolbar --}}
                    <div class="d-flex justify-content-end gap-1 pt-2 border-top">
                        <a href="{{ route('crops.show', $crop) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-list-check"></i>
                            <span>{{ __('crops.btn_manage_varieties') }}</span>
                        </a>
                        <a href="{{ route('crops.edit', $crop) }}" class="btn btn-sm btn-light border" title="{{ __('crops.btn_edit_crop') }}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('crops.toggle', $crop) }}" class="d-inline" onsubmit="return confirm('{{ __('crops.confirm_toggle') }}');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-light border text-{{ $crop->is_active ? 'danger' : 'success' }}" title="{{ $crop->is_active ? __('crops.btn_deactivate') : __('crops.btn_activate') }}">
                                <i class="bi bi-{{ $crop->is_active ? 'eye-slash' : 'eye' }}"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state py-5 text-center bg-white rounded-3">
                <i class="bi bi-flower1 fs-1 text-muted mb-2 d-block"></i>
                <h6 class="fw-bold mb-1">{{ __('crops.empty_title') }}</h6>
                <p class="small text-muted mb-0">{{ __('crops.empty_desc') }}</p>
            </div>
        @endforelse
    </div>

    @if($crops->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $crops->links() }}
        </div>
    @endif
</div>
@endsection
