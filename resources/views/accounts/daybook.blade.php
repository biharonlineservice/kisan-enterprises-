@extends('layouts.app')

@section('title', __('accounts.daybook_title'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'tab']) || (request('date') && request('date') !== now()->toDateString());
    $isNetPositive = bccomp((string) $daybook['net_flow'], '0.00', 2) >= 0;
    $dateCarbon = \Illuminate\Support\Carbon::parse($daybook['date']);
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-calendar-event"></i>
                    <strong>{{ $dateCarbon->translatedFormat('l, d M Y') }}</strong>
                </span>
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-journal-check"></i>
                    <strong>{{ __('accounts.banner_vouchers', ['count' => count($daybook['entries'])]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ \App\Services\FinancialYear::label() }}</strong>
                </span>
                @if($isNetPositive)
                    <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                        <i class="bi bi-arrow-down-left-circle-fill text-success"></i>
                        <strong>{{ __('accounts.banner_net_inflow', ['amount' => money($daybook['net_flow'])]) }}</strong>
                    </span>
                @else
                    <span class="erp-hero-chip" style="background: rgba(239, 68, 68, .25); border-color: rgba(239, 68, 68, .5); color: #fecaca; white-space: nowrap;">
                        <i class="bi bi-arrow-up-right-circle-fill text-danger"></i>
                        <strong>{{ __('accounts.banner_net_outflow', ['amount' => money(abs((float)$daybook['net_flow']))]) }}</strong>
                    </span>
                @endif
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('accounts.daybook_title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('accounts.daybook_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('accounts.daybook.export', request()->query()) }}" class="erp-hero-btn" title="{{ __('accounts.export_csv') }}">
                <i class="bi bi-download"></i>
                <span>{{ __('accounts.export_csv') }}</span>
            </a>
            <button type="button" onclick="window.print()" class="erp-hero-btn d-none d-sm-inline-flex" title="{{ __('accounts.print_daybook') }}">
                <i class="bi bi-printer"></i>
                <span>{{ __('app.print') }}</span>
            </button>
            <a href="{{ route('accounts.index') }}" class="erp-hero-btn" title="{{ __('accounts.cash_bank_shortcut') }}">
                <i class="bi bi-bank"></i>
                <span>{{ __('accounts.cash_bank_shortcut') }}</span>
            </a>
            <a href="{{ route('accounts.profit_and_loss') }}" class="erp-hero-btn" title="{{ __('accounts.pnl_shortcut') }}">
                <i class="bi bi-graph-up"></i>
                <span>{{ __('accounts.pnl_shortcut') }}</span>
            </a>
        </div>
    </div>
</div>

{{-- 2. Daily Financial KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Daily Inflows (Customer Receipts) --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('accounts.daybook', array_merge(request()->except(['tab']), ['tab' => 'receipts'])) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('accounts.kpi_inflows_title') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-arrow-down-left-circle-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-success font-monospace text-truncate">
                    +{{ money($daybook['total_inflow']) }}
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('accounts.kpi_inflows_sub', ['count' => number_format($counts['receipts'])]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Farmer Crop Payouts (Procurement Settlements) --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('accounts.daybook', array_merge(request()->except(['tab']), ['tab' => 'payments'])) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #D94848;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('accounts.kpi_payments_title') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEE2E2; color: #DC2626;">
                        <i class="bi bi-cash-coin"></i>
                    </span>
                </div>
                <div class="kpi-value text-danger font-monospace text-truncate">
                    -{{ money($totalPayments) }}
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('accounts.kpi_payments_sub', ['count' => number_format($counts['payments'])]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Operating Expenses --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('accounts.daybook', array_merge(request()->except(['tab']), ['tab' => 'expenses'])) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('accounts.kpi_expenses_title') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-receipt-cutoff"></i>
                    </span>
                </div>
                <div class="kpi-value font-monospace text-truncate" style="color: #B45309;">
                    -{{ money($totalExpenses) }}
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('accounts.kpi_expenses_sub', ['count' => number_format($counts['expenses'])]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Net Cash Flow Position --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: {{ $isNetPositive ? '#0F766E' : '#B45309' }};">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_net_flow_title') }}</span>
                <span class="kpi-badge-icon" style="background: {{ $isNetPositive ? '#CCFBF1' : '#FEF3C7' }}; color: {{ $isNetPositive ? '#0F766E' : '#B45309' }};">
                    <i class="bi {{ $isNetPositive ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i>
                </span>
            </div>
            <div class="kpi-value font-monospace text-truncate {{ $isNetPositive ? 'text-success' : 'text-danger' }}">
                {{ ($isNetPositive ? '+' : '') . money($daybook['net_flow']) }}
            </div>
            <div class="kpi-sub text-truncate">
                {{ $isNetPositive ? __('accounts.kpi_net_flow_positive') : __('accounts.kpi_net_flow_negative') }}
            </div>
        </div>
    </div>
</div>

{{-- 3. Navigation Filter Tabs Bar --}}
<div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a href="{{ route('accounts.daybook', array_merge(request()->except(['tab']), ['tab' => 'all'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'all' ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>{{ __('accounts.tab_all_entries') }}</span>
                    <span class="badge {{ $tab === 'all' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">{{ $counts['all'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounts.daybook', array_merge(request()->except(['tab']), ['tab' => 'receipts'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'receipts' ? 'active' : '' }}">
                    <i class="bi bi-arrow-down-left text-success"></i>
                    <span>{{ __('accounts.tab_receipts') }}</span>
                    <span class="badge {{ $tab === 'receipts' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">{{ $counts['receipts'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounts.daybook', array_merge(request()->except(['tab']), ['tab' => 'payments'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'payments' ? 'active' : '' }}">
                    <i class="bi bi-arrow-up-right text-danger"></i>
                    <span>{{ __('accounts.tab_payments') }}</span>
                    <span class="badge {{ $tab === 'payments' ? 'bg-danger text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">{{ $counts['payments'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounts.daybook', array_merge(request()->except(['tab']), ['tab' => 'expenses'])) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'expenses' ? 'active' : '' }}">
                    <i class="bi bi-dash-circle text-warning"></i>
                    <span>{{ __('accounts.tab_expenses') }}</span>
                    <span class="badge {{ $tab === 'expenses' ? 'bg-warning text-dark' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">{{ $counts['expenses'] }}</span>
                </a>
            </li>
        </ul>

        {{-- Quick Date Navigation Trio & Export --}}
        <div class="d-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <div class="btn-group btn-group-sm shadow-sm" role="group">
                <a href="{{ route('accounts.daybook', array_merge(request()->query(), ['date' => $prevDate])) }}" class="btn btn-outline-secondary" title="{{ __('accounts.prev_day') }}">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="{{ route('accounts.daybook', array_merge(request()->query(), ['date' => now()->toDateString()])) }}" class="btn {{ $daybook['date'] === now()->toDateString() ? 'btn-success text-white' : 'btn-outline-secondary' }}" title="{{ __('accounts.today') }}">
                    <i class="bi bi-calendar-check me-1"></i> {{ __('accounts.today') }}
                </a>
                <a href="{{ route('accounts.daybook', array_merge(request()->query(), ['date' => $nextDate])) }}" class="btn btn-outline-secondary" title="{{ __('accounts.next_day') }}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            <a href="{{ route('accounts.daybook.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-download"></i>
                <span class="d-none d-sm-inline">{{ __('app.export') }}</span>
            </a>
        </div>
    </div>
</div>

{{-- 4. Date Picker & Search Toolbar --}}
<div class="card border-0 shadow-sm mb-4 bg-white erp-filter-collapsible" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('accounts.daybook') }}" class="row g-2 align-items-end" data-autosubmit>
            @if($tab !== 'all')
                <input type="hidden" name="tab" value="{{ $tab }}">
            @endif

            <div class="col-12 col-sm-4 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('accounts.date') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3 text-muted"></i></span>
                    <input type="date" name="date" value="{{ $daybook['date'] }}" class="form-control border-start-0" onchange="this.form.submit()">
                </div>
            </div>

            <div class="col-12 col-sm-8 col-md-7">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0"
                           placeholder="{{ __('accounts.search_daybook_placeholder') }}"
                           value="{{ $q }}">
                </div>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-filter me-1"></i> {{ __('app.filter') }}
                </button>
                @if($hasFilters)
                    <a href="{{ route('accounts.daybook', ['date' => $daybook['date']]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 4. Mobile Cards Feed (< 768px) --}}
<div class="d-md-none">
    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
        <span class="text-muted small fw-bold text-uppercase">{{ __('accounts.banner_vouchers', ['count' => count($daybook['entries'])]) }}</span>
        <button type="button" onclick="window.print()" class="btn btn-sm btn-light border py-1 px-2">
            <i class="bi bi-printer"></i> {{ __('app.print') }}
        </button>
    </div>

    @if(count($daybook['entries']) > 0)
        <div class="mobile-card-list">
            @foreach($daybook['entries'] as $e)
                <div class="mobile-record-card">
                    <div class="mobile-card-header">
                        <div>
                            @if($e['type'] === 'Customer Receipt')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-arrow-down-left"></i> {{ __('accounts.receipt') }}
                                </span>
                            @elseif($e['type'] === 'Farmer Payment')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-arrow-up-right"></i> {{ __('accounts.farmer_payment') }}
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-dash-circle"></i> {{ __('accounts.expense_outflow') }}
                                </span>
                            @endif
                        </div>
                        <span class="badge bg-light text-dark border">{{ $e['mode'] }}</span>
                    </div>

                    <div class="mobile-card-body d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="font-monospace fw-bold text-dark small">{{ $e['voucher'] }}</span>
                        </div>
                        <div class="fw-bold text-dark fs-6">{{ $e['party'] }}</div>
                        @if(!empty($e['description']))
                            <div class="small text-muted mt-1">{{ $e['description'] }}</div>
                        @endif
                        @if(!empty($e['reference']))
                            <div class="small text-muted mt-1 font-monospace">
                                <i class="bi bi-hash"></i> Ref: {{ $e['reference'] }}
                            </div>
                        @endif
                    </div>

                    <div class="mobile-card-footer">
                        <span class="text-muted small">{{ __('app.amount') }}</span>
                        @if(bccomp((string) $e['inflow'], '0.00', 2) > 0)
                            <span class="mobile-stat-primary text-success font-monospace">+{{ money($e['inflow']) }}</span>
                        @else
                            <span class="mobile-stat-primary text-danger font-monospace">-{{ money($e['outflow']) }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Mobile Daybook Summary Bar --}}
        <div class="card shadow-sm border mt-3 bg-white rounded-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">{{ __('accounts.inflows') }}:</span>
                    <strong class="text-success font-monospace">+{{ money($daybook['total_inflow']) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">{{ __('accounts.outflows') }}:</span>
                    <strong class="text-danger font-monospace">-{{ money($daybook['total_outflow']) }}</strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold">
                    <span>{{ __('accounts.net_daily_flow') }}:</span>
                    <span class="font-monospace {{ $isNetPositive ? 'text-success' : 'text-danger' }}">
                        {{ ($isNetPositive ? '+' : '') . money($daybook['net_flow']) }}
                    </span>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm text-center py-5 bg-white rounded-3">
            <i class="bi bi-calendar-x fs-1 text-muted mb-2"></i>
            <p class="text-muted mb-0">{{ __('accounts.no_daybook_entries') }}</p>
        </div>
    @endif
</div>

{{-- 5. Desktop Daybook Journal Table (>= 768px) --}}
<div class="card shadow-sm border-0 d-none d-md-block bg-white rounded-3 overflow-hidden">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="card-title h6 mb-0 fw-bold text-dark">
            <i class="bi bi-journal-text text-success me-1"></i> {{ __('accounts.daybook') }} ({{ fmt_date($daybook['date']) }})
        </h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border">{{ __('accounts.banner_vouchers', ['count' => count($daybook['entries'])]) }}</span>
            <a href="{{ route('accounts.daybook.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download me-1"></i> {{ __('accounts.export_csv') }}
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">{{ __('app.status') }}</th>
                    <th style="width: 170px;">{{ __('accounts.voucher') }}</th>
                    <th>{{ __('accounts.beneficiary') }}</th>
                    <th style="width: 180px;">{{ __('accounts.mode_ref') }}</th>
                    <th>{{ __('app.notes') }}</th>
                    <th class="text-end" style="width: 150px;">{{ __('accounts.col_inflow') }}</th>
                    <th class="text-end" style="width: 150px;">{{ __('accounts.col_outflow') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daybook['entries'] as $e)
                    <tr>
                        <td>
                            @if($e['type'] === 'Customer Receipt')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-arrow-down-left"></i> {{ __('accounts.receipt') }}
                                </span>
                            @elseif($e['type'] === 'Farmer Payment')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-arrow-up-right"></i> {{ __('accounts.farmer_payment') }}
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-dash-circle"></i> {{ __('accounts.expense_outflow') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong class="font-monospace text-dark">{{ $e['voucher'] }}</strong>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $e['party'] }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $e['mode'] }}</span>
                            @if(!empty($e['reference']))
                                <small class="text-muted d-block mt-1 font-monospace">Ref: {{ $e['reference'] }}</small>
                            @endif
                        </td>
                        <td class="small text-muted" style="max-width: 320px;">
                            {{ $e['description'] ?? '-' }}
                        </td>
                        <td class="text-end font-monospace fw-bold text-success">
                            {{ bccomp((string) $e['inflow'], '0.00', 2) > 0 ? ('+' . money($e['inflow'])) : '-' }}
                        </td>
                        <td class="text-end font-monospace fw-bold text-danger">
                            {{ bccomp((string) $e['outflow'], '0.00', 2) > 0 ? ('-' . money($e['outflow'])) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                            {{ __('accounts.no_daybook_entries') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <th colspan="5" class="text-end">{{ __('app.total') }} ({{ fmt_date($daybook['date']) }}):</th>
                    <th class="text-end font-monospace text-success fs-6">+{{ money($daybook['total_inflow']) }}</th>
                    <th class="text-end font-monospace text-danger fs-6">-{{ money($daybook['total_outflow']) }}</th>
                </tr>
                <tr class="table-white border-top">
                    <th colspan="5" class="text-end text-muted small text-uppercase">{{ __('accounts.net_daily_flow') }}:</th>
                    <th colspan="2" class="text-end font-monospace fs-5 {{ $isNetPositive ? 'text-success' : 'text-danger' }}">
                        {{ ($isNetPositive ? '+' : '') . money($daybook['net_flow']) }}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- 6. Mobile Floating Action Button --}}
<div class="d-md-none position-fixed bottom-0 end-0 p-3" style="z-index: 1040; margin-bottom: 60px;">
    <a href="{{ route('accounts.daybook.export', request()->query()) }}" class="btn btn-success rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;" title="{{ __('accounts.export_csv') }}">
        <i class="bi bi-download fs-5"></i>
    </a>
</div>
@endsection
