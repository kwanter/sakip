@extends('layouts.modern')

@section('title', 'Verify Email')

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
        max-width: 560px;
    }
    .auth-card .card-body {
        padding: 32px;
    }
    .auth-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 16px;
    }
</style>

<div class="auth-wrap">
    <div class="card auth-card">
        <div class="card-body">
            <h1 class="auth-title">{{ __('Verify Your Email Address') }}</h1>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success" role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            <p>{{ __('Before proceeding, please check your email for a verification link.') }}</p>
            <p class="mb-0">{{ __('If you did not receive the email') }},
            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
            </form>
            </p>
        </div>
    </div>
</div>
@endsection
