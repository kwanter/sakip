@extends('layouts.app')

@section('title', 'Daftar Kegiatan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Kegiatan</h1>
        <a href="{{ route('kegiatan.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Kegiatan
        </a>
    </div>

    <!-- Filter and Search -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('kegiatan.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="program_id">Filter Program:</label>
                            <select name="program_id" id="program_id" class="form-control">
                                <option value="">Semua Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->nama_program }} ({{ $program->instansi->nama_instansi }})
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
                                <option value="berjalan" {{ request('status') == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="tunda" {{ request('status') == 'tunda' ? 'selected' : '' }}>Tunda</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Pencarian:</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Cari nama, kode, atau penanggung jawab..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Kegiatan</h6>
        </div>
        <div class="card-body">
            @if($kegiatans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Kegiatan</th>
                                <th>Program</th>
                                <th>Anggaran</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th>Penanggung Jawab</th>
                                <th>Indikator</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kegiatans as $index => $kegiatan)
                            <tr>
                                <td>{{ $kegiatans->firstItem() + $index }}</td>
                                <td>{{ $kegiatan->kode_kegiatan }}</td>
                                <td>
                                    <strong>{{ $kegiatan->nama_kegiatan }}</strong>
                                    @if($kegiatan->deskripsi)
                                        <br><small class="text-muted">{{ Str::limit($kegiatan->deskripsi, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $kegiatan->program->nama_program }}</span>
                                    <br><small class="text-muted">{{ $kegiatan->program->instansi->nama_instansi }}</small>
                                </td>
                                <td>Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</td>
                                <td>
                                    {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->format('d/m/Y') : '-' }}<br>
                                    <small class="text-muted">s/d {{ $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->format('d/m/Y') : '-' }}</small>
                                </td>
                                <td>
                                    @if($kegiatan->status == 'berjalan')
                                    <span class="badge badge-success">Berjalan</span>
                                @elseif($kegiatan->status == 'selesai')
                                    <span class="badge badge-primary">Selesai</span>
                                @elseif($kegiatan->status == 'tunda')
                                    <span class="badge badge-secondary">Tunda</span>
                                @else
                                    <span class="badge badge-warning">Draft</span>
                                @endif
                                </td>
                                <td>{{ $kegiatan->penanggung_jawab }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $kegiatan->indikator_kinerjas_count }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('kegiatan.show', $kegiatan) }}" 
                                           class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('kegiatan.edit', $kegiatan) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deleteKegiatan({{ $kegiatan->id }})" title="Hapus">
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
                            Menampilkan {{ $kegiatans->firstItem() }} sampai {{ $kegiatans->lastItem() }} 
                            dari {{ $kegiatans->total() }} kegiatan
                        </p>
                    </div>
                    <div>
                        {{ $kegiatans->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-tasks fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">Tidak ada data kegiatan</h5>
                    <p class="text-gray-400">Belum ada kegiatan yang ditambahkan atau tidak ada yang sesuai dengan filter.</p>
                    <a href="{{ route('kegiatan.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Kegiatan Pertama
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
                <p>Apakah Anda yakin ingin menghapus kegiatan ini?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua indikator kinerja terkait.</small></p>
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
function deleteKegiatan(id) {
    $('#deleteForm').attr('action', '/kegiatan/' + id);
    $('#deleteModal').modal('show');
}

$(document).ready(function() {
    // Auto submit form when filter changes
    $('#program_id, #status').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush