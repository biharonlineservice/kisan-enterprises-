@extends('layouts.app')

@section('title', __('inventory.adjustments.form_title'))

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('stock-adjustments.index') }}" class="text-success text-decoration-none">{{ __('inventory.adjustments.title') }}</a></li>
            <li class="breadcrumb-item active">{{ __('inventory.adjustments.form_title') }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h4 fw-bold mb-1 text-dark">{{ __('inventory.adjustments.form_title') }}</h2>
            <p class="text-muted small mb-0">{{ __('inventory.adjustments.form_subtitle') }}</p>
        </div>
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>{{ __('app.back') }}
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 bg-white rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title h6 mb-0 text-success fw-bold">
                    <i class="bi bi-sliders me-1"></i> {{ __('inventory.adjustments.particulars') }}
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('stock-adjustments.store') }}" id="adjForm">
                    @csrf
                    <x-form-token />

                    <div class="row g-3">
                        {{-- Adjustment Date --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold text-muted">{{ __('inventory.adjustments.field_date') }}</label>
                            <input type="date" name="adjustment_date" class="form-control @error('adjustment_date') is-invalid @enderror"
                                value="{{ old('adjustment_date', now()->format('Y-m-d')) }}" required>
                            @error('adjustment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Warehouse --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold text-muted">{{ __('inventory.adjustments.field_warehouse') }}</label>
                            <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                                <option value="">-- {{ __('app.select') }} {{ __('inventory.warehouse') }} --</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>
                                        {{ $w->name }} ({{ $w->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Adjustment Category --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold text-muted">{{ __('inventory.adjustments.field_type') }}</label>
                            <select name="adjustment_type" id="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                @foreach($types as $k => $label)
                                    <option value="{{ $k }}" @selected(old('adjustment_type', 'shrinkage') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('adjustment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Direction Toggle (Increase vs Decrease) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold text-muted">{{ __('inventory.adjustments.field_direction') }}</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="direction" id="dir_decrease" value="decrease"
                                       @checked(old('direction', (old('quantity_kg') !== null && (float)old('quantity_kg') < 0) ? 'decrease' : 'decrease') === 'decrease')>
                                <label class="btn btn-outline-danger btn-sm py-2" for="dir_decrease">
                                    <i class="bi bi-dash-circle me-1"></i> {{ __('inventory.adjustments.badge_out') }}
                                </label>

                                <input type="radio" class="btn-check" name="direction" id="dir_increase" value="increase"
                                       @checked(old('direction', (old('quantity_kg') !== null && (float)old('quantity_kg') > 0) ? 'increase' : '') === 'increase')>
                                <label class="btn btn-outline-success btn-sm py-2" for="dir_increase">
                                    <i class="bi bi-plus-circle me-1"></i> {{ __('inventory.adjustments.badge_in') }}
                                </label>
                            </div>
                        </div>

                        {{-- Commodity / Crop --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold text-muted">{{ __('inventory.adjustments.field_crop') }}</label>
                            <select name="crop_id" id="crop_id" class="form-select @error('crop_id') is-invalid @enderror" required>
                                <option value="">-- {{ __('app.select') }} {{ __('inventory.commodity') }} --</option>
                                @foreach($crops as $c)
                                    <option value="{{ $c->id }}" @selected(old('crop_id') == $c->id)>
                                        {{ $c->name }} ({{ $c->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('crop_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Variety --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted">{{ __('inventory.adjustments.field_variety') }}</label>
                            <select name="crop_variety_id" id="crop_variety_id" class="form-select @error('crop_variety_id') is-invalid @enderror">
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

                        {{-- Quantity Input (Kg) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label required small fw-semibold text-muted">{{ __('inventory.adjustments.field_weight_kg') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold" id="dir_prefix">-</span>
                                <input type="number" step="0.001" min="0.001" name="quantity_kg" id="quantity_kg"
                                    class="form-control text-end fw-bold @error('quantity_kg') is-invalid @enderror"
                                    value="{{ abs((float) old('quantity_kg', 0)) > 0 ? abs((float) old('quantity_kg')) : '' }}"
                                    required placeholder="0.000">
                                <span class="input-group-text">Kg</span>
                            </div>
                            @error('quantity_kg') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Live Calculated Volume Display --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted">{{ __('inventory.adjustments.field_qtl_equiv') }}</label>
                            <div class="p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-5 fw-bold text-danger" id="display_adj_qtl">0.000</span>
                                    <span class="text-muted small">Qtl</span>
                                </div>
                                <span class="badge bg-white text-dark border font-monospace" id="display_adj_bags">
                                    ~0 {{ __('inventory.col_bags') }}
                                </span>
                            </div>
                        </div>

                        {{-- Detailed Reason --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label required small fw-semibold text-muted mb-0">
                                    {{ __('inventory.adjustments.field_reason') }}
                                </label>
                                <span class="small text-muted">Presets:</span>
                            </div>

                            {{-- Preset quick chips --}}
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary preset-chip" style="font-size: 0.75rem;"
                                        data-text="Natural moisture loss and drying shrinkage during storage period">
                                    Moisture Loss
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary preset-chip" style="font-size: 0.75rem;"
                                        data-text="Bag handling spillage and torn packaging write-off during stacking">
                                    Bag Spillage
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary preset-chip" style="font-size: 0.75rem;"
                                        data-text="Physical godown inventory count reconciliation and weight verification variance">
                                    Audit Variance
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary preset-chip" style="font-size: 0.75rem;"
                                        data-text="Weighbridge calibration tare weight difference adjustment">
                                    Weighbridge Tare
                                </button>
                            </div>

                            <textarea name="reason" id="reason_input" rows="3" class="form-control @error('reason') is-invalid @enderror"
                                placeholder="{{ __('inventory.adjustments.field_reason_placeholder') }}" required minlength="5">{{ old('reason') }}</textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary px-4">
                            {{ __('inventory.adjustments.cancel_btn') }}
                        </a>
                        <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                            <i class="bi bi-send me-1"></i> {{ __('inventory.adjustments.submit_btn') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Side Helper & Governance Card --}}
    <div class="col-12 col-lg-4">
        {{-- Live Preview Card --}}
        <div class="card shadow-sm border-0 mb-4 bg-white rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark">
                    <i class="bi bi-eye me-1 text-success"></i> Adjustment Summary Preview
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ __('inventory.warehouse') }}:</span>
                    <strong id="preview_warehouse" class="text-dark">Select warehouse</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ __('inventory.commodity') }}:</span>
                    <strong id="preview_crop" class="text-dark">Select crop</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ __('inventory.adj_type') }}:</span>
                    <span class="badge bg-secondary-subtle text-secondary" id="preview_type">Natural Shrinkage</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted">Net Stock Impact:</span>
                    <span class="fs-6 fw-bold text-danger" id="preview_qty">0.000 Qtl</span>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">Estimated Bags:</span>
                    <span class="font-monospace fw-bold" id="preview_bags">~0 Bags</span>
                </div>
            </div>
        </div>

        {{-- Policy and Governance Card --}}
        <div class="card shadow-sm border-0 bg-white rounded-3 border-start border-4 border-warning">
            <div class="card-body">
                <h6 class="card-title text-warning-emphasis fw-bold mb-2">
                    <i class="bi bi-shield-lock me-1"></i> {{ __('inventory.adjustments.workflow_title') }}
                </h6>
                <p class="small text-muted mb-2">
                    • {!! __('inventory.adjustments.workflow_p1') !!}
                </p>
                <p class="small text-muted mb-2">
                    • {!! __('inventory.adjustments.workflow_p2') !!}
                </p>
                <p class="small text-muted mb-0">
                    • {!! __('inventory.adjustments.workflow_p3') !!}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('quantity_kg');
    const displayQtl = document.getElementById('display_adj_qtl');
    const displayBags = document.getElementById('display_adj_bags');
    const dirPrefix = document.getElementById('dir_prefix');
    const dirDecrease = document.getElementById('dir_decrease');
    const dirIncrease = document.getElementById('dir_increase');
    const reasonInput = document.getElementById('reason_input');
    const previewWarehouse = document.getElementById('preview_warehouse');
    const previewCrop = document.getElementById('preview_crop');
    const previewType = document.getElementById('preview_type');
    const previewQty = document.getElementById('preview_qty');
    const previewBags = document.getElementById('preview_bags');
    const warehouseSelect = document.getElementById('warehouse_id');
    const cropSelect = document.getElementById('crop_id');
    const typeSelect = document.getElementById('adjustment_type');
    const varietySelect = document.getElementById('crop_variety_id');

    function updateCalculations() {
        const kg = Math.abs(parseFloat(qtyInput.value) || 0);
        const isInc = dirIncrease.checked;
        const qtl = (kg / 100.0);
        const bags = Math.round(kg / 50.0);

        if (dirPrefix) {
            dirPrefix.textContent = isInc ? '+' : '-';
            dirPrefix.className = 'input-group-text fw-bold ' + (isInc ? 'bg-success text-white' : 'bg-danger text-white');
        }

        const formattedQtl = (isInc ? '+' : '-') + qtl.toFixed(3);
        displayQtl.textContent = formattedQtl;
        displayQtl.className = 'fs-5 fw-bold ' + (isInc ? 'text-success' : 'text-danger');

        displayBags.textContent = `~${bags} Bags (~50kg)`;

        // Update side preview
        if (previewQty) {
            previewQty.textContent = formattedQtl + ' Qtl';
            previewQty.className = 'fs-6 fw-bold ' + (isInc ? 'text-success' : 'text-danger');
        }
        if (previewBags) {
            previewBags.textContent = `~${bags} Bags`;
        }
    }

    if (qtyInput) {
        qtyInput.addEventListener('input', updateCalculations);
    }
    if (dirDecrease) {
        dirDecrease.addEventListener('change', updateCalculations);
    }
    if (dirIncrease) {
        dirIncrease.addEventListener('change', updateCalculations);
    }

    // Presets handler
    document.querySelectorAll('.preset-chip').forEach(btn => {
        btn.addEventListener('click', function() {
            if (reasonInput) {
                reasonInput.value = this.dataset.text;
                reasonInput.focus();
            }
        });
    });

    // Side preview updates
    if (warehouseSelect) {
        warehouseSelect.addEventListener('change', function() {
            const opt = warehouseSelect.options[warehouseSelect.selectedIndex];
            previewWarehouse.textContent = opt && opt.value ? opt.text : 'Select warehouse';
        });
    }

    if (cropSelect) {
        cropSelect.addEventListener('change', function() {
            const opt = cropSelect.options[cropSelect.selectedIndex];
            previewCrop.textContent = opt && opt.value ? opt.text : 'Select crop';
            filterVarieties();
        });
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const opt = typeSelect.options[typeSelect.selectedIndex];
            previewType.textContent = opt ? opt.text : '';
            // Auto suggest direction
            if (typeSelect.value === 'physical_reconciliation') {
                // leave as user chose
            } else if (typeSelect.value === 'damage' || typeSelect.value === 'shrinkage') {
                if (dirDecrease) {
                    dirDecrease.checked = true;
                    updateCalculations();
                }
            }
        });
    }

    function filterVarieties() {
        if (!cropSelect || !varietySelect) return;
        const cropId = cropSelect.value;
        const options = varietySelect.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return;
            opt.hidden = !(!cropId || opt.dataset.crop === cropId);
        });
    }

    // Initialize
    updateCalculations();
    filterVarieties();
    if (warehouseSelect && warehouseSelect.value) {
        const opt = warehouseSelect.options[warehouseSelect.selectedIndex];
        previewWarehouse.textContent = opt.text;
    }
    if (cropSelect && cropSelect.value) {
        const opt = cropSelect.options[cropSelect.selectedIndex];
        previewCrop.textContent = opt.text;
    }
});
</script>
@endpush
@endsection