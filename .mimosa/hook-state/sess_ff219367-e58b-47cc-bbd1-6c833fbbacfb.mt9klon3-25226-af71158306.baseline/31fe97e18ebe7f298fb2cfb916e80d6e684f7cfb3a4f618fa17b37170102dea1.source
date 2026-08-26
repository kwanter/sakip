/**
 * SAKIP Data Table Initialization Module
 * Provides initialization and management for data tables
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['./data-tables', './helpers'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('./data-tables'), require('./helpers'));
    } else {
        root.SAKIP_DATA_TABLE_INIT = factory(root.SAKIP_DATA_TABLES, root.SAKIP_HELPERS);
    }
}(typeof self !== 'undefined' ? self : this, function (DataTables, Helpers) {

    /**
     * Data Table Initialization Manager
     */
    class DataTableInitializer {
        constructor() {
            this.tables = new Map();
            this.defaultOptions = this.getDefaultOptions();
        }

        /**
         * Get default data table options
         */
        getDefaultOptions() {
            return {
                // Pagination
                pageSize: 10,
                pageSizeOptions: [10, 25, 50, 100],
                
                // Sorting
                defaultSortColumn: null,
                defaultSortDirection: 'asc',
                multiSort: false,
                
                // Filtering
                enableGlobalSearch: true,
                enableColumnFilters: true,
                searchDebounceMs: 300,
                
                // Selection
                enableSelection: false,
                selectionMode: 'single', // single, multiple
                
                // Export
                enableExport: true,
                exportFormats: ['csv', 'excel', 'pdf'],
                
                // Actions
                enableActions: true,
                actions: ['view', 'edit', 'delete'],
                
                // Loading
                showLoading: true,
                loadingText: 'Loading...',
                
                // Empty state
                emptyText: 'No data available',
                
                // Responsive
                responsive: true,
                
                // Styling
                striped: true,
                bordered: true,
                hover: true,
                
                // Language
                language: {
                    search: 'Search:',
                    show: 'Show',
                    entries: 'entries',
                    of: 'of',
                    to: 'to',
                    previous: 'Previous',
                    next: 'Next',
                    noData: 'No data available',
                    loading: 'Loading...',
                    processing: 'Processing...'
                }
            };
        }

        /**
         * Initialize data table
         */
        init(elementId, options = {}) {
            const element = document.getElementById(elementId);
            if (!element) {
                console.error(`Data table element with ID '${elementId}' not found`);
                return null;
            }

            // Merge options with defaults
            const mergedOptions = { ...this.defaultOptions, ...options };
            
            // Create data table instance
            const table = new DataTable(element, mergedOptions);
            
            // Store reference
            this.tables.set(elementId, table);
            
            return table;
        }

        /**
         * Initialize from data attributes
         */
        initFromDataAttributes() {
            const elements = document.querySelectorAll('[data-sakip-table]');
            
            elements.forEach(element => {
                const elementId = element.id;
                if (!elementId) {
                    console.warn('Data table element must have an ID');
                    return;
                }

                // Parse options from data attributes
                const options = this.parseDataAttributes(element);
                
                // Initialize table
                this.init(elementId, options);
            });
        }

        /**
         * Parse data attributes for options
         */
        parseDataAttributes(element) {
            const options = {};
            const attributes = element.attributes;
            
            for (let i = 0; i < attributes.length; i++) {
                const attr = attributes[i];
                const name = attr.name;
                const value = attr.value;
                
                // Parse data-sakip-* attributes
                if (name.startsWith('data-sakip-')) {
                    const optionName = name.replace('data-sakip-', '').replace(/-/g, '');
                    options[optionName] = this.parseValue(value);
                }
            }
            
            return options;
        }

        /**
         * Parse value from string
         */
        parseValue(value) {
            // Boolean values
            if (value === 'true') return true;
            if (value === 'false') return false;
            
            // Numbers
            if (/^\d+$/.test(value)) return parseInt(value, 10);
            if (/^\d+\.\d+$/.test(value)) return parseFloat(value);
            
            // Arrays (comma-separated)
            if (value.includes(',')) {
                return value.split(',').map(item => item.trim());
            }
            
            // JSON
            if (value.startsWith('{') || value.startsWith('[')) {
                try {
                    return JSON.parse(value);
                } catch (e) {
                    return value;
                }
            }
            
            // String
            return value;
        }

        /**
         * Get table instance
         */
        getTable(elementId) {
            return this.tables.get(elementId);
        }

        /**
         * Remove table instance
         */
        removeTable(elementId) {
            const table = this.tables.get(elementId);
            if (table) {
                table.destroy();
                this.tables.delete(elementId);
            }
        }

        /**
         * Get all table instances
         */
        getAllTables() {
            return Array.from(this.tables.values());
        }

        /**
         * Refresh table data
         */
        refreshTable(elementId, data) {
            const table = this.getTable(elementId);
            if (table) {
                table.setData(data);
            }
        }

        /**
         * Get selected rows
         */
        getSelectedRows(elementId) {
            const table = this.getTable(elementId);
            return table ? table.getSelectedRows() : [];
        }

        /**
         * Export table data
         */
        exportTable(elementId, format = 'csv') {
            const table = this.getTable(elementId);
            if (table) {
                table.exportData(format);
            }
        }

        /**
         * Apply filters
         */
        applyFilters(elementId, filters) {
            const table = this.getTable(elementId);
            if (table) {
                table.setFilters(filters);
            }
        }

        /**
         * Reset filters
         */
        resetFilters(elementId) {
            const table = this.getTable(elementId);
            if (table) {
                table.resetFilters();
            }
        }

        /**
         * Set page size
         */
        setPageSize(elementId, size) {
            const table = this.getTable(elementId);
            if (table) {
                table.setPageSize(size);
            }
        }

        /**
         * Go to page
         */
        goToPage(elementId, page) {
            const table = this.getTable(elementId);
            if (table) {
                table.goToPage(page);
            }
        }

        /**
         * Sort by column
         */
        sortBy(elementId, column, direction = 'asc') {
            const table = this.getTable(elementId);
            if (table) {
                table.sortBy(column, direction);
            }
        }

        /**
         * Get table state
         */
        getTableState(elementId) {
            const table = this.getTable(elementId);
            return table ? table.getState() : null;
        }

        /**
         * Restore table state
         */
        restoreTableState(elementId, state) {
            const table = this.getTable(elementId);
            if (table) {
                table.setState(state);
            }
        }

        /**
         * Create table from configuration
         */
        createTableFromConfig(config) {
            const element = document.createElement('div');
            element.id = config.id;
            element.className = 'sakip-data-table';
            
            // Add to DOM
            const container = document.getElementById(config.container) || document.body;
            container.appendChild(element);
            
            // Initialize table
            return this.init(config.id, config.options);
        }

        /**
         * Initialize common table types
         */
        initIndicatorTable(elementId, options = {}) {
            const defaultOptions = {
                columns: [
                    { field: 'kode', title: 'Kode', sortable: true },
                    { field: 'nama', title: 'Nama Indikator', sortable: true },
                    { field: 'kategori', title: 'Kategori', sortable: true },
                    { field: 'satuan', title: 'Satuan', sortable: true },
                    { field: 'target', title: 'Target', sortable: true },
                    { field: 'realisasi', title: 'Realisasi', sortable: true },
                    { field: 'capaian', title: 'Capaian', sortable: true },
                    { field: 'status', title: 'Status', sortable: true }
                ],
                enableSelection: true,
                enableExport: true,
                ...options
            };
            
            return this.init(elementId, defaultOptions);
        }

        /**
         * Initialize program table
         */
        initProgramTable(elementId, options = {}) {
            const defaultOptions = {
                columns: [
                    { field: 'kode', title: 'Kode Program', sortable: true },
                    { field: 'nama', title: 'Nama Program', sortable: true },
                    { field: 'instansi', title: 'Instansi', sortable: true },
                    { field: 'anggaran', title: 'Anggaran', sortable: true },
                    { field: 'realisasi_anggaran', title: 'Realisasi', sortable: true },
                    { field: 'capaian_anggaran', title: 'Capaian', sortable: true },
                    { field: 'status', title: 'Status', sortable: true }
                ],
                enableSelection: true,
                enableExport: true,
                ...options
            };
            
            return this.init(elementId, defaultOptions);
        }

        /**
         * Initialize kegiatan table
         */
        initKegiatanTable(elementId, options = {}) {
            const defaultOptions = {
                columns: [
                    { field: 'kode', title: 'Kode Kegiatan', sortable: true },
                    { field: 'nama', title: 'Nama Kegiatan', sortable: true },
                    { field: 'program', title: 'Program', sortable: true },
                    { field: 'instansi', title: 'Instansi', sortable: true },
                    { field: 'anggaran', title: 'Anggaran', sortable: true },
                    { field: 'target', title: 'Target', sortable: true },
                    { field: 'realisasi', title: 'Realisasi', sortable: true },
                    { field: 'status', title: 'Status', sortable: true }
                ],
                enableSelection: true,
                enableExport: true,
                ...options
            };
            
            return this.init(elementId, defaultOptions);
        }

        /**
         * Initialize laporan table
         */
        initLaporanTable(elementId, options = {}) {
            const defaultOptions = {
                columns: [
                    { field: 'nomor', title: 'Nomor Laporan', sortable: true },
                    { field: 'judul', title: 'Judul Laporan', sortable: true },
                    { field: 'jenis', title: 'Jenis', sortable: true },
                    { field: 'periode', title: 'Periode', sortable: true },
                    { field: 'instansi', title: 'Instansi', sortable: true },
                    { field: 'tanggal', title: 'Tanggal', sortable: true },
                    { field: 'status', title: 'Status', sortable: true }
                ],
                enableSelection: true,
                enableExport: true,
                ...options
            };
            
            return this.init(elementId, defaultOptions);
        }
    }

    /**
     * Create and return initializer instance
     */
    const initializer = new DataTableInitializer();

    /**
     * Public API
     */
    return {
        init: initializer.init.bind(initializer),
        initFromDataAttributes: initializer.initFromDataAttributes.bind(initializer),
        getTable: initializer.getTable.bind(initializer),
        removeTable: initializer.removeTable.bind(initializer),
        getAllTables: initializer.getAllTables.bind(initializer),
        refreshTable: initializer.refreshTable.bind(initializer),
        getSelectedRows: initializer.getSelectedRows.bind(initializer),
        exportTable: initializer.exportTable.bind(initializer),
        applyFilters: initializer.applyFilters.bind(initializer),
        resetFilters: initializer.resetFilters.bind(initializer),
        setPageSize: initializer.setPageSize.bind(initializer),
        goToPage: initializer.goToPage.bind(initializer),
        sortBy: initializer.sortBy.bind(initializer),
        getTableState: initializer.getTableState.bind(initializer),
        restoreTableState: initializer.restoreTableState.bind(initializer),
        createTableFromConfig: initializer.createTableFromConfig.bind(initializer),
        initIndicatorTable: initializer.initIndicatorTable.bind(initializer),
        initProgramTable: initializer.initProgramTable.bind(initializer),
        initKegiatanTable: initializer.initKegiatanTable.bind(initializer),
        initLaporanTable: initializer.initLaporanTable.bind(initializer)
    };

}));