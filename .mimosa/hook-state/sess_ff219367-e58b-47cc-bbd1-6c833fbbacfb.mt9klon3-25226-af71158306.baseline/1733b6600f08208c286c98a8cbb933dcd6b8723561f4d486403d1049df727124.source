@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<!-- Breadcrumbs -->
<nav class="breadcrumbs">
    <a href="{{ route('sakip.dashboard') }}" class="breadcrumb-item active">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
</nav>

<!-- Quick Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-modern.stat-card
        title="Total Indikator"
        :value="$stats['total_indicators'] ?? 0"
        icon="fa-chart-line"
        trend="up"
        trendValue="+12% dari bulan lalu"
        color="primary"
        href="{{ route('sakip.indicators.index') }}"
    />
    <x-modern.stat-card
        title="Data Terkumpul"
        :value="$stats['collected_data'] ?? 0"
        icon="fa-database"
        trend="up"
        trendValue="+8% dari bulan lalu"
        color="success"
        href="{{ route('sakip.data-collection.index') }}"
    />
    <x-modern.stat-card
        title="Target Tercapai"
        :value="$stats['targets_achieved'] ?? 0"
        icon="fa-bullseye"
        trend="neutral"
        trendValue="75% rata-rata"
        color="warning"
        href="{{ route('sakip.targets.index') }}"
    />
    <x-modern.stat-card
        title="Pending Review"
        :value="$stats['pending_review'] ?? 0"
        icon="fa-clock"
        trend="down"
        trendValue="-3 dari minggu lalu"
        color="danger"
        href="{{ route('sakip.assessments.index') }}"
    />
</div>

<!-- Main Dashboard Content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Performance Overview Chart -->
    <div class="modern-card lg:col-span-2">
        <div class="card-header">
            <h3 class="card-title">Performa Kinerja</h3>
            <div class="card-actions">
                <select class="form-select" style="width: auto;" id="periodFilter">
                    <option value="month">Bulan Ini</option>
                    <option value="quarter">Quarter Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div style="height: 280px; display: flex; align-items: flex-end; gap: 8px; padding: 20px 0;">
                <!-- Simple CSS Bar Chart (in production, use Chart.js) -->
                @foreach($monthlyPerformance ?? [
                    ['month' => 'Jan', 'value' => 75],
                    ['month' => 'Feb', 'value' => 82],
                    ['month' => 'Mar', 'value' => 88],
                    ['month' => 'Apr', 'value' => 85],
                    ['month' => 'Mei', 'value' => 92],
                    ['month' => 'Jun', 'value' => 95],
                ] as $data)
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 100%; height: {{ $data['value'] * 2.2 }}px; background: linear-gradient(to top, var(--primary-500), var(--primary-400)); border-radius: 4px 4px 0 0; transition: height 0.3s ease;" title="{{ $data['value'] }}%"></div>
                    <span style="font-size: 0.75rem; color: var(--text-tertiary);">{{ $data['month'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="modern-card">
        <div class="card-header">
            <h3 class="card-title">Aksi Cepat</h3>
        </div>
        <div class="card-body">
            <div class="flex flex-col gap-2">
                <a href="{{ route('sakip.data-collection.create') }}" class="btn btn-primary" style="width: 100%; justify-content: flex-start;">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Data Kinerja</span>
                </a>
                <a href="{{ route('sakip.indicators.create') }}" class="btn btn-secondary" style="width: 100%; justify-content: flex-start;">
                    <i class="fas fa-chart-line"></i>
                    <span>Buat Indikator Baru</span>
                </a>
                <a href="{{ route('sakip.reports.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: flex-start;">
                    <i class="fas fa-file-alt"></i>
                    <span>Generate Laporan</span>
                </a>
                <a href="{{ route('sakip.audit.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: flex-start;">
                    <i class="fas fa-history"></i>
                    <span>Lihat Audit Trail</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Tasks -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent Data Submissions -->
    <div class="modern-card">
        <div class="card-header">
            <h3 class="card-title">Pengumpulan Data Terbaru</h3>
            <a href="{{ route('sakip.data-collection.index') }}" class="btn btn-ghost btn-sm">
                Lihat Semua
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if(isset($recentSubmissions) && count($recentSubmissions) > 0)
            <div style="display: flex; flex-direction: column;">
                @foreach($recentSubmissions as $submission)
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-light);">
                    <div style="width: 40px; height: 40px; background: var(--primary-50); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--primary-600);">
                        <i class="fas fa-database"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 500; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $submission['indicator_name'] ?? 'Indikator ' . $submission['id'] }}
                        </div>
                        <div style="font-size: 0.8125rem; color: var(--text-tertiary);">
                            {{ $submission['period'] ?? 'Periode terbaru' }}  Nilai: {{ $submission['actual_value'] ?? 0 }}
                        </div>
                    </div>
                    <span class="status-pill {{ $submission['status'] ?? 'pending' }}">
                        {{ ucfirst($submission['status'] ?? 'Pending') }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="empty-state-title">Belum ada data</div>
                <div class="empty-state-description">Mulai mengumpulkan data kinerja Anda.</div>
                <a href="{{ route('sakip.data-collection.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Data
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="modern-card">
        <div class="card-header">
            <h3 class="card-title">Tugas Menunggu</h3>
            <a href="{{ route('sakip.assessments.index') }}" class="btn btn-ghost btn-sm">
                Lihat Semua
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if(isset($pendingTasks) && count($pendingTasks) > 0)
            <div style="display: flex; flex-direction: column;">
                @foreach($pendingTasks as $task)
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-light);">
                    <div style="width: 40px; height: 40px; background: var(--warning-light); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--warning);">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 500; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $task['title'] ?? 'Penilaian ' . $task['id'] }}
                        </div>
                        <div style="font-size: 0.8125rem; color: var(--text-tertiary);">
                            Due: {{ $task['due_date'] ?? 'Segera' }}
                        </div>
                    </div>
                    <span class="badge badge-warning">
                        {{ $task['priority'] ?? 'Medium' }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="empty-state-title">Semua selesai!</div>
                <div class="empty-state-description">Tidak ada tugas yang menunggu untuk saat ini.</div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Performance by Category -->
<div class="modern-card">
    <div class="card-header">
        <h3 class="card-title">Performa per Kategori</h3>
        <div class="card-actions">
            <button class="btn btn-ghost btn-sm">
                <i class="fas fa-download"></i>
                <span>Ekspor</span>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($categoryPerformance ?? [
                ['name' => 'Penyelenggaraan Pelayanan', 'achievement' => 92, 'color' => 'primary'],
                ['name' => 'Pembinaan dan Pengawasan', 'achievement' => 88, 'color' => 'success'],
                ['name' => 'Penerapan Nilai Dasar', 'achievement' => 85, 'color' => 'warning'],
                ['name' => 'Kreativitas dan Inovasi', 'achievement' => 78, 'color' => 'danger'],
            ] as $category)
            <div style="padding: 1rem; border: 1px solid var(--border-light); border-radius: var(--radius-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary);">{{ $category['name'] }}</span>
                    <span style="font-size: 1rem; font-weight: 600; color: var(--text-primary);">{{ $category['achievement'] }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill {{ $category['color'] }}" style="width: {{ $category['achievement'] }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Additional dashboard-specific styles */
@media (min-width: 640px) {
    .sm\:grid-cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .lg\:col-span-2 {
        grid-column: span 2 / span 2;
    }
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }
    .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }
}

.flex-col {
    flex-direction: column;
}
</style>
@endpush
