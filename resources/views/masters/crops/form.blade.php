@extends('layouts.app')

@section('title', $crop->exists ? __('crops.form_title_edit') . ' — ' . $crop->name : __('crops.form_title_create'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-2 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('crops.index') }}" class="text-success text-decoration-none">{{ __('crops.title') }}</a></li>
                <li class="breadcrumb-item active">{{ $crop->exists ? __('crops.form_title_edit') : __('crops.form_title_create') }}</li>
            </ol>
        </nav>
        <h2 class="h5 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-flower1 text-success"></i>
            <span>{{ $crop->exists ? __('crops.form_title_edit') : __('crops.form_title_create') }}</span>
        </h2>
    </div>
    <a href="{{ route('crops.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> {{ __('crops.btn_all_crops') }}
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 bg-white rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold fs-6 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-success fs-5"></i>
                    <span>{{ __('crops.form_commodity_info') }}</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ $crop->exists ? route('crops.update', $crop) : route('crops.store') }}" novalidate>
                    @csrf
                    @if($crop->exists)
                        @method('PUT')
                    @endif
                    <x-form-token />

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('crops.field_crop_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $crop->name) }}" 
                                   class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                   placeholder="e.g. Paddy (Dhan), Wheat (Gehun), Maize (Makka)" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="code" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('crops.field_crop_code') }}
                            </label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code', $crop->code) }}" 
                                   class="form-control form-control-sm text-uppercase font-monospace @error('code') is-invalid @enderror" 
                                   placeholder="e.g. PADDY, WHEAT">
                            @error('code')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">{{ __('crops.code_auto_hint') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="unit" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('crops.field_unit') }} <span class="text-danger">*</span>
                            </label>
                            <select id="unit" name="unit" class="form-select form-select-sm @error('unit') is-invalid @enderror" required>
                                <option value="quintal" {{ old('unit', $crop->unit) === 'quintal' ? 'selected' : '' }}>{{ __('crops.unit_quintal') }}</option>
                                <option value="kg" {{ old('unit', $crop->unit) === 'kg' ? 'selected' : '' }}>{{ __('crops.unit_kg') }}</option>
                                <option value="ton" {{ old('unit', $crop->unit) === 'ton' ? 'selected' : '' }}>{{ __('crops.unit_ton') }}</option>
                            </select>
                            @error('unit')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="hsn_code" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('crops.field_hsn') }}
                            </label>
                            <input type="text" 
                                   id="hsn_code" 
                                   name="hsn_code" 
                                   value="{{ old('hsn_code', $crop->hsn_code) }}" 
                                   class="form-control form-control-sm font-monospace @error('hsn_code') is-invalid @enderror" 
                                   placeholder="e.g. 1006 (Rice), 1001 (Wheat)">
                            @error('hsn_code')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('crops.field_description') }}
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3" 
                                      class="form-control form-control-sm" 
                                      placeholder="Procurement specifications, quality notes...">{{ old('description', $crop->description) }}</textarea>
                        </div>

                        {{-- Website Mandi Rate Box --}}
                        <div class="col-12 mt-3">
                            <div class="p-3 rounded-3 border bg-success-subtle border-success-subtle">
                                <input type="hidden" name="rate_section_present" value="1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold text-success mb-0 d-flex align-items-center gap-1">
                                            <i class="bi bi-graph-up-arrow"></i>
                                            <span>{{ __('crops.rate_box_title') }}</span>
                                        </h6>
                                        <small class="text-muted">{{ __('crops.rate_box_subtitle') }}</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="show_rate_on_website"
                                               name="show_rate_on_website"
                                               value="1"
                                               {{ old('show_rate_on_website', $crop->show_rate_on_website ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="show_rate_on_website">
                                            {{ __('crops.switch_display_website') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="current_purchase_rate" class="form-label small fw-semibold text-muted mb-1">
                                            {{ __('crops.field_base_rate') }}
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   id="current_purchase_rate"
                                                   name="current_purchase_rate"
                                                   value="{{ old('current_purchase_rate', $crop->current_purchase_rate) }}"
                                                   class="form-control font-monospace @error('current_purchase_rate') is-invalid @enderror"
                                                   placeholder="e.g. 2300">
                                        </div>
                                        @error('current_purchase_rate')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text small text-muted">{{ __('crops.base_rate_hint') }}</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="max_purchase_rate" class="form-label small fw-semibold text-muted mb-1">
                                            {{ __('crops.field_max_rate') }}
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   id="max_purchase_rate"
                                                   name="max_purchase_rate"
                                                   value="{{ old('max_purchase_rate', $crop->max_purchase_rate) }}"
                                                   class="form-control font-monospace @error('max_purchase_rate') is-invalid @enderror"
                                                   placeholder="Optional max">
                                        </div>
                                        @error('max_purchase_rate')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text small text-muted">{{ __('crops.max_rate_hint') }}</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="rate_note" class="form-label small fw-semibold text-muted mb-1">
                                            {{ __('crops.field_rate_note') }}
                                        </label>
                                        <input type="text"
                                               id="rate_note"
                                               name="rate_note"
                                               value="{{ old('rate_note', $crop->rate_note) }}"
                                               class="form-control form-control-sm @error('rate_note') is-invalid @enderror"
                                               placeholder="e.g. FAQ / Moisture <= 14%">
                                        @error('rate_note')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text small text-muted">{{ __('crops.rate_note_hint') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $crop->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="is_active">
                                    {{ __('crops.check_active_trading') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('crops.index') }}" class="btn btn-sm btn-light">{{ __('crops.btn_cancel') }}</a>
                        <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm">
                            <i class="bi bi-check2 me-1"></i> {{ $crop->exists ? __('crops.btn_save_changes') : __('crops.btn_add_crop') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
