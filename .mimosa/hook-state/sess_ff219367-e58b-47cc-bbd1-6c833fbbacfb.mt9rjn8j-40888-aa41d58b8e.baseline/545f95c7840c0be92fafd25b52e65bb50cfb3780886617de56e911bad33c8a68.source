/**
 * SAKIP Chart Data API
 * Government-style chart data fetching and processing for SAKIP module
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
        global.SAKIP_CHART_DATA = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Chart Data Configuration
     */
    const CHART_CONFIG = {
        cacheTimeout: 300000, // 5 minutes
        refreshInterval: 30000, // 30 seconds for real-time data
        maxDataPoints: 100,
        defaultTimeRange: '12months',
        supportedChartTypes: [
            'line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea',
            'scatter', 'bubble', 'area', 'mixed'
        ],
        supportedTimeRanges: [
            '7days', '30days', '3months', '6months', '12months', 'ytd', 'all'
        ],
        dataAggregationLevels: [
            'daily', 'weekly', 'monthly', 'quarterly', 'yearly'
        ]
    };

    /**
     * Chart Data Cache
     */
    class ChartDataCache {
        constructor() {
            this.cache = new Map();
            this.timestamps = new Map();
        }

        /**
         * Generate cache key
         * @param {string} chartType - Chart type
         * @param {Object} params - Request parameters
         * @returns {string} - Cache key
         */
        generateKey(chartType, params = {}) {
            const sortedParams = Object.keys(params).sort().reduce((acc, key) => {
                acc[key] = params[key];
                return acc;
            }, {});
            return `${chartType}:${JSON.stringify(sortedParams)}`;
        }

        /**
         * Get cached data
         * @param {string} key - Cache key
         * @returns {Object|null} - Cached data or null
         */
        get(key) {
            const timestamp = this.timestamps.get(key);
            if (!timestamp) return null;

            const now = Date.now();
            if (now - timestamp > CHART_CONFIG.cacheTimeout) {
                this.cache.delete(key);
                this.timestamps.delete(key);
                return null;
            }

            return this.cache.get(key);
        }

        /**
         * Set cached data
         * @param {string} key - Cache key
         * @param {*} data - Data to cache
         */
        set(key, data) {
            this.cache.set(key, data);
            this.timestamps.set(key, Date.now());
        }

        /**
         * Clear cache
         */
        clear() {
            this.cache.clear();
            this.timestamps.clear();
        }

        /**
         * Get cache size
         * @returns {number} - Cache size
         */
        size() {
            return this.cache.size;
        }
    }

    /**
     * Chart Data Processor
     */
    class ChartDataProcessor {
        constructor() {
            this.processors = new Map();
            this.initializeProcessors();
        }

        /**
         * Initialize data processors
         */
        initializeProcessors() {
            this.processors.set('line', this.processLineChartData.bind(this));
            this.processors.set('bar', this.processBarChartData.bind(this));
            this.processors.set('pie', this.processPieChartData.bind(this));
            this.processors.set('doughnut', this.processDoughnutChartData.bind(this));
            this.processors.set('radar', this.processRadarChartData.bind(this));
            this.processors.set('polarArea', this.processPolarAreaChartData.bind(this));
            this.processors.set('scatter', this.processScatterChartData.bind(this));
            this.processors.set('bubble', this.processBubbleChartData.bind(this));
            this.processors.set('area', this.processAreaChartData.bind(this));
        }

        /**
         * Process chart data based on type
         * @param {string} chartType - Chart type
         * @param {Object} rawData - Raw data from API
         * @param {Object} options - Processing options
         * @returns {Object} - Processed chart data
         */
        processChartData(chartType, rawData, options = {}) {
            const processor = this.processors.get(chartType);
            if (!processor) {
                throw new Error(`Unsupported chart type: ${chartType}`);
            }
            return processor(rawData, options);
        }

        /**
         * Process line chart data
         */
        processLineChartData(rawData, options = {}) {
            const datasets = rawData.datasets || [];
            const labels = rawData.labels || [];

            return {
                labels,
                datasets: datasets.map(dataset => ({
                    label: dataset.label || 'Data',
                    data: dataset.data || [],
                    borderColor: dataset.color || this.generateColor(),
                    backgroundColor: dataset.backgroundColor || this.generateColor(0.1),
                    fill: dataset.fill || false,
                    tension: dataset.tension || 0.1,
                    pointRadius: dataset.pointRadius || 3,
                    pointHoverRadius: dataset.pointHoverRadius || 5
                }))
            };
        }

        /**
         * Process bar chart data
         */
        processBarChartData(rawData, options = {}) {
            const datasets = rawData.datasets || [];
            const labels = rawData.labels || [];

            return {
                labels,
                datasets: datasets.map(dataset => ({
                    label: dataset.label || 'Data',
                    data: dataset.data || [],
                    backgroundColor: dataset.color || this.generateColor(0.8),
                    borderColor: dataset.borderColor || this.generateColor(),
                    borderWidth: dataset.borderWidth || 1,
                    borderRadius: dataset.borderRadius || 0,
                    borderSkipped: dataset.borderSkipped || false
                }))
            };
        }

        /**
         * Process pie chart data
         */
        processPieChartData(rawData, options = {}) {
            const data = rawData.data || [];
            const labels = rawData.labels || [];

            return {
                labels,
                datasets: [{
                    data,
                    backgroundColor: data.map((_, index) =>
                        this.generateColor(0.8, index)
                    ),
                    borderColor: data.map((_, index) =>
                        this.generateColor(1, index)
                    ),
                    borderWidth: 1
                }]
            };
        }

        /**
         * Process doughnut chart data
         */
        processDoughnutChartData(rawData, options = {}) {
            const pieData = this.processPieChartData(rawData, options);
            return {
                ...pieData,
                datasets: pieData.datasets.map(dataset => ({
                    ...dataset,
                    cutout: options.cutout || '50%'
                }))
            };
        }

        /**
         * Process radar chart data
         */
        processRadarChartData(rawData, options = {}) {
            const datasets = rawData.datasets || [];
            const labels = rawData.labels || [];

            return {
                labels,
                datasets: datasets.map(dataset => ({
                    label: dataset.label || 'Data',
                    data: dataset.data || [],
                    borderColor: dataset.color || this.generateColor(),
                    backgroundColor: dataset.backgroundColor || this.generateColor(0.2),
                    pointBackgroundColor: dataset.pointColor || this.generateColor(),
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: dataset.color || this.generateColor()
                }))
            };
        }

        /**
         * Process polar area chart data
         */
        processPolarAreaChartData(rawData, options = {}) {
            const data = rawData.data || [];
            const labels = rawData.labels || [];

            return {
                labels,
                datasets: [{
                    data,
                    backgroundColor: data.map((_, index) =>
                        this.generateColor(0.5, index)
                    ),
                    borderColor: data.map((_, index) =>
                        this.generateColor(0.8, index)
                    ),
                    borderWidth: 1
                }]
            };
        }

        /**
         * Process scatter chart data
         */
        processScatterChartData(rawData, options = {}) {
            const datasets = rawData.datasets || [];

            return {
                datasets: datasets.map(dataset => ({
                    label: dataset.label || 'Data',
                    data: dataset.data || [],
                    backgroundColor: dataset.color || this.generateColor(0.6),
                    borderColor: dataset.color || this.generateColor(),
                    pointRadius: dataset.pointRadius || 5,
                    pointHoverRadius: dataset.pointHoverRadius || 7
                }))
            };
        }

        /**
         * Process bubble chart data
         */
        processBubbleChartData(rawData, options = {}) {
            const datasets = rawData.datasets || [];

            return {
                datasets: datasets.map(dataset => ({
                    label: dataset.label || 'Data',
                    data: dataset.data || [],
                    backgroundColor: dataset.color || this.generateColor(0.4),
                    borderColor: dataset.color || this.generateColor(),
                    borderWidth: 1
                }))
            };
        }

        /**
         * Process area chart data
         */
        processAreaChartData(rawData, options = {}) {
            const lineData = this.processLineChartData(rawData, options);
            return {
                ...lineData,
                datasets: lineData.datasets.map(dataset => ({
                    ...dataset,
                    fill: true
                }))
            };
        }

        /**
         * Generate color
         * @param {number} alpha - Alpha value
         * @param {number} index - Color index
         * @returns {string} - Color string
         */
        generateColor(alpha = 1, index = 0) {
            const colors = [
                'rgba(54, 162, 235, ${alpha})',   // Blue
                'rgba(255, 99, 132, ${alpha})',   // Red
                'rgba(255, 206, 86, ${alpha})',   // Yellow
                'rgba(75, 192, 192, ${alpha})',   // Teal
                'rgba(153, 102, 255, ${alpha})',   // Purple
                'rgba(255, 159, 64, ${alpha})',    // Orange
                'rgba(199, 199, 199, ${alpha})',  // Grey
                'rgba(83, 102, 255, ${alpha})',   // Indigo
                'rgba(40, 159, 64, ${alpha})',     // Green
                'rgba(210, 99, 132, ${alpha})',   // Pink
            ];
            return colors[index % colors.length];
        }
    }

    /**
     * Chart Data Fetcher
     */
    class ChartDataFetcher {
        constructor() {
            this.cache = new ChartDataCache();
            this.processor = new ChartDataProcessor();
            this.activeRequests = new Map();
        }

        /**
         * Fetch chart data
         * @param {string} chartType - Chart type
         * @param {string} endpoint - API endpoint
         * @param {Object} params - Request parameters
         * @param {Object} options - Fetch options
         * @returns {Promise} - Chart data promise
         */
        async fetchChartData(chartType, endpoint, params = {}, options = {}) {
            // Validate chart type
            if (!CHART_CONFIG.supportedChartTypes.includes(chartType)) {
                throw new Error(`Unsupported chart type: ${chartType}`);
            }

            // Generate cache key
            const cacheKey = this.cache.generateKey(chartType, { endpoint, ...params });

            // Check cache
            if (!options.forceRefresh) {
                const cachedData = this.cache.get(cacheKey);
                if (cachedData) {
                    return cachedData;
                }
            }

            // Check for active request
            if (this.activeRequests.has(cacheKey)) {
                return this.activeRequests.get(cacheKey);
            }

            // Create new request
            const requestPromise = this.makeChartDataRequest(chartType, endpoint, params, options);
            this.activeRequests.set(cacheKey, requestPromise);

            try {
                const result = await requestPromise;
                this.cache.set(cacheKey, result);
                return result;
            } finally {
                this.activeRequests.delete(cacheKey);
            }
        }

        /**
         * Make chart data request
         * @param {string} chartType - Chart type
         * @param {string} endpoint - API endpoint
         * @param {Object} params - Request parameters
         * @param {Object} options - Request options
         * @returns {Promise} - Chart data promise
         */
        async makeChartDataRequest(chartType, endpoint, params, options) {
            try {
                // Use SAKIP_AJAX if available, otherwise use fetch
                let response;
                if (typeof SAKIP_AJAX !== 'undefined') {
                    response = await SAKIP_AJAX.get(endpoint, { params });
                } else {
                    const url = new URL(endpoint, window.location.origin);
                    Object.keys(params).forEach(key => {
                        url.searchParams.append(key, params[key]);
                    });

                    const fetchResponse = await fetch(url.toString());
                    if (!fetchResponse.ok) {
                        throw new Error(`HTTP ${fetchResponse.status}: ${fetchResponse.statusText}`);
                    }
                    response = await fetchResponse.json();
                }

                // Process chart data
                return this.processor.processChartData(chartType, response, options);
            } catch (error) {
                console.error('Chart data fetch error:', error);
                throw error;
            }
        }

        /**
         * Fetch dashboard overview data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Dashboard data promise
         */
        async fetchDashboardOverview(params = {}) {
            const defaultParams = {
                timeRange: params.timeRange || CHART_CONFIG.defaultTimeRange,
                institutionId: params.institutionId || null,
                includeTrends: params.includeTrends !== false
            };

            return this.fetchChartData('line', '/api/sakip/v1/dashboard/overview', defaultParams);
        }

        /**
         * Fetch performance metrics data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Performance data promise
         */
        async fetchPerformanceMetrics(params = {}) {
            const defaultParams = {
                timeRange: params.timeRange || CHART_CONFIG.defaultTimeRange,
                institutionId: params.institutionId || null,
                metricType: params.metricType || 'overall',
                aggregation: params.aggregation || 'monthly'
            };

            return this.fetchChartData('bar', '/api/sakip/v1/dashboard/performance', defaultParams);
        }

        /**
         * Fetch assessment distribution data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Assessment distribution data promise
         */
        async fetchAssessmentDistribution(params = {}) {
            const defaultParams = {
                institutionId: params.institutionId || null,
                category: params.category || 'all',
                status: params.status || 'all'
            };

            return this.fetchChartData('pie', '/api/sakip/v1/dashboard/assessment-distribution', defaultParams);
        }

        /**
         * Fetch institution comparison data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Institution comparison data promise
         */
        async fetchInstitutionComparison(params = {}) {
            const defaultParams = {
                timeRange: params.timeRange || CHART_CONFIG.defaultTimeRange,
                institutionIds: params.institutionIds || [],
                metric: params.metric || 'score',
                topN: params.topN || 10
            };

            return this.fetchChartData('bar', '/api/sakip/v1/dashboard/institution-comparison', defaultParams);
        }

        /**
         * Fetch trend analysis data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Trend analysis data promise
         */
        async fetchTrendAnalysis(params = {}) {
            const defaultParams = {
                timeRange: params.timeRange || CHART_CONFIG.defaultTimeRange,
                institutionId: params.institutionId || null,
                indicators: params.indicators || [],
                aggregation: params.aggregation || 'monthly'
            };

            return this.fetchChartData('line', '/api/sakip/v1/dashboard/trend-analysis', defaultParams);
        }

        /**
         * Fetch score distribution data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Score distribution data promise
         */
        async fetchScoreDistribution(params = {}) {
            const defaultParams = {
                institutionId: params.institutionId || null,
                assessmentType: params.assessmentType || 'all',
                scoreRanges: params.scoreRanges || [
                    { min: 0, max: 25, label: 'Kurang' },
                    { min: 25, max: 50, label: 'Cukup' },
                    { min: 50, max: 75, label: 'Baik' },
                    { min: 75, max: 100, label: 'Sangat Baik' }
                ]
            };

            return this.fetchChartData('doughnut', '/api/sakip/v1/dashboard/score-distribution', defaultParams);
        }

        /**
         * Fetch completion rate data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Completion rate data promise
         */
        async fetchCompletionRate(params = {}) {
            const defaultParams = {
                timeRange: params.timeRange || CHART_CONFIG.defaultTimeRange,
                institutionId: params.institutionId || null,
                assessmentType: params.assessmentType || 'all',
                aggregation: params.aggregation || 'monthly'
            };

            return this.fetchChartData('area', '/api/sakip/v1/dashboard/completion-rate', defaultParams);
        }

        /**
         * Fetch radar comparison data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Radar comparison data promise
         */
        async fetchRadarComparison(params = {}) {
            const defaultParams = {
                institutionIds: params.institutionIds || [],
                indicators: params.indicators || [],
                assessmentType: params.assessmentType || 'overall'
            };

            return this.fetchChartData('radar', '/api/sakip/v1/dashboard/radar-comparison', defaultParams);
        }

        /**
         * Fetch real-time metrics data
         * @param {Object} params - Request parameters
         * @returns {Promise} - Real-time metrics data promise
         */
        async fetchRealTimeMetrics(params = {}) {
            const defaultParams = {
                metricTypes: params.metricTypes || ['assessments', 'submissions', 'scores'],
                institutionId: params.institutionId || null,
                window: params.window || '1hour'
            };

            return this.fetchChartData('line', '/api/sakip/v1/dashboard/real-time-metrics', defaultParams);
        }

        /**
         * Fetch export-ready data
         * @param {string} chartType - Chart type
         * @param {Object} params - Request parameters
         * @returns {Promise} - Export-ready data promise
         */
        async fetchExportData(chartType, params = {}) {
            const chartData = await this.fetchChartData(chartType, params.endpoint || '/api/sakip/v1/export/chart-data', params);

            return {
                chartType,
                data: chartData,
                metadata: {
                    generatedAt: new Date().toISOString(),
                    parameters: params,
                    dataPoints: this.countDataPoints(chartData)
                }
            };
        }

        /**
         * Count data points in chart data
         * @param {Object} chartData - Chart data
         * @returns {number} - Data point count
         */
        countDataPoints(chartData) {
            if (!chartData.datasets) return 0;

            return chartData.datasets.reduce((total, dataset) => {
                return total + (dataset.data ? dataset.data.length : 0);
            }, 0);
        }

        /**
         * Clear cache
         */
        clearCache() {
            this.cache.clear();
        }

        /**
         * Get cache size
         * @returns {number} - Cache size
         */
        getCacheSize() {
            return this.cache.size();
        }

        /**
         * Get supported chart types
         * @returns {Array} - Supported chart types
         */
        getSupportedChartTypes() {
            return [...CHART_CONFIG.supportedChartTypes];
        }

        /**
         * Get supported time ranges
         * @returns {Array} - Supported time ranges
         */
        getSupportedTimeRanges() {
            return [...CHART_CONFIG.supportedTimeRanges];
        }

        /**
         * Get supported aggregation levels
         * @returns {Array} - Supported aggregation levels
         */
        getSupportedAggregationLevels() {
            return [...CHART_CONFIG.dataAggregationLevels];
        }
    }

    /**
     * Chart Data Validator
     */
    class ChartDataValidator {
        /**
         * Validate chart data
         * @param {Object} data - Chart data
         * @param {string} chartType - Chart type
         * @returns {Object} - Validation result
         */
        validateChartData(data, chartType) {
            const errors = [];
            const warnings = [];

            // Basic structure validation
            if (!data || typeof data !== 'object') {
                errors.push('Chart data must be an object');
                return { valid: false, errors, warnings };
            }

            // Validate based on chart type
            switch (chartType) {
                case 'line':
                case 'bar':
                case 'radar':
                    this.validateDatasetChart(data, errors, warnings);
                    break;
                case 'pie':
                case 'doughnut':
                case 'polarArea':
                    this.validatePieChart(data, errors, warnings);
                    break;
                case 'scatter':
                case 'bubble':
                    this.validateScatterChart(data, errors, warnings);
                    break;
                default:
                    errors.push(`Unknown chart type: ${chartType}`);
            }

            return {
                valid: errors.length === 0,
                errors,
                warnings
            };
        }

        /**
         * Validate dataset-based charts
         */
        validateDatasetChart(data, errors, warnings) {
            if (!data.labels || !Array.isArray(data.labels)) {
                errors.push('Dataset charts require labels array');
            }

            if (!data.datasets || !Array.isArray(data.datasets)) {
                errors.push('Dataset charts require datasets array');
                return;
            }

            data.datasets.forEach((dataset, index) => {
                if (!dataset.data || !Array.isArray(dataset.data)) {
                    errors.push(`Dataset ${index} must have data array`);
                }

                if (dataset.data && data.labels && dataset.data.length !== data.labels.length) {
                    warnings.push(`Dataset ${index} data length doesn't match labels length`);
                }
            });
        }

        /**
         * Validate pie charts
         */
        validatePieChart(data, errors, warnings) {
            if (!data.labels || !Array.isArray(data.labels)) {
                errors.push('Pie charts require labels array');
            }

            if (!data.datasets || !Array.isArray(data.datasets) || data.datasets.length !== 1) {
                errors.push('Pie charts require exactly one dataset');
                return;
            }

            const dataset = data.datasets[0];
            if (!dataset.data || !Array.isArray(dataset.data)) {
                errors.push('Pie chart dataset must have data array');
            }

            if (dataset.data && data.labels && dataset.data.length !== data.labels.length) {
                warnings.push('Pie chart data length doesn\'t match labels length');
            }
        }

        /**
         * Validate scatter charts
         */
        validateScatterChart(data, errors, warnings) {
            if (!data.datasets || !Array.isArray(data.datasets)) {
                errors.push('Scatter charts require datasets array');
                return;
            }

            data.datasets.forEach((dataset, index) => {
                if (!dataset.data || !Array.isArray(dataset.data)) {
                    errors.push(`Dataset ${index} must have data array`);
                    return;
                }

                dataset.data.forEach((point, pointIndex) => {
                    if (!Array.isArray(point) || point.length < 2) {
                        errors.push(`Dataset ${index} point ${pointIndex} must be [x, y] array`);
                    }
                });
            });
        }
    }

    /**
     * Main Chart Data API
     */
    const SAKIP_CHART_DATA = {
        // Configuration
        config: CHART_CONFIG,

        // Core classes
        ChartDataCache,
        ChartDataProcessor,
        ChartDataFetcher,
        ChartDataValidator,

        // Create instances
        cache: new ChartDataCache(),
        processor: new ChartDataProcessor(),
        fetcher: new ChartDataFetcher(),
        validator: new ChartDataValidator(),

        // Convenience methods
        fetchDashboardOverview: (params) => fetcher.fetchDashboardOverview(params),
        fetchPerformanceMetrics: (params) => fetcher.fetchPerformanceMetrics(params),
        fetchAssessmentDistribution: (params) => fetcher.fetchAssessmentDistribution(params),
        fetchInstitutionComparison: (params) => fetcher.fetchInstitutionComparison(params),
        fetchTrendAnalysis: (params) => fetcher.fetchTrendAnalysis(params),
        fetchScoreDistribution: (params) => fetcher.fetchScoreDistribution(params),
        fetchCompletionRate: (params) => fetcher.fetchCompletionRate(params),
        fetchRadarComparison: (params) => fetcher.fetchRadarComparison(params),
        fetchRealTimeMetrics: (params) => fetcher.fetchRealTimeMetrics(params),
        fetchExportData: (chartType, params) => fetcher.fetchExportData(chartType, params),

        // Utility methods
        clearCache: () => cache.clear(),
        getCacheSize: () => cache.size(),
        getSupportedChartTypes: () => [...CHART_CONFIG.supportedChartTypes],
        getSupportedTimeRanges: () => [...CHART_CONFIG.supportedTimeRanges],
        getSupportedAggregationLevels: () => [...CHART_CONFIG.dataAggregationLevels],

        // Validation
        validateChartData: (data, chartType) => validator.validateChartData(data, chartType)
    };

    // Return for UMD wrapper
    return SAKIP_CHART_DATA;
}));
