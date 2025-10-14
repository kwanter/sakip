@extends('layouts.app')

@section('title', 'Daftar Program')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Program</h1>
        @anyrole('admin,manager')
<a href="{{ route('program.create') }}" class="btn btn-primary btn-sm shadow-sm">
    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Program
</a>
@endanyrole
    </div>

    <!-- Filter and Search -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('program.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="instansi_id">Filter Instansi:</label>
                            <select name="instansi_id" id="instansi_id" class="form-control">
                                <option value="">Semua Instansi</option>
                                @foreach($instansis as $instansi)
                                    <option value="{{ $instansi->id }}" {{ request('instansi_id') == $instansi->id ? 'selected' : '' }}>
                                        {{ $instansi->nama_instansi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Filter Status:</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Pencarian:</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Cari nama atau kode program..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('program.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Programs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Program</h6>
        </div>
        <div class="card-body">
            @if($programs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Program</th>
                                <th>Instansi</th>
                                <th>Anggaran</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th>Kegiatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($programs as $index => $program)
                            <tr>
                                <td>{{ $programs->firstItem() + $index }}</td>
                                <td>{{ $program->kode_program }}</td>
                                <td>
                                    <strong>{{ $program->nama_program }}</strong>
                                    @if($program->deskripsi)
                                        <br><small class="text-muted">{{ Str::limit($program->deskripsi, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $program->instansi->nama_instansi }}</span>
                                </td>
                                <td>Rp {{ number_format($program->anggaran, 0, ',', '.') }}</td>
                                <td>{{ $program->tahun }}</td>
                                <td>
                                    @if($program->status == 'aktif')
                                        <span class="badge badge-success">Aktif</span>
                                    @elseif($program->status == 'selesai')
                                        <span class="badge badge-primary">Selesai</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $program->kegiatans_count }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('program.show', $program) }}" 
                                           class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('program.edit', $program) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deleteProgram({{ $program->id }})" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <p class="text-muted">
                            Menampilkan {{ $programs->firstItem() }} sampai {{ $programs->lastItem() }} 
                            dari {{ $programs->total() }} program
                        </p>
                    </div>
                    <div>
                        {{ $programs->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-folder-open fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">Tidak ada data program</h5>
                    <p class="text-gray-400">Belum ada program yang ditambahkan atau tidak ada yang sesuai dengan filter.</p>
                    <a href="{{ route('program.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Program Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus program ini?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua kegiatan terkait.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteProgram(id) {
    $('#deleteForm').attr('action', '/program/' + id);
    $('#deleteModal').modal('show');
}

$(document).ready(function() {
    // Auto submit form when filter changes
    $('#instansi_id, #status').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush