@extends('layouts.app')

@section('title', 'Sales Invoice ' . $invoice->invoice_number)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('sales-invoices.index') }}">Sales Invoices</a></li>
                <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <h2 class="h4 mb-0">{{ $invoice->invoice_number }}</h2>
            @if($invoice->status === 'finalized')
                <span class="badge bg-success-subtle text-success border border-success-subtle">Finalized &amp; Dispatched</span>
                @if($invoice->isDelivered())
                    <span class="badge bg-success text-white border border-success px-2.5 py-1">
                        <i class="bi bi-patch-check-fill me-1"></i> {{ __('sales.invoices.status_delivered') }}
                    </span>
                @else
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1">
                        <i class="bi bi-truck me-1"></i> {{ __('sales.invoices.status_in_transit') }}
                    </span>
                @endif
            @elseif($invoice->status === 'draft')
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Draft</span>
            @elseif($invoice->status === 'reversed')
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Cancelled / Reversed</span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('sales-invoices.challan', $invoice) }}" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-truck"></i> Transport Challan
        </a>
        <a href="{{ route('sales-invoices.challan.pdf', $invoice) }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf"></i> Challan PDF
        </a>
        <a href="{{ route('sales-invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer"></i> Print Invoice
        </a>
        <a href="{{ route('sales-invoices.pdf', $invoice) }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf"></i> Invoice PDF
        </a>

        @if($invoice->isDraft())
            @can('sales.finalize')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#finalizeInvoiceModal">
                    <i class="bi bi-truck"></i> Finalize & Dispatch Stock
                </button>
            @endcan
        @elseif($invoice->isFinalized())
            @can('sales.finalize')
                @if(!$invoice->isDelivered())
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markDeliveredModal">
                        <i class="bi bi-check2-circle me-1"></i> {{ __('sales.invoices.mark_delivered') }}
                    </button>
                @else
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#markDeliveredModal">
                        <i class="bi bi-pencil-square me-1"></i> Edit Delivery
                    </button>
                @endif
            @endcan

            @can('customer_receipts.create')
                @if(bccomp($invoice->balance_due, '0', 2) > 0)
                    <a href="{{ route('customer-receipts.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success">
                        <i class="bi bi-cash-stack"></i> Record Receipt
                    </a>
                @endif
            @endcan

            @can('sales.reverse')
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reverseInvoiceModal">
                    <i class="bi bi-arrow-counterclockwise"></i> Reverse Invoice
                </button>
            @endcan
        @endif
    </div>
</div>

@if($invoice->isReversed())
    <div class="alert alert-danger shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2 fw-semibold">
            <i class="bi bi-exclamation-triangle-fill"></i>
            This invoice was cancelled and stock restored to inventory on {{ fmt_date($invoice->reversed_at, 'd M Y, h:i A') }} by {{ $invoice->reverser?->name ?? 'System' }}.
        </div>
        <div class="mt-1 small">
            <strong>Cancellation Reason:</strong> {{ $invoice->reversal_reason }}
        </div>
    </div>
@endif

{{-- Metrics Row --}}
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold">Dispatched Quantity</span>
                <div class="fs-4 fw-bold text-dark mt-1">{{ bc_round($invoice->totalQuantityQtl(), 3) }} <span class="fs-6 text-muted">Qtl</span></div>
                <small class="text-muted">{{ $invoice->items->count() }} line items</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold">Subtotal</span>
                <div class="fs-4 fw-bold text-dark mt-1">{{ money($invoice->subtotal_amount) }}</div>
                <small class="text-muted">Discount: {{ money($invoice->discount_amount) }}</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold">Total Invoice Amount</span>
                <div class="fs-4 fw-bold text-success mt-1">{{ money($invoice->total_amount) }}</div>
                <small class="text-muted">Paid: {{ money($invoice->paid_amount) }}</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100 border-start border-4 {{ bccomp($invoice->balance_due, '0', 2) > 0 ? 'border-danger' : 'border-secondary' }}">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold">Balance Due</span>
                <div class="fs-4 fw-bold {{ bccomp($invoice->balance_due, '0', 2) > 0 ? 'text-danger' : 'text-muted' }} mt-1">
                    {{ money($invoice->balance_due) }}
                </div>
                <small class="text-muted">{{ bccomp($invoice->balance_due, '0', 2) > 0 ? 'Payment Pending' : 'Fully Settled' }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        {{-- Items Table --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-box-seam text-primary me-1"></i> Dispatched Grain Particulars</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Crop & Variety</th>
                            <th>Dispatch Godown</th>
                            <th class="text-end">Quantity (Qtl)</th>
                            <th class="text-end">Rate / Qtl</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->crop->name }}</strong>
                                    @if($item->cropVariety)
                                        <div class="text-muted small">{{ $item->cropVariety->name }}</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->warehouse->name }}</span></td>
                                <td class="text-end font-monospace">{{ bc_round($item->quantity_qtl, 3) }} Qtl</td>
                                <td class="text-end font-monospace">{{ money($item->rate_per_qtl) }}</td>
                                <td class="text-end font-monospace fw-bold text-dark">{{ money($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Subtotal:</th>
                            <th class="text-end font-monospace">{{ bc_round($invoice->totalQuantityQtl(), 3) }} Qtl</th>
                            <th></th>
                            <th class="text-end font-monospace fw-bold">{{ money($invoice->subtotal_amount) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Dispatch & Transport Card --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-truck text-primary me-1"></i> Gate Pass & Transport Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-6 col-md-4">
                        <span class="text-muted d-block">Vehicle Number:</span>
                        <strong>{{ $invoice->vehicle_number ?? 'Direct Handover' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted d-block">Driver Name:</span>
                        <strong>{{ $invoice->driver_name ?? '-' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted d-block">Transporter:</span>
                        <strong>{{ $invoice->transporter_name ?? '-' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted d-block">E-Way Bill Number:</span>
                        <strong>{{ $invoice->eway_bill_number ?? '-' }}</strong>
                    </div>
                    <div class="col-12 col-md-8">
                        <span class="text-muted d-block">Delivery Destination:</span>
                        <strong>{{ $invoice->delivery_destination ?? $invoice->customer->address ?? '-' }}</strong>
                    </div>
                </div>

                <hr class="my-3">

                <div class="row g-3 small align-items-center">
                    <div class="col-12 col-md-4">
                        <span class="text-muted d-block">Delivery Status:</span>
                        @if($invoice->isDelivered())
                            <span class="badge bg-success px-2.5 py-1.5" style="font-size: 0.8rem;">
                                <i class="bi bi-patch-check-fill me-1"></i> {{ __('sales.invoices.status_delivered') }}
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1.5" style="font-size: 0.8rem;">
                                <i class="bi bi-truck me-1"></i> {{ __('sales.invoices.status_in_transit') }}
                            </span>
                        @endif
                    </div>
                    @if($invoice->isDelivered())
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('sales.invoices.delivered_at') }}:</span>
                            <strong>{{ fmt_date($invoice->delivered_at, 'd M Y, h:i A') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('sales.invoices.received_by') }}:</span>
                            <strong>{{ $invoice->received_by ?? 'Acknowledged at Mill' }}</strong>
                        </div>
                        @if($invoice->deliverer)
                            <div class="col-6 col-md-4">
                                <span class="text-muted d-block">{{ __('sales.invoices.delivered_by') }}:</span>
                                <strong>{{ $invoice->deliverer->name }}</strong>
                            </div>
                        @endif
                        @if($invoice->delivery_notes)
                            <div class="col-12 col-md-8">
                                <span class="text-muted d-block">{{ __('sales.invoices.delivery_notes') }}:</span>
                                <em>{{ $invoice->delivery_notes }}</em>
                            </div>
                        @endif
                    @else
                        <div class="col-12 col-md-8 d-flex flex-wrap align-items-center gap-2">
                            <span class="text-muted">Consignment dispatched from godown and in transit to buyer destination.</span>
                            @can('sales.finalize')
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#markDeliveredModal">
                                    <i class="bi bi-check2-circle me-1"></i> {{ __('sales.invoices.mark_delivered') }}
                                </button>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Customer Receipts Ledger --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0"><i class="bi bi-cash-coin text-success me-1"></i> Payment Receipts</h5>
                @if($invoice->isFinalized() && bccomp($invoice->balance_due, '0', 2) > 0)
                    @can('customer_receipts.create')
                        <a href="{{ route('customer-receipts.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-plus-circle"></i> Add Receipt
                        </a>
                    @endcan
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->receipts as $r)
                            <tr>
                                <td>
                                    <a href="{{ route('customer-receipts.show', $r) }}" class="fw-semibold text-decoration-none">
                                        {{ $r->receipt_number }}
                                    </a>
                                </td>
                                <td>{{ fmt_date($r->receipt_date) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $r->method_label }}</span></td>
                                <td>{{ $r->transaction_reference ?? '-' }}</td>
                                <td class="text-end font-monospace fw-bold text-success">{{ money($r->amount) }}</td>
                                <td class="text-center">
                                    @if($r->isReversed())
                                        <span class="badge bg-danger-subtle text-danger">Reversed</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Completed</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('customer-receipts.show', $r) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No payment receipts recorded for this sales invoice yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Column: Financial Summary & Buyer Profile --}}
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-calculator text-primary me-1"></i> Invoice Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-semibold">{{ money($invoice->subtotal_amount) }}</span>
                </div>

                @if(bccomp($invoice->discount_amount, '0', 2) > 0)
                    <div class="d-flex justify-content-between text-danger mb-2">
                        <span>Discount:</span>
                        <span>- {{ money($invoice->discount_amount) }}</span>
                    </div>
                @endif

                @if(bccomp($invoice->tax_amount, '0', 2) > 0)
                    <div class="d-flex justify-content-between text-dark mb-2">
                        <span>Tax / GST:</span>
                        <span>+ {{ money($invoice->tax_amount) }}</span>
                    </div>
                @endif

                @if(bccomp($invoice->round_off, '0', 2) != 0)
                    <div class="d-flex justify-content-between text-muted mb-2 small">
                        <span>Round Off:</span>
                        <span>{{ $invoice->round_off }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between py-2 border-top border-bottom mb-3">
                    <span class="fw-bold fs-6">Grand Total:</span>
                    <span class="fw-bold fs-6 text-success">{{ money($invoice->total_amount) }}</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Paid:</span>
                    <span class="fw-semibold text-dark">{{ money($invoice->paid_amount) }}</span>
                </div>

                <div class="p-3 {{ bccomp($invoice->balance_due, '0', 2) > 0 ? 'bg-danger-subtle border-danger-subtle text-danger' : 'bg-success-subtle border-success-subtle text-success' }} border rounded text-center">
                    <span class="small fw-semibold d-block text-uppercase">Balance Due</span>
                    <span class="fs-4 fw-bold">{{ money($invoice->balance_due) }}</span>
                </div>
            </div>
        </div>

        {{-- Buyer Details --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-building text-primary me-1"></i> Customer Profile</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-1">{{ $invoice->customer->name }}</h6>
                <div class="badge bg-light text-dark border mb-3">{{ $invoice->customer->customer_type_label }}</div>

                <div class="small mb-2">
                    <span class="text-muted">GSTIN:</span>
                    <span>{{ $invoice->customer->gstin ?? 'Unregistered' }}</span>
                </div>
                <div class="small mb-2">
                    <span class="text-muted">Phone:</span>
                    <span>{{ $invoice->customer->phone ?? '-' }}</span>
                </div>
                <div class="small mb-3">
                    <span class="text-muted">Address:</span>
                    <span>{{ $invoice->customer->address ?? '-' }}</span>
                </div>

                <a href="{{ route('customers.show', $invoice->customer) }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-person-badge"></i> View Customer Ledger
                </a>
            </div>
        </div>

        {{-- Audit Information --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-clock-history text-muted me-1"></i> Audit Information</h5>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Created By:</span>
                    <span>{{ $invoice->creator?->name ?? 'System' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Created At:</span>
                    <span>{{ fmt_date($invoice->created_at, 'd M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Finalize Modal --}}
@can('sales.finalize')
@if($invoice->isDraft())
<div class="modal fade" id="finalizeInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('sales-invoices.finalize', $invoice) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title h6 text-white"><i class="bi bi-truck me-1"></i> Finalize Sales Invoice {{ $invoice->invoice_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Finalizing will lock the invoice particulars and <strong>dispatch {{ bc_round($invoice->totalQuantityQtl(), 3) }} Qtl</strong> of grain out of warehouse inventory.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Initial Payment Received (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="0" max="{{ $invoice->total_amount }}" name="payment_amount" class="form-control" value="0.00">
                        </div>
                        <small class="text-muted">Leave 0.00 if payment will be received later against credit terms.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_method" class="form-select">
                            <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction Reference (Optional)</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="UTR ID, Cheque #">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm & Dispatch</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- Reverse Modal --}}
@can('sales.reverse')
@if($invoice->isFinalized())
<div class="modal fade" id="reverseInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('sales-invoices.reverse', $invoice) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title h6 text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i> Reverse Invoice {{ $invoice->invoice_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Reversing this invoice will immediately restore <strong>{{ bc_round($invoice->totalQuantityQtl(), 3) }} Qtl</strong> back to godown inventory.
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Reason for Reversal / Cancellation</label>
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

{{-- Mark Delivered Modal --}}
@can('sales.finalize')
@if($invoice->isFinalized())
<div class="modal fade" id="markDeliveredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('sales-invoices.mark-delivered', $invoice) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title h6 text-white mb-0">
                        <i class="bi bi-patch-check-fill me-1"></i> {{ __('sales.invoices.modal_delivered_title') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        {{ __('sales.invoices.modal_delivered_desc') }}
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('sales.invoices.delivered_at') }}</label>
                        <input type="datetime-local" name="delivered_at" class="form-control" value="{{ $invoice->delivered_at ? $invoice->delivered_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" required>
                        <div class="form-text text-muted">Arrival &amp; unloading timestamp at the buyer's mill or godown.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('sales.invoices.received_by') }}</label>
                        <input type="text" name="received_by" class="form-control" value="{{ old('received_by', $invoice->received_by) }}" placeholder="{{ __('sales.invoices.received_by_placeholder') }}">
                        <div class="form-text text-muted">Name/designation of the mill manager or supervisor acknowledging receipt.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('sales.invoices.delivery_notes') }}</label>
                        <textarea name="delivery_notes" rows="3" class="form-control" placeholder="{{ __('sales.invoices.delivery_notes_placeholder') }}">{{ old('delivery_notes', $invoice->delivery_notes) }}</textarea>
                    </div>

                    <div class="bg-light p-3 rounded-2 border small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Invoice No:</span>
                            <strong class="font-monospace">{{ $invoice->invoice_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Destination Mill:</span>
                            <strong>{{ $invoice->delivery_destination ?? $invoice->customer?->name ?? 'Mill Destination' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Vehicle No:</span>
                            <strong>{{ $invoice->vehicle_number ?? 'Direct Dispatch' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Dispatched Qtl:</span>
                            <strong>{{ bc_round($invoice->totalQuantityQtl(), 3) }} Qtl</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($invoice->isDelivered())
                        <button type="button" class="btn btn-outline-danger me-auto" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#revertDeliveryModal">
                            <i class="bi bi-arrow-counterclockwise"></i> {{ __('sales.invoices.revert_delivery') }}
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> {{ __('sales.invoices.mark_delivered') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($invoice->isDelivered())
<div class="modal fade" id="revertDeliveryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form method="POST" action="{{ route('sales-invoices.revert-delivery', $invoice) }}">
            @csrf
            <x-form-token />
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h6 mb-0">{{ __('sales.invoices.modal_revert_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-0">
                        {{ __('sales.invoices.modal_revert_desc') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">{{ __('sales.invoices.revert_delivery') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endif
@endcan

@endsection
