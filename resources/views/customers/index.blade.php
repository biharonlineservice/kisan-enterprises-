@extends('layouts.app')

@section('title', __('sales.customers.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'type', 'city', 'status', 'has_due', 'tab', 'sort']);
    $activeTab = $activeTab ?? request('tab', 'all');
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xxl-row align-items-xxl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="background: rgba(34, 197, 94, .2); border-color: rgba(34, 197, 94, .4); color: #86efac; white-space: nowrap;">
                    <i class="bi bi-person-check-fill text-success"></i>
                    <strong>{{ __('sales.customers.banner_active', ['active' => $kpis['activeCustomers'], 'total' => $kpis['totalCustomers']]) }}</strong>
                </span>
                @if($kpis['riceMillsCount'] > 0)
                    <span class="erp-hero-chip" style="background: rgba(245, 158, 11, .2); border-color: rgba(245, 158, 11, .4); color: #fde047; white-space: nowrap;">
                        <i class="bi bi-buildings text-warning"></i>
                        <strong>{{ __('sales.customers.banner_mills', ['count' => $kpis['riceMillsCount']]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('sales.customers.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 820px;">
                {{ __('sales.customers.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('customers.export', request()->all()) }}" class="erp-hero-btn">
                <i class="bi bi-download"></i>
                <span>{{ __('app.export') }}</span>
            </a>
            @can('sales.create')
                @if(\Illuminate\Support\Facades\Route::has('sales-invoices.create'))
                    <a href="{{ route('sales-invoices.create') }}" class="erp-hero-btn">
                        <i class="bi bi-receipt"></i>
                        <span>{{ __('sales.customers.create_invoice') }}</span>
                    </a>
                @endif
            @endcan
            @can('accounts.manage')
                @if(\Illuminate\Support\Facades\Route::has('customer-receipts.create'))
                    <a href="{{ route('customer-receipts.create') }}" class="erp-hero-btn">
                        <i class="bi bi-cash-stack"></i>
                        <span>{{ __('sales.customers.record_receipt') }}</span>
                    </a>
                @endif
            @endcan
            @can('customers.manage')
                <a href="{{ route('customers.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-building-add"></i>
                    <span>{{ __('sales.customers.add_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Receivables --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customers.index', ['tab' => 'has_due']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #D94848;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.customers.kpi_total_receivables') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEE2E2; color: #DC2626;">
                        <i class="bi {{ $kpis['totalReceivables'] > 0 ? 'bi-exclamation-circle' : 'bi-check-circle' }}"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #DC2626;">{{ money($kpis['totalReceivables']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.customers.kpi_receivables_sub') }} &bull; {{ $kpis['hasDueCount'] }} Dues
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Active Buyer Network --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customers.index', ['tab' => 'active']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.customers.kpi_active_buyers') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ $kpis['activeCustomers'] }} <span class="fs-6 fw-normal text-muted">/ {{ $kpis['totalCustomers'] }}</span></div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.customers.kpi_active_sub', ['mills' => $kpis['riceMillsCount'], 'dealers' => $kpis['tradersCount'] + $kpis['wholesalersCount']]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Total Credit Exposure --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customers.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.customers.kpi_credit_exposure') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-shield-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ money($kpis['totalCreditLimit']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.customers.kpi_credit_sub') }} &bull; Limits
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Connected Mandis & Cities --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customers.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.customers.kpi_cities') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ $kpis['citiesCount'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.customers.kpi_cities_sub', ['count' => $kpis['citiesCount']]) }} &bull; Hubs
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
                    'all' => ['label' => __('sales.customers.tab_all'), 'count' => $tabCounts['all'], 'icon' => 'bi-collection'],
                    'active' => ['label' => __('sales.customers.tab_active'), 'count' => $tabCounts['active'], 'icon' => 'bi-check2-circle'],
                    'rice_mills' => ['label' => __('sales.customers.tab_rice_mills'), 'count' => $tabCounts['rice_mills'], 'icon' => 'bi-buildings'],
                    'wholesalers' => ['label' => __('sales.customers.tab_wholesalers'), 'count' => $tabCounts['wholesalers'], 'icon' => 'bi-boxes'],
                    'traders' => ['label' => __('sales.customers.tab_traders'), 'count' => $tabCounts['traders'], 'icon' => 'bi-briefcase'],
                    'has_due' => ['label' => __('sales.customers.tab_has_due'), 'count' => $tabCounts['has_due'], 'icon' => 'bi-cash-coin'],
                    'inactive' => ['label' => __('sales.customers.tab_inactive'), 'count' => $tabCounts['inactive'], 'icon' => 'bi-pause-circle'],
                ];
            @endphp
            @foreach($tabs as $key => $meta)
                <li class="nav-item">
                    <a href="{{ route('customers.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
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
                <i class="bi bi-shield-check me-1"></i>KYC Verified
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-buildings me-1"></i>Trade Registry
            </span>
        </div>
    </div>
</div>

{{-- 4. Advanced Filter Bar & Listing Card --}}
<div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
    <div class="p-3 bg-light-subtle border-bottom erp-filter-collapsible">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            {{-- Live Search --}}
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('sales.customers.search_placeholder') }}">
                </div>
            </div>

            {{-- Customer Category --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.customers.col_type') }}</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($types as $typeKey => $typeName)
                        <option value="{{ $typeKey }}" {{ request('type') === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mandi / City --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.customers.city') }}</label>
                <select name="city" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($cities as $cCity)
                        <option value="{{ $cCity }}" {{ request('city') === $cCity ? 'selected' : '' }}>{{ $cCity }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
                </select>
            </div>

            {{-- Sorting --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.sort') ?? 'Sort By' }}</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="name_asc" {{ request('sort', 'name_asc') === 'name_asc' ? 'selected' : '' }}>Firm Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Firm Name (Z-A)</option>
                    <option value="id_asc" {{ request('sort') === 'id_asc' ? 'selected' : '' }}>Customer ID (Asc)</option>
                    <option value="id_desc" {{ request('sort') === 'id_desc' ? 'selected' : '' }}>Customer ID (Desc)</option>
                    <option value="balance_desc" {{ request('sort') === 'balance_desc' ? 'selected' : '' }}>Highest Balance Due</option>
                    <option value="limit_desc" {{ request('sort') === 'limit_desc' ? 'selected' : '' }}>Highest Credit Limit</option>
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="col-12 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                </button>
                @if($hasFilters)
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 5. Desktop Table View (>= 768px) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small">
                <tr>
                    <th style="min-width: 240px;">{{ __('sales.customers.col_customer') }}</th>
                    <th>{{ __('sales.customers.col_type') }}</th>
                    <th style="min-width: 180px;">{{ __('sales.customers.col_contact') }}</th>
                    <th>{{ __('sales.customers.col_location') }}</th>
                    <th class="num">{{ __('sales.customers.col_credit_limit') }}</th>
                    <th class="num" style="min-width: 160px;">{{ __('sales.customers.col_balance') }}</th>
                    <th>{{ __('sales.customers.col_status') }}</th>
                    <th class="text-end" style="min-width: 130px;">{{ __('sales.customers.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    @php
                        $invoicesDue = (float) ($c->invoices_balance_due ?? 0);
                        $openingBal = (float) ($c->opening_balance ?? 0);
                        $netDue = $openingBal + $invoicesDue;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-success-subtle text-success-emphasis border border-success-subtle" style="width: 38px; height: 38px; font-size: 0.95rem; font-weight: 700;">
                                    @if($c->customer_type === 'rice_mill')
                                        <i class="bi bi-buildings"></i>
                                    @elseif($c->customer_type === 'wholesaler')
                                        <i class="bi bi-boxes"></i>
                                    @else
                                        {{ strtoupper(substr($c->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div style="min-width: 0;">
                                    <a href="{{ route('customers.show', $c) }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="max-width: 220px;" title="{{ $c->name }}">
                                        {{ $c->name }}
                                    </a>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <code class="small text-muted">{{ $c->customer_id }}</code>
                                        @if($c->gstin)
                                            <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">GST: {{ $c->gstin }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $c->type_name }}</span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark">{{ $c->contact_person ?: '—' }}</div>
                            @if($c->mobile)
                                <div class="small text-muted d-flex align-items-center gap-1">
                                    <a href="tel:{{ $c->mobile }}" class="text-decoration-none text-muted" title="{{ __('sales.customers.call_now') }}">
                                        <i class="bi bi-telephone text-success"></i> {{ $c->formatted_mobile ?? $c->mobile }}
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="small fw-medium">{{ $c->city ?: '—' }}</div>
                            @if($c->state)
                                <small class="text-muted">{{ $c->state }}</small>
                            @endif
                        </td>
                        <td class="num">
                            <span class="small fw-semibold">{{ money($c->credit_limit) }}</span>
                            @if($c->payment_terms_days > 0)
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $c->payment_terms_days }} days</div>
                            @else
                                <div class="text-muted" style="font-size: 0.72rem;">Cash &amp; Carry</div>
                            @endif
                        </td>
                        <td class="num">
                            <span class="fw-bold fs-6 {{ $netDue > 0 ? 'text-danger' : ($netDue < 0 ? 'text-success' : 'text-muted') }}">
                                {{ money($netDue) }}
                            </span>
                            @if(isset($c->unpaid_invoices_count) && $c->unpaid_invoices_count > 0)
                                <div style="font-size: 0.72rem;">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1">
                                        {{ $c->unpaid_invoices_count }} unpaid {{ Str::plural('bill', $c->unpaid_invoices_count) }}
                                    </span>
                                </div>
                            @elseif($openingBal != 0 && $invoicesDue == 0)
                                <div class="text-muted" style="font-size: 0.72rem;">Opening balance</div>
                            @endif
                        </td>
                        <td>
                            @if($c->is_active)
                                <span class="badge-status badge-success">{{ __('app.active') }}</span>
                            @else
                                <span class="badge-status badge-danger">{{ __('app.inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('customers.show', $c) }}" class="btn btn-sm btn-light" title="{{ __('app.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('customers.print', $c) }}" class="btn btn-sm btn-light" title="{{ __('app.print') }}" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @can('customers.manage')
                                    <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-light" title="{{ __('app.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('customers.toggle', $c) }}" class="d-inline" onsubmit="return confirm('Change status for {{ $c->name }}?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-light text-{{ $c->is_active ? 'danger' : 'success' }}" title="{{ $c->is_active ? __('app.inactive') : __('app.active') }}">
                                            <i class="bi bi-{{ $c->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state py-5 text-center">
                                <i class="bi bi-buildings fs-1 text-muted mb-2"></i>
                                <h6 class="fw-semibold text-dark">{{ __('sales.customers.no_customers') }}</h6>
                                <p class="small text-muted mb-3">{{ __('sales.customers.subtitle') }}</p>
                                @can('customers.manage')
                                    <a href="{{ route('customers.create') }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus-circle me-1"></i> {{ __('sales.customers.add_new') }}
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
        @forelse($customers as $c)
            @php
                $invoicesDue = (float) ($c->invoices_balance_due ?? 0);
                $openingBal = (float) ($c->opening_balance ?? 0);
                $netDue = $openingBal + $invoicesDue;
            @endphp
            <div class="mobile-record-card mb-2 border rounded-3 p-3 bg-white shadow-sm">
                <div class="mobile-card-header d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="avatar bg-success-subtle text-success-emphasis border border-success-subtle flex-shrink-0" style="width: 38px; height: 38px; font-size: 0.95rem; font-weight: 700;">
                            @if($c->customer_type === 'rice_mill')
                                <i class="bi bi-buildings"></i>
                            @elseif($c->customer_type === 'wholesaler')
                                <i class="bi bi-boxes"></i>
                            @else
                                {{ strtoupper(substr($c->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="overflow-hidden">
                            <a href="{{ route('customers.show', $c) }}" class="mobile-card-title text-decoration-none fw-bold text-dark d-block text-truncate">
                                {{ $c->name }}
                            </a>
                            <div class="mobile-card-meta small text-muted">
                                <code>{{ $c->customer_id }}</code>
                                @if($c->city)
                                    &bull; <span>{{ $c->city }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($c->is_active)
                            <span class="badge-status badge-success">{{ __('app.active') }}</span>
                        @else
                            <span class="badge-status badge-danger">{{ __('app.inactive') }}</span>
                        @endif
                    </div>
                </div>

                <div class="mobile-card-body border-top border-bottom py-2 my-2 d-flex flex-column gap-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">{{ __('sales.customers.col_type') }}</span>
                        <span class="badge bg-light text-dark border">{{ $c->type_name }}</span>
                    </div>

                    @if($c->contact_person)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">{{ __('sales.customers.col_contact') }}</span>
                            <span class="small fw-semibold text-dark text-truncate ms-2" style="max-width: 60%;">{{ $c->contact_person }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">{{ __('sales.customers.col_credit_limit') }}</span>
                        <span class="small fw-semibold font-monospace">{{ money($c->credit_limit) }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                        <span class="small fw-semibold text-dark">{{ __('sales.customers.col_balance') }}</span>
                        <span class="fw-bold fs-6 font-monospace {{ $netDue > 0 ? 'text-danger' : ($netDue < 0 ? 'text-success' : 'text-muted') }}">
                            {{ money($netDue) }}
                        </span>
                    </div>
                </div>

                <div class="mobile-card-footer d-flex align-items-center justify-content-between pt-1">
                    <div class="d-flex align-items-center gap-1">
                        @if($c->mobile)
                            <a href="tel:{{ $c->mobile }}" class="btn btn-sm btn-outline-success py-1 px-2" title="{{ __('sales.customers.call_now') }}">
                                <i class="bi bi-telephone-fill"></i>
                            </a>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $c->mobile) }}" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2" title="{{ __('sales.customers.whatsapp') }}">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        @endif
                        @if($c->gstin)
                            <span class="badge bg-light text-muted border small" style="font-size: 0.65rem;">GST: {{ $c->gstin }}</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('customers.show', $c) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> {{ __('app.view') }}
                        </a>
                        @can('customers.manage')
                            <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-light border">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">
                <i class="bi bi-buildings fs-1 d-block mb-2 text-secondary"></i>
                <h6 class="fw-semibold text-dark">{{ __('sales.customers.no_customers') }}</h6>
                <p class="small text-muted mb-3">{{ __('sales.customers.subtitle') }}</p>
                @can('customers.manage')
                    <a href="{{ route('customers.create') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('sales.customers.add_new') }}
                    </a>
                @endcan
            </div>
        @endforelse
    </div>

    @if($customers->hasPages())
        <div class="pagination-wrap p-3 border-top">
            {{ $customers->links() }}
        </div>
    @endif
</div>

{{-- 7. Mobile Floating Action Button (FAB) --}}
@can('customers.manage')
    <a href="{{ route('customers.create') }}" class="mobile-fab" aria-label="{{ __('sales.customers.add_new') }}">
        <i class="bi bi-building-add fs-5"></i>
        <span>{{ __('sales.customers.add_new') }}</span>
    </a>
@endcan
@endsection
