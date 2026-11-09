@extends('layouts.app')

@section('title', __('procurement.purchases.title'))

@section('content')
@php
    $pricedCrops = $crops->filter(fn ($c) => $c->current_purchase_rate !== null && (float) $c->current_purchase_rate > 0);
    $hasFilters = request()->anyFilled(['q', 'crop_id', 'warehouse_id', 'status', 'payment', 'from', 'to']);
@endphp

{{-- 1. Executive Weighbridge & Procurement Command Banner --}}
<div class="erp-hero-banner">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-speedometer2 text-warning"></i>
                    <strong>{{ __('procurement.purchases.today_chip', ['qtl' => bc_round((string) $summary['today_qtl'], 2), 'count' => $summary['today_count']]) }}</strong>
                </span>
                @if($summary['unpaid_count'] > 0)
                    <span class="erp-hero-chip">
                        <i class="bi bi-cash-coin text-warning"></i>
                        <strong>{{ __('procurement.purchases.unpaid_chip', ['count' => $summary['unpaid_count']]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('procurement.purchases.title') }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                {{ __('procurement.purchases.banner_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 flex-shrink-0">
            @can('reports.view')
                <a href="{{ route('reports.purchases') }}" class="erp-hero-btn">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>{{ __('procurement.purchases.reports_btn') }}</span>
                </a>
            @endcan
            @can('farmer_payments.create')
                <a href="{{ route('farmer-payments.create') }}" class="erp-hero-btn">
                    <i class="bi bi-cash-stack"></i>
                    <span>{{ __('procurement.purchases.pay_farmer_btn') }}</span>
                </a>
            @endcan
            @can('purchases.create')
                <a href="{{ route('purchases.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>{{ __('procurement.purchases.add_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #21864B;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.kpi_procured_vol') }}</span>
                <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                    <i class="bi bi-basket2-fill"></i>
                </span>
            </div>
            <div class="kpi-value text-dark text-truncate">
                {{ bc_round((string) $summary['finalized_qtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span>
            </div>
            <div class="kpi-sub text-truncate">
                {{ __('procurement.purchases.kpi_slips_sub', ['count' => number_format($summary['finalized_count'])]) }}
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #0284C7;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.kpi_gross_value') }}</span>
                <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                    <i class="bi bi-currency-rupee"></i>
                </span>
            </div>
            <div class="kpi-value text-truncate" style="color: #0284C7;">
                {{ money($summary['finalized_value']) }}
            </div>
            <div class="kpi-sub text-truncate">
                {{ __('procurement.purchases.kpi_today_sub', ['amount' => money($summary['today_value'])]) }}
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #D94848;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.kpi_unpaid_dues') }}</span>
                <span class="kpi-badge-icon" style="background: #FBECEC; color: #D94848;">
                    <i class="bi bi-wallet2"></i>
                </span>
            </div>
            <div class="kpi-value text-danger text-truncate">
                {{ money($summary['unpaid_due']) }}
            </div>
            <div class="kpi-sub text-truncate">
                {{ __('procurement.purchases.kpi_unpaid_sub', ['count' => number_format($summary['unpaid_count'])]) }}
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #D97706;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.purchases.kpi_draft_pending') }}</span>
                <span class="kpi-badge-icon" style="background: #FEF3C7; color: #B45309;">
                    <i class="bi bi-hourglass-split"></i>
                </span>
            </div>
            <div class="kpi-value text-dark">
                {{ number_format($summary['draft_count']) }}
            </div>
            <div class="kpi-sub text-truncate">
                {{ __('procurement.purchases.kpi_reversed_sub', ['count' => number_format($summary['reversed_count'])]) }}
            </div>
        </div>
    </div>
</div>

{{-- 3. Live Mandi Buying Rates Strip --}}
@if($pricedCrops->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 12px; border: 1px solid #DCEBE1 !important;">
        <div class="card-body py-2 px-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="small fw-bold text-success d-inline-flex align-items-center gap-1.5">
                    <span style="width:8px;height:8px;border-radius:50%;background:#16A34A;display:inline-block;box-shadow:0 0 0 3px rgba(22,163,74,.2);"></span>
                    {{ __('procurement.purchases.live_rates_strip') }}
                </span>
                @foreach($pricedCrops as $c)
                    <span class="badge bg-light text-dark border fw-semibold px-2.5 py-1.5 fs-xs">
                        {{ $c->name }}: <span class="text-success fw-bold">{{ $c->formattedPurchaseRate() }}</span>
                    </span>
                @endforeach
            </div>
            @can('masters.manage')
                <a href="{{ route('crops.index') }}" class="small fw-semibold text-success text-decoration-none text-nowrap">
                    <i class="bi bi-sliders me-1"></i>{{ __('procurement.purchases.update_rates_link') }}
                </a>
            @endcan
        </div>
    </div>
@endif

{{-- 4. Segment Filter Pill Tabs Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1 px-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0; scroll-padding-left: 6px;">
            <li class="nav-item">
                <a href="{{ route('purchases.index', request()->except(['status', 'payment', 'page'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ !request()->filled('status') && !request()->filled('payment') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i>
                    <span>{{ __('procurement.purchases.tab_all') }}</span>
                    <span class="badge {{ !request()->filled('status') && !request()->filled('payment') ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $summary['total_count'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('purchases.index', array_merge(request()->except(['payment', 'page']), ['status' => 'finalized'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ request('status') === 'finalized' && !request()->filled('payment') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle text-success"></i>
                    <span>{{ __('procurement.purchases.tab_finalized') }}</span>
                    <span class="badge {{ request('status') === 'finalized' && !request()->filled('payment') ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $summary['finalized_count'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('purchases.index', array_merge(request()->except(['status', 'page']), ['payment' => 'due'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ request('payment') === 'due' ? 'active' : '' }}">
                    <i class="bi bi-exclamation-circle text-danger"></i>
                    <span>{{ __('procurement.purchases.tab_unpaid') }}</span>
                    <span class="badge {{ request('payment') === 'due' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $summary['unpaid_count'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('purchases.index', array_merge(request()->except(['payment', 'page']), ['status' => 'draft'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ request('status') === 'draft' ? 'active' : '' }}">
                    <i class="bi bi-pencil-square text-warning"></i>
                    <span>{{ __('procurement.purchases.tab_drafts') }}</span>
                    <span class="badge {{ request('status') === 'draft' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $summary['draft_count'] }}
                    </span>
                </a>
            </li>
            @if($summary['reversed_count'] > 0 || request('status') === 'reversed')
                <li class="nav-item">
                    <a href="{{ route('purchases.index', array_merge(request()->except(['payment', 'page']), ['status' => 'reversed'])) }}"
                       class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ request('status') === 'reversed' ? 'active' : '' }}">
                        <i class="bi bi-arrow-counterclockwise text-secondary"></i>
                        <span>{{ __('procurement.purchases.tab_reversed') }}</span>
                        <span class="badge {{ request('status') === 'reversed' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $summary['reversed_count'] }}
                        </span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Weighbridge Certified
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-basket2 me-1"></i>Direct Inward
            </span>
        </div>
    </div>
</div>

{{-- 5. Search Filters & Purchases Ledger Card --}}
<div class="card shadow-sm border-0 mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom">
        <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-journal-text text-success"></i>
            <span>{{ __('procurement.purchases.title') }}</span>
        </h3>
        <div class="d-flex align-items-center gap-2">
            @if($hasFilters)
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-danger py-0 px-2 fs-xs d-inline-flex align-items-center gap-1" title="{{ __('app.reset') }}">
                    <i class="bi bi-x-circle"></i> {{ __('app.reset') }}
                </a>
            @endif
            <span class="badge bg-light text-dark border font-monospace">
                {{ $purchases->total() }} {{ __('procurement.purchases.title') }}
            </span>
        </div>
    </div>

    {{-- Search & Filter Form --}}
    <div class="p-3 bg-light bg-opacity-50 border-bottom erp-filter-collapsible">
        <form method="GET" action="{{ route('purchases.index') }}" data-autosubmit>
            {{-- Row 1: Search (6 cols), Crop (3 cols), Destination Warehouse (3 cols) --}}
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
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.purchases.crop') }}</label>
                    <select name="crop_id" class="form-select form-select-sm">
                        <option value="">{{ __('app.all') }}</option>
                        @foreach($crops as $crop)
                            <option value="{{ $crop->id }}" @selected(request('crop_id') == $crop->id)>{{ $crop->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.purchases.warehouse') }}</label>
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">{{ __('app.all') }}</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 2: Payment (3 cols), Status (3 cols), From Date (2 cols), To Date (2 cols), Actions (2 cols) --}}
            <div class="row g-2 align-items-end mt-1">
                <div class="col-6 col-md-3 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.purchases.payment_filter') }}</label>
                    <select name="payment" class="form-select form-select-sm">
                        <option value="">{{ __('app.all') }}</option>
                        <option value="due" @selected(request('payment') === 'due')>{{ __('procurement.purchases.payment_due') }}</option>
                        <option value="paid" @selected(request('payment') === 'paid')>{{ __('procurement.purchases.payment_paid') }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ __('app.all') }}</option>
                        <option value="finalized" @selected(request('status') === 'finalized')>{{ __('procurement.purchases.status_finalized') }}</option>
                        <option value="draft" @selected(request('status') === 'draft')>{{ __('procurement.purchases.status_draft') }}</option>
                        <option value="reversed" @selected(request('status') === 'reversed')>{{ __('procurement.purchases.status_reversed') }}</option>
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
                            <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-light border px-2.5 text-nowrap" title="{{ __('app.reset') }}">
                                <i class="bi bi-x-circle text-danger"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Desktop Purchase Records Table (>= 768px) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover table-compact-erp align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">{{ __('procurement.purchases.col_receipt') }}</th>
                    <th>{{ __('procurement.purchases.farmer') }}</th>
                    <th>{{ __('procurement.purchases.col_crop_grade') }}</th>
                    <th>{{ __('procurement.purchases.warehouse') }}</th>
                    <th class="num">{{ __('procurement.purchases.col_net_wt') }}</th>
                    <th class="num">{{ __('procurement.purchases.col_rate') }}</th>
                    <th class="num">{{ __('procurement.purchases.col_payable') }}</th>
                    <th class="num">{{ __('procurement.purchases.col_balance') }}</th>
                    <th class="text-end pe-3">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                    @php
                        $hasDue = $p->isFinalized() && bccomp((string) $p->balance_due, '0', 2) > 0;
                        $daysElapsed = $p->purchase_date ? (int) max(0, \Illuminate\Support\Carbon::parse($p->purchase_date)->startOfDay()->diffInDays(now()->startOfDay(), false)) : 0;
                        $isDirectWh = $p->warehouse && $p->warehouse->code === 'WH-DIRECT';
                    @endphp
                    <tr class="{{ $p->isReversed() ? 'opacity-75' : '' }}">
                        <td class="ps-3 text-nowrap">
                            <a href="{{ route('purchases.show', $p) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $p->purchase_number }}
                            </a>
                            <div class="small text-muted">
                                {{ fmt_date($p->purchase_date) }}
                                @if($p->vehicle_number)
                                    &bull; <span class="text-dark"><i class="bi bi-truck"></i> {{ $p->vehicle_number }}</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <a href="{{ route('farmers.show', $p->farmer) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $p->farmer->name }}
                            </a>
                            <div class="small text-muted">
                                <code>{{ $p->farmer->farmer_id }}</code>
                                @if($p->farmer->village)
                                    &bull; {{ $p->farmer->village }}
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold text-dark">{{ $p->crop->name }}</div>
                            <div class="small text-muted">
                                {{ $p->cropVariety?->name ?? 'Standard' }}
                                @if($p->quality_grade)
                                    &bull; <span class="text-secondary">{{ $p->quality_grade }}</span>
                                @endif
                                @if($p->moisture_pct)
                                    &bull; {{ bc_round((string) $p->moisture_pct, 1) }}%
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($isDirectWh)
                                <span class="badge bg-info-subtle text-primary border border-info-subtle">
                                    <i class="bi bi-truck"></i> {{ __('procurement.purchases.direct_transit_badge') }}
                                </span>
                            @else
                                <span class="badge bg-light text-dark border">{{ $p->warehouse->name }}</span>
                            @endif
                        </td>

                        <td class="num">
                            <div class="fw-bold text-dark">{{ bc_round((string) $p->net_weight_qtl, 3) }} <span class="fw-normal text-muted small">Qtl</span></div>
                            <small class="text-muted">{{ bc_round((string) $p->net_weight_kg, 1) }} Kg</small>
                        </td>

                        <td class="num">
                            <span class="fw-medium">{{ money($p->rate_per_qtl) }}</span>
                        </td>

                        <td class="num">
                            <div class="fw-bold text-dark">{{ money($p->net_payable_amount) }}</div>
                            @if($p->isFinalized() && bccomp((string) $p->paid_amount, '0', 2) > 0 && $hasDue)
                                <small class="text-success">{{ __('accounts.paid') }}: {{ money($p->paid_amount) }}</small>
                            @endif
                        </td>

                        <td class="num">
                            @if($p->isReversed())
                                <span class="badge-status badge-danger">{{ __('procurement.purchases.status_reversed') }}</span>
                            @elseif($p->isDraft())
                                <span class="badge-status badge-warning">{{ __('procurement.purchases.status_draft') }}</span>
                                <small class="d-block text-muted">{{ money($p->net_payable_amount) }}</small>
                            @elseif($hasDue)
                                <div class="fw-bold text-danger">{{ money($p->balance_due) }}</div>
                                @if($daysElapsed > 0)
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size: .66rem;">
                                        <i class="bi bi-clock"></i> {{ __('procurement.purchases.days_ago', ['days' => $daysElapsed]) }}
                                    </span>
                                @else
                                    <span class="badge-status badge-success" style="font-size: .68rem;">{{ __('procurement.purchases.status_finalized') }}</span>
                                @endif
                            @else
                                <span class="badge-status badge-success">{{ __('accounts.paid') }}</span>
                            @endif
                        </td>

                        <td class="text-end pe-3 text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                @if($hasDue)
                                    @can('farmer_payments.create')
                                        <a href="{{ route('farmer-payments.create', ['purchase_id' => $p->id]) }}" class="btn btn-xs btn-success" title="{{ __('procurement.purchases.pay_farmer_btn') }}">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                    @endcan
                                @endif

                                @if($p->isDraft())
                                    @can('purchases.create')
                                        <a href="{{ route('purchases.edit', $p) }}" class="btn btn-xs btn-outline-primary" title="{{ __('app.edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                @else
                                    <a href="{{ route('purchases.receipt', $p) }}" target="_blank" class="btn btn-xs btn-light border" title="{{ __('procurement.purchases.print_parchi') }}">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="{{ route('purchases.pdf', $p) }}" class="btn btn-xs btn-light border text-danger" title="{{ __('procurement.purchases.download_pdf') }}">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                @endif

                                <a href="{{ route('purchases.show', $p) }}" class="btn btn-xs btn-light border" title="{{ __('app.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state py-5">
                                <i class="bi bi-basket2"></i>
                                <h6 class="fw-semibold">{{ __('app.no_records') }}</h6>
                                @can('purchases.create')
                                    <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-plus-circle"></i> {{ __('procurement.purchases.add_new') }}
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile App Card Feed View (< 768px) --}}
    <div class="d-md-none mobile-card-list p-3">
        @forelse($purchases as $p)
            @php
                $hasDue = $p->isFinalized() && bccomp((string) $p->balance_due, '0', 2) > 0;
                $daysElapsed = $p->purchase_date ? (int) max(0, \Illuminate\Support\Carbon::parse($p->purchase_date)->startOfDay()->diffInDays(now()->startOfDay(), false)) : 0;
                $cleanMobile = preg_replace('/[^0-9]/', '', $p->farmer->mobile ?? '');
            @endphp
            <div class="mobile-record-card mb-3 border rounded-3 p-3 bg-white shadow-sm">
                {{-- 1. Card Header: Monospace voucher number and status badge --}}
                <div class="d-flex align-items-start justify-content-between gap-2 pb-2 mb-2 border-bottom">
                    <div>
                        <a href="{{ route('purchases.show', $p) }}" class="font-monospace fw-bold text-success text-decoration-none fs-6 d-block">
                            <i class="bi bi-receipt me-1"></i>{{ $p->purchase_number }}
                        </a>
                        <div class="text-muted fs-xs mt-0.5">
                            <i class="bi bi-calendar3 me-1"></i>{{ fmt_date($p->purchase_date) }}
                            @if($hasDue && $daysElapsed > 0)
                                &bull; <span class="text-warning-emphasis fw-semibold">{{ __('procurement.purchases.days_ago', ['days' => $daysElapsed]) }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($p->isFinalized())
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-xs fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i>{{ __('procurement.purchases.status_finalized') }}
                            </span>
                        @elseif($p->isDraft())
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1 fs-xs fw-semibold">
                                <i class="bi bi-pencil-square me-1"></i>{{ __('procurement.purchases.status_draft') }}
                            </span>
                        @elseif($p->isReversed())
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-xs fw-semibold">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('procurement.purchases.status_reversed') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- 2. Farmer Profile & Direct Contact --}}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <div class="fw-bold text-dark fs-6">{{ $p->farmer->name }}</div>
                        <div class="text-muted fs-xs mt-0.5">
                            <span class="badge bg-light text-secondary border font-monospace">{{ $p->farmer->farmer_id }}</span>
                            @if($p->farmer->village)
                                <span class="ms-1"><i class="bi bi-geo-alt"></i> {{ $p->farmer->village }}</span>
                            @endif
                        </div>
                    </div>
                    @if(!empty($cleanMobile))
                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                            <a href="tel:{{ $cleanMobile }}" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center" title="{{ __('sales.customers.call_now') }}">
                                <i class="bi bi-telephone-fill"></i>
                            </a>
                            <a href="https://wa.me/91{{ $cleanMobile }}" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center" title="{{ __('sales.customers.whatsapp') }}">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- 3. Commodity & Weighbridge Summary Box --}}
                <div class="bg-light p-2.5 rounded-3 mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold text-dark">
                            <i class="bi bi-basket text-success me-1"></i>{{ $p->crop->name }}
                            @if($p->cropVariety)
                                <span class="text-muted fs-xs">({{ $p->cropVariety->name }})</span>
                            @endif
                        </span>
                        <span class="badge bg-success text-white font-monospace">
                            {{ bc_round((string) $p->net_weight_qtl, 3) }} Qtl
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-muted fs-xs mb-1">
                        <span><i class="bi bi-building me-1"></i>{{ $p->warehouse->name }}</span>
                        <span class="font-monospace fw-semibold text-dark">@ {{ money($p->rate_per_qtl) }}/Qtl</span>
                    </div>
                    @if($p->vehicle_number || $p->quality_grade || (float) $p->gross_weight_kg > 0)
                        <div class="d-flex flex-wrap align-items-center gap-1.5 pt-1 border-top border-light-subtle fs-xs text-muted">
                            @if($p->vehicle_number)
                                <span class="badge bg-white text-dark border font-monospace">
                                    <i class="bi bi-truck text-primary me-1"></i>{{ $p->vehicle_number }}
                                </span>
                            @endif
                            @if($p->quality_grade)
                                <span class="badge bg-white text-dark border">
                                    <i class="bi bi-award text-warning me-1"></i>{{ $p->quality_grade }}
                                </span>
                            @endif
                            @if((float) $p->gross_weight_kg > 0)
                                <span class="text-muted">
                                    {{ number_format((float) $p->gross_weight_kg) }} Grs - {{ number_format((float) $p->tare_weight_kg) }} Tare kg
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- 4. Financial Summary --}}
                <div class="bg-light rounded-2 p-2.5 border my-2">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <span class="text-muted small fw-medium">{{ __('procurement.purchases.col_payable') }}:</span>
                        <span class="font-monospace fw-bold text-dark fs-6">{{ money($p->net_payable_amount) }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-1 border-top border-light-subtle">
                        <span class="text-muted small fw-semibold">{{ __('procurement.purchases.col_due') }}:</span>
                        @if($p->isReversed())
                            <span class="badge bg-danger-subtle text-danger fs-xs">{{ __('procurement.purchases.status_reversed') }}</span>
                        @elseif($p->isDraft())
                            <span class="badge bg-warning-subtle text-dark fs-xs">{{ __('procurement.purchases.status_draft') }}</span>
                        @elseif($hasDue)
                            <span class="font-monospace fw-bold text-danger">{{ money($p->balance_due) }}</span>
                        @else
                            <span class="badge bg-success-subtle text-success fs-xs">{{ __('accounts.paid') }}</span>
                        @endif
                    </div>
                </div>

                {{-- 5. Primary Action (if pending due or draft) --}}
                @if($hasDue)
                    @can('farmer_payments.create')
                        <a href="{{ route('farmer-payments.create', ['purchase_id' => $p->id]) }}" class="btn btn-success btn-sm w-100 py-1.5 mb-2 d-flex align-items-center justify-content-center gap-1.5 fw-semibold shadow-sm text-nowrap">
                            <i class="bi bi-cash-coin fs-6"></i>
                            <span>{{ __('procurement.purchases.pay_farmer_btn') }} ({{ money($p->balance_due) }})</span>
                        </a>
                    @endcan
                @elseif($p->isDraft())
                    @can('purchases.create')
                        <a href="{{ route('purchases.edit', $p) }}" class="btn btn-primary btn-sm w-100 py-1.5 mb-2 d-flex align-items-center justify-content-center gap-1.5 fw-semibold shadow-sm text-nowrap">
                            <i class="bi bi-pencil fs-6"></i>
                            <span>{{ __('app.edit') }} {{ __('procurement.purchases.title') }}</span>
                        </a>
                    @endcan
                @endif

                {{-- 6. Secondary Utilities Row --}}
                <div class="d-flex align-items-center gap-1.5 pt-2 border-top">
                    <a href="{{ route('purchases.show', $p) }}" class="btn btn-outline-secondary btn-sm flex-grow-1 text-center py-1">
                        <i class="bi bi-eye me-1"></i>{{ __('app.view') }}
                    </a>
                    @if(!$p->isDraft())
                        <a href="{{ route('purchases.receipt', $p) }}" target="_blank" class="btn btn-light border btn-sm px-2.5 py-1 text-dark" title="{{ __('procurement.purchases.print_parchi') }}">
                            <i class="bi bi-printer me-1"></i><span class="fs-xs fw-semibold">Parchi</span>
                        </a>
                        <a href="{{ route('purchases.pdf', $p) }}" class="btn btn-light border btn-sm px-2.5 py-1 text-danger" title="PDF">
                            <i class="bi bi-file-earmark-pdf me-1"></i><span class="fs-xs fw-semibold">PDF</span>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-muted">
                <i class="bi bi-basket2 fs-1 d-block mb-2 text-secondary"></i>
                {{ __('app.no_records') }}
            </div>
        @endforelse
    </div>

    @if($purchases->hasPages())
        <div class="pagination-wrap">
            {{ $purchases->links() }}
        </div>
    @endif
</div>

{{-- Mobile Floating Action Button (FAB) --}}
@can('purchases.create')
    <a href="{{ route('purchases.create') }}" class="mobile-fab" aria-label="{{ __('procurement.purchases.add_new') }}">
        <i class="bi bi-plus-lg fs-5"></i>
        <span>{{ __('procurement.purchases.add_new') }}</span>
    </a>
@endcan
@endsection
