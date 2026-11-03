@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="card p-4 shadow-sm border">
    <h1 class="h4 fw-bold mb-1">Reset Password</h1>
    <p class="text-muted small mb-4">Enter your registered email address and we will send you a password reset link.</p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <x-form-token />

        <div class="mb-3">
            <label for="email" class="form-label">Registered Email <span class="req">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       class="form-control @error('email') is-invalid @enderror" 
                       placeholder="you@kisan.local" 
                       required 
                       autofocus>
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
            <i class="bi bi-send"></i> Send Reset Link
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="small text-decoration-none">
                <i class="bi bi-arrow-left"></i> Back to Sign In
            </a>
        </div>
    </form>
</div>
@endsection
