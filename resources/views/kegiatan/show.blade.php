@extends('layouts.app')

@section('title', 'Detail Kegiatan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Kegiatan</h1>
        <div>
            <a href="{{ route('kegiatan.edit', $kegiatan) }}" class="btn btn-warning btn-sm shadow-sm mr-2">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kegiatan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Kode Kegiatan:</label>
                                <p class="text-gray-900">{{ $kegiatan->kode_kegiatan }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Nama Kegiatan:</label>
                                <p class="text-gray-900">{{ $kegiatan->nama_kegiatan }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Program:</label>
                                <p class="text-gray-900">
                                    <a href="{{ route('program.show', $kegiatan->program) }}" class="text-primary">
                                        {{ $kegiatan->program->nama_program }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Instansi:</label>
                                <p class="text-gray-900">
                                    <a href="{{ route('instansi.show', $kegiatan->program->instansi) }}" class="text-primary">
                                        {{ $kegiatan->program->instansi->nama_instansi }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Anggaran:</label>
                                <p class="text-gray-900">Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Penanggung Jawab:</label>
                                <p class="text-gray-900">{{ $kegiatan->penanggung_jawab }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Status:</label>
                                <p>
                                    @if($kegiatan->status == 'berjalan')
                    <span class="badge badge-success badge-lg">Berjalan</span>
                @elseif($kegiatan->status == 'selesai')
                    <span class="badge badge-primary badge-lg">Selesai</span>
                @elseif($kegiatan->status == 'tunda')
                    <span class="badge badge-secondary badge-lg">Tunda</span>
                @else
                    <span class="badge badge-warning badge-lg">Draft</span>
                @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Periode:</label>
                                <p class="text-gray-900">
                                    @if($kegiatan->tanggal_mulai && $kegiatan->tanggal_selesai)
                                        {{ $kegiatan->tanggal_mulai->format('d/m/Y') }} - {{ $kegiatan->tanggal_selesai->format('d/m/Y') }}
                                    @elseif($kegiatan->tanggal_mulai)
                                        Mulai: {{ $kegiatan->tanggal_mulai->format('d/m/Y') }}
                                    @elseif($kegiatan->tanggal_selesai)
                                        Selesai: {{ $kegiatan->tanggal_selesai->format('d/m/Y') }}
                                    @else
                                        Tidak ditentukan
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @if($kegiatan->deskripsi)
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Deskripsi:</label>
                        <p class="text-gray-900">{{ $kegiatan->deskripsi }}</p>
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Dibuat:</label>
                                <p class="text-gray-900">{{ $kegiatan->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Terakhir Diupdate:</label>
                                <p class="text-gray-900">{{ $kegiatan->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Indicators -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Indikator Kinerja</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="{{ route('indikator-kinerja.create', ['kegiatan_id' => $kegiatan->id]) }}">
                                <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>
                                Tambah Indikator
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($kegiatan->indikatorKinerjas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Indikator</th>
                                        <th>Target</th>
                                        <th>Satuan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kegiatan->indikatorKinerjas as $index => $indikator)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $indikator->nama_indikator }}</strong>
                                            @if($indikator->deskripsi)
                                                <br><small class="text-muted">{{ Str::limit($indikator->deskripsi, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ number_format($indikator->target, 2) }}</td>
                                        <td>{{ $indikator->satuan }}</td>
                                        <td>
                                            @if($indikator->status == 'aktif')
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Non-aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('indikator-kinerja.show', $indikator) }}" 
                                                   class="btn btn-info btn-sm" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('indikator-kinerja.edit', $indikator) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">Belum ada indikator kinerja</h5>
                            <p class="text-gray-400">Tambahkan indikator kinerja untuk mengukur pencapaian kegiatan ini.</p>
                            <a href="{{ route('indikator-kinerja.create', ['kegiatan_id' => $kegiatan->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Indikator Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Kegiatan</h6>
                </div>
                <div class="card-body">
                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Indikator
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kegiatan->indikatorKinerjas->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    
                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Indikator Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $kegiatan->indikatorKinerjas->where('status', 'aktif')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Laporan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $kegiatan->indikatorKinerjas->sum(function($indikator) { return $indikator->laporanKinerjas->count(); }) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('indikator-kinerja.create', ['kegiatan_id' => $kegiatan->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-plus text-primary"></i> Tambah Indikator</h6>
                            </div>
                            <p class="mb-1">Buat indikator kinerja baru untuk kegiatan ini</p>
                        </a>
                        
                        <a href="{{ route('indikator-kinerja.index', ['kegiatan_id' => $kegiatan->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-list text-info"></i> Lihat Semua Indikator</h6>
                            </div>
                            <p class="mb-1">Kelola semua indikator kinerja kegiatan</p>
                        </a>
                        
                        <a href="{{ route('laporan-kinerja.create', ['kegiatan_id' => $kegiatan->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-file-alt text-success"></i> Buat Laporan</h6>
                            </div>
                            <p class="mb-1">Buat laporan kinerja untuk kegiatan ini</p>
                        </a>
                        
                        <a href="{{ route('kegiatan.edit', $kegiatan) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-edit text-warning"></i> Edit Kegiatan</h6>
                            </div>
                            <p class="mb-1">Ubah informasi kegiatan</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Terkait</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Program Induk</h6>
                        <p class="small">
                            <a href="{{ route('program.show', $kegiatan->program) }}" class="text-decoration-none">
                                {{ $kegiatan->program->nama_program }}
                            </a>
                        </p>
                        <p class="small text-muted">{{ $kegiatan->program->deskripsi ? Str::limit($kegiatan->program->deskripsi, 100) : 'Tidak ada deskripsi' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-primary">Instansi</h6>
                        <p class="small">
                            <a href="{{ route('instansi.show', $kegiatan->program->instansi) }}" class="text-decoration-none">
                                {{ $kegiatan->program->instansi->nama_instansi }}
                            </a>
                        </p>
                        <p class="small text-muted">{{ $kegiatan->program->instansi->alamat }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush