@extends('layouts.app')

@section('title', 'Dashboard Eksekutif - SAKIP')

@section('header')
    <div class="header-content">
        <h1 class="page-title">Dashboard Eksekutif</h1>
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
    <!-- Executive Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value">{{ number_format($overallAchievement ?? 87.5, 1) }}%</h3>
                    <p class="kpi-label">Pencapaian Keseluruhan</p>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5.2% dari periode sebelumnya</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-info">
                <div class="kpi-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value">{{ $totalIndicators ?? 245 }}</h3>
                    <p class="kpi-label">Total Indikator</p>
                    <div class="kpi-trend trend-stable">
                        <i class="fas fa-minus"></i>
                        <span>Tetap dari periode sebelumnya</span>
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
                    <h3 class="kpi-value">{{ $pendingAssessments ?? 12 }}</h3>
                    <p class="kpi-label">Penilaian Tertunda</p>
                    <div class="kpi-trend trend-down">
                        <i class="fas fa-arrow-down"></i>
                        <span>-8 dari minggu lalu</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-danger">
                <div class="kpi-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value">{{ $criticalIssues ?? 3 }}</h3>
                    <p class="kpi-label">Isu Kritis</p>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>Perlu perhatian segera</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Achievement Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Tren Pencapaian Kinerja</h5>
                    <div class="chart-controls">
                        <select class="form-select form-select-sm" id="periodFilter" onchange="updateCharts()">
                            <option value="quarterly">Kuartalan</option>
                            <option value="monthly" selected>Bulanan</option>
                            <option value="weekly">Mingguan</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('achievement-trend')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="achievementTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Department Performance Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Distribusi Pencapaian Unit</h5>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('department-distribution')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="departmentDistributionChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance by Category -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Pencapaian Berdasarkan Kategori</h5>
                    <div class="chart-controls">
                        <select class="form-select form-select-sm" id="categoryFilter" onchange="updateCategoryChart()">
                            <option value="all">Semua Kategori</option>
                            <option value="strategic">Strategis</option>
                            <option value="operational">Operasional</option>
                            <option value="financial">Keuangan</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('category-performance')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="categoryPerformanceChart" width="800" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="row">
        <!-- Top Performing Departments -->
        <div class="col-lg-6 mb-4">
            <div class="table-container">
                <div class="table-header">
                    <h5 class="table-title">Unit dengan Pencapaian Tertinggi</h5>
                    <a href="{{ route('sakip.reports.index') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Unit Kerja</th>
                                <th>Pencapaian</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topDepartments ?? [] as $dept)
                            <tr>
                                <td>
                                    <div class="dept-info">
                                        <div class="dept-name">{{ $dept['name'] }}</div>
                                        <div class="dept-code">{{ $dept['code'] }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="achievement-bar">
                                        <div class="achievement-fill" style="width: {{ $dept['achievement'] }}%"></div>
                                        <span class="achievement-text">{{ $dept['achievement'] }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-success">Tercapai</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Critical Issues -->
        <div class="col-lg-6 mb-4">
            <div class="table-container">
                <div class="table-header">
                    <h5 class="table-title">Isu yang Memerlukan Perhatian</h5>
                    <a href="{{ route('sakip.assessments.index') }}" class="btn btn-sm btn-outline-primary">
                        Kelola Semua
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Isu</th>
                                <th>Unit</th>
                                <th>Prioritas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criticalIssuesList ?? [] as $issue)
                            <tr>
                                <td>
                                    <div class="issue-info">
                                        <div class="issue-title">{{ $issue['title'] }}</div>
                                        <div class="issue-date">{{ $issue['date'] }}</div>
                                    </div>
                                </td>
                                <td>{{ $issue['department'] }}</td>
                                <td>
                                    <span class="badge badge-{{ $issue['priority'] }}">
                                        {{ ucfirst($issue['priority']) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewIssue({{ $issue['id'] }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h5 class="section-title">Aksi Cepat</h5>
        <div class="action-buttons">
            <a href="{{ route('sakip.indicators.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Tambah Indikator Baru
            </a>
            <a href="{{ route('sakip.reports.generate') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-alt"></i>
                Buat Laporan
            </a>
            <button class="btn btn-outline-secondary" onclick="exportDashboard()">
                <i class="fas fa-download"></i>
                Ekspor Dashboard
            </button>
            <button class="btn btn-outline-info" onclick="scheduleReport()">
                <i class="fas fa-calendar"></i>
                Jadwalkan Laporan
            </button>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Executive Dashboard Styles */
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

.kpi-card.kpi-success { border-left-color: #10b981; }
.kpi-card.kpi-info { border-left-color: #3b82f6; }
.kpi-card.kpi-warning { border-left-color: #f59e0b; }
.kpi-card.kpi-danger { border-left-color: #ef4444; }

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

.kpi-card.kpi-success .kpi-icon { background: #10b981; }
.kpi-card.kpi-info .kpi-icon { background: #3b82f6; }
.kpi-card.kpi-warning .kpi-icon { background: #f59e0b; }
.kpi-card.kpi-danger .kpi-icon { background: #ef4444; }

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

/* Chart Containers */
.chart-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.chart-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--sakip-dark);
}

.chart-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-body {
    padding: 1.5rem;
    position: relative;
}

/* Table Styles */
.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--sakip-dark);
}

.table-responsive {
    margin: 0;
}

.table {
    margin: 0;
}

.table th {
    background: #f3f4f6;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--sakip-dark);
    border-bottom: 2px solid #e5e7eb;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

/* Department Info */
.dept-info {
    min-width: 0;
}

.dept-name {
    font-weight: 500;
    color: var(--sakip-dark);
    margin-bottom: 0.25rem;
}

.dept-code {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Achievement Bar */
.achievement-bar {
    position: relative;
    background: #e5e7eb;
    border-radius: 4px;
    height: 20px;
    overflow: hidden;
}

.achievement-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, var(--sakip-primary), var(--sakip-secondary));
    transition: width 0.3s ease;
}

.achievement-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

/* Issue Info */
.issue-info {
    min-width: 0;
}

.issue-title {
    font-weight: 500;
    color: var(--sakip-dark);
    margin-bottom: 0.25rem;
}

.issue-date {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Quick Actions */
.quick-actions {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.section-title {
    margin: 0 0 1rem 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--sakip-dark);
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Badge Styles */
.badge-success { background: #10b981; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-danger { background: #ef4444; color: white; }
.badge-info { background: #3b82f6; color: white; }

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

    .action-buttons {
        flex-direction: column;
    }

    .action-buttons .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .chart-header,
    .table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .chart-controls {
        width: 100%;
        justify-content: space-between;
    }
}

/* Print Styles */
@media print {
    .header-actions,
    .quick-actions,
    .chart-controls {
        display: none !important;
    }

    .chart-container,
    .table-container,
    .quick-actions {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart configurations
let achievementTrendChart, departmentDistributionChart, categoryPerformanceChart;

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();

    // Auto-refresh every 5 minutes
    setInterval(function() {
        refreshDashboard();
    }, 300000);
});

function initializeCharts() {
    // Achievement Trend Chart
    const achievementCtx = document.getElementById('achievementTrendChart');
    if (achievementCtx) {
        achievementTrendChart = new Chart(achievementCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Pencapaian (%)',
                    data: [75, 78, 82, 85, 87, 89, 88, 90, 87, 89, 91, 87.5],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Target (%)',
                    data: [80, 80, 80, 85, 85, 85, 90, 90, 90, 90, 90, 90],
                    borderColor: '#ef4444',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 60,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Department Distribution Chart
    const distributionCtx = document.getElementById('departmentDistributionChart');
    if (distributionCtx) {
        departmentDistributionChart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Tercapai', 'Dalam Proses', 'Tertunda', 'Belum Dimulai'],
                datasets: [{
                    data: [65, 20, 10, 5],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
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
    }

    // Category Performance Chart
    const categoryCtx = document.getElementById('categoryPerformanceChart');
    if (categoryCtx) {
        categoryPerformanceChart = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: ['Kinerja Utama', 'Kinerja Pendukung', 'Kinerja Tambahan', 'Inovasi', 'Efisiensi'],
                datasets: [{
                    label: 'Target',
                    data: [90, 85, 80, 75, 85],
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: '#ef4444',
                    borderWidth: 1
                }, {
                    label: 'Realisasi',
                    data: [87, 89, 82, 78, 88],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: '#3b82f6',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
}

function updateCharts() {
    const period = document.getElementById('periodFilter').value;

    // Simulate data update based on period
    if (achievementTrendChart) {
        // Update chart data based on selected period
        const newData = generatePeriodData(period);
        achievementTrendChart.data.datasets[0].data = newData;
        achievementTrendChart.update();
    }
}

function generatePeriodData(period) {
    // Simulate different data for different periods
    const baseData = [75, 78, 82, 85, 87, 89, 88, 90, 87, 89, 91, 87.5];

    if (period === 'quarterly') {
        return [78, 85, 89, 87.5];
    } else if (period === 'weekly') {
        return Array.from({length: 12}, () => Math.floor(Math.random() * 10) + 80);
    }

    return baseData;
}

function updateCategoryChart() {
    const category = document.getElementById('categoryFilter').value;

    if (categoryPerformanceChart) {
        // Simulate data update based on category filter
        // In real implementation, this would fetch new data from server
        categoryPerformanceChart.update();
    }
}

function exportChart(chartType) {
    let chart;

    switch(chartType) {
        case 'achievement-trend':
            chart = achievementTrendChart;
            break;
        case 'department-distribution':
            chart = departmentDistributionChart;
            break;
        case 'category-performance':
            chart = categoryPerformanceChart;
            break;
        default:
            return;
    }

    if (chart) {
        const url = chart.toBase64Image();
        const link = document.createElement('a');
        link.download = `sakip-${chartType}-${new Date().toISOString().split('T')[0]}.png`;
        link.href = url;
        link.click();
    }
}

function refreshDashboard() {
    // Show loading state
    const cards = document.querySelectorAll('.kpi-card, .chart-container, .table-container');
    cards.forEach(card => {
        card.style.opacity = '0.6';
    });

    // Simulate data refresh
    setTimeout(() => {
        // Update KPI values with slight variations
        updateKPIValues();

        // Refresh charts
        if (achievementTrendChart) achievementTrendChart.update();
        if (departmentDistributionChart) departmentDistributionChart.update();
        if (categoryPerformanceChart) categoryPerformanceChart.update();

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
            value.textContent = newValue.toFixed(1) + (value.textContent.includes('%') ? '%' : '');
        }
    });
}

function exportDashboard() {
    // Show loading state
    showNotification('Mempersiapkan ekspor dashboard...', 'info');

    // Simulate export process
    setTimeout(() => {
        showNotification('Dashboard berhasil diekspor', 'success');
    }, 2000);
}

function scheduleReport() {
    // Open modal or redirect to report scheduling
    showNotification('Fitur penjadwalan laporan akan segera tersedia', 'info');
}

function viewIssue(issueId) {
    // Navigate to issue detail page
    window.location.href = `/sakip/issues/${issueId}`;
}

function viewDepartment(departmentId) {
    // Navigate to department detail page
    window.location.href = `/sakip/departments/${departmentId}`;
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
</script>
@endpush
