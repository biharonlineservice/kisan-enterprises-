@extends('layouts.app')

@section('title', 'New Sales Order')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('sales-orders.index') }}">Sales Orders</a></li>
            <li class="breadcrumb-item active">New Order</li>
        </ol>
    </nav>
    <h2 class="h4 mb-0">Record Sales Order</h2>
</div>

<form method="POST" action="{{ route('sales-orders.store') }}" id="orderForm">
    @csrf
    <x-form-token />

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 mb-0"><i class="bi bi-person text-primary me-1"></i> Buyer & Order Particulars</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label required">Order Date</label>
                            <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror"
                                value="{{ old('order_date', now()->format('Y-m-d')) }}" required>
                            @error('order_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label required">Customer / Mill / Buyer</label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Select Buyer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                            data-address="{{ $c->full_address }}"
                                            @selected(old('customer_id') == $c->id)>
                                        {{ $c->name }} ({{ $c->type_name }}) — {{ $c->city ? $c->city : ($c->mobile ?? 'Registered') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Delivery Address / Destination</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btn_fill_customer_address" style="font-size: 0.76rem;">
                                    <i class="bi bi-geo-alt me-1"></i>Fill Buyer Address
                                </button>
                            </div>
                            <input type="text" name="delivery_address" id="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror"
                                value="{{ old('delivery_address') }}" placeholder="Mill gate address or delivery depot">
                            @error('delivery_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Line Items Card --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 mb-0"><i class="bi bi-box-seam text-primary me-1"></i> Order Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_item">
                        <i class="bi bi-plus-circle"></i> Add Line
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Crop</th>
                                <th style="width: 20%;">Variety</th>
                                <th style="width: 20%;">Godown</th>
                                <th style="width: 15%;" class="text-end">Qty (Qtl)</th>
                                <th style="width: 15%;" class="text-end">Rate / Qtl (₹)</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
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
                                    <input type="number" step="0.001" min="0.001" name="items[0][quantity_qtl]" class="form-control form-control-sm text-end qty-input" placeholder="0.000" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="1" name="items[0][rate_per_qtl]" class="form-control form-control-sm text-end rate-input" placeholder="0.00" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="display: none;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 1.5rem;">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title h6 mb-0 text-white"><i class="bi bi-calculator me-1"></i> Order Valuation</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Quantity:</span>
                        <span class="fs-5 fw-bold text-dark" id="display_total_qtl">0.000 Qtl</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted">Estimated Amount:</span>
                        <span class="fs-4 fw-bold text-success" id="display_total_amount">₹0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Order Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control form-control-sm" placeholder="Terms, delivery timeline, or payment agreements...">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check2-circle"></i> Confirm Sales Order
                        </button>
                        <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const body = document.getElementById('itemsBody');
    const addBtn = document.getElementById('btn_add_item');
    const displayQtl = document.getElementById('display_total_qtl');
    const displayAmt = document.getElementById('display_total_amount');

    function calculateOrder() {
        let totalQtl = 0;
        let totalAmt = 0;

        body.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            totalQtl += qty;
            totalAmt += (qty * rate);
        });

        displayQtl.textContent = totalQtl.toLocaleString('en-IN', { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' Qtl';
        displayAmt.textContent = '₹' + totalAmt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function hookRow(row) {
        row.querySelector('.qty-input').addEventListener('input', calculateOrder);
        row.querySelector('.rate-input').addEventListener('input', calculateOrder);

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
                    calculateOrder();
                }
            });
        }
    }

    hookRow(body.querySelector('.item-row'));

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

    // Auto-fill delivery address from selected buyer address
    const customerSelect = document.getElementById('customer_id') || document.querySelector('select[name="customer_id"]');
    const deliveryInput = document.getElementById('delivery_address') || document.querySelector('input[name="delivery_address"]');
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
});
</script>
@endpush
@endsection
