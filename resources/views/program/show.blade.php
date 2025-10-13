@extends('layouts.app')

@section('title', 'Detail Program')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Program</h1>
        <div>
            <a href="{{ route('program.edit', $program) }}" class="btn btn-warning btn-sm shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ route('program.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Program Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Program</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Kode Program:</label>
                                <p class="text-gray-900">{{ $program->kode_program }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Status:</label>
                                <p>
                                    @if($program->status == 'aktif')
                                        <span class="badge badge-success">Aktif</span>
                                    @elseif($program->status == 'selesai')
                                        <span class="badge badge-primary">Selesai</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Nama Program:</label>
                        <p class="text-gray-900">{{ $program->nama_program }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Instansi:</label>
                        <p class="text-gray-900">
                            <a href="{{ route('instansi.show', $program->instansi) }}" class="text-decoration-none">
                                {{ $program->instansi->nama_instansi }}
                            </a>
                        </p>
                    </div>
                    
                    @if($program->deskripsi)
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Deskripsi:</label>
                        <p class="text-gray-900">{{ $program->deskripsi }}</p>
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Anggaran:</label>
                                <p class="text-gray-900">Rp {{ number_format($program->anggaran, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Tahun Mulai:</label>
                                <p class="text-gray-900">{{ $program->tahun }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Tahun:</label>
                <p class="text-gray-900">{{ $program->tahun }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Penanggung Jawab:</label>
                        <p class="text-gray-900">{{ $program->penanggung_jawab }}</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Dibuat:</label>
                                <p class="text-gray-900">{{ $program->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Terakhir Diupdate:</label>
                                <p class="text-gray-900">{{ $program->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Program</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <h4 class="text-primary">{{ $program->kegiatans->count() }}</h4>
                            <p class="text-gray-600 mb-0">Total Kegiatan</p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-success">{{ $program->kegiatans->where('status', 'aktif')->count() }}</h4>
                            <p class="text-gray-600 mb-0">Kegiatan Aktif</p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-info">{{ $program->kegiatans->where('status', 'selesai')->count() }}</h4>
                            <p class="text-gray-600 mb-0">Kegiatan Selesai</p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-warning">Rp {{ number_format($program->kegiatans->sum('anggaran'), 0, ',', '.') }}</h4>
                            <p class="text-gray-600 mb-0">Total Anggaran Kegiatan</p>
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
                    <div class="d-grid gap-2">
                        <a href="{{ route('kegiatan.create', ['program_id' => $program->id]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Kegiatan
                        </a>
                        <a href="{{ route('kegiatan.by-program', $program) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-list"></i> Lihat Kegiatan
                        </a>
                        <a href="{{ route('program.edit', $program) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Program
                        </a>
                        <a href="{{ route('instansi.show', $program->instansi) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-building"></i> Lihat Instansi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Activities List -->
    @if($program->kegiatans->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kegiatan</h6>
            <a href="{{ route('kegiatan.create', ['program_id' => $program->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kegiatan
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Kegiatan</th>
                            <th>Anggaran</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Penanggung Jawab</th>
                            <th>Indikator</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($program->kegiatans as $kegiatan)
                        <tr>
                            <td>{{ $kegiatan->kode_kegiatan }}</td>
                            <td>
                                <strong>{{ $kegiatan->nama_kegiatan }}</strong>
                                @if($kegiatan->deskripsi)
                                    <br><small class="text-muted">{{ Str::limit($kegiatan->deskripsi, 50) }}</small>
                                @endif
                            </td>
                            <td>Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</td>
                            <td>
                                {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->format('d/m/Y') : '-' }} - 
                                {{ $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->format('d/m/Y') : '-' }}
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
                                <span class="badge badge-info">{{ $kegiatan->indikatorKinerjas->count() }}</span>
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
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kegiatan</h6>
        </div>
        <div class="card-body">
            <div class="text-center py-4">
                <i class="fas fa-tasks fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-500">Belum ada kegiatan</h5>
                <p class="text-gray-400">Program ini belum memiliki kegiatan. Tambahkan kegiatan pertama untuk memulai.</p>
                <a href="{{ route('kegiatan.create', ['program_id' => $program->id]) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kegiatan Pertama
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush