@extends('layouts.app')

@section('title', __('audit_logs.show_title', ['id' => $log->id]))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-shield-check text-success"></i>
                    <strong>#{{ $log->id }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex font-monospace" style="white-space: nowrap;">
                    <i class="bi bi-clock-history"></i>
                    {{ fmt_date($log->created_at, true) }}
                </span>
                <span class="erp-hero-chip font-monospace" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-code-slash text-warning"></i>
                    <strong>{{ $log->action }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('audit_logs.show_title', ['id' => $log->id]) }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 780px;">
                {{ __('audit_logs.show_subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('audit-logs.index') }}" class="erp-hero-btn" title="{{ __('audit_logs.btn_back') }}">
                <i class="bi bi-arrow-left"></i>
                <span>{{ __('audit_logs.btn_back') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Action Metadata --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success p-2 rounded-2">
                        <i class="bi bi-info-circle fs-6"></i>
                    </span>
                    <span>{{ __('audit_logs.card_metadata') }}</span>
                </h6>
            </div>
            <div class="card-body p-4">
                <dl class="row mb-0 gy-3">
                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_log_id') }}</dt>
                    <dd class="col-sm-8 fw-bold text-dark font-monospace">#{{ $log->id }}</dd>

                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_timestamp') }}</dt>
                    <dd class="col-sm-8 text-dark">
                        <span class="font-monospace fw-semibold">{{ fmt_date($log->created_at, true) }}</span>
                        <small class="text-muted d-block font-monospace">({{ $log->created_at->diffForHumans() }})</small>
                    </dd>

                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_user') }}</dt>
                    <dd class="col-sm-8">
                        @if($log->user)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                </div>
                                <span class="fw-bold text-dark">{{ $log->user->name }}</span>
                            </div>
                            <div class="small text-muted">{{ $log->user->email }}</div>
                            <span class="badge bg-light text-secondary border mt-1 font-monospace" style="font-size: 0.7rem;">
                                {{ $log->user->role?->display_name ?? 'Staff User' }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">
                                <i class="bi bi-cpu me-1"></i> {{ __('audit_logs.system_user') }}
                            </span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_action') }}</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace py-1 px-2">
                            {{ $log->action }}
                        </span>
                    </dd>

                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_subject') }}</dt>
                    <dd class="col-sm-8">
                        @if($log->auditable_type)
                            <span class="badge bg-light text-dark border font-monospace">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </span>
                            <small class="text-muted d-block mt-1 font-monospace" style="font-size: 0.75rem;">
                                {{ $log->auditable_type }}
                            </small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_desc') }}</dt>
                    <dd class="col-sm-8 text-secondary small bg-light p-2 rounded-2 border">
                        {{ $log->description ?: '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_ip') }}</dt>
                    <dd class="col-sm-8 font-monospace text-dark small">
                        <i class="bi bi-hdd-network text-success me-1"></i>
                        {{ $log->ip_address ?: '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted small">{{ __('audit_logs.field_ua') }}</dt>
                    <dd class="col-sm-8 small text-muted text-break font-monospace" style="font-size: 0.75rem;">
                        {{ $log->user_agent ?: '—' }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Right Column: State Changes / Payload Diff --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-3 h-100 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-2">
                        <i class="bi bi-code-slash fs-6"></i>
                    </span>
                    <span>{{ __('audit_logs.card_payload') }}</span>
                </h6>
            </div>
            <div class="card-body p-4">
                @if(!empty($log->old_values))
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <i class="bi bi-dash-circle me-1"></i> {{ __('audit_logs.diff_before') }}
                            </span>
                        </div>
                        <pre class="bg-danger-subtle bg-opacity-25 border border-danger border-opacity-25 rounded-3 p-3 font-monospace text-danger-emphasis small overflow-auto" style="max-height: 280px;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                @if(!empty($log->new_values))
                    <div class="mb-2">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="bi bi-plus-circle me-1"></i> {{ __('audit_logs.diff_after') }}
                            </span>
                        </div>
                        <pre class="bg-success-subtle bg-opacity-25 border border-success border-opacity-25 rounded-3 p-3 font-monospace text-success-emphasis small overflow-auto" style="max-height: 280px;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                @if(empty($log->old_values) && empty($log->new_values))
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-info-circle text-muted fs-1 d-block mb-2"></i>
                        <h6 class="fw-semibold">{{ __('audit_logs.no_diff') }}</h6>
                        <small class="text-muted">This action did not record explicit old/new model attribute diffs.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
