@extends('layouts.app')

@section('title', 'Dashboard SAKIP')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Dashboard SAKIP</h1>
            <p class="mb-0">Sistem Akuntabilitas Kinerja Instansi Pemerintah</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Instansi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_instansi'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Program
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_program'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Kegiatan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_kegiatan'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Laporan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_laporan'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Program by Instansi Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Program per Instansi</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="programChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kegiatan Status Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Status Kegiatan</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports and Top Indicators -->
    <div class="row">
        <!-- Recent Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Laporan Terbaru</h6>
                </div>
                <div class="card-body">
                    @if($laporanTerbaru->count() > 0)
                        @foreach($laporanTerbaru as $laporan)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-file-alt text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small text-gray-500">{{ $laporan->created_at->format('d M Y') }}</div>
                                <div class="font-weight-bold">{{ $laporan->indikatorKinerja->nama_indikator }}</div>
                                <div class="small">{{ $laporan->indikatorKinerja->kegiatan->nama_kegiatan }}</div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $laporan->status_verifikasi == 'terverifikasi' ? 'success' : 'warning' }}">
                                    {{ ucfirst($laporan->status_verifikasi) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">Belum ada laporan.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Indicators -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Indikator Kinerja Terbaik</h6>
                </div>
                <div class="card-body">
                    @if($indikatorTerbaik->count() > 0)
                        @foreach($indikatorTerbaik as $indikator)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <div class="mr-3">
                                <div class="icon-circle bg-success">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $indikator->nama_indikator }}</div>
                                <div class="small text-gray-500">{{ $indikator->kegiatan->nama_kegiatan }}</div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ min($indikator->persentase, 100) }}%"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-weight-bold text-success">{{ number_format($indikator->persentase, 1) }}%</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">Belum ada data indikator.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Program Chart
const programCtx = document.getElementById('programChart').getContext('2d');
const programChart = new Chart(programCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($programByInstansi->pluck('nama_instansi')) !!},
        datasets: [{
            label: 'Jumlah Program',
            data: {!! json_encode($programByInstansi->pluck('programs_count')) !!},
            backgroundColor: 'rgba(78, 115, 223, 0.8)',
            borderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($kegiatanByStatus->pluck('status')) !!},
        datasets: [{
            data: {!! json_encode($kegiatanByStatus->pluck('total')) !!},
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    },
});
</script>
@endpush