@extends('layouts.app')

@section('title', 'Activity, Cancellations & Audit Reports')

@section('content')
<div class="page-toolbar">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports Hub</a></li>
                <li class="breadcrumb-item active" aria-current="page">Audit & Activity</li>
            </ol>
        </nav>
        <h2 class="h5 fw-bold mb-1">Cancellations, Growth & Audit Trail</h2>
        <p class="subtitle">Complete auditability for reversed vouchers, user actions, and farmer enrollment trends.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Cancelled & Reversed Transactions --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-x-circle text-danger"></i>
                    Cancelled Purchases & Reversed Vouchers
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type & Ref</th>
                            <th>Date</th>
                            <th>Party / Category</th>
                            <th class="text-end">Amount (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cancelledData['cancelled_purchases'] as $p)
                            <tr>
                                <td>
                                    <div class="fw-bold">Purchase #{{ $p->purchase_id }}</div>
                                    <small class="text-muted">{{ $p->crop?->name }} ({{ $p->net_weight_qtl }} Qtl)</small>
                                </td>
                                <td class="small text-muted">{{ $p->purchase_date->format('d M Y') }}</td>
                                <td>{{ $p->farmer?->name }}</td>
                                <td class="text-end fw-semibold">{{ money($p->net_payable) }}</td>
                                <td><span class="badge bg-danger-subtle text-danger">Cancelled</span></td>
                            </tr>
                        @empty
                        @endforelse

                        @forelse($cancelledData['reversed_expenses'] as $e)
                            <tr>
                                <td>
                                    <div class="fw-bold">Expense #{{ $e->voucher_number }}</div>
                                    <small class="text-muted">{{ $e->description }}</small>
                                </td>
                                <td class="small text-muted">{{ $e->expense_date->format('d M Y') }}</td>
                                <td>{{ $e->category?->name }}</td>
                                <td class="text-end fw-semibold">{{ money($e->amount) }}</td>
                                <td><span class="badge bg-danger-subtle text-danger">Reversed</span></td>
                            </tr>
                        @empty
                        @endforelse

                        @if($cancelledData['cancelled_purchases']->isEmpty() && $cancelledData['reversed_expenses']->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                                    Zero cancelled or reversed transactions found.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Traceable Audit Log entries for Reversals --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-primary"></i>
                    Reversal Audit Trail (Who reversed what and why)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Reason / Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cancelledData['reversal_logs'] as $log)
                            <tr>
                                <td class="small text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                <td class="fw-semibold">{{ $log->user?->name ?? 'System' }}</td>
                                <td><code>{{ $log->action }}</code></td>
                                <td class="small text-muted">
                                    {{ $log->details['reason'] ?? json_encode($log->details) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No reversal logs recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Farmer Registration Growth --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-graph-up text-success"></i>
                    Farmer Enrollment Growth
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($farmerGrowth as $growth)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="fw-semibold">{{ $growth['month'] }}</span>
                            <span class="badge bg-success-subtle text-success fs-6">
                                +{{ $growth['count'] }} Farmers
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card border-0 bg-light p-3 small text-muted">
            <i class="bi bi-info-circle text-primary me-1"></i>
            All reversals in Kisan Enterprises permanently preserve original draft records and log an immutable audit event for financial transparency.
        </div>
    </div>
</div>
@endsection
