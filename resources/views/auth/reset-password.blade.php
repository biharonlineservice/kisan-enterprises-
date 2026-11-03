@extends('layouts.auth')

@section('title', 'Set New Password')

@section('content')
<div class="card p-4 shadow-sm border">
    <h1 class="h4 fw-bold mb-1">Set New Password</h1>
    <p class="text-muted small mb-4">Please choose a new, strong password with at least 8 characters including letters and numbers.</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <x-form-token />
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email Address <span class="req">*</span></label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   value="{{ old('email', $email) }}" 
                   class="form-control @error('email') is-invalid @enderror" 
                   required>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password <span class="req">*</span></label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="At least 8 characters" 
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
                   placeholder="Re-enter new password" 
                   required>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-shield-check"></i> Reset Password & Sign In
        </button>
    </form>
</div>
@endsection
