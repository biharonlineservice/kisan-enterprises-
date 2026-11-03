@extends('layouts.auth')

@section('title', __('auth.login_title'))

@section('content')
<div class="card p-4 shadow-sm border">
    <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
            <i class="bi bi-phone-fill me-1"></i>{{ app()->getLocale() === 'hi' ? 'मोबाइल लॉगिन' : 'Mobile Login' }}
        </span>
    </div>
    <h1 class="h4 fw-bold mb-1">{{ __('auth.login_title') }}</h1>
    <p class="text-muted small mb-4">{{ __('auth.login_subtitle') }}</p>

    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="mobile" class="form-label">{{ __('auth.mobile_number') }} <span class="req">*</span></label>
            <div class="input-group">
                <span class="input-group-text fw-semibold text-dark">
                    <i class="bi bi-telephone-fill text-success me-1"></i>+91
                </span>
                <input type="tel"
                       id="mobile"
                       name="mobile"
                       value="{{ old('mobile') }}"
                       inputmode="numeric"
                       autocomplete="tel-national"
                       maxlength="10"
                       pattern="[6-9][0-9]{9}"
                       class="form-control fw-semibold tabular-nums @error('mobile') is-invalid @enderror"
                       placeholder="{{ __('auth.mobile_placeholder') }}"
                       required
                       autofocus>
            </div>
            @error('mobile')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label mb-0">{{ __('auth.password_label') }} <span class="req">*</span></label>
                <a href="{{ route('password.request') }}" class="small text-decoration-none">{{ __('auth.forgot_password') }}</a>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password"
                       id="password"
                       name="password"
                       autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••"
                       required>
                <button type="button" class="btn btn-outline-secondary" id="togglePasswordBtn" aria-label="Toggle password visibility" tabindex="-1">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label small" for="remember">{{ __('auth.remember_me') }}</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('auth.login_btn') }}
        </button>
    </form>
</div>

@if(app()->environment('local'))
    <div class="card mt-3 p-3 bg-white border small">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                {{ app()->getLocale() === 'hi' ? 'त्वरित डेमो लॉगिन (मोबाइल व पासवर्ड):' : 'Development Quick Logins (Mobile & Password):' }}
            </strong>
        </div>
        <div class="d-flex flex-column gap-1">
            <button type="button" class="btn btn-sm btn-light border text-start d-flex justify-content-between align-items-center js-dev-login" data-mobile="9000000001" data-pass="Kisan@1234">
                <span><strong>Admin:</strong> <code>9000000001</code> <span class="text-muted">/ Kisan@1234</span></span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ app()->getLocale() === 'hi' ? 'भरें' : 'Fill' }}</span>
            </button>
            <button type="button" class="btn btn-sm btn-light border text-start d-flex justify-content-between align-items-center js-dev-login" data-mobile="9000000002" data-pass="Kisan@1234">
                <span><strong>Purchase & Sales:</strong> <code>9000000002</code> <span class="text-muted">/ Kisan@1234</span></span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ app()->getLocale() === 'hi' ? 'भरें' : 'Fill' }}</span>
            </button>
            <button type="button" class="btn btn-sm btn-light border text-start d-flex justify-content-between align-items-center js-dev-login" data-mobile="9000000003" data-pass="Kisan@1234">
                <span><strong>Accounts:</strong> <code>9000000003</code> <span class="text-muted">/ Kisan@1234</span></span>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ app()->getLocale() === 'hi' ? 'भरें' : 'Fill' }}</span>
            </button>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mobileInput = document.getElementById('mobile');
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (mobileInput) {
        mobileInput.addEventListener('input', function () {
            let digits = mobileInput.value.replace(/\D+/g, '');
            if (digits.length === 12 && digits.startsWith('91')) {
                digits = digits.slice(2);
            } else if (digits.length === 11 && digits.startsWith('0')) {
                digits = digits.slice(1);
            }
            mobileInput.value = digits.slice(0, 10);
        });
    }

    if (toggleBtn && pwdInput && toggleIcon) {
        toggleBtn.addEventListener('click', function () {
            const show = pwdInput.type === 'password';
            pwdInput.type = show ? 'text' : 'password';
            toggleIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    document.querySelectorAll('.js-dev-login').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (mobileInput) mobileInput.value = btn.getAttribute('data-mobile') || '';
            if (pwdInput) pwdInput.value = btn.getAttribute('data-pass') || '';
        });
    });
});
</script>
@endpush
