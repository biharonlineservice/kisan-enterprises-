@extends('layouts.app')

@section('title', __('sales.receipts.title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'customer_id', 'method', 'from', 'to', 'tab']);
    $activeTab = $activeTab ?? request('tab', 'all');
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xxl-row align-items-xxl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                @if($kpis['activeCount'] > 0)
                    <span class="erp-hero-chip" style="background: rgba(34, 197, 94, .2); border-color: rgba(34, 197, 94, .4); color: #86efac; white-space: nowrap;">
                        <i class="bi bi-file-earmark-check-fill text-success"></i>
                        <strong>{{ __('sales.receipts.banner_active', ['active' => $kpis['activeCount'], 'total' => $kpis['totalCount']]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip" style="background: rgba(245, 158, 11, .2); border-color: rgba(245, 158, 11, .4); color: #fde047; white-space: nowrap;">
                    <i class="bi bi-wallet2 text-warning"></i>
                    <strong>{{ __('sales.receipts.banner_amount', ['amount' => money($kpis['totalReceived'])]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('sales.receipts.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 820px;">
                {{ __('sales.receipts.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('customer-receipts.export', request()->all()) }}" class="erp-hero-btn">
                <i class="bi bi-download"></i>
                <span>{{ __('sales.receipts.export_csv') }}</span>
            </a>
            <a href="{{ route('sales-invoices.index') }}" class="erp-hero-btn">
                <i class="bi bi-receipt"></i>
                <span>{{ __('sales.receipts.invoices_shortcut') }}</span>
            </a>
            <a href="{{ route('customers.index') }}" class="erp-hero-btn">
                <i class="bi bi-buildings"></i>
                <span>{{ __('sales.receipts.customers_shortcut') }}</span>
            </a>
            @can('customer_receipts.create')
                <a href="{{ route('customer-receipts.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('sales.receipts.add_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Received Amount --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customer-receipts.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.receipts.kpi_total_received') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-cash-stack"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ money($kpis['totalReceived']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.receipts.kpi_received_sub', ['count' => $kpis['activeCount']]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Today's Collections --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customer-receipts.index', ['from' => today()->toDateString(), 'to' => today()->toDateString()]) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.receipts.kpi_today_received') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ money($kpis['todayReceived']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.receipts.kpi_today_sub') }} &bull; {{ $kpis['todayCount'] }} Today
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Bank & Digital Transfers --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customer-receipts.index', ['tab' => 'bank']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.receipts.kpi_digital_bank') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-bank"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ money(bcadd($kpis['bankReceived'], $kpis['upiReceived'], 2)) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.receipts.kpi_digital_sub', ['count' => $kpis['bankCount'] + $kpis['upiCount']]) }} &bull; NEFT/UPI
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Cash Counter Receipts --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('customer-receipts.index', ['tab' => 'cash']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('sales.receipts.kpi_cash_received') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-cash"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ money($kpis['cashReceived']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('sales.receipts.kpi_cash_sub', ['count' => $kpis['cashCount']]) }} &bull; Counter
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
                    'all' => ['label' => __('sales.receipts.tab_all'), 'count' => $tabCounts['all'], 'icon' => 'bi-collection'],
                    'active' => ['label' => __('sales.receipts.tab_active'), 'count' => $tabCounts['active'], 'icon' => 'bi-check-circle-fill'],
                    'bank' => ['label' => __('sales.receipts.tab_bank'), 'count' => $tabCounts['bank'], 'icon' => 'bi-bank2'],
                    'upi' => ['label' => __('sales.receipts.tab_upi'), 'count' => $tabCounts['upi'], 'icon' => 'bi-qr-code-scan'],
                    'cash' => ['label' => __('sales.receipts.tab_cash'), 'count' => $tabCounts['cash'], 'icon' => 'bi-cash'],
                    'reversed' => ['label' => __('sales.receipts.tab_reversed'), 'count' => $tabCounts['reversed'], 'icon' => 'bi-arrow-counterclockwise'],
                ];
            @endphp
            @foreach($tabs as $key => $meta)
                <li class="nav-item">
                    <a href="{{ route('customer-receipts.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
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
                <i class="bi bi-shield-check me-1"></i>Audit Reconciled
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-receipt me-1"></i>Inward Ledger
            </span>
        </div>
    </div>
</div>

{{-- 4. Advanced Filter Bar & Listing Card --}}
<div class="card border-0 shadow-sm mb-4 bg-white erp-filter-collapsible" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('customer-receipts.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            {{-- Live Search --}}
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('sales.receipts.receipt_number') }}, {{ __('sales.receipts.customer') }}, UTR, {{ __('sales.receipts.invoice') }}...">
                </div>
            </div>

            {{-- Buyer / Mill Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.receipts.customer') }}</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Payment Method Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('sales.receipts.payment_mode') }}</label>
                <select name="method" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($methods as $key => $lbl)
                        <option value="{{ $key }}" @selected(request('method') === $key)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            {{-- From Date --}}
            <div class="col-6 col-md-1">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.date') }} ({{ __('app.from') ?? 'From' }})</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>

            {{-- To Date --}}
            <div class="col-6 col-md-1">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.date') }} ({{ __('app.to') ?? 'To' }})</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>

            {{-- Action Buttons --}}
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                </button>
                @if($hasFilters)
                    <a href="{{ route('customer-receipts.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Desktop Receipts Table View (>= 768px) --}}
<div class="card d-none d-md-block mb-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 140px;">{{ __('sales.receipts.col_receipt') }}</th>
                    <th style="min-width: 200px;">{{ __('sales.receipts.col_customer') }}</th>
                    <th style="min-width: 160px;">{{ __('sales.receipts.col_invoice') }}</th>
                    <th style="min-width: 150px;">{{ __('sales.receipts.col_method') }}</th>
                    <th style="min-width: 160px;">{{ __('sales.receipts.col_reference') }}</th>
                    <th class="text-end" style="min-width: 130px;">{{ __('sales.receipts.col_amount') }}</th>
                    <th class="text-center" style="min-width: 110px;">{{ __('sales.receipts.col_status') }}</th>
                    <th class="text-end" style="min-width: 110px;">{{ __('sales.receipts.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $r)
                    <tr>
                        {{-- Receipt Voucher # & Date --}}
                        <td>
                            <a href="{{ route('customer-receipts.show', $r) }}" class="fw-bold text-decoration-none text-success font-monospace">
                                {{ $r->receipt_number }}
                            </a>
                            <div class="text-muted small">
                                <i class="bi bi-calendar3"></i> {{ fmt_date($r->receipt_date) }}
                            </div>
                            @if($r->creator)
                                <div class="text-muted fs-xs">
                                    <i class="bi bi-person"></i> {{ $r->creator->name }}
                                </div>
                            @endif
                        </td>

                        {{-- Customer / Buyer --}}
                        <td>
                            <div class="fw-semibold text-dark">
                                <a href="{{ route('customers.show', $r->customer) }}" class="text-dark text-decoration-none hover-underline">
                                    {{ $r->customer->name }}
                                </a>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-1.5 mt-0.5">
                                <span class="badge bg-light text-secondary border fs-xs">
                                    {{ $r->customer->customer_type_label }}
                                </span>
                                @if($r->customer->city)
                                    <span class="text-muted fs-xs">
                                        <i class="bi bi-geo-alt"></i> {{ $r->customer->city }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Linked Invoice --}}
                        <td>
                            @if($r->invoice)
                                <a href="{{ route('sales-invoices.show', $r->invoice) }}" class="fw-semibold text-decoration-none font-monospace small">
                                    <i class="bi bi-receipt text-primary"></i> {{ $r->invoice->invoice_number }}
                                </a>
                                <div class="text-muted fs-xs mt-0.5">
                                    Total: {{ money($r->invoice->total_amount) }}
                                </div>
                            @else
                                <span class="text-muted small">Advance / Unallocated</span>
                            @endif
                        </td>

                        {{-- Payment Mode --}}
                        <td>
                            <span class="badge bg-light text-dark border">
                                @if($r->payment_method === 'cash')
                                    <i class="bi bi-cash text-success me-1"></i>
                                @elseif($r->payment_method === 'upi')
                                    <i class="bi bi-qr-code text-primary me-1"></i>
                                @else
                                    <i class="bi bi-bank text-info me-1"></i>
                                @endif
                                {{ $r->method_label }}
                            </span>
                        </td>

                        {{-- Reference & Bank Details --}}
                        <td>
                            @if($r->transaction_reference)
                                <div class="font-monospace small text-dark fw-medium">
                                    {{ $r->transaction_reference }}
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif

                            @if($r->bank_name)
                                <div class="text-muted fs-xs mt-0.5">
                                    <i class="bi bi-building"></i> {{ $r->bank_name }}
                                </div>
                            @endif
                        </td>

                        {{-- Amount Received --}}
                        <td class="text-end font-monospace fw-bold fs-6 text-success">
                            {{ money($r->amount) }}
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if($r->isReversed())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i>{{ __('sales.invoices.reverse') }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('accounts.paid') }}
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customer-receipts.show', $r) }}" class="btn btn-outline-secondary" title="{{ __('app.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('customer-receipts.print', $r) }}" target="_blank" class="btn btn-outline-secondary" title="{{ __('sales.receipts.print_voucher') }}">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="empty-state">
                                <div class="empty-state-icon text-muted mb-2">
                                    <i class="bi bi-wallet2 fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-dark">{{ __('sales.receipts.empty_receipts') }}</h6>
                                <p class="text-muted small mb-3">{{ __('app.no_records') }}</p>
                                @can('customer_receipts.create')
                                    <a href="{{ route('customer-receipts.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle"></i> {{ __('sales.receipts.add_new') }}
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
    @forelse($receipts as $r)
        <div class="mobile-record-card mb-3 border rounded-3 p-3 bg-white shadow-sm">
            {{-- Card Header --}}
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <a href="{{ route('customer-receipts.show', $r) }}" class="fw-bold text-success text-decoration-none font-monospace fs-6">
                        <i class="bi bi-wallet2"></i> {{ $r->receipt_number }}
                    </a>
                    <div class="text-muted fs-xs mt-0.5">
                        <i class="bi bi-calendar3"></i> {{ fmt_date($r->receipt_date) }}
                    </div>
                </div>
                <div>
                    @if($r->isReversed())
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fs-xs">
                            <i class="bi bi-x-circle"></i> Reversed
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-xs">
                            <i class="bi bi-check-circle-fill"></i> Completed
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Body (Vertical Stack) --}}
            <div class="py-2 my-2 border-top border-bottom">
                {{-- Buyer Profile --}}
                <div class="mb-2">
                    <div class="fw-bold text-dark fs-6">{{ $r->customer->name }}</div>
                    <div class="d-flex flex-wrap align-items-center gap-1.5 mt-0.5">
                        <span class="badge bg-light text-secondary border fs-xs">{{ $r->customer->customer_type_label }}</span>
                        @if($r->customer->city)
                            <span class="text-muted fs-xs"><i class="bi bi-geo-alt"></i> {{ $r->customer->city }}</span>
                        @endif
                    </div>
                </div>

                {{-- Linked Invoice & Payment Mode --}}
                <div class="bg-light p-2.5 rounded-3 mb-2">
                    @if($r->invoice)
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted fs-xs">{{ __('sales.receipts.invoice') }}:</span>
                            <a href="{{ route('sales-invoices.show', $r->invoice) }}" class="fw-semibold text-decoration-none font-monospace small">
                                <i class="bi bi-receipt"></i> {{ $r->invoice->invoice_number }}
                            </a>
                        </div>
                    @endif
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fs-xs">{{ __('sales.receipts.payment_mode') }}:</span>
                        <span class="badge bg-white text-dark border">
                            {{ $r->method_label }}
                        </span>
                    </div>
                    @if($r->transaction_reference || $r->bank_name)
                        <div class="d-flex align-items-center justify-content-between text-muted fs-xs mt-1 pt-1 border-top">
                            <span>Ref / Bank:</span>
                            <span class="font-monospace text-dark">{{ $r->transaction_reference ?? $r->bank_name }}</span>
                        </div>
                    @endif
                </div>

                {{-- Amount Received --}}
                <div class="d-flex align-items-center justify-content-between pt-1">
                    <span class="text-muted small fw-semibold">{{ __('sales.receipts.amount') }}:</span>
                    <span class="font-monospace fw-bold text-success fs-5">{{ money($r->amount) }}</span>
                </div>
            </div>

            {{-- Card Footer Actions --}}
            <div class="d-flex align-items-center justify-content-between pt-1">
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $r->customer->phone ?? $r->customer->mobile ?? '');
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
                    <a href="{{ route('customer-receipts.show', $r) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye"></i> {{ __('app.view') }}
                    </a>
                    <a href="{{ route('customer-receipts.print', $r) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-printer"></i> {{ __('sales.receipts.print_voucher') }}
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="card p-4 text-center text-muted">
            <i class="bi bi-wallet2 fs-1 d-block mb-2 text-secondary"></i>
            <h6 class="fw-bold">{{ __('sales.receipts.empty_receipts') }}</h6>
            <p class="small text-muted mb-0">{{ __('app.no_records') }}</p>
        </div>
    @endforelse
</div>

{{-- 7. Pagination --}}
@if($receipts->hasPages())
    <div class="card shadow-sm p-3 bg-white mb-4">
        {{ $receipts->links() }}
    </div>
@endif

{{-- 8. Mobile Floating Action Button (FAB) --}}
@can('customer_receipts.create')
    <a href="{{ route('customer-receipts.create') }}" class="mobile-fab" aria-label="{{ __('sales.receipts.add_new') }}">
        <i class="bi bi-plus-lg fs-5"></i>
        <span>{{ __('sales.receipts.add_new') }}</span>
    </a>
@endcan
@endsection
