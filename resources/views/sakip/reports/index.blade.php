@extends('layouts.app')

@section('title', 'Laporan SAKIP')

@section('content')
<!-- Page Header -->
<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">
                        <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                        Laporan SAKIP
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 sm:text-base">
                        Kelola dan pantau laporan kinerja instansi pemerintah
                    </p>
                </div>
                    <div class="mt-4 flex space-x-3 sm:mt-0 sm:ml-4">
                        @can('create', App\Models\Report::class)
                        <a href="{{ route('sakip.reports.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Buat Laporan
                        </a>
                        @endcan
                        <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-download mr-2"></i>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        @if(isset($statistics))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Reports -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Laporan</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_reports'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Approval -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Menunggu Persetujuan</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['pending_approval'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Approved Reports -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Disetujui</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['approved'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Rejected Reports -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Ditolak</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['rejected'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Laporan</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="Cari berdasarkan judul, periode, atau status...">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status-filter"
                                name="status"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending_approval">Menunggu Persetujuan</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label for="type-filter" class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                        <select id="type-filter"
                                name="type"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Semua Jenis</option>
                            <option value="monthly">Bulanan</option>
                            <option value="quarterly">Triwulan</option>
                            <option value="semester">Semester</option>
                            <option value="annual">Tahunan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Laporan</h3>
                    <div class="mt-3 sm:mt-0 flex items-center space-x-2">
                        <button type="button"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-1"></i>
                            Filter
                        </button>
                        <button type="button"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-sort mr-1"></i>
                            Urutkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Data Table Component -->
            <div class="overflow-hidden">
                @component('sakip.components.data-table', [
                    'id' => 'reports-table',
                    'type' => 'laporan',
                    'apiUrl' => route('sakip.api.datatables.laporan'),
                    'searchable' => true,
                    'exportable' => true,
                    'selectable' => true,
                    'show_actions' => true,
                    'actions' => ['view', 'edit', 'delete', 'download'],
                    'columns' => [
                        ['field' => 'title', 'title' => 'Judul Laporan', 'sortable' => true],
                        ['field' => 'report_type', 'title' => 'Jenis', 'sortable' => true],
                        ['field' => 'period', 'title' => 'Periode', 'sortable' => true],
                        ['field' => 'status', 'title' => 'Status', 'sortable' => true],
                        ['field' => 'created_by', 'title' => 'Dibuat Oleh', 'sortable' => false],
                        ['field' => 'created_at', 'title' => 'Tanggal Dibuat', 'sortable' => true],
                    ]
                ])
                @endcomponent
            </div>
        </div>

        <!-- Recent Activity -->
        @if(isset($recentReports) && $recentReports->count() > 0)
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
            </div>
            <div class="p-6">
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($recentReports->take(5) as $report)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-file-alt text-white text-sm"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900">{{ $report->title }}</span>
                                                {{ $report->status === 'approved' ? 'telah disetujui' : 'telah diperbarui' }}
                                            </p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="{{ $report->updated_at }}">{{ $report->updated_at->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>

@push('styles')
<style>
    /* Custom styles for enhanced UI */
    .sakip-reports-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card {
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .filter-section {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }

    /* Enhanced responsive design */
    @media (max-width: 640px) {
        .grid-cols-1 {
            gap: 1rem;
        }

        /* Mobile-first approach for statistics cards */
        .stat-card {
            padding: 1rem;
        }

        .stat-card .text-2xl {
            font-size: 1.5rem;
        }

        /* Improve mobile table experience */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        /* Better mobile spacing */
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        /* Mobile header adjustments */
        .text-2xl.sm\:text-3xl {
            font-size: 1.5rem;
        }

        /* Mobile button adjustments */
        .inline-flex.items-center {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
    }

    @media (max-width: 768px) {
        /* Tablet adjustments */
        .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        /* Better filter layout on tablets */
        .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-4 .lg\:col-span-2 {
            grid-column: span 2;
        }
    }

    @media (max-width: 1024px) {
        /* Large tablet/small desktop adjustments */
        .max-w-7xl {
            max-width: 100%;
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    /* Improved focus states for accessibility */
    .focus\:ring-2:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    }

    /* Better hover states */
    .hover\:shadow-md:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* Loading states */
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }

    /* Print styles */
    @media print {
        .bg-gray-50 {
            background: white !important;
        }

        .shadow-sm, .shadow-md {
            box-shadow: none !important;
        }

        .border {
            border: 1px solid #e5e7eb !important;
        }

        .text-blue-600 {
            color: #000 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize data table
        if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
            SAKIP_DATA_TABLE_INIT.initLaporanTable('reports-table');
        }

        // Search functionality
        const searchInput = document.getElementById('search');
        const statusFilter = document.getElementById('status-filter');
        const typeFilter = document.getElementById('type-filter');

        // Debounce function for search
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Search handler
        const handleSearch = debounce(function() {
            const searchTerm = searchInput.value;
            const status = statusFilter.value;
            const type = typeFilter.value;

            // Update data table with filters
            if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
                SAKIP_DATA_TABLE_INIT.updateFilters('reports-table', {
                    search: searchTerm,
                    status: status,
                    type: type
                });
            }
        }, 300);

        // Event listeners
        searchInput.addEventListener('input', handleSearch);
        statusFilter.addEventListener('change', handleSearch);
        typeFilter.addEventListener('change', handleSearch);

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
                SAKIP_DATA_TABLE_INIT.refreshTable('reports-table');
            }
        }, 300000);
    });
</script>
@endpush
@endsection
