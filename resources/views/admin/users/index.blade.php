@extends('layouts.app')

@section('title', __('users.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-people-fill"></i>
                    <strong>{{ __('users.banner_total', ['count' => $metrics['totalUsers']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-person-check-fill"></i>
                    {{ __('users.banner_active', ['count' => $metrics['activeUsers']]) }}
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-shield-lock-fill"></i>
                    {{ __('users.banner_admins', ['count' => $metrics['adminUsers']]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-clock-history text-success"></i>
                    <strong>{{ __('users.banner_recent_login', ['count' => $metrics['recentLoginUsers']]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('users.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('users.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-light text-success fw-bold shadow-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill"></i>
                <span>{{ __('users.btn_add_user') }}</span>
            </a>
            @if(\Illuminate\Support\Facades\Route::has('roles.index'))
                <a href="{{ route('roles.index') }}" class="erp-hero-btn" title="{{ __('users.btn_roles') }}">
                    <i class="bi bi-shield-lock"></i>
                    <span>{{ __('users.btn_roles') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('audit-logs.index'))
                <a href="{{ route('audit-logs.index') }}" class="erp-hero-btn" title="{{ __('users.btn_audit_logs') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('users.btn_audit_logs') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('settings.edit'))
                <a href="{{ route('settings.edit') }}" class="erp-hero-btn" title="{{ __('users.btn_settings') }}">
                    <i class="bi bi-gear"></i>
                    <span>{{ __('users.btn_settings') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Total Staff Users --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('users.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('users.kpi_total_users') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($metrics['totalUsers']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('users.kpi_total_users_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Active Staff --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('users.index', ['tab' => 'active']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('users.kpi_active_users') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-person-check-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format($metrics['activeUsers']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('users.kpi_active_users_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: System Administrators --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('users.index', ['tab' => 'admins']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('users.kpi_admins') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($metrics['adminUsers']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('users.kpi_admins_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Temporary Passwords --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('users.index', ['tab' => 'temp_pwd']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('users.kpi_temp_passwords') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-key-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ number_format($metrics['tempPasswordUsers']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('users.kpi_temp_passwords_sub') }}
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
                       href="{{ route('users.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}">
                        <i class="bi bi-grid-fill"></i>
                        <span>{{ __('users.tab_all') }}</span>
                        <span class="badge {{ $tab === 'all' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['totalUsers'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'active' ? 'active' : '' }}" 
                       href="{{ route('users.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'active'])) }}">
                        <i class="bi bi-person-check-fill text-success"></i>
                        <span>{{ __('users.tab_active') }}</span>
                        <span class="badge {{ $tab === 'active' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['activeUsers'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'inactive' ? 'active' : '' }}" 
                       href="{{ route('users.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'inactive'])) }}">
                        <i class="bi bi-pause-circle text-secondary"></i>
                        <span>{{ __('users.tab_inactive') }}</span>
                        <span class="badge {{ $tab === 'inactive' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['inactiveUsers'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'admins' ? 'active' : '' }}" 
                       href="{{ route('users.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'admins'])) }}">
                        <i class="bi bi-shield-lock-fill text-warning"></i>
                        <span>{{ __('users.tab_admins') }}</span>
                        <span class="badge {{ $tab === 'admins' ? 'bg-warning text-dark' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['adminUsers'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'temp_pwd' ? 'active' : '' }}" 
                       href="{{ route('users.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'temp_pwd'])) }}">
                        <i class="bi bi-key-fill text-primary"></i>
                        <span>{{ __('users.tab_temp_pwd') }}</span>
                        <span class="badge {{ $tab === 'temp_pwd' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['tempPasswordUsers'] }}
                        </span>
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 flex-shrink-0 pe-2">
                <a href="{{ route('users.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-download"></i>
                    <span>{{ __('users.btn_export_csv') }}</span>
                </a>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-none d-xxl-inline-flex" style="font-size: 0.72rem;">
                    <i class="bi bi-shield-check me-1"></i>Role-Based Access
                </span>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card-body p-3 bg-light-subtle border-bottom">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end" data-autosubmit>
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('users.filter_search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="{{ __('users.search_placeholder') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('users.filter_role') }}</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">{{ __('users.role_all') }}</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ request('role') == $r->id ? 'selected' : '' }}>{{ $r->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">{{ __('users.filter_status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('users.status_all') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('users.status_active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('users.status_inactive') }}</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> {{ __('users.btn_filter') }}
                </button>
                @if(request()->anyFilled(['q', 'role', 'status']))
                    <a href="{{ route('users.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('users.btn_reset') }}">
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
                    <th class="ps-3" style="min-width: 230px;">{{ __('users.col_user') }}</th>
                    <th style="width: 140px;">{{ __('users.col_role') }}</th>
                    <th style="width: 150px;">{{ __('users.col_designation') }}</th>
                    <th class="text-center" style="width: 140px;">{{ __('users.col_status') }}</th>
                    <th style="width: 140px;">{{ __('users.col_last_active') }}</th>
                    <th class="text-end pe-3" style="width: 100px;">{{ __('users.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-success text-white fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div style="min-width: 0;">
                                    <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                        <span>{{ $u->name }}</span>
                                        @if($u->id === auth()->id())
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">You</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted d-flex flex-wrap align-items-center gap-1">
                                        @if($u->mobile)
                                            <span class="fw-semibold text-dark"><i class="bi bi-telephone text-success me-1"></i>+91 {{ $u->mobile }}</span>
                                            @if($u->email) &bull; @endif
                                        @endif
                                        @if($u->email)
                                            <span class="text-truncate" style="max-width: 160px;" title="{{ $u->email }}">{{ $u->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleName = $u->role?->name;
                                $badgeClass = match($roleName) {
                                    \App\Models\Role::ADMIN => 'bg-dark text-white border-dark',
                                    \App\Models\Role::PURCHASE_SALES => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                    \App\Models\Role::ACCOUNTS => 'bg-success-subtle text-success border-success-subtle',
                                    default => 'bg-light text-dark border',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} border px-2 py-1">
                                {{ $u->role?->display_name ?? 'No Role' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-secondary small">{{ $u->designation ?: '—' }}</span>
                        </td>
                        <td class="text-center">
                            @if($u->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('users.status_active_pill') }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>{{ __('users.status_inactive_pill') }}
                                </span>
                            @endif

                            @if($u->must_change_password)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" title="User must change temporary password upon login">
                                    <i class="bi bi-key-fill me-1"></i>{{ __('users.temp_pwd_pill') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-muted font-monospace">
                                {{ $u->last_login_at ? fmt_date($u->last_login_at, true) : __('users.never_logged_in') }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="{{ __('users.btn_edit') }}">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                @if($u->id !== auth()->id())
                                    <form method="POST" 
                                          action="{{ route('users.toggle', $u) }}" 
                                          class="d-inline"
                                          onsubmit="return confirm('{{ __('users.confirm_toggle') }}');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} px-2 py-1" title="{{ $u->is_active ? __('users.btn_deactivate') : __('users.btn_activate') }}">
                                            <i class="bi {{ $u->is_active ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people text-muted d-block fs-1 mb-2"></i>
                            <div class="fw-semibold">{{ __('users.empty_title') }}</div>
                            <small class="text-muted">{{ __('users.empty_text') }}</small>
                            <div class="mt-3">
                                <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-person-plus-fill me-1"></i> {{ __('users.btn_create_first') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card Feed View (< 768px) --}}
    <div class="d-md-none p-3">
        @forelse($users as $u)
            <div class="card shadow-sm border rounded-3 mb-3 overflow-hidden bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                            <div class="avatar bg-success text-white fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div style="min-width: 0;">
                                <h6 class="fw-bold mb-0 text-dark text-truncate">{{ $u->name }}</h6>
                                <span class="badge bg-light text-dark border px-2 py-0" style="font-size: 0.72rem;">
                                    {{ $u->role?->display_name ?? 'No Role' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            @if($u->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('users.status_active_pill') }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>{{ __('users.status_inactive_pill') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Contact & Status Box --}}
                    <div class="p-2 rounded-2 bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted"><i class="bi bi-telephone text-success me-1"></i>Mobile</span>
                            <a href="tel:{{ $u->mobile }}" class="fw-semibold text-dark text-decoration-none small font-monospace">+91 {{ $u->mobile }}</a>
                        </div>
                        @if($u->email)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted"><i class="bi bi-envelope me-1"></i>Email</span>
                                <span class="small text-muted text-truncate" style="max-width: 180px;">{{ $u->email }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted"><i class="bi bi-clock-history me-1"></i>Last Active</span>
                            <span class="small text-muted font-monospace">{{ $u->last_login_at ? fmt_date($u->last_login_at, true) : __('users.never_logged_in') }}</span>
                        </div>
                        @if($u->must_change_password)
                            <div class="mt-1 pt-1 border-top text-end">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.7rem;">
                                    <i class="bi bi-key-fill me-1"></i>{{ __('users.temp_pwd_pill') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary px-3">
                            <i class="bi bi-pencil-square me-1"></i> {{ __('users.btn_edit') }}
                        </a>
                        @if($u->id !== auth()->id())
                            <form method="POST" 
                                  action="{{ route('users.toggle', $u) }}" 
                                  class="d-inline"
                                  onsubmit="return confirm('{{ __('users.confirm_toggle') }}');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} px-3">
                                    {{ $u->is_active ? __('users.btn_deactivate') : __('users.btn_activate') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <i class="bi bi-people text-muted d-block fs-1 mb-2"></i>
                <div class="fw-semibold">{{ __('users.empty_title') }}</div>
                <small class="text-muted">{{ __('users.empty_text') }}</small>
                <div class="mt-3">
                    <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-person-plus-fill me-1"></i> {{ __('users.btn_create_first') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
