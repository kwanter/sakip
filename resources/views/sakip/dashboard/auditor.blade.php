@extends('layouts.app')

@section('title', 'Dashboard Auditor - SAKIP')

@section('content')
<div class="sakip-dashboard-auditor">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm-12">
                <h1 class="page-title">Dashboard Auditor</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard Auditor</li>
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
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">142</h3>
                        <p class="stat-label">Audit Aktif</p>
                        <small class="stat-change text-warning">
                            <i class="fas fa-clock"></i> 23 perlu perhatian
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card stat-card-success">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">89</h3>
                        <p class="stat-label">Terverifikasi</p>
                        <small class="stat-change text-success">
                            <i class="fas fa-arrow-up"></i> +15 bulan ini
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card stat-card-warning">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">34</h3>
                        <p class="stat-label">Temuan Audit</p>
                        <small class="stat-change text-danger">
                            <i class="fas fa-exclamation-circle"></i> 8 kritis
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card stat-card-info">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">87%</h3>
                        <p class="stat-label">Tingkat Kepatuhan</p>
                        <small class="stat-change text-info">
                            <i class="fas fa-chart-line"></i> +2% triwulan ini
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Audit Trail Overview -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> Audit Trail Terbaru
                    </h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshAuditTrail()">
                            <i class="fas fa-sync-alt"></i> Segarkan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="audit-filters mb-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="auditTypeFilter">
                                    <option value="">Semua Tipe</option>
                                    <option value="create">Pembuatan</option>
                                    <option value="update">Perubahan</option>
                                    <option value="delete">Penghapusan</option>
                                    <option value="login">Login</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="auditUserFilter">
                                    <option value="">Semua Pengguna</option>
                                    <option value="admin">Administrator</option>
                                    <option value="collector">Pengumpul Data</option>
                                    <option value="assessor">Penilai</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control form-control-sm" id="auditDateFilter">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-sm btn-primary w-100" onclick="applyAuditFilters()">
                                    <i class="fas fa-filter"></i> Terapkan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="audit-timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">Data Indikator Diperbarui</h6>
                                    <small class="timeline-time">2 menit yang lalu</small>
                                </div>
                                <p class="timeline-desc">IKU-001: Capaian Kinerja Utama - Dinas Kesehatan</p>
                                <div class="timeline-meta">
                                    <span class="badge bg-primary">Pengumpulan Data</span>
                                    <span class="text-muted">oleh: Budi Santoso</span>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">Penilaian Disetujui</h6>
                                    <small class="timeline-time">15 menit yang lalu</small>
                                </div>
                                <p class="timeline-desc">IKU-002: Efisiensi Anggaran - Dinas Pendidikan</p>
                                <div class="timeline-meta">
                                    <span class="badge bg-info">Penilaian</span>
                                    <span class="text-muted">oleh: Dr. Ani Widyawati</span>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">Login Sistem</h6>
                                    <small class="timeline-time">1 jam yang lalu</small>
                                </div>
                                <p class="timeline-desc">User login dari IP: 192.168.1.100</p>
                                <div class="timeline-meta">
                                    <span class="badge bg-secondary">Autentikasi</span>
                                    <span class="text-muted">oleh: admin@sakip</span>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">Data Dihapus</h6>
                                    <small class="timeline-time">2 jam yang lalu</small>
                                </div>
                                <p class="timeline-desc">Lampiran dokumen: bukti_kinerja_q1.pdf</p>
                                <div class="timeline-meta">
                                    <span class="badge bg-danger">Penghapusan</span>
                                    <span class="text-muted">oleh: Pengumpul Data</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Menampilkan 4 dari 1,247 aktivitas</small>
                        <a href="{{ route('sakip.audit.index') }}" class="btn btn-sm btn-outline-primary">
                            Lihat Semua <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Compliance Status -->
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
                        <button class="btn btn-primary" onclick="generateComplianceReport()">
                            <i class="fas fa-file-alt"></i> Laporan Kepatuhan
                        </button>
                        <button class="btn btn-outline-primary" onclick="exportAuditLog()">
                            <i class="fas fa-download"></i> Export Audit Log
                        </button>
                        <button class="btn btn-outline-warning" onclick="scheduleAudit()">
                            <i class="fas fa-calendar-plus"></i> Jadwalkan Audit
                        </button>
                        <button class="btn btn-outline-info" onclick="viewComplianceMatrix()">
                            <i class="fas fa-table"></i> Matriks Kepatuhan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Compliance Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt"></i> Status Kepatuhan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="compliance-items">
                        <div class="compliance-item">
                            <div class="compliance-label">Keamanan Data</div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 95%"></div>
                            </div>
                            <small class="text-muted">95% - Sangat Baik</small>
                        </div>

                        <div class="compliance-item">
                            <div class="compliance-label">Audit Trail</div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 88%"></div>
                            </div>
                            <small class="text-muted">88% - Baik</small>
                        </div>

                        <div class="compliance-item">
                            <div class="compliance-label">Akses Pengguna</div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 75%"></div>
                            </div>
                            <small class="text-muted">75% - Perlu Perhatian</small>
                        </div>

                        <div class="compliance-item">
                            <div class="compliance-label">Retensi Data</div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 92%"></div>
                            </div>
                            <small class="text-muted">92% - Sangat Baik</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Critical Alerts -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle"></i> Peringatan Kritis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert-list">
                        <div class="alert alert-danger alert-sm">
                            <strong>Login Gagal Berulang</strong><br>
                            <small>5 percobaan gagal dari user: john.doe</small>
                            <div class="alert-time">5 menit yang lalu</div>
                        </div>

                        <div class="alert alert-warning alert-sm">
                            <strong>Perubahan Data Sensitif</strong><br>
                            <small>IKU master diubah tanpa otorisasi</small>
                            <div class="alert-time">30 menit yang lalu</div>
                        </div>

                        <div class="alert alert-info alert-sm">
                            <strong>Akses dari IP Baru</strong><br>
                            <small>Login dari IP: 203.78.12.45</small>
                            <div class="alert-time">1 jam yang lalu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Findings Chart -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i> Kategori Temuan Audit
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="auditFindingsChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Tren Audit Bulanan
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="auditTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Detail Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditDetailModalLabel">Detail Audit Trail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="auditDetailContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="exportAuditDetail()">Export Detail</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Auditor Dashboard Styles */
.sakip-dashboard-auditor {
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

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, var(--sakip-warning), var(--sakip-warning-dark));
}

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, var(--sakip-info), var(--sakip-info-dark));
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

/* Audit Timeline */
.audit-timeline {
    position: relative;
    padding-left: 30px;
}

.audit-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--sakip-border);
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.timeline-content {
    background: var(--sakip-light);
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid var(--sakip-primary);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.timeline-title {
    color: var(--sakip-primary-dark);
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0;
}

.timeline-time {
    color: var(--sakip-muted);
    font-size: 0.8rem;
    white-space: nowrap;
}

.timeline-desc {
    color: var(--sakip-secondary);
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.timeline-meta {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Compliance Items */
.compliance-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.compliance-item {
    padding: 15px;
    background: var(--sakip-light);
    border-radius: 8px;
    border-left: 3px solid var(--sakip-primary);
}

.compliance-label {
    font-weight: 600;
    color: var(--sakip-primary-dark);
    margin-bottom: 8px;
}

/* Alert List */
.alert-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.alert-sm {
    padding: 10px 15px;
    margin-bottom: 0;
    font-size: 0.9rem;
}

.alert-time {
    font-size: 0.75rem;
    color: inherit;
    opacity: 0.8;
    margin-top: 5px;
}

/* Audit Filters */
.audit-filters {
    background: var(--sakip-light);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
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
.btn {
    font-weight: 500;
}

.btn-primary {
    background: var(--sakip-primary);
    border-color: var(--sakip-primary);
}

.btn-primary:hover {
    background: var(--sakip-primary-dark);
    border-color: var(--sakip-primary-dark);
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

    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .audit-filters .row > div {
        margin-bottom: 10px;
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

    .audit-timeline::before {
        background: #ddd !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Audit Findings Chart
const auditFindingsCtx = document.getElementById('auditFindingsChart').getContext('2d');
const auditFindingsChart = new Chart(auditFindingsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Keamanan Data', 'Akses Pengguna', 'Audit Trail', 'Retensi Data', 'Lainnya'],
        datasets: [{
            data: [12, 8, 6, 4, 4],
            backgroundColor: [
                '#dc3545',
                '#ffc107',
                '#17a2b8',
                '#28a745',
                '#6c757d'
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

// Audit Trend Chart
const auditTrendCtx = document.getElementById('auditTrendChart').getContext('2d');
const auditTrendChart = new Chart(auditTrendCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        datasets: [{
            label: 'Audit Selesai',
            data: [25, 32, 28, 35, 42, 38],
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Temuan Audit',
            data: [8, 12, 6, 15, 9, 11],
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
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
                text: 'Tren Audit 6 Bulan Terakhir'
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
function refreshAuditTrail() {
    showLoading('audit-timeline');

    // Simulate API call
    setTimeout(() => {
        hideLoading('audit-timeline');
        showToast('Audit trail telah disegarkan', 'success');
    }, 1000);
}

function applyAuditFilters() {
    const typeFilter = document.getElementById('auditTypeFilter').value;
    const userFilter = document.getElementById('auditUserFilter').value;
    const dateFilter = document.getElementById('auditDateFilter').value;

    showLoading('audit-timeline');

    // Simulate filtering
    setTimeout(() => {
        hideLoading('audit-timeline');
        showToast('Filter diterapkan', 'info');
    }, 800);
}

function generateComplianceReport() {
    showToast('Laporan kepatuhan sedang dibuat...', 'info');

    // Simulate report generation
    setTimeout(() => {
        showToast('Laporan kepatuhan berhasil dibuat', 'success');
    }, 2000);
}

function exportAuditLog() {
    showToast('Audit log sedang diekspor...', 'info');

    // Simulate export
    setTimeout(() => {
        showToast('Audit log berhasil diekspor', 'success');
    }, 1500);
}

function scheduleAudit() {
    showToast('Fitur penjadwalan audit akan segera tersedia', 'info');
}

function viewComplianceMatrix() {
    showToast('Matriks kepatuhan akan ditampilkan', 'info');
}

function viewAuditDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('auditDetailModal'));

    // Simulate loading audit detail
    document.getElementById('auditDetailContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat detail audit trail...</p>
        </div>
    `;

    // Simulate API call
    setTimeout(() => {
        document.getElementById('auditDetailContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informasi Aktivitas</h6>
                    <table class="table table-sm">
                        <tr><td>Tipe</td><td>Update Data</td></tr>
                        <tr><td>Tabel</td><td>indicators</td></tr>
                        <tr><td>ID Record</td><td>123</td></tr>
                        <tr><td>Waktu</td><td>2024-01-15 14:30:25</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Informasi Pengguna</h6>
                    <table class="table table-sm">
                        <tr><td>User</td><td>budi.santoso</td></tr>
                        <tr><td>Role</td><td>Pengumpul Data</td></tr>
                        <tr><td>IP Address</td><td>192.168.1.100</td></tr>
                        <tr><td>User Agent</td><td>Chrome/120.0.0.0</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6>Perubahan Data</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Nilai Lama</th>
                                <th>Nilai Baru</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>target_value</td>
                                <td>85</td>
                                <td>90</td>
                            </tr>
                            <tr>
                                <td>updated_at</td>
                                <td>2024-01-14 10:20:15</td>
                                <td>2024-01-15 14:30:25</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }, 500);

    modal.show();
}

function exportAuditDetail() {
    showToast('Detail audit sedang diekspor...', 'info');

    // Simulate export
    setTimeout(() => {
        showToast('Detail audit berhasil diekspor', 'success');
    }, 1000);
}

function showLoading(elementClass) {
    const elements = document.querySelectorAll('.' + elementClass);
    elements.forEach(element => {
        element.style.opacity = '0.5';
        element.style.pointerEvents = 'none';
    });
}

function hideLoading(elementClass) {
    const elements = document.querySelectorAll('.' + elementClass);
    elements.forEach(element => {
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
    });
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

// Initialize functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as default for date filter
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('auditDateFilter').value = today;

    console.log('Auditor dashboard loaded');
});
</script>
@endpush
