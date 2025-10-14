@extends('layouts.app')

@section('title', 'Tidak Diizinkan')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-left-danger mb-4">
                <div class="card-header">
                    <i class="fas fa-ban"></i> Akses Ditolak (403)
                </div>
                <div class="card-body">
                    <p>Maaf, Anda tidak memiliki hak untuk mengakses halaman ini.</p>
                    @auth
                        <p>Jika Anda merasa ini kesalahan, hubungi administrator untuk mendapatkan akses yang sesuai.</p>
                    @else
                        <p>Silakan <a href="{{ route('login') }}">login</a> terlebih dahulu.</p>
                    @endauth
                    <a href="{{ url('/') }}" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection