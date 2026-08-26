@extends('layouts.modern')

@section('title', 'Daftar Program')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Daftar Program</h1>
                <p class="page-header-subtitle">Kelola program instansi pemerintahan</p>
            </div>
            <div class="page-header-actions">
                @can('create', App\Models\Program::class)
                <a href="{{ route('sakip.program.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Program</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sakip.program.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, kode program..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
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
                    <label class="form-label">Sasaran Strategis</label>
                    <select name="sasaran_strategis_id" class="form-select">
                        <option value="">Semua Sasaran</option>
                        @foreach($sasaranStrategis ?? [] as $sasaran)
                            <option value="{{ $sasaran->id }}" {{ request('sasaran_strategis_id') == $sasaran->id ? 'selected' : '' }}>
                                {{ $sasaran->nama_strategis }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                            <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100" title="Cari">
                        <i class="fas fa-search"></i>
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
                            <th>Nama Program</th>
                            <th>Instansi</th>
                            <th>Sasaran Strategis</th>
                            <th>Tahun</th>
                            <th>Anggaran</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs ?? [] as $key => $program)
                        <tr>
                            <td>{{ $programs->firstItem() + $key }}</td>
                            <td><span class="badge bg-light text-dark">{{ $program->kode_program }}</span></td>
                            <td><strong>{{ $program->nama_program }}</strong></td>
                            <td>{{ $program->instansi->nama_instansi ?? '-' }}</td>
                            <td>{{ $program->sasaranStrategis->nama_strategis ?? '-' }}</td>
                            <td>{{ $program->tahun }}</td>
                            <td>Rp {{ number_format($program->anggaran ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @if($program->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @elseif($program->status == 'selesai')
                                    <span class="badge bg-info">Selesai</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.program.show', $program) }}" class="btn btn-outline-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $program)
                                    <a href="{{ route('sakip.program.edit', $program) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $program)
                                    <form action="{{ route('sakip.program.destroy', $program) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program ini?')">
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
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-layer-group text-muted"></i>
                                    <p class="mb-0">Tidak ada data program</p>
                                    <small class="text-muted">Silakan tambahkan program baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($programs) && $programs->hasPages())
            <div class="mt-3">
                {{ $programs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
