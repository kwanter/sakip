@extends('layouts.modern')

@section('title', 'Daftar Sasaran Strategis')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Daftar Sasaran Strategis</h1>
                <p class="page-header-subtitle">Kelola sasaran strategis instansi</p>
            </div>
            <div class="page-header-actions">
                @can('create', App\Models\SasaranStrategis::class)
                <a href="{{ route('sakip.sasaran-strategis.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Sasaran</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sakip.sasaran-strategis.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, kode sasaran..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Instansi</label>
                    <select name="instansi_id" class="form-select">
                        <option value="">Semua Instansi</option>
                        @foreach($instansis ?? [] as $inst)
                            <option value="{{ $inst->id }}" {{ request('instansi_id') == $inst->id ? 'selected' : '' }}>
                                {{ $inst->nama_instansi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-search"></i>
                        <span class="ms-1">Cari</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Sasaran Strategis</th>
                            <th>Instansi</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sasaranStrategis ?? [] as $key => $sasaran)
                        <tr>
                            <td>{{ $sasaranStrategis->firstItem() + $key }}</td>
                            <td><span class="badge bg-light text-dark">{{ $sasaran->kode_sasaran_strategis }}</span></td>
                            <td><strong>{{ $sasaran->nama_strategis }}</strong></td>
                            <td>{{ $sasaran->instansi->nama_instansi ?? '-' }}</td>
                            <td>
                                @if($sasaran->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.sasaran-strategis.show', $sasaran) }}" class="btn btn-outline-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $sasaran)
                                    <a href="{{ route('sakip.sasaran-strategis.edit', $sasaran) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $sasaran)
                                    <form action="{{ route('sakip.sasaran-strategis.destroy', $sasaran) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sasaran strategis ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-bullseye text-muted"></i>
                                    <p class="mb-0">Tidak ada data sasaran strategis</p>
                                    <small class="text-muted">Silakan tambahkan sasaran strategis baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($sasaranStrategis) && $sasaranStrategis->hasPages())
            <div class="mt-3">
                {{ $sasaranStrategis->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
