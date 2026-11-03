@extends('layouts.app')

@section('title', __('sales.invoices.add_new'))

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('sales-invoices.index') }}">{{ __('sales.invoices.title') }}</a></li>
            <li class="breadcrumb-item active">{{ __('sales.invoices.add_new') }}</li>
        </ol>
    </nav>
    <h2 class="h4 mb-0">{{ __('sales.invoices.add_new') }}</h2>
</div>

<form method="POST" action="{{ route('sales-invoices.store') }}" id="invoiceForm">
    @csrf
    <x-form-token />

    @if($selectedOrder)
        <input type="hidden" name="sales_order_id" value="{{ $selectedOrder->id }}">
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            {{-- Buyer & Basic Info --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 mb-0"><i class="bi bi-person text-primary me-1"></i> {{ __('sales.invoices.details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label required">{{ __('sales.invoices.invoice_date') }}</label>
                            <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                                value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                            @error('invoice_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">{{ __('sales.orders.delivery_date') }}</label>
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}">
                            @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required">{{ __('sales.invoices.customer') }}</label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">{{ __('app.select_option') }}</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                            data-address="{{ $c->full_address }}"
                                            @selected(old('customer_id', $selectedOrder?->customer_id) == $c->id)>
                                        {{ $c->name }} ({{ $c->customer_type_label }}) — {{ $c->city ?? $c->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($selectedOrder)
                            <div class="col-12">
                                <div class="alert alert-info py-2 px-3 small mb-0">
                                    <i class="bi bi-link-45deg me-1"></i> Pre-filled from Sales Order: <strong>{{ $selectedOrder->order_number }}</strong>
                                </div>
                            </div>
                        @elseif($selectedPurchase)
                            <div class="col-12">
                                <div class="alert alert-success py-2 px-3 small mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <i class="bi bi-truck text-success me-1"></i>
                                        <strong>{{ __('sales.invoices.direct_sale_from_purchase') }}:</strong>
                                        <a href="{{ route('purchases.show', $selectedPurchase) }}" class="fw-bold text-success text-decoration-underline" target="_blank">{{ $selectedPurchase->purchase_number }}</a>
                                        <span class="ms-1">({{ $selectedPurchase->farmer->name }} &bull; {{ bc_round($selectedPurchase->net_weight_qtl, 3) }} Qtl)</span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('sales.invoices.direct_sale_info') }}</span>
                                </div>
                                <input type="hidden" name="remarks" value="{{ old('remarks', 'Direct sale / dispatch against Purchase ' . $selectedPurchase->purchase_number) }}">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Dispatch & Transport Particulars --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 mb-0"><i class="bi bi-truck text-primary me-1"></i> {{ __('sales.invoices.vehicle_number') }} / {{ __('procurement.purchases.transport_section') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('sales.invoices.vehicle_number') }}</label>
                            <input type="text" name="vehicle_number" class="form-control text-uppercase @error('vehicle_number') is-invalid @enderror"
                                value="{{ old('vehicle_number', $selectedPurchase?->vehicle_number) }}" placeholder="e.g. UP 32 AB 9876">
                            @error('vehicle_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('sales.invoices.driver_name') }}</label>
                            <input type="text" name="driver_name" class="form-control @error('driver_name') is-invalid @enderror"
                                value="{{ old('driver_name', $selectedPurchase?->driver_name) }}" placeholder="Driver name">
                            @error('driver_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('sales.invoices.driver_mobile') }}</label>
                            <input type="text" name="transporter_name" class="form-control @error('transporter_name') is-invalid @enderror"
                                value="{{ old('transporter_name') }}" placeholder="Transport agency / fleet">
                            @error('transporter_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('sales.invoices.e_way_bill') }}</label>
                            <input type="text" name="eway_bill_number" class="form-control @error('eway_bill_number') is-invalid @enderror"
                                value="{{ old('eway_bill_number') }}" placeholder="e.g. 123456789012">
                            @error('eway_bill_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">{{ __('sales.customers.address') }}</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btn_fill_customer_address" style="font-size: 0.76rem;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ app()->getLocale() === 'hi' ? 'खरीदार का पता भरें' : 'Fill Buyer Address' }}
                                </button>
                            </div>
                            <input type="text" name="delivery_destination" id="delivery_destination" class="form-control @error('delivery_destination') is-invalid @enderror"
                                value="{{ old('delivery_destination', $selectedOrder?->delivery_address) }}" placeholder="Mill gate, godown address or destination market">
                            @error('delivery_destination') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Line Items Table --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 mb-0"><i class="bi bi-box-seam text-primary me-1"></i> {{ __('sales.orders.items_section') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_item">
                        <i class="bi bi-plus-circle"></i> {{ __('app.add') }}
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">{{ __('sales.invoices.crop') }}</th>
                                <th style="width: 20%;">{{ __('sales.invoices.variety') }}</th>
                                <th style="width: 20%;">{{ __('sales.invoices.warehouse') }}</th>
                                <th style="width: 15%;" class="text-end">{{ __('sales.invoices.quantity_qtl') }}</th>
                                <th style="width: 15%;" class="text-end">{{ __('sales.invoices.rate_per_qtl') }}</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @if($selectedOrder && $selectedOrder->items->count() > 0)
                                @foreach($selectedOrder->items as $idx => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $idx }}][crop_id]" class="form-select form-select-sm crop-select" required>
                                                <option value="">Select Crop</option>
                                                @foreach($crops as $c)
                                                    <option value="{{ $c->id }}" @selected($item->crop_id == $c->id)>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[{{ $idx }}][crop_variety_id]" class="form-select form-select-sm variety-select">
                                                <option value="">Standard</option>
                                                @foreach($crops as $c)
                                                    @foreach($c->varieties as $v)
                                                        <option value="{{ $v->id }}" data-crop="{{ $c->id }}" @selected($item->crop_variety_id == $v->id)>{{ $v->name }}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[{{ $idx }}][warehouse_id]" class="form-select form-select-sm" required>
                                                <option value="">Select Godown</option>
                                                @foreach($warehouses as $w)
                                                    <option value="{{ $w->id }}" @selected($item->warehouse_id == $w->id)>{{ $w->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.001" min="0.001" inputmode="decimal" name="items[{{ $idx }}][quantity_qtl]"
                                                value="{{ $item->quantity_qtl }}" class="form-control form-control-sm text-end qty-input" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="1" inputmode="decimal" name="items[{{ $idx }}][rate_per_qtl]"
                                                value="{{ $item->rate_per_qtl }}" class="form-control form-control-sm text-end rate-input" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="{{ $idx === 0 ? 'display: none;' : '' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($selectedPurchase)
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][crop_id]" class="form-select form-select-sm crop-select" required>
                                            <option value="">Select Crop</option>
                                            @foreach($crops as $c)
                                                <option value="{{ $c->id }}" @selected($selectedPurchase->crop_id == $c->id)>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[0][crop_variety_id]" class="form-select form-select-sm variety-select">
                                            <option value="">Standard</option>
                                            @foreach($crops as $c)
                                                @foreach($c->varieties as $v)
                                                    <option value="{{ $v->id }}" data-crop="{{ $c->id }}" @selected($selectedPurchase->crop_variety_id == $v->id)>{{ $v->name }}</option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[0][warehouse_id]" class="form-select form-select-sm" required>
                                            <option value="">Select Godown</option>
                                            @foreach($warehouses as $w)
                                                <option value="{{ $w->id }}" @selected($selectedPurchase->warehouse_id == $w->id)>{{ $w->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" min="0.001" inputmode="decimal" name="items[0][quantity_qtl]"
                                            value="{{ bc_round($selectedPurchase->net_weight_qtl, 3) }}" class="form-control form-control-sm text-end qty-input" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="1" inputmode="decimal" name="items[0][rate_per_qtl]" class="form-control form-control-sm text-end rate-input" placeholder="Enter sale rate" autofocus required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="display: none;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @else
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][crop_id]" class="form-select form-select-sm crop-select" required>
                                            <option value="">Select Crop</option>
                                            @foreach($crops as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[0][crop_variety_id]" class="form-select form-select-sm variety-select">
                                            <option value="">Standard</option>
                                            @foreach($crops as $c)
                                                @foreach($c->varieties as $v)
                                                    <option value="{{ $v->id }}" data-crop="{{ $c->id }}">{{ $v->name }}</option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[0][warehouse_id]" class="form-select form-select-sm" required>
                                            <option value="">Select Godown</option>
                                            @foreach($warehouses as $w)
                                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" min="0.001" inputmode="decimal" name="items[0][quantity_qtl]" class="form-control form-control-sm text-end qty-input" placeholder="0.000" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="1" inputmode="decimal" name="items[0][rate_per_qtl]" class="form-control form-control-sm text-end rate-input" placeholder="0.00" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="display: none;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: Valuation & Summary --}}
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 1.5rem;">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title h6 mb-0 text-white"><i class="bi bi-calculator me-1"></i> {{ __('sales.invoices.details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('sales.invoices.quantity_qtl') }}:</span>
                        <span class="fw-bold" id="display_total_qtl">0.000 Qtl</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('sales.invoices.taxable_amount') }}:</span>
                        <span class="fw-semibold" id="display_subtotal">₹0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">{{ __('procurement.purchases.deductions') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                            <input type="number" step="0.01" min="0" inputmode="decimal" name="discount_amount" id="discount_amount"
                                class="form-control text-end" value="{{ old('discount_amount', '0.00') }}">
                        </div>
                    </div>

                    @if(setting('tax.enabled', '0') == '1')
                        <div class="mb-3">
                            <label class="form-label small text-muted">{{ __('sales.invoices.tax_rate') }}</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" min="0" max="100" inputmode="decimal" name="tax_rate" id="tax_rate"
                                    class="form-control text-end" value="{{ old('tax_rate', setting('tax.default_rate', '0')) }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="tax_rate" id="tax_rate" value="0.00">
                    @endif

                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6">{{ __('sales.invoices.total_amount') }}:</span>
                            <span class="fs-4 fw-bold text-success" id="display_total">₹0.00</span>
                        </div>
                    </div>

                    {{-- Initial receipt collection option --}}
                    <div class="border-top pt-3 mb-4">
                        <h6 class="text-muted small fw-semibold text-uppercase mb-2">{{ __('sales.receipts.title') }}</h6>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('sales.invoices.paid_amount') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                                <input type="number" step="0.01" min="0" inputmode="decimal" name="initial_paid_amount" id="initial_paid_amount"
                                    class="form-control text-end" value="{{ old('initial_paid_amount', '0.00') }}">
                            </div>
                        </div>
                        <div class="row g-2 align-items-end">
                            <div class="col-6">
                                <label class="form-label small text-truncate d-block mb-1" title="{{ __('sales.receipts.payment_mode') }}">{{ __('sales.receipts.payment_mode') }}</label>
                                <select name="payment_method" class="form-select form-select-sm">
                                    <option value="bank_transfer">{{ __('procurement.payments.mode_bank') }}</option>
                                    <option value="cash">{{ __('procurement.payments.mode_cash') }}</option>
                                    <option value="upi">{{ __('procurement.payments.mode_upi') }}</option>
                                    <option value="cheque">{{ __('procurement.payments.mode_cheque') }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-truncate d-block mb-1" title="{{ __('sales.receipts.reference') }}">{{ __('sales.receipts.reference') }}</label>
                                <input type="text" name="payment_reference" class="form-control form-control-sm" placeholder="UTR / Chq #">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        @can('sales.finalize')
                            <button type="submit" name="action" value="finalize" class="btn btn-primary btn-lg">
                                <i class="bi bi-truck"></i> {{ __('sales.invoices.finalize_dispatch') }}
                            </button>
                        @endcan
                        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                            <i class="bi bi-save"></i> {{ __('sales.invoices.save_draft') }}
                        </button>
                        <a href="{{ route('sales-invoices.index') }}" class="btn btn-link text-muted btn-sm">{{ __('app.cancel') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Sticky Action Bar (< 768px) --}}
    <div class="mobile-sticky-actions">
        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
            <i class="bi bi-save"></i> {{ __('sales.invoices.save_draft') }}
        </button>
        @can('sales.finalize')
            <button type="submit" name="action" value="finalize" class="btn btn-primary">
                <i class="bi bi-truck"></i> {{ __('sales.invoices.finalize_dispatch') }}
            </button>
        @endcan
    </div>
    <div class="pb-5 mb-4 d-md-none"></div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = {{ $selectedOrder ? max(1, $selectedOrder->items->count()) : 1 }};
    const body = document.getElementById('itemsBody');
    const addBtn = document.getElementById('btn_add_item');
    const discountInput = document.getElementById('discount_amount');
    const taxInput = document.getElementById('tax_rate');

    const displayQtl = document.getElementById('display_total_qtl');
    const displaySubtotal = document.getElementById('display_subtotal');
    const displayTotal = document.getElementById('display_total');

    function calculateInvoice() {
        let totalQtl = 0;
        let subtotal = 0;

        body.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            totalQtl += qty;
            subtotal += (qty * rate);
        });

        const discount = parseFloat(discountInput.value) || 0;
        const taxRate = parseFloat(taxInput.value) || 0;
        const taxAmt = (subtotal * taxRate) / 100.0;

        let grandTotal = subtotal + taxAmt - discount;
        if (grandTotal < 0) grandTotal = 0;

        displayQtl.textContent = totalQtl.toLocaleString('en-IN', { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' Qtl';
        displaySubtotal.textContent = '₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        displayTotal.textContent = '₹' + Math.round(grandTotal).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function hookRow(row) {
        row.querySelector('.qty-input').addEventListener('input', calculateInvoice);
        row.querySelector('.rate-input').addEventListener('input', calculateInvoice);

        const cropSel = row.querySelector('.crop-select');
        const varSel = row.querySelector('.variety-select');
        cropSel.addEventListener('change', function() {
            const cId = cropSel.value;
            varSel.querySelectorAll('option').forEach(opt => {
                if (!opt.value) return;
                opt.hidden = !(!cId || opt.dataset.crop === cId);
            });
        });

        const removeBtn = row.querySelector('.btn-remove-row');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (body.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    calculateInvoice();
                }
            });
        }
    }

    body.querySelectorAll('.item-row').forEach(r => hookRow(r));
    discountInput.addEventListener('input', calculateInvoice);
    if (taxInput) taxInput.addEventListener('input', calculateInvoice);

    addBtn.addEventListener('click', function() {
        const firstRow = body.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach(input => {
            input.value = '';
            input.name = input.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
        });
        newRow.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
            select.name = select.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
        });

        newRow.querySelector('.btn-remove-row').style.display = 'inline-block';
        body.appendChild(newRow);
        hookRow(newRow);
        rowIndex++;
    });

    // Auto-fill delivery destination from selected buyer address
    const customerSelect = document.getElementById('customer_id') || document.querySelector('select[name="customer_id"]');
    const deliveryInput = document.getElementById('delivery_destination') || document.querySelector('input[name="delivery_destination"]');
    const fillAddressBtn = document.getElementById('btn_fill_customer_address');

    function fillBuyerAddress(force = false) {
        if (!customerSelect || !deliveryInput) return;
        const opt = customerSelect.options[customerSelect.selectedIndex];
        const addr = opt ? (opt.dataset.address || '') : '';
        if (addr && (force || !deliveryInput.value.trim())) {
            deliveryInput.value = addr;
            deliveryInput.classList.add('is-valid');
            setTimeout(() => deliveryInput.classList.remove('is-valid'), 1500);
        }
    }

    if (customerSelect) {
        customerSelect.addEventListener('change', function() {
            fillBuyerAddress(true);
        });
    }

    if (fillAddressBtn) {
        fillAddressBtn.addEventListener('click', function() {
            fillBuyerAddress(true);
        });
    }

    if (customerSelect && customerSelect.value && deliveryInput && !deliveryInput.value.trim()) {
        fillBuyerAddress(false);
    }

    calculateInvoice();
});
</script>
@endpush
@endsection
