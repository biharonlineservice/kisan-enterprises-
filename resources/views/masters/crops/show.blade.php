@extends('layouts.app')

@section('title', __('crops.show_title', ['name' => $crop->name]))

@section('content')
{{-- Header with Breadcrumbs & Actions --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 pb-2 border-bottom">
    <div class="flex-grow-1" style="min-width: 0;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('crops.index') }}" class="text-success text-decoration-none">{{ __('crops.title') }}</a></li>
                <li class="breadcrumb-item active text-truncate">{{ $crop->name }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h2 class="h5 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-flower1 text-success"></i>
                <span>{{ $crop->name }}</span>
            </h2>
            <code class="badge bg-light text-primary border font-monospace">{{ $crop->code }}</code>
            @if($crop->is_active)
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="bi bi-check-circle me-1"></i>{{ __('crops.status_active_pill') }}
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                    <i class="bi bi-dash-circle me-1"></i>{{ __('crops.status_inactive_pill') }}
                </span>
            @endif
            <span class="badge bg-light text-dark border">
                {{ __('crops.col_unit') }}: <strong>{{ $crop->shortUnitLabel() }}</strong>
            </span>
            @if($crop->hsn_code)
                <span class="badge bg-light text-muted border font-monospace">
                    HSN: {{ $crop->hsn_code }}
                </span>
            @endif
            @if($crop->formattedPurchaseRate())
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-graph-up-arrow me-1"></i>{{ $crop->formattedPurchaseRate() }}
                    @if($crop->show_rate_on_website)
                        <span class="badge bg-success text-white ms-1 py-0 px-1" style="font-size: 0.65rem;">Hero Live</span>
                    @endif
                </span>
            @endif
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2 flex-shrink-0">
        <a href="{{ route('crops.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('crops.btn_all_crops') }}
        </a>
        <a href="{{ route('crops.edit', $crop) }}" class="btn btn-sm btn-success fw-semibold shadow-sm">
            <i class="bi bi-pencil me-1"></i> {{ __('crops.btn_edit_crop') }}
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Add Variety Form --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 bg-white rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold fs-6 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill text-success fs-5"></i>
                    <span>{{ __('crops.add_variety_title') }}</span>
                </h5>
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ route('crops.varieties.store', $crop) }}" novalidate>
                    @csrf
                    <x-form-token />

                    <div class="mb-3">
                        <label for="variety_name" class="form-label small fw-semibold text-muted mb-1">
                            {{ __('crops.variety_name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               id="variety_name" 
                               name="name" 
                               class="form-control form-control-sm @error('name') is-invalid @enderror" 
                               placeholder="{{ __('crops.variety_name_placeholder') }}" 
                               required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="variety_code" class="form-label small fw-semibold text-muted mb-1">
                            {{ __('crops.variety_code') }}
                        </label>
                        <input type="text" 
                               id="variety_code" 
                               name="code" 
                               class="form-control form-control-sm text-uppercase font-monospace @error('code') is-invalid @enderror" 
                               placeholder="{{ __('crops.variety_code_placeholder') }}">
                        @error('code')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="standard_moisture_pct" class="form-label small fw-semibold text-muted mb-1">
                            {{ __('crops.standard_moisture') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="number" 
                                   step="0.1" 
                                   min="0" 
                                   max="100" 
                                   id="standard_moisture_pct" 
                                   name="standard_moisture_pct" 
                                   value="{{ old('standard_moisture_pct', '14.0') }}" 
                                   class="form-control font-monospace @error('standard_moisture_pct') is-invalid @enderror" 
                                   required>
                            <span class="input-group-text bg-light">%</span>
                        </div>
                        <div class="form-text small text-muted">{{ __('crops.moisture_hint') }}</div>
                        @error('standard_moisture_pct')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="default_grade" class="form-label small fw-semibold text-muted mb-1">
                            {{ __('crops.default_grade') }} <span class="text-danger">*</span>
                        </label>
                        <select id="default_grade" name="default_grade" class="form-select form-select-sm @error('default_grade') is-invalid @enderror" required>
                            <option value="Grade A">Grade A (Premium)</option>
                            <option value="Grade B" selected>Grade B (Standard)</option>
                            <option value="Grade C">Grade C (Commercial)</option>
                            <option value="FAQ">FAQ (Fair Average Quality)</option>
                        </select>
                        @error('default_grade')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="variety_description" class="form-label small fw-semibold text-muted mb-1">
                            {{ __('crops.variety_notes') }}
                        </label>
                        <textarea id="variety_description" name="description" rows="2" class="form-control form-control-sm" placeholder="{{ __('crops.variety_notes_placeholder') }}"></textarea>
                    </div>

                    <button type="submit" class="btn btn-sm btn-success w-100 fw-semibold shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('crops.btn_add_variety') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right Column: Varieties List --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 bg-white rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold fs-6 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-tags-fill text-primary fs-5"></i>
                    <span>{{ __('crops.varieties_list_title', ['name' => $crop->name]) }}</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    {{ __('crops.varieties_registered', ['count' => $crop->varieties->count()]) }}
                </span>
            </div>

            {{-- Desktop Table View --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">{{ __('crops.variety_name') }}</th>
                            <th>{{ __('crops.col_code') }}</th>
                            <th>{{ __('crops.standard_moisture') }}</th>
                            <th>{{ __('crops.default_grade') }}</th>
                            <th>{{ __('crops.col_status') }}</th>
                            <th class="text-end pe-3">{{ __('crops.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($crop->varieties as $v)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ $v->name }}</div>
                                    @if($v->description)
                                        <small class="text-muted text-truncate d-block" style="max-width: 250px;">{{ $v->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code class="small text-primary font-monospace">{{ $v->code ?: '—' }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $v->standard_moisture_pct }}%</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $v->default_grade }}</span>
                                </td>
                                <td>
                                    @if($v->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            {{ __('crops.status_active_pill') }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            {{ __('crops.status_inactive_pill') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" 
                                            class="btn btn-sm btn-light border" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editVarietyModal{{ $v->id }}" 
                                            title="{{ __('crops.btn_save_changes') }}">
                                        <i class="bi bi-pencil text-dark"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state py-5 text-center">
                                        <i class="bi bi-tags fs-1 text-muted mb-2 d-block"></i>
                                        <h6 class="fw-bold mb-1">{{ __('crops.no_varieties_title') }}</h6>
                                        <p class="small text-muted mb-0">{{ __('crops.no_varieties_desc', ['name' => $crop->name]) }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards Feed (< 768px) --}}
            <div class="d-md-none p-2">
                @forelse($crop->varieties as $v)
                    <div class="card shadow-sm border rounded-3 mb-2 p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong class="text-dark d-block">{{ $v->name }}</strong>
                                <code class="small text-primary font-monospace">{{ $v->code ?: '—' }}</code>
                            </div>
                            <div>
                                @if($v->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        {{ __('crops.status_active_pill') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        {{ __('crops.status_inactive_pill') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-light text-dark border">
                                Moisture: {{ $v->standard_moisture_pct }}%
                            </span>
                            <span class="badge bg-light text-dark border">
                                Grade: {{ $v->default_grade }}
                            </span>
                        </div>

                        @if($v->description)
                            <p class="small text-muted mb-2">{{ $v->description }}</p>
                        @endif

                        <div class="text-end pt-2 border-top">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editVarietyModal{{ $v->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-4 text-center">
                        <i class="bi bi-tags fs-2 text-muted mb-1 d-block"></i>
                        <h6 class="fw-bold mb-1">{{ __('crops.no_varieties_title') }}</h6>
                        <p class="small text-muted mb-0">{{ __('crops.no_varieties_desc', ['name' => $crop->name]) }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Edit Variety Modals --}}
@foreach($crop->varieties as $v)
    <div class="modal fade" id="editVarietyModal{{ $v->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('crops.varieties.update', [$crop, $v]) }}">
                    @csrf
                    @method('PUT')
                    <x-form-token />
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold fs-6">{{ __('crops.edit_variety_title', ['name' => $v->name]) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('crops.variety_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ $v->name }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('crops.variety_code') }}</label>
                            <input type="text" name="code" value="{{ $v->code }}" class="form-control form-control-sm text-uppercase font-monospace">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('crops.standard_moisture') }} <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.1" name="standard_moisture_pct" value="{{ $v->standard_moisture_pct }}" class="form-control font-monospace" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('crops.default_grade') }} <span class="text-danger">*</span></label>
                            <input type="text" name="default_grade" value="{{ $v->default_grade }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('crops.variety_notes') }}</label>
                            <textarea name="description" rows="2" class="form-control form-control-sm">{{ $v->description }}</textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="v_active_{{ $v->id }}" name="is_active" value="1" {{ $v->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="v_active_{{ $v->id }}">{{ __('crops.variety_is_active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('crops.btn_cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm">{{ __('crops.btn_save_changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
