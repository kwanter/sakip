@extends('layouts.modern')

@section('title', 'Pengumpulan Data Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Pengumpulan Data Kinerja</h1>
                <p class="page-header-subtitle">Kelola dan input data kinerja periode berjalan</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('sakip.data-collection.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Input Data Baru</span>
                </a>
                <a href="{{ route('sakip.data-collection.import') }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i>
                    <span class="ms-1">Import Excel</span>
                </a>
                <button class="btn btn-info" onclick="exportData()">
                    <i class="fas fa-file-export"></i>
                    <span class="ms-1">Export</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sakip.data-collection.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach(range(date('Y'), date('Y') - 5) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Instansi</label>
                    <select name="instansi" class="form-select">
                        <option value="">Semua Instansi</option>
                        @foreach($instansiList ?? [] as $instansi)
                        <option value="{{ $instansi->id }}" {{ request('instansi') == $instansi->id ? 'selected' : '' }}>{{ $instansi->nama_instansi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="validated" {{ request('status') == 'validated' ? 'selected' : '' }}>Validated</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter"></i>
                        <span class="ms-1">Filter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
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
                            <th>Indikator</th>
                            <th>Periode</th>
                            <th>Target</th>
                            <th>Capaian</th>
                            <th>Achievement</th>
                            <th>Instansi</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($performanceData ?? []) as $data)
                        <tr>
                            <td>
                                <strong>{{ $data->indicator->name ?? '-' }}</strong>
                                <br><small class="text-muted">{{ $data->indicator->code ?? '' }}</small>
                            </td>
                            <td>{{ $data->period ?? '-' }}</td>
                            <td>{{ number_format($data->target ?? 0, 2) }}</td>
                            <td>{{ number_format($data->actual ?? 0, 2) }}</td>
                            <td>
                                @php
                                    $achievement = ($data->target ?? 0) > 0 ? (($data->actual ?? 0) / $data->target * 100) : 0;
                                @endphp
                                <span class="badge @if($achievement >= 100) bg-success @elseif($achievement >= 80) bg-info @elseif($achievement >= 60) bg-warning @else bg-danger @endif">
                                    {{ number_format($achievement, 1) }}%
                                </span>
                            </td>
                            <td>{{ $data->instansi->nama_instansi ?? '-' }}</td>
                            <td>
                                <span class="badge @if($data->status === 'approved') bg-success @elseif($data->status === 'validated') bg-info @else bg-warning @endif">
                                    {{ ucfirst($data->status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.data-collection.show', $data) }}" class="btn btn-outline-primary" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $data)
                                    <a href="{{ route('sakip.data-collection.edit', $data) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-database text-muted"></i>
                                    <p class="mb-0">Tidak ada data kinerja</p>
                                    <small class="text-muted">Silakan input data kinerja baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($performanceData) && $performanceData->hasPages())
            <div class="mt-3">
                {{ $performanceData->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function exportData() {
    alert('Fitur export akan ditambahkan segera');
}
</script>
@endsection
