@extends('layouts.modern')

@section('title', 'Detail Sasaran Strategis')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.sasaran-strategis.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Detail Sasaran Strategis</h1>
                <p class="page-header-subtitle">{{ $sasaranStrategis->nama_strategis }}</p>
            </div>
            <div class="page-header-actions">
                @can('update', $sasaranStrategis)
                <a href="{{ route('sakip.sasaran-strategis.edit', $sasaranStrategis) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    <span class="ms-1">Edit</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Sasaran Strategis -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bullseye me-2"></i>
                        Informasi Sasaran Strategis
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row detail-list">
                        <dt class="col-sm-4">Kode Sasaran</dt>
                        <dd class="col-sm-8"><span class="badge bg-light text-dark">{{ $sasaranStrategis->kode_sasaran_strategis }}</span></dd>

                        <dt class="col-sm-4">Nama Sasaran</dt>
                        <dd class="col-sm-8">{{ $sasaranStrategis->nama_strategis }}</dd>

                        <dt class="col-sm-4">Instansi</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('sakip.instansi.show', $sasaranStrategis->instansi) }}" class="text-decoration-none">
                                <i class="fas fa-building me-1"></i>
                                {{ $sasaranStrategis->instansi->nama_instansi }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $sasaranStrategis->deskripsi ?? '-' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($sasaranStrategis->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Non-Aktif</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Dibuat Pada</dt>
                        <dd class="col-sm-8 small text-muted">{{ $sasaranStrategis->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Diupdate Pada</dt>
                        <dd class="col-sm-8 small text-muted">{{ $sasaranStrategis->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistik
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <div class="stat-item-label">Total Program</div>
                        <div class="stat-item-value">{{ $sasaranStrategis->programs->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-label">Program Aktif</div>
                        <div class="stat-item-value">{{ $sasaranStrategis->programs->where('status', 'aktif')->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-label">Total Kegiatan</div>
                        <div class="stat-item-value">{{ $sasaranStrategis->programs->sum(function($program) { return $program->kegiatans->count(); }) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Terkait -->
    @if($sasaranStrategis->programs->count() > 0)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-layer-group me-2"></i>
                Program Terkait
            </h5>
            @can('create', App\Models\Program::class)
            <a href="{{ route('sakip.program.create', ['sasaran_strategis_id' => $sasaranStrategis->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Program
            </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Program</th>
                            <th>Tahun</th>
                            <th>Anggaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sasaranStrategis->programs as $key => $program)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><span class="badge bg-light text-dark">{{ $program->kode_program }}</span></td>
                            <td>{{ $program->nama_program }}</td>
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
                            <td>
                                <a href="{{ route('sakip.program.show', $program) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-layer-group me-2"></i>
                Program Terkait
            </h5>
            @can('create', App\Models\Program::class)
            <a href="{{ route('sakip.program.create', ['sasaran_strategis_id' => $sasaranStrategis->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Program
            </a>
            @endcan
        </div>
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-layer-group text-muted"></i>
                <p class="mb-0">Belum ada program terkait dengan sasaran strategis ini.</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
