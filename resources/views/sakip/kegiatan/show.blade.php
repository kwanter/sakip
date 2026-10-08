@extends('layouts.modern')

@section('title', 'Detail Kegiatan')

@section('page-title', 'Detail Kegiatan')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Detail Kegiatan</h1>
                <p class="page-header-subtitle">{{ $kegiatan->nama_kegiatan }}</p>
            </div>
            <div class="page-header-actions">
                @can('update', $kegiatan)
                <a href="{{ route('sakip.kegiatan.edit', $kegiatan) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i>
                    <span class="ms-1">Edit</span>
                </a>
                @endcan
                <a href="{{ route('sakip.kegiatan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Kegiatan -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold">Informasi Kegiatan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="250">Kode Kegiatan</th>
                            <td>: <strong>{{ $kegiatan->kode_kegiatan }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nama Kegiatan</th>
                            <td>: {{ $kegiatan->nama_kegiatan }}</td>
                        </tr>
                        <tr>
                            <th>Program</th>
                            <td>:
                                @if($kegiatan->program)
                                    <a href="{{ route('sakip.program.show', $kegiatan->program) }}">
                                        {{ $kegiatan->program->nama_program }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @if($kegiatan->program)
                        <tr>
                            <th>Instansi</th>
                            <td>:
                                <a href="{{ route('sakip.instansi.show', $kegiatan->program->instansi) }}">
                                    {{ $kegiatan->program->instansi->nama_instansi }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Sasaran Strategis</th>
                            <td>:
                                <a href="{{ route('sakip.sasaran-strategis.show', $kegiatan->program->sasaranStrategis) }}">
                                    {{ $kegiatan->program->sasaranStrategis->nama_strategis }}
                                </a>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Deskripsi</th>
                            <td>: {{ $kegiatan->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Anggaran</th>
                            <td>: <strong>Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai</th>
                            <td>: {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Selesai</th>
                            <td>: {{ $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Penanggung Jawab</th>
                            <td>: {{ $kegiatan->penanggung_jawab ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>:
                                @if($kegiatan->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @elseif($kegiatan->status == 'selesai')
                                    <span class="badge badge-primary">Selesai</span>
                                @else
                                    <span class="badge badge-neutral">Draft</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>: {{ $kegiatan->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diupdate Pada</th>
                            <td>: {{ $kegiatan->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    @can('update', $kegiatan)
                    <a href="{{ route('sakip.kegiatan.edit', $kegiatan) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-edit"></i> Edit Kegiatan
                    </a>
                    @endcan
                    @can('delete', $kegiatan)
                    <form action="{{ route('sakip.kegiatan.destroy', $kegiatan) }}" method="POST" style="display: inline;"
                          data-confirm="Apakah Anda yakin ingin menghapus kegiatan ini?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash"></i> Hapus Kegiatan
                        </button>
                    </form>
                    @endcan
                    <a href="{{ route('sakip.kegiatan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-list"></i> Lihat Semua Kegiatan
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold">Statistik</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Status Kegiatan</small>
                        <h4>
                            @if($kegiatan->status == 'aktif')
                                <span class="badge badge-success">Aktif</span>
                            @elseif($kegiatan->status == 'selesai')
                                <span class="badge badge-primary">Selesai</span>
                            @else
                                <span class="badge badge-neutral">Draft</span>
                            @endif
                        </h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Anggaran Kegiatan</small>
                        <h4>Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Durasi</small>
                        <h4>
                            @if($kegiatan->tanggal_mulai && $kegiatan->tanggal_selesai)
                                {{ $kegiatan->tanggal_mulai->diffInDays($kegiatan->tanggal_selesai) }} Hari
                            @else
                                -
                            @endif
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Program Terkait -->
            @if($kegiatan->program)
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold">Program Terkait</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <strong>{{ $kegiatan->program->nama_program }}</strong>
                    </p>
                    <small class="text-muted">{{ $kegiatan->program->kode_program }}</small>
                    <br><br>
                    <a href="{{ route('sakip.program.show', $kegiatan->program) }}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-arrow-right"></i> Lihat Program
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
