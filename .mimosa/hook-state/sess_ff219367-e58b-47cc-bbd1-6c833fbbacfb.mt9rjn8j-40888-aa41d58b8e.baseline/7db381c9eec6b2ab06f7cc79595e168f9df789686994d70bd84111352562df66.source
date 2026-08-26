/**
 * SAKIP API Endpoint Definitions
 * Government-style API endpoint management for SAKIP module
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
        global.SAKIP_ENDPOINTS = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * API Configuration
     */
    const API_CONFIG = {
        BASE_URL: '/api/sakip',
        VERSION: 'v1',
        TIMEOUT: 30000,
        RETRY_ATTEMPTS: 3,
        RETRY_DELAY: 1000,
        HEADERS: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    /**
     * API Endpoint Categories
     */
    const ENDPOINT_CATEGORIES = {
        AUTH: 'auth',
        DASHBOARD: 'dashboard',
        ASSESSMENT: 'assessment',
        INSTITUTION: 'institution',
        REPORT: 'report',
        USER: 'user',
        SYSTEM: 'system',
        AUDIT: 'audit',
        NOTIFICATION: 'notification',
        FILE: 'file',
        EXPORT: 'export',
        CONFIG: 'config'
    };

    /**
     * Comprehensive API Endpoints
     */
    const API_ENDPOINTS = {
        /**
         * Authentication Endpoints
         */
        AUTH: {
            LOGIN: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/login`,
            LOGOUT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/logout`,
            REFRESH: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/refresh`,
            PROFILE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/profile`,
            UPDATE_PROFILE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/profile/update`,
            CHANGE_PASSWORD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/password/change`,
            FORGOT_PASSWORD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/password/forgot`,
            RESET_PASSWORD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/password/reset`,
            VERIFY_EMAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/email/verify`,
            RESEND_VERIFICATION: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/auth/email/resend`
        },

        /**
         * Dashboard Endpoints
         */
        DASHBOARD: {
            OVERVIEW: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/overview`,
            ANALYTICS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/analytics`,
            CHART_DATA: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/chart-data`,
            PERFORMANCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/performance`,
            STATISTICS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/statistics`,
            RECENT_ACTIVITIES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/activities`,
            NOTIFICATIONS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/notifications`,
            SUMMARY: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/dashboard/summary`
        },

        /**
         * Assessment Endpoints
         */
        ASSESSMENT: {
            LIST: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments`,
            DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}`,
            CREATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/create`,
            UPDATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/update`,
            DELETE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/delete`,
            SUBMIT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/submit`,
            APPROVE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/approve`,
            REJECT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/reject`,
            RETURN: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/return`,
            SCORE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/score`,
            EVIDENCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/evidence`,
            UPLOAD_EVIDENCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/evidence/upload`,
            DELETE_EVIDENCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/evidence/{evidenceId}/delete`,
            INDICATORS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/indicators`,
            UPDATE_INDICATOR: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/indicators/{indicatorId}/update`,
            HISTORY: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/history`,
            ASSIGN: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/{id}/assign`,
            BULK_ASSIGN: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/bulk-assign`,
            BULK_SUBMIT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/assessments/bulk-submit`
        },

        /**
         * Institution Endpoints
         */
        INSTITUTION: {
            LIST: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions`,
            DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}`,
            CREATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/create`,
            UPDATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/update`,
            DELETE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/delete`,
            ACTIVATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/activate`,
            DEACTIVATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/deactivate`,
            PROFILE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/profile`,
            UPDATE_PROFILE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/profile/update`,
            USERS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/users`,
            ADD_USER: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/users/add`,
            REMOVE_USER: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/users/{userId}/remove`,
            ASSESSMENTS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/assessments`,
            PERFORMANCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/performance`,
            STATISTICS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/institutions/{id}/statistics`
        },

        /**
         * Report Endpoints
         */
        REPORT: {
            LIST: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports`,
            DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}`,
            CREATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/create`,
            UPDATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}/update`,
            DELETE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}/delete`,
            GENERATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}/generate`,
            DOWNLOAD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}/download`,
            SHARE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}/share`,
            PUBLISH: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}/publish`,
            ARCHIVE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/{id}/archive`,
            TEMPLATES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/templates`,
            TEMPLATE_DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/templates/{templateId}`,
            CUSTOM: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/custom`,
            SCHEDULE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/schedule`,
            SCHEDULED_REPORTS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/reports/scheduled`
        },

        /**
         * User Management Endpoints
         */
        USER: {
            LIST: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users`,
            DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}`,
            CREATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/create`,
            UPDATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/update`,
            DELETE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/delete`,
            ACTIVATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/activate`,
            DEACTIVATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/deactivate`,
            ROLES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/roles`,
            ASSIGN_ROLE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/roles/{roleId}/assign`,
            REMOVE_ROLE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/roles/{roleId}/remove`,
            PERMISSIONS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/permissions`,
            PROFILE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/profile`,
            UPDATE_PROFILE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/profile/update`,
            AVATAR: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/avatar`,
            UPLOAD_AVATAR: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/avatar/upload`,
            DELETE_AVATAR: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/avatar/delete`,
            PREFERENCES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/preferences`,
            UPDATE_PREFERENCES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/users/{id}/preferences/update`
        },

        /**
         * System Configuration Endpoints
         */
        SYSTEM: {
            CONFIG: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/config`,
            UPDATE_CONFIG: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/config/update`,
            STATUS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/status`,
            HEALTH: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/health`,
            METRICS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/metrics`,
            LOGS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/logs`,
            CLEAR_LOGS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/logs/clear`,
            BACKUP: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/backup`,
            RESTORE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/restore`,
            MAINTENANCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/maintenance`,
            ENABLE_MAINTENANCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/maintenance/enable`,
            DISABLE_MAINTENANCE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/system/maintenance/disable`
        },

        /**
         * Audit Trail Endpoints
         */
        AUDIT: {
            TRAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/audit/trail`,
            DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/audit/trail/{id}`,
            SEARCH: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/audit/trail/search`,
            EXPORT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/audit/trail/export`,
            CLEAR_OLD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/audit/trail/clear-old`,
            SETTINGS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/audit/settings`,
            UPDATE_SETTINGS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/audit/settings/update`
        },

        /**
         * Notification Endpoints
         */
        NOTIFICATION: {
            LIST: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications`,
            DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/{id}`,
            MARK_READ: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/{id}/mark-read`,
            MARK_ALL_READ: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/mark-all-read`,
            DELETE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/{id}/delete`,
            DELETE_ALL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/delete-all`,
            SETTINGS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/settings`,
            UPDATE_SETTINGS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/settings/update`,
            PREFERENCES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/preferences`,
            UPDATE_PREFERENCES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/preferences/update`,
            SEND: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/send`,
            BULK_SEND: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/bulk-send`,
            TEMPLATES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/notifications/templates`
        },

        /**
         * File Management Endpoints
         */
        FILE: {
            UPLOAD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/upload`,
            UPLOAD_CHUNK: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/upload-chunk`,
            COMPLETE_CHUNK: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/complete-chunk`,
            DOWNLOAD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/{id}/download`,
            PREVIEW: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/{id}/preview`,
            DELETE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/{id}/delete`,
            BULK_DELETE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/bulk-delete`,
            LIST: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files`,
            DETAIL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/{id}`,
            VALIDATE: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/validate`,
            SCAN: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/scan`,
            METADATA: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/{id}/metadata`,
            UPDATE_METADATA: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/files/{id}/metadata/update`
        },

        /**
         * Export Endpoints
         */
        EXPORT: {
            ASSESSMENT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/assessment`,
            INSTITUTION: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/institution`,
            REPORT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/report`,
            AUDIT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/audit`,
            USER: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/user`,
            CUSTOM: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/custom`,
            STATUS: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/{id}/status`,
            DOWNLOAD: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/{id}/download`,
            CANCEL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/{id}/cancel`,
            TEMPLATES: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/templates`,
            CONFIG: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/export/config`
        },

        /**
         * Configuration Endpoints
         */
        CONFIG: {
            GENERAL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/general`,
            UPDATE_GENERAL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/general/update`,
            VALIDATION: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/validation`,
            UPDATE_VALIDATION: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/validation/update`,
            NOTIFICATION: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/notification`,
            UPDATE_NOTIFICATION: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/notification/update`,
            EXPORT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/export`,
            UPDATE_EXPORT: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/export/update`,
            SYSTEM: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/system`,
            UPDATE_SYSTEM: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}/config/system/update`
        }
    };

    /**
     * Endpoint Utilities
     */
    const EndpointUtils = {
        /**
         * Replace path parameters in endpoint URL
         * @param {string} endpoint - Endpoint URL with placeholders
         * @param {Object} params - Parameters to replace
         * @returns {string} - Formatted endpoint URL
         */
        formatEndpoint: function(endpoint, params = {}) {
            let formattedEndpoint = endpoint;
            Object.keys(params).forEach(key => {
                const placeholder = `{${key}}`;
                formattedEndpoint = formattedEndpoint.replace(placeholder, params[key]);
            });
            return formattedEndpoint;
        },

        /**
         * Build query string from parameters
         * @param {Object} params - Query parameters
         * @returns {string} - Query string
         */
        buildQueryString: function(params = {}) {
            const queryParams = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if (params[key] !== null && params[key] !== undefined) {
                    queryParams.append(key, params[key]);
                }
            });
            return queryParams.toString() ? `?${queryParams.toString()}` : '';
        },

        /**
         * Build complete URL with query parameters
         * @param {string} endpoint - Base endpoint
         * @param {Object} pathParams - Path parameters
         * @param {Object} queryParams - Query parameters
         * @returns {string} - Complete URL
         */
        buildUrl: function(endpoint, pathParams = {}, queryParams = {}) {
            const formattedEndpoint = this.formatEndpoint(endpoint, pathParams);
            const queryString = this.buildQueryString(queryParams);
            return `${formattedEndpoint}${queryString}`;
        },

        /**
         * Get endpoint by category and action
         * @param {string} category - Endpoint category
         * @param {string} action - Action name
         * @returns {string|null} - Endpoint URL or null
         */
        getEndpoint: function(category, action) {
            const categoryEndpoints = API_ENDPOINTS[category.toUpperCase()];
            return categoryEndpoints ? categoryEndpoints[action.toUpperCase()] : null;
        },

        /**
         * Get all endpoints for a category
         * @param {string} category - Endpoint category
         * @returns {Object|null} - Category endpoints or null
         */
        getCategoryEndpoints: function(category) {
            return API_ENDPOINTS[category.toUpperCase()] || null;
        },

        /**
         * Validate endpoint exists
         * @param {string} category - Endpoint category
         * @param {string} action - Action name
         * @returns {boolean} - True if endpoint exists
         */
        validateEndpoint: function(category, action) {
            const endpoint = this.getEndpoint(category, action);
            return endpoint !== null;
        },

        /**
         * Get API configuration
         * @returns {Object} - API configuration
         */
        getConfig: function() {
            return { ...API_CONFIG };
        },

        /**
         * Update API configuration
         * @param {Object} newConfig - New configuration values
         */
        updateConfig: function(newConfig) {
            Object.assign(API_CONFIG, newConfig);
        },

        /**
         * Get all available endpoints
         * @returns {Object} - All endpoints
         */
        getAllEndpoints: function() {
            return { ...API_ENDPOINTS };
        },

        /**
         * Get endpoint categories
         * @returns {Object} - Endpoint categories
         */
        getCategories: function() {
            return { ...ENDPOINT_CATEGORIES };
        }
    };

    /**
     * Endpoint Builder Class
     */
    class EndpointBuilder {
        constructor(baseEndpoint) {
            this.baseEndpoint = baseEndpoint;
            this.pathParams = {};
            this.queryParams = {};
        }

        /**
         * Set path parameter
         * @param {string} key - Parameter key
         * @param {*} value - Parameter value
         * @returns {EndpointBuilder} - Builder instance
         */
        pathParam(key, value) {
            this.pathParams[key] = value;
            return this;
        }

        /**
         * Set multiple path parameters
         * @param {Object} params - Path parameters
         * @returns {EndpointBuilder} - Builder instance
         */
        pathParams(params) {
            Object.assign(this.pathParams, params);
            return this;
        }

        /**
         * Set query parameter
         * @param {string} key - Parameter key
         * @param {*} value - Parameter value
         * @returns {EndpointBuilder} - Builder instance
         */
        queryParam(key, value) {
            this.queryParams[key] = value;
            return this;
        }

        /**
         * Set multiple query parameters
         * @param {Object} params - Query parameters
         * @returns {EndpointBuilder} - Builder instance
         */
        queryParams(params) {
            Object.assign(this.queryParams, params);
            return this;
        }

        /**
         * Build final URL
         * @returns {string} - Complete URL
         */
        build() {
            return EndpointUtils.buildUrl(this.baseEndpoint, this.pathParams, this.queryParams);
        }
    };

    /**
     * Main API Endpoints Object
     */
    const SAKIP_ENDPOINTS = {
        // Configuration
        CONFIG: API_CONFIG,
        CATEGORIES: ENDPOINT_CATEGORIES,
        ENDPOINTS: API_ENDPOINTS,

        // Utilities
        utils: EndpointUtils,

        // Builder factory
        builder: function(endpoint) {
            return new EndpointBuilder(endpoint);
        },

        // Convenience methods for common operations
        auth: {
            login: () => API_ENDPOINTS.AUTH.LOGIN,
            logout: () => API_ENDPOINTS.AUTH.LOGOUT,
            profile: () => API_ENDPOINTS.AUTH.PROFILE,
            updateProfile: () => API_ENDPOINTS.AUTH.UPDATE_PROFILE
        },

        dashboard: {
            overview: () => API_ENDPOINTS.DASHBOARD.OVERVIEW,
            analytics: () => API_ENDPOINTS.DASHBOARD.ANALYTICS,
            chartData: () => API_ENDPOINTS.DASHBOARD.CHART_DATA,
            performance: () => API_ENDPOINTS.DASHBOARD.PERFORMANCE
        },

        assessment: {
            list: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.ASSESSMENT.LIST, {}, params),
            detail: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.DETAIL, { id }),
            create: () => API_ENDPOINTS.ASSESSMENT.CREATE,
            update: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.UPDATE, { id }),
            delete: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.DELETE, { id }),
            submit: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.SUBMIT, { id }),
            approve: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.APPROVE, { id }),
            reject: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.REJECT, { id }),
            score: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.SCORE, { id }),
            evidence: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.ASSESSMENT.EVIDENCE, { id })
        },

        institution: {
            list: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.INSTITUTION.LIST, {}, params),
            detail: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.INSTITUTION.DETAIL, { id }),
            create: () => API_ENDPOINTS.INSTITUTION.CREATE,
            update: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.INSTITUTION.UPDATE, { id }),
            delete: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.INSTITUTION.DELETE, { id }),
            users: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.INSTITUTION.USERS, { id }),
            assessments: (id, params = {}) => EndpointUtils.buildUrl(
                EndpointUtils.formatEndpoint(API_ENDPOINTS.INSTITUTION.ASSESSMENTS, { id }),
                {},
                params
            )
        },

        report: {
            list: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.REPORT.LIST, {}, params),
            detail: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.REPORT.DETAIL, { id }),
            create: () => API_ENDPOINTS.REPORT.CREATE,
            generate: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.REPORT.GENERATE, { id }),
            download: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.REPORT.DOWNLOAD, { id }),
            templates: () => API_ENDPOINTS.REPORT.TEMPLATES
        },

        user: {
            list: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.USER.LIST, {}, params),
            detail: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.USER.DETAIL, { id }),
            create: () => API_ENDPOINTS.USER.CREATE,
            update: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.USER.UPDATE, { id }),
            delete: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.USER.DELETE, { id }),
            profile: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.USER.PROFILE, { id }),
            preferences: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.USER.PREFERENCES, { id })
        },

        system: {
            config: () => API_ENDPOINTS.SYSTEM.CONFIG,
            status: () => API_ENDPOINTS.SYSTEM.STATUS,
            health: () => API_ENDPOINTS.SYSTEM.HEALTH,
            metrics: () => API_ENDPOINTS.SYSTEM.METRICS,
            logs: () => API_ENDPOINTS.SYSTEM.LOGS
        },

        audit: {
            trail: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.AUDIT.TRAIL, {}, params),
            detail: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.AUDIT.DETAIL, { id }),
            search: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.AUDIT.SEARCH, {}, params),
            export: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.AUDIT.EXPORT, {}, params)
        },

        notification: {
            list: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.NOTIFICATION.LIST, {}, params),
            detail: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.NOTIFICATION.DETAIL, { id }),
            markRead: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.NOTIFICATION.MARK_READ, { id }),
            markAllRead: () => API_ENDPOINTS.NOTIFICATION.MARK_ALL_READ,
            settings: () => API_ENDPOINTS.NOTIFICATION.SETTINGS,
            templates: () => API_ENDPOINTS.NOTIFICATION.TEMPLATES
        },

        file: {
            upload: () => API_ENDPOINTS.FILE.UPLOAD,
            download: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.FILE.DOWNLOAD, { id }),
            preview: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.FILE.PREVIEW, { id }),
            delete: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.FILE.DELETE, { id }),
            list: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.FILE.LIST, {}, params),
            detail: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.FILE.DETAIL, { id }),
            validate: () => API_ENDPOINTS.FILE.VALIDATE
        },

        export: {
            assessment: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.EXPORT.ASSESSMENT, {}, params),
            institution: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.EXPORT.INSTITUTION, {}, params),
            report: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.EXPORT.REPORT, {}, params),
            audit: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.EXPORT.AUDIT, {}, params),
            user: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.EXPORT.USER, {}, params),
            custom: (params = {}) => EndpointUtils.buildUrl(API_ENDPOINTS.EXPORT.CUSTOM, {}, params),
            status: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.EXPORT.STATUS, { id }),
            download: (id) => EndpointUtils.formatEndpoint(API_ENDPOINTS.EXPORT.DOWNLOAD, { id }),
            templates: () => API_ENDPOINTS.EXPORT.TEMPLATES,
            config: () => API_ENDPOINTS.EXPORT.CONFIG
        }
};
}));
