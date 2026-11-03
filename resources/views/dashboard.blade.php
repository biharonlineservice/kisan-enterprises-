@extends('layouts.app')

@section('title', __('dashboard.title'))

@section('content')
@php
    $sym = setting('locale.currency_symbol', '₹');
    $formattedPhone = null;
    if (!empty($user->mobile) && strlen($user->mobile) === 10) {
        $formattedPhone = '+91 ' . substr($user->mobile, 0, 5) . ' ' . substr($user->mobile, 5);
    } elseif (!empty($user->mobile)) {
        $formattedPhone = $user->mobile;
    }
@endphp

{{-- 1. Executive Mandi Command Banner --}}
<div class="erp-hero-banner">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-shield-check text-warning"></i>
                    {{ __('app.role') }}: <strong>{{ $user->role?->display_name ?? 'Staff' }}</strong>
                </span>
                @if($formattedPhone)
                    <span class="erp-hero-chip">
                        <i class="bi bi-phone"></i> {{ $formattedPhone }}
                    </span>
                @endif
                <span class="erp-hero-chip">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex">
                    <i class="bi bi-people"></i>
                    {{ __('dashboard.active_network', ['farmers' => number_format($activeFarmersCount), 'buyers' => number_format($activeCustomersCount)]) }}
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('dashboard.welcome', ['name' => $user->name]) }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                {{ __('dashboard.command_subtitle') }}
                <span class="ms-1 opacity-75">&bull; {{ now()->format('d M Y, D') }}</span>
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="erp-hero-btn">
                <i class="bi bi-globe2"></i>
                <span>{{ __('dashboard.live_website') }}</span>
                <i class="bi bi-box-arrow-up-right" style="font-size: .7rem;"></i>
            </a>
            <a href="{{ route('reports.index') }}" class="erp-hero-btn gold">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>{{ __('dashboard.reports_hub') }}</span>
            </a>
        </div>
    </div>
</div>

{{-- 2. Quick Operations Strip (Swipeable on mobile, clean wrap on desktop) --}}
<div class="erp-quick-strip" role="region" aria-label="{{ __('dashboard.quick_actions_label') }}">
    @can('purchases.create')
        <a href="{{ route('purchases.create') }}" class="erp-action-tile primary-tile">
            <span class="tile-icon"><i class="bi bi-basket-fill"></i></span>
            <span>{{ __('dashboard.quick_new_purchase') }}</span>
        </a>
    @endcan

    @can('sales.create')
        <a href="{{ route('sales-invoices.create') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #E0F2FE; color: #0284C7;"><i class="bi bi-receipt"></i></span>
            <span>{{ __('dashboard.quick_new_invoice') }}</span>
        </a>
    @endcan

    @can('farmer_payments.create')
        <a href="{{ route('farmer-payments.create') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #DCFCE7; color: #15803D;"><i class="bi bi-cash-coin"></i></span>
            <span>{{ __('dashboard.quick_pay_farmer') }}</span>
        </a>
    @endcan

    @can('customer_receipts.create')
        <a href="{{ route('customer-receipts.create') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #CCFBF1; color: #0F766E;"><i class="bi bi-wallet2"></i></span>
            <span>{{ __('dashboard.quick_receive_money') }}</span>
        </a>
    @endcan

    @can('inventory.transfer')
        <a href="{{ route('stock-transfers.create') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #EEF2FF; color: #4338CA;"><i class="bi bi-truck"></i></span>
            <span>{{ __('dashboard.quick_stock_transfer') }}</span>
        </a>
    @endcan

    @can('expenses.create')
        <a href="{{ route('expenses.create') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #FEF3C7; color: #B45309;"><i class="bi bi-journal-minus"></i></span>
            <span>{{ __('dashboard.quick_add_expense') }}</span>
        </a>
    @endcan

    @can('farmers.create')
        <a href="{{ route('farmers.create') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #E8F3EC; color: #155B36;"><i class="bi bi-person-plus-fill"></i></span>
            <span>{{ __('dashboard.quick_add_farmer') }}</span>
        </a>
    @endcan

    @can('website.manage')
        <a href="{{ route('website.admin.index') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #FEF9C3; color: #A16207;"><i class="bi bi-graph-up-arrow"></i></span>
            <span>{{ __('dashboard.quick_update_rates') }}</span>
        </a>
    @elsecan('masters.manage')
        <a href="{{ route('crops.index') }}" class="erp-action-tile">
            <span class="tile-icon" style="background: #FEF9C3; color: #A16207;"><i class="bi bi-graph-up-arrow"></i></span>
            <span>{{ __('dashboard.quick_update_rates') }}</span>
        </a>
    @endcan
</div>

{{-- 3. Pending Drafts & Action Required Strip --}}
@php
    $hasActionAlerts = $draftPurchasesCount > 0
        || $draftSalesCount > 0
        || $draftOrdersCount > 0
        || $lowStockCrops->isNotEmpty()
        || ($pendingAdjustmentsCount > 0 && $user->hasPermission('inventory.approve'))
        || ($newInquiriesCount > 0 && $user->hasPermission('website.manage'));
@endphp
@if($hasActionAlerts)
    <div class="row g-2 mb-4">
        @if($draftPurchasesCount > 0)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="erp-alert-pill" style="--pill-color: #F4B740;">
                    <div class="small text-truncate">
                        <i class="bi bi-hourglass-split text-warning me-1"></i>
                        <strong>{{ $draftPurchasesCount }}</strong> {{ __('dashboard.draft_purchases') }}
                    </div>
                    <a href="{{ route('purchases.index', ['status' => 'draft']) }}" class="btn btn-xs btn-light border flex-shrink-0">{{ __('app.view') }}</a>
                </div>
            </div>
        @endif

        @if($draftSalesCount > 0)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="erp-alert-pill" style="--pill-color: #0284C7;">
                    <div class="small text-truncate">
                        <i class="bi bi-file-earmark-text text-primary me-1"></i>
                        <strong>{{ $draftSalesCount }}</strong> {{ __('dashboard.draft_sales') }}
                    </div>
                    <a href="{{ route('sales-invoices.index', ['status' => 'draft']) }}" class="btn btn-xs btn-light border flex-shrink-0">{{ __('app.view') }}</a>
                </div>
            </div>
        @endif

        @if($draftOrdersCount > 0)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="erp-alert-pill" style="--pill-color: #6366F1;">
                    <div class="small text-truncate">
                        <i class="bi bi-clipboard-check me-1" style="color: #6366F1;"></i>
                        <strong>{{ $draftOrdersCount }}</strong> {{ __('dashboard.draft_orders') }}
                    </div>
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-xs btn-light border flex-shrink-0">{{ __('app.view') }}</a>
                </div>
            </div>
        @endif

        @can('inventory.approve')
            @if($pendingAdjustmentsCount > 0)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="erp-alert-pill" style="--pill-color: #21864B;">
                        <div class="small text-truncate">
                            <i class="bi bi-sliders text-success me-1"></i>
                            <strong>{{ $pendingAdjustmentsCount }}</strong> {{ __('dashboard.pending_adjustments') }}
                        </div>
                        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-xs btn-outline-primary flex-shrink-0">{{ __('app.view') }}</a>
                    </div>
                </div>
            @endif
        @endcan

        @can('website.manage')
            @if($newInquiriesCount > 0)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="erp-alert-pill" style="--pill-color: #16A34A;">
                        <div class="small text-truncate">
                            <i class="bi bi-envelope-exclamation-fill text-success me-1"></i>
                            <strong>{{ $newInquiriesCount }}</strong> {{ __('dashboard.new_inquiries') }}
                        </div>
                        <a href="{{ route('website.admin.inquiries') }}" class="btn btn-xs btn-outline-success flex-shrink-0">{{ __('app.view') }}</a>
                    </div>
                </div>
            @endif
        @endcan

        @if($lowStockCrops->isNotEmpty())
            <div class="col-12 col-md-6 col-xl-4">
                <div class="erp-alert-pill" style="--pill-color: #D94848;">
                    <div class="small text-truncate">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                        <strong>{{ $lowStockCrops->count() }}</strong> {{ __('dashboard.low_stock_alerts', ['threshold' => bc_round((string) $lowStockThreshold, 0)]) }}
                    </div>
                    <a href="{{ route('reports.inventory', ['tab' => 'low_stock']) }}" class="btn btn-xs btn-outline-danger flex-shrink-0">{{ __('app.view') }}</a>
                </div>
            </div>
        @endif
    </div>
@endif

{{-- 4. Main KPI Metric Cards (8 Cards across 2 balanced rows on desktop, 2-col grid on mobile) --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- 1. Today's Purchases --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('purchases.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_today_purchases') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-basket-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ money($todayPurchasesAmount) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('dashboard.records_count', ['count' => $todayPurchasesCount]) }} &bull; {{ bc_round((string) $todayPurchasesQtl, 2) }} Qtl
                </div>
            </div>
        </a>
    </div>

    {{-- 2. Today's Sales --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('sales-invoices.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_today_sales') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-receipt"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ money($todaySalesAmount) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('dashboard.invoices_count', ['count' => $todaySalesCount]) }} &bull; {{ bc_round((string) $todaySalesQtl, 2) }} Qtl
                </div>
            </div>
        </a>
    </div>

    {{-- 3. Stock on Hand --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_total_stock') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-box-seam-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-dark text-truncate">
                    {{ bc_round((string) $totalStockQtl, 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span>
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('dashboard.est_bags', ['count' => number_format($totalStockBags)]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- 4. Cash & Bank Liquidity --}}
    <div class="col-6 col-xl-3">
        <a href="{{ $user->hasPermission('accounts.view') ? route('accounts.daybook') : '#' }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0F766E;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_liquidity') }}</span>
                    <span class="kpi-badge-icon" style="background: #CCFBF1; color: #0F766E;">
                        <i class="bi bi-bank2"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0F766E;">{{ money($totalLiquidity) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('dashboard.today_in_out', ['in' => money($todayReceipts), 'out' => money($todayDisbursements)]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- 5. Farmer Outstanding --}}
    <div class="col-6 col-xl-3">
        <a href="{{ $user->hasPermission('farmer_payments.view') ? route('farmer-payments.index') : route('purchases.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #D94848;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_farmer_outstanding') }}</span>
                    <span class="kpi-badge-icon" style="background: #FBECEC; color: #D94848;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-danger text-truncate">{{ money($farmerOutstandingTotal) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('dashboard.delayed_bills_count', ['count' => $delayedPurchasesCount]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- 6. Delayed Payment Bonus (Accruing & Paid) --}}
    <div class="col-6 col-xl-3">
        <a href="{{ $user->hasPermission('farmer_payments.create') ? route('farmer-payments.create') : ($user->hasPermission('farmer_payments.view') ? route('farmer-payments.index') : '#') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #16A34A;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_delayed_bonus') }}</span>
                    <span class="kpi-badge-icon" style="background: #DCFCE7; color: #15803D;">
                        <i class="bi bi-gift-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #15803D;">+{{ money($estimatedAccruedBonus) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('dashboard.bonus_paid_label', ['paid' => money($totalBonusPaid)]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- 7. Customer Receivables --}}
    <div class="col-6 col-xl-3">
        <a href="{{ $user->hasPermission('customer_receipts.view') ? route('customer-receipts.index') : '#' }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #D97706;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_customer_receivables') }}</span>
                    <span class="kpi-badge-icon" style="background: #FFF4E5; color: #D97706;">
                        <i class="bi bi-building-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #D97706;">{{ money($customerReceivablesTotal) }}</div>
                <div class="kpi-sub text-truncate">
                    @if($unpaidInvoicesCount > 0)
                        {{ __('dashboard.unpaid_invoices_count', ['count' => $unpaidInvoicesCount]) }}
                    @else
                        {{ __('dashboard.pending_receivables') }}
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- 8. Today's Expenses --}}
    <div class="col-6 col-xl-3">
        <a href="{{ $user->hasPermission('expenses.view') ? route('expenses.index') : '#' }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('dashboard.kpi_today_expenses') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-journal-minus"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ money($todayExpenses) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('dashboard.approved_today') }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 5. Current Project Feature: Live Mandi Crop Purchase Rates, Price Change Diffs & Trend Sparklines --}}
@if($mandiRateCards->isNotEmpty())
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2.5">
            <div class="min-w-0 pe-lg-3">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <span class="erp-live-dot flex-shrink-0"></span>
                    <span>{{ __('dashboard.mandi_rates_title') }}</span>
                </h6>
                <div class="text-muted small mt-1">
                    {{ __('dashboard.mandi_rates_subtitle') }}
                    @if($latestRateUpdatedAt)
                        <span class="badge bg-light text-dark border ms-sm-1 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-clock text-muted"></i> {{ __('dashboard.mandi_rates_updated', ['time' => \Carbon\Carbon::parse($latestRateUpdatedAt)->diffForHumans()]) }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 flex-shrink-0 flex-wrap pt-2 pt-lg-0">
                @can('website.manage')
                    <a href="{{ route('website.admin.index') }}" class="btn btn-xs btn-primary text-nowrap px-2.5 py-1">
                        <i class="bi bi-sliders me-1"></i>{{ __('dashboard.mandi_manage_rates') }}
                    </a>
                @endcan
                @can('masters.manage')
                    <a href="{{ route('crops.index') }}" class="btn btn-xs btn-light border text-nowrap px-2.5 py-1">
                        <i class="bi bi-flower1 me-1"></i>{{ __('dashboard.mandi_crop_masters') }}
                    </a>
                @endcan
                <a href="{{ route('home') }}#price-trend" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success text-nowrap px-2.5 py-1">
                    <i class="bi bi-graph-up-arrow me-1"></i>{{ __('dashboard.mandi_public_graph') }}
                </a>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                @foreach($mandiRateCards as $rc)
                    @php
                        /** @var \App\Models\Crop $rcCrop */
                        $rcCrop = $rc['crop'];
                        $trend = $rc['trend'] ?? [];
                        $dir = $trend['direction'] ?? 'new';
                        $change = $trend['change'] ?? null;
                        $changePct = $trend['change_pct'] ?? null;
                        $prevRate = $trend['previous'] ?? null;
                        $sparkline = $trend['sparkline'] ?? '';
                        $strokeColor = $dir === 'down' ? '#DC2626' : '#16A34A';
                        $isLowCropStock = $rc['stock_qtl'] < $lowStockThreshold;
                    @endphp
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="erp-mandi-card">
                            <div>
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark lh-sm">{{ $rcCrop->name }}</div>
                                        <div class="text-muted" style="font-size: .7rem;">{{ $rcCrop->code }}@if($rcCrop->hsn_code) &bull; HSN {{ $rcCrop->hsn_code }}@endif</div>
                                    </div>
                                    @if($rc['is_public'])
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle flex-shrink-0" style="font-size: .66rem;">
                                            {{ __('dashboard.rate_live_badge') }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-light text-muted border flex-shrink-0" style="font-size: .66rem;">
                                            {{ __('dashboard.rate_hidden_badge') }}
                                        </span>
                                    @endif
                                </div>

                                @if($rc['has_rate'])
                                    <div class="erp-mandi-price mb-2">
                                        {{ $rc['formatted_rate'] }}
                                        <span class="fw-normal text-muted" style="font-size: .76rem;">/ {{ $rc['unit_label'] }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <div>
                                            @if($dir === 'up' && $change !== null)
                                                <span class="erp-diff-pill up">
                                                    ▲ +{{ $sym }}{{ indian_number(abs($change), $change != floor($change) ? 2 : 0) }}@if($changePct !== null) (+{{ abs($changePct) }}%)@endif
                                                </span>
                                            @elseif($dir === 'down' && $change !== null)
                                                <span class="erp-diff-pill down">
                                                    ▼ -{{ $sym }}{{ indian_number(abs($change), $change != floor($change) ? 2 : 0) }}@if($changePct !== null) (-{{ abs($changePct) }}%)@endif
                                                </span>
                                            @elseif($dir === 'same')
                                                <span class="erp-diff-pill same">= {{ __('dashboard.rate_no_change') }}</span>
                                            @else
                                                <span class="erp-diff-pill new">{{ __('dashboard.rate_first_entry') }}</span>
                                            @endif
                                        </div>

                                        @if(!empty($sparkline))
                                            <div class="flex-shrink-0" title="90-Day Price Trend">
                                                <svg width="78" height="24" viewBox="0 0 100 28" fill="none" aria-hidden="true">
                                                    <polyline
                                                        points="{{ $sparkline }}"
                                                        fill="none"
                                                        stroke="{{ $strokeColor }}"
                                                        stroke-width="2.6"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="fw-semibold text-muted py-2">{{ __('dashboard.rate_not_set') }}</div>
                                @endif
                            </div>

                            <div class="d-flex align-items-center justify-content-between gap-2 pt-2 mt-2 border-top" style="font-size: .74rem;">
                                <span class="text-muted text-truncate">
                                    @if($prevRate !== null && $prevRate > 0)
                                        {{ __('dashboard.rate_prev_label', ['rate' => $sym . indian_number($prevRate, $prevRate != floor($prevRate) ? 2 : 0)]) }}
                                    @elseif(!empty($trend['low']) && !empty($trend['high']) && $rc['has_rate'])
                                        {{ __('dashboard.rate_90d_range', ['low' => $sym . indian_number($trend['low'], 0), 'high' => $sym . indian_number($trend['high'], 0)]) }}
                                    @else
                                        HSN {{ $rcCrop->hsn_code ?: '—' }}
                                    @endif
                                </span>
                                <span class="badge {{ $isLowCropStock ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-light text-dark border' }}">
                                    {{ __('dashboard.crop_stock_chip', ['qtl' => bc_round((string) $rc['stock_qtl'], 1)]) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

{{-- 6. Operational Spotlight: Delayed Farmer Payments (Bonus Accrual) & Recent Dispatches / Purchases --}}
<div class="row g-4 mb-4">
    {{-- Delayed Farmer Payments & Accruing Bonus --}}
    <div class="col-xl-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="kpi-badge-icon" style="width: 30px; height: 30px; font-size: .95rem; background: #DCFCE7; color: #15803D; border-radius: 8px; display: inline-grid; place-items: center;">
                        <i class="bi bi-gift-fill"></i>
                    </span>
                    <h6 class="card-title mb-0 fw-bold">{{ __('dashboard.delayed_payments_title') }}</h6>
                    @if($delayedPurchasesCount > 0)
                        <span class="badge rounded-pill bg-danger-subtle text-danger">{{ $delayedPurchasesCount }}</span>
                    @endif
                </div>
                @can('farmer_payments.create')
                    <a href="{{ route('farmer-payments.create') }}" class="btn btn-xs btn-success">
                        <i class="bi bi-cash-coin"></i> {{ __('dashboard.quick_pay_farmer') }}
                    </a>
                @endcan
            </div>

            <div class="card-body p-0">
                @if($delayedPurchasesList->isEmpty())
                    <div class="p-4 text-center text-muted small">
                        <i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>
                        {{ __('dashboard.delayed_payments_empty') }}
                    </div>
                @else
                    {{-- Desktop Table View --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-sm align-middle mb-0 w-100" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2 text-nowrap">{{ __('dashboard.col_purchase_farmer') }}</th>
                                    <th class="py-2 text-nowrap">{{ __('dashboard.col_delay_rate') }}</th>
                                    <th class="text-end py-2 text-nowrap">{{ __('dashboard.col_balance_due') }}</th>
                                    <th class="text-end py-2 text-nowrap">{{ __('dashboard.col_est_bonus') }}</th>
                                    <th class="text-end pe-3 py-2 text-nowrap" style="min-width: 65px;">{{ __('dashboard.col_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delayedPurchasesList as $item)
                                    @php $dp = $item['purchase']; @endphp
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <a href="{{ route('purchases.show', $dp) }}" class="fw-bold text-decoration-none text-nowrap">{{ $dp->purchase_number }}</a>
                                            <div class="text-muted text-truncate" style="max-width: 175px;" title="{{ $dp->farmer?->name }} ({{ $dp->crop?->name }})">
                                                {{ $dp->farmer?->name }} <span class="text-secondary opacity-75">({{ $dp->crop?->name }})</span>
                                            </div>
                                        </td>
                                        <td class="py-2 text-nowrap">
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-1.5 py-0.5">{{ $item['days_delayed'] }}d</span>
                                            @if($item['bonus_enabled'])
                                                <span class="badge {{ $item['is_custom'] ? 'bg-success text-white' : 'bg-light text-success border' }} px-1.5 py-0.5" style="font-size: 0.72rem;">
                                                    {{ (float) $item['bonus_rate'] }}% {{ $item['bonus_mode'] === 'monthly' ? 'Mo' : ucfirst($item['bonus_mode']) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary px-1.5 py-0.5" style="font-size: 0.72rem;">{{ __('dashboard.bonus_off') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold text-danger num text-nowrap py-2">{{ money($dp->balance_due) }}</td>
                                        <td class="text-end fw-bold text-success num text-nowrap py-2">+{{ money($item['accrued_bonus']) }}</td>
                                        <td class="text-end pe-3 text-nowrap py-2">
                                            @can('farmer_payments.create')
                                                <a href="{{ route('farmer-payments.create', ['purchase_id' => $dp->id]) }}" class="btn btn-xs btn-success px-2 py-0.5 shadow-sm text-nowrap">
                                                    {{ __('dashboard.btn_pay_now') }}
                                                </a>
                                            @else
                                                <a href="{{ route('purchases.show', $dp) }}" class="btn btn-xs btn-outline-secondary px-2 py-0.5 text-nowrap">
                                                    {{ __('app.view') }}
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Native Card List (Zero Horizontal Scroll on Phones) --}}
                    <div class="mobile-card-list p-3 d-md-none">
                        @foreach($delayedPurchasesList as $item)
                            @php $dp = $item['purchase']; @endphp
                            <div class="mobile-record-card p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <a href="{{ route('purchases.show', $dp) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $dp->purchase_number }}
                                        </a>
                                        <div class="small text-muted">{{ $dp->farmer?->name }} &bull; {{ $dp->crop?->name }}</div>
                                    </div>
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle">{{ $item['days_delayed'] }}d</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <div>
                                        <span class="text-muted d-block" style="font-size: .72rem;">{{ __('dashboard.col_balance_due') }}</span>
                                        <span class="fw-bold text-danger">{{ money($dp->balance_due) }}</span>
                                        <span class="text-success fw-bold ms-1">(+{{ money($item['accrued_bonus']) }})</span>
                                    </div>
                                    <div>
                                        @can('farmer_payments.create')
                                            <a href="{{ route('farmer-payments.create', ['purchase_id' => $dp->id]) }}" class="btn btn-xs btn-success">
                                                <i class="bi bi-cash-coin"></i> {{ __('dashboard.btn_pay_now') }}
                                            </a>
                                        @else
                                            <a href="{{ route('purchases.show', $dp) }}" class="btn btn-xs btn-outline-secondary">
                                                {{ __('app.view') }}
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Dispatches & Transport Challans + Recent Weighbridge Purchases Tabs --}}
    <div class="col-xl-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <span class="kpi-badge-icon" style="width: 30px; height: 30px; font-size: .95rem; background: #E0F2FE; color: #0284C7; border-radius: 8px; display: inline-grid; place-items: center;">
                        <i class="bi bi-truck"></i>
                    </span>
                    <span>{{ __('dashboard.recent_challans_title') }}</span>
                </h6>

                <ul class="nav erp-tab-pills gap-1" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0" id="tab-challans-btn" data-bs-toggle="tab" data-bs-target="#paneChallans" type="button" role="tab" aria-selected="true">
                            {{ __('dashboard.recent_challans_tab') }} ({{ $recentChallans->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0" id="tab-purchases-btn" data-bs-toggle="tab" data-bs-target="#panePurchases" type="button" role="tab" aria-selected="false">
                            {{ __('dashboard.recent_purchases_tab') }} ({{ $recentPurchases->count() }})
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0 tab-content">
                {{-- Tab 1: Transport Challans --}}
                <div class="tab-pane fade show active" id="paneChallans" role="tabpanel" aria-labelledby="tab-challans-btn">
                    @if($recentChallans->isEmpty())
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-truck fs-3 text-muted d-block mb-1"></i>
                            {{ __('app.no_records') }}
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentChallans as $ch)
                                <div class="list-group-item d-flex justify-content-between align-items-center gap-2 py-2 px-3 small">
                                    <div class="overflow-hidden">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span class="badge {{ $ch['type'] === 'sale' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-dark' }}">
                                                {{ $ch['challan_number'] }}
                                            </span>
                                            <a href="{{ $ch['doc_url'] }}" class="fw-bold text-decoration-none text-dark text-truncate">{{ $ch['party_or_route'] }}</a>
                                        </div>
                                        <div class="text-muted mt-1 d-flex flex-wrap align-items-center gap-1">
                                            <span>{{ fmt_date($ch['date']) }}</span>
                                            <span>&bull; {{ bc_round((string) $ch['qty_qtl'], 2) }} Qtl</span>
                                            @if($ch['vehicle'])
                                                <span class="badge bg-light text-dark border fw-semibold" style="font-size: .7rem;">
                                                    <i class="bi bi-truck"></i> {{ $ch['vehicle'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ $ch['challan_url'] }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Print Transport Challan">
                                            <i class="bi bi-printer"></i> {{ __('dashboard.btn_challan') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tab 2: Recent Finalized Purchases --}}
                <div class="tab-pane fade" id="panePurchases" role="tabpanel" aria-labelledby="tab-purchases-btn">
                    @if($recentPurchases->isEmpty())
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-basket fs-3 text-muted d-block mb-1"></i>
                            {{ __('app.no_records') }}
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentPurchases as $rp)
                                <div class="list-group-item d-flex justify-content-between align-items-center gap-2 py-2 px-3 small">
                                    <div class="overflow-hidden">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <a href="{{ route('purchases.show', $rp) }}" class="badge bg-success-subtle text-success text-decoration-none">
                                                {{ $rp->purchase_number }}
                                            </a>
                                            <span class="fw-bold text-dark text-truncate">{{ $rp->farmer?->name ?? '-' }}</span>
                                        </div>
                                        <div class="text-muted mt-1">
                                            {{ fmt_date($rp->purchase_date) }}
                                            &bull; {{ $rp->crop?->name }} ({{ bc_round((string) $rp->net_weight_qtl, 2) }} Qtl)
                                            &bull; <strong class="text-dark">{{ money($rp->net_payable_amount) }}</strong>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('purchases.receipt', $rp) }}" target="_blank" class="btn btn-xs btn-outline-success">
                                            <i class="bi bi-printer"></i> {{ __('dashboard.btn_receipt') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 7. Visual Analytics Charts & Godown Capacity --}}
<div class="row g-4 mb-4">
    {{-- Purchases vs Sales Monthly Trend --}}
    <div class="col-xl-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-steps text-primary"></i>
                    <span>{{ __('dashboard.chart_purchases_sales') }}</span>
                </h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ __('dashboard.chart_6m_purchases', ['amount' => money($sixMonthPurchasesTotal)]) }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        {{ __('dashboard.chart_6m_sales', ['amount' => money($sixMonthSalesTotal)]) }}
                    </span>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div style="position: relative; height: 280px;">
                    <canvas id="procurementSalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Distribution by Crop & Warehouse Capacity --}}
    <div class="col-xl-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart-fill text-success"></i>
                    <span>{{ __('dashboard.chart_stock_crop') }}</span>
                </h6>
                @can('inventory.view')
                    <a href="{{ route('inventory.index') }}" class="btn btn-xs btn-light border">{{ __('app.view') }}</a>
                @endcan
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column align-items-center justify-content-between">
                @if(count($stockByCropData) > 0)
                    <div class="w-100 d-flex justify-content-center" style="position: relative; height: 205px;">
                        <canvas id="stockDistributionChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-box-seam fs-2 text-muted d-block mb-2"></i>
                        {{ __('app.no_records') }}
                    </div>
                @endif

                @if($warehouseStats->isNotEmpty())
                    <div class="w-100 mt-3 pt-3 border-top">
                        <div class="small fw-bold text-muted mb-2 d-flex justify-content-between align-items-center">
                            <span>{{ __('dashboard.warehouse_stock_title') }}</span>
                            <span class="text-dark">{{ bc_round((string) $totalStockQtl, 1) }} Qtl</span>
                        </div>
                        @foreach($warehouseStats as $ws)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center small gap-2">
                                    <span class="text-truncate">
                                        {{ $ws['warehouse']->name }}
                                        @if(!empty($ws['is_transit']))
                                            <span class="badge bg-light text-muted border ms-1" style="font-size: .65rem;">{{ __('dashboard.transit_badge') }}</span>
                                        @endif
                                    </span>
                                    <span class="fw-semibold flex-shrink-0" style="font-variant-numeric: tabular-nums;">
                                        {{ bc_round((string) $ws['stock_qtl'], 1) }}@if($ws['capacity_qtl'] > 0 && empty($ws['is_transit'])) / {{ bc_round((string) $ws['capacity_qtl'], 0) }}@endif Qtl
                                    </span>
                                </div>
                                @if($ws['utilization_pct'] !== null && empty($ws['is_transit']))
                                    <div class="progress mt-1" style="height: 5px; background: #EDF2EE;">
                                        <div class="progress-bar {{ $ws['utilization_pct'] > 85 ? 'bg-danger' : ($ws['utilization_pct'] > 65 ? 'bg-warning' : 'bg-success') }}"
                                             role="progressbar"
                                             style="width: {{ max($ws['stock_qtl'] > 0 ? 3 : 0, $ws['utilization_pct']) }}%;"></div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 8. Top Partners & Live Cash / Bank Liquidity --}}
<div class="row g-4 mb-4">
    {{-- Top Farmers by Volume --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-award-fill text-success"></i>
                    <span>{{ __('dashboard.top_farmers') }}</span>
                </h6>
                <a href="{{ route('reports.purchases') }}" class="btn btn-xs btn-light border">{{ __('app.view') }}</a>
            </div>
            <div class="card-body p-0">
                @if($topFarmers->isEmpty())
                    <div class="p-4 text-center text-muted small">{{ __('app.no_records') }}</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($topFarmers as $idx => $tf)
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2 py-2 px-3">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span class="erp-rank-badge rank-{{ $idx + 1 }}">#{{ $idx + 1 }}</span>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold small text-truncate">
                                            @if($tf->farmer)
                                                <a href="{{ route('farmers.show', $tf->farmer) }}" class="text-decoration-none text-dark">{{ $tf->farmer->name }}</a>
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <small class="text-muted d-block text-truncate">{{ $tf->farmer?->village }} ({{ $tf->farmer?->farmer_id }})</small>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <span class="badge bg-success-subtle text-success">{{ bc_round((string) $tf->total_qtl, 1) }} Qtl</span>
                                    <small class="d-block text-muted" style="font-variant-numeric: tabular-nums;">{{ money($tf->total_amount) }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Top Customers by Revenue --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-buildings-fill text-primary"></i>
                    <span>{{ __('dashboard.top_customers') }}</span>
                </h6>
                <a href="{{ route('reports.sales') }}" class="btn btn-xs btn-light border">{{ __('app.view') }}</a>
            </div>
            <div class="card-body p-0">
                @if($topCustomers->isEmpty())
                    <div class="p-4 text-center text-muted small">{{ __('app.no_records') }}</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($topCustomers as $idx => $tc)
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2 py-2 px-3">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span class="erp-rank-badge rank-{{ $idx + 1 }}">#{{ $idx + 1 }}</span>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold small text-truncate">
                                            @if($tc->customer)
                                                <a href="{{ route('customers.show', $tc->customer) }}" class="text-decoration-none text-dark">{{ $tc->customer->name }}</a>
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <small class="text-muted d-block text-truncate">{{ $tc->customer?->contact_person ?: $tc->customer?->city }}</small>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <span class="badge bg-primary-subtle text-primary" style="font-variant-numeric: tabular-nums;">{{ money($tc->total_revenue) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Live Cash & Bank Balances --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100 d-flex flex-column">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 text-dark"></i>
                    <span>{{ __('accounts.cash_bank') }}</span>
                </h6>
                @can('accounts.view')
                    <a href="{{ route('accounts.index') }}" class="btn btn-xs btn-light border">{{ __('app.view') }}</a>
                @endcan
            </div>
            <div class="card-body p-0 flex-grow-1">
                @if($cashAccounts->isEmpty())
                    <div class="p-4 text-center text-muted small">{{ __('app.no_records') }}</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($cashAccounts as $acc)
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2 py-2 px-3">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span class="erp-rank-badge" style="background: {{ $acc->account_type === 'cash' ? '#E8F3EC' : '#E0F2FE' }}; color: {{ $acc->account_type === 'cash' ? '#155B36' : '#0284C7' }};">
                                        <i class="bi {{ $acc->account_type === 'cash' ? 'bi-cash-stack' : 'bi-bank' }}"></i>
                                    </span>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold small text-truncate">{{ $acc->name }}</div>
                                        <small class="text-muted text-capitalize d-block text-truncate">
                                            {{ $acc->account_type }} &bull; {{ $acc->account_number ?: __('dashboard.physical_drawer') }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end fw-bold text-dark flex-shrink-0" style="font-variant-numeric: tabular-nums;">
                                    {{ money($acc->current_balance) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @if($cashAccounts->isNotEmpty())
                <div class="card-footer d-flex justify-content-between align-items-center py-2 px-3 small">
                    <span class="text-muted fw-semibold">{{ __('dashboard.total_liquidity_footer') }}</span>
                    <strong class="text-success" style="font-variant-numeric: tabular-nums;">{{ money($totalLiquidity) }}</strong>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- 9. Setup Checklist for Admin & Recent System Audit Feed --}}
<div class="row g-4">
    @if(count($setup) > 0)
        @php
            $setupDoneCount = collect($setup)->filter(fn ($row) => !empty($row[1]))->count();
            $setupTotalCount = count($setup);
            $setupPct = $setupTotalCount > 0 ? round(($setupDoneCount / $setupTotalCount) * 100) : 100;
        @endphp
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-check2-circle text-success"></i>
                            <span>{{ __('dashboard.setup_checklist') }}</span>
                            <span class="badge bg-success-subtle text-success">{{ $setupDoneCount }}/{{ $setupTotalCount }}</span>
                        </h6>
                    </div>
                    @if(count($userStats) > 0)
                        <span class="badge bg-light text-dark border small">
                            {{ __('dashboard.active_users') }}: {{ $userStats['active'] }}
                            @if($userStats['failedLogins'] > 0)
                                &bull; <span class="text-danger">{{ __('dashboard.failed_logins') }}: {{ $userStats['failedLogins'] }}</span>
                            @endif
                        </span>
                    @endif
                </div>
                <div class="px-3 pt-2 pb-1 bg-light border-bottom">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $setupPct }}%;"></div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($setup as [$label, $done, $link])
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2 py-2 px-3">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <i class="bi {{ $done ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                                    <span class="small {{ $done ? 'text-dark' : 'text-muted' }} text-truncate">{{ $label }}</span>
                                </div>
                                @if(! $done)
                                    <a href="{{ $link }}" class="btn btn-xs btn-outline-primary flex-shrink-0">{{ __('dashboard.setup_complete_btn') }} &rarr;</a>
                                @else
                                    <span class="badge bg-success-subtle text-success flex-shrink-0">{{ __('dashboard.setup_done_badge') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ count($setup) > 0 ? 'col-lg-6' : 'col-12' }}">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary"></i>
                    <span>{{ __('dashboard.recent_activity') }}</span>
                </h6>
                @can('audit.view')
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-xs btn-light border">{{ __('app.view') }} &rarr;</a>
                @endcan
            </div>
            <div class="card-body p-0">
                @if($recentActivity->isEmpty())
                    <div class="p-4 text-center text-muted small">{{ __('app.no_records') }}</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity as $act)
                            @php
                                $actPrefix = strtok((string) $act->action, '.');
                                $dotColor = match ($actPrefix) {
                                    'purchase', 'farmer_payment' => '#21864B',
                                    'sale', 'sales_invoice', 'customer_receipt' => '#0284C7',
                                    'auth' => str_contains((string) $act->action, 'failed') ? '#DC2626' : '#0F766E',
                                    'website', 'crop' => '#D97706',
                                    default => '#64748B',
                                };
                                $actorName = $act->user?->name ?? __('dashboard.system_actor');
                            @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2 py-2 px-3 small">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $dotColor }}; flex-shrink: 0;"></span>
                                    <div class="text-truncate">
                                        <span class="fw-semibold text-dark">{{ $actorName }}</span>
                                        <span class="text-muted ms-1">{{ __('dashboard.performed_label') }}</span>
                                        <code class="ms-1 px-1 py-0 rounded bg-light border text-dark">{{ $act->action }}</code>
                                    </div>
                                </div>
                                <span class="text-muted flex-shrink-0" style="font-size: 0.74rem;">{{ $act->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Purchases vs Sales Bar Chart
        const psCtx = document.getElementById('procurementSalesChart');
        if (psCtx) {
            new Chart(psCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: "{{ app()->getLocale() === 'hi' ? 'फसल खरीद (₹)' : 'Procurement Purchases (₹)' }}",
                            data: @json($chartPurchases),
                            backgroundColor: 'rgba(33, 134, 75, 0.82)',
                            borderColor: '#21864B',
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 34,
                        },
                        {
                            label: "{{ app()->getLocale() === 'hi' ? 'बिक्री राजस्व (₹)' : 'Sales Revenue (₹)' }}",
                            data: @json($chartSales),
                            backgroundColor: 'rgba(2, 132, 199, 0.78)',
                            borderColor: '#0284C7',
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 34,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: 10000,
                            border: { dash: [4, 4] },
                            grid: { color: '#EDF2EE' },
                            ticks: {
                                precision: 0,
                                callback: function(value) {
                                    return '₹' + Number(value).toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { usePointStyle: true, boxWidth: 8 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Stock Distribution Doughnut Chart
        const sdCtx = document.getElementById('stockDistributionChart');
        if (sdCtx && @json($stockByCropData).length > 0) {
            new Chart(sdCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($stockByCropLabels),
                    datasets: [{
                        data: @json($stockByCropData),
                        backgroundColor: [
                            '#21864B',
                            '#F4B740',
                            '#0284C7',
                            '#155B36',
                            '#0F766E',
                            '#E67E22'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, boxWidth: 8, padding: 12 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + Number(context.raw).toLocaleString('en-IN') + ' Qtl';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
