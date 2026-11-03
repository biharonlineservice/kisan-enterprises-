@extends('layouts.app')

@section('title', __('expense_categories.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-tags-fill"></i>
                    <strong>{{ __('expense_categories.banner_count', ['count' => $metrics['totalCategories']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ __('expense_categories.banner_active', ['active' => $metrics['activeCategories']]) }}
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-receipt-cutoff"></i>
                    {{ __('expense_categories.banner_vouchers', ['vouchers' => $metrics['totalVouchers']]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-cash-coin text-success"></i>
                    <strong>{{ __('expense_categories.banner_spend', ['amount' => number_format($metrics['totalApprovedAmount'], 2)]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('expense_categories.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('expense_categories.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <button type="button" class="btn btn-sm btn-light text-success fw-bold shadow-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-circle-fill"></i>
                <span>{{ __('expense_categories.btn_add_category') }}</span>
            </button>
            @if(\Illuminate\Support\Facades\Route::has('expenses.index'))
                <a href="{{ route('expenses.index') }}" class="erp-hero-btn" title="{{ __('expense_categories.btn_expenses_register') }}">
                    <i class="bi bi-journal-text"></i>
                    <span>{{ __('expense_categories.btn_expenses_register') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('accounts.index'))
                <a href="{{ route('accounts.index') }}" class="erp-hero-btn" title="{{ __('expense_categories.btn_cash_accounts') }}">
                    <i class="bi bi-bank"></i>
                    <span>{{ __('expense_categories.btn_cash_accounts') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('reports.profit-and-loss'))
                <a href="{{ route('reports.profit-and-loss') }}" class="erp-hero-btn" title="{{ __('expense_categories.btn_pnl_report') }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>{{ __('expense_categories.btn_pnl_report') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Categories --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('expense-categories.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('expense_categories.kpi_total_categories') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-tags-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($metrics['totalCategories']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('expense_categories.kpi_total_categories_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Active Categories --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('expense-categories.index', ['tab' => 'active']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('expense_categories.kpi_active_categories') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-check2-circle"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format($metrics['activeCategories']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('expense_categories.kpi_active_categories_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Total Approved Vouchers --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('expense-categories.index', ['tab' => 'with_expenses']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('expense_categories.kpi_total_vouchers') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-receipt-cutoff"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($metrics['totalVouchers']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('expense_categories.kpi_total_vouchers_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Total Approved Spend --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('expenses.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('expense_categories.kpi_total_spend') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-cash-coin"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">₹{{ number_format($metrics['totalApprovedAmount'], 2) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('expense_categories.kpi_total_spend_sub') }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Filter Tabs, Export & Search Bar --}}
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom p-2">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
            <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'all' ? 'active' : '' }}" 
                       href="{{ route('expense-categories.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}">
                        <i class="bi bi-grid-fill"></i>
                        <span>{{ __('expense_categories.tab_all') }}</span>
                        <span class="badge {{ $tab === 'all' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['totalCategories'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'active' ? 'active' : '' }}" 
                       href="{{ route('expense-categories.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'active'])) }}">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>{{ __('expense_categories.tab_active') }}</span>
                        <span class="badge {{ $tab === 'active' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['activeCategories'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'inactive' ? 'active' : '' }}" 
                       href="{{ route('expense-categories.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'inactive'])) }}">
                        <i class="bi bi-pause-circle text-secondary"></i>
                        <span>{{ __('expense_categories.tab_inactive') }}</span>
                        <span class="badge {{ $tab === 'inactive' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['inactiveCategories'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'with_expenses' ? 'active' : '' }}" 
                       href="{{ route('expense-categories.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'with_expenses'])) }}">
                        <i class="bi bi-receipt text-warning"></i>
                        <span>{{ __('expense_categories.tab_with_expenses') }}</span>
                        <span class="badge {{ $tab === 'with_expenses' ? 'bg-warning text-dark' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['withExpensesCount'] }}
                        </span>
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 flex-shrink-0 pe-2">
                <a href="{{ route('expense-categories.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-download"></i>
                    <span>{{ __('expense_categories.btn_export_csv') }}</span>
                </a>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-none d-lg-inline-flex" style="font-size: 0.72rem;">
                    <i class="bi bi-shield-check me-1"></i>Voucher Auditing
                </span>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card-body p-3 bg-light-subtle border-bottom">
        <form method="GET" action="{{ route('expense-categories.index') }}" class="row g-2 align-items-end" data-autosubmit>
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('expense_categories.search_placeholder') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('expense_categories.search_placeholder') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('expense_categories.filter_status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('expense_categories.status_all') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('expense_categories.status_active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('expense_categories.status_inactive') }}</option>
                </select>
            </div>

            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('expense_categories.btn_filter') }}
                </button>
                @if(request()->anyFilled(['q', 'status']))
                    <a href="{{ route('expense-categories.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('expense_categories.btn_reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width: 110px;">{{ __('expense_categories.col_code') }}</th>
                    <th style="min-width: 200px;">{{ __('expense_categories.col_name') }}</th>
                    <th style="min-width: 220px;">{{ __('expense_categories.col_description') }}</th>
                    <th class="text-center" style="width: 110px;">{{ __('expense_categories.col_vouchers') }}</th>
                    <th class="text-end" style="width: 160px;">{{ __('expense_categories.col_spend') }}</th>
                    <th class="text-center" style="width: 90px;">{{ __('expense_categories.col_status') }}</th>
                    <th class="text-end pe-3" style="width: 140px;">{{ __('expense_categories.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    @php
                        $approvedSpend = (float) ($categoryTotals[$cat->id] ?? 0);
                        $approvedVouchers = (int) ($categoryVouchers[$cat->id] ?? 0);
                    @endphp
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border font-monospace text-uppercase px-2 py-1 fs-7">
                                {{ $cat->code }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-tag text-success"></i>
                                <span>{{ $cat->name }}</span>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted d-block text-truncate" style="max-width: 280px;" title="{{ $cat->description }}">
                                {{ $cat->description ?: '-' }}
                            </small>
                        </td>
                        <td class="text-center">
                            @if($cat->expenses_count > 0)
                                <a href="{{ route('expenses.index', ['category_id' => $cat->id]) }}" class="badge bg-light text-dark border text-decoration-none px-2 py-1" title="View {{ $cat->expenses_count }} vouchers">
                                    <i class="bi bi-receipt me-1 text-muted"></i>{{ $cat->expenses_count }}
                                </a>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">0</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="fw-bold font-monospace {{ $approvedSpend > 0 ? 'text-dark' : 'text-muted' }}">
                                ₹{{ number_format($approvedSpend, 2) }}
                            </div>
                            @if($approvedVouchers > 0)
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    {{ $approvedVouchers }} {{ __('expense_categories.col_vouchers') }}
                                </small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($cat->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('expense_categories.status_active_pill') }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>{{ __('expense_categories.status_inactive_pill') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}" title="{{ __('expense_categories.btn_edit') }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form method="POST" action="{{ route('expense-categories.toggle', $cat) }}" class="d-inline" onsubmit="return confirm('{{ __('expense_categories.confirm_toggle') }}');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $cat->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} px-2 py-1" title="{{ $cat->is_active ? __('expense_categories.btn_deactivate') : __('expense_categories.btn_activate') }}">
                                        <i class="bi {{ $cat->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-tags text-muted d-block fs-1 mb-2"></i>
                            <div class="fw-semibold">{{ __('expense_categories.empty_title') }}</div>
                            <small class="text-muted">{{ __('expense_categories.empty_text') }}</small>
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                    <i class="bi bi-plus-circle me-1"></i> {{ __('expense_categories.btn_add_first') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card Feed View --}}
    <div class="d-md-none p-3">
        @forelse($categories as $cat)
            @php
                $approvedSpend = (float) ($categoryTotals[$cat->id] ?? 0);
                $approvedVouchers = (int) ($categoryVouchers[$cat->id] ?? 0);
            @endphp
            <div class="card shadow-sm border rounded-3 mb-3 overflow-hidden bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div style="min-width: 0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-light text-dark border font-monospace text-uppercase px-2 py-1">
                                    {{ $cat->code }}
                                </span>
                                @if($cat->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>{{ __('expense_categories.status_active_pill') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                        <i class="bi bi-dash-circle me-1"></i>{{ __('expense_categories.status_inactive_pill') }}
                                    </span>
                                @endif
                            </div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $cat->name }}</h6>
                        </div>
                    </div>

                    @if($cat->description)
                        <p class="small text-muted mb-2 bg-light p-2 rounded">
                            {{ $cat->description }}
                        </p>
                    @endif

                    {{-- Spend & Voucher Stat Box --}}
                    <div class="p-2 rounded-2 bg-light border d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ __('expense_categories.col_vouchers') }}</small>
                            <span class="badge bg-white text-dark border font-monospace px-2 py-1">
                                <i class="bi bi-receipt me-1 text-muted"></i>{{ $cat->expenses_count }}
                            </span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ __('expense_categories.col_spend') }}</small>
                            <strong class="text-dark font-monospace fs-6">
                                ₹{{ number_format($approvedSpend, 2) }}
                            </strong>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}">
                            <i class="bi bi-pencil-square me-1"></i> {{ __('expense_categories.btn_edit') }}
                        </button>
                        <form method="POST" action="{{ route('expense-categories.toggle', $cat) }}" class="d-inline" onsubmit="return confirm('{{ __('expense_categories.confirm_toggle') }}');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $cat->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} px-3">
                                {{ $cat->is_active ? __('expense_categories.btn_deactivate') : __('expense_categories.btn_activate') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <i class="bi bi-tags text-muted d-block fs-1 mb-2"></i>
                <div class="fw-semibold">{{ __('expense_categories.empty_title') }}</div>
                <small class="text-muted">{{ __('expense_categories.empty_text') }}</small>
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('expense_categories.btn_add_first') }}
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    @if($categories->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $categories->links() }}
        </div>
    @endif
</div>

{{-- 4. Create Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expense-categories.store') }}" class="w-100">
            @csrf
            <x-form-token />
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title h6 fw-bold mb-0 d-flex align-items-center gap-2" id="createCategoryModalLabel">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>{{ __('expense_categories.modal_create_title') }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        {{ __('expense_categories.modal_create_subtitle') }}
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small required">{{ __('expense_categories.label_name') }}</label>
                        <input type="text" name="name" class="form-control" placeholder="{{ __('expense_categories.placeholder_name') }}" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small required">{{ __('expense_categories.label_code') }}</label>
                        <input type="text" name="code" class="form-control font-monospace text-uppercase" placeholder="{{ __('expense_categories.placeholder_code') }}" required maxlength="30">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">{{ __('expense_categories.label_description') }}</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="{{ __('expense_categories.placeholder_description') }}" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('expense_categories.btn_cancel') }}</button>
                    <button type="submit" class="btn btn-success btn-sm fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> {{ __('expense_categories.btn_save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 5. Edit Modals (One per category) --}}
@foreach($categories as $cat)
    <div class="modal fade" id="editCategoryModal{{ $cat->id }}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{ $cat->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('expense-categories.update', $cat) }}" class="w-100">
                @csrf
                @method('PUT')
                <x-form-token />
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title h6 fw-bold mb-0 d-flex align-items-center gap-2" id="editCategoryModalLabel{{ $cat->id }}">
                            <i class="bi bi-pencil-square text-success"></i>
                            <span>{{ __('expense_categories.modal_edit_title') }}: <span class="text-white-50">{{ $cat->code }}</span></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">
                            {{ __('expense_categories.modal_edit_subtitle') }}
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small required">{{ __('expense_categories.label_name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $cat->name) }}" class="form-control" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small required">{{ __('expense_categories.label_code') }}</label>
                            <input type="text" name="code" value="{{ old('code', $cat->code) }}" class="form-control font-monospace text-uppercase" required maxlength="30">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">{{ __('expense_categories.label_description') }}</label>
                            <textarea name="description" rows="3" class="form-control" maxlength="255">{{ old('description', $cat->description) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light-subtle">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('expense_categories.btn_cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                            <i class="bi bi-save me-1"></i> {{ __('expense_categories.btn_update') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

@endsection
