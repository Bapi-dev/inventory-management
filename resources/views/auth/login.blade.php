@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="login-card mx-auto">
    <div class="p-4 p-sm-5 text-center bg-light border-bottom">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 mb-3 shadow" style="width: 54px; height: 54px;">
            <i class="bi bi-handbag-fill fs-3"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">BagSmart Wholesale</h4>
        <p class="text-muted small mb-0">Manufacturing & Wholesale Inventory System</p>
    </div>

    <div class="p-4 p-sm-5">
        @if(session('info'))
            <div class="alert alert-info py-2 px-3 small mb-3">
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="{{ old('email', 'admin@bagswholesale.com') }}" required autofocus placeholder="admin@bagswholesale.com">
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label small fw-semibold text-secondary mb-0">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required value="password123" placeholder="••••••••">
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                <label class="form-check-label small text-muted" for="remember">Remember me on this browser</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                Sign In to Dashboard <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="mt-4 p-3 bg-light rounded text-center small text-muted border">
            <div class="fw-semibold text-dark mb-1"><i class="bi bi-shield-lock-fill text-primary me-1"></i> Demo Credentials:</div>
            <div>Email: <code class="text-primary">admin@bagswholesale.com</code></div>
            <div>Password: <code class="text-primary">password123</code></div>
        </div>
    </div>
</div>
@endsection
