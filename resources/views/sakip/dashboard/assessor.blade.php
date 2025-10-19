@extends('layouts.app')

@section('title', 'Dashboard Penilai - SAKIP')

@section('content')
<div class="sakip-dashboard-assessor">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm-12">
                <h1 class="page-title">Dashboard Penilai</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard Penilai</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card stat-card-primary">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">24</h3>
                        <p class="stat-label">Menunggu Penilaian</p>
                        <small class="stat-change text-warning">
                            <i class="fas fa-clock"></i> 8 baru hari ini
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card stat-card-success">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">156</h3>
                        <p class="stat-label">Telah Dinilai</p>
                        <small class="stat-change text-success">
                            <i class="fas fa-arrow-up"></i> +12 minggu ini
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card stat-card-info">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-redo"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">8</h3>
                        <p class="stat-label">Perlu Revisi</p>
                        <small class="stat-change text-danger">
                            <i class="fas fa-exclamation-triangle"></i> 3 mendesak
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card stat-card-warning">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">4.2</h3>
                        <p class="stat-label">Rata-rata Nilai</p>
                        <small class="stat-change text-info">
                            <i class="fas fa-chart-line"></i> Bulan ini
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Assessment Queue -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks"></i> Antrian Penilaian
                    </h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshAssessmentQueue()">
                            <i class="fas fa-sync-alt"></i> Segarkan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="assessmentQueueTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Indikator Kinerja</th>
                                    <th width="15%">Unit Kerja</th>
                                    <th width="10%">Periode</th>
                                    <th width="10%">Tgl Pengajuan</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Prioritas</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <strong>Capaian Kinerja Utama</strong><br>
                                        <small class="text-muted">IKU-001</small>
                                    </td>
                                    <td>Dinas Kesehatan</td>
                                    <td>Q1 2024</td>
                                    <td>15 Jan 2024</td>
                                    <td>
                                        <span class="badge bg-warning">Menunggu</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Tinggi</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('sakip.assessments.review', 1) }}" class="btn btn-primary btn-sm" title="Mulai Penilaian">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <button class="btn btn-outline-secondary btn-sm" title="Lihat Detail" onclick="viewAssessmentDetail(1)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>
                                        <strong>Efisiensi Anggaran</strong><br>
                                        <small class="text-muted">IKU-002</small>
                                    </td>
                                    <td>Dinas Pendidikan</td>
                                    <td>Q1 2024</td>
                                    <td>14 Jan 2024</td>
                                    <td>
                                        <span class="badge bg-warning">Menunggu</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Sedang</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('sakip.assessments.review', 2) }}" class="btn btn-primary btn-sm" title="Mulai Penilaian">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <button class="btn btn-outline-secondary btn-sm" title="Lihat Detail" onclick="viewAssessmentDetail(2)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>
                                        <strong>Kepuasan Masyarakat</strong><br>
                                        <small class="text-muted">IKU-003</small>
                                    </td>
                                    <td>Dinas Sosial</td>
                                    <td>Q4 2023</td>
                                    <td>12 Jan 2024</td>
                                    <td>
                                        <span class="badge bg-info">Dalam Proses</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Rendah</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('sakip.assessments.review', 3) }}" class="btn btn-primary btn-sm" title="Lanjutkan Penilaian">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <button class="btn btn-outline-secondary btn-sm" title="Lihat Detail" onclick="viewAssessmentDetail(3)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Menampilkan 3 dari 24 penilaian menunggu</small>
                        <a href="{{ route('sakip.assessments.index') }}" class="btn btn-sm btn-outline-primary">
                            Lihat Semua <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Info -->
        <div class="col-lg-4 mb-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Aksi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sakip.assessments.index') }}" class="btn btn-primary">
                            <i class="fas fa-tasks"></i> Lihat Semua Penilaian
                        </a>
                        <button class="btn btn-outline-primary" onclick="showAssessmentGuidelines()">
                            <i class="fas fa-book"></i> Panduan Penilaian
                        </button>
                        <button class="btn btn-outline-secondary" onclick="showAssessmentCriteria()">
                            <i class="fas fa-list"></i> Kriteria Penilaian
                        </button>
                    </div>
                </div>
            </div>

            <!-- Assessment Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i> Statistik Penilaian
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="assessmentStatsChart" width="200" height="200"></canvas>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> Aktivitas Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title">Penilaian selesai</p>
                                <p class="activity-desc">IKU-005 - Dinas Perhubungan</p>
                                <small class="activity-time">2 jam yang lalu</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-primary">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title">Mulai penilaian</p>
                                <p class="activity-desc">IKU-003 - Dinas Sosial</p>
                                <small class="activity-time">5 jam yang lalu</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title">Penilaian ditunda</p>
                                <p class="activity-desc">IKU-007 - Dinas PU</p>
                                <small class="activity-time">1 hari yang lalu</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Performance Chart -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Performa Penilaian
                    </h5>
                    <div class="card-tools">
                        <select class="form-select form-select-sm" id="performancePeriod" onchange="updatePerformanceChart()">
                            <option value="week">Minggu Ini</option>
                            <option value="month" selected>Bulan Ini</option>
                            <option value="quarter">Triwulan Ini</option>
                            <option value="year">Tahun Ini</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assessment Detail Modal -->
<div class="modal fade" id="assessmentDetailModal" tabindex="-1" aria-labelledby="assessmentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assessmentDetailModalLabel">Detail Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="assessmentDetailContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" class="btn btn-primary" id="startAssessmentBtn">Mulai Penilaian</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Assessor Dashboard Styles */
.sakip-dashboard-assessor {
    padding: 20px 0;
}

.page-header {
    background: var(--sakip-primary-light);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border-left: 4px solid var(--sakip-primary);
}

.page-title {
    color: var(--sakip-primary-dark);
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 5px;
}

/* Stat Cards */
.stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.stat-card .card-body {
    padding: 20px;
    display: flex;
    align-items: center;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-right: 15px;
    flex-shrink: 0;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, var(--sakip-primary), var(--sakip-primary-dark));
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, var(--sakip-success), var(--sakip-success-dark));
}

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, var(--sakip-info), var(--sakip-info-dark));
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, var(--sakip-warning), var(--sakip-warning-dark));
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0;
    color: var(--sakip-dark);
}

.stat-label {
    margin-bottom: 5px;
    color: var(--sakip-secondary);
    font-size: 0.9rem;
}

.stat-change {
    font-size: 0.8rem;
}

/* Table Styles */
.table-responsive {
    border-radius: 8px;
}

.table th {
    background: var(--sakip-light);
    color: var(--sakip-primary-dark);
    font-weight: 600;
    border: none;
    padding: 12px 8px;
    font-size: 0.85rem;
}

.table td {
    padding: 10px 8px;
    vertical-align: middle;
    border-color: var(--sakip-border);
}

.table-hover tbody tr:hover {
    background-color: var(--sakip-primary-light);
}

/* Activity List */
.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 15px 0;
    border-bottom: 1px solid var(--sakip-border);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    margin-right: 12px;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    margin-bottom: 2px;
    color: var(--sakip-dark);
}

.activity-desc {
    color: var(--sakip-secondary);
    margin-bottom: 2px;
    font-size: 0.9rem;
}

.activity-time {
    color: var(--sakip-muted);
    font-size: 0.8rem;
}

/* Card Styles */
.card {
    border: 1px solid var(--sakip-border);
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
    background: var(--sakip-light);
    border-bottom: 1px solid var(--sakip-border);
    border-radius: 12px 12px 0 0 !important;
    padding: 15px 20px;
}

.card-title {
    color: var(--sakip-primary-dark);
    font-weight: 600;
}

/* Buttons */
.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.btn-primary {
    background: var(--sakip-primary);
    border-color: var(--sakip-primary);
}

.btn-primary:hover {
    background: var(--sakip-primary-dark);
    border-color: var(--sakip-primary-dark);
}

/* Badges */
.badge {
    padding: 0.4em 0.6em;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.5rem;
    }

    .stat-card .card-body {
        flex-direction: column;
        text-align: center;
    }

    .stat-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }

    .table-responsive {
        font-size: 0.8rem;
    }

    .btn-group-sm > .btn, .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
}

@media print {
    .card-tools,
    .btn-group,
    .btn {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Assessment Statistics Chart
const assessmentStatsCtx = document.getElementById('assessmentStatsChart').getContext('2d');
const assessmentStatsChart = new Chart(assessmentStatsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Menunggu', 'Dalam Proses', 'Selesai', 'Revisi'],
        datasets: [{
            data: [24, 8, 156, 8],
            backgroundColor: [
                '#ffc107',
                '#17a2b8',
                '#28a745',
                '#dc3545'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

// Performance Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Penilaian Selesai',
            data: [12, 15, 18, 22, 25, 28, 32, 35, 38, 42, 45, 48],
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Rata-rata Nilai',
            data: [3.8, 3.9, 4.0, 4.1, 4.2, 4.1, 4.3, 4.2, 4.4, 4.3, 4.5, 4.2],
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Performa Penilaian Bulanan'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Functions
function refreshAssessmentQueue() {
    showLoading('assessmentQueueTable');

    // Simulate API call
    setTimeout(() => {
        hideLoading('assessmentQueueTable');
        showToast('Antrian penilaian telah disegarkan', 'success');
    }, 1000);
}

function viewAssessmentDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('assessmentDetailModal'));

    // Simulate loading assessment detail
    document.getElementById('assessmentDetailContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat detail penilaian...</p>
        </div>
    `;

    // Simulate API call
    setTimeout(() => {
        document.getElementById('assessmentDetailContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informasi Indikator</h6>
                    <table class="table table-sm">
                        <tr><td>Kode</td><td>IKU-00${id}</td></tr>
                        <tr><td>Nama</td><td>Indikator Kinerja Utama</td></tr>
                        <tr><td>Unit Kerja</td><td>Dinas Kesehatan</td></tr>
                        <tr><td>Periode</td><td>Q1 2024</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Status Penilaian</h6>
                    <table class="table table-sm">
                        <tr><td>Status</td><td><span class="badge bg-warning">Menunggu</span></td></tr>
                        <tr><td>Tgl Pengajuan</td><td>15 Jan 2024</td></tr>
                        <tr><td>Prioritas</td><td><span class="badge bg-danger">Tinggi</span></td></tr>
                        <tr><td>Catatan</td><td>Evaluasi capaian target kinerja</td></tr>
                    </table>
                </div>
            </div>
        `;

        document.getElementById('startAssessmentBtn').href = `/sakip/assessments/review/${id}`;
    }, 500);

    modal.show();
}

function updatePerformanceChart() {
    const period = document.getElementById('performancePeriod').value;

    // Simulate data update based on period
    let newData, newLabels;

    switch(period) {
        case 'week':
            newLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            newData = [5, 8, 6, 9, 7, 4, 3];
            break;
        case 'month':
            newLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
            newData = [25, 32, 28, 35];
            break;
        case 'quarter':
            newLabels = ['Jan', 'Feb', 'Mar'];
            newData = [85, 92, 78];
            break;
        default:
            return;
    }

    performanceChart.data.labels = newLabels;
    performanceChart.data.datasets[0].data = newData;
    performanceChart.update();
}

function showAssessmentGuidelines() {
    showToast('Panduan penilaian akan ditampilkan', 'info');
}

function showAssessmentCriteria() {
    showToast('Kriteria penilaian akan ditampilkan', 'info');
}

function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.opacity = '0.5';
        element.style.pointerEvents = 'none';
    }
}

function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
    }
}

function showToast(message, type = 'info') {
    // Create toast element
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    // Add toast container if not exists
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    const container = document.querySelector('.toast-container');
    container.insertAdjacentHTML('beforeend', toastHtml);

    const toast = new bootstrap.Toast(container.lastElementChild);
    toast.show();

    // Remove toast element after hiding
    container.lastElementChild.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Initialize DataTable
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any additional functionality here
    console.log('Assessor dashboard loaded');
});
</script>
@endpush
