@extends('layouts.modern')

@section('title', 'Daftar Instansi')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Daftar Instansi</h1>
                <p class="page-header-subtitle">Kelola data instansi pemerintahan</p>
            </div>
            <div class="page-header-actions">
                @can('create', App\Models\Instansi::class)
                <a href="{{ route('sakip.instansi.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Instansi</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sakip.instansi.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, kode, atau kepala instansi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
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
                            <th>Nama Instansi</th>
                            <th>Kepala Instansi</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instansis ?? [] as $key => $instansi)
                        <tr>
                            <td>{{ $instansis->firstItem() + $key }}</td>
                            <td><span class="badge bg-light text-dark">{{ $instansi->kode_instansi }}</span></td>
                            <td><strong>{{ $instansi->nama_instansi }}</strong></td>
                            <td>{{ $instansi->kepala_instansi ?? '-' }}</td>
                            <td>{{ $instansi->telepon ?? '-' }}</td>
                            <td>
                                @if($instansi->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.instansi.show', $instansi) }}" class="btn btn-outline-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $instansi)
                                    <a href="{{ route('sakip.instansi.edit', $instansi) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $instansi)
                                    <form action="{{ route('sakip.instansi.destroy', $instansi) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus instansi ini?')">
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
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-building text-muted"></i>
                                    <p class="mb-0">Tidak ada data instansi</p>
                                    <small class="text-muted">Silakan tambahkan data instansi baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($instansis) && $instansis->hasPages())
            <div class="mt-3">
                {{ $instansis->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
