/**
 * SAKIP Dashboard Charts
 * Government-style Chart.js configurations and dashboard analytics for SAKIP module
 *
 * @author SAKIP Development Team
 * @version 1.0.0
 * @since 2024
 */

(function(global, factory) {
    if (typeof exports === 'object' && typeof module !== 'undefined') {
        module.exports = factory();
    } else if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        global.SAKIP_DASHBOARD_CHARTS = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Chart Configuration Constants
     */
    const CHART_CONSTANTS = {
        // Government color palette
        COLORS: {
            primary: '#1f2937',      // Slate 800
            secondary: '#3b82f6',    // Blue 500
            success: '#10b981',      // Emerald 500
            warning: '#f59e0b',      // Amber 500
            danger: '#ef4444',         // Red 500
            info: '#06b6d4',         // Cyan 500
            light: '#f8fafc',        // Slate 50
            dark: '#0f172a',         // Slate 900
            muted: '#64748b'         // Slate 500
        },

        // Chart themes
        THEMES: {
            light: {
                backgroundColor: '#ffffff',
                gridColor: '#e2e8f0',
                textColor: '#1f2937',
                borderColor: '#d1d5db'
            },
            dark: {
                backgroundColor: '#1f2937',
                gridColor: '#374151',
                textColor: '#f9fafb',
                borderColor: '#4b5563'
            }
        },

        // Default chart options
        DEFAULT_OPTIONS: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            family: 'Inter, sans-serif',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    cornerRadius: 6,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: '#e2e8f0'
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif',
                            size: 11
                        }
                    }
                },
                y: {
                    grid: {
                        display: true,
                        color: '#e2e8f0'
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif',
                            size: 11
                        }
                    }
                }
            }
        },

        // Chart sizes
        SIZES: {
            small: { height: 200 },
            medium: { height: 300 },
            large: { height: 400 },
            full: { height: 500 }
        }
    };

    /**
     * Chart Configuration Generator
     */
    class ChartConfigGenerator {
        constructor() {
            this.colorPalette = this.generateColorPalette();
        }

        /**
         * Generate government color palette
         */
        generateColorPalette() {
            return [
                '#1f2937', // Slate 800
                '#3b82f6', // Blue 500
                '#10b981', // Emerald 500
                '#f59e0b', // Amber 500
                '#ef4444', // Red 500
                '#06b6d4', // Cyan 500
                '#8b5cf6', // Violet 500
                '#ec4899', // Pink 500
                '#84cc16', // Lime 500
                '#f97316'  // Orange 500
            ];
        }

        /**
         * Get color by index
         */
        getColor(index, alpha = 1) {
            const color = this.colorPalette[index % this.colorPalette.length];
            if (alpha < 1) {
                return this.hexToRgba(color, alpha);
            }
            return color;
        }

        /**
         * Convert hex to RGBA
         */
        hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        /**
         * Generate line chart configuration
         */
        generateLineChart(data, options = {}) {
            const config = {
                type: 'line',
                data: this.processLineData(data, options),
                options: this.generateLineOptions(options)
            };

            return config;
        }

        /**
         * Generate bar chart configuration
         */
        generateBarChart(data, options = {}) {
            const config = {
                type: 'bar',
                data: this.processBarData(data, options),
                options: this.generateBarOptions(options)
            };

            return config;
        }

        /**
         * Generate pie chart configuration
         */
        generatePieChart(data, options = {}) {
            const config = {
                type: 'pie',
                data: this.processPieData(data, options),
                options: this.generatePieOptions(options)
            };

            return config;
        }

        /**
         * Generate doughnut chart configuration
         */
        generateDoughnutChart(data, options = {}) {
            const config = this.generatePieChart(data, options);
            config.type = 'doughnut';
            config.options = {
                ...config.options,
                cutout: options.cutout || '50%'
            };

            return config;
        }

        /**
         * Generate radar chart configuration
         */
        generateRadarChart(data, options = {}) {
            const config = {
                type: 'radar',
                data: this.processRadarData(data, options),
                options: this.generateRadarOptions(options)
            };

            return config;
        }

        /**
         * Generate polar area chart configuration
         */
        generatePolarAreaChart(data, options = {}) {
            const config = {
                type: 'polarArea',
                data: this.processPolarAreaData(data, options),
                options: this.generatePolarAreaOptions(options)
            };

            return config;
        }

        /**
         * Process line chart data
         */
        processLineData(data, options) {
            const datasets = data.datasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                borderColor: dataset.color || this.getColor(index),
                backgroundColor: dataset.backgroundColor || this.getColor(index, 0.1),
                fill: dataset.fill || false,
                tension: dataset.tension || 0.1,
                pointRadius: dataset.pointRadius || 3,
                pointHoverRadius: dataset.pointHoverRadius || 5,
                borderWidth: dataset.borderWidth || 2
            }));

            return {
                labels: data.labels,
                datasets
            };
        }

        /**
         * Process bar chart data
         */
        processBarData(data, options) {
            const datasets = data.datasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: dataset.backgroundColor || this.getColor(index, 0.8),
                borderColor: dataset.borderColor || this.getColor(index),
                borderWidth: dataset.borderWidth || 1,
                borderRadius: dataset.borderRadius || 0,
                borderSkipped: dataset.borderSkipped || false
            }));

            return {
                labels: data.labels,
                datasets
            };
        }

        /**
         * Process pie chart data
         */
        processPieData(data, options) {
            const backgroundColors = data.data.map((_, index) =>
                this.getColor(index, 0.8)
            );

            return {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: backgroundColors,
                    borderColor: data.borderColor || '#ffffff',
                    borderWidth: data.borderWidth || 2
                }]
            };
        }

        /**
         * Process radar chart data
         */
        processRadarData(data, options) {
            const datasets = data.datasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                borderColor: dataset.borderColor || this.getColor(index),
                backgroundColor: dataset.backgroundColor || this.getColor(index, 0.2),
                pointBackgroundColor: dataset.pointColor || this.getColor(index),
                pointBorderColor: '#ffffff',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: dataset.borderColor || this.getColor(index)
            }));

            return {
                labels: data.labels,
                datasets
            };
        }

        /**
         * Process polar area chart data
         */
        processPolarAreaData(data, options) {
            const backgroundColors = data.data.map((_, index) =>
                this.getColor(index, 0.5)
            );

            return {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: backgroundColors,
                    borderColor: data.borderColor || '#ffffff',
                    borderWidth: data.borderWidth || 2
                }]
            };
        }

        /**
         * Generate line chart options
         */
        generateLineOptions(options) {
            return {
                ...CHART_CONSTANTS.DEFAULT_OPTIONS,
                plugins: {
                    ...CHART_CONSTANTS.DEFAULT_OPTIONS.plugins,
                    title: {
                        display: !!options.title,
                        text: options.title || '',
                        font: {
                            family: 'Inter, sans-serif',
                            size: 14,
                            weight: 'bold'
                        },
                        color: CHART_CONSTANTS.COLORS.primary,
                        padding: 20
                    }
                },
                scales: {
                    x: {
                        display: !options.hideXAxis,
                        grid: {
                            display: options.showGrid !== false,
                            color: CHART_CONSTANTS.THEMES.light.gridColor
                        },
                        ticks: {
                            display: !options.hideXLabels,
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            },
                            color: CHART_CONSTANTS.COLORS.muted
                        }
                    },
                    y: {
                        display: !options.hideYAxis,
                        grid: {
                            display: options.showGrid !== false,
                            color: CHART_CONSTANTS.THEMES.light.gridColor
                        },
                        ticks: {
                            display: !options.hideYLabels,
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            },
                            color: CHART_CONSTANTS.COLORS.muted
                        },
                        beginAtZero: options.beginAtZero !== false
                    }
                }
            };
        }

        /**
         * Generate bar chart options
         */
        generateBarOptions(options) {
            return {
                ...CHART_CONSTANTS.DEFAULT_OPTIONS,
                plugins: {
                    ...CHART_CONSTANTS.DEFAULT_OPTIONS.plugins,
                    title: {
                        display: !!options.title,
                        text: options.title || '',
                        font: {
                            family: 'Inter, sans-serif',
                            size: 14,
                            weight: 'bold'
                        },
                        color: CHART_CONSTANTS.COLORS.primary,
                        padding: 20
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            },
                            color: CHART_CONSTANTS.COLORS.muted
                        }
                    },
                    y: {
                        grid: {
                            display: options.showGrid !== false,
                            color: CHART_CONSTANTS.THEMES.light.gridColor
                        },
                        ticks: {
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            },
                            color: CHART_CONSTANTS.COLORS.muted
                        },
                        beginAtZero: options.beginAtZero !== false
                    }
                }
            };
        }

        /**
         * Generate pie chart options
         */
        generatePieOptions(options) {
            return {
                ...CHART_CONSTANTS.DEFAULT_OPTIONS,
                plugins: {
                    ...CHART_CONSTANTS.DEFAULT_OPTIONS.plugins,
                    title: {
                        display: !!options.title,
                        text: options.title || '',
                        font: {
                            family: 'Inter, sans-serif',
                            size: 14,
                            weight: 'bold'
                        },
                        color: CHART_CONSTANTS.COLORS.primary,
                        padding: 20
                    },
                    legend: {
                        ...CHART_CONSTANTS.DEFAULT_OPTIONS.plugins.legend,
                        position: options.legendPosition || 'right'
                    }
                }
            };
        }

        /**
         * Generate radar chart options
         */
        generateRadarOptions(options) {
            return {
                ...CHART_CONSTANTS.DEFAULT_OPTIONS,
                plugins: {
                    ...CHART_CONSTANTS.DEFAULT_OPTIONS.plugins,
                    title: {
                        display: !!options.title,
                        text: options.title || '',
                        font: {
                            family: 'Inter, sans-serif',
                            size: 14,
                            weight: 'bold'
                        },
                        color: CHART_CONSTANTS.COLORS.primary,
                        padding: 20
                    }
                },
                scales: {
                    r: {
                        grid: {
                            color: CHART_CONSTANTS.THEMES.light.gridColor
                        },
                        ticks: {
                            font: {
                                family: 'Inter, sans-serif',
                                size: 10
                            },
                            color: CHART_CONSTANTS.COLORS.muted
                        },
                        pointLabels: {
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            },
                            color: CHART_CONSTANTS.COLORS.primary
                        },
                        beginAtZero: true
                    }
                }
            };
        }

        /**
         * Generate polar area chart options
         */
        generatePolarAreaOptions(options) {
            return {
                ...CHART_CONSTANTS.DEFAULT_OPTIONS,
                plugins: {
                    ...CHART_CONSTANTS.DEFAULT_OPTIONS.plugins,
                    title: {
                        display: !!options.title,
                        text: options.title || '',
                        font: {
                            family: 'Inter, sans-serif',
                            size: 14,
                            weight: 'bold'
                        },
                        color: CHART_CONSTANTS.COLORS.primary,
                        padding: 20
                    },
                    legend: {
                        ...CHART_CONSTANTS.DEFAULT_OPTIONS.plugins.legend,
                        position: options.legendPosition || 'right'
                    }
                },
                scales: {
                    r: {
                        grid: {
                            color: CHART_CONSTANTS.THEMES.light.gridColor
                        },
                        ticks: {
                            display: false
                        }
                    }
                }
            };
        }
    }

    /**
     * Dashboard Chart Manager
     */
    class DashboardChartManager {
        constructor() {
            this.charts = new Map();
            this.configGenerator = new ChartConfigGenerator();
            this.updateIntervals = new Map();
            this.refreshCallbacks = new Map();
        }

        /**
         * Create chart
         */
        createChart(canvasId, chartType, data, options = {}) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                throw new Error(`Canvas element with ID '${canvasId}' not found`);
            }

            // Destroy existing chart if present
            this.destroyChart(canvasId);

            // Generate configuration
            let config;
            switch (chartType) {
                case 'line':
                    config = this.configGenerator.generateLineChart(data, options);
                    break;
                case 'bar':
                    config = this.configGenerator.generateBarChart(data, options);
                    break;
                case 'pie':
                    config = this.configGenerator.generatePieChart(data, options);
                    break;
                case 'doughnut':
                    config = this.configGenerator.generateDoughnutChart(data, options);
                    break;
                case 'radar':
                    config = this.configGenerator.generateRadarChart(data, options);
                    break;
                case 'polarArea':
                    config = this.configGenerator.generatePolarAreaChart(data, options);
                    break;
                default:
                    throw new Error(`Unsupported chart type: ${chartType}`);
            }

            // Create chart
            const ctx = canvas.getContext('2d');
            const chart = new Chart(ctx, config);

            // Store chart reference
            this.charts.set(canvasId, chart);

            // Set up auto-refresh if specified
            if (options.autoRefresh && options.refreshInterval) {
                this.setupAutoRefresh(canvasId, options.refreshInterval, options.refreshCallback);
            }

            return chart;
        }

        /**
         * Update chart data
         */
        updateChart(canvasId, data) {
            const chart = this.charts.get(canvasId);
            if (!chart) {
                console.warn(`Chart with ID '${canvasId}' not found`);
                return false;
            }

            chart.data = data;
            chart.update();
            return true;
        }

        /**
         * Destroy chart
         */
        destroyChart(canvasId) {
            const chart = this.charts.get(canvasId);
            if (chart) {
                chart.destroy();
                this.charts.delete(canvasId);
            }

            // Clear auto-refresh interval
            const interval = this.updateIntervals.get(canvasId);
            if (interval) {
                clearInterval(interval);
                this.updateIntervals.delete(canvasId);
                this.refreshCallbacks.delete(canvasId);
            }
        }

        /**
         * Setup auto-refresh
         */
        setupAutoRefresh(canvasId, interval, callback) {
            const existingInterval = this.updateIntervals.get(canvasId);
            if (existingInterval) {
                clearInterval(existingInterval);
            }

            const newInterval = setInterval(() => {
                if (callback && typeof callback === 'function') {
                    callback(canvasId);
                }
            }, interval);

            this.updateIntervals.set(canvasId, newInterval);
            this.refreshCallbacks.set(canvasId, callback);
        }

        /**
         * Get chart instance
         */
        getChart(canvasId) {
            return this.charts.get(canvasId);
        }

        /**
         * Get all chart instances
         */
        getAllCharts() {
            return new Map(this.charts);
        }

        /**
         * Resize all charts
         */
        resizeAllCharts() {
            this.charts.forEach(chart => {
                chart.resize();
            });
        }

        /**
         * Destroy all charts
         */
        destroyAllCharts() {
            this.charts.forEach((chart, canvasId) => {
                this.destroyChart(canvasId);
            });
        }
    }

    /**
     * Dashboard Analytics
     */
    class DashboardAnalytics {
        constructor() {
            this.chartManager = new DashboardChartManager();
            this.dataCache = new Map();
        }

        /**
         * Create overview chart
         */
        createOverviewChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Ringkasan Penilaian SAKIP',
                showGrid: true,
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'line', data, chartOptions);
        }

        /**
         * Create performance metrics chart
         */
        createPerformanceChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Metrik Kinerja',
                showGrid: true,
                beginAtZero: true,
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'bar', data, chartOptions);
        }

        /**
         * Create assessment distribution chart
         */
        createAssessmentDistributionChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Distribusi Penilaian',
                legendPosition: 'bottom',
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'doughnut', data, chartOptions);
        }

        /**
         * Create institution comparison chart
         */
        createInstitutionComparisonChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Perbandingan Institusi',
                showGrid: true,
                beginAtZero: true,
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'bar', data, chartOptions);
        }

        /**
         * Create trend analysis chart
         */
        createTrendAnalysisChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Analisis Tren',
                showGrid: true,
                fill: options.fill || false,
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'line', data, chartOptions);
        }

        /**
         * Create score distribution chart
         */
        createScoreDistributionChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Distribusi Nilai',
                legendPosition: 'bottom',
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'pie', data, chartOptions);
        }

        /**
         * Create completion rate chart
         */
        createCompletionRateChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Tingkat Penyelesaian',
                showGrid: true,
                fill: true,
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'line', data, chartOptions);
        }

        /**
         * Create radar comparison chart
         */
        createRadarComparisonChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Perbandingan Radar',
                autoRefresh: options.autoRefresh || false,
                refreshInterval: options.refreshInterval || 30000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'radar', data, chartOptions);
        }

        /**
         * Create real-time metrics chart
         */
        createRealTimeMetricsChart(canvasId, data, options = {}) {
            const chartOptions = {
                title: 'Metrik Real-time',
                showGrid: true,
                fill: false,
                autoRefresh: true,
                refreshInterval: options.refreshInterval || 5000,
                refreshCallback: options.refreshCallback || null
            };

            return this.chartManager.createChart(canvasId, 'line', data, chartOptions);
        }

        /**
         * Update chart data
         */
        updateChartData(canvasId, data) {
            return this.chartManager.updateChart(canvasId, data);
        }

        /**
         * Get chart instance
         */
        getChart(canvasId) {
            return this.chartManager.getChart(canvasId);
        }

        /**
         * Cache data
         */
        cacheData(key, data, ttl = 300000) { // 5 minutes default
            const cacheEntry = {
                data,
                timestamp: Date.now(),
                ttl
            };
            this.dataCache.set(key, cacheEntry);
        }

        /**
         * Get cached data
         */
        getCachedData(key) {
            const cacheEntry = this.dataCache.get(key);
            if (!cacheEntry) return null;

            const now = Date.now();
            if (now - cacheEntry.timestamp > cacheEntry.ttl) {
                this.dataCache.delete(key);
                return null;
            }

            return cacheEntry.data;
        }

        /**
         * Clear cache
         */
        clearCache() {
            this.dataCache.clear();
        }
    }

    /**
     * Chart Data Formatter
     */
    class ChartDataFormatter {
        /**
         * Format assessment data for charts
         */
        static formatAssessmentData(rawData) {
            return {
                labels: rawData.labels || [],
                datasets: rawData.datasets.map(dataset => ({
                    label: dataset.label,
                    data: dataset.values,
                    color: dataset.color
                }))
            };
        }

        /**
         * Format performance data for charts
         */
        static formatPerformanceData(rawData) {
            return {
                labels: rawData.categories || [],
                datasets: rawData.metrics.map(metric => ({
                    label: metric.name,
                    data: metric.values,
                    color: metric.color
                }))
            };
        }

        /**
         * Format distribution data for charts
         */
        static formatDistributionData(rawData) {
            return {
                labels: rawData.categories || [],
                data: rawData.values || [],
                colors: rawData.colors || []
            };
        }

        /**
         * Format comparison data for charts
         */
        static formatComparisonData(rawData) {
            return {
                labels: rawData.institutions || [],
                datasets: rawData.metrics.map(metric => ({
                    label: metric.name,
                    data: metric.values,
                    color: metric.color
                }))
            };
        }

        /**
         * Format trend data for charts
         */
        static formatTrendData(rawData) {
            return {
                labels: rawData.timeLabels || [],
                datasets: rawData.series.map(series => ({
                    label: series.name,
                    data: series.values,
                    color: series.color,
                    fill: series.fill || false
                }))
            };
        }
    }

    /**
     * Chart Utilities
     */
    class ChartUtilities {
        /**
         * Download chart as image
         */
        static downloadChartAsImage(chart, filename = 'chart.png') {
            const url = chart.toBase64Image();
            const link = document.createElement('a');
            link.download = filename;
            link.href = url;
            link.click();
        }

        /**
         * Download chart as PDF
         */
        static downloadChartAsPDF(chart, filename = 'chart.pdf') {
            // This would require a PDF library like jsPDF
            console.warn('PDF download requires jsPDF library');
        }

        /**
         * Print chart
         */
        static printChart(chart) {
            const url = chart.toBase64Image();
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head><title>Print Chart</title></head>
                    <body style="text-align: center; padding: 20px;">
                        <img src="${url}" style="max-width: 100%; height: auto;">
                        <script>window.print(); window.close();</script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }

        /**
         * Export chart data
         */
        static exportChartData(chart, format = 'json') {
            const data = chart.data;

            switch (format.toLowerCase()) {
                case 'json':
                    return JSON.stringify(data, null, 2);
                case 'csv':
                    return this.convertToCSV(data);
                case 'excel':
                    console.warn('Excel export requires additional library');
                    return null;
                default:
                    throw new Error(`Unsupported export format: ${format}`);
            }
        }

        /**
         * Convert chart data to CSV
         */
        static convertToCSV(data) {
            const headers = ['Label', ...data.datasets.map(d => d.label)];
            const rows = data.labels.map((label, index) => {
                const row = [label];
                data.datasets.forEach(dataset => {
                    row.push(dataset.data[index] || '');
                });
                return row;
            });

            return [headers, ...rows].map(row => row.join(',')).join('\n');
        }

        /**
         * Validate chart data
         */
        static validateChartData(data, chartType) {
            if (!data || typeof data !== 'object') {
                return { valid: false, error: 'Data must be an object' };
            }

            switch (chartType) {
                case 'line':
                case 'bar':
                case 'radar':
                    if (!data.labels || !Array.isArray(data.labels)) {
                        return { valid: false, error: 'Line/bar/radar charts require labels array' };
                    }
                    if (!data.datasets || !Array.isArray(data.datasets)) {
                        return { valid: false, error: 'Line/bar/radar charts require datasets array' };
                    }
                    break;
                case 'pie':
                case 'doughnut':
                case 'polarArea':
                    if (!data.labels || !Array.isArray(data.labels)) {
                        return { valid: false, error: 'Pie/doughnut charts require labels array' };
                    }
                    if (!data.data || !Array.isArray(data.data)) {
                        return { valid: false, error: 'Pie/doughnut charts require data array' };
                    }
                    break;
                default:
                    return { valid: false, error: `Unsupported chart type: ${chartType}` };
            }

            return { valid: true };
        }
    }

    /**
     * Main SAKIP Dashboard Charts API
     */
    const SAKIP_DASHBOARD_CHARTS = {
        // Constants
        constants: CHART_CONSTANTS,

        // Core classes
        ChartConfigGenerator,
        DashboardChartManager,
        DashboardAnalytics,
        ChartDataFormatter,
        ChartUtilities,

        // Create instances
        configGenerator: new ChartConfigGenerator(),
        chartManager: new DashboardChartManager(),
        analytics: new DashboardAnalytics(),

        // Convenience methods
        createChart: (canvasId, chartType, data, options) =>
            chartManager.createChart(canvasId, chartType, data, options),

        updateChart: (canvasId, data) =>
            chartManager.updateChart(canvasId, data),

        destroyChart: (canvasId) =>
            chartManager.destroyChart(canvasId),

        destroyAllCharts: () =>
            chartManager.destroyAllCharts(),

        // Analytics methods
        createOverviewChart: (canvasId, data, options) =>
            analytics.createOverviewChart(canvasId, data, options),

        createPerformanceChart: (canvasId, data, options) =>
            analytics.createPerformanceChart(canvasId, data, options),

        createAssessmentDistributionChart: (canvasId, data, options) =>
            analytics.createAssessmentDistributionChart(canvasId, data, options),

        createInstitutionComparisonChart: (canvasId, data, options) =>
            analytics.createInstitutionComparisonChart(canvasId, data, options),

        createTrendAnalysisChart: (canvasId, data, options) =>
            analytics.createTrendAnalysisChart(canvasId, data, options),

        createScoreDistributionChart: (canvasId, data, options) =>
            analytics.createScoreDistributionChart(canvasId, data, options),

        createCompletionRateChart: (canvasId, data, options) =>
            analytics.createCompletionRateChart(canvasId, data, options),

        createRadarComparisonChart: (canvasId, data, options) =>
            analytics.createRadarComparisonChart(canvasId, data, options),

        createRealTimeMetricsChart: (canvasId, data, options) =>
            analytics.createRealTimeMetricsChart(canvasId, data, options),

        // Utility methods
        downloadChartAsImage: (chart, filename) =>
            ChartUtilities.downloadChartAsImage(chart, filename),

        printChart: (chart) =>
            ChartUtilities.printChart(chart),

        exportChartData: (chart, format) =>
            ChartUtilities.exportChartData(chart, format),

        validateChartData: (data, chartType) =>
            ChartUtilities.validateChartData(data, chartType)
    };

    return SAKIP_DASHBOARD_CHARTS;
}));