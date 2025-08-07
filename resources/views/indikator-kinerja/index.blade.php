@extends('layouts.app')

@section('title', 'Daftar Indikator Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Indikator Kinerja</h1>
        <a href="{{ route('indikator-kinerja.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Indikator
        </a>
    </div>

    <!-- Filter and Search -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('indikator-kinerja.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kegiatan_id">Filter Kegiatan:</label>
                            <select name="kegiatan_id" id="kegiatan_id" class="form-control">
                                <option value="">Semua Kegiatan</option>
                                @foreach($kegiatans as $kegiatan)
                                    <option value="{{ $kegiatan->id }}" {{ request('kegiatan_id') == $kegiatan->id ? 'selected' : '' }}>
                                        {{ $kegiatan->nama_kegiatan }} ({{ $kegiatan->program->nama_program }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Filter Status:</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Pencarian:</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Cari nama indikator, deskripsi, atau satuan..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('indikator-kinerja.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Indicators Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Indikator Kinerja</h6>
        </div>
        <div class="card-body">
            @if($indikators->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Indikator</th>
                                <th>Kegiatan</th>
                                <th>Target</th>
                                <th>Satuan</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Laporan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($indikators as $index => $indikator)
                            <tr>
                                <td>{{ $indikators->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $indikator->nama_indikator }}</strong>
                                    @if($indikator->deskripsi)
                                        <br><small class="text-muted">{{ Str::limit($indikator->deskripsi, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $indikator->kegiatan->nama_kegiatan }}</span>
                                    <br><small class="text-muted">{{ $indikator->kegiatan->program->nama_program }}</small>
                                    <br><small class="text-muted">{{ $indikator->kegiatan->program->instansi->nama_instansi }}</small>
                                </td>
                                <td>{{ number_format($indikator->target, 2) }}</td>
                                <td>{{ $indikator->satuan }}</td>
                                <td>
                                    @if($indikator->jenis == 'input')
                                        <span class="badge badge-secondary">Input</span>
                                    @elseif($indikator->jenis == 'output')
                                        <span class="badge badge-primary">Output</span>
                                    @elseif($indikator->jenis == 'outcome')
                                        <span class="badge badge-success">Outcome</span>
                                    @else
                                        <span class="badge badge-warning">Impact</span>
                                    @endif
                                </td>
                                <td>
                                    @if($indikator->status == 'aktif')
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Non-aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $indikator->laporanKinerjas->count() }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('indikator-kinerja.show', $indikator) }}" 
                                           class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('laporan-kinerja.create', ['indikator_kinerja_id' => $indikator->id]) }}" 
                                           class="btn btn-success btn-sm" title="Tambah Laporan Kinerja">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <a href="{{ route('indikator-kinerja.edit', $indikator) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deleteIndikator({{ $indikator->id }})" title="Hapus">
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
                            Menampilkan {{ $indikators->firstItem() }} sampai {{ $indikators->lastItem() }} 
                            dari {{ $indikators->total() }} indikator
                        </p>
                    </div>
                    <div>
                        {{ $indikators->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">Tidak ada data indikator kinerja</h5>
                    <p class="text-gray-400">Belum ada indikator kinerja yang ditambahkan atau tidak ada yang sesuai dengan filter.</p>
                    <a href="{{ route('indikator-kinerja.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Indikator Pertama
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
                <p>Apakah Anda yakin ingin menghapus indikator kinerja ini?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua laporan kinerja terkait.</small></p>
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
function deleteIndikator(id) {
    $('#deleteForm').attr('action', '/indikator-kinerja/' + id);
    $('#deleteModal').modal('show');
}

$(document).ready(function() {
    // Auto submit form when filter changes
    $('#kegiatan_id, #status').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush