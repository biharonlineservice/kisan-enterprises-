@extends('layouts.app')

@section('title', __('audit_logs.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-shield-check text-success"></i>
                    <strong>{{ __('audit_logs.banner_total', ['count' => number_format($metrics['totalLogs'])]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-calendar-check"></i>
                    {{ __('audit_logs.banner_today', ['count' => number_format($metrics['todayLogs'])]) }}
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-people"></i>
                    {{ __('audit_logs.banner_users', ['count' => number_format($metrics['activeUsers'])]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-lock-fill text-warning"></i>
                    <strong>{{ __('audit_logs.banner_immutable') }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('audit_logs.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 780px;">
                {{ __('audit_logs.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            @if(\Illuminate\Support\Facades\Route::has('users.index'))
                <a href="{{ route('users.index') }}" class="erp-hero-btn" title="{{ __('audit_logs.btn_users') }}">
                    <i class="bi bi-people"></i>
                    <span>{{ __('audit_logs.btn_users') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('roles.index'))
                <a href="{{ route('roles.index') }}" class="erp-hero-btn" title="{{ __('audit_logs.btn_roles') }}">
                    <i class="bi bi-shield-check"></i>
                    <span>{{ __('audit_logs.btn_roles') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('settings.edit'))
                <a href="{{ route('settings.edit') }}" class="erp-hero-btn" title="{{ __('audit_logs.btn_settings') }}">
                    <i class="bi bi-gear"></i>
                    <span>{{ __('audit_logs.btn_settings') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('audit'))
                <a href="{{ route('audit') }}" class="erp-hero-btn" title="{{ __('audit_logs.btn_reports') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>{{ __('audit_logs.btn_reports') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Audit Events --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('audit-logs.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('audit_logs.kpi_total') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-shield-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($metrics['totalLogs']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('audit_logs.kpi_total_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Today's Events --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('audit-logs.index', ['tab' => 'today']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('audit_logs.kpi_today') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format($metrics['todayLogs']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('audit_logs.kpi_today_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Active Operators --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('audit-logs.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('audit_logs.kpi_users') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($metrics['activeUsers']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('audit_logs.kpi_users_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: State Mutations --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('audit-logs.index', ['tab' => 'mutations']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('audit_logs.kpi_mutations') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-clock-history"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ number_format($metrics['stateMutations']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('audit_logs.kpi_mutations_sub') }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Navigation Filter Tabs Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'all' ? 'active' : '' }}" 
                   href="{{ route('audit-logs.index', array_merge(request()->except('page', 'tab'), ['tab' => 'all'])) }}">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('audit_logs.tab_all') }}</span>
                    <span class="badge {{ $tab === 'all' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ number_format($metrics['totalLogs']) }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'today' ? 'active' : '' }}" 
                   href="{{ route('audit-logs.index', array_merge(request()->except('page', 'tab'), ['tab' => 'today'])) }}">
                    <i class="bi bi-calendar-day"></i>
                    <span>{{ __('audit_logs.tab_today') }}</span>
                    <span class="badge {{ $tab === 'today' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ number_format($metrics['todayLogs']) }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'mutations' ? 'active' : '' }}" 
                   href="{{ route('audit-logs.index', array_merge(request()->except('page', 'tab'), ['tab' => 'mutations'])) }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>{{ __('audit_logs.tab_mutations') }}</span>
                    <span class="badge {{ $tab === 'mutations' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ number_format($metrics['stateMutations']) }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'security' ? 'active' : '' }}" 
                   href="{{ route('audit-logs.index', array_merge(request()->except('page', 'tab'), ['tab' => 'security'])) }}">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>{{ __('audit_logs.tab_security') }}</span>
                    <span class="badge {{ $tab === 'security' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ number_format($metrics['securityLogs']) }}
                    </span>
                </a>
            </li>
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Append-Only Ledger
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-file-lock2 me-1"></i>Immutable
            </span>
        </div>
    </div>
</div>

{{-- 4. Audit Log Records Data Card --}}
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
    {{-- Filter Toolbar --}}
    <div class="card-body border-bottom bg-light p-3 erp-filter-collapsible">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 align-items-end" data-autosubmit>
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('audit_logs.filter_from') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm font-monospace">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('audit_logs.filter_to') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm font-monospace">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('audit_logs.filter_user') }}</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">{{ __('audit_logs.filter_all_users') }}</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('audit_logs.filter_action') }}</label>
                <select name="action" class="form-select form-select-sm font-monospace">
                    <option value="">{{ __('audit_logs.filter_all_actions') }}</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('audit_logs.btn_filter') }}
                </button>
                @if(request()->anyFilled(['from', 'to', 'user_id', 'action']))
                    <a href="{{ route('audit-logs.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('audit_logs.btn_reset') }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Desktop Table View (> 768px) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width: 170px;">{{ __('audit_logs.col_timestamp') }}</th>
                    <th style="min-width: 190px;">{{ __('audit_logs.col_user') }}</th>
                    <th style="min-width: 180px;">{{ __('audit_logs.col_action') }}</th>
                    <th style="min-width: 140px;">{{ __('audit_logs.col_subject') }}</th>
                    <th>{{ __('audit_logs.col_description') }}</th>
                    <th style="width: 130px;">{{ __('audit_logs.col_ip') }}</th>
                    <th class="text-end pe-3" style="width: 80px;">{{ __('audit_logs.col_details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="ps-3">
                            <span class="small font-monospace text-dark d-block">
                                {{ fmt_date($log->created_at, true) }}
                            </span>
                            <small class="text-muted" style="font-size: 0.72rem;">
                                {{ $log->created_at->diffForHumans() }}
                            </small>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $log->user->name }}</div>
                                        <small class="badge bg-light text-secondary border px-1 py-0" style="font-size: 0.7rem;">
                                            {{ $log->user->role?->display_name ?? 'Staff' }}
                                        </small>
                                    </div>
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <i class="bi bi-cpu me-1"></i> {{ __('audit_logs.system_user') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $actionColor = 'primary';
                                if (str_starts_with($log->action, 'purchase.') || str_starts_with($log->action, 'crop.')) $actionColor = 'success';
                                elseif (str_starts_with($log->action, 'expense.') || str_starts_with($log->action, 'account.')) $actionColor = 'warning-emphasis';
                                elseif (str_starts_with($log->action, 'settings.') || str_starts_with($log->action, 'role.')) $actionColor = 'danger';
                                elseif (str_starts_with($log->action, 'stock.')) $actionColor = 'info text-dark';
                            @endphp
                            <span class="badge bg-{{ $actionColor }}-subtle text-{{ $actionColor }} border border-{{ $actionColor }}-subtle font-monospace py-1 px-2">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            @if($log->auditable_type)
                                <span class="badge bg-light text-dark border font-monospace">
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small text-secondary">
                            {{ $log->description ?: '—' }}
                            @if(!empty($log->old_values) || !empty($log->new_values))
                                <span class="badge bg-info-subtle text-primary border border-info-subtle ms-1" style="font-size: 0.68rem;" title="Has recorded data payload diff">
                                    <i class="bi bi-layers-fill me-1"></i>Diff
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="small font-monospace text-muted d-flex align-items-center gap-1">
                                <i class="bi bi-hdd-network text-secondary"></i>
                                <span>{{ $log->ip_address ?: '—' }}</span>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-success px-2 py-1" title="View Log Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history text-muted d-block fs-1 mb-2"></i>
                            <div class="fw-semibold">{{ __('audit_logs.empty_title') }}</div>
                            <small class="text-muted">{{ __('audit_logs.empty_text') }}</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card Feed View (< 768px) --}}
    <div class="d-md-none p-3">
        @forelse($logs as $log)
            <div class="card shadow-sm border rounded-3 mb-3 bg-white overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $log->user?->name ?? __('audit_logs.system_user') }}</h6>
                                <span class="small text-muted font-monospace">{{ fmt_date($log->created_at, true) }}</span>
                            </div>
                        </div>
                        <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                            {{ $log->action }}
                        </span>
                        @if($log->auditable_type)
                            <span class="badge bg-light text-dark border font-monospace">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </span>
                        @endif
                        @if(!empty($log->old_values) || !empty($log->new_values))
                            <span class="badge bg-info-subtle text-primary border border-info-subtle">
                                <i class="bi bi-layers-fill me-1"></i>Diff
                            </span>
                        @endif
                    </div>

                    <p class="small text-secondary mb-2 bg-light p-2 rounded-2 border">
                        {{ $log->description ?: 'No additional description provided.' }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center small text-muted font-monospace">
                        <span><i class="bi bi-hdd-network me-1"></i>{{ $log->ip_address ?: '—' }}</span>
                        <span>{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">
                <i class="bi bi-clock-history fs-1 d-block mb-2 text-muted"></i>
                <p class="mb-0 fw-semibold">{{ __('audit_logs.empty_title') }}</p>
            </div>
        @endforelse
    </div>

    @if($logs->hasPages())
        <div class="card-footer bg-white border-top p-3">
            <div class="pagination-wrap">
                {{ $logs->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
