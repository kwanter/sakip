@extends('layouts.modern')

@section('title', 'Panel Admin')

@section('page-title', 'Panel Admin')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Panel Admin</h1>
                <p class="page-header-subtitle">Antrean verifikasi dan aktivitas sistem</p>
            </div>
        </div>
    </div>

    <!-- Attention strip: only when performance data awaits verification -->
    @if($pendingDataCount > 0)
    <div class="card card-accent-warning mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-clock text-warning fa-lg" aria-hidden="true"></i>
                <span>
                    <strong>{{ number_format($pendingDataCount) }}</strong> data kinerja menunggu verifikasi
                </span>
            </div>
            <a href="{{ route('sakip.data-collection.index', ['validation_status' => 'submitted']) }}" class="btn btn-warning">
                Tinjau Sekarang
            </a>
        </div>
    </div>
    @endif

    <!-- Stat Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('sakip.data-collection.index', ['validation_status' => 'submitted']) }}" class="d-block text-decoration-none">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon warning">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ number_format($pendingDataCount) }}</div>
                    <div class="stat-label">Menunggu Verifikasi</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                    <div class="stat-value">{{ number_format($validatedCount) }}</div>
                    <div class="stat-label">Tervalidasi ({{ $currentPeriodLabel }})</div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('sakip.indicators.index') }}" class="d-block text-decoration-none">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon primary">
                            <i class="fas fa-bullseye"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ number_format($indicatorCount) }}</div>
                    <div class="stat-label">Indikator Kinerja</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon info">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
                    <div class="stat-value">{{ number_format($recentLogins) }}</div>
                    <div class="stat-label">Aktivitas Login (7 Hari)</div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row">
        <!-- Recent Audit Logs -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Aktivitas Terbaru</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Pengguna</th>
                                    <th scope="col">Aktivitas</th>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">Alamat IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                <tr>
                                    <td>
                                        @if($log->user)
                                            <strong>{{ $log->user->name }}</strong><br>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                            <em>Sistem</em>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-secondary">{{ $log->action }}</span>
                                    </td>
                                    <td>
                                        <small title="{{ $log->created_at->locale('id')->translatedFormat('d F Y H:i') }}">{{ $log->created_at->locale('id')->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $log->ip_address }}</code>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox text-muted"></i>
                                            <p class="mb-0">Belum ada aktivitas tercatat</p>
                                            <small class="text-muted">Aktivitas pengguna akan tampil di sini</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.audit-logs') }}" class="btn btn-sm btn-primary">
                            Lihat Semua Aktivitas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.users.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus fa-fw me-2"></i>
                            Buat Pengguna Baru
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-users fa-fw me-2"></i>
                            Kelola Pengguna
                        </a>
                        <a href="{{ route('admin.audit-logs') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-history fa-fw me-2"></i>
                            Log Audit
                        </a>
                        @can('manage-settings')
                        <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog fa-fw me-2"></i>
                            Pengaturan Sistem
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
