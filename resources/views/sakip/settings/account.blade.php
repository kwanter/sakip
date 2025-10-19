@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Pengaturan Akun</h5>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-user me-1"></i> Profil Saya
                    </a>
                </div>
                <div class="card-body">
                    @php($user = $user ?? auth()->user())

                    <div class="mb-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="ms-3">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="text-muted">{{ $user->email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <p class="mb-2 text-muted">Halaman ini untuk meninjau dan memperbarui pengaturan akun Anda.</p>
                        <ul class="mb-0 small text-muted">
                            <li>Nama dan email dapat diperbarui di versi lengkap.</li>
                            <li>Kata sandi dan preferensi notifikasi akan tersedia di update berikutnya.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
