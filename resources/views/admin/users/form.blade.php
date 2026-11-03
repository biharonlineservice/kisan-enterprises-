@extends('layouts.app')

@section('title', $user->exists ? __('users.form_edit_title', ['name' => $user->name]) : __('users.form_create_title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('users.index') }}" class="erp-hero-chip text-decoration-none" style="background: rgba(255, 255, 255, 0.22); border-color: rgba(255, 255, 255, 0.4);">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ __('users.btn_back') }}</span>
                </a>
                <span class="erp-hero-chip">
                    <i class="bi bi-person-badge"></i>
                    <strong>{{ $user->exists ? __('users.form_edit_title', ['name' => $user->name]) : __('users.form_create_title') }}</strong>
                </span>
                @if($user->exists)
                    <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0;">
                        <i class="bi bi-shield-check"></i>
                        {{ $user->role?->display_name ?? 'No Role' }}
                    </span>
                @endif
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">
                {{ $user->exists ? __('users.form_edit_title', ['name' => $user->name]) : __('users.form_create_title') }}
            </h2>
            <p class="mb-0 small text-white-50" style="max-width: 720px;">
                {{ $user->exists ? __('users.form_edit_subtitle') : __('users.form_create_subtitle') }}
            </p>
        </div>

        <div class="flex-shrink-0">
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light text-success fw-bold shadow-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ __('users.btn_back') }}</span>
            </a>
        </div>
    </div>
</div>

{{-- 2. Staff Registration / Edit Form --}}
<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" novalidate>
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif
            <x-form-token />

            <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success p-2 rounded-circle">
                            <i class="bi bi-person-lines-fill fs-6"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $user->exists ? __('users.form_edit_title', ['name' => $user->name]) : __('users.form_create_title') }}</h6>
                            <small class="text-muted">Enter identification details and assign role permissions.</small>
                        </div>
                    </div>
                    <span class="badge bg-light text-dark border font-monospace">
                        {{ $user->exists ? 'ID: #' . $user->id : 'New Staff' }}
                    </span>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Full Name --}}
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold small required">{{ __('users.label_name') }}</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="{{ __('users.placeholder_name') }}" 
                                   required 
                                   maxlength="100">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Legal staff name as per government identification.</small>
                        </div>

                        {{-- Mobile Number (Login ID) --}}
                        <div class="col-md-6">
                            <label for="mobile" class="form-label fw-semibold small required">{{ __('users.label_mobile') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold font-monospace text-muted">+91</span>
                                <input type="tel" 
                                       id="mobile" 
                                       name="mobile" 
                                       value="{{ old('mobile', $user->mobile) }}" 
                                       inputmode="numeric"
                                       class="form-control font-monospace @error('mobile') is-invalid @enderror" 
                                       placeholder="{{ __('users.placeholder_mobile') }}" 
                                       maxlength="10" 
                                       required>
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">{{ __('users.help_mobile') }}</small>
                        </div>

                        {{-- Email Address --}}
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold small">{{ __('users.label_email') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="{{ __('users.placeholder_email') }}" 
                                       maxlength="150">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">{{ __('users.help_email') }}</small>
                        </div>

                        {{-- Designation / Title --}}
                        <div class="col-md-6">
                            <label for="designation" class="form-label fw-semibold small">{{ __('users.label_designation') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-briefcase"></i></span>
                                <input type="text" 
                                       id="designation" 
                                       name="designation" 
                                       value="{{ old('designation', $user->designation) }}" 
                                       class="form-control @error('designation') is-invalid @enderror" 
                                       placeholder="{{ __('users.placeholder_designation') }}" 
                                       maxlength="100">
                                @error('designation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Title shown on vouchers, receipts, and staff directory.</small>
                        </div>

                        {{-- System Role --}}
                        <div class="col-md-6">
                            <label for="role_id" class="form-label fw-semibold small required">{{ __('users.label_role') }}</label>
                            <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Select a Role...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }} ({{ $role->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('users.help_role') }}</small>
                        </div>

                        {{-- Active Status Checkbox (Now perfectly aligned) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">{{ __('users.label_is_active') }}</label>
                            <div class="form-control d-flex align-items-center justify-content-between bg-light">
                                <label class="form-check-label fw-semibold text-dark mb-0 d-flex align-items-center gap-2" for="is_active" style="cursor: pointer;">
                                    <i class="bi bi-shield-check text-success fs-5"></i>
                                    <span>{{ __('users.label_is_active') }}</span>
                                </label>
                                <div class="form-check form-switch m-0 p-0">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           role="switch"
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           style="cursor: pointer; width: 2.25rem; height: 1.15rem;"
                                           {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <small class="form-text text-muted">{{ __('users.help_is_active') }}</small>
                        </div>

                        {{-- Section Divider: Security & Password Setup --}}
                        <div class="col-12 mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-circle">
                                    <i class="bi bi-shield-lock fs-6"></i>
                                </span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('users.section_password') }}</h6>
                                    <small class="text-muted">{{ $user->exists ? __('users.help_password_edit') : __('users.help_password_create') }}</small>
                                </div>
                            </div>
                        </div>

                        {{-- Password Field with Eye Toggle --}}
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold small {{ $user->exists ? '' : 'required' }}">
                                {{ __('users.label_password') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="{{ __('users.placeholder_password') }}" 
                                       {{ $user->exists ? '' : 'required' }}>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password', this)" title="Show/Hide Password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                {{ $user->exists ? __('users.help_password_edit') : __('users.help_password_create') }}
                            </small>
                        </div>

                        {{-- Password Confirmation with Eye Toggle --}}
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold small {{ $user->exists ? '' : 'required' }}">
                                {{ __('users.label_password_confirmation') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="form-control" 
                                       placeholder="{{ __('users.placeholder_password_confirmation') }}" 
                                       {{ $user->exists ? '' : 'required' }}>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password_confirmation', this)" title="Show/Hide Password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Repeat the exact same password to confirm.</small>
                        </div>
                    </div>

                    {{-- Actions Footer --}}
                    <div class="d-flex align-items-center justify-content-end gap-2 pt-4 border-top mt-4">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4">
                            {{ __('users.btn_cancel') }}
                        </a>
                        <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i>
                            {{ $user->exists ? __('users.btn_save') : __('users.btn_create') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endsection
