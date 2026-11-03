@extends('layouts.app')

@section('title', __('inventory.adjustments.title') . ' — ' . $adjustment->adjustment_number)

@section('content')
{{-- Header with Breadcrumbs & Actions --}}
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-start align-items-xl-center gap-3 mb-3 pb-2 border-bottom">
    <div class="flex-grow-1" style="min-width: 0;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('stock-adjustments.index') }}" class="text-success text-decoration-none">{{ __('inventory.adjustments.title') }}</a></li>
                <li class="breadcrumb-item active text-truncate">{{ $adjustment->adjustment_number }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h2 class="h5 fw-bold mb-0 text-dark font-monospace text-nowrap">{{ $adjustment->adjustment_number }}</h2>
            @if($adjustment->status === 'approved')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 text-nowrap">
                    <i class="bi bi-check-circle me-1"></i>{{ __('inventory.adjustments.status_approved') }}
                </span>
            @elseif($adjustment->status === 'pending')
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 text-nowrap">
                    <i class="bi bi-hourglass-split me-1"></i>{{ __('inventory.adjustments.status_pending') }}
                </span>
            @elseif($adjustment->status === 'rejected')
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 text-nowrap">
                    <i class="bi bi-x-circle me-1"></i>{{ __('inventory.adjustments.status_rejected') }}
                </span>
            @endif
            <span class="badge bg-light text-dark border text-nowrap">
                <i class="bi bi-calendar3 me-1 text-muted"></i>{{ fmt_date($adjustment->adjustment_date) }}
            </span>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2 flex-shrink-0">
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">
            <i class="bi bi-arrow-left me-1"></i> {{ __('inventory.adjustments.tab_all') }}
        </a>
        @if($adjustment->isPending())
            @can('inventory.approve')
                <form method="POST" action="{{ route('stock-adjustments.approve', $adjustment) }}"
                      onsubmit="return confirm('{{ __('inventory.adjustments.confirm_approve_msg') }}');" class="d-inline m-0">
                    @csrf
                    <x-form-token />
                    <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm text-nowrap">
                        <i class="bi bi-check-circle me-1"></i> {{ __('inventory.adjustments.approve_action') }}
                    </button>
                </form>

                <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#rejectAdjModal">
                    <i class="bi bi-x-circle me-1"></i> {{ __('inventory.adjustments.reject_action') }}
                </button>
            @endcan
        @endif
    </div>
</div>

{{-- Rejection Notice Alert --}}
@if($adjustment->isRejected())
    <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-x-circle-fill fs-5"></i>
            <strong class="fs-6">{{ __('inventory.adjustments.status_rejected') }}</strong>
        </div>
        <div class="mt-2 small ps-4">
            <strong>{{ __('inventory.adjustments.rejection_reason') }}:</strong> {{ $adjustment->rejection_reason }}
            @if($adjustment->approver)
                <div class="text-muted mt-1">
                    {{ __('inventory.adjustments.rejected_by') }}: <strong>{{ $adjustment->approver->name }}</strong>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Workflow Step Ribbon (Compact & Responsive) --}}
<div class="card shadow-sm border-0 bg-white rounded-3 mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-circle bg-success text-white" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                    <i class="bi bi-check-lg"></i>
                </span>
                <div>
                    <span class="fw-bold small text-dark d-block">{{ __('inventory.adjustments.step_requested') }}</span>
                    <span class="text-muted" style="font-size: 0.72rem;">{{ fmt_date($adjustment->created_at, 'd M Y, h:i A') }}</span>
                </div>
            </div>

            <i class="bi bi-chevron-right text-muted d-none d-md-block opacity-50"></i>

            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-circle {{ $adjustment->isApproved() ? 'bg-success text-white' : ($adjustment->isPending() ? 'bg-warning text-dark' : 'bg-danger text-white') }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                    <i class="bi {{ $adjustment->isApproved() ? 'bi-check-lg' : ($adjustment->isPending() ? 'bi-hourglass-split' : 'bi-x-lg') }}"></i>
                </span>
                <div>
                    <span class="fw-bold small text-dark d-block">{{ __('inventory.adjustments.step_review') }}</span>
                    <span class="text-muted" style="font-size: 0.72rem;">
                        @if($adjustment->isApproved())
                            Approved by {{ $adjustment->approver?->name ?? 'Manager' }}
                        @elseif($adjustment->isPending())
                            Pending Action
                        @elseif($adjustment->isRejected())
                            Rejected
                        @endif
                    </span>
                </div>
            </div>

            <i class="bi bi-chevron-right text-muted d-none d-md-block opacity-50"></i>

            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-circle {{ $adjustment->isApproved() ? 'bg-success text-white' : 'bg-light text-muted border' }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                    <i class="bi bi-box-seam"></i>
                </span>
                <div>
                    <span class="fw-bold small {{ $adjustment->isApproved() ? 'text-dark' : 'text-muted' }} d-block">{{ __('inventory.adjustments.step_posted') }}</span>
                    <span class="text-muted" style="font-size: 0.72rem;">
                        {{ $adjustment->isApproved() ? 'Stock Ledger Posted' : 'Awaiting Approval' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Main Particulars Card --}}
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 bg-white rounded-3 mb-3">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title h6 mb-0 text-success fw-bold">
                    <i class="bi bi-sliders me-1"></i> {{ __('inventory.adjustments.particulars') }}
                </h5>
                <span class="badge {{ $adjustment->isIncrease() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border">
                    {{ $adjustment->isIncrease() ? __('inventory.adjustments.badge_in') : __('inventory.adjustments.badge_out') }}
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light text-muted fw-semibold" style="width: 32%;">{{ __('inventory.adjustments.col_adj_no') }}</th>
                            <td class="fw-bold text-dark font-monospace">{{ $adjustment->adjustment_number }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">{{ __('inventory.adjustments.field_date') }}</th>
                            <td><i class="bi bi-calendar3 me-1 text-muted"></i>{{ fmt_date($adjustment->adjustment_date) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">{{ __('inventory.adjustments.col_warehouse') }}</th>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1 text-muted"></i>{{ $adjustment->warehouse->name }}
                                </span>
                                <small class="text-muted ms-2">({{ $adjustment->warehouse->code }})</small>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">{{ __('inventory.adjustments.col_commodity') }}</th>
                            <td>
                                <strong class="text-dark">{{ $adjustment->crop->name }}</strong>
                                @if($adjustment->cropVariety)
                                    <span class="text-muted">({{ $adjustment->cropVariety->name }})</span>
                                @else
                                    <span class="text-muted fst-italic">(Standard / Mixed)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">{{ __('inventory.adjustments.col_type') }}</th>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ $adjustment->type_name }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">{{ __('inventory.adjustments.col_quantity_qtl') }}</th>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fs-4 fw-bold font-monospace {{ $adjustment->isIncrease() ? 'text-success' : 'text-danger' }}">
                                        {{ $adjustment->isIncrease() ? '+' : '' }}{{ bc_round($adjustment->quantity_qtl, 3) }} Qtl
                                    </span>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $adjustment->isIncrease() ? '+' : '' }}{{ bc_round($adjustment->quantity_kg, 1) }} Kg
                                    </span>
                                    <span class="badge bg-light text-muted border">
                                        ~{{ $adjustment->est_bags }} {{ __('inventory.col_bags') }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">{{ __('inventory.adjustments.field_reason') }}</th>
                            <td class="text-dark">
                                <div class="p-2 bg-light rounded">
                                    <i class="bi bi-quote me-1 text-muted"></i>{{ $adjustment->reason }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ledger Impact Summary (If Approved) --}}
        @if($adjustment->isApproved())
            <div class="card shadow-sm border-0 bg-white rounded-3 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="fw-bold text-success mb-1">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ __('inventory.adjustments.ledger_impact') }}
                    </h6>
                    <p class="small text-muted mb-2">
                        {{ __('inventory.adjustments.ledger_impact_desc', [
                            'qtl' => ($adjustment->isIncrease() ? '+' : '') . bc_round($adjustment->quantity_qtl, 3),
                            'warehouse' => $adjustment->warehouse->name,
                            'date' => fmt_date($adjustment->approved_at ?? $adjustment->updated_at)
                        ]) }}
                    </p>
                    <a href="{{ route('inventory.movements', ['q' => $adjustment->adjustment_number]) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-clock-history me-1"></i> View Stock Movements Entry
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Audit Trail & Status Sidebar --}}
    <div class="col-12 col-lg-4">
        {{-- Audit Trail Card --}}
        <div class="card shadow-sm border-0 bg-white rounded-3 mb-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title h6 mb-0 text-dark fw-bold">
                    <i class="bi bi-clock-history text-muted me-1"></i> {{ __('inventory.adjustments.audit_trail') }}
                </h5>
            </div>
            <div class="card-body small">
                <div class="d-flex flex-wrap justify-content-between gap-1 mb-2">
                    <span class="text-muted">{{ __('inventory.adjustments.requested_by') }}:</span>
                    <strong class="text-dark">{{ $adjustment->creator?->name ?? 'System' }}</strong>
                </div>
                <div class="d-flex flex-wrap justify-content-between gap-1 mb-2">
                    <span class="text-muted">{{ __('inventory.adjustments.requested_at') }}:</span>
                    <span class="text-nowrap">{{ fmt_date($adjustment->created_at, 'd M Y, h:i A') }}</span>
                </div>
                @if($adjustment->approved_at)
                    <hr class="my-2">
                    <div class="d-flex flex-wrap justify-content-between gap-1 mb-2">
                        <span class="text-muted">{{ __('inventory.adjustments.approved_by') }}:</span>
                        <strong class="text-success">{{ $adjustment->approver?->name ?? 'Manager' }}</strong>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between gap-1 mb-2">
                        <span class="text-muted">{{ __('inventory.adjustments.approved_at') }}:</span>
                        <span class="text-nowrap">{{ fmt_date($adjustment->approved_at, 'd M Y, h:i A') }}</span>
                    </div>
                @endif
                @if($adjustment->isRejected())
                    <hr class="my-2">
                    <div class="d-flex flex-wrap justify-content-between gap-1 mb-2">
                        <span class="text-muted">{{ __('inventory.adjustments.rejected_by') }}:</span>
                        <strong class="text-danger">{{ $adjustment->approver?->name ?? 'Manager' }}</strong>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between gap-1 mb-2">
                        <span class="text-muted">Rejected At:</span>
                        <span class="text-nowrap">{{ fmt_date($adjustment->updated_at, 'd M Y, h:i A') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Status Summary Card --}}
        <div class="card shadow-sm border-0 bg-white rounded-3 border-start border-4 {{ $adjustment->isApproved() ? 'border-success' : ($adjustment->isPending() ? 'border-warning' : 'border-danger') }}">
            <div class="card-body small">
                <h6 class="fw-bold mb-1 {{ $adjustment->isApproved() ? 'text-success' : ($adjustment->isPending() ? 'text-warning-emphasis' : 'text-danger') }}">
                    {{ __('inventory.adjustments.status_summary') }}
                </h6>
                @if($adjustment->isApproved())
                    <p class="text-muted mb-0">
                        This stock adjustment has been approved and permanently posted to the warehouse stock ledger.
                    </p>
                @elseif($adjustment->isPending())
                    <p class="text-muted mb-0">
                        Pending review and approval by an authorized manager. Warehouse stock is not modified until approved.
                    </p>
                @elseif($adjustment->isRejected())
                    <p class="text-muted mb-0">
                        This adjustment was rejected. No changes were applied to warehouse stock or accounting ledgers.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
@can('inventory.approve')
@if($adjustment->isPending())
<div class="modal fade" id="rejectAdjModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('stock-adjustments.reject', $adjustment) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title h6 text-white mb-0">
                        <i class="bi bi-x-circle me-1"></i> {{ __('inventory.adjustments.reject_modal_title') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required small fw-semibold text-muted">{{ __('inventory.adjustments.reject_reason_label') }}</label>
                        <textarea name="reason" rows="3" class="form-control"
                            placeholder="{{ __('inventory.adjustments.reject_reason_placeholder') }}" required minlength="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('inventory.adjustments.cancel_btn') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('inventory.adjustments.confirm_reject') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

@endsection