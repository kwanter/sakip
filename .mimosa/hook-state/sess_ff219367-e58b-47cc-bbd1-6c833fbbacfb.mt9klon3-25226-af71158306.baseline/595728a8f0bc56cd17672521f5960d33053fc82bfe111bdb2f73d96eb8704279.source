@extends('layouts.app')

@section('title', 'Daftar Kegiatan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks"></i> Daftar Kegiatan
        </h1>
        <div>
            @can('create', App\Models\Kegiatan::class)
            <a href="{{ route('sakip.kegiatan.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kegiatan
            </a>
            @endcan
        </div>
    </div>

    @if($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if($message = Session::get('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Kegiatan</h6>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search">Cari Kegiatan</label>
                            <input type="text" id="search" name="search" class="form-control form-control-sm"
                                   placeholder="Cari berdasarkan nama atau kode kegiatan"
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm btn-block">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Kode Kegiatan</th>
                            <th>Nama Kegiatan</th>
                            <th>Program</th>
                            <th width="100">Anggaran</th>
                            <th width="80">Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kegiatans as $key => $kegiatan)
                        <tr>
                            <td>{{ $kegiatans->firstItem() + $key }}</td>
                            <td>
                                <strong>{{ $kegiatan->kode_kegiatan }}</strong>
                            </td>
                            <td>{{ $kegiatan->nama_kegiatan }}</td>
                            <td>
                                @if($kegiatan->program)
                                    <a href="{{ route('sakip.program.show', $kegiatan->program) }}">
                                        {{ $kegiatan->program->nama_program }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <strong>Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($kegiatan->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @elseif($kegiatan->status == 'selesai')
                                    <span class="badge badge-info">Selesai</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sakip.kegiatan.show', $kegiatan) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $kegiatan)
                                <a href="{{ route('sakip.kegiatan.edit', $kegiatan) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('delete', $kegiatan)
                                <form action="{{ route('sakip.kegiatan.destroy', $kegiatan) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox"></i> Tidak ada data kegiatan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $kegiatans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
