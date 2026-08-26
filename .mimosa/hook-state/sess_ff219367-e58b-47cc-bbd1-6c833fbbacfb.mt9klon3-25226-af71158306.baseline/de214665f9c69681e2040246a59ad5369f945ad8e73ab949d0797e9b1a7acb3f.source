/**
 * SAKIP AJAX Request Handlers
 * Government-style AJAX utilities for SAKIP module
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
        global.SAKIP_AJAX = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * AJAX Configuration
     */
    const AJAX_CONFIG = {
        timeout: 30000,
        retryAttempts: 3,
        retryDelay: 1000,
        enableCSRF: true,
        enableAuth: true,
        enableLogging: true,
        enableCaching: false,
        cacheTimeout: 300000, // 5 minutes
        enableRateLimiting: true,
        rateLimitDelay: 100,
        maxConcurrentRequests: 10,
        enableRequestQueue: true
    };

    /**
     * Request Queue Management
     */
    class RequestQueue {
        constructor(maxConcurrent = 10) {
            this.maxConcurrent = maxConcurrent;
            this.activeRequests = 0;
            this.pendingRequests = [];
            this.requestHistory = [];
            this.rateLimitTracker = new Map();
        }

        /**
         * Add request to queue
         * @param {Function} requestFn - Request function to execute
         * @param {Object} options - Request options
         * @returns {Promise} - Request promise
         */
        add(requestFn, options = {}) {
            return new Promise((resolve, reject) => {
                const queueItem = {
                    requestFn,
                    options,
                    resolve,
                    reject,
                    timestamp: Date.now(),
                    id: this.generateRequestId()
                };

                this.pendingRequests.push(queueItem);
                this.processQueue();
            });
        }

        /**
         * Process request queue
         */
        async processQueue() {
            if (this.activeRequests >= this.maxConcurrent || this.pendingRequests.length === 0) {
                return;
            }

            const queueItem = this.pendingRequests.shift();
            this.activeRequests++;

            try {
                // Rate limiting check
                if (AJAX_CONFIG.enableRateLimiting) {
                    await this.checkRateLimit(queueItem);
                }

                const result = await queueItem.requestFn(queueItem.options);
                queueItem.resolve(result);
                this.logRequest(queueItem, 'success');
            } catch (error) {
                queueItem.reject(error);
                this.logRequest(queueItem, 'error', error);
            } finally {
                this.activeRequests--;
                this.requestHistory.push({
                    ...queueItem,
                    completedAt: Date.now()
                });

                // Process next request
                setTimeout(() => this.processQueue(), AJAX_CONFIG.rateLimitDelay);
            }
        }

        /**
         * Check rate limiting
         * @param {Object} queueItem - Queue item
         */
        async checkRateLimit(queueItem) {
            const endpoint = queueItem.options.url || 'unknown';
            const now = Date.now();
            const key = `${endpoint}_${Math.floor(now / 1000)}`;

            const requestCount = this.rateLimitTracker.get(key) || 0;
            if (requestCount >= 5) { // Max 5 requests per second per endpoint
                const waitTime = 1000 - (now % 1000);
                await this.sleep(waitTime);
            }

            this.rateLimitTracker.set(key, requestCount + 1);

            // Clean up old entries
            for (const [k, timestamp] of this.rateLimitTracker.entries()) {
                if (now - timestamp > 1000) {
                    this.rateLimitTracker.delete(k);
                }
            }
        }

        /**
         * Sleep utility
         * @param {number} ms - Milliseconds to sleep
         * @returns {Promise} - Sleep promise
         */
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        /**
         * Generate unique request ID
         * @returns {string} - Request ID
         */
        generateRequestId() {
            return `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        }

        /**
         * Log request
         * @param {Object} queueItem - Queue item
         * @param {string} status - Request status
         * @param {Error} error - Error object (optional)
         */
        logRequest(queueItem, status, error = null) {
            if (!AJAX_CONFIG.enableLogging) return;

            const logEntry = {
                id: queueItem.id,
                url: queueItem.options.url || 'unknown',
                method: queueItem.options.method || 'GET',
                status,
                duration: Date.now() - queueItem.timestamp,
                timestamp: new Date().toISOString()
            };

            if (error) {
                logEntry.error = error.message;
            }

            console.log(`[SAKIP_AJAX] Request ${status}:`, logEntry);
        }

        /**
         * Get queue status
         * @returns {Object} - Queue status
         */
        getStatus() {
            return {
                activeRequests: this.activeRequests,
                pendingRequests: this.pendingRequests.length,
                maxConcurrent: this.maxConcurrent,
                totalProcessed: this.requestHistory.length
            };
        }
    }

    /**
     * Response Cache
     */
    class ResponseCache {
        constructor() {
            this.cache = new Map();
        }

        /**
         * Generate cache key
         * @param {string} url - Request URL
         * @param {Object} options - Request options
         * @returns {string} - Cache key
         */
        generateKey(url, options = {}) {
            const method = options.method || 'GET';
            const body = options.body ? JSON.stringify(options.body) : '';
            return `${method}:${url}:${body}`;
        }

        /**
         * Get cached response
         * @param {string} key - Cache key
         * @returns {Object|null} - Cached response or null
         */
        get(key) {
            const entry = this.cache.get(key);
            if (!entry) return null;

            const now = Date.now();
            if (now - entry.timestamp > AJAX_CONFIG.cacheTimeout) {
                this.cache.delete(key);
                return null;
            }

            return entry.data;
        }

        /**
         * Set cached response
         * @param {string} key - Cache key
         * @param {*} data - Response data
         */
        set(key, data) {
            this.cache.set(key, {
                data,
                timestamp: Date.now()
            });
        }

        /**
         * Clear cache
         */
        clear() {
            this.cache.clear();
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
     * CSRF Token Management
     */
    class CSRFManager {
        constructor() {
            this.token = null;
            this.tokenName = 'X-CSRF-TOKEN';
            this.headerName = 'X-CSRF-TOKEN';
        }

        /**
         * Get CSRF token
         * @returns {string|null} - CSRF token
         */
        getToken() {
            if (this.token) return this.token;

            // Try to get from meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                this.token = metaTag.getAttribute('content');
                return this.token;
            }

            // Try to get from cookie
            this.token = this.getCookie('csrf_token');
            return this.token;
        }

        /**
         * Set CSRF token
         * @param {string} token - CSRF token
         */
        setToken(token) {
            this.token = token;
        }

        /**
         * Get cookie value
         * @param {string} name - Cookie name
         * @returns {string|null} - Cookie value
         */
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        /**
         * Add CSRF token to headers
         * @param {Object} headers - Request headers
         * @returns {Object} - Headers with CSRF token
         */
        addTokenToHeaders(headers = {}) {
            if (!AJAX_CONFIG.enableCSRF) return headers;

            const token = this.getToken();
            if (token) {
                headers[this.headerName] = token;
            }
            return headers;
        }
    }

    /**
     * Authentication Manager
     */
    class AuthManager {
        constructor() {
            this.token = null;
            this.refreshToken = null;
            this.tokenHeader = 'Authorization';
            this.tokenPrefix = 'Bearer';
        }

        /**
         * Set authentication token
         * @param {string} token - Authentication token
         * @param {string} refreshToken - Refresh token (optional)
         */
        setToken(token, refreshToken = null) {
            this.token = token;
            if (refreshToken) {
                this.refreshToken = refreshToken;
            }
        }

        /**
         * Get authentication token
         * @returns {string|null} - Authentication token
         */
        getToken() {
            return this.token;
        }

        /**
         * Clear authentication tokens
         */
        clearToken() {
            this.token = null;
            this.refreshToken = null;
        }

        /**
         * Add authentication token to headers
         * @param {Object} headers - Request headers
         * @returns {Object} - Headers with authentication token
         */
        addTokenToHeaders(headers = {}) {
            if (!AJAX_CONFIG.enableAuth) return headers;

            const token = this.getToken();
            if (token) {
                headers[this.tokenHeader] = `${this.tokenPrefix} ${token}`;
            }
            return headers;
        }

        /**
         * Check if authenticated
         * @returns {boolean} - Authentication status
         */
        isAuthenticated() {
            return !!this.token;
        }
    }

    /**
     * Error Handler
     */
    class ErrorHandler {
        constructor() {
            this.errorCallbacks = new Map();
        }

        /**
         * Register error callback
         * @param {string} type - Error type
         * @param {Function} callback - Error callback
         */
        onError(type, callback) {
            if (!this.errorCallbacks.has(type)) {
                this.errorCallbacks.set(type, []);
            }
            this.errorCallbacks.get(type).push(callback);
        }

        /**
         * Handle error
         * @param {Error} error - Error object
         * @param {Object} request - Request object
         */
        handleError(error, request = {}) {
            const errorType = this.categorizeError(error);
            const callbacks = this.errorCallbacks.get(errorType) || [];

            callbacks.forEach(callback => {
                try {
                    callback(error, request);
                } catch (callbackError) {
                    console.error('Error in error callback:', callbackError);
                }
            });

            // Default error handling
            this.defaultErrorHandler(error, request);
        }

        /**
         * Categorize error
         * @param {Error} error - Error object
         * @returns {string} - Error category
         */
        categorizeError(error) {
            if (error.name === 'AbortError' || error.message.includes('timeout')) {
                return 'timeout';
            }
            if (error.message.includes('network')) {
                return 'network';
            }
            if (error.status === 401) {
                return 'unauthorized';
            }
            if (error.status === 403) {
                return 'forbidden';
            }
            if (error.status === 404) {
                return 'not_found';
            }
            if (error.status >= 500) {
                return 'server_error';
            }
            return 'general';
        }

        /**
         * Default error handler
         * @param {Error} error - Error object
         * @param {Object} request - Request object
         */
        defaultErrorHandler(error, request) {
            const errorType = this.categorizeError(error);

            switch (errorType) {
                case 'timeout':
                    console.error('Request timeout:', request.url);
                    break;
                case 'network':
                    console.error('Network error:', error.message);
                    break;
                case 'unauthorized':
                    console.error('Unauthorized access - redirecting to login');
                    // Handle unauthorized access
                    break;
                case 'server_error':
                    console.error('Server error:', error.message);
                    break;
                default:
                    console.error('AJAX error:', error.message);
            }
        }
    }

    /**
     * Main AJAX Manager
     */
    class AJAXManager {
        constructor() {
            this.queue = new RequestQueue(AJAX_CONFIG.maxConcurrentRequests);
            this.cache = new ResponseCache();
            this.csrf = new CSRFManager();
            this.auth = new AuthManager();
            this.errorHandler = new ErrorHandler();
            this.requestInterceptors = [];
            this.responseInterceptors = [];
        }

        /**
         * Add request interceptor
         * @param {Function} interceptor - Request interceptor function
         */
        addRequestInterceptor(interceptor) {
            this.requestInterceptors.push(interceptor);
        }

        /**
         * Add response interceptor
         * @param {Function} interceptor - Response interceptor function
         */
        addResponseInterceptor(interceptor) {
            this.responseInterceptors.push(interceptor);
        }

        /**
         * Execute request interceptors
         * @param {Object} options - Request options
         * @returns {Object} - Modified options
         */
        async executeRequestInterceptors(options) {
            let modifiedOptions = { ...options };

            for (const interceptor of this.requestInterceptors) {
                try {
                    modifiedOptions = await interceptor(modifiedOptions) || modifiedOptions;
                } catch (error) {
                    console.error('Request interceptor error:', error);
                }
            }

            return modifiedOptions;
        }

        /**
         * Execute response interceptors
         * @param {*} response - Response data
         * @param {Object} options - Request options
         * @returns {*} - Modified response
         */
        async executeResponseInterceptors(response, options) {
            let modifiedResponse = response;

            for (const interceptor of this.responseInterceptors) {
                try {
                    modifiedResponse = await interceptor(modifiedResponse, options) || modifiedResponse;
                } catch (error) {
                    console.error('Response interceptor error:', error);
                }
            }

            return modifiedResponse;
        }

        /**
         * Make AJAX request
         * @param {string} url - Request URL
         * @param {Object} options - Request options
         * @returns {Promise} - Request promise
         */
        async request(url, options = {}) {
            // Add to queue if enabled
            if (AJAX_CONFIG.enableRequestQueue) {
                return this.queue.add(() => this.executeRequest(url, options), options);
            }

            return this.executeRequest(url, options);
        }

        /**
         * Execute request
         * @param {string} url - Request URL
         * @param {Object} options - Request options
         * @returns {Promise} - Request promise
         */
        async executeRequest(url, options = {}) {
            const requestOptions = await this.prepareRequest(url, options);

            // Check cache for GET requests
            if (requestOptions.method === 'GET' && AJAX_CONFIG.enableCaching) {
                const cacheKey = this.cache.generateKey(url, requestOptions);
                const cachedResponse = this.cache.get(cacheKey);
                if (cachedResponse) {
                    return cachedResponse;
                }
            }

            let retryCount = 0;

            while (retryCount <= AJAX_CONFIG.retryAttempts) {
                try {
                    const response = await this.makeFetchRequest(requestOptions);

                    // Cache successful GET responses
                    if (requestOptions.method === 'GET' && AJAX_CONFIG.enableCaching && response.ok) {
                        const responseData = await response.clone().json();
                        this.cache.set(cacheKey, responseData);
                    }

                    const processedResponse = await this.processResponse(response, requestOptions);
                    return processedResponse;

                } catch (error) {
                    if (retryCount === AJAX_CONFIG.retryAttempts) {
                        this.errorHandler.handleError(error, requestOptions);
                        throw error;
                    }

                    retryCount++;
                    await this.sleep(AJAX_CONFIG.retryDelay * retryCount);
                }
            }
        }

        /**
         * Prepare request
         * @param {string} url - Request URL
         * @param {Object} options - Request options
         * @returns {Object} - Prepared options
         */
        async prepareRequest(url, options = {}) {
            let preparedOptions = {
                url,
                method: 'GET',
                headers: {},
                timeout: AJAX_CONFIG.timeout,
                ...options
            };

            // Add CSRF token
            preparedOptions.headers = this.csrf.addTokenToHeaders(preparedOptions.headers);

            // Add authentication token
            preparedOptions.headers = this.auth.addTokenToHeaders(preparedOptions.headers);

            // Set default headers
            preparedOptions.headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...preparedOptions.headers
            };

            // Process request body
            if (preparedOptions.body && typeof preparedOptions.body === 'object' &&
                !preparedOptions.body instanceof FormData) {
                preparedOptions.body = JSON.stringify(preparedOptions.body);
            }

            // Execute request interceptors
            preparedOptions = await this.executeRequestInterceptors(preparedOptions);

            return preparedOptions;
        }

        /**
         * Make fetch request with timeout
         * @param {Object} options - Request options
         * @returns {Promise} - Fetch promise
         */
        makeFetchRequest(options) {
            return new Promise((resolve, reject) => {
                const timeoutId = setTimeout(() => {
                    reject(new Error('Request timeout'));
                }, options.timeout);

                fetch(options.url, {
                    method: options.method,
                    headers: options.headers,
                    body: options.body,
                    credentials: 'same-origin'
                })
                .then(response => {
                    clearTimeout(timeoutId);
                    resolve(response);
                })
                .catch(error => {
                    clearTimeout(timeoutId);
                    reject(error);
                });
            });
        }

        /**
         * Process response
         * @param {Response} response - Fetch response
         * @param {Object} options - Request options
         * @returns {*} - Processed response
         */
        async processResponse(response, options) {
            let data;

            // Handle different response types
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else if (contentType && contentType.includes('text')) {
                data = await response.text();
            } else {
                data = await response.blob();
            }

            // Handle HTTP errors
            if (!response.ok) {
                const error = new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
                error.status = response.status;
                error.response = data;
                throw error;
            }

            // Execute response interceptors
            data = await this.executeResponseInterceptors(data, options);

            return data;
        }

        /**
         * Sleep utility
         * @param {number} ms - Milliseconds to sleep
         * @returns {Promise} - Sleep promise
         */
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        /**
         * Convenience methods for HTTP methods
         */
        async get(url, options = {}) {
            return this.request(url, { ...options, method: 'GET' });
        }

        async post(url, data, options = {}) {
            return this.request(url, { ...options, method: 'POST', body: data });
        }

        async put(url, data, options = {}) {
            return this.request(url, { ...options, method: 'PUT', body: data });
        }

        async patch(url, data, options = {}) {
            return this.request(url, { ...options, method: 'PATCH', body: data });
        }

        async delete(url, options = {}) {
            return this.request(url, { ...options, method: 'DELETE' });
        }

        /**
         * Upload file
         * @param {string} url - Upload URL
         * @param {File} file - File to upload
         * @param {Object} options - Upload options
         * @returns {Promise} - Upload promise
         */
        async upload(url, file, options = {}) {
            const formData = new FormData();
            formData.append('file', file);

            if (options.additionalData) {
                Object.keys(options.additionalData).forEach(key => {
                    formData.append(key, options.additionalData[key]);
                });
            }

            return this.request(url, {
                ...options,
                method: 'POST',
                body: formData,
                headers: {
                    ...options.headers,
                    'Content-Type': undefined // Let browser set content-type
                }
            });
        }

        /**
         * Upload multiple files
         * @param {string} url - Upload URL
         * @param {FileList|File[]} files - Files to upload
         * @param {Object} options - Upload options
         * @returns {Promise} - Upload promise
         */
        async uploadMultiple(url, files, options = {}) {
            const formData = new FormData();

            Array.from(files).forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });

            if (options.additionalData) {
                Object.keys(options.additionalData).forEach(key => {
                    formData.append(key, options.additionalData[key]);
                });
            }

            return this.request(url, {
                ...options,
                method: 'POST',
                body: formData,
                headers: {
                    ...options.headers,
                    'Content-Type': undefined
                }
            });
        }

        /**
         * Download file
         * @param {string} url - Download URL
         * @param {Object} options - Download options
         * @returns {Promise} - Download promise
         */
        async download(url, options = {}) {
            const response = await this.request(url, {
                ...options,
                headers: {
                    ...options.headers,
                    'Accept': '*/*'
                }
            });

            // Create download link
            const blob = new Blob([response]);
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = options.filename || 'download';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);

            return response;
        }

        /**
         * Get queue status
         * @returns {Object} - Queue status
         */
        getQueueStatus() {
            return this.queue.getStatus();
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
         * Set authentication token
         * @param {string} token - Authentication token
         * @param {string} refreshToken - Refresh token
         */
        setAuthToken(token, refreshToken) {
            this.auth.setToken(token, refreshToken);
        }

        /**
         * Clear authentication
         */
        clearAuth() {
            this.auth.clearToken();
        }

        /**
         * Check authentication status
         * @returns {boolean} - Authentication status
         */
        isAuthenticated() {
            return this.auth.isAuthenticated();
        }

        /**
         * Add error callback
         * @param {string} type - Error type
         * @param {Function} callback - Error callback
         */
        onError(type, callback) {
            this.errorHandler.onError(type, callback);
        }

        /**
         * Update configuration
         * @param {Object} newConfig - New configuration
         */
        updateConfig(newConfig) {
            Object.assign(AJAX_CONFIG, newConfig);
        }

        /**
         * Get configuration
         * @returns {Object} - Current configuration
         */
        getConfig() {
            return { ...AJAX_CONFIG };
        }
    }

    /**
     * Create AJAX manager instance
     */
    const ajaxManager = new AJAXManager();

    /**
     * Main SAKIP AJAX Object
     */
    const SAKIP_AJAX = {
        // Manager instance
        manager: ajaxManager,

        // Configuration
        config: AJAX_CONFIG,

        // HTTP methods
        get: (url, options) => ajaxManager.get(url, options),
        post: (url, data, options) => ajaxManager.post(url, data, options),
        put: (url, data, options) => ajaxManager.put(url, data, options),
        patch: (url, data, options) => ajaxManager.patch(url, data, options),
        delete: (url, options) => ajaxManager.delete(url, options),

        // Specialized methods
        upload: (url, file, options) => ajaxManager.upload(url, file, options),
        uploadMultiple: (url, files, options) => ajaxManager.uploadMultiple(url, files, options),
        download: (url, options) => ajaxManager.download(url, options),
        request: (url, options) => ajaxManager.request(url, options),

        // Configuration methods
        setAuthToken: (token, refreshToken) => ajaxManager.setAuthToken(token, refreshToken),
        clearAuth: () => ajaxManager.clearAuth(),
        isAuthenticated: () => ajaxManager.isAuthenticated(),
        updateConfig: (newConfig) => ajaxManager.updateConfig(newConfig),
        getConfig: () => ajaxManager.getConfig(),

        // Utility methods
        getQueueStatus: () => ajaxManager.getQueueStatus(),
        clearCache: () => ajaxManager.clearCache(),
        getCacheSize: () => ajaxManager.getCacheSize(),
        onError: (type, callback) => ajaxManager.onError(type, callback),

        // Add interceptors
        addRequestInterceptor: (interceptor) => ajaxManager.addRequestInterceptor(interceptor),
        addResponseInterceptor: (interceptor) => ajaxManager.addResponseInterceptor(interceptor),

        // Error types
        ERROR_TYPES: {
            TIMEOUT: 'timeout',
            NETWORK: 'network',
            UNAUTHORIZED: 'unauthorized',
            FORBIDDEN: 'forbidden',
            NOT_FOUND: 'not_found',
            SERVER_ERROR: 'server_error',
            GENERAL: 'general'
        }
    };

    return SAK