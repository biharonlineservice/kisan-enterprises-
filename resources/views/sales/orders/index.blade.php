@extends('layouts.app')

@section('title', __('sales.orders.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'customer_id', 'status', 'from', 'to', 'tab']);
    $activeTab = $activeTab ?? request('tab', 'all');
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xxl-row align-items-xxl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                @if($kpis['confirmedOrders'] > 0)
                    <span class="erp-hero-chip" style="background: rgba(34, 197, 94, .2); border-color: rgba(34, 197, 94, .4); color: #86efac; white-space: nowrap;">
                        <i class="bi bi-file-earmark-check-fill text-success"></i>
                        <strong>{{ __('sales.orders.banner_confirmed', ['count' => $kpis['confirmedOrders']]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip" style="background: rgba(245, 158, 11, .2); border-color: rgba(245, 158, 11, .4); color: #fde047; white-space: nowrap;">
                    <i class="bi bi-box-seam text-warning"></i>
                    <strong>{{ __('sales.orders.banner_volume', ['qtl' => number_format($kpis['totalQtlBooked'], 1)]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('sales.orders.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 820px;">
                {{ __('sales.orders.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('sales-orders.export', request()->all()) }}" class="erp-hero-btn">
                <i class="bi bi-download"></i>
                <span>{{ __('app.export') }}</span>
            </a>
            <a href="{{ route('customers.index') }}" class="erp-hero-btn">
                <i class="bi bi-buildings"></i>
                <span>{{ __('sales.customers.title') }}</span>
            </a>
            <a href="{{ route('sales-invoices.index') }}" class="erp-hero-btn">
                <i class="bi bi-receipt"></i>
                <span>{{ __('sales.invoices.title') }}</span>
            </a>
            @can('sales.create')
                <a href="{{ route('sales-orders.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('sales.orders.add_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Booked Volume --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('sales-orders.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.orders.kpi_total_volume') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-box-seam-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($kpis['totalQtlBooked'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span></div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.orders.kpi_volume_sub') }} &bull; {{ $kpis['confirmedOrders'] + $kpis['dispatchedOrders'] }} Active
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Active Contract Value --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('sales-orders.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.orders.kpi_total_value') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-currency-rupee"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ money($kpis['totalOrderValue']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.orders.kpi_value_sub') }} &bull; Booked Sum
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Pending Fulfillment --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('sales-orders.index', ['tab' => 'confirmed']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.orders.kpi_pending_fulfillment') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi {{ $kpis['pendingFulfillmentQtl'] > 0 ? 'bi-hourglass-split' : 'bi-check-circle' }}"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($kpis['pendingFulfillmentQtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span></div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.orders.kpi_pending_sub', ['qtl' => number_format($kpis['pendingFulfillmentQtl'], 1)]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Contracted Buyers --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customers.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.orders.kpi_active_buyers') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ $kpis['activeBuyersCount'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.orders.kpi_buyers_sub', ['mills' => $kpis['riceMillsCount'], 'traders' => $kpis['tradersCount']]) }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Filter Pill Tabs Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            @php
                $tabs = [
                    'all' => ['label' => __('sales.orders.tab_all'), 'count' => $tabCounts['all'], 'icon' => 'bi-collection'],
                    'confirmed' => ['label' => __('sales.orders.tab_confirmed'), 'count' => $tabCounts['confirmed'], 'icon' => 'bi-check-circle'],
                    'dispatched' => ['label' => __('sales.orders.tab_dispatched'), 'count' => $tabCounts['dispatched'], 'icon' => 'bi-truck'],
                    'completed' => ['label' => __('sales.orders.tab_completed'), 'count' => $tabCounts['completed'], 'icon' => 'bi-check2-all'],
                    'cancelled' => ['label' => __('sales.orders.tab_cancelled'), 'count' => $tabCounts['cancelled'], 'icon' => 'bi-x-circle'],
                ];
            @endphp
            @foreach($tabs as $key => $meta)
                <li class="nav-item">
                    <a href="{{ route('sales-orders.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
                       class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeTab === $key ? 'active' : '' }}">
                        <i class="bi {{ $meta['icon'] }}"></i>
                        <span>{{ $meta['label'] }}</span>
                        <span class="badge {{ $activeTab === $key ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ number_format($meta['count']) }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Trade Verified
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-clipboard-data me-1"></i>Sauda Books
            </span>
        </div>
    </div>
</div>

{{-- 4. Advanced Filter Bar & Listing Card --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('sales-orders.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            {{-- Live Search --}}
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('sales.orders.order_number') }}, {{ __('sales.orders.customer') }}...">
                </div>
            </div>

            {{-- Buyer / Mill Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.orders.customer') }}</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Order Status --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.orders.status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="confirmed" @selected(request('status') === 'confirmed')>{{ __('sales.orders.status_confirmed') }}</option>
                    <option value="dispatched" @selected(request('status') === 'dispatched')>{{ __('sales.orders.status_dispatched') }}</option>
                    <option value="completed" @selected(request('status') === 'completed')>{{ __('sales.orders.status_completed') }}</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('sales.orders.status_cancelled') }}</option>
                </select>
            </div>

            {{-- From Date --}}
            <div class="col-6 col-md-1">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.from') ?? 'From' }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>

            {{-- To Date --}}
            <div class="col-6 col-md-1">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.to') ?? 'To' }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>

            {{-- Action Buttons --}}
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                </button>
                @if($hasFilters)
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

    {{-- 5. Desktop Table View (>= 768px) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small">
                <tr>
                    <th style="min-width: 170px;">{{ __('sales.orders.col_order') }}</th>
                    <th style="min-width: 220px;">{{ __('sales.orders.col_customer') }}</th>
                    <th style="min-width: 200px;">{{ __('sales.orders.col_commodities') }}</th>
                    <th class="num">{{ __('sales.orders.col_volume') }}</th>
                    <th class="num">{{ __('sales.orders.col_value') }}</th>
                    <th class="text-center">{{ __('sales.orders.col_status') }}</th>
                    <th class="text-end" style="min-width: 140px;">{{ __('sales.orders.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td>
                            <a href="{{ route('sales-orders.show', $o) }}" class="fw-bold text-dark text-decoration-none font-monospace">
                                {{ $o->order_number }}
                            </a>
                            <div class="small text-muted">
                                <i class="bi bi-calendar3 me-1"></i>{{ fmt_date($o->order_date) }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-success-subtle text-success-emphasis border border-success-subtle" style="width: 34px; height: 34px; font-size: 0.85rem; font-weight: 700;">
                                    @if($o->customer?->customer_type === 'rice_mill')
                                        <i class="bi bi-buildings"></i>
                                    @else
                                        {{ strtoupper(substr($o->customer?->name ?? 'C', 0, 1)) }}
                                    @endif
                                </div>
                                <div style="min-width: 0;">
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 200px;" title="{{ $o->customer?->name }}">
                                        {{ $o->customer?->name ?? '—' }}
                                    </div>
                                    <div class="d-flex align-items-center gap-1 small text-muted">
                                        <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">{{ $o->customer?->type_name }}</span>
                                        @if($o->customer?->city)
                                            <span>&bull; {{ $o->customer->city }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @foreach($o->items->take(2) as $item)
                                <div class="small">
                                    <strong>{{ $item->crop?->name }}</strong>
                                    @if($item->cropVariety)
                                        <span class="text-muted">({{ $item->cropVariety->name }})</span>
                                    @endif
                                    <span class="text-muted">&bull; {{ $item->warehouse?->name }}</span>
                                </div>
                            @endforeach
                            @if($o->items->count() > 2)
                                <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">+{{ $o->items->count() - 2 }} more</span>
                            @endif
                        </td>
                        <td class="num">
                            <span class="fw-bold font-monospace">{{ bc_round($o->total_qtl, 3) }}</span>
                            <span class="small text-muted">Qtl</span>
                        </td>
                        <td class="num">
                            <span class="fw-bold text-success font-monospace">{{ money($o->total_amount) }}</span>
                        </td>
                        <td class="text-center">
                            @if($o->status === 'confirmed')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('sales.orders.status_confirmed') }}
                                </span>
                            @elseif($o->status === 'dispatched')
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                    <i class="bi bi-truck me-1"></i>{{ __('sales.orders.status_dispatched') }}
                                </span>
                            @elseif($o->status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check2-all me-1"></i>{{ __('sales.orders.status_completed') }}
                                </span>
                            @elseif($o->status === 'cancelled')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('sales.orders.status_cancelled') }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ ucfirst($o->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('sales-orders.show', $o) }}" class="btn btn-sm btn-light" title="{{ __('sales.orders.view_details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales-orders.print', $o) }}" class="btn btn-sm btn-light" title="{{ __('sales.orders.print_slip') }}" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @can('sales.create')
                                    @if(in_array($o->status, ['confirmed', 'dispatched']))
                                        <a href="{{ route('sales-invoices.create', ['order_id' => $o->id]) }}" class="btn btn-sm btn-success" title="{{ __('sales.orders.generate_invoice') }}">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state py-5 text-center">
                                <i class="bi bi-file-earmark-spreadsheet fs-1 text-muted mb-2"></i>
                                <h6 class="fw-semibold text-dark">{{ __('sales.orders.empty_orders') }}</h6>
                                <p class="small text-muted mb-3">{{ __('sales.orders.subtitle') }}</p>
                                @can('sales.create')
                                    <a href="{{ route('sales-orders.create') }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus-circle me-1"></i> {{ __('sales.orders.add_new') }}
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 6. Mobile App Card Feed View (< 768px) --}}
    <div class="d-md-none mobile-card-list p-2">
        @forelse($orders as $o)
            <div class="mobile-record-card mb-2 border rounded-3 p-3 bg-white shadow-sm">
                <div class="mobile-card-header d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="mobile-card-code fw-bold text-dark font-monospace">{{ $o->order_number }}</span>
                        <div class="small text-muted">{{ fmt_date($o->order_date) }}</div>
                    </div>
                    <div>
                        @if($o->status === 'confirmed')
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Confirmed</span>
                        @elseif($o->status === 'dispatched')
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Dispatched</span>
                        @elseif($o->status === 'completed')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('sales.orders.status_completed') }}</span>
                        @elseif($o->status === 'cancelled')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ __('sales.orders.status_cancelled') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($o->status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mobile-card-body border-top border-bottom py-2 my-2">
                    <div class="fw-bold text-dark fs-6 mb-1">{{ $o->customer?->name }}</div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span class="badge bg-light text-dark border small">{{ $o->customer?->type_name }}</span>
                        @if($o->customer?->city)
                            <span class="badge bg-light text-muted border small"><i class="bi bi-geo-alt"></i> {{ $o->customer->city }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">{{ __('sales.orders.col_volume') }}</span>
                        <span class="small fw-bold font-monospace">{{ bc_round($o->total_qtl, 3) }} Qtl</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                        <span class="small fw-semibold text-dark">{{ __('sales.orders.col_value') }}</span>
                        <span class="fw-bold text-success fs-6 font-monospace">{{ money($o->total_amount) }}</span>
                    </div>
                </div>

                <div class="mobile-card-footer d-flex align-items-center justify-content-between pt-1">
                    <div class="d-flex align-items-center gap-1">
                        @if($o->customer?->mobile)
                            <a href="tel:{{ $o->customer->mobile }}" class="btn btn-sm btn-outline-success py-1 px-2" title="{{ __('sales.customers.call_now') }}">
                                <i class="bi bi-telephone-fill"></i>
                            </a>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $o->customer->mobile) }}" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2" title="{{ __('sales.customers.whatsapp') }}">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('sales-orders.show', $o) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> {{ __('app.view') }}
                        </a>
                        @can('sales.create')
                            @if(in_array($o->status, ['confirmed', 'dispatched']))
                                <a href="{{ route('sales-invoices.create', ['order_id' => $o->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-receipt"></i> Bill
                                </a>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">
                <i class="bi bi-file-earmark-spreadsheet fs-1 d-block mb-2 text-secondary"></i>
                <h6 class="fw-semibold text-dark">{{ __('sales.orders.empty_orders') }}</h6>
                <p class="small text-muted mb-3">{{ __('sales.orders.subtitle') }}</p>
                @can('sales.create')
                    <a href="{{ route('sales-orders.create') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('sales.orders.add_new') }}
                    </a>
                @endcan
            </div>
        @endforelse
    </div>

    @if($orders->hasPages())
        <div class="pagination-wrap p-3 border-top">
            {{ $orders->links() }}
        </div>
    @endif
</div>

{{-- 7. Mobile Floating Action Button (FAB) --}}
@can('sales.create')
    <a href="{{ route('sales-orders.create') }}" class="mobile-fab" aria-label="{{ __('sales.orders.add_new') }}">
        <i class="bi bi-plus-circle fs-5"></i>
        <span>{{ __('sales.orders.add_new') }}</span>
    </a>
@endcan
@endsection
