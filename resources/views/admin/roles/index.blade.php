@extends('layouts.app')

@section('title', __('roles.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-shield-check"></i>
                    <strong>{{ __('roles.banner_roles', ['count' => $metrics['totalRoles']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-key-fill"></i>
                    {{ __('roles.banner_permissions', ['count' => $metrics['totalPermissions']]) }}
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-people-fill"></i>
                    {{ __('roles.banner_staff', ['count' => $metrics['assignedStaff']]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-folder2-open text-success"></i>
                    <strong>{{ __('roles.banner_groups', ['count' => $metrics['permissionGroups']]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('roles.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('roles.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            @if(\Illuminate\Support\Facades\Route::has('users.index'))
                <a href="{{ route('users.index') }}" class="erp-hero-btn" title="{{ __('roles.btn_users') }}">
                    <i class="bi bi-people"></i>
                    <span>{{ __('roles.btn_users') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('audit-logs.index'))
                <a href="{{ route('audit-logs.index') }}" class="erp-hero-btn" title="{{ __('roles.btn_audit_logs') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('roles.btn_audit_logs') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('settings.edit'))
                <a href="{{ route('settings.edit') }}" class="erp-hero-btn" title="{{ __('roles.btn_settings') }}">
                    <i class="bi bi-gear"></i>
                    <span>{{ __('roles.btn_settings') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Enterprise Roles --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('roles.index', ['tab' => 'overview']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('roles.kpi_total_roles') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-shield-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($metrics['totalRoles']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('roles.kpi_total_roles_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Granular Permissions --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('roles.index', ['tab' => 'all']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('roles.kpi_total_permissions') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-key-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format($metrics['totalPermissions']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('roles.kpi_total_permissions_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Assigned Staff Coverage --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('users.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('roles.kpi_assigned_staff') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($metrics['assignedStaff']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('roles.kpi_assigned_staff_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Security Groups --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('roles.index', ['tab' => 'overview']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('roles.kpi_permission_groups') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-folder2-open"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ number_format($metrics['permissionGroups']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('roles.kpi_permission_groups_sub') }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Navigation Tabs Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'overview' ? 'active' : '' }}" 
                   href="{{ route('roles.index', ['tab' => 'overview']) }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>{{ __('roles.tab_overview') }}</span>
                </a>
            </li>
            @foreach($roles as $r)
                <li class="nav-item">
                    <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === $r->name ? 'active' : '' }}" 
                       href="{{ route('roles.index', ['tab' => $r->name]) }}">
                        <i class="bi {{ $r->isAdmin() ? 'bi-shield-lock-fill text-warning' : 'bi-shield-check text-success' }}"></i>
                        <span>{{ $r->display_name }}</span>
                        <span class="badge {{ $tab === $r->name ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $r->users_count }}
                        </span>
                    </a>
                </li>
            @endforeach
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'all' ? 'active' : '' }}" 
                   href="{{ route('roles.index', ['tab' => 'all']) }}">
                    <i class="bi bi-layers-fill"></i>
                    <span>{{ __('roles.tab_all') }}</span>
                </a>
            </li>
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Least Privilege
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-lock2 me-1"></i>Access Control
            </span>
        </div>
    </div>
</div>

{{-- 4. Overview Tab (Cards for all 3 roles) --}}
@if($tab === 'overview')
    <div class="row g-4 mb-4">
        @foreach($roles as $role)
            @php
                $permCount = $role->isAdmin() ? $metrics['totalPermissions'] : $role->permissions->count();
                $pct = $metrics['totalPermissions'] > 0 ? round(($permCount / $metrics['totalPermissions']) * 100) : 0;
                $borderClass = $role->isAdmin() ? 'border-warning' : 'border-success';
                $roleIcon = $role->isAdmin() ? 'bi-shield-lock-fill text-warning' : 'bi-shield-check text-success';
            @endphp
            <div class="col-12 col-lg-4">
                <div class="card h-100 shadow-sm border rounded-3 p-4 bg-white d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge {{ $role->isAdmin() ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} px-3 py-2 rounded-pill font-monospace">
                                <i class="bi {{ $roleIcon }} me-1"></i> {{ strtoupper($role->name) }}
                            </span>
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="bi bi-people me-1"></i> {{ __('roles.assigned_users_count', ['count' => $role->users_count]) }}
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-2">{{ $role->display_name }}</h5>
                        <p class="small text-muted mb-3" style="min-height: 40px;">
                            {{ $role->description }}
                        </p>

                        {{-- Permission Coverage Progress Bar --}}
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-dark">Permissions Authority</span>
                                <span class="small font-monospace fw-bold {{ $role->isAdmin() ? 'text-warning-emphasis' : 'text-success' }}">{{ $pct }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $role->isAdmin() ? 'bg-warning' : 'bg-success' }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                                {{ __('roles.coverage_label', ['count' => $permCount, 'total' => $metrics['totalPermissions'], 'pct' => $pct]) }}
                            </small>
                        </div>

                        {{-- Assigned Users Avatars --}}
                        <div class="mb-3">
                            <small class="text-muted fw-semibold d-block mb-2">Assigned Personnel:</small>
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                @forelse($role->users as $u)
                                    <span class="badge bg-white text-dark border d-inline-flex align-items-center gap-1 px-2 py-1" title="{{ $u->name }} ({{ $u->is_active ? 'Active' : 'Inactive' }})">
                                        <span class="rounded-circle {{ $u->is_active ? 'bg-success' : 'bg-secondary' }}" style="width: 7px; height: 7px; display: inline-block;"></span>
                                        <span>{{ Str::limit($u->name, 16) }}</span>
                                    </span>
                                @empty
                                    <span class="small text-muted fst-italic">No users currently assigned to this role.</span>
                                @endforelse
                                @if($role->users_count > 6)
                                    <span class="badge bg-light text-muted border">+{{ $role->users_count - 6 }} more</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top mt-2">
                        @if($role->isAdmin())
                            <a href="{{ route('roles.index', ['tab' => $role->name]) }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">
                                <i class="bi bi-eye me-1"></i> View Full Authority Scope
                            </a>
                        @else
                            <a href="{{ route('roles.index', ['tab' => $role->name]) }}" class="btn btn-sm btn-success w-100 fw-semibold shadow-sm">
                                <i class="bi bi-sliders me-1"></i> {{ __('roles.btn_manage_permissions') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- 5. Individual Role Matrices or Stacked All View --}}
@php
    $displayRoles = ($tab === 'all') 
        ? $roles 
        : (($tab !== 'overview') ? $roles->where('name', $tab) : collect());
@endphp

@foreach($displayRoles as $role)
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
        {{-- Role Header --}}
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge {{ $role->isAdmin() ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} px-2 py-1 font-monospace">
                            <i class="bi {{ $role->isAdmin() ? 'bi-shield-lock-fill' : 'bi-shield-check' }} me-1"></i>
                            {{ strtoupper($role->name) }}
                        </span>
                        <h5 class="mb-0 fw-bold text-dark">{{ $role->display_name }}</h5>
                        <span class="badge bg-light text-dark border ms-1">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</span>
                    </div>
                    <small class="text-muted">{{ $role->description }}</small>
                </div>

                @if(!$role->isAdmin())
                    <button type="submit" form="role-form-{{ $role->id }}" class="btn btn-sm btn-success fw-semibold shadow-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> {{ __('roles.btn_save_permissions') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            @if($role->isAdmin())
                {{-- Super Admin Notice Box --}}
                <div class="p-5 text-center bg-light-subtle">
                    <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-check fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">{{ __('roles.admin_notice_title') }}</h5>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 620px;">
                        {{ __('roles.admin_notice_desc') }}
                    </p>
                    <div class="d-inline-flex flex-wrap align-items-center justify-content-center gap-2">
                        @foreach($groups as $groupName => $perms)
                            <span class="badge bg-white text-dark border px-3 py-2 shadow-sm">
                                <i class="bi bi-check2 text-success me-1"></i> {{ $groupName }} ({{ $perms->count() }})
                            </span>
                        @endforeach
                    </div>
                </div>
            @else
                <form id="role-form-{{ $role->id }}" method="POST" action="{{ route('roles.update', $role) }}">
                    @csrf
                    @method('PUT')
                    <x-form-token />

                    {{-- Quick Action Toolbar --}}
                    <div class="p-3 bg-light border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-semibold text-dark">Quick Select:</span>
                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-3 fw-semibold" style="font-size: 0.78rem;" onclick="toggleRoleCheckboxes({{ $role->id }}, true)">
                                <i class="bi bi-check-all me-1"></i> {{ __('roles.btn_select_all') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-3 fw-semibold" style="font-size: 0.78rem;" onclick="toggleRoleCheckboxes({{ $role->id }}, false)">
                                <i class="bi bi-x me-1"></i> {{ __('roles.btn_deselect_all') }}
                            </button>
                        </div>

                        <div class="input-group input-group-sm" style="max-width: 320px;">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" placeholder="{{ __('roles.search_perm_placeholder') }}" oninput="filterPermissionsTable({{ $role->id }}, this.value)">
                        </div>
                    </div>

                    {{-- Permission Groups Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="perms-table-{{ $role->id }}">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 25%;">{{ __('roles.col_module') }}</th>
                                    <th style="width: 50%;">{{ __('roles.col_permission') }}</th>
                                    <th style="width: 25%;" class="text-center pe-3">{{ __('roles.col_status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentRolePermIds = $role->permissions->pluck('id')->all(); @endphp
                                @foreach($groups as $groupName => $permissions)
                                    @php $groupSlug = Str::slug($groupName); @endphp
                                    <tr class="table-light-subtle perm-group-header" data-group="{{ $groupSlug }}">
                                        <td colspan="2" class="fw-bold text-dark py-2 ps-3">
                                            <i class="bi bi-folder2-open text-success me-2"></i>
                                            <span>{{ $groupName }}</span>
                                            <span class="badge bg-light text-dark border ms-2" style="font-size: 0.72rem;">{{ $permissions->count() }} permissions</span>
                                        </td>
                                        <td class="text-center pe-3 py-2">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.7rem;" onclick="toggleGroupCheckboxes({{ $role->id }}, '{{ $groupSlug }}', true)">
                                                    {{ __('roles.btn_select_group') }}
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.7rem;" onclick="toggleGroupCheckboxes({{ $role->id }}, '{{ $groupSlug }}', false)">
                                                    {{ __('roles.btn_deselect_group') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach($permissions as $perm)
                                        <tr class="perm-row-{{ $role->id }}" data-group="{{ $groupSlug }}" data-text="{{ strtolower($perm->label . ' ' . $perm->key . ' ' . $groupName) }}">
                                            <td class="text-muted small ps-4">
                                                <span class="badge bg-light text-muted border">{{ $groupName }}</span>
                                            </td>
                                            <td>
                                                <label class="fw-semibold text-dark mb-0 d-block cursor-pointer" for="perm_{{ $role->id }}_{{ $perm->id }}">
                                                    {{ $perm->label }}
                                                </label>
                                                <code class="small text-muted font-monospace" style="font-size: 0.72rem;">{{ $perm->key }}</code>
                                            </td>
                                            <td class="text-center pe-3">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input type="checkbox" 
                                                           class="form-check-input role-perm-{{ $role->id }} group-perm-{{ $role->id }}-{{ $groupSlug }}" 
                                                           name="permissions[]" 
                                                           value="{{ $perm->id }}" 
                                                           id="perm_{{ $role->id }}_{{ $perm->id }}"
                                                           style="cursor: pointer; width: 2.4em; height: 1.25em;"
                                                           {{ in_array($perm->id, $currentRolePermIds, true) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Form Footer --}}
                    <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">
                            Changes take effect immediately upon saving.
                        </span>
                        <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> {{ __('roles.btn_save_for_role', ['role' => $role->display_name]) }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endforeach

@endsection

@push('scripts')
<script>
function toggleRoleCheckboxes(roleId, state) {
    document.querySelectorAll('.role-perm-' + roleId).forEach(function(cb) {
        cb.checked = state;
    });
}

function toggleGroupCheckboxes(roleId, groupSlug, state) {
    document.querySelectorAll('.group-perm-' + roleId + '-' + groupSlug).forEach(function(cb) {
        cb.checked = state;
    });
}

function filterPermissionsTable(roleId, query) {
    var term = query.toLowerCase().trim();
    var rows = document.querySelectorAll('.perm-row-' + roleId);
    rows.forEach(function(row) {
        var text = row.getAttribute('data-text') || '';
        if (!term || text.indexOf(term) !== -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endpush
