@extends('layouts.app')

@section('title', __('accounts.pnl_title'))

@section('content')
@php
    $isNetProfit = bccomp((string) $report['net_profit'], '0.00', 2) >= 0;
    $isGrossProfit = bccomp((string) $report['gross_profit'], '0.00', 2) >= 0;
    $hasSales = bccomp((string) $report['total_sales'], '0.00', 2) > 0;
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-calendar-range"></i>
                    <strong>{{ fmt_date($from) }} — {{ fmt_date($to) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ \App\Services\FinancialYear::label() }}</strong>
                </span>
                @if($isNetProfit)
                    <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                        <i class="bi bi-graph-up-arrow text-success"></i>
                        <strong>{{ __('accounts.banner_status_profit', ['amount' => money($report['net_profit']), 'margin' => $netMarginPct]) }}</strong>
                    </span>
                @else
                    <span class="erp-hero-chip" style="background: rgba(239, 68, 68, .25); border-color: rgba(239, 68, 68, .5); color: #fecaca; white-space: nowrap;">
                        <i class="bi bi-graph-down-arrow text-danger"></i>
                        <strong>{{ __('accounts.banner_status_loss', ['amount' => money(abs((float)$report['net_profit'])), 'margin' => $netMarginPct]) }}</strong>
                    </span>
                @endif
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('accounts.pnl_title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('accounts.pnl_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('accounts.profit_and_loss.export', request()->query()) }}" class="erp-hero-btn" title="{{ __('accounts.export_csv') }}">
                <i class="bi bi-download"></i>
                <span>{{ __('accounts.export_csv') }}</span>
            </a>
            <button type="button" onclick="window.print()" class="erp-hero-btn d-none d-sm-inline-flex" title="{{ __('accounts.print_statement') }}">
                <i class="bi bi-printer"></i>
                <span>{{ __('app.print') }}</span>
            </button>
            <a href="{{ route('accounts.daybook') }}" class="erp-hero-btn" title="{{ __('accounts.daybook_shortcut') }}">
                <i class="bi bi-journal-text"></i>
                <span>{{ __('accounts.daybook_shortcut') }}</span>
            </a>
            <a href="{{ route('accounts.index') }}" class="erp-hero-btn" title="{{ __('accounts.cash_bank_shortcut') }}">
                <i class="bi bi-bank"></i>
                <span>{{ __('accounts.cash_bank_shortcut') }}</span>
            </a>
        </div>
    </div>
</div>

{{-- 2. Executive Metric Highlight Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Revenue (Sales) --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_revenue_title') }}</span>
                <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                    <i class="bi bi-graph-up"></i>
                </span>
            </div>
            <div class="kpi-value text-success font-monospace text-truncate">
                {{ money($report['total_sales']) }}
            </div>
            <div class="erp-kpi-sub text-truncate">
                {{ __('accounts.kpi_revenue_sub') }}
            </div>
        </div>
    </div>

    {{-- Card 2: Cost of Goods Sold (COGS) --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_cogs_title') }}</span>
                <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                    <i class="bi bi-cart-check"></i>
                </span>
            </div>
            <div class="kpi-value text-primary font-monospace text-truncate">
                {{ money($report['total_purchases']) }}
            </div>
            <div class="erp-kpi-sub text-truncate">
                {{ __('accounts.kpi_cogs_sub') }}
            </div>
        </div>
    </div>

    {{-- Card 3: Gross Trading Profit --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_gross_profit_title') }}</span>
                <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                    <i class="bi bi-pie-chart"></i>
                </span>
            </div>
            <div class="kpi-value {{ $isGrossProfit ? 'text-success' : 'text-danger' }} font-monospace text-truncate">
                {{ money($report['gross_profit']) }}
            </div>
            <div class="erp-kpi-sub text-truncate">
                {{ __('accounts.kpi_gross_profit_sub', ['margin' => $grossMarginPct]) }}
            </div>
        </div>
    </div>

    {{-- Card 4: Net Operating Profit / Loss --}}
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card hover-card" style="--kpi-accent: {{ $isNetProfit ? '#0F766E' : '#D94848' }};">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('accounts.kpi_net_profit_title') }}</span>
                <span class="kpi-badge-icon" style="background: {{ $isNetProfit ? '#CCFBF1' : '#FEE2E2' }}; color: {{ $isNetProfit ? '#0F766E' : '#DC2626' }};">
                    <i class="bi {{ $isNetProfit ? 'bi-trophy-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                </span>
            </div>
            <div class="kpi-value font-monospace text-truncate {{ $isNetProfit ? 'text-success' : 'text-danger' }}">
                {{ money($report['net_profit']) }}
            </div>
            <div class="erp-kpi-sub text-truncate">
                {{ __('accounts.kpi_net_profit_sub', ['margin' => $netMarginPct]) }}
            </div>
        </div>
    </div>
</div>

{{-- 3. Period Preset Tabs Bar --}}
<div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-md-0" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none;">
            <li class="nav-item">
                <a href="{{ route('accounts.profit_and_loss', ['period' => 'this_month']) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $period === 'this_month' ? 'active' : '' }}">
                    <i class="bi bi-calendar-month"></i>
                    <span>{{ __('accounts.period_this_month') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounts.profit_and_loss', ['period' => 'last_month']) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $period === 'last_month' ? 'active' : '' }}">
                    <i class="bi bi-calendar-minus"></i>
                    <span>{{ __('accounts.period_last_month') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounts.profit_and_loss', ['period' => 'this_quarter']) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $period === 'this_quarter' ? 'active' : '' }}">
                    <i class="bi bi-calendar3-range"></i>
                    <span>{{ __('accounts.period_this_quarter') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounts.profit_and_loss', ['period' => 'this_fy']) }}"
                   class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $period === 'this_fy' ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ __('accounts.period_this_fy', ['fy' => \App\Services\FinancialYear::label()]) }}</span>
                </a>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <a href="{{ route('accounts.profit_and_loss.export', array_merge(request()->query(), ['period' => $period])) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-download"></i>
                <span class="d-none d-sm-inline">{{ __('app.export') }}</span>
            </a>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-none d-lg-inline-flex" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Accrual Basis
            </span>
        </div>
    </div>
</div>

{{-- 4. Custom Date Range Toolbar --}}
<div class="card border-0 shadow-sm mb-4 bg-white erp-filter-collapsible" style="border-radius: 12px;">
    <div class="card-body p-3 bg-light-subtle rounded-3">
        <form method="GET" action="{{ route('accounts.profit_and_loss') }}" class="row g-2 align-items-end" data-autosubmit>
            <input type="hidden" name="period" value="custom">

            <div class="col-6 col-sm-5 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('accounts.from_date') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event text-muted"></i></span>
                    <input type="date" name="from" value="{{ $from }}" class="form-control border-start-0" title="{{ __('accounts.from_date') }}">
                </div>
            </div>

            <div class="col-6 col-sm-5 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('accounts.to_date') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-check text-muted"></i></span>
                    <input type="date" name="to" value="{{ $to }}" class="form-control border-start-0" title="{{ __('accounts.to_date') }}">
                </div>
            </div>

            <div class="col-12 col-sm-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1 flex-md-grow-0 px-3">
                    <i class="bi bi-arrow-repeat me-1"></i> {{ __('accounts.btn_generate') }}
                </button>
                @if($period === 'custom')
                    <a href="{{ route('accounts.profit_and_loss', ['period' => 'this_month']) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 4. Mobile P&L Feed (< 768px) --}}
<div class="d-md-none mb-4">
    {{-- Section 1: Revenue Card --}}
    <div class="card shadow-sm border-0 mb-3 bg-white rounded-3">
        <div class="card-header bg-success-subtle text-success py-2 px-3 fw-bold small text-uppercase">
            <i class="bi bi-arrow-down-left-circle me-1"></i> {{ __('accounts.section_revenue') }}
        </div>
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold text-dark">{{ __('accounts.col_sales_desc') }}</div>
                    <small class="text-muted">{{ __('accounts.pct_revenue') }}: 100%</small>
                </div>
                <div class="font-monospace fw-bold text-success fs-6">
                    {{ money($report['total_sales']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Section 2: COGS Card --}}
    <div class="card shadow-sm border-0 mb-3 bg-white rounded-3">
        <div class="card-header bg-primary-subtle text-primary py-2 px-3 fw-bold small text-uppercase">
            <i class="bi bi-cart-check me-1"></i> {{ __('accounts.section_cogs') }}
        </div>
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold text-dark">{{ __('accounts.col_purchases_desc') }}</div>
                    <small class="text-muted">{{ __('accounts.pct_revenue') }}: {{ $hasSales ? bcmul(bcdiv($report['total_purchases'], $report['total_sales'], 4), '100', 1) : '0' }}%</small>
                </div>
                <div class="font-monospace fw-bold text-danger fs-6">
                    -{{ money($report['total_purchases']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3: Gross Trading Profit Bar --}}
    <div class="card shadow-sm border-0 mb-3 rounded-3" style="background: #f8fafc; border-left: 4px solid #f59e0b !important;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-dark">{{ __('accounts.section_gross') }}</div>
                    <small class="text-muted">{{ __('accounts.gross_margin') }}: <strong>{{ $grossMarginPct }}%</strong></small>
                </div>
                <div class="font-monospace fw-bold fs-5 {{ $isGrossProfit ? 'text-success' : 'text-danger' }}">
                    {{ money($report['gross_profit']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Section 4: Operating Expenses Card --}}
    <div class="card shadow-sm border-0 mb-3 bg-white rounded-3">
        <div class="card-header bg-light py-2 px-3 fw-bold small text-uppercase text-secondary">
            <i class="bi bi-receipt-cutoff me-1"></i> {{ __('accounts.section_expenses') }}
        </div>
        <div class="card-body p-3">
            @forelse($report['expenses_by_category'] as $categoryName => $catAmount)
                <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="text-dark small">{{ $categoryName }}</span>
                    <span class="font-monospace text-danger small">-{{ money($catAmount) }}</span>
                </div>
            @empty
                <div class="text-muted small fst-italic py-2">{{ __('accounts.no_expenses_period') }}</div>
            @endforelse
            <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top fw-bold small">
                <span>{{ __('accounts.col_total_expenses') }}</span>
                <span class="font-monospace text-danger">-{{ money($report['total_expenses']) }}</span>
            </div>
        </div>
    </div>

    {{-- Section 5: Net Profit Bottom Line --}}
    <div class="card shadow-sm border-0 rounded-3 text-white" style="background: {{ $isNetProfit ? 'linear-gradient(135deg, #14462b, #1f6f43)' : 'linear-gradient(135deg, #991b1b, #dc2626)' }};">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">{{ __('accounts.section_net') }}</div>
                    <small class="text-white-50">Net Margin: {{ $netMarginPct }}%</small>
                </div>
                <div class="font-monospace fw-bold fs-4">
                    {{ money($report['net_profit']) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 5. Desktop Detailed P&L Statement Table (>= 768px) --}}
<div class="card shadow-sm border-0 d-none d-md-block bg-white rounded-3 overflow-hidden">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="card-title h6 mb-0 fw-bold text-dark">
            <i class="bi bi-file-earmark-spreadsheet text-success me-1"></i> {{ __('accounts.pnl_title') }} ({{ fmt_date($from) }} — {{ fmt_date($to) }})
        </h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border">{{ \App\Services\FinancialYear::label() }}</span>
            <a href="{{ route('accounts.profit_and_loss.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download me-1"></i> {{ __('accounts.export_csv') }}
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('accounts.beneficiary') }} / Particulars</th>
                    <th style="width: 140px;">Type / Flow</th>
                    <th class="text-end" style="width: 180px;">{{ __('app.amount') }} (₹)</th>
                    <th class="text-end" style="width: 140px;">{{ __('accounts.pct_revenue') }}</th>
                </tr>
            </thead>
            <tbody>
                {{-- 1. Revenue from Operations --}}
                <tr class="table-light border-top border-2">
                    <th colspan="4" class="text-uppercase fw-bold text-success py-2">
                        <i class="bi bi-arrow-down-left-circle me-1"></i> {{ __('accounts.section_revenue') }}
                    </th>
                </tr>
                <tr>
                    <td class="ps-4 fw-semibold text-dark">
                        {{ __('accounts.col_sales_desc') }}
                    </td>
                    <td>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Credit (+)</span>
                    </td>
                    <td class="text-end font-monospace fw-bold text-success">
                        +{{ money($report['total_sales']) }}
                    </td>
                    <td class="text-end font-monospace text-muted">
                        100.00%
                    </td>
                </tr>

                {{-- 2. Cost of Goods Sold (COGS) --}}
                <tr class="table-light border-top border-2">
                    <th colspan="4" class="text-uppercase fw-bold text-primary py-2">
                        <i class="bi bi-cart-check me-1"></i> {{ __('accounts.section_cogs') }}
                    </th>
                </tr>
                <tr>
                    <td class="ps-4 fw-semibold text-dark">
                        {{ __('accounts.col_purchases_desc') }}
                    </td>
                    <td>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Debit (-)</span>
                    </td>
                    <td class="text-end font-monospace fw-bold text-danger">
                        -{{ money($report['total_purchases']) }}
                    </td>
                    <td class="text-end font-monospace text-muted">
                        {{ $hasSales ? bcmul(bcdiv($report['total_purchases'], $report['total_sales'], 4), '100', 2) : '0.00' }}%
                    </td>
                </tr>

                {{-- Gross Trading Profit --}}
                <tr style="background: #f8fafc;" class="border-top border-bottom border-2">
                    <th class="ps-4 fw-bold fs-6 text-dark py-3">
                        <i class="bi bi-pie-chart text-warning me-1"></i> {{ __('accounts.section_gross') }}
                    </th>
                    <td>
                        <span class="badge {{ $isGrossProfit ? 'bg-success text-white' : 'bg-danger text-white' }}">
                            {{ $isGrossProfit ? 'Gross Surplus' : 'Gross Deficit' }}
                        </span>
                    </td>
                    <th class="text-end font-monospace fw-bold fs-6 {{ $isGrossProfit ? 'text-success' : 'text-danger' }}">
                        {{ money($report['gross_profit']) }}
                    </th>
                    <th class="text-end font-monospace fw-bold text-dark">
                        {{ $grossMarginPct }}%
                    </th>
                </tr>

                {{-- 3. Operating & Administrative Expenses --}}
                <tr class="table-light border-top border-2">
                    <th colspan="4" class="text-uppercase fw-bold text-secondary py-2">
                        <i class="bi bi-receipt-cutoff me-1"></i> {{ __('accounts.section_expenses') }}
                    </th>
                </tr>
                @forelse($report['expenses_by_category'] as $categoryName => $catAmount)
                    <tr>
                        <td class="ps-4 text-dark">
                            {{ $categoryName }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">Debit (-)</span>
                        </td>
                        <td class="text-end font-monospace text-danger">
                            -{{ money($catAmount) }}
                        </td>
                        <td class="text-end font-monospace text-muted small">
                            {{ $hasSales ? bcmul(bcdiv($catAmount, $report['total_sales'], 4), '100', 2) : '0.00' }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="ps-4 text-muted fst-italic py-3">
                            {{ __('accounts.no_expenses_period') }}
                        </td>
                    </tr>
                @endforelse
                <tr class="bg-light fw-bold">
                    <th class="ps-4 text-muted">
                        {{ __('accounts.col_total_expenses') }}
                    </th>
                    <td>
                        <span class="badge bg-danger-subtle text-danger">Total Dr</span>
                    </td>
                    <th class="text-end font-monospace text-danger">
                        -{{ money($report['total_expenses']) }}
                    </th>
                    <th class="text-end font-monospace text-danger">
                        {{ $expenseRatioPct }}%
                    </th>
                </tr>

                {{-- 4. Net Operating Profit / Loss --}}
                <tr class="{{ $isNetProfit ? 'table-success' : 'table-danger' }} border-top border-3">
                    <th class="ps-4 fw-bold fs-5 py-3">
                        <i class="bi {{ $isNetProfit ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-1"></i>
                        {{ __('accounts.section_net') }}
                    </th>
                    <td>
                        <span class="badge {{ $isNetProfit ? 'bg-success' : 'bg-danger' }} fs-6">
                            {{ $isNetProfit ? 'Net Profit' : 'Net Loss' }}
                        </span>
                    </td>
                    <th class="text-end font-monospace fw-bold fs-4 {{ $isNetProfit ? 'text-success' : 'text-danger' }}">
                        {{ money($report['net_profit']) }}
                    </th>
                    <th class="text-end font-monospace fw-bold fs-6 {{ $isNetProfit ? 'text-success' : 'text-danger' }}">
                        {{ $netMarginPct }}%
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 6. Mobile Floating Action Button --}}
<div class="d-md-none position-fixed bottom-0 end-0 p-3" style="z-index: 1040; margin-bottom: 60px;">
    <a href="{{ route('accounts.profit_and_loss.export', request()->query()) }}" class="btn btn-success rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;" title="{{ __('accounts.export_csv') }}">
        <i class="bi bi-download fs-5"></i>
    </a>
</div>
@endsection
