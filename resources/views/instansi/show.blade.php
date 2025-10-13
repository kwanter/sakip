@extends('layouts.app')

@section('title', 'Detail Instansi')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Instansi</h1>
        <div>
            <a href="{{ route('instansi.edit', $instansi) }}" class="btn btn-warning btn-sm shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ route('instansi.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Instansi Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Instansi</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Kode Instansi:</label>
                                <p class="text-gray-900">{{ $instansi->kode_instansi }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Status:</label>
                                <p>
                                    @if($instansi->status == 'aktif')
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Non-aktif</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Nama Instansi:</label>
                        <p class="text-gray-900">{{ $instansi->nama_instansi }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Alamat:</label>
                        <p class="text-gray-900">{{ $instansi->alamat }}</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Kepala Instansi:</label>
                                <p class="text-gray-900">{{ $instansi->kepala_instansi }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">NIP Kepala:</label>
                                <p class="text-gray-900">{{ $instansi->nip_kepala ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Telepon:</label>
                                <p class="text-gray-900">
                                    @if($instansi->telepon)
                                        <a href="tel:{{ $instansi->telepon }}">{{ $instansi->telepon }}</a>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Email:</label>
                                <p class="text-gray-900">
                                    @if($instansi->email)
                                        <a href="mailto:{{ $instansi->email }}">{{ $instansi->email }}</a>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Website:</label>
                                <p class="text-gray-900">
                                    @if($instansi->website)
                                        <a href="{{ $instansi->website }}" target="_blank">{{ $instansi->website }}</a>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Dibuat:</label>
                                <p class="text-gray-900">{{ $instansi->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Terakhir Diupdate:</label>
                                <p class="text-gray-900">{{ $instansi->updated_at->format('d/m/Y H:i') }}</p>
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
                    <h6 class="m-0 font-weight-bold text-primary">Statistik</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <h4 class="text-primary">{{ $instansi->programs->count() }}</h4>
                            <p class="text-gray-600 mb-0">Total Program</p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-success">{{ $instansi->programs->where('status', 'aktif')->count() }}</h4>
                            <p class="text-gray-600 mb-0">Program Aktif</p>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-info">{{ $instansi->programs->sum('kegiatans_count') }}</h4>
                            <p class="text-gray-600 mb-0">Total Kegiatan</p>
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
                        <a href="{{ route('program.create', ['instansi_id' => $instansi->id]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Program
                        </a>
                        <a href="{{ route('program.by-instansi', $instansi) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-list"></i> Lihat Program
                        </a>
                        <a href="{{ route('instansi.edit', $instansi) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Instansi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Programs List -->
    @if($instansi->programs->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Program</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Program</th>
                            <th>Anggaran</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Kegiatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instansi->programs as $program)
                        <tr>
                            <td>{{ $program->kode_program }}</td>
                            <td>{{ $program->nama_program }}</td>
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
                                <a href="{{ route('program.show', $program) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('program.edit', $program) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection