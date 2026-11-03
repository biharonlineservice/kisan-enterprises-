@extends('layouts.app')

@section('title', __('maintenance.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-database-check text-success"></i>
                    <strong>{{ __('maintenance.banner_backups', ['count' => $metrics['totalBackups']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-hdd-stack"></i>
                    {{ __('maintenance.banner_size', ['size' => $metrics['totalSizeFormatted']]) }}
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex font-monospace" style="white-space: nowrap;">
                    <i class="bi bi-server"></i>
                    {{ __('maintenance.banner_env', ['php' => $metrics['phpVersion'], 'db' => $metrics['databaseName']]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-pie-chart-fill text-warning"></i>
                    <strong>{{ __('maintenance.banner_disk', ['space' => $metrics['diskFreeSpace']]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{!! 'Database Backup & System Maintenance' !!}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 780px;">
                {{ __('maintenance.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <form action="{{ route('maintenance.backup.create') }}" method="POST" onsubmit="return confirm('{{ __('maintenance.confirm_create') }}');" class="d-inline">
                @csrf
                <button type="submit" class="erp-hero-btn" style="background: #10b981; border-color: #059669; color: #ffffff;" title="{{ __('maintenance.btn_create_backup') }}">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                    <span>{{ __('maintenance.btn_create_backup') }}</span>
                </button>
            </form>
            @if(\Illuminate\Support\Facades\Route::has('settings.edit'))
                <a href="{{ route('settings.edit') }}" class="erp-hero-btn" title="{{ __('maintenance.btn_settings') }}">
                    <i class="bi bi-gear"></i>
                    <span>{{ __('maintenance.btn_settings') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('audit-logs.index'))
                <a href="{{ route('audit-logs.index') }}" class="erp-hero-btn" title="{{ __('maintenance.btn_audit_logs') }}">
                    <i class="bi bi-shield-check"></i>
                    <span>{{ __('maintenance.btn_audit_logs') }}</span>
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('audit'))
                <a href="{{ route('audit') }}" class="erp-hero-btn" title="{{ __('maintenance.btn_reports') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>{{ __('maintenance.btn_reports') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Stored SQL Snapshots --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('maintenance.index', ['tab' => 'backups']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('maintenance.kpi_backups') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-database-check"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ $metrics['totalBackups'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('maintenance.kpi_backups_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Backup Storage Footprint --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('maintenance.index', ['tab' => 'backups']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('maintenance.kpi_size') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-hdd-stack"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ $metrics['totalSizeFormatted'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('maintenance.kpi_size_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Active Database Engine --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('maintenance.index', ['tab' => 'telemetry']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('maintenance.kpi_database') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-server"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ $metrics['databaseName'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('maintenance.kpi_database_sub', ['driver' => $metrics['databaseDriver']]) }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Server Free Space --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('maintenance.index', ['tab' => 'telemetry']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('maintenance.kpi_disk') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ $metrics['diskFreeSpace'] }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('maintenance.kpi_disk_sub') }}
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
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'backups' ? 'active' : '' }}" 
                   href="{{ route('maintenance.index', ['tab' => 'backups']) }}">
                    <i class="bi bi-database-check"></i>
                    <span>{{ __('maintenance.tab_backups') }}</span>
                    <span class="badge {{ $tab === 'backups' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $metrics['totalBackups'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'recovery' ? 'active' : '' }}" 
                   href="{{ route('maintenance.index', ['tab' => 'recovery']) }}">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>{{ __('maintenance.tab_recovery') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'automation' ? 'active' : '' }}" 
                   href="{{ route('maintenance.index', ['tab' => 'automation']) }}">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('maintenance.tab_automation') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'telemetry' ? 'active' : '' }}" 
                   href="{{ route('maintenance.index', ['tab' => 'telemetry']) }}">
                    <i class="bi bi-cpu"></i>
                    <span>{{ __('maintenance.tab_telemetry') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'all' ? 'active' : '' }}" 
                   href="{{ route('maintenance.index', ['tab' => 'all']) }}">
                    <i class="bi bi-layers-fill"></i>
                    <span>{{ __('maintenance.tab_all') }}</span>
                </a>
            </li>
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>AES-256 Storage
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-file-lock2 me-1"></i>Audit Logged
            </span>
        </div>
    </div>
</div>

{{-- 4. Stored Backups Module --}}
@if($tab === 'backups' || $tab === 'all')
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success p-2 rounded-2">
                        <i class="bi bi-database-check fs-6"></i>
                    </span>
                    <span>{{ __('maintenance.table_title', ['count' => count($backupFiles)]) }}</span>
                </h6>
                <small class="text-muted d-block mt-1">Directly download SQL dumps or remove old snapshots.</small>
            </div>
            <form action="{{ route('maintenance.backup.create') }}" method="POST" onsubmit="return confirm('{{ __('maintenance.confirm_create') }}');" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> {{ __('maintenance.btn_create_backup') }}
                </button>
            </form>
        </div>

        {{-- Desktop Table View (> 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('maintenance.col_filename') }}</th>
                        <th style="width: 140px;">{{ __('maintenance.col_size') }}</th>
                        <th style="width: 220px;">{{ __('maintenance.col_created') }}</th>
                        <th class="text-end pe-4" style="width: 160px;">{{ __('maintenance.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backupFiles as $backup)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                        <i class="bi bi-file-earmark-code"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark font-monospace">{{ $backup['filename'] }}</div>
                                        <small class="text-muted font-monospace">storage/app/backups/{{ $backup['filename'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                    {{ $backup['size_formatted'] }}
                                </span>
                            </td>
                            <td>
                                <span class="small font-monospace text-dark d-block">
                                    {{ fmt_date($backup['created_at'], true) }}
                                </span>
                                <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                    {{ $backup['created_at']->diffForHumans() }}
                                </small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('maintenance.backup.download', $backup['filename']) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="{{ __('maintenance.btn_download') }}">
                                        <i class="bi bi-download me-1"></i> {{ __('maintenance.btn_download') }}
                                    </a>
                                    <form action="{{ route('maintenance.backup.delete', $backup['filename']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('maintenance.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="{{ __('maintenance.btn_delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-hdd-stack fs-1 text-muted d-block mb-2"></i>
                                <div class="fw-semibold">{{ __('maintenance.empty_title') }}</div>
                                <small class="text-muted">{{ __('maintenance.empty_text') }}</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card Feed View (< 768px) --}}
        <div class="d-md-none p-3">
            @forelse($backupFiles as $backup)
                <div class="card shadow-sm border rounded-3 mb-3 bg-white overflow-hidden">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                    <i class="bi bi-file-earmark-code"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark font-monospace text-break" style="font-size: 0.85rem;">{{ $backup['filename'] }}</h6>
                                    <span class="badge bg-light text-dark border font-monospace mt-1">{{ $backup['size_formatted'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-2 rounded-2 bg-light border mb-3 small text-muted font-monospace">
                            <i class="bi bi-clock-history text-success me-1"></i>
                            {{ fmt_date($backup['created_at'], true) }} ({{ $backup['created_at']->diffForHumans() }})
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                            <a href="{{ route('maintenance.backup.download', $backup['filename']) }}" class="btn btn-sm btn-outline-primary px-3">
                                <i class="bi bi-download me-1"></i> {{ __('maintenance.btn_download') }}
                            </a>
                            <form action="{{ route('maintenance.backup.delete', $backup['filename']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('maintenance.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                                    <i class="bi bi-trash me-1"></i> {{ __('maintenance.btn_delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-hdd-stack fs-1 d-block mb-2 text-muted"></i>
                    <p class="mb-0 fw-semibold">{{ __('maintenance.empty_title') }}</p>
                </div>
            @endforelse
        </div>
    </div>
@endif

{{-- 5. Restoration & Automation Modules --}}
@if($tab === 'recovery' || $tab === 'automation' || $tab === 'telemetry' || $tab === 'all')
    <div class="row g-4">
        {{-- Left Column: Recovery Guide & Telemetry --}}
        <div class="{{ $tab === 'all' ? 'col-lg-7' : 'col-12' }}">
            @if($tab === 'recovery' || $tab === 'all')
                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-2">
                                <i class="bi bi-arrow-counterclockwise fs-6"></i>
                            </span>
                            <span>{{ __('maintenance.restore_title') }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-3">
                            {{ __('maintenance.restore_desc') }}
                        </p>
                        <div class="bg-dark text-light p-3 rounded-3 mb-3 small font-monospace overflow-auto" style="line-height: 1.6;">
                            <span class="text-white-50"># 1. SSH into your host or open Terminal</span><br>
                            <span class="text-white-50"># 2. Run the MySQL restore command:</span><br>
                            <span class="text-success">mysql -u {{ config('database.connections.mysql.username') }} -p {{ config('database.connections.mysql.database') }} &lt; storage/app/backups/kisan_erp_backup_YYYY_MM_DD_HHMMSS.sql</span>
                        </div>
                        <div class="alert alert-warning border-warning-subtle bg-warning-subtle bg-opacity-25 small mb-0 d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0 mt-1"></i>
                            <div>
                                <strong>Caution / चेतावनी:</strong>
                                <p class="mb-0 mt-1 text-dark">
                                    {{ __('maintenance.restore_caution') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($tab === 'telemetry' || $tab === 'all')
                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-primary p-2 rounded-2">
                                <i class="bi bi-cpu fs-6"></i>
                            </span>
                            <span>{{ __('maintenance.telemetry_title') }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <small class="text-muted d-block mb-1">{{ __('maintenance.field_php') }}</small>
                                    <div class="fw-bold text-dark font-monospace">PHP {{ $systemInfo['php_version'] }}</div>
                                    <small class="text-muted">Laravel {{ $systemInfo['laravel_version'] }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <small class="text-muted d-block mb-1">{{ __('maintenance.field_os') }}</small>
                                    <div class="fw-bold text-dark font-monospace">{{ $systemInfo['os'] }}</div>
                                    <small class="text-muted text-truncate d-block">{{ $systemInfo['server_software'] }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <small class="text-muted d-block mb-1">{{ __('maintenance.field_db_name') }}</small>
                                    <div class="fw-bold text-success font-monospace">{{ $systemInfo['database_name'] }}</div>
                                    <small class="text-muted">{{ ucfirst($systemInfo['database_driver']) }} Engine</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <small class="text-muted d-block mb-1">{{ __('maintenance.field_disk_free') }}</small>
                                    <div class="fw-bold text-primary font-monospace">{{ $systemInfo['disk_free_space'] }}</div>
                                    <small class="text-muted">Server Filesystem Capacity</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column: Cron Automation & Retention --}}
        <div class="{{ $tab === 'all' ? 'col-lg-5' : 'col-12' }}">
            @if($tab === 'automation' || $tab === 'all')
                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-success text-white py-3">
                        <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history"></i>
                            <span>{{ __('maintenance.cron_title') }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-3">
                            {{ __('maintenance.cron_desc') }}
                        </p>

                        <h6 class="fw-bold small text-dark mb-1">{{ __('maintenance.cron_schedule') }}</h6>
                        <div class="bg-light p-2 rounded-2 small font-monospace mb-2 border text-success fw-bold">
                            0 2 * * *
                        </div>
                        <small class="text-muted d-block mb-3">{{ __('maintenance.cron_schedule_sub') }}</small>

                        <h6 class="fw-bold small text-dark mb-1">{{ __('maintenance.cron_command') }}</h6>
                        <div class="bg-dark text-light p-2 rounded-2 small font-monospace mb-3" style="font-size: 0.75rem; word-break: break-all; line-height: 1.5;">
                            mysqldump -u {{ config('database.connections.mysql.username') }} -p'YOUR_PASSWORD' {{ config('database.connections.mysql.database') }} &gt; /home/username/backups/kisan_$(date +\%F).sql
                        </div>

                        <div class="border-top pt-3 small text-muted">
                            <strong class="d-block text-dark mb-1">{{ __('maintenance.retention_title') }}</strong>
                            &bull; {{ __('maintenance.retention_daily') }}<br>
                            &bull; {{ __('maintenance.retention_monthly') }}<br>
                            &bull; {{ __('maintenance.retention_offsite') }}
                        </div>
                    </div>
                </div>

                <div class="card border-0 bg-success-subtle bg-opacity-25 rounded-3 p-3 small text-dark d-flex align-items-start gap-2">
                    <i class="bi bi-shield-check text-success fs-5 flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Immutable Audit Trail:</strong>
                        <p class="mb-0 mt-1 text-secondary">
                            {{ __('maintenance.audit_notice') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection
