@extends('layouts.modern')

@section('title', 'Detail Instansi')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.instansi.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Detail Instansi</h1>
                <p class="page-header-subtitle">{{ $instansi->nama_instansi }}</p>
            </div>
            <div class="page-header-actions">
                @can('update', $instansi)
                <a href="{{ route('sakip.instansi.edit', $instansi) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    <span class="ms-1">Edit</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Instansi -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>
                        Informasi Instansi
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row detail-list">
                        <dt class="col-sm-4">Kode Instansi</dt>
                        <dd class="col-sm-8"><span class="badge bg-light text-dark">{{ $instansi->kode_instansi }}</span></dd>

                        <dt class="col-sm-4">Nama Instansi</dt>
                        <dd class="col-sm-8">{{ $instansi->nama_instansi }}</dd>

                        <dt class="col-sm-4">Alamat</dt>
                        <dd class="col-sm-8">{{ $instansi->alamat ?? '-' }}</dd>

                        <dt class="col-sm-4">Telepon</dt>
                        <dd class="col-sm-8">{{ $instansi->telepon ?? '-' }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">
                            @if($instansi->email)
                                <a href="mailto:{{ $instansi->email }}">{{ $instansi->email }}</a>
                            @else
                                -
                            @endif
                        </dd>

                        <dt class="col-sm-4">Website</dt>
                        <dd class="col-sm-8">
                            @if($instansi->website)
                                <a href="{{ $instansi->website }}" target="_blank">{{ $instansi->website }}</a>
                            @else
                                -
                            @endif
                        </dd>

                        <dt class="col-sm-4">Kepala Instansi</dt>
                        <dd class="col-sm-8">{{ $instansi->kepala_instansi ?? '-' }}</dd>

                        <dt class="col-sm-4">NIP Kepala</dt>
                        <dd class="col-sm-8">{{ $instansi->nip_kepala ?? '-' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($instansi->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Non-Aktif</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Dibuat Pada</dt>
                        <dd class="col-sm-8 small text-muted">{{ $instansi->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Diupdate Pada</dt>
                        <dd class="col-sm-8 small text-muted">{{ $instansi->updated_at->format('d/m/Y H:i') }}</dd>
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
                        <div class="stat-item-label">Sasaran Strategis</div>
                        <div class="stat-item-value">{{ $instansi->sasaranStrategis->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-label">Program</div>
                        <div class="stat-item-value">{{ $instansi->programs->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-label">Indikator Kinerja</div>
                        <div class="stat-item-value">{{ $instansi->performanceIndicators->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sasaran Strategis Terkait -->
    @if($instansi->sasaranStrategis->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-bullseye me-2"></i>
                Sasaran Strategis Terkait
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Sasaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instansi->sasaranStrategis as $key => $sasaran)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><span class="badge bg-light text-dark">{{ $sasaran->kode_sasaran_strategis }}</span></td>
                            <td>{{ $sasaran->nama_strategis }}</td>
                            <td>
                                @if($sasaran->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sakip.sasaran-strategis.show', $sasaran) }}" class="btn btn-sm btn-outline-primary">
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
    @endif

    <!-- Program Terkait -->
    @if($instansi->programs->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-layer-group me-2"></i>
                Program Terkait
            </h5>
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
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instansi->programs as $key => $program)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><span class="badge bg-light text-dark">{{ $program->kode_program }}</span></td>
                            <td>{{ $program->nama_program }}</td>
                            <td>{{ $program->tahun }}</td>
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
    @endif
</div>
@endsection
