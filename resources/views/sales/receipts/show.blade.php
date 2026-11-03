@extends('layouts.app')

@section('title', 'Receipt ' . $receipt->receipt_number)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('customer-receipts.index') }}">Customer Receipts</a></li>
                <li class="breadcrumb-item active">{{ $receipt->receipt_number }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <h2 class="h4 mb-0">{{ $receipt->receipt_number }}</h2>
            @if($receipt->isReversed())
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Reversed</span>
            @else
                <span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customer-receipts.print', $receipt) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer"></i> Print Receipt
        </a>

        @if(!$receipt->isReversed())
            @can('payments.reverse')
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reverseReceiptModal">
                    <i class="bi bi-arrow-counterclockwise"></i> Reverse Receipt
                </button>
            @endcan
        @endif
    </div>
</div>

@if($receipt->isReversed())
    <div class="alert alert-danger shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2 fw-semibold">
            <i class="bi bi-exclamation-triangle-fill"></i>
            This receipt voucher was reversed on {{ fmt_date($receipt->reversed_at, 'd M Y, h:i A') }} by {{ $receipt->reverser?->name ?? 'System' }}.
        </div>
        <div class="mt-1 small">
            <strong>Reversal Reason:</strong> {{ $receipt->reversal_reason }}
        </div>
    </div>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-wallet2 text-success me-1"></i> Receipt Particulars</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light text-muted fw-semibold" style="width: 30%;">Receipt Voucher #</th>
                            <td class="fw-bold">{{ $receipt->receipt_number }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Receipt Date</th>
                            <td>{{ fmt_date($receipt->receipt_date) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Amount Received</th>
                            <td class="fs-5 fw-bold text-success">{{ money($receipt->amount) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Payment Method</th>
                            <td><span class="badge bg-light text-dark border">{{ $receipt->method_label }}</span></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Transaction Reference</th>
                            <td>{{ $receipt->transaction_reference ?? 'None / Cash Receipt' }}</td>
                        </tr>
                        @if($receipt->bank_name)
                            <tr>
                                <th class="bg-light text-muted fw-semibold">Remitting Bank</th>
                                <td>{{ $receipt->bank_name }}</td>
                            </tr>
                        @endif
                        @if($receipt->notes)
                            <tr>
                                <th class="bg-light text-muted fw-semibold">Notes</th>
                                <td>{{ $receipt->notes }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Linked Sales Invoice --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0"><i class="bi bi-receipt text-primary me-1"></i> Linked Sales Invoice</h5>
                <a href="{{ route('sales-invoices.show', $receipt->invoice) }}" class="btn btn-sm btn-outline-primary">
                    View Invoice <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Invoice Number</span>
                        <strong>{{ $receipt->invoice->invoice_number }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Invoice Date</span>
                        <span>{{ fmt_date($receipt->invoice->invoice_date) }}</span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Total Invoice Amount</span>
                        <span>{{ money($receipt->invoice->total_amount) }}</span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Remaining Balance</span>
                        <strong class="{{ bccomp($receipt->invoice->balance_due, '0', 2) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ money($receipt->invoice->balance_due) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        {{-- Buyer Profile --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-building text-primary me-1"></i> Customer Profile</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-1">{{ $receipt->customer->name }}</h6>
                <div class="badge bg-light text-dark border mb-3">{{ $receipt->customer->customer_type_label }}</div>

                <div class="small mb-2">
                    <span class="text-muted">GSTIN:</span>
                    <span>{{ $receipt->customer->gstin ?? 'Unregistered' }}</span>
                </div>
                <div class="small mb-2">
                    <span class="text-muted">Phone:</span>
                    <span>{{ $receipt->customer->phone ?? '-' }}</span>
                </div>
                <div class="small mb-3">
                    <span class="text-muted">Address:</span>
                    <span>{{ $receipt->customer->address ?? '-' }}</span>
                </div>

                <a href="{{ route('customers.show', $receipt->customer) }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-person-badge"></i> View Customer Ledger
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-clock-history text-muted me-1"></i> Audit Information</h5>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded By:</span>
                    <span>{{ $receipt->creator?->name ?? 'System' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded At:</span>
                    <span>{{ fmt_date($receipt->created_at, 'd M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reversal Modal --}}
@can('payments.reverse')
@if(!$receipt->isReversed())
<div class="modal fade" id="reverseReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customer-receipts.reverse', $receipt) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title h6 text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i> Reverse Customer Receipt {{ $receipt->receipt_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Reversing this receipt of <strong>{{ money($receipt->amount) }}</strong> will add the amount back to the customer's outstanding invoice balance.
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
