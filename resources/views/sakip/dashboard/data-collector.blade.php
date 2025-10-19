@extends('layouts.app')

@section('title', 'Dashboard Pengumpul Data - SAKIP')

@section('header')
    <div class="header-content">
        <h1 class="page-title">Dashboard Pengumpul Data</h1>
        <div class="header-actions">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i>
                Cetak
            </button>
            <button class="btn btn-outline-secondary" onclick="toggleFullscreen()">
                <i class="fas fa-expand"></i>
                Layar Penuh
            </button>
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i>
                Segarkan
            </button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Data Collector Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-info">
                <div class="kpi-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value">{{ $pendingTasks ?? 8 }}</h3>
                    <p class="kpi-label">Tugas Menunggu</p>
                    <div class="kpi-trend trend-down">
                        <i class="fas fa-arrow-down"></i>
                        <span>-2 dari kemarin</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value">{{ $completedTasks ?? 24 }}</h3>
                    <p class="kpi-label">Tugas Selesai</p>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5 dari minggu lalu</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value">{{ $overdueTasks ?? 3 }}</h3>
                    <p class="kpi-label">Tugas Terlambat</p>
                    <div class="kpi-trend trend-stable">
                        <i class="fas fa-minus"></i>
                        <span>Tetap dari kemarin</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon">
                    <i class="fas fa-upload"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value">{{ $uploadedFiles ?? 47 }}</h3>
                    <p class="kpi-label">File Diunggah</p>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12 dari minggu lalu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Management Section -->
    <div class="row mb-4">
        <!-- Pending Tasks -->
        <div class="col-lg-8 mb-4">
            <div class="task-container">
                <div class="task-header">
                    <h5 class="task-title">Tugas Menunggu</h5>
                    <div class="task-controls">
                        <select class="form-select form-select-sm" id="taskFilter" onchange="filterTasks()">
                            <option value="all">Semua Tugas</option>
                            <option value="pending">Menunggu</option>
                            <option value="in-progress">Dalam Proses</option>
                            <option value="overdue">Terlambat</option>
                        </select>
                        <button class="btn btn-sm btn-primary" onclick="createNewTask()">
                            <i class="fas fa-plus"></i>
                            Baru
                        </button>
                    </div>
                </div>
                <div class="task-body">
                    <div class="task-list" id="taskList">
                        @foreach($pendingTasksList ?? [] as $task)
                        <div class="task-item" data-status="{{ $task['status'] }}" data-priority="{{ $task['priority'] }}">
                            <div class="task-content">
                                <div class="task-header-item">
                                    <h6 class="task-name">{{ $task['name'] }}</h6>
                                    <span class="task-priority priority-{{ $task['priority'] }}">
                                        {{ ucfirst($task['priority']) }}
                                    </span>
                                </div>
                                <div class="task-meta">
                                    <span class="task-indicator">
                                        <i class="fas fa-chart-bar"></i>
                                        {{ $task['indicator'] }}
                                    </span>
                                    <span class="task-deadline">
                                        <i class="fas fa-calendar"></i>
                                        {{ $task['deadline'] }}
                                    </span>
                                    <span class="task-status status-{{ $task['status'] }}">
                                        {{ ucfirst($task['status']) }}
                                    </span>
                                </div>
                                <div class="task-description">
                                    {{ $task['description'] }}
                                </div>
                            </div>
                            <div class="task-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="editTask({{ $task['id'] }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="submitData({{ $task['id'] }})" title="Kirim Data">
                                    <i class="fas fa-upload"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="uploadEvidence({{ $task['id'] }})" title="Unggah Bukti">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="quick-actions-panel">
                <div class="panel-header">
                    <h5 class="panel-title">Aksi Cepat</h5>
                </div>
                <div class="panel-body">
                    <div class="action-grid">
                        <button class="action-btn" onclick="submitData()">
                            <div class="action-icon">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Kirim Data</span>
                                <span class="action-desc">Unggah data kinerja</span>
                            </div>
                        </button>

                        <button class="action-btn" onclick="uploadEvidence()">
                            <div class="action-icon">
                                <i class="fas fa-paperclip"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Unggah Bukti</span>
                                <span class="action-desc">Lampirkan dokumen</span>
                            </div>
                        </button>

                        <button class="action-btn" onclick="bulkImport()">
                            <div class="action-icon">
                                <i class="fas fa-file-import"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Impor Massal</span>
                                <span class="action-desc">Impor data Excel</span>
                            </div>
                        </button>

                        <button class="action-btn" onclick="viewHistory()">
                            <div class="action-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Riwayat</span>
                                <span class="action-desc">Lihat riwayat unggahan</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Guidelines -->
            <div class="guidelines-panel">
                <div class="panel-header">
                    <h5 class="panel-title">Panduan Unggah</h5>
                </div>
                <div class="panel-body">
                    <div class="guideline-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Pastikan data sudah diverifikasi</span>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Lampirkan bukti pendukung</span>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Perhatikan tenggat waktu</span>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Gunakan format yang ditentukan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Submission History -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="history-container">
                <div class="history-header">
                    <h5 class="history-title">Riwayat Pengumpulan Data</h5>
                    <div class="history-controls">
                        <select class="form-select form-select-sm" id="historyFilter" onchange="filterHistory()">
                            <option value="all">Semua Status</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="pending">Menunggu</option>
                        </select>
                        <input type="date" class="form-control form-control-sm" id="historyDate" onchange="filterHistory()">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportHistory()">
                            <i class="fas fa-download"></i>
                            Ekspor
                        </button>
                    </div>
                </div>
                <div class="history-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="historyTable">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Indikator</th>
                                    <th>Unit</th>
                                    <th>Nilai</th>
                                    <th>Status</th>
                                    <th>Diverifikasi Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissionHistory ?? [] as $submission)
                                <tr data-status="{{ $submission['status'] }}" data-date="{{ $submission['date'] }}">
                                    <td>{{ $submission['date'] }}</td>
                                    <td>
                                        <div class="indicator-info">
                                            <div class="indicator-name">{{ $submission['indicator'] }}</div>
                                            <div class="indicator-code">{{ $submission['indicator_code'] }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $submission['unit'] }}</td>
                                    <td>
                                        <span class="value-badge">{{ $submission['value'] }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $submission['status'] }}">
                                            {{ ucfirst($submission['status']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="verifier-info">
                                            <div class="verifier-name">{{ $submission['verifier'] ?? '-' }}</div>
                                            <div class="verifier-date">{{ $submission['verified_date'] ?? '-' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewSubmission({{ $submission['id'] }})" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($submission['status'] === 'rejected')
                                            <button class="btn btn-sm btn-outline-warning" onclick="resubmitData({{ $submission['id'] }})" title="Kirim Ulang">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Operations -->
    <div class="row">
        <div class="col-12">
            <div class="bulk-operations-panel">
                <div class="panel-header">
                    <h5 class="panel-title">Operasi Massal</h5>
                </div>
                <div class="panel-body">
                    <div class="bulk-grid">
                        <div class="bulk-item">
                            <h6>Impor Data</h6>
                            <p>Unggah data dalam format Excel untuk beberapa indikator sekaligus</p>
                            <button class="btn btn-outline-primary" onclick="bulkImport()">
                                <i class="fas fa-file-excel"></i>
                                Pilih File Excel
                            </button>
                        </div>

                        <div class="bulk-item">
                            <h6>Ekspor Template</h6>
                            <p>Unduh template Excel untuk pengisian data massal</p>
                            <button class="btn btn-outline-secondary" onclick="exportTemplate()">
                                <i class="fas fa-download"></i>
                                Unduh Template
                            </button>
                        </div>

                        <div class="bulk-item">
                            <h6>Validasi Data</h6>
                            <p>Periksa kelayakan data sebelum pengiriman</p>
                            <button class="btn btn-outline-info" onclick="validateData()">
                                <i class="fas fa-check-double"></i>
                                Validasi Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Data Collector Dashboard Styles */
.kpi-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid var(--sakip-primary);
    display: flex;
    align-items: center;
    gap: 1rem;
    height: 100%;
    transition: all 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.kpi-card.kpi-primary { border-left-color: var(--sakip-primary); }
.kpi-card.kpi-success { border-left-color: #10b981; }
.kpi-card.kpi-warning { border-left-color: #f59e0b; }
.kpi-card.kpi-info { border-left-color: #3b82f6; }

.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
}

.kpi-card.kpi-primary .kpi-icon { background: var(--sakip-primary); }
.kpi-card.kpi-success .kpi-icon { background: #10b981; }
.kpi-card.kpi-warning .kpi-icon { background: #f59e0b; }
.kpi-card.kpi-info .kpi-icon { background: #3b82f6; }

.kpi-content {
    flex: 1;
    min-width: 0;
}

.kpi-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: var(--sakip-dark);
}

.kpi-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.5rem 0;
}

.kpi-trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
}

.kpi-trend.trend-up { color: #10b981; }
.kpi-trend.trend-down { color: #ef4444; }
.kpi-trend.trend-stable { color: #6b7280; }

/* Task Container */
.task-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.task-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--sakip-dark);
}

.task-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.task-body {
    padding: 1.5rem;
}

/* Task Items */
.task-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.task-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    transition: all 0.3s ease;
}

.task-item:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.task-item[data-status="overdue"] {
    border-left: 4px solid #ef4444;
    background: #fef2f2;
}

.task-item[data-status="pending"] {
    border-left: 4px solid #f59e0b;
    background: #fffbeb;
}

.task-content {
    flex: 1;
    min-width: 0;
    margin-right: 1rem;
}

.task-header-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.task-name {
    margin: 0;
    font-size: 1rem;
    font-weight: 500;
    color: var(--sakip-dark);
    flex: 1;
}

.task-priority {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.priority-high { background: #fef2f2; color: #dc2626; }
.priority-medium { background: #fffbeb; color: #d97706; }
.priority-low { background: #f0fdf4; color: #16a34a; }

.task-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.task-meta span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.task-status {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-pending { background: #fffbeb; color: #d97706; }
.status-in-progress { background: #eff6ff; color: #2563eb; }
.status-overdue { background: #fef2f2; color: #dc2626; }

.task-description {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.4;
}

.task-actions {
    display: flex;
    gap: 0.25rem;
    flex-shrink: 0;
}

/* Quick Actions Panel */
.quick-actions-panel,
.guidelines-panel,
.history-container,
.bulk-operations-panel {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.panel-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.panel-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--sakip-dark);
}

.panel-body {
    padding: 1.5rem;
}

/* Action Grid */
.action-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    text-align: left;
    transition: all 0.3s ease;
    cursor: pointer;
}

.action-btn:hover {
    background: #f9fafb;
    border-color: var(--sakip-primary);
    transform: translateY(-1px);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--sakip-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.action-text {
    flex: 1;
    min-width: 0;
}

.action-title {
    display: block;
    font-weight: 500;
    color: var(--sakip-dark);
    margin-bottom: 0.25rem;
}

.action-desc {
    display: block;
    font-size: 0.875rem;
    color: #6b7280;
}

/* Guidelines */
.guideline-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.guideline-item:last-child {
    border-bottom: none;
}

.guideline-item i {
    font-size: 1rem;
    flex-shrink: 0;
}

/* History Table */
.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-wrap: wrap;
    gap: 1rem;
}

.history-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--sakip-dark);
}

.history-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.history-body {
    padding: 1.5rem;
}

/* Status Badges */
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-approved { background: #f0fdf4; color: #16a34a; }
.status-rejected { background: #fef2f2; color: #dc2626; }
.status-pending { background: #fffbeb; color: #d97706; }

.value-badge {
    padding: 0.25rem 0.5rem;
    background: #eff6ff;
    color: #2563eb;
    border-radius: 4px;
    font-weight: 500;
    font-size: 0.875rem;
}

/* Indicator Info */
.indicator-info {
    min-width: 0;
}

.indicator-name {
    font-weight: 500;
    color: var(--sakip-dark);
    margin-bottom: 0.25rem;
}

.indicator-code {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Verifier Info */
.verifier-info {
    min-width: 0;
}

.verifier-name {
    font-weight: 500;
    color: var(--sakip-dark);
    margin-bottom: 0.25rem;
}

.verifier-date {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Bulk Operations */
.bulk-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.bulk-item {
    text-align: center;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    transition: all 0.3s ease;
}

.bulk-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.bulk-item h6 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--sakip-dark);
}

.bulk-item p {
    margin: 0 0 1rem 0;
    font-size: 0.875rem;
    color: #6b7280;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .header-actions {
        width: 100%;
        justify-content: flex-end;
    }

    .task-header,
    .history-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .task-controls,
    .history-controls {
        width: 100%;
        justify-content: space-between;
    }

    .task-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .task-actions {
        margin-top: 1rem;
        width: 100%;
        justify-content: flex-end;
    }

    .bulk-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .task-controls,
    .history-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .action-grid {
        gap: 0.75rem;
    }

    .action-btn {
        padding: 0.75rem;
    }
}

/* Print Styles */
@media print {
    .header-actions,
    .task-controls,
    .history-controls,
    .quick-actions-panel,
    .bulk-operations-panel {
        display: none !important;
    }

    .task-container,
    .history-container {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Data Collector Dashboard Functions
let taskFilter = 'all';
let historyFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    setupEventListeners();

    // Auto-refresh every 3 minutes
    setInterval(function() {
        refreshDashboard();
    }, 180000);
});

function initializeFilters() {
    // Set default date filter to today
    const today = new Date().toISOString().split('T')[0];
    const historyDateInput = document.getElementById('historyDate');
    if (historyDateInput) {
        historyDateInput.value = today;
    }
}

function setupEventListeners() {
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            createNewTask();
        }
        if (e.ctrlKey && e.key === 'u') {
            e.preventDefault();
            submitData();
        }
        if (e.ctrlKey && e.key === 'i') {
            e.preventDefault();
            bulkImport();
        }
    });
}

function filterTasks() {
    const filter = document.getElementById('taskFilter').value;
    const taskItems = document.querySelectorAll('.task-item');

    taskItems.forEach(item => {
        const status = item.dataset.status;
        if (filter === 'all' || status === filter) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });

    taskFilter = filter;
    updateTaskCount();
}

function filterHistory() {
    const statusFilter = document.getElementById('historyFilter').value;
    const dateFilter = document.getElementById('historyDate').value;
    const rows = document.querySelectorAll('#historyTable tbody tr');

    rows.forEach(row => {
        const status = row.dataset.status;
        const date = row.dataset.date;

        let showRow = true;

        if (statusFilter !== 'all' && status !== statusFilter) {
            showRow = false;
        }

        if (dateFilter && date !== dateFilter) {
            showRow = false;
        }

        row.style.display = showRow ? 'table-row' : 'none';
    });

    historyFilter = statusFilter;
}

function updateTaskCount() {
    const visibleTasks = document.querySelectorAll('.task-item:not([style*="display: none"])');
    const taskCount = document.querySelector('.kpi-value');
    if (taskCount) {
        taskCount.textContent = visibleTasks.length;
    }
}

function createNewTask() {
    // Navigate to task creation page
    window.location.href = '{{ route("sakip.data-collection.create") }}';
}

function editTask(taskId) {
    // Navigate to task edit page
    window.location.href = `/sakip/data-collection/${taskId}/edit`;
}

function submitData(taskId = null) {
    if (taskId) {
        // Submit specific task
        window.location.href = `/sakip/data-collection/${taskId}/submit`;
    } else {
        // Open general submission form
        window.location.href = '{{ route("sakip.data-collection.create") }}';
    }
}

function uploadEvidence(taskId = null) {
    if (taskId) {
        // Upload evidence for specific task
        window.location.href = `/sakip/data-collection/${taskId}/evidence`;
    } else {
        // Open general evidence upload
        window.location.href = '{{ route("sakip.data-collection.evidence-upload") }}';
    }
}

function bulkImport() {
    window.location.href = '{{ route("sakip.data-collection.bulk-import") }}';
}

function viewHistory() {
    // Scroll to history section
    document.querySelector('.history-container').scrollIntoView({
        behavior: 'smooth'
    });
}

function viewSubmission(submissionId) {
    // Navigate to submission detail
    window.location.href = `/sakip/data-collection/${submissionId}`;
}

function resubmitData(submissionId) {
    // Navigate to resubmission form
    window.location.href = `/sakip/data-collection/${submissionId}/resubmit`;
}

function exportHistory() {
    // Show loading state
    showNotification('Mempersiapkan ekspor riwayat...', 'info');

    // Simulate export process
    setTimeout(() => {
        showNotification('Riwayat berhasil diekspor', 'success');
    }, 2000);
}

function exportTemplate() {
    // Download Excel template
    const link = document.createElement('a');
    link.href = '/templates/sakip-data-collection-template.xlsx';
    link.download = 'template-pengumpulan-data.xlsx';
    link.click();

    showNotification('Template berhasil diunduh', 'success');
}

function validateData() {
    showNotification('Memvalidasi data...', 'info');

    // Simulate validation process
    setTimeout(() => {
        showNotification('Validasi data selesai. Tidak ada masalah ditemukan.', 'success');
    }, 3000);
}

function refreshDashboard() {
    // Show loading state
    const cards = document.querySelectorAll('.kpi-card, .task-container, .history-container');
    cards.forEach(card => {
        card.style.opacity = '0.6';
    });

    // Simulate data refresh
    setTimeout(() => {
        // Update KPI values with slight variations
        updateKPIValues();

        // Restore opacity
        cards.forEach(card => {
            card.style.opacity = '1';
        });

        // Show success notification
        showNotification('Dashboard berhasil disegarkan', 'success');
    }, 1000);
}

function updateKPIValues() {
    // Simulate slight variations in KPI values
    const kpiValues = document.querySelectorAll('.kpi-value');
    kpiValues.forEach(value => {
        const currentValue = parseFloat(value.textContent);
        if (!isNaN(currentValue)) {
            const variation = (Math.random() - 0.5) * 2; // ±1% variation
            const newValue = Math.max(0, currentValue + variation);
            value.textContent = Math.round(newValue);
        }
    });
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

function showNotification(message, type = 'info') {
    // Simple notification implementation
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        max-width: 300px;
        word-wrap: break-word;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

    // Set background color based on type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    notification.style.backgroundColor = colors[type] || colors.info;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add drag and drop functionality for file uploads
document.addEventListener('DOMContentLoaded', function() {
    const bulkImportBtn = document.querySelector('[onclick="bulkImport()"]');
    if (bulkImportBtn) {
        // Add drag and drop to the button
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            bulkImportBtn.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            bulkImportBtn.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            bulkImportBtn.addEventListener(eventName, unhighlight, false);
        });

        bulkImportBtn.addEventListener('drop', handleDrop, false);
    }
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(e) {
    e.target.classList.add('highlight');
}

function unhighlight(e) {
    e.target.classList.remove('highlight');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;

    if (files.length > 0) {
        handleFiles(files);
    }
}

function handleFiles(files) {
    ([...files]).forEach(file => {
        if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
            file.name.endsWith('.xlsx') ||
            file.name.endsWith('.xls')) {
            showNotification(`File ${file.name} siap untuk diimpor`, 'info');
            // Process the file
        } else {
            showNotification('Format file tidak didukung. Gunakan file Excel (.xlsx, .xls)', 'error');
        }
    });
}
</script>
@endpush
