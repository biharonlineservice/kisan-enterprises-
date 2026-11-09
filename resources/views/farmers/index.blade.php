@extends('layouts.app')

@section('title', __('procurement.farmers.title'))

@section('content')
@php
    $globalBonusEnabled = setting('procurement.delayed_bonus_enabled', '1') == '1';
    $globalBonusRate = bc_round((string) setting('procurement.delayed_bonus_rate', '1.00'), 2);
    $globalBonusMode = ucfirst((string) setting('procurement.delayed_bonus_mode', 'monthly'));
    $hasFilters = request()->anyFilled(['q', 'village', 'block', 'status', 'due', 'bonus']);
@endphp

{{-- 1. Executive Farmer Directory Command Banner --}}
<div class="erp-hero-banner">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-people-fill text-warning"></i>
                    <strong>{{ number_format($summary['active']) }}</strong> / {{ number_format($summary['total']) }} {{ __('app.active') }}
                </span>
                <span class="erp-hero-chip">
                    <i class="bi bi-geo-alt-fill"></i>
                    <strong>{{ number_format($summary['villages_count']) }}</strong> {{ __('procurement.farmers.village') }}
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex">
                    <i class="bi bi-gift-fill text-warning"></i>
                    @if($globalBonusEnabled)
                        {{ __('procurement.farmers.col_delayed_bonus') }}: <strong>{{ $globalBonusRate }}% {{ $globalBonusMode }}</strong>
                    @else
                        {{ __('procurement.farmers.col_delayed_bonus') }}: <strong>{{ __('procurement.farmers.bonus_disabled') }}</strong>
                    @endif
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('procurement.farmers.title') }}</h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                {{ __('procurement.farmers.directory_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 flex-shrink-0">
            <a href="{{ route('website.farmer.register') }}" target="_blank" rel="noopener" class="erp-hero-btn">
                <i class="bi bi-globe2"></i>
                <span>{{ __('procurement.farmers.online_portal') }}</span>
                <i class="bi bi-box-arrow-up-right" style="font-size: .7rem;"></i>
            </a>
            @can('farmers.export')
                <a href="{{ route('farmers.export', request()->all()) }}" class="erp-hero-btn">
                    <i class="bi bi-download"></i>
                    <span>{{ __('app.export') }}</span>
                </a>
            @endcan
            @can('farmers.create')
                <a href="{{ route('farmers.create') }}" class="erp-hero-btn gold">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>{{ __('procurement.farmers.add_new') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #21864B;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.farmers.kpi_total_farmers') }}</span>
                <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                    <i class="bi bi-people-fill"></i>
                </span>
            </div>
            <div class="kpi-value text-dark">{{ number_format($summary['total']) }}</div>
            <div class="kpi-sub text-truncate">
                {{ __('procurement.farmers.kpi_active_split', ['active' => number_format($summary['active']), 'inactive' => number_format($summary['inactive'])]) }}
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="erp-kpi-card" style="--kpi-accent: #0284C7;">
            <div class="kpi-top">
                <span class="kpi-label text-truncate">{{ __('procurement.farmers.kpi_supplied_volume') }}</span>
                <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                    <i class="bi bi-basket-fill"></i>
                </span>
            </div>
            <div class="kpi-value" style="color: #0284C7;">
                {{ bc_round((string) $summary['total_qtl'], 2) }} <span class="fs-6 fw-normal text-muted">Qtl</span>
            </div>
            <div class="kpi-sub text-truncate">
                {{ __('procurement.farmers.kpi_slips_count', ['count' => number_format($summary['total_slips'])]) }}
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ route('farmers.index', ['due' => 'pending']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: {{ $summary['total_payable'] > 0 ? '#D94848' : '#16A34A' }};">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('procurement.farmers.kpi_net_payable') }}</span>
                    <span class="kpi-badge-icon" style="background: {{ $summary['total_payable'] > 0 ? '#FBECEC' : '#DCFCE7' }}; color: {{ $summary['total_payable'] > 0 ? '#D94848' : '#15803D' }};">
                        <i class="bi bi-cash-coin"></i>
                    </span>
                </div>
                <div class="kpi-value {{ $summary['total_payable'] > 0 ? 'text-danger' : 'text-success' }} text-truncate">
                    {{ money($summary['total_payable']) }}
                </div>
                <div class="kpi-sub text-truncate">
                    {{ __('procurement.farmers.kpi_due_farmers', ['count' => number_format($summary['farmers_with_due'])]) }}
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ route('farmers.index', ['bonus' => 'custom']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('procurement.farmers.kpi_village_reach') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-dark">{{ number_format($summary['villages_count']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('procurement.farmers.kpi_custom_bonus', ['count' => number_format($summary['custom_bonus_count'])]) }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Farmer Directory Card (Quick Filter Tabs + Search/Filter Bar + Responsive Views) --}}
<div class="card shadow-sm border-0 mb-4">
    {{-- Quick Segment Filter Strip --}}
    <div class="px-3 pt-3 pb-2 border-bottom d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-2 bg-white rounded-top">
        <div class="d-flex flex-nowrap overflow-x-auto align-items-center gap-1 erp-tab-pills pb-1 pb-xl-0 flex-grow-1" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            @php
                $isAllTab = !request()->filled('status') && !request()->filled('due') && !request()->filled('bonus');
            @endphp
            <a href="{{ route('farmers.index', request()->except(['status', 'due', 'bonus', 'page'])) }}"
               class="nav-link text-nowrap {{ $isAllTab ? 'active' : '' }}">
                {{ __('procurement.farmers.filter_all_farmers') }} ({{ $summary['total'] }})
            </a>
            <a href="{{ route('farmers.index', array_merge(request()->except(['due', 'bonus', 'page']), ['status' => 'active'])) }}"
               class="nav-link text-nowrap {{ request('status') === 'active' ? 'active' : '' }}">
                {{ __('procurement.farmers.filter_active_only') }} ({{ $summary['active'] }})
            </a>
            <a href="{{ route('farmers.index', array_merge(request()->except(['status', 'bonus', 'page']), ['due' => 'pending'])) }}"
               class="nav-link text-nowrap {{ request('due') === 'pending' ? 'active' : '' }}">
                <i class="bi bi-exclamation-circle text-danger me-1"></i>{{ __('procurement.farmers.filter_pending_dues') }} ({{ $summary['farmers_with_due'] }})
            </a>
            <a href="{{ route('farmers.index', array_merge(request()->except(['status', 'due', 'page']), ['bonus' => 'custom'])) }}"
               class="nav-link text-nowrap {{ request('bonus') === 'custom' ? 'active' : '' }}">
                <i class="bi bi-gift text-success me-1"></i>{{ __('procurement.farmers.filter_custom_bonus') }} ({{ $summary['custom_bonus_count'] }})
            </a>
        </div>

        <div class="small text-muted">
            {{ $farmers->total() }} {{ __('procurement.farmers.title') }}
        </div>
    </div>

    {{-- Search & Filter Form --}}
    <div class="p-3 bg-light bg-opacity-50 border-bottom erp-filter-collapsible">
        <form method="GET" action="{{ route('farmers.index') }}" class="row g-2 align-items-end" data-autosubmit>
            @if(request()->filled('bonus'))
                <input type="hidden" name="bonus" value="{{ request('bonus') }}">
            @endif

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           class="form-control form-control-sm border-start-0"
                           placeholder="{{ __('procurement.farmers.search_placeholder') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.farmers.village') }}</label>
                <select name="village" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($villages as $v)
                        <option value="{{ $v }}" {{ request('village') === $v ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.farmers.block_tehsil') }}</label>
                <select name="block" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($blocks as $b)
                        <option value="{{ $b }}" {{ request('block') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('procurement.farmers.filter_balance') }}</label>
                <select name="due" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="pending" {{ request('due') === 'pending' ? 'selected' : '' }}>{{ __('procurement.farmers.balance_has_due') }}</option>
                    <option value="settled" {{ request('due') === 'settled' ? 'selected' : '' }}>{{ __('procurement.farmers.balance_settled') }}</option>
                </select>
            </div>

            <div class="col-6 col-md-1">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('app.status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1 d-none d-md-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold shadow-sm flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('farmers.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('app.reset') }}">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Desktop Table View (>= 768px) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover table-compact-erp align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">{{ __('procurement.farmers.name') }}</th>
                    <th>{{ __('procurement.farmers.mobile') }}</th>
                    <th>{{ __('procurement.farmers.village') }}</th>
                    <th class="num">{{ __('procurement.farmers.col_supplied') }}</th>
                    <th>{{ __('procurement.farmers.col_delayed_bonus') }}</th>
                    <th class="num">{{ __('procurement.farmers.col_net_payable') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end pe-3">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($farmers as $farmer)
                    @php
                        $suppliedQtl = (float) ($farmer->finalized_qtl_sum ?? 0);
                        $slipsCount = (int) ($farmer->finalized_purchases_count ?? 0);
                        $purchaseDue = (float) ($farmer->purchases_due_sum ?? 0);
                        $openingBal = (float) ($farmer->opening_balance ?? 0);
                        $netPayable = round($openingBal + $purchaseDue, 2);
                        $cleanDigits = $farmer->mobile ? preg_replace('/\D+/', '', (string) $farmer->mobile) : '';
                    @endphp
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar flex-shrink-0">
                                    {{ mb_strtoupper(mb_substr($farmer->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('farmers.show', $farmer) }}" class="fw-bold text-dark text-decoration-none d-inline-block">
                                        {{ $farmer->name }}
                                    </a>
                                    <div class="d-flex flex-wrap align-items-center gap-1 mt-0">
                                        <code class="small text-muted">{{ $farmer->farmer_id }}</code>
                                        @can('farmers.bank')
                                            @if(!empty($farmer->bank_account_number))
                                                <span class="badge bg-info-subtle text-primary border border-info-subtle" style="font-size: .64rem;">
                                                    <i class="bi bi-bank"></i> {{ __('procurement.farmers.bank_linked') }}
                                                </span>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            @if($farmer->mobile)
                                <div class="d-inline-flex align-items-center gap-1 text-nowrap">
                                    <a href="tel:+91{{ $cleanDigits }}" class="text-decoration-none text-dark small fw-semibold text-nowrap" style="font-variant-numeric: tabular-nums;">
                                        <i class="bi bi-telephone-fill text-success me-1"></i>{{ $farmer->formattedMobile() }}
                                    </a>
                                    @if(strlen($cleanDigits) === 10)
                                        <a href="https://wa.me/91{{ $cleanDigits }}" target="_blank" rel="noopener" class="btn btn-xs btn-light border text-success py-0 px-1" title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="small fw-semibold text-dark">{{ $farmer->village ?: '—' }}</div>
                            @if($farmer->block || $farmer->district)
                                <small class="text-muted">
                                    {{ collect([$farmer->block, $farmer->district])->filter()->join(', ') }}
                                </small>
                            @endif
                        </td>

                        <td class="num">
                            @if($slipsCount > 0 || $suppliedQtl > 0)
                                <div class="fw-bold text-dark">{{ bc_round((string) $suppliedQtl, 2) }} <span class="fw-normal text-muted small">Qtl</span></div>
                                <small class="text-muted">{{ __('procurement.farmers.slips_short', ['count' => $slipsCount]) }}</small>
                            @else
                                <span class="text-muted small">0.00 Qtl</span>
                            @endif
                        </td>

                        <td>
                            @if($farmer->isDelayedBonusEnabled())
                                <span class="badge {{ $farmer->hasCustomBonusSetting() ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-dark border' }}">
                                    <i class="bi bi-gift"></i>
                                    {{ $farmer->effective_bonus_rate }}% {{ ucfirst($farmer->effective_bonus_mode) }}
                                    @if($farmer->hasCustomBonusSetting())
                                        &bull; {{ __('procurement.farmers.custom_badge') }}
                                    @endif
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">{{ __('procurement.farmers.bonus_disabled') }}</span>
                            @endif
                        </td>

                        <td class="num">
                            @if($netPayable > 0)
                                <div class="fw-bold text-danger">{{ money($netPayable) }}</div>
                            @elseif($netPayable < 0)
                                <div class="fw-bold text-primary">{{ money(abs($netPayable)) }}</div>
                                <small class="text-primary">{{ __('procurement.farmers.advance_label') }}</small>
                            @else
                                <span class="badge bg-light text-muted border">{{ __('procurement.farmers.settled_label') }}</span>
                            @endif
                            @if($openingBal != 0 && $purchaseDue > 0)
                                <small class="d-block text-muted">{{ __('procurement.farmers.opening_prefix') }} {{ money($openingBal) }}</small>
                            @endif
                        </td>

                        <td>
                            @if($farmer->is_active)
                                <span class="badge-status badge-success">{{ __('app.active') }}</span>
                            @else
                                <span class="badge-status badge-danger">{{ __('app.inactive') }}</span>
                            @endif
                        </td>

                        <td class="text-end pe-3 text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('purchases.create')
                                    @if($farmer->is_active)
                                        <a href="{{ route('purchases.create', ['farmer_id' => $farmer->id]) }}" class="btn btn-xs btn-outline-success" title="{{ __('procurement.farmers.quick_buy') }}">
                                            <i class="bi bi-basket-fill"></i>
                                        </a>
                                    @endif
                                @endcan
                                @can('farmer_payments.create')
                                    @if($purchaseDue > 0)
                                        <a href="{{ route('farmer-payments.create', ['farmer_id' => $farmer->id]) }}" class="btn btn-xs btn-success" title="{{ __('procurement.farmers.quick_pay') }}">
                                            <i class="bi bi-cash-coin"></i> {{ __('procurement.farmers.quick_pay') }}
                                        </a>
                                    @endif
                                @endcan
                                <a href="{{ route('farmers.show', $farmer) }}" class="btn btn-xs btn-light border" title="{{ __('app.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('farmers.print', $farmer) }}" class="btn btn-xs btn-light border" title="{{ __('app.print') }}" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @can('farmers.edit')
                                    <a href="{{ route('farmers.edit', $farmer) }}" class="btn btn-xs btn-light border" title="{{ __('app.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('farmers.toggle', $farmer) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-xs btn-light border text-{{ $farmer->is_active ? 'danger' : 'success' }}" title="{{ $farmer->is_active ? __('app.inactive') : __('app.active') }}">
                                            <i class="bi bi-{{ $farmer->is_active ? 'person-dash' : 'person-check' }}"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state py-5">
                                <i class="bi bi-people"></i>
                                <h6 class="fw-semibold">{{ __('procurement.farmers.no_farmers') }}</h6>
                                @can('farmers.create')
                                    <a href="{{ route('farmers.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-person-plus"></i> {{ __('procurement.farmers.add_new') }}
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
    <div class="d-md-none mobile-card-list p-2 p-sm-3">
        @forelse($farmers as $farmer)
            @php
                $suppliedQtl = (float) ($farmer->finalized_qtl_sum ?? 0);
                $slipsCount = (int) ($farmer->finalized_purchases_count ?? 0);
                $purchaseDue = (float) ($farmer->purchases_due_sum ?? 0);
                $openingBal = (float) ($farmer->opening_balance ?? 0);
                $netPayable = round($openingBal + $purchaseDue, 2);
                $cleanDigits = $farmer->mobile ? preg_replace('/\D+/', '', (string) $farmer->mobile) : '';
            @endphp
            <div class="mobile-record-card">
                <div class="mobile-card-header">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="avatar flex-shrink-0" style="width: 36px; height: 36px; font-size: .88rem;">
                            {{ mb_strtoupper(mb_substr($farmer->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('farmers.show', $farmer) }}" class="mobile-card-title text-decoration-none text-truncate d-block">
                                {{ $farmer->name }}
                            </a>
                            <div class="mobile-card-meta">
                                <code class="text-muted">{{ $farmer->farmer_id }}</code>
                                @if($farmer->village)
                                    &bull; <i class="bi bi-geo-alt"></i> {{ $farmer->village }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($farmer->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle small">{{ __('app.active') }}</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">{{ __('app.inactive') }}</span>
                        @endif
                    </div>
                </div>

                <div class="mobile-card-body">
                    <div class="mobile-card-main">
                        <div class="mobile-card-sub mb-2">
                            <span class="mobile-chip">
                                <i class="bi bi-basket text-success"></i> {{ bc_round((string) $suppliedQtl, 1) }} Qtl ({{ $slipsCount }})
                            </span>
                            @if($farmer->isDelayedBonusEnabled())
                                <span class="mobile-chip">
                                    <i class="bi bi-gift text-success"></i> {{ $farmer->effective_bonus_rate }}%
                                </span>
                            @endif
                        </div>

                        @if($farmer->mobile)
                            <a href="tel:+91{{ $cleanDigits }}" class="btn btn-xs btn-light border text-dark d-inline-flex align-items-center gap-1">
                                <i class="bi bi-telephone-fill text-success"></i>
                                <span>{{ $farmer->formattedMobile() }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="mobile-card-stats">
                        <div class="small text-muted">{{ __('procurement.farmers.balance_due') }}</div>
                        <div class="mobile-stat-primary {{ $netPayable > 0 ? 'text-danger' : 'text-success' }}">
                            {{ money($netPayable) }}
                        </div>
                    </div>
                </div>

                <div class="mobile-card-footer">
                    <div class="d-flex gap-1">
                        @can('purchases.create')
                            @if($farmer->is_active)
                                <a href="{{ route('purchases.create', ['farmer_id' => $farmer->id]) }}" class="btn btn-xs btn-success">
                                    <i class="bi bi-basket-fill"></i> {{ __('procurement.farmers.quick_buy') }}
                                </a>
                            @endif
                        @endcan
                        @can('farmer_payments.create')
                            @if($purchaseDue > 0)
                                <a href="{{ route('farmer-payments.create', ['farmer_id' => $farmer->id]) }}" class="btn btn-xs btn-outline-success">
                                    <i class="bi bi-cash-coin"></i> {{ __('procurement.farmers.quick_pay') }}
                                </a>
                            @endif
                        @endcan
                    </div>

                    <div class="mobile-card-actions">
                        <a href="{{ route('farmers.show', $farmer) }}" class="btn btn-xs btn-light border">
                            <i class="bi bi-eye"></i> {{ __('app.view') }}
                        </a>
                        @can('farmers.edit')
                            <a href="{{ route('farmers.edit', $farmer) }}" class="btn btn-xs btn-light border">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                        @if(strlen($cleanDigits) === 10)
                            <a href="https://wa.me/91{{ $cleanDigits }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success" title="WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-muted">
                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                {{ __('procurement.farmers.no_farmers') }}
            </div>
        @endforelse
    </div>

    @if($farmers->hasPages())
        <div class="pagination-wrap">
            {{ $farmers->links() }}
        </div>
    @endif
</div>

{{-- Mobile Floating Action Button (FAB) --}}
@can('farmers.create')
    <a href="{{ route('farmers.create') }}" class="mobile-fab" aria-label="{{ __('procurement.farmers.add_new') }}">
        <i class="bi bi-person-plus-fill fs-5"></i>
        <span>{{ __('procurement.farmers.add_new') }}</span>
    </a>
@endcan
@endsection
