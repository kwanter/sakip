@extends('layouts.modern')

@section('title', 'Detail Data Kinerja')

@section('page-title', 'Detail Data Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.data-collection.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Detail Data Kinerja</h1>
                <p class="page-header-subtitle">Informasi lengkap data kinerja</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('sakip.data-collection.edit', $data) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-edit"></i>
            <span class="ms-1">Edit</span>
        </a>

        @if($data->status == 'pending')
        <form action="{{ route('sakip.data-collection.validate', $data) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-check"></i>
                <span class="ms-1">Validasi</span>
            </button>
        </form>

        <form action="{{ route('sakip.data-collection.reject', $data) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-times"></i>
                <span class="ms-1">Tolak</span>
            </button>
        </form>
        @endif

        <form action="{{ route('sakip.data-collection.destroy', $data) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash"></i>
                <span class="ms-1">Hapus</span>
            </button>
        </form>
    </div>

    <!-- Basic Information -->
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Informasi Dasar</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-label mb-1">Indikator Kinerja</div>
                    <p class="fw-bold mb-1">{{ $data->indicator->name }}</p>
                    <small class="text-muted">{{ $data->indicator->code }}</small>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Instansi</div>
                    <p class="fw-bold mb-0">{{ $data->instansi->name }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Periode</div>
                    <p class="fw-bold mb-0">{{ $data->period }} {{ $data->year }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Status Validasi</div>
                    <span class="badge {{ $data->status == 'validated' ? 'badge-success' : ($data->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ ucfirst($data->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Data -->
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Data Kinerja</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-label mb-1">Nilai Kinerja</div>
                    <p class="stat-value">{{ number_format($data->value, 2) }}</p>
                </div>

                <div class="col-md-4">
                    <div class="stat-label mb-1">Target</div>
                    <p class="stat-value">{{ number_format($data->target, 2) }}</p>
                </div>

                <div class="col-md-4">
                    <div class="stat-label mb-1">Pencapaian</div>
                    <p class="stat-value {{ ($data->target > 0 ? ($data->value / $data->target * 100) : 0) >= 100 ? 'text-success' : 'text-danger' }}">
                        @if($data->target > 0)
                            {{ number_format(($data->value / $data->target) * 100, 1) }}%
                        @else
                            -
                        @endif
                    </p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Sumber Data</div>
                    <p class="mb-0">{{ $data->data_source }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Metode Pengumpulan</div>
                    <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $data->collection_method)) }}</p>
                </div>
            </div>

            @if($data->notes)
            <div class="mt-4">
                <div class="stat-label mb-1">Catatan</div>
                <p class="mb-0">{{ $data->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Evidence Documents -->
    @if($data->evidence->count() > 0)
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Dokumen Bukti</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($data->evidence as $evidence)
                <div class="col-md-6 col-lg-4">
                    <div class="p-3 rounded border h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-alt fa-2x text-muted me-3"></i>
                            <div>
                                <p class="mb-0 fw-bold">{{ $evidence->filename }}</p>
                                <small class="text-muted">{{ number_format($evidence->file_size / 1024, 2) }} KB</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('sakip.evidence.download', $evidence) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-eye"></i>
                                <span class="ms-1">Lihat</span>
                            </a>
                            <a href="{{ route('sakip.evidence.download', $evidence) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-download"></i>
                                <span class="ms-1">Unduh</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Validation Information -->
    @if($data->validated_at)
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Informasi Validasi</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-label mb-1">Divalidasi oleh</div>
                    <p class="mb-0">{{ $data->validatedBy->name }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Tanggal Validasi</div>
                    <p class="mb-0">{{ $data->validated_at->format('d F Y H:i') }}</p>
                </div>
            </div>

            @if($data->validation_notes)
            <div class="mt-4">
                <div class="stat-label mb-1">Catatan Validasi</div>
                <p class="mb-0">{{ $data->validation_notes }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- System Information -->
    <div class="modern-card">
        <div class="card-header">
            <h6 class="card-title mb-0">Informasi Sistem</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-label mb-1">Dibuat oleh</div>
                    <p class="mb-0">{{ $data->createdBy->name }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Tanggal Dibuat</div>
                    <p class="mb-0">{{ $data->created_at->format('d F Y H:i') }}</p>
                </div>

                @if($data->updated_at != $data->created_at)
                <div class="col-md-6">
                    <div class="stat-label mb-1">Terakhir Diperbarui oleh</div>
                    <p class="mb-0">{{ $data->updatedBy ? $data->updatedBy->name : '-' }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Tanggal Diperbarui</div>
                    <p class="mb-0">{{ $data->updated_at->format('d F Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
