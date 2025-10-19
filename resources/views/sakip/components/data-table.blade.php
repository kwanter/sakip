@props(['id', 'columns' => [], 'data_source' => null, 'options' => []])

<!-- Enhanced Data Table Component -->
<div class="bg-white shadow-sm overflow-hidden rounded-xl border border-gray-200" data-table-id="{{ $id }}">
    <!-- Table Header with Actions -->
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            <div class="flex-1 min-w-0">
                <h3 class="text-base sm:text-lg leading-6 font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-table text-blue-600 mr-2"></i>
                    Data Table
                </h3>
                <p class="mt-1 text-xs sm:text-sm text-gray-500">
                    Kelola data dengan mudah dan efisien
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                <div class="relative min-w-0 flex-1 sm:max-w-xs">
                    <input type="text" 
                           class="block w-full pl-10 pr-3 py-2 sm:py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors duration-200" 
                           placeholder="Cari data..." 
                           data-table-search="{{ $id }}">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button type="button" 
                            class="inline-flex items-center px-2 sm:px-3 py-2 sm:py-2.5 border border-gray-300 shadow-sm text-xs sm:text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" 
                            data-table-refresh="{{ $id }}">
                        <i class="fas fa-sync-alt mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                    
                    <div class="relative">
                        <button type="button" 
                                class="inline-flex items-center px-2 sm:px-3 py-2 sm:py-2.5 border border-gray-300 shadow-sm text-xs sm:text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" 
                                data-table-export="{{ $id }}">
                            <i class="fas fa-download mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Export</span>
                            <i class="fas fa-chevron-down ml-1 sm:ml-2"></i>
                        </button>
                        <!-- Export dropdown menu (hidden by default) -->
                        <div id="{{ $id }}-export-menu" class="hidden absolute right-0 mt-2 w-40 sm:w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                            <div class="py-1">
                                <a href="#" class="export-option block px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-format="excel">
                                    <i class="fas fa-file-excel text-green-600 mr-2"></i>Excel
                                </a>
                                <a href="#" class="export-option block px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-format="pdf">
                                    <i class="fas fa-file-pdf text-red-600 mr-2"></i>PDF
                                </a>
                                <a href="#" class="export-option block px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-format="csv">
                                    <i class="fas fa-file-csv text-blue-600 mr-2"></i>CSV
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    @if(isset($options['show_add_button']) && $options['show_add_button'])
                    <button type="button" 
                            class="inline-flex items-center px-3 sm:px-4 py-2 sm:py-2.5 border border-transparent text-xs sm:text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" 
                            data-table-add="{{ $id }}">
                        <i class="fas fa-plus mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Tambah Baru</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(isset($options['filters']) && !empty($options['filters']))
    <div class="sakip-data-table-filters px-4 sm:px-6 py-3 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach($options['filters'] as $filterName => $filterOptions)
            <div class="sakip-data-table-filter">
                <label for="{{ $id }}-filter-{{ $filterName }}" class="block text-xs font-medium text-gray-700 mb-1">{{ ucfirst(str_replace('_', ' ', $filterName)) }}:</label>
                <select id="{{ $id }}-filter-{{ $filterName }}" class="sakip-data-table-filter-select block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" data-table-filter="{{ $id }}" data-filter-name="{{ $filterName }}">
                    <option value="">All {{ ucfirst(str_replace('_', ' ', $filterName)) }}</option>
                    @foreach($filterOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Enhanced Table Wrapper -->
    <div class="overflow-x-auto">
        <table id="{{ $id }}" class="min-w-full divide-y divide-gray-200" data-table-type="{{ $options['type'] ?? 'default' }}" data-source="{{ $data_source }}">
            <thead class="bg-gray-50">
                <tr>
                    @if(isset($options['selectable']) && $options['selectable'])
                    <th scope="col" class="relative w-12 px-4 sm:w-16 sm:px-6 lg:px-8">
                        <input type="checkbox" class="absolute left-3 sm:left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-table-select-all="{{ $id }}">
                    </th>
                    @endif
                    @foreach($columns as $column)
                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200" data-field="{{ $column['field'] }}" data-sortable="{{ $column['sortable'] ?? false }}">
                        <div class="flex items-center space-x-1">
                            <span class="truncate">{{ $column['title'] }}</span>
                            @if(isset($column['sortable']) && $column['sortable'])
                            <span class="flex flex-col">
                                <i class="fas fa-caret-up text-xs text-gray-400 -mb-1"></i>
                                <i class="fas fa-caret-down text-xs text-gray-400"></i>
                            </span>
                            @endif
                        </div>
                    </th>
                    @endforeach
                    @if(isset($options['show_actions']) && $options['show_actions'])
                    <th scope="col" class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="animate-pulse">
                    <td colspan="{{ count($columns) + (isset($options['selectable']) && $options['selectable'] ? 1 : 0) + (isset($options['show_actions']) && $options['show_actions'] ? 1 : 0) }}" class="px-3 sm:px-6 py-8 sm:py-12 text-center">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-blue-600"></div>
                            <span class="text-xs sm:text-sm text-gray-500">Memuat data...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Enhanced Footer with Pagination -->
    <div class="bg-white px-4 sm:px-6 py-3 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            <div class="flex-1 flex justify-between sm:hidden">
                <button type="button" class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200" data-table-page="{{ $id }}" data-page="prev" disabled>
                    <i class="fas fa-chevron-left mr-1"></i>
                    Previous
                </button>
                <button type="button" class="ml-3 relative inline-flex items-center px-3 py-2 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200" data-table-page="{{ $id }}" data-page="next" disabled>
                    Next
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-700" data-table-info="{{ $id }}">
                        Menampilkan <span class="font-medium">0</span> sampai <span class="font-medium">0</span> dari <span class="font-medium">0</span> entri
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" data-table-pagination="{{ $id }}">
                        <button type="button" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-xs sm:text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200" data-table-page="{{ $id }}" data-page="prev" disabled>
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="relative inline-flex items-center px-3 sm:px-4 py-2 border border-gray-300 bg-white text-xs sm:text-sm font-medium text-gray-700" data-table-page-info="{{ $id }}">
                            Halaman 1 dari 1
                        </span>
                        <button type="button" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-xs sm:text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200" data-table-page="{{ $id }}" data-page="next" disabled>
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
        SAKIP_DATA_TABLE_INIT.initializeTable('{{ $id }}', @json($options));
    }
    
    // Mobile-specific enhancements
    if (window.innerWidth <= 768) {
        // Add touch-friendly interactions
        const table = document.getElementById('{{ $id }}');
        if (table) {
            table.style.fontSize = '0.875rem';
        }
        
        // Improve mobile scrolling
        const scrollContainer = table?.closest('.overflow-x-auto');
        if (scrollContainer) {
            scrollContainer.style.webkitOverflowScrolling = 'touch';
        }
    }
});
</script>
@endpush