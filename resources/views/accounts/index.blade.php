@extends('layouts.app')

@section('title', __('accounts.cash_bank'))

@section('content')
@php
    $hasFilters = request()->anyFilled(['q', 'tab']);
    $activeTab = $activeTab ?? request('tab', 'all');
@endphp

{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-wallet2 text-warning"></i>
                    <strong>{{ __('accounts.banner_accounts', ['count' => $counts['all']]) }}</strong>
                </span>
                <span class="erp-hero-chip" style="background: rgba(34, 197, 94, .2); border-color: rgba(34, 197, 94, .4); color: #86efac; white-space: nowrap;">
                    <i class="bi bi-cash-stack text-success"></i>
                    <strong>{{ __('accounts.banner_liquidity', ['amount' => money($totalLiquidity)]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar3"></i>
                    {{ __('app.financial_year') }}: <strong>{{ indian_fy() }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">{{ __('accounts.cash_bank') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 720px;">
                {{ __('accounts.accounts_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('accounts.export') }}" class="erp-hero-btn" title="{{ __('accounts.export_csv') }}">
                <i class="bi bi-download"></i>
                <span>{{ __('accounts.export_csv') }}</span>
            </a>
            <a href="{{ route('accounts.daybook') }}" class="erp-hero-btn" title="{{ __('accounts.daybook') }}">
                <i class="bi bi-journal-text"></i>
                <span>{{ __('accounts.daybook_shortcut') }}</span>
            </a>
            <a href="{{ route('accounts.profit_and_loss') }}" class="erp-hero-btn" title="{{ __('accounts.pnl') }}">
                <i class="bi bi-graph-up"></i>
                <span>{{ __('accounts.pnl_shortcut') }}</span>
            </a>
            @can('accounts.manage')
                <button type="button" class="erp-hero-btn" data-bs-toggle="modal" data-bs-target="#transferModal">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>{{ __('accounts.quick_transfer') }}</span>
                </button>
                <button type="button" class="erp-hero-btn gold" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('accounts.add_account') }}</span>
                </button>
            @endcan
        </div>
    </div>
</div>

{{-- 2. Summary KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Liquid Funds --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('accounts.index', request()->except(['tab', 'page'])) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('accounts.kpi_total_liquidity') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-wallet2"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ money($totalLiquidity) }}</div>
                <div class="kpi-sub text-truncate">{{ __('accounts.kpi_total_liquidity_sub') }}</div>
            </div>
        </a>
    </div>

    {{-- Card 2: Cash in Hand (Galla) --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('accounts.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'cash'])) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('accounts.kpi_cash_balance') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-cash-stack"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ money($totalCash) }}</div>
                <div class="kpi-sub text-truncate">{{ __('accounts.kpi_cash_balance_sub', ['count' => $counts['cash']]) }}</div>
            </div>
        </a>
    </div>

    {{-- Card 3: Bank Account Balances --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('accounts.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'bank'])) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('accounts.kpi_bank_balance') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-bank"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ money($totalBank) }}</div>
                <div class="kpi-sub text-truncate">{{ __('accounts.kpi_bank_balance_sub', ['count' => $counts['bank']]) }}</div>
            </div>
        </a>
    </div>

    {{-- Card 4: Active Accounts --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('accounts.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'active'])) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('accounts.kpi_active_accounts') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-check2-circle"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ $counts['active'] }} <span class="fs-6 fw-normal text-muted">/ {{ $counts['all'] }}</span></div>
                <div class="kpi-sub text-truncate">{{ __('accounts.kpi_active_accounts_sub') }}</div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Navigation Filter Tabs & Search Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeTab === 'all' ? 'active' : '' }}" 
                   href="{{ route('accounts.index', request()->except(['tab', 'page'])) }}">
                    <i class="bi bi-wallet2"></i>
                    <span>{{ __('accounts.tab_all_accounts') }}</span>
                    <span class="badge {{ $activeTab === 'all' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['all'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeTab === 'cash' ? 'active' : '' }}" 
                   href="{{ route('accounts.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'cash'])) }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>{{ __('accounts.tab_cash_tills') }}</span>
                    <span class="badge {{ $activeTab === 'cash' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['cash'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeTab === 'bank' ? 'active' : '' }}" 
                   href="{{ route('accounts.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'bank'])) }}">
                    <i class="bi bi-bank"></i>
                    <span>{{ __('accounts.tab_bank_accounts') }}</span>
                    <span class="badge {{ $activeTab === 'bank' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['bank'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $activeTab === 'active' ? 'active' : '' }}" 
                   href="{{ route('accounts.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'active'])) }}">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ __('accounts.tab_active_accounts') }}</span>
                    <span class="badge {{ $activeTab === 'active' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $counts['active'] }}
                    </span>
                </a>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2 flex-shrink-0 pe-2 w-100 w-md-auto erp-filter-collapsible">
            <form method="GET" action="{{ route('accounts.index') }}" class="d-flex align-items-center gap-1 flex-grow-1 flex-md-grow-0" style="max-width: 100%;" data-autosubmit>
                @if(request('tab'))
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                @endif
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0"
                           placeholder="{{ __('accounts.search_accounts_placeholder') }}"
                           value="{{ request('q') }}">
                    @if(request('q'))
                        <a href="{{ route('accounts.index', request()->except(['q', 'page'])) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-none d-lg-inline-flex text-nowrap" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Reconciled
            </span>
        </div>
    </div>
</div>

{{-- 4. Mobile Feed View (< 768px) --}}
<div class="d-md-none mb-4">
    @if(count($accounts) > 0)
        <div class="mobile-card-list">
            @foreach($accounts as $acc)
                <div class="mobile-record-card mb-3">
                    <div class="mobile-card-header d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1 me-2" style="min-width: 0;">
                            <span class="fw-bold text-dark fs-6 text-truncate">{{ $acc->name }}</span>
                            <span class="badge bg-light text-muted font-monospace border flex-shrink-0" style="font-size: 0.7rem;">#{{ str_pad($acc->id, 3, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        @if($acc->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle flex-shrink-0">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ __('app.active') }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border flex-shrink-0">
                                {{ __('app.inactive') }}
                            </span>
                        @endif
                    </div>

                    <div class="mb-2">
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @if($acc->isCash())
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-cash me-1"></i>{{ __('accounts.type_cash') }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi bi-bank me-1"></i>{{ __('accounts.type_bank') }}
                                </span>
                            @endif

                            @if($acc->bank_name)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1 text-muted"></i>{{ $acc->bank_name }}
                                </span>
                            @endif
                        </div>

                        @if($acc->account_number)
                            <div class="p-2 rounded bg-light font-monospace small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">A/c:</span>
                                    <code class="text-dark">{{ $acc->account_number }}</code>
                                </div>
                                @if($acc->ifsc_code)
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-muted">IFSC:</span>
                                        <span class="fw-semibold text-uppercase text-dark">{{ $acc->ifsc_code }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        <div>
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ __('accounts.current_balance') }}</span>
                            <span class="fs-5 fw-bold font-monospace {{ bccomp($acc->current_balance, '0', 2) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ money($acc->current_balance) }}
                            </span>
                            <span class="small text-muted d-block font-monospace" style="font-size: 0.72rem;">
                                Op: {{ money($acc->opening_balance) }}
                            </span>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            @can('accounts.manage')
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        data-bs-toggle="modal" data-bs-target="#transferModal"
                                        onclick="prefillTransfer({{ $acc->id }})">
                                    <i class="bi bi-arrow-left-right me-1"></i>{{ __('accounts.quick_transfer') }}
                                </button>
                            @endcan
                            <a href="{{ route('accounts.daybook') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('accounts.daybook') }}">
                                <i class="bi bi-journal-text"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm border-0 text-center py-5 bg-white rounded-3">
            <i class="bi bi-bank fs-1 text-muted mb-2"></i>
            <h6 class="text-muted mb-1">{{ __('app.no_records') }}</h6>
            <p class="text-muted small mb-0">{{ __('accounts.empty_expenses') }}</p>
        </div>
    @endif
</div>

{{-- 5. Desktop Accounts Table (>= 768px) --}}
<div class="card shadow-sm border-0 bg-white rounded-3 d-none d-md-block mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="card-title h6 mb-0 text-success fw-bold">
            <i class="bi bi-bank me-1"></i> {{ __('accounts.cash_bank') }}
        </h5>
        <span class="badge bg-light text-dark border">
            {{ count($accounts) }} {{ __('accounts.cash_bank') }}
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 25%;">{{ __('accounts.account_name') }}</th>
                    <th style="width: 18%;">{{ __('accounts.account_type') }}</th>
                    <th style="width: 18%;">{{ __('accounts.type_bank') }}</th>
                    <th style="width: 15%;">A/c # / IFSC</th>
                    <th class="text-end" style="width: 12%;">{{ __('accounts.opening_balance') }}</th>
                    <th class="text-end" style="width: 12%;">{{ __('accounts.current_balance') }}</th>
                    <th class="text-center" style="width: 8%;">{{ __('app.status') }}</th>
                    <th class="text-center" style="width: 10%;">{{ __('accounts.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $acc)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $acc->name }}</div>
                            <small class="text-muted font-monospace">ID: #ACC-{{ str_pad($acc->id, 3, '0', STR_PAD_LEFT) }}</small>
                        </td>
                        <td>
                            @if($acc->isCash())
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-cash me-1"></i>{{ __('accounts.type_cash') }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-bank me-1"></i>{{ __('accounts.type_bank') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($acc->bank_name)
                                <div class="fw-semibold text-dark">{{ $acc->bank_name }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($acc->account_number)
                                <code class="text-dark">{{ $acc->account_number }}</code>
                                @if($acc->ifsc_code)
                                    <small class="text-muted d-block text-uppercase font-monospace">IFSC: {{ $acc->ifsc_code }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace text-muted">
                            {{ money($acc->opening_balance) }}
                        </td>
                        <td class="text-end font-monospace fw-bold fs-6 {{ bccomp($acc->current_balance, '0', 2) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ money($acc->current_balance) }}
                        </td>
                        <td class="text-center">
                            @if($acc->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('app.active') }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    {{ __('app.inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                @can('accounts.manage')
                                    <button type="button" class="btn btn-outline-success btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#transferModal"
                                            onclick="prefillTransfer({{ $acc->id }})"
                                            title="{{ __('accounts.fund_transfer') }}">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>
                                @endcan
                                <a href="{{ route('accounts.daybook') }}" class="btn btn-outline-secondary btn-sm" title="{{ __('accounts.daybook') }}">
                                    <i class="bi bi-journal-text"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-bank fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6 class="text-muted mb-1">{{ __('app.no_records') }}</h6>
                            <p class="text-muted small mb-0">{{ __('accounts.empty_expenses') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 6. Create Account Modal --}}
@can('accounts.manage')
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            <x-form-token />
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title h6 text-white mb-0">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('accounts.add_account') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('accounts.account_name') }}</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Main Cash Till, SBI Mandi Current A/c" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('accounts.account_type') }}</label>
                        <select name="account_type" id="modalAccountType" class="form-select" required onchange="toggleBankFields(this.value)">
                            <option value="bank">{{ __('accounts.type_bank') }}</option>
                            <option value="cash">{{ __('accounts.type_cash') }}</option>
                        </select>
                    </div>
                    <div id="bankDetailsGroup">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ __('accounts.type_bank') }} (Bank Name)</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="e.g. State Bank of India, HDFC Bank">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label small fw-semibold text-muted">A/c Number</label>
                                <input type="text" name="account_number" class="form-control font-monospace" placeholder="e.g. 30894567890" inputmode="numeric">
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-semibold text-muted">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control text-uppercase font-monospace" placeholder="SBIN0001234">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('accounts.opening_balance') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="0" name="opening_balance" class="form-control text-end font-monospace fw-bold" value="0.00" inputmode="decimal" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-success fw-semibold shadow-sm">{{ __('app.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 7. Contra Fund Transfer Modal --}}
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('accounts.transfer') }}">
            @csrf
            <x-form-token />
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title h6 text-white mb-0">
                        <i class="bi bi-arrow-left-right me-1"></i> {{ __('accounts.fund_transfer') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small border-0 rounded-3 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> {{ __('accounts.contra_hint') }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('app.date') }}</label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('accounts.from_account') }}</label>
                        <select name="from_account_id" id="fromAccountSelect" class="form-select" required>
                            <option value="">-- {{ __('app.select') }} --</option>
                            @foreach($allAccounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} ({{ __('accounts.available_balance', ['amount' => money($acc->current_balance)]) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('accounts.to_account') }}</label>
                        <select name="to_account_id" id="toAccountSelect" class="form-select" required>
                            <option value="">-- {{ __('app.select') }} --</option>
                            @foreach($allAccounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} ({{ __('accounts.available_balance', ['amount' => money($acc->current_balance)]) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('app.total_amount') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control text-end fw-bold font-monospace" inputmode="decimal" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">{{ __('accounts.mode_ref') }}</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. Cheque #459021, Cash Deposit Slip #">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">{{ __('app.notes') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Cash withdrawal for daily farmer mandi payments">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-success fw-semibold shadow-sm">{{ __('accounts.transfer_funds') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Mobile Floating Action Button --}}
<button type="button" class="mobile-fab" data-bs-toggle="modal" data-bs-target="#createAccountModal" aria-label="{{ __('accounts.add_account') }}">
    <i class="bi bi-plus-lg"></i>
    <span>{{ __('accounts.add_account') }}</span>
</button>
@endcan

@push('scripts')
<script>
function toggleBankFields(val) {
    var grp = document.getElementById('bankDetailsGroup');
    if (grp) {
        grp.style.display = (val === 'cash') ? 'none' : 'block';
    }
}
function prefillTransfer(fromId) {
    var sel = document.getElementById('fromAccountSelect');
    if (sel) {
        sel.value = fromId;
    }
}
</script>
@endpush

@endsection
