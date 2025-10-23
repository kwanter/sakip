@extends('layouts.app')

@section('title', 'Detail Sasaran Strategis')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bullseye"></i> Detail Sasaran Strategis
        </h1>
        <div>
            @can('update', $sasaranStrategis)
            <a href="{{ route('sakip.sasaran-strategis.edit', $sasaranStrategis) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            <a href="{{ route('sakip.sasaran-strategis.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Sasaran Strategis -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Sasaran Strategis</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="250">Kode Sasaran Strategis</th>
                            <td>: {{ $sasaranStrategis->kode_sasaran_strategis }}</td>
                        </tr>
                        <tr>
                            <th>Nama Sasaran Strategis</th>
                            <td>: {{ $sasaranStrategis->nama_strategis }}</td>
                        </tr>
                        <tr>
                            <th>Instansi</th>
                            <td>:
                                <a href="{{ route('sakip.instansi.show', $sasaranStrategis->instansi) }}">
                                    {{ $sasaranStrategis->instansi->nama_instansi }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>: {{ $sasaranStrategis->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>:
                                @if($sasaranStrategis->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>: {{ $sasaranStrategis->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diupdate Pada</th>
                            <td>: {{ $sasaranStrategis->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Total Program</small>
                        <h4>{{ $sasaranStrategis->programs->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Program Aktif</small>
                        <h4>{{ $sasaranStrategis->programs->where('status', 'aktif')->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Kegiatan</small>
                        <h4>{{ $sasaranStrategis->programs->sum(function($program) { return $program->kegiatans->count(); }) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Terkait -->
    @if($sasaranStrategis->programs->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Program Terkait</h6>
            @can('create', App\Models\Program::class)
            <a href="{{ route('sakip.program.create', ['sasaran_strategis_id' => $sasaranStrategis->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Program
            </a>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Program</th>
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
                            <td>{{ $program->kode_program }}</td>
                            <td>{{ $program->nama_program }}</td>
                            <td>{{ $program->tahun }}</td>
                            <td>Rp {{ number_format($program->anggaran, 0, ',', '.') }}</td>
                            <td>
                                @if($program->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @elseif($program->status == 'selesai')
                                    <span class="badge badge-info">Selesai</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sakip.program.show', $program) }}" class="btn btn-info btn-sm">
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
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Program Terkait</h6>
            @can('create', App\Models\Program::class)
            <a href="{{ route('sakip.program.create', ['sasaran_strategis_id' => $sasaranStrategis->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Program
            </a>
            @endcan
        </div>
        <div class="card-body">
            <p class="text-center text-muted mb-0">Belum ada program terkait dengan sasaran strategis ini.</p>
        </div>
    </div>
    @endif
</div>
@endsection
