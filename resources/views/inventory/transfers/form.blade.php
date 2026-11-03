@extends('layouts.app')

@section('title', __('inventory.transfers.new_transfer'))

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('inventory.title') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('stock-transfers.index') }}">{{ __('inventory.transfers.title') }}</a></li>
            <li class="breadcrumb-item active">{{ __('inventory.transfers.new_transfer') }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center justify-content-between">
        <h2 class="h4 mb-0 fw-bold text-dark">{{ __('inventory.transfers.new_transfer') }}</h2>
        <a href="{{ route('stock-transfers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('inventory.transfers.tab_all') }}
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0 fw-bold text-dark">
                    <i class="bi bi-truck-flatbed text-primary me-2"></i>Inter-Warehouse Transfer Particulars
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('stock-transfers.store') }}" id="transferForm">
                    @csrf
                    <x-form-token />

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label required small fw-semibold">{{ __('inventory.transfer_date') }}</label>
                            <input type="date" name="transfer_date" class="form-control form-control-sm @error('transfer_date') is-invalid @enderror"
                                value="{{ old('transfer_date', now()->format('Y-m-d')) }}" required>
                            @error('transfer_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required small fw-semibold">{{ __('inventory.transfers.source_godown') }}</label>
                            <select name="from_warehouse_id" id="from_warehouse_id" class="form-select form-select-sm @error('from_warehouse_id') is-invalid @enderror" required>
                                <option value="">Select Source Warehouse</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" @selected(old('from_warehouse_id') == $w->id)>
                                        {{ $w->name }} ({{ $w->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('from_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required small fw-semibold">{{ __('inventory.transfers.dest_godown') }}</label>
                            <select name="to_warehouse_id" id="to_warehouse_id" class="form-select form-select-sm @error('to_warehouse_id') is-invalid @enderror" required>
                                <option value="">Select Destination Warehouse</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" @selected(old('to_warehouse_id') == $w->id)>
                                        {{ $w->name }} ({{ $w->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('to_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold">{{ __('inventory.commodity') }}</label>
                            <select name="crop_id" id="crop_id" class="form-select form-select-sm @error('crop_id') is-invalid @enderror" required>
                                <option value="">Select Crop</option>
                                @foreach($crops as $c)
                                    <option value="{{ $c->id }}" @selected(old('crop_id') == $c->id)>
                                        {{ $c->name }} ({{ $c->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('crop_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">{{ __('inventory.variety') }}</label>
                            <select name="crop_variety_id" id="crop_variety_id" class="form-select form-select-sm @error('crop_variety_id') is-invalid @enderror">
                                <option value="">Standard / Mixed</option>
                                @foreach($crops as $c)
                                    @foreach($c->varieties as $v)
                                        <option value="{{ $v->id }}" data-crop="{{ $c->id }}" @selected(old('crop_variety_id') == $v->id)>
                                            {{ $c->name }} - {{ $v->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('crop_variety_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold">{{ __('inventory.transfers.col_weight_kg') }}</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.001" min="0.001" name="quantity_kg" id="quantity_kg"
                                    class="form-control text-end fw-bold font-monospace @error('quantity_kg') is-invalid @enderror"
                                    value="{{ old('quantity_kg') }}" required placeholder="0.000">
                                <span class="input-group-text">Kg</span>
                            </div>
                            @error('quantity_kg') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Conversion Breakdown</label>
                            <div class="p-2 bg-light border rounded d-flex justify-content-between align-items-center" style="min-height: 38px;">
                                <div>
                                    <span class="fs-6 fw-bold text-success" id="display_transfer_qtl">0.000</span>
                                    <span class="text-muted small">Qtl</span>
                                </div>
                                <span class="badge bg-white text-dark border font-monospace" id="display_transfer_bags">~0 Bags</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">{{ __('inventory.transfers.col_vehicle') }}</label>
                            <input type="text" name="vehicle_number" class="form-control form-control-sm text-uppercase @error('vehicle_number') is-invalid @enderror"
                                value="{{ old('vehicle_number') }}" placeholder="e.g. BR 01 GA 4321">
                            @error('vehicle_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">{{ __('app.notes') }}</label>
                            <input type="text" name="notes" class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                value="{{ old('notes') }}" placeholder="e.g. Inter-godown consolidation, transit note">
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary">
                            {{ __('app.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Complete & Issue Challan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border mb-4">
            <div class="card-body">
                <h6 class="card-title fw-bold text-dark mb-3">
                    <i class="bi bi-shield-check text-success me-1"></i> Stock Transfer Policy
                </h6>
                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-2">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check2 text-success mt-1"></i>
                        <span>Immediate double-entry: Outward (-) from source warehouse, Inward (+) to destination warehouse.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check2 text-success mt-1"></i>
                        <span>Available stock is verified at submit; negative transfers are blocked if insufficient stock exists.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check2 text-success mt-1"></i>
                        <span>Standard delivery challan with barcode and dual consignee/transporter copy is auto-generated.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyKgInput = document.getElementById('quantity_kg');
    const displayQtl = document.getElementById('display_transfer_qtl');
    const displayBags = document.getElementById('display_transfer_bags');

    function updateCalculations() {
        const kg = parseFloat(qtyKgInput.value) || 0;
        const qtl = kg / 100.0;
        const bags = Math.round(qtl * 2);
        displayQtl.textContent = qtl.toLocaleString('en-IN', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
        displayBags.textContent = '~' + bags.toLocaleString('en-IN') + ' Bags';
    }

    if (qtyKgInput) {
        qtyKgInput.addEventListener('input', updateCalculations);
        updateCalculations();
    }

    const cropSelect = document.getElementById('crop_id');
    const varietySelect = document.getElementById('crop_variety_id');
    if (cropSelect && varietySelect) {
        function filterVarieties() {
            const cropId = cropSelect.value;
            const options = varietySelect.querySelectorAll('option');
            options.forEach(opt => {
                if (!opt.value) return;
                opt.hidden = !(!cropId || opt.dataset.crop === cropId);
            });
        }
        cropSelect.addEventListener('change', filterVarieties);
        filterVarieties();
    }
});
</script>
@endpush
@endsection
