/**
 * SAKIP Helpers Module
 * Provides utility functions for SAKIP integration
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.SAKIP_HELPERS = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {

    /**
     * CSRF token for AJAX requests
     */
    let csrfToken = null;

    /**
     * API base URL
     */
    const API_BASE_URL = '/sakip/api';

    /**
     * Helper functions
     */
    const Helpers = {

        /**
         * Set CSRF token
         */
        setCsrfToken: function(token) {
            csrfToken = token;
        },

        /**
         * Get CSRF token
         */
        getCsrfToken: function() {
            if (!csrfToken) {
                const tokenElement = document.querySelector('meta[name="csrf-token"]');
                csrfToken = tokenElement ? tokenElement.getAttribute('content') : null;
            }
            return csrfToken;
        },

        /**
         * Make API request
         */
        apiRequest: async function(url, options = {}) {
            const token = this.getCsrfToken();
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...options.headers
            };

            if (token) {
                headers['X-CSRF-TOKEN'] = token;
            }

            const response = await fetch(url, {
                ...options,
                headers
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response;
        },

        /**
         * GET request
         */
        get: function(url, params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const fullUrl = queryString ? `${url}?${queryString}` : url;
            return this.apiRequest(fullUrl);
        },

        /**
         * POST request
         */
        post: function(url, data = {}) {
            return this.apiRequest(url, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },

        /**
         * PUT request
         */
        put: function(url, data = {}) {
            return this.apiRequest(url, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },

        /**
         * DELETE request
         */
        delete: function(url) {
            return this.apiRequest(url, {
                method: 'DELETE'
            });
        },

        /**
         * Format date
         */
        formatDate: function(date, format = 'DD/MM/YYYY') {
            const d = new Date(date);
            
            const day = d.getDate().toString().padStart(2, '0');
            const month = (d.getMonth() + 1).toString().padStart(2, '0');
            const year = d.getFullYear();

            return format
                .replace('DD', day)
                .replace('MM', month)
                .replace('YYYY', year);
        },

        /**
         * Format datetime
         */
        formatDateTime: function(date, format = 'DD/MM/YYYY HH:mm') {
            const d = new Date(date);
            
            const day = d.getDate().toString().padStart(2, '0');
            const month = (d.getMonth() + 1).toString().padStart(2, '0');
            const year = d.getFullYear();
            const hours = d.getHours().toString().padStart(2, '0');
            const minutes = d.getMinutes().toString().padStart(2, '0');

            return format
                .replace('DD', day)
                .replace('MM', month)
                .replace('YYYY', year)
                .replace('HH', hours)
                .replace('mm', minutes);
        },

        /**
         * Format currency
         */
        formatCurrency: function(amount, currency = 'IDR') {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        /**
         * Format number
         */
        formatNumber: function(number, decimals = 0) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        },

        /**
         * Format percentage
         */
        formatPercentage: function(value, decimals = 1) {
            return `${value.toFixed(decimals)}%`;
        },

        /**
         * Deep clone object
         */
        deepClone: function(obj) {
            if (obj === null || typeof obj !== 'object') return obj;
            if (obj instanceof Date) return new Date(obj.getTime());
            if (obj instanceof Array) return obj.map(item => this.deepClone(item));
            if (typeof obj === 'object') {
                const cloned = {};
                Object.keys(obj).forEach(key => {
                    cloned[key] = this.deepClone(obj[key]);
                });
                return cloned;
            }
        },

        /**
         * Generate unique ID
         */
        generateId: function(prefix = 'sakip') {
            const timestamp = Date.now().toString(36);
            const random = Math.random().toString(36).substr(2, 9);
            return `${prefix}_${timestamp}_${random}`;
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait, immediate = false) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    timeout = null;
                    if (!immediate) func(...args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func(...args);
            };
        },

        /**
         * Throttle function
         */
        throttle: function(func, limit) {
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
        },

        /**
         * Show loading state
         */
        showLoading: function(element) {
            const el = typeof element === 'string' ? document.querySelector(element) : element;
            if (el) {
                el.classList.add('sakip-loading');
            }
        },

        /**
         * Hide loading state
         */
        hideLoading: function(element) {
            const el = typeof element === 'string' ? document.querySelector(element) : element;
            if (el) {
                el.classList.remove('sakip-loading');
            }
        },

        /**
         * Show error message
         */
        showError: function(message, title = 'Error') {
            if (window.SAKIP_NOTIFICATION) {
                window.SAKIP_NOTIFICATION.show({
                    type: 'error',
                    title: title,
                    message: message
                });
            } else {
                alert(`${title}: ${message}`);
            }
        },

        /**
         * Show success message
         */
        showSuccess: function(message, title = 'Success') {
            if (window.SAKIP_NOTIFICATION) {
                window.SAKIP_NOTIFICATION.show({
                    type: 'success',
                    title: title,
                    message: message
                });
            } else {
                alert(`${title}: ${message}`);
            }
        },

        /**
         * Validate form data
         */
        validateForm: function(formData, rules) {
            const errors = {};

            Object.keys(rules).forEach(field => {
                const fieldRules = rules[field].split('|');
                const value = formData[field];

                fieldRules.forEach(rule => {
                    const [ruleName, ruleValue] = rule.split(':');

                    switch (ruleName) {
                        case 'required':
                            if (!value || value.trim() === '') {
                                errors[field] = errors[field] || [];
                                errors[field].push(`${field} is required`);
                            }
                            break;
                        case 'min':
                            if (value && value.length < parseInt(ruleValue)) {
                                errors[field] = errors[field] || [];
                                errors[field].push(`${field} must be at least ${ruleValue} characters`);
                            }
                            break;
                        case 'max':
                            if (value && value.length > parseInt(ruleValue)) {
                                errors[field] = errors[field] || [];
                                errors[field].push(`${field} must not exceed ${ruleValue} characters`);
                            }
                            break;
                        case 'email':
                            if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                                errors[field] = errors[field] || [];
                                errors[field].push(`${field} must be a valid email`);
                            }
                            break;
                    }
                });
            });

            return Object.keys(errors).length > 0 ? errors : null;
        },

        /**
         * Get file extension
         */
        getFileExtension: function(filename) {
            return filename.split('.').pop().toLowerCase();
        },

        /**
         * Format file size
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Sanitize HTML
         */
        sanitizeHtml: function(html) {
            const div = document.createElement('div');
            div.textContent = html;
            return div.innerHTML;
        },

        /**
         * Escape special characters
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    };

    /**
     * Public API
     */
    return Helpers;

}));