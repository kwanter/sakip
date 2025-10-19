@extends('layouts.app')

@section('title', 'Dashboard SAKIP')

@section('content')
<div class="container py-4" data-sakip-dashboard>
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Dashboard SAKIP {{ $currentYear ?? '' }}</h1>
            @if(session('error'))
                <div class="alert alert-danger mt-2">{{ session('error') }}</div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Indikator</span>
                        <strong>{{ $dashboardData['total_indicators'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Data</span>
                        <strong>{{ $dashboardData['total_data_points'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Penilaian</span>
                        <strong>{{ $dashboardData['total_assessments'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Laporan</span>
                        <strong>{{ $dashboardData['total_reports'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Aktivitas Terbaru</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse(($recentActivities ?? []) as $activity)
                            <li class="list-group-item">{{ $activity['description'] ?? 'Aktivitas' }}</li>
                        @empty
                            <li class="list-group-item text-muted">Belum ada aktivitas terbaru.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Peringatan</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse(($alerts ?? []) as $alert)
                            <li class="list-group-item">
                                <span class="badge bg-{{ $alert['type'] ?? 'info' }}">&nbsp;</span>
                                {{ $alert['message'] ?? 'Peringatan' }}
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Tidak ada peringatan saat ini.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">Aksi Cepat</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @forelse(($quickActions ?? []) as $action)
                            <a href="{{ $action['link'] ?? '#' }}" class="btn btn-primary btn-sm">{{ $action['label'] ?? 'Aksi' }}</a>
                        @empty
                            <span class="text-muted">Tidak ada aksi cepat tersedia.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection