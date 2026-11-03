@extends('layouts.app')

@section('title', 'Record Customer Receipt')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('customer-receipts.index') }}">Customer Receipts</a></li>
            <li class="breadcrumb-item active">New Receipt</li>
        </ol>
    </nav>
    <h2 class="h4 mb-0">Record Customer Payment Receipt</h2>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-wallet2 text-success me-1"></i> Remittance Particulars</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customer-receipts.store') }}">
                    @csrf
                    <x-form-token />

                    <div class="mb-3">
                        <label class="form-label required">Select Sales Invoice (Pending Balance)</label>
                        <select name="sales_invoice_id" id="sales_invoice_id" class="form-select @error('sales_invoice_id') is-invalid @enderror" required>
                            <option value="">Choose an invoice with outstanding balance...</option>
                            @foreach($pendingInvoices as $inv)
                                <option value="{{ $inv->id }}"
                                    data-customer="{{ $inv->customer->name }}"
                                    data-total="{{ $inv->total_amount }}"
                                    data-balance="{{ $inv->balance_due }}"
                                    @selected(old('sales_invoice_id', $selectedInvoice?->id) == $inv->id)>
                                    {{ $inv->invoice_number }} — {{ $inv->customer->name }} — Balance: {{ money($inv->balance_due) }}
                                </option>
                            @endforeach
                        </select>
                        @error('sales_invoice_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Invoice Snapshot Preview --}}
                    <div id="invoice_preview" class="p-3 bg-light rounded border mb-4 d-none">
                        <div class="row g-2 small">
                            <div class="col-6 col-md-4">
                                <span class="text-muted d-block">Buyer:</span>
                                <strong id="prev_customer">-</strong>
                            </div>
                            <div class="col-6 col-md-4">
                                <span class="text-muted d-block">Invoice Total:</span>
                                <strong id="prev_total" class="text-dark">-</strong>
                            </div>
                            <div class="col-6 col-md-4">
                                <span class="text-muted d-block">Current Balance Due:</span>
                                <strong id="prev_balance" class="text-danger fs-6">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('sales.receipts.amount') }} ({{ setting('locale.currency_symbol', '₹') }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                                <input type="number" step="0.01" min="0.01" inputmode="decimal" name="amount" id="amount"
                                    class="form-control text-end fw-bold @error('amount') is-invalid @enderror"
                                    value="{{ old('amount', $selectedInvoice ? $selectedInvoice->balance_due : '') }}" required placeholder="0.00">
                                <button class="btn btn-outline-secondary" type="button" id="btn_pay_full">Receive Full</button>
                            </div>
                            @error('amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('sales.receipts.payment_mode') }}</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                @foreach($methods as $key => $lbl)
                                    <option value="{{ $key }}" @selected(old('payment_method') === $key)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('sales.receipts.reference') }}</label>
                            <input type="text" name="transaction_reference" class="form-control @error('transaction_reference') is-invalid @enderror"
                                value="{{ old('transaction_reference') }}" placeholder="e.g. UTR12345678, Chq #000456">
                            @error('transaction_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('accounts.type_bank') }}</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                value="{{ old('bank_name') }}" placeholder="e.g. State Bank of India, HDFC Bank">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('app.notes') }}</label>
                            <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                value="{{ old('notes') }}" placeholder="Any payment memo or deposit note">
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-none d-md-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('customer-receipts.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> {{ __('sales.receipts.record_receipt') }}
                        </button>
                    </div>

                    {{-- Mobile Sticky Action Bar (< 768px) --}}
                    <div class="mobile-sticky-actions">
                        <a href="{{ route('customer-receipts.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> {{ __('app.save') }}
                        </button>
                    </div>
                    <div class="pb-5 mb-4 d-md-none"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-start border-4 border-success">
            <div class="card-body">
                <h6 class="card-title text-success"><i class="bi bi-info-circle me-1"></i> Receipt Collection Rules</h6>
                <p class="small text-muted mb-2">
                    - Receipts immediately reduce the customer's outstanding balance on the selected invoice.
                </p>
                <p class="small text-muted mb-2">
                    - For bank transfers and cheques, specify the transaction reference or UTR for ledger reconciliation.
                </p>
                <p class="small text-muted mb-0">
                    - An official acknowledgement receipt slip is generated upon saving.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const invSelect = document.getElementById('sales_invoice_id');
    const previewBox = document.getElementById('invoice_preview');
    const prevCust = document.getElementById('prev_customer');
    const prevTotal = document.getElementById('prev_total');
    const prevBal = document.getElementById('prev_balance');
    const amountInput = document.getElementById('amount');
    const payFullBtn = document.getElementById('btn_pay_full');

    function updatePreview() {
        const opt = invSelect.selectedOptions[0];
        if (opt && opt.value) {
            prevCust.textContent = opt.dataset.customer || '-';
            prevTotal.textContent = '₹' + (parseFloat(opt.dataset.total) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
            prevBal.textContent = '₹' + (parseFloat(opt.dataset.balance) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
            previewBox.classList.remove('d-none');
        } else {
            previewBox.classList.add('d-none');
        }
    }

    if (payFullBtn) {
        payFullBtn.addEventListener('click', function() {
            const opt = invSelect.selectedOptions[0];
            if (opt && opt.dataset.balance) {
                amountInput.value = parseFloat(opt.dataset.balance).toFixed(2);
            }
        });
    }

    if (invSelect) {
        invSelect.addEventListener('change', function() {
            updatePreview();
            const opt = invSelect.selectedOptions[0];
            if (opt && opt.dataset.balance && (!amountInput.value || amountInput.value === '0.00' || amountInput.value === '0')) {
                amountInput.value = parseFloat(opt.dataset.balance).toFixed(2);
            }
        });
        updatePreview();
    }
});
</script>
@endpush
@endsection
