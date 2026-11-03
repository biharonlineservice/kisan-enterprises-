@extends('layouts.app')

@section('title', 'Payment Voucher ' . $payment->voucher_number)

@section('content')
{{-- 1. Executive Voucher Command Header --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <a href="{{ route('farmer-payments.index') }}" class="erp-hero-chip text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ __('procurement.payments.title') }}</span>
                </a>
                <span class="erp-hero-chip">
                    <i class="bi bi-calendar-event"></i>
                    <span>{{ fmt_date($payment->payment_date) }}</span>
                </span>
                @if($payment->isReversed())
                    <span class="badge bg-danger text-white border border-danger-subtle px-2 py-1">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('procurement.purchases.status_reversed') }}
                    </span>
                @else
                    <span class="badge bg-success text-white border border-success-subtle px-2 py-1">
                        <i class="bi bi-check-circle-fill me-1"></i>{{ app()->getLocale() === 'hi' ? 'पूर्ण चुकता' : 'Paid' }}
                    </span>
                @endif
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">
                <i class="bi bi-cash-coin me-2"></i>{{ $payment->voucher_number }}
            </h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                Farmer Disbursement Voucher • {{ $payment->farmer->name }} ({{ $payment->farmer->farmer_id }})
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 flex-shrink-0">
            <a href="{{ route('farmer-payments.print', $payment) }}" target="_blank" class="erp-hero-btn gold">
                <i class="bi bi-printer-fill"></i>
                <span>Print Voucher</span>
            </a>
            @if(!$payment->isReversed())
                @can('payments.reverse')
                    <button type="button" class="btn btn-sm btn-outline-light border-light-subtle d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#reversePaymentModal">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reverse Voucher</span>
                    </button>
                @endcan
            @endif
        </div>
    </div>
</div>

@if($payment->isReversed())
    <div class="alert alert-danger shadow-sm mb-4 border-0" style="border-radius: 0.75rem;">
        <div class="d-flex align-items-center gap-2 fw-semibold">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            This payment voucher was reversed on {{ fmt_date($payment->reversed_at, 'd M Y, h:i A') }} by {{ $payment->reverser?->name ?? 'System' }}.
        </div>
        <div class="mt-1 small ps-4">
            <strong>Reversal Reason:</strong> {{ $payment->reversal_reason }}
        </div>
    </div>
@endif

<div class="row g-4">
    {{-- Main Column: Payment Particulars & Linked Purchase --}}
    <div class="col-12 col-lg-8">
        {{-- Payment Particulars Card --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title h6 mb-0 fw-bold">
                    <i class="bi bi-receipt text-success me-1"></i> Payment Particulars & Payout Breakdown
                </h5>
                <span class="badge bg-light text-dark border">
                    {{ $payment->method_label }}
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light text-muted fw-semibold" style="width: 35%;">Voucher Number</th>
                            <td class="fw-bold">{{ $payment->voucher_number }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Payment Date</th>
                            <td>
                                <i class="bi bi-calendar3 text-muted me-1"></i>{{ fmt_date($payment->payment_date) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">
                                {{ bccomp((string) $payment->bonus_amount, '0.00', 2) > 0 ? 'Principal Amount (Against Balance)' : 'Amount Paid' }}
                            </th>
                            <td class="fs-5 fw-bold text-dark">{{ money($payment->amount) }}</td>
                        </tr>
                        @if(bccomp((string) $payment->bonus_amount, '0.00', 2) > 0)
                            <tr class="table-success">
                                <th class="text-success fw-semibold">
                                    <i class="bi bi-gift-fill me-1"></i> Delayed Payment Bonus
                                </th>
                                <td>
                                    <span class="fw-bold text-success fs-6">+ {{ money($payment->bonus_amount) }}</span>
                                    <span class="badge bg-white text-success border border-success-subtle ms-2">
                                        {{ $payment->days_delayed }} days delayed
                                        @if(bccomp((string) $payment->bonus_percentage, '0.00', 2) > 0)
                                            @ {{ bc_round($payment->bonus_percentage, 2) }}% ({{ ucfirst($payment->bonus_calculation_mode ?? 'monthly') }})
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-bold">{{ __('procurement.payments.total_payout') }}</th>
                                <td class="fs-4 fw-bold text-success">{{ money($payment->effective_payout) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Payment Method</th>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    @if($payment->payment_method === 'cash')
                                        <i class="bi bi-cash text-success me-1"></i>
                                    @elseif($payment->payment_method === 'bank_transfer')
                                        <i class="bi bi-bank text-primary me-1"></i>
                                    @elseif($payment->payment_method === 'upi')
                                        <i class="bi bi-qr-code text-info me-1"></i>
                                    @else
                                        <i class="bi bi-credit-card text-secondary me-1"></i>
                                    @endif
                                    {{ $payment->method_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Transaction Reference</th>
                            <td><code>{{ $payment->transaction_reference ?: 'None / Cash Handover' }}</code></td>
                        </tr>
                        @if($payment->notes)
                            <tr>
                                <th class="bg-light text-muted fw-semibold">Notes / Memo</th>
                                <td>{{ $payment->notes }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Linked Purchase Card --}}
        @if($payment->purchase)
            <div class="card shadow-sm border-0" style="border-radius: 0.75rem;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 mb-0 fw-bold">
                        <i class="bi bi-basket text-primary me-1"></i> Linked Purchase Record
                    </h5>
                    <a href="{{ route('purchases.show', $payment->purchase) }}" class="btn btn-sm btn-outline-primary">
                        <span>View Purchase Receipt</span> <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <span class="text-muted small d-block">Purchase Number</span>
                            <strong>{{ $payment->purchase->purchase_number }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted small d-block">Purchase Date</span>
                            <span>{{ fmt_date($payment->purchase->purchase_date) }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted small d-block">Crop</span>
                            <span class="badge bg-light text-dark border">{{ $payment->purchase->crop?->name ?? 'Crop' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted small d-block">Net Quantity</span>
                            <span>{{ bc_round($payment->purchase->net_weight_qtl, 3) }} Qtl</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted small d-block">Net Payable</span>
                            <span class="fw-semibold text-dark">{{ money($payment->purchase->net_payable_amount) }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted small d-block">Total Paid to Date</span>
                            <span class="text-success fw-semibold">{{ money($payment->purchase->paid_amount) }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted small d-block">Remaining Balance</span>
                            <strong class="{{ bccomp((string) $payment->purchase->balance_due, '0.00', 2) > 0 ? 'text-danger' : 'text-success' }}">
                                {{ money($payment->purchase->balance_due) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Side Column: Farmer Profile & Audit --}}
    <div class="col-12 col-lg-4">
        {{-- Farmer Profile Card --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 0.75rem;">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title h6 mb-0 fw-bold">
                    <i class="bi bi-person text-success me-1"></i> Farmer Profile
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-success-subtle text-success rounded-circle p-2 fs-5">
                        <i class="bi bi-person-fill"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold">{{ $payment->farmer->name }}</h6>
                        <code class="text-muted small">{{ $payment->farmer->farmer_id }}</code>
                    </div>
                </div>

                <div class="small mb-2 d-flex justify-content-between">
                    <span class="text-muted">Mobile:</span>
                    <span>
                        @if($payment->farmer->phone)
                            <a href="tel:{{ $payment->farmer->phone }}" class="text-success text-decoration-none fw-semibold">
                                <i class="bi bi-telephone-fill me-1"></i>{{ $payment->farmer->phone }}
                            </a>
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="small mb-2 d-flex justify-content-between">
                    <span class="text-muted">Village:</span>
                    <span>{{ $payment->farmer->village ?? '-' }}</span>
                </div>
                <div class="small mb-3 d-flex justify-content-between">
                    <span class="text-muted">Tehsil / District:</span>
                    <span>{{ $payment->farmer->tehsil ?? '-' }}, {{ $payment->farmer->district ?? '-' }}</span>
                </div>

                <a href="{{ route('farmers.show', $payment->farmer) }}" class="btn btn-sm btn-outline-success w-100">
                    <i class="bi bi-person-badge me-1"></i> View Farmer Ledger & Profile
                </a>
            </div>
        </div>

        {{-- Audit Trail Card --}}
        <div class="card shadow-sm border-0" style="border-radius: 0.75rem;">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title h6 mb-0 fw-bold">
                    <i class="bi bi-shield-check text-muted me-1"></i> Audit Information
                </h5>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded By:</span>
                    <span class="fw-semibold">{{ $payment->creator?->name ?? 'System' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded At:</span>
                    <span>{{ fmt_date($payment->created_at, 'd M Y, h:i A') }}</span>
                </div>
                @if($payment->isReversed())
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Reversed By:</span>
                        <span class="fw-semibold">{{ $payment->reverser?->name ?? 'System' }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-danger">
                        <span>Reversed At:</span>
                        <span>{{ fmt_date($payment->reversed_at, 'd M Y, h:i A') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Reversal Modal --}}
@can('payments.reverse')
@if(!$payment->isReversed())
<div class="modal fade" id="reversePaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('farmer-payments.reverse', $payment) }}">
            @csrf
            <x-form-token />
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title h6 text-white mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Reverse Payment Voucher {{ $payment->voucher_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Reversing this payment of <strong>{{ money($payment->amount) }}</strong> will add the principal amount back to the purchase's pending balance.
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
