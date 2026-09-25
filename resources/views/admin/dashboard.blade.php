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

    <!-- Triage region: the period envelope, the Cakupan Instansi and the three queue figures -->
    <section class="mb-4" data-triage-region data-triage-period="{{ $summary->period->key }}" data-triage-scope="{{ $summary->scope->value }}">
        <div class="mb-3">
            <span class="badge text-bg-light border" data-triage-scope-label>Cakupan: {{ $summary->scopeLabel }}</span>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="triage-period" class="form-label">Periode Pelaporan</label>
                        <select name="period" id="triage-period" class="form-select">
                            @foreach (\App\Support\ReportingPeriod::KEYS as $key)
                            <option value="{{ $key }}" @selected($key === $summary->period->key)>{{ \App\Support\ReportingPeriod::fromKey($key)->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($summary->verificationCount + $summary->assessmentCount + $summary->reportCount > 0)
        <div class="card card-accent-warning mb-4" data-triage-attention>
            <div class="card-body d-flex flex-wrap gap-4">
                @foreach ([
                    ['handle' => 'verification', 'count' => $summary->verificationCount, 'subject' => 'data kinerja', 'action' => 'Tinjau Antrean Verifikasi', 'url' => $summary->verificationUrl],
                    ['handle' => 'assessment', 'count' => $summary->assessmentCount, 'subject' => 'asesmen', 'action' => 'Tinjau Antrean Asesmen', 'url' => $summary->assessmentUrl],
                    ['handle' => 'report', 'count' => $summary->reportCount, 'subject' => 'laporan', 'action' => 'Tinjau Antrean Laporan', 'url' => $summary->reportUrl],
                ] as $signal)
                @if ($signal['count'] > 0)
                <div class="d-flex align-items-center gap-2" data-triage-signal="{{ $signal['handle'] }}">
                    <i class="fas fa-clock text-warning" aria-hidden="true"></i>
                    <span><strong>{{ number_format($signal['count']) }}</strong> {{ $signal['subject'] }} menunggu tindakan</span>
                    <a href="{{ $signal['url'] }}" class="btn btn-sm btn-warning">{{ $signal['action'] }}</a>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @else
        <div class="card mb-4" data-triage-empty>
            <div class="card-body">Belum ada pekerjaan tertunda pada periode ini.</div>
        </div>
        @endif

        <div class="row">
            @foreach ([
                ['handle' => 'verification', 'name' => 'Antrean Verifikasi', 'count' => $summary->verificationCount, 'basis' => $summary->periodLabel, 'url' => $summary->verificationUrl],
                ['handle' => 'assessment', 'name' => 'Antrean Asesmen', 'count' => $summary->assessmentCount, 'basis' => \App\Support\AdminTriageSummary::PERIOD_INDEPENDENT_BASIS_LABEL, 'url' => $summary->assessmentUrl],
                ['handle' => 'report', 'name' => 'Antrean Laporan', 'count' => $summary->reportCount, 'basis' => \App\Support\AdminTriageSummary::PERIOD_INDEPENDENT_BASIS_LABEL, 'url' => $summary->reportUrl],
            ] as $figure)
            <div class="col-md-4">
                <a href="{{ $figure['url'] }}" class="d-block text-decoration-none" data-triage-figure="{{ $figure['handle'] }}">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon primary">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ number_format($figure['count']) }}</div>
                        <div class="stat-label">{{ $figure['name'] }}</div>
                        <small class="text-muted"@if ($figure['handle'] === 'verification') data-triage-period-label @endif>{{ $figure['basis'] }}</small>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </section>

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
