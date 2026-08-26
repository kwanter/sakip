@extends('layouts.modern')

@section('title', 'Penilaian Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Penilaian Kinerja</h1>
                <p class="page-header-subtitle">Evaluasi dan penilaian kinerja instansi</p>
            </div>
            <div class="page-header-actions">
                @can('create', App\Models\Assessment::class)
                <a href="{{ route('sakip.assessments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Buat Penilaian</span>
                </a>
                @endcan
                <button class="btn btn-success" onclick="batchAssess()">
                    <i class="fas fa-tasks"></i>
                    <span class="ms-1">Penilaian Massal</span>
                </button>
                <button class="btn btn-info" onclick="exportAssessments()">
                    <i class="fas fa-file-export"></i>
                    <span class="ms-1">Export</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if(isset($statistics))
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon primary">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['total'] ?? 0 }}</div>
                <div class="stat-label">Total Penilaian</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['completed'] ?? 0 }}</div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['pending'] ?? 0 }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon info">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($statistics['avg_score'] ?? 0, 1) }}</div>
                <div class="stat-label">Rata-rata Skor</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table Card -->
    <div class="card">
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle alert-icon"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Instansi</th>
                            <th>Periode</th>
                            <th>Skor</th>
                            <th>Grade</th>
                            <th>Penilai</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($assessments ?? []) as $assessment)
                        <tr>
                            <td>
                                <strong>{{ $assessment->instansi->nama_instansi ?? '-' }}</strong>
                            </td>
                            <td>{{ $assessment->period ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar @if($assessment->score >= 90) bg-success @elseif($assessment->score >= 75) bg-info @elseif($assessment->score >= 60) bg-warning @else bg-danger @endif"
                                                 role="progressbar" style="width: {{ $assessment->score ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                    <span class="ms-2 fw-bold">{{ number_format($assessment->score ?? 0, 1) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge @if($assessment->grade === 'A') bg-success @elseif($assessment->grade === 'B') bg-info @elseif($assessment->grade === 'C') bg-warning @else bg-danger @endif fs-6">
                                    {{ $assessment->grade ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $assessment->assessor->name ?? '-' }}</td>
                            <td>
                                <span class="badge @if($assessment->status === 'completed') bg-success @elseif($assessment->status === 'in_progress') bg-info @else bg-secondary @endif">
                                    {{ ucfirst(str_replace('_', ' ', $assessment->status ?? 'pending')) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.assessments.show', $assessment) }}" class="btn btn-outline-primary" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($assessment->status !== 'completed')
                                    @can('update', $assessment)
                                    <a href="{{ route('sakip.assessments.edit', $assessment) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list text-muted"></i>
                                    <p class="mb-0">Tidak ada penilaian</p>
                                    <small class="text-muted">Silakan buat penilaian baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($assessments) && $assessments->hasPages())
            <div class="mt-3">
                {{ $assessments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function batchAssess() {
    alert('Fitur penilaian massal akan ditambahkan segera');
}

function exportAssessments() {
    alert('Fitur export akan ditambahkan segera');
}
</script>
@endsection
