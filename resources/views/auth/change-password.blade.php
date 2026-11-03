@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm border">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock text-primary fs-5"></i>
                    <div>
                        <h5 class="mb-0 fw-bold">Change Password</h5>
                        <small class="text-muted">Keep your account secure with a strong password.</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                @if(auth()->user()->must_change_password)
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><strong>Action Required:</strong> You are currently using a temporary password. Please set a new personal password to continue using the system.</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.change.update') }}" novalidate>
                    @csrf
                    @method('PUT')
                    <x-form-token />

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="req">*</span></label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               required 
                               autofocus>
                        @error('current_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="req">*</span></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Minimum 8 characters (letters & numbers)" 
                               required>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="req">*</span></label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="form-control" 
                               placeholder="Re-type new password" 
                               required>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        @if(!auth()->user()->must_change_password)
                            <a href="{{ route('dashboard') }}" class="btn btn-light">Cancel</a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
