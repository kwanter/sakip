@extends('layouts.app')

@section('title', 'Detail Indikator Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Indikator Kinerja</h1>
        <div>
            <a href="{{ route('indikator-kinerja.edit', $indikatorKinerja->id) }}" class="btn btn-warning btn-sm shadow-sm mr-2">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ route('indikator-kinerja.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Indikator Kinerja</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="{{ route('indikator-kinerja.edit', $indikatorKinerja->id) }}">
                                <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#deleteModal">
                                <i class="fas fa-trash fa-sm fa-fw mr-2 text-gray-400"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Nama Indikator:</label>
                                <p class="text-gray-800">{{ $indikatorKinerja->nama_indikator }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Target:</label>
                                <p class="text-gray-800">{{ number_format($indikatorKinerja->target, 2) }} {{ $indikatorKinerja->satuan }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Jenis Indikator:</label>
                                <span class="badge badge-{{ $indikatorKinerja->jenis == 'input' ? 'info' : ($indikatorKinerja->jenis == 'output' ? 'success' : ($indikatorKinerja->jenis == 'outcome' ? 'warning' : 'danger')) }} badge-pill">
                                    {{ ucfirst($indikatorKinerja->jenis) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Status:</label>
                                <span class="badge badge-{{ $indikatorKinerja->status == 'aktif' ? 'success' : 'secondary' }} badge-pill">
                                    {{ ucfirst($indikatorKinerja->status) }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Dibuat:</label>
                                <p class="text-gray-800">{{ $indikatorKinerja->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Terakhir Diupdate:</label>
                                <p class="text-gray-800">{{ $indikatorKinerja->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($indikatorKinerja->deskripsi)
                    <div class="mb-3">
                        <label class="font-weight-bold text-primary">Deskripsi:</label>
                        <p class="text-gray-800">{{ $indikatorKinerja->deskripsi }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tata Cara Perhitungan Indikator Kinerja -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tata Cara Perhitungan Indikator Kinerja</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Target:</label>
                                <p class="text-gray-800">{{ number_format($indikatorKinerja->target, 2) }} {{ $indikatorKinerja->satuan }}</p>
                            </div>
                        </div>
                        @if($indikatorKinerja->input)
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Input:</label>
                                <p class="text-gray-800">{{ number_format($indikatorKinerja->input, 2) }} {{ $indikatorKinerja->satuan }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-{{ $indikatorKinerja->input ? '4' : '8' }}">
                            <div class="mb-3">
                                <label class="font-weight-bold text-gray-800">Realisasi Saat Ini:</label>
                                @if($indikatorKinerja->laporanKinerjas->count() > 0)
                                    @php
                                        $totalRealisasi = $indikatorKinerja->laporanKinerjas->sum('realisasi');
                                    @endphp
                                    <p class="text-gray-800">{{ number_format($totalRealisasi, 2) }} {{ $indikatorKinerja->satuan }}</p>
                                @else
                                    <p class="text-gray-500">Belum ada data realisasi</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                    <h6 class="font-weight-bold text-gray-800">Formula Perhitungan:</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="mb-2">
                            <strong>Metode 1 - Berdasarkan Target:</strong><br>
                            <code class="text-dark">
                                Persentase Capaian = (Realisasi / Target) × 100%
                            </code>
                        </div>
                        <div>
                            <strong>Metode 2 - Berdasarkan Input:</strong><br>
                            <code class="text-dark">
                                Persentase Capaian = (Realisasi / Input) × 100%
                            </code>
                            <small class="text-muted d-block mt-1">
                                *Gunakan metode ini jika indikator mengukur efisiensi atau rasio antara input dan realisasi
                            </small>
                        </div>
                    </div>
                </div>
                            
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-gray-800">Contoh dengan Data Saat Ini:</h6>
                                <div class="bg-light p-3 rounded">
                                    <p class="mb-2"><strong>Target Saat Ini:</strong> {{ number_format($indikatorKinerja->target, 2) }} {{ $indikatorKinerja->satuan }}</p>
                                    @if($indikatorKinerja->input)
                                        <p class="mb-2"><strong>Input Saat Ini:</strong> {{ number_format($indikatorKinerja->input, 2) }} {{ $indikatorKinerja->satuan }}</p>
                                    @endif
                                    @if($indikatorKinerja->laporanKinerjas->count() > 0)
                                        @php
                                            $laporanTerbaru = $indikatorKinerja->laporanKinerjas->sortByDesc('periode_laporan')->first();
                                            $persentaseTarget = $indikatorKinerja->target > 0 ? ($laporanTerbaru->realisasi / $indikatorKinerja->target) * 100 : 0;
                                            $persentaseInput = $indikatorKinerja->input > 0 ? ($laporanTerbaru->realisasi / $indikatorKinerja->input) * 100 : 0;
                                        @endphp
                                        <p class="mb-2"><strong>Realisasi Terbaru:</strong> {{ number_format($laporanTerbaru->realisasi, 2) }} {{ $indikatorKinerja->satuan }}</p>
                                        
                                        <div class="mb-3">
                                            <strong>Metode 1 - Berdasarkan Target:</strong><br>
                                            <small>Perhitungan: ({{ number_format($laporanTerbaru->realisasi, 2) }} / {{ number_format($indikatorKinerja->target, 2) }}) × 100% = {{ number_format($persentaseTarget, 2) }}%</small>
                                            <div class="progress mt-1" style="height: 15px;">
                                                <div class="progress-bar bg-{{ $persentaseTarget >= 100 ? 'success' : ($persentaseTarget >= 75 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min($persentaseTarget, 100) }}%" 
                                                     aria-valuenow="{{ $persentaseTarget }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ number_format($persentaseTarget, 1) }}%
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($indikatorKinerja->input)
                                        <div class="mb-3">
                                            <strong>Metode 2 - Berdasarkan Input:</strong><br>
                                            <small>Perhitungan: ({{ number_format($laporanTerbaru->realisasi, 2) }} / {{ number_format($indikatorKinerja->input, 2) }}) × 100% = {{ number_format($persentaseInput, 2) }}%</small>
                                            <div class="progress mt-1" style="height: 15px;">
                                                <div class="progress-bar bg-{{ $persentaseInput >= 100 ? 'success' : ($persentaseInput >= 75 ? 'warning' : 'info') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min($persentaseInput, 100) }}%" 
                                                     aria-valuenow="{{ $persentaseInput }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ number_format($persentaseInput, 1) }}%
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @else
                                        <p class="text-muted">Belum ada data realisasi</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-gray-800">Kategori Penilaian:</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center p-2 bg-success text-white rounded mb-2">
                                            <strong>Sangat Baik</strong><br>
                                            <small>≥ 100%</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-2 bg-warning text-white rounded mb-2">
                                            <strong>Baik</strong><br>
                                            <small>75% - 99%</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-2 bg-info text-white rounded mb-2">
                                            <strong>Cukup</strong><br>
                                            <small>50% - 74%</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-2 bg-danger text-white rounded mb-2">
                                            <strong>Kurang</strong><br>
                                            <small>< 50%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Catatan Penting:</h6>
                                <ul class="mb-0">
                                    <li>Perhitungan dilakukan berdasarkan target yang telah ditetapkan</li>
                                    <li>Realisasi dapat melebihi 100% jika pencapaian melebihi target</li>
                                    <li>Untuk indikator jenis <strong>{{ ucfirst($indikatorKinerja->jenis) }}</strong>, fokus pada {{ $indikatorKinerja->jenis == 'input' ? 'penggunaan sumber daya' : ($indikatorKinerja->jenis == 'output' ? 'hasil langsung kegiatan' : ($indikatorKinerja->jenis == 'outcome' ? 'manfaat jangka menengah' : 'dampak jangka panjang')) }}</li>
                                    <li>Laporan kinerja harus diisi secara berkala sesuai periode yang ditentukan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informasi Kegiatan Terkait -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kegiatan Terkait</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Kegiatan:</label>
                                <p class="text-gray-800">
                                    <a href="{{ route('kegiatan.show', $indikatorKinerja->kegiatan->id) }}" class="text-decoration-none">
                                        {{ $indikatorKinerja->kegiatan->nama_kegiatan }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Program:</label>
                                <p class="text-gray-800">
                                    <a href="{{ route('program.show', $indikatorKinerja->kegiatan->program->id) }}" class="text-decoration-none">
                                        {{ $indikatorKinerja->kegiatan->program->nama_program }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Instansi:</label>
                                <p class="text-gray-800">
                                    <a href="{{ route('instansi.show', $indikatorKinerja->kegiatan->program->instansi->id) }}" class="text-decoration-none">
                                        {{ $indikatorKinerja->kegiatan->program->instansi->nama_instansi }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="font-weight-bold text-primary">Periode Kegiatan:</label>
                                <p class="text-gray-800">{{ $indikatorKinerja->kegiatan->tanggal_mulai->format('d/m/Y') }} - {{ $indikatorKinerja->kegiatan->tanggal_selesai->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Reports List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Laporan Kinerja</h6>
                    <a href="{{ route('laporan-kinerja.create', ['indikator_kinerja_id' => $indikatorKinerja->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Laporan
                    </a>
                </div>
                <div class="card-body">
                    @if($indikatorKinerja->laporanKinerjas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Periode</th>
                                        <th>Realisasi</th>
                                        <th>Persentase</th>
                                        <th>Status</th>
                                        <th>Tanggal Lapor</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($indikatorKinerja->laporanKinerjas->sortByDesc('periode_laporan') as $laporan)
                                    <tr>
                                        <td>{{ $laporan->periode_laporan }}</td>
                                        <td>{{ number_format($laporan->realisasi, 2) }} {{ $indikatorKinerja->satuan }}</td>
                                        <td>
                                            @php
                                                $persentase = $indikatorKinerja->target > 0 ? ($laporan->realisasi / $indikatorKinerja->target) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $persentase >= 100 ? 'success' : ($persentase >= 75 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" style="width: {{ min($persentase, 100) }}%">
                                                    {{ number_format($persentase, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $laporan->status == 'final' ? 'success' : 'warning' }} badge-pill">
                                                {{ ucfirst($laporan->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $laporan->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="#" class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Belum ada laporan kinerja untuk indikator ini</p>
                            <a href="{{ route('laporan-kinerja.create', ['indikator_kinerja_id' => $indikatorKinerja->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Buat Laporan Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik</h6>
                </div>
                <div class="card-body">
                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Laporan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $indikatorKinerja->laporanKinerjas->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    
                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Laporan Final</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $indikatorKinerja->laporanKinerjas->where('status', 'final')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    
                    @if($indikatorKinerja->laporanKinerjas->count() > 0)
                    @php
                        $laporanTerbaru = $indikatorKinerja->laporanKinerjas->sortByDesc('periode_laporan')->first();
                        $persentaseTerbaru = $indikatorKinerja->target > 0 ? ($laporanTerbaru->realisasi / $indikatorKinerja->target) * 100 : 0;
                    @endphp
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pencapaian Terbaru</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ number_format($persentaseTerbaru, 1) }}%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-{{ $persentaseTerbaru >= 100 ? 'success' : ($persentaseTerbaru >= 75 ? 'warning' : 'danger') }}" 
                                             role="progressbar" style="width: {{ min($persentaseTerbaru, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('laporan-kinerja.create', ['indikator_kinerja_id' => $indikatorKinerja->id]) }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-plus"></i> Tambah Laporan Kinerja
                    </a>
                    <a href="{{ route('kegiatan.show', $indikatorKinerja->kegiatan->id) }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-eye"></i> Lihat Kegiatan
                    </a>
                    <a href="{{ route('indikator-kinerja.edit', $indikatorKinerja->id) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Indikator
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-block dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Trend -->
            @if($indikatorKinerja->laporanKinerjas->count() > 1)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tren Kinerja</h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" width="100%" height="60"></canvas>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus indikator kinerja <strong>{{ $indikatorKinerja->nama_indikator }}</strong>?</p>
                @if($indikatorKinerja->laporanKinerjas->count() > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Indikator ini memiliki {{ $indikatorKinerja->laporanKinerjas->count() }} laporan kinerja yang akan ikut terhapus.
                </div>
                @endif
                <p class="text-muted">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="{{ route('indikator-kinerja.destroy', $indikatorKinerja->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($indikatorKinerja->laporanKinerjas->count() > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Trend Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const laporanData = @json($indikatorKinerja->laporanKinerjas->sortBy('periode_laporan')->values());
const target = {{ $indikatorKinerja->target }};

const labels = laporanData.map(laporan => laporan.periode_laporan);
const realisasiData = laporanData.map(laporan => laporan.realisasi);
const persentaseData = laporanData.map(laporan => target > 0 ? (laporan.realisasi / target) * 100 : 0);

const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Realisasi',
            data: realisasiData,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            yAxisID: 'y'
        }, {
            label: 'Persentase (%)',
            data: persentaseData,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Periode'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Realisasi ({{ $indikatorKinerja->satuan }})'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Persentase (%)'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            }
        }
    }
});
</script>
@endif
@endpush