@extends('layouts.modern')

@section('title', 'Masuk')

@section('content')
{{-- Page-scoped auth styles (Genesis tokens only) --}}
<style>
    .auth-wrap {
        display: flex;
        justify-content: center;
        padding: 48px 16px;
    }
    .auth-card {
        width: 100%;
        max-width: 420px;
    }
    .auth-card .card-body {
        padding: 32px;
    }
    .auth-brand {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-lg);
        background: var(--primary-50);
        color: var(--primary-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 16px;
    }
    .auth-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 4px;
    }
    .auth-subtitle {
        font-size: 13px;
        color: var(--text-secondary);
        margin: 0 0 24px;
    }
    .auth-card .form-control {
        border-radius: var(--radius-md);
    }
    .auth-card .form-control:focus {
        border-color: var(--primary-500);
        box-shadow: var(--focus-ring);
    }
</style>

<div class="auth-wrap">
    <div class="card auth-card">
        <div class="card-body">
            <div class="auth-brand" aria-hidden="true">
                <i class="fas fa-chart-pie"></i>
            </div>
            <h1 class="auth-title">Masuk</h1>
            <p class="auth-subtitle">Sistem Akuntabilitas Kinerja Instansi Pemerintah</p>
            <form method="POST" action="{{ route('auth.login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Masuk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
