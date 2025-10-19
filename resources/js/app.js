import './bootstrap';
import '../css/app.css';

// Initialize application
async function initializeApp() {
    try {
        // Import SAKIP modules - using dynamic imports for UMD modules
        const SAKIP_DATA_TABLES = await import('./sakip/data-tables.js').then(m => m.default || m.SAKIP_DATA_TABLES || m);
        const SAKIP_HELPERS = await import('./sakip/helpers.js').then(m => m.default || m.SAKIP_HELPERS || m);
        const SAKIP_NOTIFICATION = await import('./sakip/notification.js').then(m => m.default || m.SAKIP_NOTIFICATION || m);
        const SAKIP_DATA_TABLE_INIT = await import('./sakip/data-table-init.js').then(m => m.default || m.SAKIP_DATA_TABLE_INIT || m);
        const SAKIP_DASHBOARD = await import('./sakip/dashboard.js').then(m => m.default || m.SAKIP_DASHBOARD || m);

        // Make SAKIP modules globally available
        window.SAKIP_DATA_TABLES = SAKIP_DATA_TABLES;
        window.SAKIP_HELPERS = SAKIP_HELPERS;
        window.SAKIP_NOTIFICATION = SAKIP_NOTIFICATION;
        window.SAKIP_DATA_TABLE_INIT = SAKIP_DATA_TABLE_INIT;
        window.SAKIP_DASHBOARD = SAKIP_DASHBOARD;

        // Initialize application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize SAKIP configuration from global config
            if (window.SAKIP_CONFIG) {
                // Set API base URL
                if (window.SAKIP_CONFIG.apiUrl) {
                    window.SAKIP_API_BASE = window.SAKIP_CONFIG.apiUrl;
                }
                
                // Set CSRF token from config
                if (window.SAKIP_CONFIG.csrfToken) {
                    window.csrfToken = window.SAKIP_CONFIG.csrfToken;
                    if (window.axios) {
                        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.SAKIP_CONFIG.csrfToken;
                    }
                    
                    // Set token in helpers
                    if (window.SAKIP_HELPERS) {
                        window.SAKIP_HELPERS.setCsrfToken(window.SAKIP_CONFIG.csrfToken);
                    }
                }
            }
            
            // Fallback: Initialize CSRF token for AJAX requests from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken && !window.csrfToken) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
                window.csrfToken = csrfToken;
                
                // Set token in helpers
                if (window.SAKIP_HELPERS) {
                    window.SAKIP_HELPERS.setCsrfToken(csrfToken);
                }
            }

            // Initialize theme support
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Initialize tooltips and popovers
            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Initialize popovers
                const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                });
            }

            try {
                // Initialize SAKIP components
                initializeSakipComponents();
                
                // Initialize any other SAKIP components
                console.log('SAKIP application initialized');
            } catch (error) {
                console.error('Error initializing SAKIP application:', error);
                if (window.SAKIP_NOTIFICATION) {
                    window.SAKIP_NOTIFICATION.error('Initialization Error', 'Failed to initialize SAKIP components');
                }
            }
        });

        /**
         * Load SAKIP configuration
         */
        function loadSakipConfig() {
            if (window.SAKIP_CONFIG) {
                // Configure API endpoints
                if (window.SAKIP_CONFIG.apiUrl && window.SAKIP_HELPERS) {
                    window.SAKIP_HELPERS.setApiUrl(window.SAKIP_CONFIG.apiUrl);
                }
                
                // Configure user information
                if (window.SAKIP_CONFIG.user && window.SAKIP_HELPERS) {
                    window.SAKIP_HELPERS.setCurrentUser(window.SAKIP_CONFIG.user);
                }
                
                // Configure permissions
                if (window.SAKIP_CONFIG.permissions && window.SAKIP_HELPERS) {
                    window.SAKIP_HELPERS.setPermissions(window.SAKIP_CONFIG.permissions);
                }
                
                console.log('SAKIP configuration loaded');
                return true;
            }
            return false;
        }

        /**
         * Initialize SAKIP components
         */
        function initializeSakipComponents() {
            // Load SAKIP configuration first
            loadSakipConfig();
            
            // Initialize data tables from data attributes
            if (window.SAKIP_DATA_TABLE_INIT) {
                window.SAKIP_DATA_TABLE_INIT.initFromDataAttributes();
                console.log('SAKIP Data Table Initialization completed');
            }

            // Initialize dashboard if available
            if (window.SAKIP_DASHBOARD) {
                const dashboardElements = document.querySelectorAll('[data-sakip-dashboard]');
                dashboardElements.forEach(element => {
                    const dashboardId = element.id || 'dashboard-' + Date.now();
                    if (!element.id) {
                        element.id = dashboardId;
                    }
                    
                    // Initialize dashboard manager
                    const dashboard = new window.SAKIP_DASHBOARD.DashboardManager(dashboardId);
                    dashboard.init();
                });
                console.log('SAKIP Dashboard components initialized');
            }

            // Initialize notifications
            if (window.SAKIP_NOTIFICATION) {
                console.log('SAKIP Notification system initialized');
            }

            // Initialize helpers
            if (window.SAKIP_HELPERS) {
                console.log('SAKIP Helpers initialized');
            }

            // Initialize data tables
            if (window.SAKIP_DATA_TABLES) {
                console.log('SAKIP Data Tables initialized');
            }

            // Setup global AJAX error handling
            setupGlobalAjaxHandling();
        }

        /**
         * Setup global AJAX error handling
         */
        function setupGlobalAjaxHandling() {
            // Handle AJAX errors globally
            document.addEventListener('ajax:error', function(event) {
                const detail = event.detail;
                const errorMessage = detail.message || 'An error occurred';
                
                if (window.SAKIP_NOTIFICATION) {
                    window.SAKIP_NOTIFICATION.error('Error', errorMessage);
                } else {
                    alert('Error: ' + errorMessage);
                }
            });

            // Handle AJAX success globally
            document.addEventListener('ajax:success', function(event) {
                const detail = event.detail;
                const successMessage = detail.message || 'Operation completed successfully';
                
                if (window.SAKIP_NOTIFICATION && detail.showNotification !== false) {
                    window.SAKIP_NOTIFICATION.success('Success', successMessage);
                }
            });
        }

    } catch (error) {
        console.error('Error loading SAKIP modules:', error);
    }
}

// Start the application
initializeApp();