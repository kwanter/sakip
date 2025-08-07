@extends('layouts.app')

@section('title', 'Data Instansi')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Instansi</h1>
        <a href="{{ route('instansi.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Instansi
        </a>
    </div>

    <!-- DataTable Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Instansi</h6>
        </div>
        <div class="card-body">
            @if($instansis->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Instansi</th>
                                <th>Kepala Instansi</th>
                                <th>Kontak</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instansis as $index => $instansi)
                            <tr>
                                <td>{{ $instansis->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $instansi->kode_instansi }}</span>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $instansi->nama_instansi }}</div>
                                    <div class="small text-gray-500">{{ Str::limit($instansi->alamat, 50) }}</div>
                                </td>
                                <td>
                                    <div>{{ $instansi->kepala_instansi }}</div>
                                    @if($instansi->nip_kepala)
                                        <div class="small text-gray-500">NIP: {{ $instansi->nip_kepala }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($instansi->telepon)
                                        <div><i class="fas fa-phone fa-sm"></i> {{ $instansi->telepon }}</div>
                                    @endif
                                    @if($instansi->email)
                                        <div><i class="fas fa-envelope fa-sm"></i> {{ $instansi->email }}</div>
                                    @endif
                                    @if($instansi->website)
                                        <div><i class="fas fa-globe fa-sm"></i> 
                                            <a href="{{ $instansi->website }}" target="_blank" class="text-primary">Website</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $instansi->programs_count }} Program</span>
                                </td>
                                <td>
                                    @if($instansi->status == 'aktif')
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Non-aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('instansi.show', $instansi) }}" 
                                           class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('instansi.edit', $instansi) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('instansi.destroy', $instansi) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-delete" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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
                        <small class="text-muted">
                            Menampilkan {{ $instansis->firstItem() }} sampai {{ $instansis->lastItem() }} 
                            dari {{ $instansis->total() }} data
                        </small>
                    </div>
                    <div>
                        {{ $instansis->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">Belum ada data instansi</h5>
                    <p class="text-gray-400">Klik tombol "Tambah Instansi" untuk menambah data baru.</p>
                    <a href="{{ route('instansi.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Instansi Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table td {
    vertical-align: middle;
}

.table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[title]').tooltip();
    
    // Confirm delete
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Apakah Anda yakin ingin menghapus instansi ini?\n\nPerhatian: Semua data program, kegiatan, dan laporan yang terkait juga akan terhapus.')) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush