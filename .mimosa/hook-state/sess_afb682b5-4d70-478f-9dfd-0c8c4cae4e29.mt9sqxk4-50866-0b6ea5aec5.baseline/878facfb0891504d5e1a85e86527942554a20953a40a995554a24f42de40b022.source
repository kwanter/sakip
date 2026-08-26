@extends('layouts.modern')

@section('title', 'Detail Program')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.program.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Detail Program</h1>
                <p class="page-header-subtitle">{{ $program->nama_program }}</p>
            </div>
            <div class="page-header-actions">
                @can('update', $program)
                <a href="{{ route('sakip.program.edit', $program) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    <span class="ms-1">Edit</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Program -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        Informasi Program
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row detail-list">
                        <dt class="col-sm-4">Kode Program</dt>
                        <dd class="col-sm-8"><span class="badge bg-light text-dark">{{ $program->kode_program }}</span></dd>

                        <dt class="col-sm-4">Nama Program</dt>
                        <dd class="col-sm-8">{{ $program->nama_program }}</dd>

                        <dt class="col-sm-4">Instansi</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('sakip.instansi.show', $program->instansi) }}" class="text-decoration-none">
                                <i class="fas fa-building me-1"></i>
                                {{ $program->instansi->nama_instansi }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">Sasaran Strategis</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('sakip.sasaran-strategis.show', $program->sasaranStrategis) }}" class="text-decoration-none">
                                <i class="fas fa-bullseye me-1"></i>
                                {{ $program->sasaranStrategis->nama_strategis }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $program->deskripsi ?? '-' }}</dd>

                        <dt class="col-sm-4">Anggaran</dt>
                        <dd class="col-sm-8"><strong>Rp {{ number_format($program->anggaran, 0, ',', '.') }}</strong></dd>

                        <dt class="col-sm-4">Tahun</dt>
                        <dd class="col-sm-8">{{ $program->tahun }}</dd>

                        <dt class="col-sm-4">Penanggung Jawab</dt>
                        <dd class="col-sm-8">{{ $program->penanggung_jawab ?? '-' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($program->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @elseif($program->status == 'selesai')
                                <span class="badge bg-info">Selesai</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Dibuat Pada</dt>
                        <dd class="col-sm-8 small text-muted">{{ $program->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Diupdate Pada</dt>
                        <dd class="col-sm-8 small text-muted">{{ $program->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Statistik & Progress -->
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
                        <div class="stat-item-label">Total Kegiatan</div>
                        <div class="stat-item-value">{{ $program->kegiatans->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-label">Kegiatan Aktif</div>
                        <div class="stat-item-value">{{ $program->kegiatans->where('status', 'aktif')->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-label">Total Indikator</div>
                        <div class="stat-item-value">{{ $program->performanceIndicators?->count() ?? 0 }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-label">Realisasi Anggaran</div>
                        @php
                            $realisasi = $program->kegiatans->sum('anggaran_realisasi');
                            $persentase = $program->anggaran > 0 ? ($realisasi / $program->anggaran) * 100 : 0;
                        @endphp
                        <div class="stat-item-value small">{{ number_format($persentase, 1) }}%</div>
                        <div class="text-muted small">Rp {{ number_format($realisasi, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            @php
                $totalKegiatan = $program->kegiatans->count();
                $kegiatanSelesai = $program->kegiatans->where('status', 'selesai')->count();
                $progressPersentase = $totalKegiatan > 0 ? ($kegiatanSelesai / $totalKegiatan) * 100 : 0;
            @endphp
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        Progress
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Kegiatan Selesai</span>
                        <span class="fw-bold">{{ $kegiatanSelesai }}/{{ $totalKegiatan }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-{{ $progressPersentase >= 75 ? 'success' : ($progressPersentase >= 50 ? 'info' : 'warning') }}"
                             role="progressbar" style="width: {{ $progressPersentase }}%">
                            {{ number_format($progressPersentase, 0) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kegiatan Terkait -->
    @if($program->kegiatans->count() > 0)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-tasks me-2"></i>
                Kegiatan Terkait
            </h5>
            @can('create', App\Models\Kegiatan::class)
            <a href="{{ route('sakip.kegiatan.create', ['program_id' => $program->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kegiatan
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
                            <th>Nama Kegiatan</th>
                            <th>Anggaran</th>
                            <th>Realisasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($program->kegiatans as $key => $kegiatan)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><span class="badge bg-light text-dark">{{ $kegiatan->kode_kegiatan }}</span></td>
                            <td>{{ $kegiatan->nama_kegiatan }}</td>
                            <td>Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($kegiatan->anggaran_realisasi ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @if($kegiatan->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @elseif($kegiatan->status == 'selesai')
                                    <span class="badge bg-info">Selesai</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sakip.kegiatan.show', $kegiatan) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th>Rp {{ number_format($program->kegiatans->sum('anggaran'), 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($program->kegiatans->sum('anggaran_realisasi'), 0, ',', '.') }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-tasks me-2"></i>
                Kegiatan Terkait
            </h5>
            @can('create', App\Models\Kegiatan::class)
            <a href="{{ route('sakip.kegiatan.create', ['program_id' => $program->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kegiatan
            </a>
            @endcan
        </div>
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-tasks text-muted"></i>
                <p class="mb-0">Belum ada kegiatan terkait dengan program ini.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Indikator Kinerja Terkait -->
    @if(($program->performanceIndicators?->count() ?? 0) > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Indikator Kinerja Program
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Indikator</th>
                            <th>Satuan</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($program->performanceIndicators as $key => $indicator)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $indicator->nama_indikator }}</td>
                            <td>{{ $indicator->satuan ?? '-' }}</td>
                            <td>{{ $indicator->targets->first()->target ?? '-' }}</td>
                            <td>
                                @if($indicator->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sakip.indicators.show', $indicator) }}" class="btn btn-sm btn-outline-primary">
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
