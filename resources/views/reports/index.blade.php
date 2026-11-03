@extends('layouts.app')

@section('title', __('reports.hub_title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-pie-chart-fill"></i>
                    <strong>{{ __('reports.banner_modules', ['count' => 6]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ \App\Services\FinancialYear::label() }}</strong>
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-shield-check text-success"></i>
                    <strong>{{ __('reports.active_godowns', ['count' => $metrics['activeWarehousesCount']]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{!! __('reports.hub_title') !!}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('reports.hub_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            @if($user->hasPermission('accounts.view'))
                <a href="{{ route('accounts.profit_and_loss') }}" class="erp-hero-btn" title="{{ __('accounts.pnl_shortcut') }}">
                    <i class="bi bi-graph-up"></i>
                    <span>{{ __('accounts.pnl_shortcut') }}</span>
                </a>
                <a href="{{ route('accounts.daybook') }}" class="erp-hero-btn" title="{{ __('accounts.daybook_shortcut') }}">
                    <i class="bi bi-journal-text"></i>
                    <span>{{ __('accounts.daybook_shortcut') }}</span>
                </a>
                <a href="{{ route('accounts.index') }}" class="erp-hero-btn" title="{{ __('accounts.cash_bank_shortcut') }}">
                    <i class="bi bi-bank"></i>
                    <span>{{ __('accounts.cash_bank_shortcut') }}</span>
                </a>
            @endif
            @if($user->hasPermission('backup.view'))
                <a href="{{ route('maintenance.index') }}" class="erp-hero-btn" title="{{ __('reports.card_backup_title') }}">
                    <i class="bi bi-hdd-stack"></i>
                    <span>{{ __('reports.btn_backup_shortcut') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-3 mb-4">
    {{-- Card 1: YTD Procurement Value --}}
    <div class="col-6 col-lg-3">
        <a href="{{ route('reports.purchases') }}" class="text-decoration-none">
            <div class="erp-kpi-card emerald h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="erp-kpi-label">{{ __('reports.kpi_procurement_ytd') }}</span>
                    <span class="erp-kpi-badge bg-success-subtle text-success">
                        <i class="bi bi-basket"></i>
                    </span>
                </div>
                <div class="erp-kpi-value text-success font-monospace">
                    {{ money($metrics['totalPurchasesYtd']) }}
                </div>
                <div class="erp-kpi-sub text-truncate">
                    {{ __('reports.kpi_procurement_sub', ['count' => number_format($metrics['purchasesCount'])]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: YTD Sales Turnover --}}
    <div class="col-6 col-lg-3">
        <a href="{{ route('reports.sales') }}" class="text-decoration-none">
            <div class="erp-kpi-card blue h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="erp-kpi-label">{{ __('reports.kpi_sales_ytd') }}</span>
                    <span class="erp-kpi-badge bg-primary-subtle text-primary">
                        <i class="bi bi-receipt"></i>
                    </span>
                </div>
                <div class="erp-kpi-value text-primary font-monospace">
                    {{ money($metrics['totalSalesYtd']) }}
                </div>
                <div class="erp-kpi-sub text-truncate">
                    {{ __('reports.kpi_sales_sub', ['count' => number_format($metrics['salesCount'])]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Inventory Stock in Godowns --}}
    <div class="col-6 col-lg-3">
        <a href="{{ route('reports.inventory') }}" class="text-decoration-none">
            <div class="erp-kpi-card amber h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="erp-kpi-label">{{ __('reports.kpi_inventory_held') }}</span>
                    <span class="erp-kpi-badge bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-box-seam"></i>
                    </span>
                </div>
                <div class="erp-kpi-value text-warning-emphasis font-monospace">
                    {{ number_format((float)$metrics['totalStockQtl'], 1) }} <span class="fs-6 fw-normal text-muted">Qtl</span>
                </div>
                <div class="erp-kpi-sub text-truncate">
                    {{ __('reports.kpi_inventory_sub', ['count' => $metrics['activeWarehousesCount']]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Trading Network Reach --}}
    <div class="col-6 col-lg-3">
        <div class="erp-kpi-card h-100" style="border-left: 4px solid #8b5cf6;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="erp-kpi-label">{{ __('reports.kpi_network_reach') }}</span>
                <span class="erp-kpi-badge bg-purple-subtle text-primary">
                    <i class="bi bi-people-fill"></i>
                </span>
            </div>
            <div class="erp-kpi-value font-monospace" style="color: #6d28d9;">
                {{ number_format($metrics['activeFarmersCount'] + $metrics['activeCustomersCount']) }}
            </div>
            <div class="erp-kpi-sub text-truncate">
                {{ __('reports.kpi_network_sub', ['farmers' => number_format($metrics['activeFarmersCount']), 'customers' => number_format($metrics['activeCustomersCount'])]) }}
            </div>
        </div>
    </div>
</div>

{{-- 3. Categorized Analytical Modules Directory --}}
<div class="row g-4">
    {{-- 1. Procurement & Grain Purchases --}}
    @if($user->hasPermission('reports.purchase'))
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 bg-white rounded-3 overflow-hidden d-flex flex-column" style="border-top: 4px solid #10b981 !important;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-dark fs-6">
                        <i class="bi bi-basket-fill text-success fs-5"></i>
                        {{ __('reports.card_procurement_title') }}
                    </h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ __('reports.badge_purchases', ['count' => number_format($metrics['purchasesCount'])]) }}
                    </span>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <p class="small text-muted mb-3">
                            {{ __('reports.card_procurement_desc') }}
                        </p>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.proc_feature_1') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.proc_feature_2') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.proc_feature_3') }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.proc_feature_4') }}</span>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('reports.purchases') }}" class="btn btn-sm btn-success w-100 fw-semibold shadow-sm">
                        {{ __('reports.btn_open_purchases') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 2. Commercial Sales & Dispatch --}}
    @if($user->hasPermission('reports.sales'))
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 bg-white rounded-3 overflow-hidden d-flex flex-column" style="border-top: 4px solid #2563eb !important;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-dark fs-6">
                        <i class="bi bi-receipt-cutoff text-primary fs-5"></i>
                        {{ __('reports.card_sales_title') }}
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        {{ __('reports.badge_invoices', ['count' => number_format($metrics['salesCount'])]) }}
                    </span>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <p class="small text-muted mb-3">
                            {{ __('reports.card_sales_desc') }}
                        </p>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>{{ __('reports.sales_feature_1') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>{{ __('reports.sales_feature_2') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>{{ __('reports.sales_feature_3') }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>{{ __('reports.sales_feature_4') }}</span>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-primary w-100 fw-semibold shadow-sm">
                        {{ __('reports.btn_open_sales') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 3. Inventory & Godown Storage --}}
    @if($user->hasPermission('reports.inventory'))
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 bg-white rounded-3 overflow-hidden d-flex flex-column" style="border-top: 4px solid #f59e0b !important;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-dark fs-6">
                        <i class="bi bi-box-seam-fill text-warning fs-5"></i>
                        {{ __('reports.card_inventory_title') }}
                    </h5>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                        {{ __('reports.badge_warehouses', ['count' => $metrics['activeWarehousesCount']]) }}
                    </span>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <p class="small text-muted mb-3">
                            {{ __('reports.card_inventory_desc') }}
                        </p>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-warning"></i>
                                <span>{{ __('reports.inv_feature_1') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-warning"></i>
                                <span>{{ __('reports.inv_feature_2') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-warning"></i>
                                <span>{{ __('reports.inv_feature_3') }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-warning"></i>
                                <span>{{ __('reports.inv_feature_4') }}</span>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-warning w-100 fw-semibold text-dark shadow-sm">
                        {{ __('reports.btn_open_inventory') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 4. Finance & Outstanding Ledgers --}}
    @if($user->hasPermission('reports.finance'))
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 bg-white rounded-3 overflow-hidden d-flex flex-column" style="border-top: 4px solid #14462b !important;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-dark fs-6">
                        <i class="bi bi-cash-stack text-success fs-5"></i>
                        {{ __('reports.card_finance_title') }}
                    </h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ __('reports.badge_live_ledgers') }}
                    </span>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <p class="small text-muted mb-3">
                            {{ __('reports.card_finance_desc') }}
                        </p>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.fin_feature_1') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.fin_feature_2') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.fin_feature_3') }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ __('reports.fin_feature_4') }}</span>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('reports.finance') }}" class="btn btn-sm btn-outline-success w-100 fw-semibold shadow-sm">
                        {{ __('reports.btn_open_finance') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 5. Activity, Governance & Audit Trail --}}
    @if($user->hasPermission('reports.activity'))
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 bg-white rounded-3 overflow-hidden d-flex flex-column" style="border-top: 4px solid #64748b !important;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-dark fs-6">
                        <i class="bi bi-shield-check text-secondary fs-5"></i>
                        {{ __('reports.card_audit_title') }}
                    </h5>
                    <span class="badge bg-light text-dark border">
                        {{ __('reports.badge_audit_trail') }}
                    </span>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <p class="small text-muted mb-3">
                            {{ __('reports.card_audit_desc') }}
                        </p>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-secondary"></i>
                                <span>{{ __('reports.audit_feature_1') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-secondary"></i>
                                <span>{{ __('reports.audit_feature_2') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-secondary"></i>
                                <span>{{ __('reports.audit_feature_3') }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-secondary"></i>
                                <span>{{ __('reports.audit_feature_4') }}</span>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('reports.audit') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold shadow-sm">
                        {{ __('reports.btn_open_audit') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 6. Database Backup & System Maintenance --}}
    @if($user->hasPermission('backup.view'))
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 bg-white rounded-3 overflow-hidden d-flex flex-column" style="border-top: 4px solid #1e293b !important;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-dark fs-6">
                        <i class="bi bi-hdd-network-fill text-dark fs-5"></i>
                        {{ __('reports.card_backup_title') }}
                    </h5>
                    <span class="badge bg-light text-dark border">
                        {{ __('reports.badge_system_maintenance') }}
                    </span>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <p class="small text-muted mb-3">
                            {{ __('reports.card_backup_desc') }}
                        </p>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-dark"></i>
                                <span>{{ __('reports.backup_feature_1') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-dark"></i>
                                <span>{{ __('reports.backup_feature_2') }}</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-dark"></i>
                                <span>{{ __('reports.backup_feature_3') }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-dark"></i>
                                <span>{{ __('reports.backup_feature_4') }}</span>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-dark w-100 fw-semibold shadow-sm">
                        {{ __('reports.btn_open_backup') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
