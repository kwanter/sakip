@extends('layouts.modern')

@section('title', 'Dashboard Pengumpul Data - SAKIP')

@section('page-title', 'Dashboard Pengumpul Data')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Dashboard Pengumpul Data</h1>
                <p class="page-header-subtitle">Kelola dan input data kinerja anda</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('sakip.data-collection.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Data</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    <div class="alert alert-info mb-4" role="alert">
        <i class="fas fa-info-circle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Informasi</div>
            <div class="alert-message">Anda memiliki 5 indikator yang belum diisi data untuk periode ini.</div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-value">24</div>
                <div class="stat-label">Total Data terinput</div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">18</div>
                <div class="stat-label">Tervalidasi (75% dari total)</div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value">4</div>
                <div class="stat-label">Menunggu validasi</div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
                <div class="stat-value">2</div>
                <div class="stat-label">Perlu revisi</div>
            </div>
        </div>
    </div>

    <!-- Pending Data Collection -->
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Indikator Belum Diisi</h6>
            <span class="badge badge-danger">5 perlu diisi</span>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <div class="d-flex align-items-center justify-content-between p-3 rounded border">
                    <div class="d-flex align-items-center">
                        <span class="d-inline-block rounded-circle bg-danger me-3" style="width: 8px; height: 8px;"></span>
                        <div>
                            <p class="mb-0 fw-bold">IK.01 - Persentase pelayanan publik tervalidasi</p>
                            <small class="text-muted">Periode: Triwulan IV 2024 | Tenggat: 30 Des 2024</small>
                        </div>
                    </div>
                    <a href="{{ route('sakip.data-collection.create') }}">Input Data</a>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded border">
                    <div class="d-flex align-items-center">
                        <span class="d-inline-block rounded-circle bg-danger me-3" style="width: 8px; height: 8px;"></span>
                        <div>
                            <p class="mb-0 fw-bold">IK.02 - Jumlah program unggulan terlaksana</p>
                            <small class="text-muted">Periode: Triwulan IV 2024 | Tenggat: 30 Des 2024</small>
                        </div>
                    </div>
                    <a href="{{ route('sakip.data-collection.create') }}">Input Data</a>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded border">
                    <div class="d-flex align-items-center">
                        <span class="d-inline-block rounded-circle bg-danger me-3" style="width: 8px; height: 8px;"></span>
                        <div>
                            <p class="mb-0 fw-bold">IK.03 - Tingkat kepuasan masyarakat</p>
                            <small class="text-muted">Periode: Triwulan IV 2024 | Tenggat: 30 Des 2024</small>
                        </div>
                    </div>
                    <a href="{{ route('sakip.data-collection.create') }}">Input Data</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data Collection -->
    <div class="modern-table-container mb-4">
        <div class="table-toolbar">
            <h6 class="card-title mb-0">Data Terakhir Diinput</h6>
            <div class="table-actions">
                <a href="{{ route('sakip.data-collection.index') }}">Lihat Semua</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Indikator</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">IK.15</td>
                        <td>Persentase kehadiran pegawai</td>
                        <td>95.2%</td>
                        <td>
                            <span class="badge badge-success">Tervalidasi</span>
                        </td>
                        <td>2 jam lalu</td>
                        <td class="text-end">
                            <a href="#">Detail</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">IK.14</td>
                        <td>Jumlah kegiatan yang dilaksanakan</td>
                        <td>24</td>
                        <td>
                            <span class="badge badge-warning">Menunggu</span>
                        </td>
                        <td>1 hari lalu</td>
                        <td class="text-end">
                            <a href="#">Detail</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">IK.13</td>
                        <td>Angka kepuasan masyarakat</td>
                        <td>4.2</td>
                        <td>
                            <span class="badge badge-danger">Perlu Revisi</span>
                        </td>
                        <td>3 hari lalu</td>
                        <td class="text-end">
                            <a href="#">Revisi</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="modern-card">
        <div class="card-header">
            <h6 class="card-title mb-0">Aksi Cepat</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="{{ route('sakip.data-collection.create') }}" class="text-decoration-none">
                        <div class="d-flex align-items-center p-3 rounded border h-100">
                            <div class="stat-icon primary me-3">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold">Input Data Baru</p>
                                <small class="text-muted">Tambah data kinerja</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="text-decoration-none">
                        <div class="d-flex align-items-center p-3 rounded border h-100">
                            <div class="stat-icon success me-3">
                                <i class="fas fa-file-import"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold">Impor Excel</p>
                                <small class="text-muted">Upload data massal</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="text-decoration-none">
                        <div class="d-flex align-items-center p-3 rounded border h-100">
                            <div class="stat-icon warning me-3">
                                <i class="fas fa-file-export"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold">Ekspor Data</p>
                                <small class="text-muted">Unduh laporan</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ app()->bound('csp-nonce') ? app('csp-nonce') : '' }}">
document.addEventListener('DOMContentLoaded', function() {
    // Add any specific data collector dashboard functionality here
    console.log('Data collector dashboard loaded');
});
</script>
@endpush
