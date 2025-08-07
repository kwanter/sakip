@extends('layouts.app')

@section('title', 'Detail Laporan Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Laporan Kinerja</h1>
        <div>
            <a href="{{ route('laporan-kinerja.edit', $laporanKinerja) }}" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ route('laporan-kinerja.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Laporan Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Laporan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Tahun:</label>
                                <p class="text-gray-800">{{ $laporanKinerja->tahun }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Periode:</label>
                                <p class="text-gray-800">
                                    @switch($laporanKinerja->periode)
                                        @case('triwulan1')
                                            <span class="badge badge-info">Triwulan I</span>
                                            @break
                                        @case('triwulan2')
                                            <span class="badge badge-info">Triwulan II</span>
                                            @break
                                        @case('triwulan3')
                                            <span class="badge badge-info">Triwulan III</span>
                                            @break
                                        @case('triwulan4')
                                            <span class="badge badge-info">Triwulan IV</span>
                                            @break
                                        @case('tahunan')
                                            <span class="badge badge-primary">Tahunan</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Nilai Realisasi:</label>
                                <p class="text-gray-800">
                                    <span class="h5 text-primary">{{ number_format($laporanKinerja->nilai_realisasi, 2) }}</span>
                                    <small class="text-muted">{{ $laporanKinerja->indikatorKinerja->satuan }}</small>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Target:</label>
                                <p class="text-gray-800">
                                    <span class="h5 text-info">{{ number_format($laporanKinerja->indikatorKinerja->target, 2) }}</span>
                                    <small class="text-muted">{{ $laporanKinerja->indikatorKinerja->satuan }}</small>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Persentase Capaian:</label>
                                <p class="text-gray-800">
                                    @if($laporanKinerja->persentase_capaian)
                                        <span class="h5 badge badge-{{ $laporanKinerja->persentase_capaian >= 80 ? 'success' : ($laporanKinerja->persentase_capaian >= 60 ? 'warning' : 'danger') }}">
                                            {{ number_format($laporanKinerja->persentase_capaian, 2) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Status Verifikasi:</label>
                                <p class="text-gray-800">
                                    @switch($laporanKinerja->status_verifikasi)
                                        @case('draft')
                                            <span class="badge badge-secondary">Draft</span>
                                            @break
                                        @case('submitted')
                                            <span class="badge badge-warning">Diajukan</span>
                                            @break
                                        @case('verified')
                                            <span class="badge badge-success">Terverifikasi</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge badge-danger">Ditolak</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Tanggal Dibuat:</label>
                                <p class="text-gray-800">{{ $laporanKinerja->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kendala dan Tindak Lanjut -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Kendala dan Tindak Lanjut</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Kendala:</label>
                                <div class="border rounded p-3 bg-light">
                                    @if($laporanKinerja->kendala)
                                        <p class="text-gray-800 mb-0">{{ $laporanKinerja->kendala }}</p>
                                    @else
                                        <p class="text-muted mb-0"><em>Tidak ada kendala yang dilaporkan</em></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Tindak Lanjut:</label>
                                <div class="border rounded p-3 bg-light">
                                    @if($laporanKinerja->tindak_lanjut)
                                        <p class="text-gray-800 mb-0">{{ $laporanKinerja->tindak_lanjut }}</p>
                                    @else
                                        <p class="text-muted mb-0"><em>Tidak ada tindak lanjut yang direncanakan</em></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan Verifikasi -->
            @if($laporanKinerja->catatan_verifikasi)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Catatan Verifikasi</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        {{ $laporanKinerja->catatan_verifikasi }}
                    </div>
                </div>
            </div>
            @endif

            <!-- File Pendukung -->
            @if($laporanKinerja->file_pendukung)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">File Pendukung</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-alt fa-2x text-primary mr-3"></i>
                        <div>
                            <p class="mb-1 font-weight-bold">{{ basename($laporanKinerja->file_pendukung) }}</p>
                            <a href="{{ Storage::url($laporanKinerja->file_pendukung) }}" 
                               class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-download"></i> Unduh File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Indikator Kinerja Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Indikator Kinerja</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Nama Indikator:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->nama_indikator }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Definisi:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->definisi }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Satuan:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->satuan }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Jenis:</label>
                        <p class="text-gray-800">
                            <span class="badge badge-{{ $laporanKinerja->indikatorKinerja->jenis == 'output' ? 'success' : 'info' }}">
                                {{ ucfirst($laporanKinerja->indikatorKinerja->jenis) }}
                            </span>
                        </p>
                    </div>
                    
                    @if($laporanKinerja->indikatorKinerja->formula_perhitungan)
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Formula Perhitungan:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->formula_perhitungan }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Kegiatan Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kegiatan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Instansi:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->kegiatan->program->instansi->nama_instansi }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Program:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->kegiatan->program->nama_program }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Kegiatan:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->kegiatan->nama_kegiatan }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Periode Kegiatan:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->kegiatan->tanggal_mulai->format('d/m/Y') }} - {{ $laporanKinerja->indikatorKinerja->kegiatan->tanggal_selesai->format('d/m/Y') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Anggaran:</label>
                        <p class="text-gray-800">Rp {{ number_format($laporanKinerja->indikatorKinerja->kegiatan->anggaran, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('laporan-kinerja.edit', $laporanKinerja) }}" class="btn btn-warning btn-block">
                            <i class="fas fa-edit"></i> Edit Laporan
                        </a>
                        
                        <a href="{{ route('indikator-kinerja.show', $laporanKinerja->indikatorKinerja) }}" class="btn btn-info btn-block">
                            <i class="fas fa-chart-line"></i> Lihat Indikator
                        </a>
                        
                        <a href="{{ route('kegiatan.show', $laporanKinerja->indikatorKinerja->kegiatan) }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-tasks"></i> Lihat Kegiatan
                        </a>
                        
                        <form action="{{ route('laporan-kinerja.destroy', $laporanKinerja) }}" method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash"></i> Hapus Laporan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection