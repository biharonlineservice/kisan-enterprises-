@extends('layouts.app')

@section('title', __('accounts.expenses'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'category_id', 'method', 'status', 'from', 'to', 'tab']);
    $activeTab = $activeTab ?? request('tab', 'all');
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xxl-row align-items-xxl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                @if($kpis['approvedCount'] > 0)
                    <span class="erp-hero-chip" style="background: rgba(34, 197, 94, .2); border-color: rgba(34, 197, 94, .4); color: #86efac; white-space: nowrap;">
                        <i class="bi bi-file-earmark-check-fill text-success"></i>
                        <strong>{{ __('accounts.banner_approved', ['approved' => $kpis['approvedCount'], 'total' => $kpis['totalCount']]) }}</strong>
                    </span>
                @endif
                <span class="erp-hero-chip" style="background: rgba(239, 68, 68, .2); border-color: rgba(239, 68, 68, .4); color: #fca5a5; white-space: nowrap;">
                    <i class="bi bi-wallet2 text-danger"></i>
                    <strong>{{ __('accounts.banner_amount', ['amount' => money($kpis['totalExpenses'])]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('accounts.expenses') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 820px;">
                {{ __('accounts.expenses_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('expenses.export', request()->all()) }}" class="erp-hero-btn">
                <i class="bi bi-download"></i>
                <span>{{ __('accounts.export_csv') }}</span>
            </a>
            <a href="{{ route('accounts.index') }}" class="erp-hero-btn">
                <i class="bi bi-bank"></i>
                <span>{{ __('accounts.cash_bank_shortcut') }}</span>
            </a>
            <a href="{{ route('accounts.daybook') }}" class="erp-hero-btn">
                <i class="bi bi-journal-text"></i>
                <span>{{ __('accounts.daybook_shortcut') }}</span>
            </a>
            @can('expenses.create')
                <a href="{{ route('expenses.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('accounts.add_expense') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Operating Expenses --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: #D94848;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_total_expenses') }}</span>
                <span class="kpi-badge-icon" style="background: #FEE2E2; color: #DC2626;">
                    <i class="bi bi-cash-stack"></i>
                </span>
            </div>
            <div class="kpi-value text-danger text-truncate">{{ money($kpis['totalExpenses']) }}</div>
            <div class="kpi-sub text-truncate">
                {{ __('accounts.kpi_expenses_sub', ['count' => $kpis['approvedCount']]) }}
            </div>
        </div>
    </div>

    {{-- Card 2: Today's Outflow --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_today_expenses') }}</span>
                <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                    <i class="bi bi-calendar-minus"></i>
                </span>
            </div>
            <div class="kpi-value text-truncate" style="color: #0284C7;">{{ money($kpis['todayExpenses']) }}</div>
            <div class="kpi-sub text-truncate">
                {{ __('accounts.kpi_today_sub') }} ({{ $kpis['todayCount'] }} Today)
            </div>
        </div>
    </div>

    {{-- Card 3: Cash Counter Outflows --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_cash_expenses') }}</span>
                <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                    <i class="bi bi-cash"></i>
                </span>
            </div>
            <div class="kpi-value text-truncate" style="color: #B45309;">{{ money($kpis['cashExpenses']) }}</div>
            <div class="kpi-sub text-truncate">
                {{ __('accounts.kpi_cash_sub', ['count' => $kpis['cashCount']]) }}
            </div>
        </div>
    </div>

    {{-- Card 4: Bank & Digital Outflow --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_bank_digital') }}</span>
                <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                    <i class="bi bi-bank"></i>
                </span>
            </div>
            <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ money($kpis['digitalExpenses']) }}</div>
            <div class="kpi-sub text-truncate">
                {{ __('accounts.kpi_bank_sub', ['count' => $kpis['digitalCount']]) }}
            </div>
        </div>
    </div>
</div>

{{-- 3. Filter Pill Tabs Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            @php
                $tabs = [
                    'all' => ['label' => __('accounts.tab_all'), 'count' => $tabCounts['all'], 'icon' => 'bi-collection'],
                    'approved' => ['label' => __('accounts.tab_approved'), 'count' => $tabCounts['approved'], 'icon' => 'bi-check-circle-fill'],
                    'cash' => ['label' => __('accounts.tab_cash'), 'count' => $tabCounts['cash'], 'icon' => 'bi-cash'],
                    'bank' => ['label' => __('accounts.tab_bank'), 'count' => $tabCounts['bank'], 'icon' => 'bi-bank2'],
                    'upi' => ['label' => __('accounts.tab_upi'), 'count' => $tabCounts['upi'], 'icon' => 'bi-qr-code-scan'],
                    'reversed' => ['label' => __('accounts.tab_reversed'), 'count' => $tabCounts['reversed'], 'icon' => 'bi-arrow-counterclockwise'],
                ];
            @endphp
            @foreach($tabs as $key => $meta)
                <li class="nav-item">
                    <a href="{{ route('expenses.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
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
                <i class="bi bi-shield-check me-1"></i>Audit Locked
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-receipt me-1"></i>GST Vouchers
            </span>
        </div>
    </div>
</div>

{{-- 4. Advanced Filter Bar & Listing Card --}}
<div class="card border-0 shadow-sm mb-4 bg-white erp-filter-collapsible" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            {{-- Live Search --}}
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('accounts.expense_number') }}, {{ __('accounts.payee') }}...">
                </div>
            </div>

            {{-- Category Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('accounts.category') }}</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Payment Method Filter --}}
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('accounts.payment_method') }}</label>
                <select name="method" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($methods as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('method') === $k)>{{ $lbl }}</option>
                    @endforeach
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
                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Desktop Expense Vouchers Table (>= 768px) --}}
<div class="card d-none d-md-block mb-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 140px;">{{ __('accounts.col_voucher') }}</th>
                    <th style="min-width: 160px;">{{ __('accounts.col_category') }}</th>
                    <th style="min-width: 220px;">{{ __('accounts.col_payee') }}</th>
                    <th style="min-width: 160px;">{{ __('accounts.col_account') }}</th>
                    <th class="text-end" style="min-width: 130px;">{{ __('accounts.col_amount') }}</th>
                    <th class="text-center" style="min-width: 110px;">{{ __('accounts.col_status') }}</th>
                    <th class="text-end" style="min-width: 90px;">{{ __('accounts.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                    <tr>
                        {{-- Voucher # & Date --}}
                        <td>
                            <a href="{{ route('expenses.show', $e) }}" class="fw-bold text-decoration-none text-success font-monospace">
                                {{ $e->voucher_number }}
                            </a>
                            <div class="text-muted small">
                                <i class="bi bi-calendar3"></i> {{ fmt_date($e->expense_date) }}
                            </div>
                            @if($e->creator)
                                <div class="text-muted fs-xs">
                                    <i class="bi bi-person"></i> {{ $e->creator->name }}
                                </div>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td>
                            <span class="badge bg-light text-dark border fw-medium">
                                <i class="bi bi-tag text-primary me-1"></i> {{ $e->category->name }}
                            </span>
                        </td>

                        {{-- Payee & Remarks --}}
                        <td>
                            <div class="fw-semibold text-dark">{{ $e->paid_to }}</div>
                            @if($e->remarks)
                                <div class="text-muted fs-xs text-truncate mt-0.5" style="max-width: 240px;" title="{{ $e->remarks }}">
                                    {{ $e->remarks }}
                                </div>
                            @endif
                        </td>

                        {{-- Account & Payment Mode --}}
                        <td>
                            <span class="badge bg-light text-dark border">
                                @if($e->payment_method === 'cash')
                                    <i class="bi bi-cash text-success me-1"></i>
                                @elseif($e->payment_method === 'upi')
                                    <i class="bi bi-qr-code text-primary me-1"></i>
                                @else
                                    <i class="bi bi-bank text-info me-1"></i>
                                @endif
                                {{ $e->method_label }}
                            </span>
                            @if($e->account)
                                <div class="text-muted fs-xs mt-0.5">
                                    <i class="bi bi-wallet2"></i> {{ $e->account->name }}
                                </div>
                            @endif
                            @if($e->transaction_reference)
                                <div class="font-monospace fs-xs text-muted">
                                    Ref: {{ $e->transaction_reference }}
                                </div>
                            @endif
                        </td>

                        {{-- Expense Amount --}}
                        <td class="text-end font-monospace fw-bold fs-6 text-danger">
                            {{ money($e->amount) }}
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if($e->isReversed())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i>{{ __('accounts.tab_reversed') }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('app.approved') }}
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">
                            <a href="{{ route('expenses.show', $e) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="empty-state">
                                <div class="empty-state-icon text-muted mb-2">
                                    <i class="bi bi-wallet2 fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-dark">{{ __('accounts.empty_expenses') }}</h6>
                                <p class="text-muted small mb-3">{{ __('app.no_records') }}</p>
                                @can('expenses.create')
                                    <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle"></i> {{ __('accounts.add_expense') }}
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
    @forelse($expenses as $e)
        <div class="mobile-record-card mb-3 border rounded-3 p-3 bg-white shadow-sm">
            {{-- Card Header --}}
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <a href="{{ route('expenses.show', $e) }}" class="fw-bold text-success text-decoration-none font-monospace fs-6">
                        <i class="bi bi-receipt"></i> {{ $e->voucher_number }}
                    </a>
                    <div class="text-muted fs-xs mt-0.5">
                        <i class="bi bi-calendar3"></i> {{ fmt_date($e->expense_date) }}
                    </div>
                </div>
                <div>
                    @if($e->isReversed())
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fs-xs">
                            <i class="bi bi-x-circle"></i> Reversed
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-xs">
                            <i class="bi bi-check-circle-fill"></i> Approved
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Body (Vertical Stack) --}}
            <div class="py-2 my-2 border-top border-bottom">
                {{-- Payee & Category --}}
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="fw-bold text-dark fs-6">{{ $e->paid_to }}</div>
                        <div class="mt-0.5">
                            <span class="badge bg-light text-dark border fs-xs">
                                <i class="bi bi-tag text-primary"></i> {{ $e->category->name }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Account & Mode --}}
                <div class="bg-light p-2.5 rounded-3 mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fs-xs">{{ __('accounts.payment_method') }}:</span>
                        <span class="badge bg-white text-dark border">
                            {{ $e->method_label }}
                        </span>
                    </div>
                    @if($e->account)
                        <div class="d-flex align-items-center justify-content-between text-muted fs-xs">
                            <span>{{ __('accounts.account') }}:</span>
                            <span class="text-dark fw-medium">{{ $e->account->name }}</span>
                        </div>
                    @endif
                    @if($e->transaction_reference)
                        <div class="d-flex align-items-center justify-content-between text-muted fs-xs mt-1 pt-1 border-top">
                            <span>Ref / UTR:</span>
                            <span class="font-monospace text-dark">{{ $e->transaction_reference }}</span>
                        </div>
                    @endif
                </div>

                @if($e->remarks)
                    <div class="small text-muted mb-2 fst-italic">
                        "{{ $e->remarks }}"
                    </div>
                @endif

                {{-- Expense Outflow Amount --}}
                <div class="d-flex align-items-center justify-content-between pt-1">
                    <span class="text-muted small fw-semibold">{{ __('accounts.col_amount') }}:</span>
                    <span class="font-monospace fw-bold text-danger fs-5">{{ money($e->amount) }}</span>
                </div>
            </div>

            {{-- Card Footer Actions --}}
            <div class="d-flex align-items-center justify-content-end pt-1">
                <a href="{{ route('expenses.show', $e) }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-eye"></i> {{ __('app.view') }}
                </a>
            </div>
        </div>
    @empty
        <div class="card p-4 text-center text-muted">
            <i class="bi bi-journal-minus fs-1 d-block mb-2 text-secondary"></i>
            <h6 class="fw-bold">{{ __('accounts.empty_expenses') }}</h6>
            <p class="small text-muted mb-0">{{ __('app.no_records') }}</p>
        </div>
    @endforelse
</div>

{{-- 7. Pagination --}}
@if($expenses->hasPages())
    <div class="card shadow-sm p-3 bg-white mb-4">
        {{ $expenses->links() }}
    </div>
@endif

{{-- 8. Mobile Floating Action Button (FAB) --}}
@can('expenses.create')
    <a href="{{ route('expenses.create') }}" class="mobile-fab" aria-label="{{ __('accounts.add_expense') }}">
        <i class="bi bi-plus-lg fs-5"></i>
        <span>{{ __('accounts.add_expense') }}</span>
    </a>
@endcan
@endsection
