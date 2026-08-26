@extends('layouts.modern')

@section('title', 'Indikator Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Indikator Kinerja</h1>
                <p class="page-header-subtitle">Kelola indikator kinerja instansi</p>
            </div>
            <div class="page-header-actions">
                @can('create', App\Models\PerformanceIndicator::class)
                <a href="{{ route('sakip.indicators.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Indikator</span>
                </a>
                @endcan
                <button class="btn btn-secondary" onclick="showImportModal()">
                    <i class="fas fa-file-import"></i>
                    <span class="ms-1">Import</span>
                </button>
                <button class="btn btn-info" onclick="exportIndicators()">
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
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['total'] ?? 0 }}</div>
                <div class="stat-label">Total Indikator</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['mandatory'] ?? 0 }}</div>
                <div class="stat-label">Indikator Wajib</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon info">
                        <i class="fas fa-bullseye"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['with_targets'] ?? 0 }}</div>
                <div class="stat-label">Dengan Target</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['with_data'] ?? 0 }}</div>
                <div class="stat-label">Dengan Data</div>
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
                            <th>Kode</th>
                            <th>Nama Indikator</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Frekuensi</th>
                            <th>Instansi</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicators as $indicator)
                        <tr>
                            <td><span class="badge bg-light text-dark">{{ $indicator->code }}</span></td>
                            <td>
                                <a href="{{ route('sakip.indicators.show', $indicator) }}" class="text-decoration-none fw-bold">
                                    {{ $indicator->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ ucfirst($indicator->category) }}
                                </span>
                            </td>
                            <td>{{ $indicator->measurement_unit }}</td>
                            <td>{{ ucfirst($indicator->frequency) }}</td>
                            <td>{{ $indicator->instansi->nama_instansi ?? '-' }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.indicators.show', $indicator) }}" class="btn btn-outline-primary" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $indicator)
                                    <a href="{{ route('sakip.indicators.edit', $indicator) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $indicator)
                                    <form action="{{ route('sakip.indicators.destroy', $indicator) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus indikator ini?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-inbox text-muted"></i>
                                    <p class="mb-0">Tidak ada data indikator kinerja</p>
                                    <small class="text-muted">Silakan tambahkan indikator kinerja baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($indicators->hasPages())
            <div class="mt-3">
                {{ $indicators->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function showImportModal() {
    alert('Fitur import akan ditambahkan segera');
}

function exportIndicators() {
    alert('Fitur export akan ditambahkan segera');
}
</script>
@endsection
