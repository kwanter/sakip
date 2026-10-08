@extends('layouts.modern')

@section('title', 'Tidak Diizinkan')

@section('page-title', 'Akses Ditolak')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-ban fa-3x" style="color: var(--danger);"></i>
                    </div>
                    <h1 class="page-header-title mb-2">Akses Ditolak (403)</h1>
                    <p class="mb-2">Maaf, Anda tidak memiliki hak untuk mengakses halaman ini.</p>
                    @auth
                        <p>Jika Anda merasa ini kesalahan, hubungi administrator untuk mendapatkan akses yang sesuai.</p>
                    @else
                        <p>Silakan <a href="{{ route('login') }}">login</a> terlebih dahulu.</p>
                    @endauth
                    <a href="{{ url('/') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-home"></i>
                        <span class="ms-1">Kembali ke Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
