/**
 * SAKIP Dashboard Module
 * Handles dashboard functionality and data visualization
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['chart.js'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('chart.js'));
    } else {
        root.SAKIP_DASHBOARD = factory(root.Chart);
    }
}(typeof self !== 'undefined' ? self : this, function (Chart) {

    /**
     * Dashboard Manager Class
     */
    class DashboardManager {
        constructor(options = {}) {
            this.options = {
                refreshInterval: 300000, // 5 minutes
                autoRefresh: true,
                chartColors: {
                    primary: '#3b82f6',
                    secondary: '#10b981',
                    success: '#22c55e',
                    warning: '#f59e0b',
                    danger: '#ef4444',
                    info: '#06b6d4'
                },
                ...options
            };
            
            this.charts = new Map();
            this.refreshTimer = null;
            this.isRefreshing = false;
            
            this.init();
        }

        /**
         * Initialize dashboard
         */
        init() {
            this.setupEventListeners();
            this.loadDashboardData();
            
            if (this.options.autoRefresh) {
                this.startAutoRefresh();
            }
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            document.addEventListener('click', (e) => {
                if (e.target.matches('[data-dashboard-action="refresh"]')) {
                    e.preventDefault();
                    this.refreshDashboard();
                }
                
                if (e.target.matches('[data-dashboard-action="export"]')) {
                    e.preventDefault();
                    const format = e.target.dataset.format || 'pdf';
                    this.exportDashboard(format);
                }
            });

            document.addEventListener('change', (e) => {
                if (e.target.matches('[data-dashboard-filter]')) {
                    this.applyFilter(e.target.dataset.filter, e.target.value);
                }
            });
        }

        /**
         * Load dashboard data
         */
        async loadDashboardData() {
            try {
                this.showLoadingState();
                
                const response = await this.makeApiRequest('/sakip/api/dashboard-data');
                const data = await response.json();
                
                this.updateDashboard(data);
                this.hideLoadingState();
                
            } catch (error) {
                console.error('Failed to load dashboard data:', error);
                this.showErrorState('Failed to load dashboard data');
            }
        }

        /**
         * Update dashboard with new data
         */
        updateDashboard(data) {
            this.updateKeyMetrics(data.metrics);
            this.updateCharts(data.charts);
            this.updateTables(data.tables);
            this.updateNotifications(data.notifications);
        }

        /**
         * Update key metrics
         */
        updateKeyMetrics(metrics) {
            if (!metrics) return;

            Object.keys(metrics).forEach(key => {
                const element = document.querySelector(`[data-metric="${key}"]`);
                if (element) {
                    const value = metrics[key];
                    element.textContent = this.formatMetric(value, key);
                    
                    // Add animation class
                    element.classList.add('metric-updated');
                    setTimeout(() => element.classList.remove('metric-updated'), 1000);
                }
            });
        }

        /**
         * Update charts
         */
        updateCharts(charts) {
            if (!charts) return;

            Object.keys(charts).forEach(chartId => {
                const chartData = charts[chartId];
                const canvas = document.getElementById(chartId);
                
                if (canvas) {
                    this.renderChart(canvas, chartData);
                }
            });
        }

        /**
         * Render individual chart
         */
        renderChart(canvas, data) {
            const ctx = canvas.getContext('2d');
            const chartId = canvas.id;
            
            // Destroy existing chart if present
            if (this.charts.has(chartId)) {
                this.charts.get(chartId).destroy();
            }

            const chart = new Chart(ctx, {
                type: data.type || 'bar',
                data: data.data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: data.title
                        }
                    },
                    ...data.options
                }
            });

            this.charts.set(chartId, chart);
        }

        /**
         * Update tables
         */
        updateTables(tables) {
            if (!tables) return;

            Object.keys(tables).forEach(tableId => {
                const tableData = tables[tableId];
                const table = document.getElementById(tableId);
                
                if (table && window.SAKIP_DATA_TABLES) {
                    const dataTable = new SAKIP_DATA_TABLES.DataTable(table, {
                        data: tableData.data,
                        columns: tableData.columns,
                        ...tableData.options
                    });
                }
            });
        }

        /**
         * Update notifications
         */
        updateNotifications(notifications) {
            if (!notifications || !window.SAKIP_NOTIFICATION) return;

            notifications.forEach(notification => {
                window.SAKIP_NOTIFICATION.show(notification);
            });
        }

        /**
         * Format metric value
         */
        formatMetric(value, type) {
            switch (type) {
                case 'percentage':
                    return `${value.toFixed(1)}%`;
                case 'currency':
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(value);
                case 'number':
                    return new Intl.NumberFormat('id-ID').format(value);
                default:
                    return value;
            }
        }

        /**
         * Apply filter
         */
        applyFilter(filterType, filterValue) {
            this.loadDashboardData();
        }

        /**
         * Refresh dashboard
         */
        async refreshDashboard() {
            if (this.isRefreshing) return;
            
            this.isRefreshing = true;
            await this.loadDashboardData();
            this.isRefreshing = false;
        }

        /**
         * Export dashboard
         */
        exportDashboard(format) {
            const url = `/sakip/reports/dashboard/export?format=${format}`;
            window.open(url, '_blank');
        }

        /**
         * Start auto refresh
         */
        startAutoRefresh() {
            this.refreshTimer = setInterval(() => {
                this.refreshDashboard();
            }, this.options.refreshInterval);
        }

        /**
         * Stop auto refresh
         */
        stopAutoRefresh() {
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
                this.refreshTimer = null;
            }
        }

        /**
         * Show loading state
         */
        showLoadingState() {
            document.body.classList.add('dashboard-loading');
        }

        /**
         * Hide loading state
         */
        hideLoadingState() {
            document.body.classList.remove('dashboard-loading');
        }

        /**
         * Show error state
         */
        showErrorState(message) {
            if (window.SAKIP_NOTIFICATION) {
                window.SAKIP_NOTIFICATION.show({
                    type: 'error',
                    title: 'Dashboard Error',
                    message: message
                });
            }
        }

        /**
         * Make API request
         */
        async makeApiRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };

            const response = await fetch(url, {
                ...defaultOptions,
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response;
        }

        /**
         * Destroy dashboard
         */
        destroy() {
            this.stopAutoRefresh();
            
            // Destroy all charts
            this.charts.forEach(chart => chart.destroy());
            this.charts.clear();
        }
    }

    /**
     * Public API
     */
    return {
        DashboardManager,
        
        /**
         * Initialize dashboard
         */
        init: function(options) {
            return new DashboardManager(options);
        },

        /**
         * Create chart
         */
        createChart: function(canvasId, data, options = {}) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return null;

            const ctx = canvas.getContext('2d');
            return new Chart(ctx, {
                type: data.type || 'bar',
                data: data.data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    ...options
                }
            });
        },

        /**
         * Update chart
         */
        updateChart: function(chartId, newData) {
            const chart = Chart.getChart(chartId);
            if (chart) {
                chart.data = newData;
                chart.update();
            }
        },

        /**
         * Format number for display
         */
        formatNumber: function(number, type = 'number') {
            switch (type) {
                case 'percentage':
                    return `${number.toFixed(1)}%`;
                case 'currency':
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(number);
                case 'number':
                default:
                    return new Intl.NumberFormat('id-ID').format(number);
            }
        }
    };

}));