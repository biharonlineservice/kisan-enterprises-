@extends('layouts.app')

@section('title', $warehouse->exists ? __('warehouses.form_title_edit') . ' — ' . $warehouse->name : __('warehouses.form_title_create'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-2 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}" class="text-success text-decoration-none">{{ __('warehouses.title') }}</a></li>
                <li class="breadcrumb-item active">{{ $warehouse->exists ? __('warehouses.form_title_edit') : __('warehouses.form_title_create') }}</li>
            </ol>
        </nav>
        <h2 class="h5 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-house-door text-success"></i>
            <span>{{ $warehouse->exists ? __('warehouses.form_title_edit') : __('warehouses.form_title_create') }}</span>
        </h2>
    </div>
    <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> {{ __('warehouses.btn_all') }}
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 bg-white rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold fs-6 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-success fs-5"></i>
                    <span>{{ __('warehouses.form_info') }}</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ $warehouse->exists ? route('warehouses.update', $warehouse) : route('warehouses.store') }}" novalidate>
                    @csrf
                    @if($warehouse->exists)
                        @method('PUT')
                    @endif
                    <x-form-token />

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('warehouses.field_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $warehouse->name) }}" 
                                   class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                   placeholder="e.g. Main Mandi Godown No. 1" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="code" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('warehouses.field_code') }}
                            </label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code', $warehouse->code) }}" 
                                   class="form-control form-control-sm text-uppercase font-monospace @error('code') is-invalid @enderror" 
                                   placeholder="e.g. WH-01">
                            @error('code')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">{{ __('warehouses.code_hint') }}</div>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('warehouses.field_address') }}
                            </label>
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address', $warehouse->address) }}" 
                                   class="form-control form-control-sm" 
                                   placeholder="Plot/Road/Mandi premise address">
                        </div>

                        <div class="col-md-6">
                            <label for="capacity_quintals" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('warehouses.field_capacity') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       id="capacity_quintals" 
                                       name="capacity_quintals" 
                                       value="{{ old('capacity_quintals', $warehouse->capacity_quintals) }}" 
                                       class="form-control font-monospace @error('capacity_quintals') is-invalid @enderror" 
                                       required>
                                <span class="input-group-text bg-light">Qtl</span>
                            </div>
                            @error('capacity_quintals')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="supervisor_id" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('warehouses.field_supervisor') }}
                            </label>
                            <select id="supervisor_id" name="supervisor_id" class="form-select form-select-sm @error('supervisor_id') is-invalid @enderror">
                                <option value="">{{ __('warehouses.select_supervisor') }}</option>
                                @foreach($supervisors as $s)
                                    <option value="{{ $s->id }}" {{ old('supervisor_id', $warehouse->supervisor_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }} ({{ $s->role?->display_name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supervisor_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-semibold text-muted mb-1">
                                {{ __('warehouses.field_phone') }}
                            </label>
                            <input type="text" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $warehouse->phone) }}" 
                                   class="form-control form-control-sm font-monospace" 
                                   placeholder="e.g. 9876543210">
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch mt-3">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $warehouse->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="is_active">
                                    {{ __('warehouses.check_active') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-light">{{ __('warehouses.btn_cancel') }}</a>
                        <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm">
                            <i class="bi bi-check2 me-1"></i> {{ $warehouse->exists ? __('warehouses.btn_save') : __('warehouses.btn_create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
