@extends('layouts.app')

@section('title', 'Detail Instansi')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> Detail Instansi
        </h1>
        <div>
            @can('update', $instansi)
            <a href="{{ route('sakip.instansi.edit', $instansi) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            <a href="{{ route('sakip.instansi.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Instansi -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Instansi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Kode Instansi</th>
                            <td>: {{ $instansi->kode_instansi }}</td>
                        </tr>
                        <tr>
                            <th>Nama Instansi</th>
                            <td>: {{ $instansi->nama_instansi }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>: {{ $instansi->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>: {{ $instansi->telepon ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>: {{ $instansi->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Website</th>
                            <td>:
                                @if($instansi->website)
                                    <a href="{{ $instansi->website }}" target="_blank">{{ $instansi->website }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Kepala Instansi</th>
                            <td>: {{ $instansi->kepala_instansi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>NIP Kepala</th>
                            <td>: {{ $instansi->nip_kepala ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>:
                                @if($instansi->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>: {{ $instansi->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diupdate Pada</th>
                            <td>: {{ $instansi->updated_at->format('d/m/Y H:i') }}</td>
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
                        <small class="text-muted">Sasaran Strategis</small>
                        <h4>{{ $instansi->sasaranStrategis->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Program</small>
                        <h4>{{ $instansi->programs->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Indikator Kinerja</small>
                        <h4>{{ $instansi->performanceIndicators->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sasaran Strategis Terkait -->
    @if($instansi->sasaranStrategis->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Sasaran Strategis Terkait</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Strategis</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instansi->sasaranStrategis as $key => $sasaran)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $sasaran->kode_sasaran_strategis }}</td>
                            <td>{{ $sasaran->nama_strategis }}</td>
                            <td>
                                @if($sasaran->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sakip.sasaran-strategis.show', $sasaran) }}" class="btn btn-info btn-sm">
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
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Program Terkait</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
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
                            <td>{{ $program->kode_program }}</td>
                            <td>{{ $program->nama_program }}</td>
                            <td>{{ $program->tahun }}</td>
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
    @endif
</div>
@endsection
