@extends('layouts.app')

@section('title', __('sales.invoices.title'))

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
                @if($kpis['finalizedCount'] > 0)
                    <span class="erp-hero-chip" style="background: rgba(34, 197, 94, .2); border-color: rgba(34, 197, 94, .4); color: #86efac; white-space: nowrap;">
                        <i class="bi bi-file-earmark-check-fill text-success"></i>
                        <strong>{{ __('sales.invoices.banner_finalized', ['finalized' => $kpis['finalizedCount'], 'total' => $kpis['totalInvoicesCount']]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip" style="background: rgba(245, 158, 11, .2); border-color: rgba(245, 158, 11, .4); color: #fde047; white-space: nowrap;">
                    <i class="bi bi-truck text-warning"></i>
                    <strong>{{ __('sales.invoices.banner_volume', ['volume' => number_format((float) $kpis['totalDispatchedQtl'], 1)]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('sales.invoices.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 820px;">
                {{ __('sales.invoices.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('sales-invoices.export', request()->all()) }}" class="erp-hero-btn">
                <i class="bi bi-download"></i>
                <span>{{ __('sales.invoices.export_csv') }}</span>
            </a>
            <a href="{{ route('sales-orders.index') }}" class="erp-hero-btn">
                <i class="bi bi-journal-text"></i>
                <span>{{ __('sales.invoices.orders_shortcut') }}</span>
            </a>
            <a href="{{ route('customers.index') }}" class="erp-hero-btn">
                <i class="bi bi-buildings"></i>
                <span>{{ __('sales.customers.title') }}</span>
            </a>
            @can('sales.create')
                <a href="{{ route('sales-invoices.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('sales.invoices.add_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Invoiced Sales --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('sales-invoices.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.invoices.kpi_total_invoiced') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-cash-stack"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ money($kpis['totalInvoiced']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.invoices.kpi_invoiced_sub', ['count' => $kpis['finalizedCount']]) }} &bull; Finalized
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Outstanding Receivables --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('sales-invoices.index', ['tab' => 'unpaid']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #D94848;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.invoices.kpi_total_due') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEE2E2; color: #DC2626;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #DC2626;">{{ money($kpis['totalDue']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.invoices.kpi_due_sub', ['count' => $kpis['unpaidCount']]) }} &bull; Pending
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Dispatched Grain Volume --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('sales-invoices.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.invoices.kpi_dispatched_volume') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-truck"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format((float) $kpis['totalDispatchedQtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span></div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.invoices.kpi_volume_sub') }} &bull; Outward
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Billed Buyer Network --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customers.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.invoices.kpi_active_buyers') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-buildings-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ $kpis['activeBuyersCount'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.invoices.kpi_buyers_sub') }} &bull; Buyers
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
                    'all' => ['label' => __('sales.invoices.tab_all'), 'count' => $tabCounts['all'], 'icon' => 'bi-collection'],
                    'finalized' => ['label' => __('sales.invoices.tab_finalized'), 'count' => $tabCounts['finalized'], 'icon' => 'bi-patch-check-fill'],
                    'delivered' => ['label' => __('sales.invoices.tab_delivered'), 'count' => $tabCounts['delivered'] ?? 0, 'icon' => 'bi-check2-circle'],
                    'in_transit' => ['label' => __('sales.invoices.tab_in_transit'), 'count' => $tabCounts['in_transit'] ?? 0, 'icon' => 'bi-truck'],
                    'unpaid' => ['label' => __('sales.invoices.tab_unpaid'), 'count' => $tabCounts['unpaid'], 'icon' => 'bi-exclamation-circle-fill'],
                    'paid' => ['label' => __('sales.invoices.tab_paid'), 'count' => $tabCounts['paid'], 'icon' => 'bi-check2-all'],
                    'draft' => ['label' => __('sales.invoices.tab_draft'), 'count' => $tabCounts['draft'], 'icon' => 'bi-pencil-square'],
                    'reversed' => ['label' => __('sales.invoices.tab_reversed'), 'count' => $tabCounts['reversed'], 'icon' => 'bi-arrow-counterclockwise'],
                ];
            @endphp
            @foreach($tabs as $key => $meta)
                <li class="nav-item">
                    <a href="{{ route('sales-invoices.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
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
                <i class="bi bi-shield-check me-1"></i>GST Registered
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-truck me-1"></i>Mandi Dispatches
            </span>
        </div>
    </div>
</div>

{{-- 4. Advanced Filter Bar & Listing Card --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('sales-invoices.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            {{-- Live Search --}}
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('sales.invoices.invoice_number') }}, {{ __('sales.invoices.customer') }}, {{ __('sales.invoices.vehicle_number') }}...">
                </div>
            </div>

            {{-- Buyer / Mill Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.invoices.customer') }}</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Invoice Status Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.invoices.col_status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="finalized" @selected(request('status') === 'finalized')>{{ __('sales.invoices.tab_finalized') }}</option>
                    <option value="draft" @selected(request('status') === 'draft')>{{ __('sales.invoices.tab_draft') }}</option>
                    <option value="reversed" @selected(request('status') === 'reversed')>{{ __('sales.invoices.tab_reversed') }}</option>
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
                    <a href="{{ route('sales-invoices.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Desktop Invoices Table View (>= 768px) --}}
<div class="card d-none d-md-block mb-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 140px;">{{ __('sales.invoices.col_invoice') }}</th>
                    <th style="min-width: 200px;">{{ __('sales.invoices.col_customer') }}</th>
                    <th style="min-width: 200px;">{{ __('sales.invoices.col_items') }}</th>
                    <th style="min-width: 160px;">{{ __('sales.invoices.col_dispatch') }}</th>
                    <th class="text-end" style="min-width: 120px;">{{ __('sales.invoices.col_total') }}</th>
                    <th class="text-end" style="min-width: 110px;">{{ __('sales.invoices.col_paid') }}</th>
                    <th class="text-end" style="min-width: 120px;">{{ __('sales.invoices.col_due') }}</th>
                    <th class="text-center" style="min-width: 120px;">{{ __('sales.invoices.col_status') }}</th>
                    <th class="text-end" style="min-width: 130px;">{{ __('sales.invoices.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        {{-- Invoice # & Date --}}
                        <td>
                            <a href="{{ route('sales-invoices.show', $inv) }}" class="fw-bold text-decoration-none text-success">
                                {{ $inv->invoice_number }}
                            </a>
                            <div class="text-muted small">
                                <i class="bi bi-calendar3"></i> {{ fmt_date($inv->invoice_date) }}
                            </div>
                            @if($inv->creator)
                                <div class="text-muted fs-xs">
                                    <i class="bi bi-person"></i> {{ $inv->creator->name }}
                                </div>
                            @endif
                        </td>

                        {{-- Customer / Buyer --}}
                        <td>
                            <div class="fw-semibold text-dark">
                                <a href="{{ route('customers.show', $inv->customer) }}" class="text-dark text-decoration-none hover-underline">
                                    {{ $inv->customer->name }}
                                </a>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-1.5 mt-0.5">
                                <span class="badge bg-light text-secondary border fs-xs">
                                    {{ $inv->customer->customer_type_label }}
                                </span>
                                @if($inv->customer->city)
                                    <span class="text-muted fs-xs">
                                        <i class="bi bi-geo-alt"></i> {{ $inv->customer->city }}
                                    </span>
                                @endif
                            </div>
                            @if($inv->customer->phone || $inv->customer->mobile)
                                <div class="text-muted fs-xs mt-0.5 font-monospace">
                                    <i class="bi bi-telephone"></i> {{ $inv->customer->phone ?? $inv->customer->mobile }}
                                </div>
                            @endif
                        </td>

                        {{-- Commodities & Warehouses --}}
                        <td>
                            @foreach($inv->items as $item)
                                <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                                    <span class="fw-medium text-dark small">
                                        {{ $item->crop->name ?? 'Crop' }}
                                        @if($item->cropVariety)
                                            <span class="text-muted fs-xs">({{ $item->cropVariety->name }})</span>
                                        @endif
                                    </span>
                                    <span class="badge bg-success-subtle text-success font-monospace">
                                        {{ number_format((float) $item->quantity_qtl, 3) }} Qtl
                                    </span>
                                </div>
                                <div class="text-muted fs-xs">
                                    <i class="bi bi-house-door"></i> {{ $item->warehouse->name ?? 'Warehouse' }}
                                    @if($item->rate_per_qtl > 0)
                                        <span class="ms-1 font-monospace">@ {{ money($item->rate_per_qtl) }}/Qtl</span>
                                    @endif
                                </div>
                            @endforeach
                        </td>

                        {{-- Vehicle & Logistics --}}
                        <td>
                            @if($inv->vehicle_number)
                                <div class="fw-semibold text-dark font-monospace small">
                                    <i class="bi bi-truck text-primary"></i> {{ $inv->vehicle_number }}
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif

                            @if($inv->eway_bill_number)
                                <div class="text-muted fs-xs mt-0.5">
                                    <span class="badge bg-light text-dark border">EWB: {{ $inv->eway_bill_number }}</span>
                                </div>
                            @endif

                            @if($inv->delivery_destination)
                                <div class="text-muted fs-xs text-truncate mt-0.5" style="max-width: 180px;" title="{{ $inv->delivery_destination }}">
                                    <i class="bi bi-geo-alt-fill text-danger-emphasis"></i> {{ $inv->delivery_destination }}
                                </div>
                            @endif
                        </td>

                        {{-- Total Amount --}}
                        <td class="text-end">
                            <span class="font-monospace fw-bold fs-6 text-dark">{{ money($inv->total_amount) }}</span>
                            @if(bccomp($inv->tax_amount, '0', 2) > 0)
                                <div class="text-muted fs-xs">GST: {{ money($inv->tax_amount) }}</div>
                            @endif
                        </td>

                        {{-- Paid Amount --}}
                        <td class="text-end">
                            <span class="font-monospace fw-semibold text-success">{{ money($inv->paid_amount) }}</span>
                            @if($inv->receipts->count() > 0)
                                <div class="text-muted fs-xs">{{ $inv->receipts->count() }} receipt(s)</div>
                            @endif
                        </td>

                        {{-- Balance Due --}}
                        <td class="text-end">
                            @if(bccomp($inv->balance_due, '0', 2) > 0)
                                <span class="font-monospace fw-bold text-danger">{{ money($inv->balance_due) }}</span>
                                <div class="badge bg-danger-subtle text-danger border border-danger-subtle fs-xs">Pending</div>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle-fill"></i> {{ __('accounts.paid') }}
                                </span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if($inv->status === 'finalized')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-patch-check-fill me-1"></i>Dispatched
                                </span>
                                @if($inv->isDelivered())
                                    <div class="mt-1">
                                        <span class="badge bg-success text-white px-2 py-0.5 rounded-pill fs-xs shadow-xs" title="{{ __('sales.invoices.status_delivered') }} ({{ fmt_datetime($inv->delivered_at) }})">
                                            <i class="bi bi-check2-all me-1"></i>{{ __('sales.invoices.status_delivered') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-1">
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0.5 rounded-pill fs-xs">
                                            <i class="bi bi-truck me-1"></i>{{ __('sales.invoices.status_in_transit') }}
                                        </span>
                                    </div>
                                @endif
                            @elseif($inv->status === 'draft')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-pencil-square me-1"></i>{{ __('sales.invoices.save_draft') }}
                                </span>
                            @elseif($inv->status === 'reversed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i>{{ __('sales.invoices.reverse') }}
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sales-invoices.show', $inv) }}" class="btn btn-outline-secondary" title="{{ __('app.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales-invoices.challan', $inv) }}" target="_blank" class="btn btn-outline-primary" title="{{ __('sales.invoices.transport_challan') }}">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <a href="{{ route('sales-invoices.print', $inv) }}" target="_blank" class="btn btn-outline-secondary" title="{{ __('sales.invoices.print') }}">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <div class="empty-state">
                                <div class="empty-state-icon text-muted mb-2">
                                    <i class="bi bi-receipt fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-dark">{{ __('sales.invoices.empty_invoices') }}</h6>
                                <p class="text-muted small mb-3">{{ __('app.no_records') }}</p>
                                @can('sales.create')
                                    <a href="{{ route('sales-invoices.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle"></i> {{ __('sales.invoices.add_new') }}
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 6. Mobile App Card Feed View (< 768px, 390px Optimized) --}}
<div class="d-md-none mobile-card-list p-2 mb-4">
    @forelse($invoices as $inv)
        <div class="mobile-record-card mb-3 border rounded-3 p-3 bg-white shadow-sm">
            {{-- Card Header --}}
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <a href="{{ route('sales-invoices.show', $inv) }}" class="fw-bold text-success text-decoration-none font-monospace fs-6">
                        <i class="bi bi-receipt"></i> {{ $inv->invoice_number }}
                    </a>
                    <div class="text-muted fs-xs mt-0.5">
                        <i class="bi bi-calendar3"></i> {{ fmt_date($inv->invoice_date) }}
                    </div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    @if($inv->status === 'finalized')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fs-xs">
                            <i class="bi bi-check-circle-fill"></i> Dispatched
                        </span>
                        @if($inv->isDelivered())
                            <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fs-xs">
                                <i class="bi bi-check2-all"></i> {{ __('sales.invoices.status_delivered') }}
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-0.5 fs-xs">
                                <i class="bi bi-truck"></i> {{ __('sales.invoices.status_in_transit') }}
                            </span>
                        @endif
                    @elseif($inv->status === 'draft')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fs-xs">
                            <i class="bi bi-pencil"></i> Draft
                        </span>
                    @elseif($inv->status === 'reversed')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fs-xs">
                            <i class="bi bi-x-circle"></i> Cancelled
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Body (Vertical Stack) --}}
            <div class="py-2 my-2 border-top border-bottom">
                {{-- Buyer Profile --}}
                <div class="mb-2">
                    <div class="fw-bold text-dark fs-6">{{ $inv->customer->name }}</div>
                    <div class="d-flex flex-wrap align-items-center gap-1.5 mt-0.5">
                        <span class="badge bg-light text-secondary border fs-xs">{{ $inv->customer->customer_type_label }}</span>
                        @if($inv->customer->city)
                            <span class="text-muted fs-xs"><i class="bi bi-geo-alt"></i> {{ $inv->customer->city }}</span>
                        @endif
                    </div>
                </div>

                {{-- Dispatched Commodities Summary --}}
                <div class="bg-light p-2.5 rounded-3 mb-2">
                    @foreach($inv->items as $item)
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-semibold text-dark">
                                {{ $item->crop->name ?? 'Crop' }}
                                @if($item->cropVariety)
                                    <span class="text-muted fs-xs">({{ $item->cropVariety->name }})</span>
                                @endif
                            </span>
                            <span class="badge bg-success text-white font-monospace">
                                {{ number_format((float) $item->quantity_qtl, 2) }} Qtl
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted fs-xs">
                            <span><i class="bi bi-house-door"></i> {{ $item->warehouse->name ?? 'Warehouse' }}</span>
                            @if($item->rate_per_qtl > 0)
                                <span class="font-monospace">@ {{ money($item->rate_per_qtl) }}/Qtl</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Vehicle & Logistics --}}
                @if($inv->vehicle_number || $inv->eway_bill_number || $inv->delivery_destination)
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2 text-muted fs-xs">
                        @if($inv->vehicle_number)
                            <span class="badge bg-secondary-subtle text-dark border font-monospace">
                                <i class="bi bi-truck text-primary"></i> {{ $inv->vehicle_number }}
                            </span>
                        @endif
                        @if($inv->eway_bill_number)
                            <span class="badge bg-light text-dark border">
                                EWB: {{ $inv->eway_bill_number }}
                            </span>
                        @endif
                        @if($inv->delivery_destination)
                            <span class="text-truncate" style="max-width: 180px;" title="{{ $inv->delivery_destination }}">
                                <i class="bi bi-geo-alt"></i> {{ $inv->delivery_destination }}
                            </span>
                        @endif
                    </div>
                @endif

                {{-- Financials Summary --}}
                <div class="pt-1">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">{{ __('sales.invoices.col_total') }}:</span>
                        <span class="font-monospace fw-bold text-dark fs-6">{{ money($inv->total_amount) }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">{{ __('sales.invoices.col_paid') }}:</span>
                        <span class="font-monospace text-success fw-semibold">{{ money($inv->paid_amount) }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small fw-semibold">{{ __('sales.invoices.col_due') }}:</span>
                        @if(bccomp($inv->balance_due, '0', 2) > 0)
                            <span class="font-monospace fw-bold text-danger">{{ money($inv->balance_due) }}</span>
                        @else
                            <span class="badge bg-success-subtle text-success fs-xs">{{ __('accounts.paid') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card Footer Actions --}}
            <div class="d-flex align-items-center justify-content-between pt-1">
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $inv->customer->phone ?? $inv->customer->mobile ?? '');
                @endphp
                <div class="d-flex align-items-center gap-1">
                    @if(!empty($cleanPhone))
                        <a href="tel:{{ $cleanPhone }}" class="btn btn-sm btn-outline-success py-1 px-2" title="{{ __('sales.customers.call_now') }}">
                            <i class="bi bi-telephone-fill"></i>
                        </a>
                        <a href="https://wa.me/91{{ $cleanPhone }}" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2" title="{{ __('sales.customers.whatsapp') }}">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-1.5">
                    <a href="{{ route('sales-invoices.show', $inv) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye"></i> {{ __('app.view') }}
                    </a>
                    <a href="{{ route('sales-invoices.challan', $inv) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-truck"></i> Challan
                    </a>
                    <a href="{{ route('sales-invoices.print', $inv) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-printer"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="card p-4 text-center text-muted">
            <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
            <h6 class="fw-bold">{{ __('sales.invoices.empty_invoices') }}</h6>
            <p class="small text-muted mb-0">{{ __('app.no_records') }}</p>
        </div>
    @endforelse
</div>

{{-- 7. Pagination --}}
@if($invoices->hasPages())
    <div class="card shadow-sm p-3 bg-white mb-4">
        {{ $invoices->links() }}
    </div>
@endif

{{-- 8. Mobile Floating Action Button (FAB) --}}
@can('sales.create')
    <a href="{{ route('sales-invoices.create') }}" class="mobile-fab" aria-label="{{ __('sales.invoices.add_new') }}">
        <i class="bi bi-plus-lg fs-5"></i>
        <span>{{ __('sales.invoices.add_new') }}</span>
    </a>
@endcan
@endsection
