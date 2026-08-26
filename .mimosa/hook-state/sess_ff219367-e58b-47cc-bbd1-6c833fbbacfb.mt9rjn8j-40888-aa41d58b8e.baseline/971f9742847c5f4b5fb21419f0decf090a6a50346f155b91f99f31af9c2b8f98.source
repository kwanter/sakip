/**
 * SAKIP Data Tables System
 * Enhanced data tables with advanced filtering, sorting, and pagination
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['chart.js'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('chart.js'));
    } else {
        root.SAKIP_DATA_TABLES = factory(root.Chart);
    }
}(typeof self !== 'undefined' ? self : this, function (Chart) {

    /**
     * Data table constants and configuration
     */
    const DATA_TABLE_CONSTANTS = {
        // Column types
        COLUMN_TYPES: {
            TEXT: 'text',
            NUMBER: 'number',
            DATE: 'date',
            DATETIME: 'datetime',
            BOOLEAN: 'boolean',
            SELECT: 'select',
            MULTISELECT: 'multiselect',
            CURRENCY: 'currency',
            PERCENTAGE: 'percentage',
            BADGE: 'badge',
            STATUS: 'status',
            ACTIONS: 'actions',
            CUSTOM: 'custom'
        },

        // Filter operators
        FILTER_OPERATORS: {
            EQUALS: 'equals',
            NOT_EQUALS: 'not_equals',
            CONTAINS: 'contains',
            NOT_CONTAINS: 'not_contains',
            STARTS_WITH: 'starts_with',
            ENDS_WITH: 'ends_with',
            GREATER_THAN: 'greater_than',
            LESS_THAN: 'less_than',
            GREATER_EQUAL: 'greater_equal',
            LESS_EQUAL: 'less_equal',
            BETWEEN: 'between',
            IN: 'in',
            NOT_IN: 'not_in',
            IS_EMPTY: 'is_empty',
            IS_NOT_EMPTY: 'is_not_empty',
            IS_TRUE: 'is_true',
            IS_FALSE: 'is_false'
        },

        // Sort directions
        SORT_DIRECTIONS: {
            ASC: 'asc',
            DESC: 'desc',
            NONE: 'none'
        },

        // Pagination types
        PAGINATION_TYPES: {
            SIMPLE: 'simple',
            NUMERIC: 'numeric',
            COMPACT: 'compact',
            FULL: 'full'
        },

        // Selection modes
        SELECTION_MODES: {
            NONE: 'none',
            SINGLE: 'single',
            MULTIPLE: 'multiple'
        },

        // Export formats
        EXPORT_FORMATS: {
            CSV: 'csv',
            EXCEL: 'excel',
            PDF: 'pdf',
            JSON: 'json',
            PRINT: 'print'
        },

        // Default settings
        DEFAULT_SETTINGS: {
            pageSize: 10,
            pageSizeOptions: [5, 10, 25, 50, 100],
            maxVisiblePages: 5,
            enablePagination: true,
            enableSorting: true,
            enableFiltering: true,
            enableSearch: true,
            enableSelection: true,
            enableExport: true,
            enableColumnVisibility: true,
            enableColumnReorder: true,
            enableColumnResize: true,
            enableRowExpansion: true,
            enableLazyLoading: false,
            enableInfiniteScroll: false,
            enableVirtualization: false,
            enableGrouping: false,
            enableAggregation: false,
            enableRowNumbers: false,
            enableRowActions: true,
            enableBulkActions: true,
            enableStickyHeader: true,
            enableResponsive: true,
            enableLoadingState: true,
            enableEmptyState: true,
            enableErrorState: true,
            selectionMode: 'multiple',
            paginationType: 'numeric',
            defaultSortColumn: null,
            defaultSortDirection: 'asc',
            searchPlaceholder: 'Cari...',
            noDataMessage: 'Tidak ada data yang tersedia',
            loadingMessage: 'Memuat data...',
            errorMessage: 'Terjadi kesalahan saat memuat data',
            emptySearchMessage: 'Tidak ada hasil yang cocok dengan pencarian Anda',
            dateFormat: 'DD/MM/YYYY',
            datetimeFormat: 'DD/MM/YYYY HH:mm',
            currencyFormat: 'IDR',
            decimalPlaces: 2,
            thousandsSeparator: ',',
            decimalSeparator: '.',
            locale: 'id-ID'
        },

        // Status badges
        STATUS_BADGES: {
            ACTIVE: { text: 'Aktif', class: 'sakip-status-active', color: '#28a745' },
            INACTIVE: { text: 'Tidak Aktif', class: 'sakip-status-inactive', color: '#dc3545' },
            PENDING: { text: 'Menunggu', class: 'sakip-status-pending', color: '#ffc107' },
            APPROVED: { text: 'Disetujui', class: 'sakip-status-approved', color: '#28a745' },
            REJECTED: { text: 'Ditolak', class: 'sakip-status-rejected', color: '#dc3545' },
            PROCESSING: { text: 'Diproses', class: 'sakip-status-processing', color: '#17a2b8' },
            COMPLETED: { text: 'Selesai', class: 'sakip-status-completed', color: '#28a745' },
            FAILED: { text: 'Gagal', class: 'sakip-status-failed', color: '#dc3545' },
            DRAFT: { text: 'Draft', class: 'sakip-status-draft', color: '#6c757d' },
            SUBMITTED: { text: 'Dikirim', class: 'sakip-status-submitted', color: '#007bff' }
        },

        // Error messages (Indonesian)
        ERROR_MESSAGES: {
            INVALID_COLUMN_TYPE: 'Tipe kolom tidak valid',
            INVALID_FILTER_OPERATOR: 'Operator filter tidak valid',
            INVALID_SORT_DIRECTION: 'Arah pengurutan tidak valid',
            COLUMN_NOT_FOUND: 'Kolom tidak ditemukan',
            DATA_LOAD_FAILED: 'Gagal memuat data',
            EXPORT_FAILED: 'Gagal mengekspor data',
            INVALID_PAGE_SIZE: 'Ukuran halaman tidak valid',
            INVALID_PAGE_NUMBER: 'Nomor halaman tidak valid',
            SELECTION_NOT_ALLOWED: 'Pilihan tidak diizinkan untuk mode ini',
            MAX_SELECTION_EXCEEDED: 'Jumlah pilihan melebihi batas maksimum',
            OPERATION_NOT_SUPPORTED: 'Operasi tidak didukung',
            NETWORK_ERROR: 'Kesalahan jaringan',
            PERMISSION_DENIED: 'Izin ditolak',
            VALIDATION_FAILED: 'Validasi gagal'
        },

        // Success messages (Indonesian)
        SUCCESS_MESSAGES: {
            DATA_LOADED: 'Data berhasil dimuat',
            EXPORT_COMPLETED: 'Ekspor data berhasil',
            SELECTION_UPDATED: 'Pilihan berhasil diperbarui',
            COLUMN_VISIBILITY_UPDATED: 'Visibilitas kolom berhasil diperbarui',
            COLUMN_ORDER_UPDATED: 'Urutan kolom berhasil diperbarui',
            FILTER_APPLIED: 'Filter berhasil diterapkan',
            SORT_APPLIED: 'Pengurutan berhasil diterapkan',
            PAGE_CHANGED: 'Halaman berhasil diubah'
        }
    };

    /**
     * Data manager class - handles data operations
     */
    class DataManager {
        constructor(data = [], options = {}) {
            this.originalData = DataUtils.deepClone(data);
            this.filteredData = DataUtils.deepClone(data);
            this.currentData = DataUtils.deepClone(data);
            this.selectedRows = new Set();
            this.expandedRows = new Set();
            this.filters = [];
            this.sortConfig = null;
            this.pagination = {
                currentPage: 1,
                pageSize: options.pageSize || DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.pageSize,
                totalItems: data.length,
                totalPages: Math.ceil(data.length / (options.pageSize || DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.pageSize))
            };
            this.searchTerm = '';
            this.globalSearchFields = options.globalSearchFields || [];
            this.eventListeners = {};
        }

        /**
         * Set data
         */
        setData(data) {
            this.originalData = DataUtils.deepClone(data);
            this.applyFiltersAndSort();
            this.emit('dataChanged', this.getCurrentData());
        }

        /**
         * Add filter
         */
        addFilter(columnId, operator, value) {
            const existingFilterIndex = this.filters.findIndex(f => f.columnId === columnId);
            if (existingFilterIndex >= 0) {
                this.filters[existingFilterIndex] = { columnId, operator, value };
            } else {
                this.filters.push({ columnId, operator, value });
            }
            this.applyFiltersAndSort();
            this.emit('filterChanged', this.filters);
        }

        /**
         * Remove filter
         */
        removeFilter(columnId) {
            this.filters = this.filters.filter(f => f.columnId !== columnId);
            this.applyFiltersAndSort();
            this.emit('filterChanged', this.filters);
        }

        /**
         * Clear all filters
         */
        clearFilters() {
            this.filters = [];
            this.searchTerm = '';
            this.applyFiltersAndSort();
            this.emit('filterChanged', this.filters);
        }

        /**
         * Set search term
         */
        setSearchTerm(searchTerm) {
            this.searchTerm = searchTerm.toLowerCase();
            this.applyFiltersAndSort();
            this.emit('searchChanged', this.searchTerm);
        }

        /**
         * Apply filters
         */
        applyFilters() {
            this.filteredData = this.originalData.filter(row => {
                // Apply column filters
                for (const filter of this.filters) {
                    const value = row[filter.columnId];
                    if (!DataUtils.filterValue(value, filter.value, filter.operator)) {
                        return false;
                    }
                }

                // Apply global search
                if (this.searchTerm) {
                    const searchFields = this.globalSearchFields.length > 0 ?
                        this.globalSearchFields : Object.keys(row);

                    const found = searchFields.some(field => {
                        const value = String(row[field] || '').toLowerCase();
                        return value.includes(this.searchTerm);
                    });

                    if (!found) return false;
                }

                return true;
            });
        }

        /**
         * Apply sorting
         */
        applySorting() {
            if (this.sortConfig) {
                this.filteredData.sort((a, b) => {
                    const aVal = a[this.sortConfig.columnId];
                    const bVal = b[this.sortConfig.columnId];

                    let comparison = 0;
                    if (aVal > bVal) comparison = 1;
                    else if (aVal < bVal) comparison = -1;

                    return this.sortConfig.direction === DATA_TABLE_CONSTANTS.SORT_DIRECTIONS.ASC ?
                        comparison : -comparison;
                });
            }
        }

        /**
         * Apply filters and sorting
         */
        applyFiltersAndSort() {
            this.applyFilters();
            this.applySorting();
            this.updatePagination();
        }

        /**
         * Set sort configuration
         */
        setSort(columnId, direction) {
            this.sortConfig = { columnId, direction };
            this.applyFiltersAndSort();
            this.emit('sortChanged', this.sortConfig);
        }

        /**
         * Clear sort
         */
        clearSort() {
            this.sortConfig = null;
            this.applyFiltersAndSort();
            this.emit('sortChanged', null);
        }

        /**
         * Update pagination
         */
        updatePagination() {
            this.pagination.totalItems = this.filteredData.length;
            this.pagination.totalPages = Math.ceil(
                this.filteredData.length / this.pagination.pageSize
            );

            // Ensure current page is valid
            if (this.pagination.currentPage > this.pagination.totalPages) {
                this.pagination.currentPage = Math.max(1, this.pagination.totalPages);
            }

            // Slice data for current page
            const startIndex = (this.pagination.currentPage - 1) * this.pagination.pageSize;
            const endIndex = startIndex + this.pagination.pageSize;
            this.currentData = this.filteredData.slice(startIndex, endIndex);
        }

        /**
         * Change page
         */
        changePage(page) {
            if (page >= 1 && page <= this.pagination.totalPages) {
                this.pagination.currentPage = page;
                this.updatePagination();
                this.emit('pageChanged', this.pagination.currentPage);
            }
        }

        /**
         * Change page size
         */
        changePageSize(pageSize) {
            this.pagination.pageSize = pageSize;
            this.pagination.currentPage = 1; // Reset to first page
            this.updatePagination();
            this.emit('pageSizeChanged', this.pagination.pageSize);
        }

        /**
         * Select row
         */
        selectRow(rowId) {
            this.selectedRows.add(rowId);
            this.emit('selectionChanged', Array.from(this.selectedRows));
        }

        /**
         * Deselect row
         */
        deselectRow(rowId) {
            this.selectedRows.delete(rowId);
            this.emit('selectionChanged', Array.from(this.selectedRows));
        }

        /**
         * Toggle row selection
         */
        toggleRowSelection(rowId) {
            if (this.selectedRows.has(rowId)) {
                this.deselectRow(rowId);
            } else {
                this.selectRow(rowId);
            }
        }

        /**
         * Select all rows
         */
        selectAllRows() {
            this.currentData.forEach(row => {
                if (row.id) {
                    this.selectedRows.add(row.id);
                }
            });
            this.emit('selectionChanged', Array.from(this.selectedRows));
        }

        /**
         * Deselect all rows
         */
        deselectAllRows() {
            this.selectedRows.clear();
            this.emit('selectionChanged', Array.from(this.selectedRows));
        }

        /**
         * Toggle all rows selection
         */
        toggleAllRows() {
            if (this.selectedRows.size === this.currentData.length) {
                this.deselectAllRows();
            } else {
                this.selectAllRows();
            }
        }

        /**
         * Expand row
         */
        expandRow(rowId) {
            this.expandedRows.add(rowId);
            this.emit('rowExpanded', rowId);
        }

        /**
         * Collapse row
         */
        collapseRow(rowId) {
            this.expandedRows.delete(rowId);
            this.emit('rowCollapsed', rowId);
        }

        /**
         * Toggle row expansion
         */
        toggleRowExpansion(rowId) {
            if (this.expandedRows.has(rowId)) {
                this.collapseRow(rowId);
            } else {
                this.expandRow(rowId);
            }
        }

        /**
         * Get current data
         */
        getCurrentData() {
            return this.currentData;
        }

        /**
         * Get filtered data
         */
        getFilteredData() {
            return this.filteredData;
        }

        /**
         * Get original data
         */
        getOriginalData() {
            return this.originalData;
        }

        /**
         * Get selected rows
         */
        getSelectedRows() {
            return Array.from(this.selectedRows);
        }

        /**
         * Get expanded rows
         */
        getExpandedRows() {
            return Array.from(this.expandedRows);
        }

        /**
         * Get statistics
         */
        getStatistics() {
            return {
                total: this.originalData.length,
                filtered: this.filteredData.length,
                currentPage: this.currentData.length,
                selected: this.selectedRows.size,
                totalPages: this.pagination.totalPages,
                currentPageNumber: this.pagination.currentPage
            };
        }

        /**
         * Add event listener
         */
        on(event, callback) {
            if (!this.eventListeners[event]) {
                this.eventListeners[event] = [];
            }
            this.eventListeners[event].push(callback);
        }

        /**
         * Remove event listener
         */
        off(event, callback) {
            if (this.eventListeners[event]) {
                this.eventListeners[event] = this.eventListeners[event].filter(cb => cb !== callback);
            }
        }

        /**
         * Emit event
         */
        emit(event, data) {
            if (this.eventListeners[event]) {
                this.eventListeners[event].forEach(callback => callback(data));
            }
        }
     }

    /**
     * Column manager class - handles column operations
     */
    class ColumnManager {
        constructor(columns = []) {
            this.columns = columns.map(col => ({
                ...col,
                visible: col.visible !== false,
                sortable: col.sortable !== false,
                filterable: col.filterable !== false,
                width: col.width || 'auto'
            }));
            this.visibleColumns = this.columns.filter(col => col.visible);
        }

        /**
         * Get all columns
         */
        getAllColumns() {
            return this.columns;
        }

        /**
         * Get visible columns
         */
        getVisibleColumns() {
            return this.visibleColumns;
        }

        /**
         * Get column by ID
         */
        getColumnById(columnId) {
            return this.columns.find(col => col.id === columnId);
        }

        /**
         * Show column
         */
        showColumn(columnId) {
            const column = this.getColumnById(columnId);
            if (column) {
                column.visible = true;
                this.updateVisibleColumns();
            }
        }

        /**
         * Hide column
         */
        hideColumn(columnId) {
            const column = this.getColumnById(columnId);
            if (column) {
                column.visible = false;
                this.updateVisibleColumns();
            }
        }

        /**
         * Toggle column visibility
         */
        toggleColumnVisibility(columnId) {
            const column = this.getColumnById(columnId);
            if (column) {
                column.visible = !column.visible;
                this.updateVisibleColumns();
            }
        }

        /**
         * Reorder columns
         */
        reorderColumns(columnIds) {
            const newColumns = [];
            columnIds.forEach(id => {
                const column = this.getColumnById(id);
                if (column) {
                    newColumns.push(column);
                }
            });

            // Add remaining columns that weren't specified
            this.columns.forEach(column => {
                if (!columnIds.includes(column.id)) {
                    newColumns.push(column);
                }
            });

            this.columns = newColumns;
            this.updateVisibleColumns();
        }

        /**
         * Resize column
         */
        resizeColumn(columnId, width) {
            const column = this.getColumnById(columnId);
            if (column) {
                column.width = width;
            }
        }

        /**
         * Update visible columns
         */
        updateVisibleColumns() {
            this.visibleColumns = this.columns.filter(col => col.visible);
        }

        /**
         * Get column count
         */
        getColumnCount() {
            return this.columns.length;
        }

        /**
         * Get visible column count
         */
        getVisibleColumnCount() {
            return this.visibleColumns.length;
        }

        /**
         * Get column statistics
         */
        getColumnStatistics() {
            return {
                total: this.columns.length,
                visible: this.visibleColumns.length,
                hidden: this.columns.length - this.visibleColumns.length,
                sortable: this.columns.filter(col => col.sortable).length,
                filterable: this.columns.filter(col => col.filterable).length
            };
        }
    }

    /**
     * Main DataTable class - orchestrates managers and exposes public API
     */
    class DataTable {
        constructor(options = {}) {
            const { data = [], columns = [], settings = {} } = options;
            this.constants = DATA_TABLE_CONSTANTS;
            this.dataManager = new DataManager(data, settings);
            this.columnManager = new ColumnManager(columns);
            this.exportManager = new ExportManager(this.dataManager, this.columnManager);

            this.eventListeners = {};
        }

        // Data operations
        setData(data) { this.dataManager.setData(data); }
        addFilter(columnId, operator, value) { this.dataManager.addFilter(columnId, operator, value); }
        removeFilter(columnId) { this.dataManager.removeFilter(columnId); }
        clearFilters() { this.dataManager.clearFilters(); }
        setSearchTerm(term) { this.dataManager.setSearchTerm(term); }
        setSort(columnId, direction) { this.dataManager.setSort(columnId, direction); }
        clearSort() { this.dataManager.clearSort(); }
        changePage(page) { this.dataManager.changePage(page); }
        changePageSize(pageSize) { this.dataManager.changePageSize(pageSize); }

        // Selection
        selectRow(id) { this.dataManager.selectRow(id); }
        deselectRow(id) { this.dataManager.deselectRow(id); }
        toggleRowSelection(id) { this.dataManager.toggleRowSelection(id); }
        selectAllRows() { this.dataManager.selectAllRows(); }
        deselectAllRows() { this.dataManager.deselectAllRows(); }

        // Expansion
        expandRow(id) { this.dataManager.expandRow(id); }
        collapseRow(id) { this.dataManager.collapseRow(id); }
        toggleRowExpansion(id) { this.dataManager.toggleRowExpansion(id); }

        // Accessors
        getCurrentData() { return this.dataManager.getCurrentData(); }
        getFilteredData() { return this.dataManager.getFilteredData(); }
        getOriginalData() { return this.dataManager.getOriginalData(); }
        getSelectedRows() { return this.dataManager.getSelectedRows(); }
        getExpandedRows() { return this.dataManager.getExpandedRows(); }
        getStatistics() { return this.dataManager.getStatistics(); }
        getVisibleColumns() { return this.columnManager.getVisibleColumns(); }

        // Column operations
        showColumn(id) { this.columnManager.showColumn(id); }
        hideColumn(id) { this.columnManager.hideColumn(id); }
        toggleColumnVisibility(id) { this.columnManager.toggleColumnVisibility(id); }
        reorderColumns(ids) { this.columnManager.reorderColumns(ids); }
        resizeColumn(id, width) { this.columnManager.resizeColumn(id, width); }

        // Export
        exportData(format, options = {}) { this.exportManager.exportData(format, options); }

        // Events
        on(event, callback) {
            if (!this.eventListeners[event]) this.eventListeners[event] = [];
            this.eventListeners[event].push(callback);
        }
        off(event, callback) {
            if (this.eventListeners[event]) {
                this.eventListeners[event] = this.eventListeners[event].filter(cb => cb !== callback);
            }
        }
        emit(event, data) {
            if (this.eventListeners[event]) {
                this.eventListeners[event].forEach(cb => cb(data));
            }
        }
    }

    /**
     * Export manager class - handles data export
     */
    class ExportManager {
        constructor(dataManager, columnManager) {
            this.dataManager = dataManager;
            this.columnManager = columnManager;
        }

        /**
         * Export to CSV
         */
        exportToCSV(options = {}) {
            const data = options.filtered ? this.dataManager.getFilteredData() : this.dataManager.getOriginalData();
            const columns = this.columnManager.getVisibleColumns();

            let csv = '';

            // Add headers
            if (options.includeHeaders !== false) {
                const headers = columns.map(col => col.header || col.id);
                csv += headers.join(',') + '\n';
            }

            // Add data rows
            data.forEach(row => {
                const values = columns.map(col => {
                    const value = row[col.id];
                    const formattedValue = DataUtils.formatValue(value, col.type, col.formatOptions);
                    // Escape quotes and wrap in quotes if contains comma
                    return formattedValue.includes(',') ? `"${formattedValue.replace(/"/g, '""')}"` : formattedValue;
                });
                csv += values.join(',') + '\n';
            });

            this.downloadFile(csv, 'text/csv', `${options.filename || 'data'}.csv`);
        }

        /**
         * Export to Excel (mock implementation - falls back to CSV)
         */
        exportToExcel(options = {}) {
            // For now, fall back to CSV
            // In a real implementation, you would use a library like SheetJS
            this.exportToCSV({ ...options, filename: options.filename || 'data' });
        }

        /**
         * Export to PDF (mock implementation - falls back to print)
         */
        exportToPDF(options = {}) {
            // For now, fall back to print
            // In a real implementation, you would use a library like jsPDF
            this.exportToPrint(options);
        }

        /**
         * Export to JSON
         */
        exportToJSON(options = {}) {
            const data = options.filtered ? this.dataManager.getFilteredData() : this.dataManager.getOriginalData();
            const json = JSON.stringify(data, null, 2);
            this.downloadFile(json, 'application/json', `${options.filename || 'data'}.json`);
        }

        /**
         * Export to print
         */
        exportToPrint(options = {}) {
            const data = options.filtered ? this.dataManager.getFilteredData() : this.dataManager.getOriginalData();
            const columns = this.columnManager.getVisibleColumns();

            let printContent = `
                <html>
                <head>
                    <title>${options.title || 'Data Export'}</title>
                    <style>
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <h2>${options.title || 'Data Export'}</h2>
                    <table>
                        <thead>
                            <tr>${columns.map(col => `<th>${col.header || col.id}</th>`).join('')}</tr>
                        </thead>
                        <tbody>
                            ${data.map(row => `
                                <tr>${columns.map(col => {
                                    const value = row[col.id];
                                    const formattedValue = DataUtils.formatValue(value, col.type, col.formatOptions);
                                    return `<td>${formattedValue}</td>`;
                                }).join('')}</tr>
                            `).join('')}
                        </tbody>
                    </table>
                </body>
                </html>
            `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }

        /**
         * Download file
         */
        downloadFile(content, mimeType, filename) {
            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        /**
         * Export data
         */
        exportData(format, options = {}) {
            try {
                switch (format.toLowerCase()) {
                    case 'csv':
                        this.exportToCSV(options);
                        break;
                    case 'excel':
                    case 'xlsx':
                        this.exportToExcel(options);
                        break;
                    case 'pdf':
                        this.exportToPDF(options);
                        break;
                    case 'json':
                        this.exportToJSON(options);
                        break;
                    case 'print':
                        this.exportToPrint(options);
                        break;
                    default:
                        throw new Error(`Unsupported export format: ${format}`);
                }
            } catch (error) {
                console.error('Export error:', error);
                throw error;
            }
        }
    }

    /**
     * Data utilities class
     */
    class DataUtils {
        /**
         * Format value based on column type
         */
        static formatValue(value, columnType, options = {}) {
            if (value === null || value === undefined) {
                return options.emptyText || '-';
            }

            switch (columnType) {
                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.DATE:
                    return this.formatDate(value, options.dateFormat || DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.dateFormat);

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.DATETIME:
                    return this.formatDateTime(value, options.datetimeFormat || DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.datetimeFormat);

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.NUMBER:
                    return this.formatNumber(value, options.decimalPlaces || DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.decimalPlaces);

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.CURRENCY:
                    return this.formatCurrency(value, options.currency || DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.currencyFormat);

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.PERCENTAGE:
                    return this.formatPercentage(value, options.decimalPlaces || DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.decimalPlaces);

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.BOOLEAN:
                    return this.formatBoolean(value, options.trueText, options.falseText);

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.STATUS:
                    return this.formatStatus(value);

                default:
                    return String(value);
            }
        }

        /**
         * Format date
         */
        static formatDate(date, format = DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.dateFormat) {
            const dateObj = new Date(date);
            if (isNaN(dateObj.getTime())) return '-';

            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            };

            return dateObj.toLocaleDateString(DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.locale, options);
        }

        /**
         * Format date and time
         */
        static formatDateTime(date, format = DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.datetimeFormat) {
            const dateObj = new Date(date);
            if (isNaN(dateObj.getTime())) return '-';

            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            };

            return dateObj.toLocaleDateString(DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.locale, options);
        }

        /**
         * Format number
         */
        static formatNumber(number, decimalPlaces = DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.decimalPlaces) {
            const num = parseFloat(number);
            if (isNaN(num)) return '-';

            return num.toLocaleString(DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.locale, {
                minimumFractionDigits: decimalPlaces,
                maximumFractionDigits: decimalPlaces
            });
        }

        /**
         * Format currency
         */
        static formatCurrency(amount, currency = DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.currencyFormat) {
            const num = parseFloat(amount);
            if (isNaN(num)) return '-';

            return new Intl.NumberFormat(DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.locale, {
                style: 'currency',
                currency: currency === 'IDR' ? 'IDR' : 'USD'
            }).format(num);
        }

        /**
         * Format percentage
         */
        static formatPercentage(value, decimalPlaces = DATA_TABLE_CONSTANTS.DEFAULT_SETTINGS.decimalPlaces) {
            const num = parseFloat(value);
            if (isNaN(num)) return '-';

            return (num * 100).toFixed(decimalPlaces) + '%';
        }

        /**
         * Format boolean
         */
        static formatBoolean(value, trueText = 'Ya', falseText = 'Tidak') {
            return value ? trueText : falseText;
        }

        /**
         * Format status
         */
        static formatStatus(value) {
            const statusConfig = DATA_TABLE_CONSTANTS.STATUS_BADGES[value.toUpperCase()];
            if (statusConfig) {
                return `<span class="sakip-status-badge ${statusConfig.class}">${statusConfig.text}</span>`;
            }
            return String(value);
        }

        /**
         * Parse value based on column type
         */
        static parseValue(value, columnType) {
            switch (columnType) {
                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.NUMBER:
                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.CURRENCY:
                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.PERCENTAGE:
                    return parseFloat(value) || 0;

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.DATE:
                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.DATETIME:
                    return new Date(value);

                case DATA_TABLE_CONSTANTS.COLUMN_TYPES.BOOLEAN:
                    return Boolean(value);

                default:
                    return String(value);
            }
        }

        /**
         * Compare values for sorting
         */
        static compareValues(a, b, columnType) {
            const valA = this.parseValue(a, columnType);
            const valB = this.parseValue(b, columnType);

            if (valA < valB) return -1;
            if (valA > valB) return 1;
            return 0;
        }

        /**
         * Filter value based on operator
         */
        static filterValue(value, filterValue, operator, columnType) {
            if (value === null || value === undefined) {
                return operator === DATA_TABLE_CONSTANTS.FILTER_OPERATORS.IS_EMPTY;
            }

            const formattedValue = this.formatValue(value, columnType);
            const searchValue = String(filterValue).toLowerCase();
            const targetValue = String(formattedValue).toLowerCase();

            switch (operator) {
                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.EQUALS:
                    return targetValue === searchValue;

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.NOT_EQUALS:
                    return targetValue !== searchValue;

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.CONTAINS:
                    return targetValue.includes(searchValue);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.NOT_CONTAINS:
                    return !targetValue.includes(searchValue);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.STARTS_WITH:
                    return targetValue.startsWith(searchValue);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.ENDS_WITH:
                    return targetValue.endsWith(searchValue);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.IS_EMPTY:
                    return !formattedValue || formattedValue === '-';

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.IS_NOT_EMPTY:
                    return formattedValue && formattedValue !== '-';

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.GREATER_THAN:
                    return this.parseValue(value, columnType) > this.parseValue(filterValue, columnType);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.LESS_THAN:
                    return this.parseValue(value, columnType) < this.parseValue(filterValue, columnType);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.GREATER_EQUAL:
                    return this.parseValue(value, columnType) >= this.parseValue(filterValue, columnType);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.LESS_EQUAL:
                    return this.parseValue(value, columnType) <= this.parseValue(filterValue, columnType);

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.IS_TRUE:
                    return Boolean(value) === true;

                case DATA_TABLE_CONSTANTS.FILTER_OPERATORS.IS_FALSE:
                    return Boolean(value) === false;

                default:
                    return true;
            }
        }

        /**
         * Deep clone object
         */
        static deepClone(obj) {
            if (obj === null || typeof obj !== 'object') return obj;
            if (obj instanceof Date) return new Date(obj.getTime());
            if (obj instanceof Array) return obj.map(item => this.deepClone(item));

            const clonedObj = {};
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    clonedObj[key] = this.deepClone(obj[key]);
                }
            }
            return clonedObj;
        }

        /**
         * Generate unique ID
         */
        static generateId(prefix = 'row') {
            return `${prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        }

        /**
         * Debounce function
         */
        static debounce(func, wait) {
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

        /**
         * Throttle function
         */
        static throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    }

    // Public API export
    const SAKIP_DATA_TABLES = {
        DATA_TABLE_CONSTANTS,
        DataManager,
        ColumnManager,
        ExportManager,
        DataUtils,
        DataTable
    };

    // Return for UMD wrapper
    return SAKIP_DATA_TABLES;
}));
