@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Profil Saya</h5>
                    @if(Route::has('settings.account'))
                        <a href="{{ route('settings.account') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-cog me-1"></i> Pengaturan Akun
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @php($user = $user ?? auth()->user())

                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:64px; height:64px;">
                            <i class="fas fa-user fa-lg"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-1">{{ $user->name }}</h4>
                            <div class="text-muted">{{ $user->email }}</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="mb-2 fw-semibold">Peran</div>
                                <div>
                                    @php($roles = method_exists($user, 'roles') ? $user->roles->pluck('name')->all() : [])
                                    @forelse($roles as $role)
                                        <span class="badge bg-secondary me-1">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                    @empty
                                        <span class="text-muted">Tidak ada peran</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="mb-2 fw-semibold">Institusi</div>
                                @php($institution = $user->institution ?? $user->instansi ?? null)
                                @if($institution)
                                    <div>{{ $institution->name ?? $institution->nama ?? '—' }}</div>
                                    @if(Route::has('institution.profile'))
                                        <a href="{{ route('institution.profile') }}" class="small">Lihat profil institusi</a>
                                    @endif
                                @else
                                    <div class="text-muted">Belum terdaftar pada institusi</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <div class="text-muted">
                            Terakhir login: {{ optional($user->last_login_at)->format('Y-m-d H:i') ?? '—' }}
                        </div>
                        @if(Route::has('settings.account'))
                            <a class="btn btn-outline-primary" href="{{ route('settings.account') }}">
                                <i class="fas fa-edit me-1"></i> Edit Profil
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
