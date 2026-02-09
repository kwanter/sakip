@extends('layouts.modern')

@section('title', 'Laporan SAKIP')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Laporan SAKIP</h1>
                <p class="page-header-subtitle">Kelola dan pantau laporan kinerja instansi</p>
            </div>
            <div class="page-header-actions">
                @can('create', App\Models\Report::class)
                <a href="{{ route('sakip.reports.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Buat Laporan</span>
                </a>
                @endcan
                <button class="btn btn-secondary" onclick="showExportModal()">
                    <i class="fas fa-download"></i>
                    <span class="ms-1">Download</span>
                </button>
                <button class="btn btn-info" onclick="showTemplateModal()">
                    <i class="fas fa-file-contract"></i>
                    <span class="ms-1">Template</span>
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
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['total'] ?? 0 }}</div>
                <div class="stat-label">Total Laporan</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['approved'] ?? 0 }}</div>
                <div class="stat-label">Disetujui</div>
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
                <div class="stat-label">Menunggu</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon info">
                        <i class="fas fa-calendar"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['this_year'] ?? 0 }}</div>
                <div class="stat-label">Tahun Ini</div>
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
                            <th>Judul Laporan</th>
                            <th>Instansi</th>
                            <th>Periode</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($reports ?? []) as $report)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $report->title }}</strong>
                                    @if($report->description)
                                    <br><small class="text-muted">{{ Str::limit($report->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $report->instansi->nama_instansi ?? '-' }}</td>
                            <td>{{ ucfirst($report->period ?? '-') }}</td>
                            <td>{{ $report->year ?? '-' }}</td>
                            <td>
                                <span class="badge @if($report->status === 'approved') bg-success @elseif($report->status === 'submitted') bg-info @else bg-secondary @endif">
                                    {{ ucfirst(str_replace('_', ' ', $report->status ?? 'draft')) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $report->created_at?->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.reports.show', $report) }}" class="btn btn-outline-primary" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $report)
                                    <a href="{{ route('sakip.reports.edit', $report) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    <a href="{{ route('sakip.reports.download', $report) }}" class="btn btn-outline-info" title="Download" onclick="event.preventDefault(); downloadReport({{ $report->id }})">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if($report->status !== 'approved')
                                    @can('approve', $report)
                                    <form action="{{ route('sakip.reports.approve', $report) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Setujui" onclick="return confirm('Setujui laporan ini?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-alt text-muted"></i>
                                    <p class="mb-0">Tidak ada laporan</p>
                                    <small class="text-muted">Silakan buat laporan baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($reports) && $reports->hasPages())
            <div class="mt-3">
                {{ $reports->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function showExportModal() {
    alert('Modal export akan ditambahkan segera');
}

function showTemplateModal() {
    alert('Modal template akan ditambahkan segera');
}

function downloadReport(id) {
    window.location.href = `/sakip/reports/${id}/download`;
}
</script>
@endsection
