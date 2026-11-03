@extends('layouts.app')

@section('title', 'Expense Voucher ' . $expense->voucher_number)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item active">{{ $expense->voucher_number }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <h2 class="h4 mb-0">{{ $expense->voucher_number }}</h2>
            @if($expense->isReversed())
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Reversed</span>
            @else
                <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @if(!$expense->isReversed())
            @can('expenses.approve')
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reverseExpenseModal">
                    <i class="bi bi-arrow-counterclockwise"></i> Reverse Voucher
                </button>
            @endcan
        @endif
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> All Expenses
        </a>
    </div>
</div>

@if($expense->isReversed())
    <div class="alert alert-danger shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2 fw-semibold">
            <i class="bi bi-exclamation-triangle-fill"></i>
            This expense voucher was reversed on {{ fmt_date($expense->reversed_at, 'd M Y, h:i A') }} by {{ $expense->reverser?->name ?? 'System' }}.
        </div>
        <div class="mt-1 small">
            <strong>Reversal Reason:</strong> {{ $expense->reversal_reason }}
        </div>
    </div>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-receipt text-primary me-1"></i> Voucher Particulars</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light text-muted fw-semibold" style="width: 30%;">Voucher Number</th>
                            <td class="fw-bold">{{ $expense->voucher_number }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Expense Date</th>
                            <td>{{ fmt_date($expense->expense_date) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Category</th>
                            <td><span class="badge bg-light text-dark border fs-6">{{ $expense->category->name }}</span></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Amount</th>
                            <td class="fs-4 fw-bold text-danger">{{ money($expense->amount) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Paid To / Beneficiary</th>
                            <td class="fw-semibold">{{ $expense->paid_to }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Payment Method</th>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $expense->method_label }}</span></td>
                        </tr>
                        @if($expense->account)
                            <tr>
                                <th class="bg-light text-muted fw-semibold">Deducted From Account</th>
                                <td>{{ $expense->account->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Reference Number</th>
                            <td>{{ $expense->transaction_reference ?? '-' }}</td>
                        </tr>
                        @if($expense->remarks)
                            <tr>
                                <th class="bg-light text-muted fw-semibold">Remarks</th>
                                <td>{{ $expense->remarks }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-clock-history text-muted me-1"></i> Audit Information</h5>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded By:</span>
                    <span>{{ $expense->creator?->name ?? 'System' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded At:</span>
                    <span>{{ fmt_date($expense->created_at, 'd M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reversal Modal --}}
@can('expenses.approve')
@if(!$expense->isReversed())
<div class="modal fade" id="reverseExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('expenses.reverse', $expense) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title h6 text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i> Reverse Expense Voucher</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Reversing this expense of <strong>{{ money($expense->amount) }}</strong> will restore the account balance and exclude it from Profit & Loss.
                    </p>
                    <div class="mb-3">
                        <label class="form-label required">Reason for Reversal</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="Provide mandatory reason (minimum 5 characters)..." required minlength="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Reversal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

@endsection
