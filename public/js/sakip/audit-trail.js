/**
 * SAKIP Audit Trail
 * Government-style audit trail timeline and filtering system for SAKIP module
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
        global.SAKIP_AUDIT_TRAIL = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Audit Trail Configuration Constants
     */
    const AUDIT_CONSTANTS = {
        // Action types
        ACTION_TYPES: {
            CREATE: 'create',
            UPDATE: 'update',
            DELETE: 'delete',
            VIEW: 'view',
            EXPORT: 'export',
            IMPORT: 'import',
            LOGIN: 'login',
            LOGOUT: 'logout',
            APPROVE: 'approve',
            REJECT: 'reject',
            SUBMIT: 'submit',
            ASSIGN: 'assign',
            UNASSIGN: 'unassign',
            CONFIGURE: 'configure',
            BACKUP: 'backup',
            RESTORE: 'restore',
            SCHEDULE: 'schedule',
            CANCEL: 'cancel'
        },

        // Resource types
        RESOURCE_TYPES: {
            USER: 'user',
            INSTITUTION: 'institution',
            ASSESSMENT: 'assessment',
            INDICATOR: 'indicator',
            REPORT: 'report',
            TEMPLATE: 'template',
            FILE: 'file',
            SYSTEM: 'system',
            ROLE: 'role',
            PERMISSION: 'permission',
            SETTING: 'setting',
            AUDIT_TRAIL: 'audit_trail',
            NOTIFICATION: 'notification',
            DASHBOARD: 'dashboard'
        },

        // Severity levels
        SEVERITY_LEVELS: {
            LOW: 'low',
            MEDIUM: 'medium',
            HIGH: 'high',
            CRITICAL: 'critical'
        },

        // Status types
        STATUS_TYPES: {
            SUCCESS: 'success',
            FAILED: 'failed',
            WARNING: 'warning',
            INFO: 'info'
        },

        // Timeline types
        TIMELINE_TYPES: {
            VERTICAL: 'vertical',
            HORIZONTAL: 'horizontal',
            COMPACT: 'compact',
            DETAILED: 'detailed'
        },

        // Filter types
        FILTER_TYPES: {
            DATE_RANGE: 'date_range',
            USER: 'user',
            ACTION: 'action',
            RESOURCE: 'resource',
            SEVERITY: 'severity',
            STATUS: 'status',
            KEYWORD: 'keyword'
        },

        // Time ranges
        TIME_RANGES: {
            TODAY: 'today',
            YESTERDAY: 'yesterday',
            THIS_WEEK: 'this_week',
            LAST_WEEK: 'last_week',
            THIS_MONTH: 'this_month',
            LAST_MONTH: 'last_month',
            THIS_YEAR: 'this_year',
            LAST_YEAR: 'last_year',
            CUSTOM: 'custom'
        },

        // Export formats
        EXPORT_FORMATS: {
            CSV: 'csv',
            JSON: 'json',
            PDF: 'pdf',
            EXCEL: 'excel'
        },

        // Maximum records
        MAX_RECORDS: {
            TIMELINE_VIEW: 1000,
            EXPORT: 10000,
            SEARCH: 500
        },

        // Error messages (Indonesian)
        ERROR_MESSAGES: {
            INVALID_FILTER: 'Filter tidak valid',
            INVALID_DATE_RANGE: 'Rentang tanggal tidak valid',
            NO_DATA_FOUND: 'Tidak ada data audit ditemukan',
            EXPORT_FAILED: 'Gagal mengekspor data audit',
            SEARCH_FAILED: 'Pencarian gagal',
            PERMISSION_DENIED: 'Akses ditolak',
            INVALID_PARAMETERS: 'Parameter tidak valid',
            SYSTEM_ERROR: 'Kesalahan sistem'
        },

        // Success messages
        SUCCESS_MESSAGES: {
            DATA_LOADED: 'Data audit berhasil dimuat',
            EXPORT_COMPLETED: 'Ekspor data audit selesai',
            FILTER_APPLIED: 'Filter berhasil diterapkan',
            SEARCH_COMPLETED: 'Pencarian selesai'
        },

        // Default settings
        DEFAULT_SETTINGS: {
            timelineType: 'vertical',
            itemsPerPage: 50,
            autoRefresh: false,
            refreshInterval: 30000, // 30 seconds
            enableRealTime: true,
            showDetails: true,
            groupByDate: true,
            enableExport: true,
            enableSearch: true,
            enableFilters: true,
            dateFormat: 'DD/MM/YYYY HH:mm',
            timezone: 'Asia/Jakarta'
        }
    };

    /**
     * Audit Trail Data Manager
     */
    class AuditTrailDataManager {
        constructor() {
            this.auditEntries = [];
            this.filteredEntries = [];
            this.currentFilters = {};
            this.searchQuery = '';
            this.sortBy = 'timestamp';
            this.sortOrder = 'desc';
            this.currentPage = 1;
            this.itemsPerPage = AUDIT_CONSTANTS.DEFAULT_SETTINGS.itemsPerPage;
            this.totalPages = 1;
            this.totalRecords = 0;
            this.isLoading = false;
            this.autoRefreshInterval = null;
            this.realTimeConnection = null;
            
            this.initializeMockData();
        }

        /**
         * Initialize mock data for demonstration
         */
        initializeMockData() {
            const mockEntries = [];
            const users = ['admin', 'user1', 'user2', 'manager', 'auditor'];
            const actions = Object.values(AUDIT_CONSTANTS.ACTION_TYPES);
            const resources = Object.values(AUDIT_CONSTANTS.RESOURCE_TYPES);
            const severities = Object.values(AUDIT_CONSTANTS.SEVERITY_LEVELS);
            const statuses = Object.values(AUDIT_CONSTANTS.STATUS_TYPES);

            // Generate 100 mock entries
            for (let i = 0; i < 100; i++) {
                const timestamp = new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000);
                const action = actions[Math.floor(Math.random() * actions.length)];
                const resource = resources[Math.floor(Math.random() * resources.length)];
                
                mockEntries.push({
                    id: i + 1,
                    userId: users[Math.floor(Math.random() * users.length)],
                    userName: `User ${i + 1}`,
                    action: action,
                    resource: resource,
                    resourceId: Math.floor(Math.random() * 1000) + 1,
                    resourceName: `${resource} ${Math.floor(Math.random() * 100) + 1}`,
                    timestamp: timestamp,
                    severity: severities[Math.floor(Math.random() * severities.length)],
                    status: statuses[Math.floor(Math.random() * statuses.length)],
                    ipAddress: `192.168.1.${Math.floor(Math.random() * 255) + 1}`,
                    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    details: {
                        oldValue: action === 'update' ? { name: 'Old Name' } : null,
                        newValue: action === 'update' ? { name: 'New Name' } : null,
                        reason: action === 'delete' ? 'Data expired' : null,
                        duration: Math.floor(Math.random() * 5000) + 100, // 100-5100ms
                        affectedRows: action === 'update' ? Math.floor(Math.random() * 10) + 1 : 1
                    },
                    metadata: {
                        sessionId: `session_${i + 1}`,
                        requestId: `req_${i + 1}`,
                        correlationId: `corr_${i + 1}`
                    }
                });
            }

            this.auditEntries = mockEntries.sort((a, b) => b.timestamp - a.timestamp);
            this.applyFilters();
        }

        /**
         * Get audit entries
         */
        getAuditEntries(filters = {}, options = {}) {
            return new Promise((resolve, reject) => {
                try {
                    // Apply filters
                    this.applyFilters(filters, options);
                    
                    // Paginate results
                    const paginated = this.paginateResults();
                    
                    resolve({
                        data: paginated,
                        pagination: {
                            currentPage: this.currentPage,
                            totalPages: this.totalPages,
                            totalRecords: this.totalRecords,
                            itemsPerPage: this.itemsPerPage,
                            hasNext: this.currentPage < this.totalPages,
                            hasPrevious: this.currentPage > 1
                        },
                        filters: this.currentFilters,
                        searchQuery: this.searchQuery,
                        sortBy: this.sortBy,
                        sortOrder: this.sortOrder
                    });
                } catch (error) {
                    reject(new Error(AUDIT_CONSTANTS.ERROR_MESSAGES.SYSTEM_ERROR));
                }
            });
        }

        /**
         * Apply filters to audit entries
         */
        applyFilters(filters = {}, options = {}) {
            // Update current filters
            this.currentFilters = { ...this.currentFilters, ...filters };
            this.searchQuery = options.searchQuery || this.searchQuery;
            this.sortBy = options.sortBy || this.sortBy;
            this.sortOrder = options.sortOrder || this.sortOrder;
            this.itemsPerPage = options.itemsPerPage || this.itemsPerPage;

            let filtered = [...this.auditEntries];

            // Apply date range filter
            if (this.currentFilters.dateRange) {
                filtered = this.filterByDateRange(filtered, this.currentFilters.dateRange);
            }

            // Apply user filter
            if (this.currentFilters.userId) {
                filtered = filtered.filter(entry => entry.userId === this.currentFilters.userId);
            }

            // Apply action filter
            if (this.currentFilters.action) {
                filtered = filtered.filter(entry => entry.action === this.currentFilters.action);
            }

            // Apply resource filter
            if (this.currentFilters.resource) {
                filtered = filtered.filter(entry => entry.resource === this.currentFilters.resource);
            }

            // Apply severity filter
            if (this.currentFilters.severity) {
                filtered = filtered.filter(entry => entry.severity === this.currentFilters.severity);
            }

            // Apply status filter
            if (this.currentFilters.status) {
                filtered = filtered.filter(entry => entry.status === this.currentFilters.status);
            }

            // Apply keyword search
            if (this.searchQuery) {
                filtered = this.filterByKeyword(filtered, this.searchQuery);
            }

            // Apply sorting
            filtered = this.sortEntries(filtered);

            this.filteredEntries = filtered;
            this.totalRecords = filtered.length;
            this.totalPages = Math.ceil(this.totalRecords / this.itemsPerPage);
        }

        /**
         * Filter by date range
         */
        filterByDateRange(entries, dateRange) {
            const now = new Date();
            let startDate, endDate;

            switch (dateRange.type) {
                case AUDIT_CONSTANTS.TIME_RANGES.TODAY:
                    startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.YESTERDAY:
                    startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
                    endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.THIS_WEEK:
                    const startOfWeek = new Date(now);
                    startOfWeek.setDate(now.getDate() - now.getDay());
                    startDate = new Date(startOfWeek.getFullYear(), startOfWeek.getMonth(), startOfWeek.getDate());
                    endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.LAST_WEEK:
                    const lastWeekStart = new Date(now);
                    lastWeekStart.setDate(now.getDate() - now.getDay() - 7);
                    const lastWeekEnd = new Date(lastWeekStart);
                    lastWeekEnd.setDate(lastWeekStart.getDate() + 7);
                    startDate = lastWeekStart;
                    endDate = lastWeekEnd;
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.THIS_MONTH:
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 1);
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.LAST_MONTH:
                    startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    endDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.THIS_YEAR:
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear() + 1, 0, 1);
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.LAST_YEAR:
                    startDate = new Date(now.getFullYear() - 1, 0, 1);
                    endDate = new Date(now.getFullYear(), 0, 1);
                    break;

                case AUDIT_CONSTANTS.TIME_RANGES.CUSTOM:
                    if (dateRange.startDate && dateRange.endDate) {
                        startDate = new Date(dateRange.startDate);
                        endDate = new Date(dateRange.endDate);
                        endDate.setDate(endDate.getDate() + 1); // Include end date
                    }
                    break;

                default:
                    return entries;
            }

            return entries.filter(entry => 
                entry.timestamp >= startDate && entry.timestamp < endDate
            );
        }

        /**
         * Filter by keyword
         */
        filterByKeyword(entries, keyword) {
            const searchTerm = keyword.toLowerCase();
            
            return entries.filter(entry => {
                return (
                    entry.userId.toLowerCase().includes(searchTerm) ||
                    entry.userName.toLowerCase().includes(searchTerm) ||
                    entry.action.toLowerCase().includes(searchTerm) ||
                    entry.resource.toLowerCase().includes(searchTerm) ||
                    entry.resourceName.toLowerCase().includes(searchTerm) ||
                    entry.ipAddress.toLowerCase().includes(searchTerm) ||
                    (entry.details && JSON.stringify(entry.details).toLowerCase().includes(searchTerm))
                );
            });
        }

        /**
         * Sort entries
         */
        sortEntries(entries) {
            return entries.sort((a, b) => {
                let aValue, bValue;

                switch (this.sortBy) {
                    case 'timestamp':
                        aValue = a.timestamp;
                        bValue = b.timestamp;
                        break;
                    case 'user':
                        aValue = a.userId;
                        bValue = b.userId;
                        break;
                    case 'action':
                        aValue = a.action;
                        bValue = b.action;
                        break;
                    case 'resource':
                        aValue = a.resource;
                        bValue = b.resource;
                        break;
                    case 'severity':
                        const severityOrder = { 'low': 1, 'medium': 2, 'high': 3, 'critical': 4 };
                        aValue = severityOrder[a.severity] || 0;
                        bValue = severityOrder[b.severity] || 0;
                        break;
                    default:
                        aValue = a.timestamp;
                        bValue = b.timestamp;
                }

                if (this.sortOrder === 'asc') {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
        }

        /**
         * Paginate results
         */
        paginateResults() {
            const startIndex = (this.currentPage - 1) * this.itemsPerPage;
            const endIndex = startIndex + this.itemsPerPage;
            
            return this.filteredEntries.slice(startIndex, endIndex);
        }

        /**
         * Add audit entry
         */
        addAuditEntry(entry) {
            return new Promise((resolve, reject) => {
                try {
                    const newEntry = {
                        id: this.auditEntries.length + 1,
                        timestamp: new Date(),
                        ...entry
                    };

                    this.auditEntries.unshift(newEntry); // Add to beginning
                    this.applyFilters(); // Reapply filters
                    
                    resolve(newEntry);
                } catch (error) {
                    reject(new Error(AUDIT_CONSTANTS.ERROR_MESSAGES.SYSTEM_ERROR));
                }
            });
        }

        /**
         * Export audit data
         */
        exportAuditData(format = AUDIT_CONSTANTS.EXPORT_FORMATS.CSV, filters = {}) {
            return new Promise((resolve, reject) => {
                try {
                    // Apply filters for export
                    this.applyFilters(filters);
                    
                    const entriesToExport = this.filteredEntries.slice(0, AUDIT_CONSTANTS.MAX_RECORDS.EXPORT);
                    
                    let exportData;
                    
                    switch (format) {
                        case AUDIT_CONSTANTS.EXPORT_FORMATS.CSV:
                            exportData = this.convertToCSV(entriesToExport);
                            break;
                        case AUDIT_CONSTANTS.EXPORT_FORMATS.JSON:
                            exportData = JSON.stringify(entriesToExport, null, 2);
                            break;
                        default:
                            throw new Error('Format tidak didukung');
                    }
                    
                    resolve({
                        data: exportData,
                        format: format,
                        recordCount: entriesToExport.length,
                        exportDate: new Date()
                    });
                } catch (error) {
                    reject(new Error(AUDIT_CONSTANTS.ERROR_MESSAGES.EXPORT_FAILED));
                }
            });
        }

        /**
         * Convert to CSV format
         */
        convertToCSV(entries) {
            const headers = ['ID', 'Timestamp', 'User', 'Action', 'Resource', 'Resource Name', 'Severity', 'Status', 'IP Address', 'Details'];
            const rows = entries.map(entry => [
                entry.id,
                entry.timestamp.toISOString(),
                entry.userId,
                entry.action,
                entry.resource,
                entry.resourceName,
                entry.severity,
                entry.status,
                entry.ipAddress,
                JSON.stringify(entry.details)
            ]);

            return [headers, ...rows].map(row => 
                row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')
            ).join('\n');
        }

        /**
         * Enable auto refresh
         */
        enableAutoRefresh(interval = AUDIT_CONSTANTS.DEFAULT_SETTINGS.refreshInterval) {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
            }

            this.autoRefreshInterval = setInterval(() => {
                this.refreshData();
            }, interval);
        }

        /**
         * Disable auto refresh
         */
        disableAutoRefresh() {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
                this.autoRefreshInterval = null;
            }
        }

        /**
         * Refresh data
         */
        refreshData() {
            // In real implementation, this would fetch new data from server
            // For now, just reapply current filters
            this.applyFilters();
            
            // Emit refresh event
            if (typeof window !== 'undefined' && window.dispatchEvent) {
                window.dispatchEvent(new CustomEvent('auditTrailRefresh', {
                    detail: {
                        totalRecords: this.totalRecords,
                        lastUpdated: new Date()
                    }
                }));
            }
        }

        /**
         * Get statistics
         */
        getStatistics(filters = {}) {
            const originalFiltered = this.filteredEntries;
            this.applyFilters(filters);
            
            const stats = {
                total: this.filteredEntries.length,
                byAction: {},
                byResource: {},
                bySeverity: {},
                byStatus: {},
                byUser: {},
                timeDistribution: {},
                recentActivity: []
            };

            this.filteredEntries.forEach(entry => {
                // Count by action
                stats.byAction[entry.action] = (stats.byAction[entry.action] || 0) + 1;
                
                // Count by resource
                stats.byResource[entry.resource] = (stats.byResource[entry.resource] || 0) + 1;
                
                // Count by severity
                stats.bySeverity[entry.severity] = (stats.bySeverity[entry.severity] || 0) + 1;
                
                // Count by status
                stats.byStatus[entry.status] = (stats.byStatus[entry.status] || 0) + 1;
                
                // Count by user
                stats.byUser[entry.userId] = (stats.byUser[entry.userId] || 0) + 1;
                
                // Time distribution (by hour)
                const hour = entry.timestamp.getHours();
                stats.timeDistribution[hour] = (stats.timeDistribution[hour] || 0) + 1;
            });

            // Get recent activity (last 24 hours)
            const last24Hours = new Date(Date.now() - 24 * 60 * 60 * 1000);
            stats.recentActivity = this.filteredEntries
                .filter(entry => entry.timestamp >= last24Hours)
                .slice(0, 10);

            // Restore original filtered entries
            this.filteredEntries = originalFiltered;
            
            return stats;
        }
    }

    /**
     * Timeline Renderer
     */
    class TimelineRenderer {
        constructor(options = {}) {
            this.options = { ...AUDIT_CONSTANTS.DEFAULT_SETTINGS, ...options };
            this.templates = this.initializeTemplates();
        }

        /**
         * Initialize timeline templates
         */
        initializeTemplates() {
            return {
                vertical: this.getVerticalTimelineTemplate(),
                horizontal: this.getHorizontalTimelineTemplate(),
                compact: this.getCompactTimelineTemplate(),
                detailed: this.getDetailedTimelineTemplate()
            };
        }

        /**
         * Get vertical timeline template
         */
        getVerticalTimelineTemplate() {
            return `
                <div class="sakip-audit-timeline sakip-timeline-vertical">
                    <div class="sakip-timeline-header">
                        <h3 class="sakip-timeline-title">Audit Trail Timeline</h3>
                        <div class="sakip-timeline-controls">
                            <button class="sakip-btn sakip-btn-sm sakip-btn-secondary" id="refresh-timeline">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="sakip-btn sakip-btn-sm sakip-btn-primary" id="export-timeline">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="sakip-timeline-container" id="timeline-container">
                        <div class="sakip-timeline-line"></div>
                        <div class="sakip-timeline-entries" id="timeline-entries">
                            <!-- Entries will be populated here -->
                        </div>
                    </div>
                    <div class="sakip-timeline-pagination" id="timeline-pagination">
                        <!-- Pagination will be populated here -->
                    </div>
                </div>
            `;
        }

        /**
         * Get horizontal timeline template
         */
        getHorizontalTimelineTemplate() {
            return `
                <div class="sakip-audit-timeline sakip-timeline-horizontal">
                    <div class="sakip-timeline-header">
                        <h3 class="sakip-timeline-title">Audit Trail Timeline</h3>
                        <div class="sakip-timeline-controls">
                            <button class="sakip-btn sakip-btn-sm sakip-btn-secondary" id="refresh-timeline">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="sakip-btn sakip-btn-sm sakip-btn-primary" id="export-timeline">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="sakip-timeline-container" id="timeline-container">
                        <div class="sakip-timeline-line-horizontal"></div>
                        <div class="sakip-timeline-entries-horizontal" id="timeline-entries">
                            <!-- Entries will be populated here -->
                        </div>
                    </div>
                    <div class="sakip-timeline-pagination" id="timeline-pagination">
                        <!-- Pagination will be populated here -->
                    </div>
                </div>
            `;
        }

        /**
         * Get compact timeline template
         */
        getCompactTimelineTemplate() {
            return `
                <div class="sakip-audit-timeline sakip-timeline-compact">
                    <div class="sakip-timeline-header">
                        <h3 class="sakip-timeline-title">Audit Trail Summary</h3>
                        <div class="sakip-timeline-controls">
                            <button class="sakip-btn sakip-btn-sm sakip-btn-secondary" id="refresh-timeline">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="sakip-btn sakip-btn-sm sakip-btn-primary" id="export-timeline">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="sakip-timeline-container" id="timeline-container">
                        <div class="sakip-timeline-entries-compact" id="timeline-entries">
                            <!-- Entries will be populated here -->
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Get detailed timeline template
         */
        getDetailedTimelineTemplate() {
            return `
                <div class="sakip-audit-timeline sakip-timeline-detailed">
                    <div class="sakip-timeline-header">
                        <h3 class="sakip-timeline-title">Detailed Audit Trail</h3>
                        <div class="sakip-timeline-controls">
                            <button class="sakip-btn sakip-btn-sm sakip-btn-secondary" id="refresh-timeline">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="sakip-btn sakip-btn-sm sakip-btn-primary" id="export-timeline">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="sakip-timeline-container" id="timeline-container">
                        <div class="sakip-timeline-line"></div>
                        <div class="sakip-timeline-entries-detailed" id="timeline-entries">
                            <!-- Entries will be populated here -->
                        </div>
                    </div>
                    <div class="sakip-timeline-pagination" id="timeline-pagination">
                        <!-- Pagination will be populated here -->
                    </div>
                </div>
            `;
        }

        /**
         * Render timeline
         */
        renderTimeline(container, entries, pagination, options = {}) {
            const timelineType = options.timelineType || this.options.timelineType;
            const template = this.templates[timelineType] || this.templates.vertical;
            
            container.innerHTML = template;
            
            const entriesContainer = container.querySelector('#timeline-entries');
            const paginationContainer = container.querySelector('#timeline-pagination');
            
            // Render entries based on timeline type
            switch (timelineType) {
                case 'horizontal':
                    this.renderHorizontalEntries(entriesContainer, entries);
                    break;
                case 'compact':
                    this.renderCompactEntries(entriesContainer, entries);
                    break;
                case 'detailed':
                    this.renderDetailedEntries(entriesContainer, entries);
                    break;
                default:
                    this.renderVerticalEntries(entriesContainer, entries);
            }
            
            // Render pagination
            this.renderPagination(paginationContainer, pagination);
            
            // Setup event listeners
            this.setupTimelineEventListeners(container);
        }

        /**
         * Render vertical timeline entries
         */
        renderVerticalEntries(container, entries) {
            if (!entries || entries.length === 0) {
                container.innerHTML = '<div class="sakip-timeline-empty">Tidak ada data audit ditemukan</div>';
                return;
            }

            const entriesHTML = entries.map(entry => {
                const iconClass = this.getActionIcon(entry.action);
                const severityClass = `sakip-severity-${entry.severity}`;
                const statusClass = `sakip-status-${entry.status}`;
                
                return `
                    <div class="sakip-timeline-entry ${severityClass}" data-entry-id="${entry.id}">
                        <div class="sakip-timeline-marker ${statusClass}">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="sakip-timeline-content">
                            <div class="sakip-timeline-header">
                                <h4 class="sakip-timeline-action">${this.formatActionText(entry.action)}</h4>
                                <span class="sakip-timeline-timestamp">${this.formatTimestamp(entry.timestamp)}</span>
                            </div>
                            <div class="sakip-timeline-body">
                                <p class="sakip-timeline-description">
                                    <strong>${entry.userName}</strong> ${this.formatActionText(entry.action)} 
                                    <strong>${entry.resourceName}</strong> (${entry.resource})
                                </p>
                                <div class="sakip-timeline-details">
                                    <span class="sakip-timeline-user">User: ${entry.userId}</span>
                                    <span class="sakip-timeline-ip">IP: ${entry.ipAddress}</span>
                                    <span class="sakip-timeline-severity">Severity: ${entry.severity}</span>
                                </div>
                            </div>
                            <div class="sakip-timeline-footer">
                                <button class="sakip-btn sakip-btn-sm sakip-btn-link" onclick="SAKIP_AUDIT_TRAIL.showEntryDetails(${entry.id})">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = entriesHTML;
        }

        /**
         * Render horizontal timeline entries
         */
        renderHorizontalEntries(container, entries) {
            if (!entries || entries.length === 0) {
                container.innerHTML = '<div class="sakip-timeline-empty">Tidak ada data audit ditemukan</div>';
                return;
            }

            const entriesHTML = entries.map(entry => {
                const iconClass = this.getActionIcon(entry.action);
                const severityClass = `sakip-severity-${entry.severity}`;
                const statusClass = `sakip-status-${entry.status}`;
                
                return `
                    <div class="sakip-timeline-entry-horizontal ${severityClass}" data-entry-id="${entry.id}">
                        <div class="sakip-timeline-marker-horizontal ${statusClass}">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="sakip-timeline-content-horizontal">
                            <div class="sakip-timeline-header-horizontal">
                                <h5 class="sakip-timeline-action">${this.formatActionText(entry.action)}</h5>
                                <span class="sakip-timeline-timestamp">${this.formatTimestamp(entry.timestamp)}</span>
                            </div>
                            <div class="sakip-timeline-body-horizontal">
                                <p class="sakip-timeline-user">${entry.userId}</p>
                                <p class="sakip-timeline-resource">${entry.resource}</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = entriesHTML;
        }

        /**
         * Render compact timeline entries
         */
        renderCompactEntries(container, entries) {
            if (!entries || entries.length === 0) {
                container.innerHTML = '<div class="sakip-timeline-empty">Tidak ada data audit ditemukan</div>';
                return;
            }

            const entriesHTML = entries.map(entry => {
                const iconClass = this.getActionIcon(entry.action);
                const severityClass = `sakip-severity-${entry.severity}`;
                
                return `
                    <div class="sakip-timeline-entry-compact ${severityClass}" data-entry-id="${entry.id}">
                        <div class="sakip-timeline-icon">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="sakip-timeline-info">
                            <span class="sakip-timeline-action-compact">${entry.action}</span>
                            <span class="sakip-timeline-user-compact">${entry.userId}</span>
                            <span class="sakip-timeline-time-compact">${this.formatTime(entry.timestamp)}</span>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = entriesHTML;
        }

        /**
         * Render detailed timeline entries
         */
        renderDetailedEntries(container, entries) {
            if (!entries || entries.length === 0) {
                container.innerHTML = '<div class="sakip-timeline-empty">Tidak ada data audit ditemukan</div>';
                return;
            }

            const entriesHTML = entries.map(entry => {
                const iconClass = this.getActionIcon(entry.action);
                const severityClass = `sakip-severity-${entry.severity}`;
                const statusClass = `sakip-status-${entry.status}`;
                
                return `
                    <div class="sakip-timeline-entry-detailed ${severityClass}" data-entry-id="${entry.id}">
                        <div class="sakip-timeline-marker-detailed ${statusClass}">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="sakip-timeline-content-detailed">
                            <div class="sakip-timeline-header-detailed">
                                <h4 class="sakip-timeline-action-detailed">${this.formatActionText(entry.action)}</h4>
                                <div class="sakip-timeline-meta">
                                    <span class="sakip-timeline-timestamp-detailed">${this.formatTimestamp(entry.timestamp)}</span>
                                    <span class="sakip-timeline-severity-detailed sakip-severity-badge sakip-severity-${entry.severity}">${entry.severity}</span>
                                    <span class="sakip-timeline-status-detailed sakip-status-badge sakip-status-${entry.status}">${entry.status}</span>
                                </div>
                            </div>
                            <div class="sakip-timeline-body-detailed">
                                <div class="sakip-timeline-main-info">
                                    <p><strong>User:</strong> ${entry.userName} (${entry.userId})</p>
                                    <p><strong>Resource:</strong> ${entry.resourceName} (${entry.resource})</p>
                                    <p><strong>Resource ID:</strong> ${entry.resourceId}</p>
                                    <p><strong>IP Address:</strong> ${entry.ipAddress}</p>
                                </div>
                                <div class="sakip-timeline-details-detailed">
                                    ${entry.details ? this.formatDetails(entry.details) : ''}
                                </div>
                                <div class="sakip-timeline-metadata">
                                    ${entry.metadata ? this.formatMetadata(entry.metadata) : ''}
                                </div>
                            </div>
                            <div class="sakip-timeline-footer-detailed">
                                <button class="sakip-btn sakip-btn-sm sakip-btn-link" onclick="SAKIP_AUDIT_TRAIL.showEntryDetails(${entry.id})">
                                    <i class="fas fa-info-circle"></i> Full Details
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = entriesHTML;
        }

        /**
         * Render pagination
         */
        renderPagination(container, pagination) {
            if (!pagination || pagination.totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            const paginationHTML = `
                <div class="sakip-pagination-info">
                    Showing ${((pagination.currentPage - 1) * pagination.itemsPerPage) + 1} to 
                    ${Math.min(pagination.currentPage * pagination.itemsPerPage, pagination.totalRecords)} of 
                    ${pagination.totalRecords} entries
                </div>
                <div class="sakip-pagination-controls">
                    <button class="sakip-btn sakip-btn-sm sakip-btn-secondary ${!pagination.hasPrevious ? 'disabled' : ''}" 
                            onclick="SAKIP_AUDIT_TRAIL.goToPage(${pagination.currentPage - 1})" 
                            ${!pagination.hasPrevious ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    
                    <div class="sakip-pagination-pages">
                        ${this.generatePageNumbers(pagination.currentPage, pagination.totalPages)}
                    </div>
                    
                    <button class="sakip-btn sakip-btn-sm sakip-btn-secondary ${!pagination.hasNext ? 'disabled' : ''}" 
                            onclick="SAKIP_AUDIT_TRAIL.goToPage(${pagination.currentPage + 1})" 
                            ${!pagination.hasNext ? 'disabled' : ''}>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            `;

            container.innerHTML = paginationHTML;
        }

        /**
         * Generate page numbers
         */
        generatePageNumbers(currentPage, totalPages) {
            const pages = [];
            const maxVisiblePages = 5;
            
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            // First page
            if (startPage > 1) {
                pages.push(`<button class="sakip-btn sakip-btn-sm sakip-btn-link" onclick="SAKIP_AUDIT_TRAIL.goToPage(1)">1</button>`);
                if (startPage > 2) {
                    pages.push('<span class="sakip-pagination-ellipsis">...</span>');
                }
            }

            // Middle pages
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                pages.push(`<button class="sakip-btn sakip-btn-sm sakip-btn-link ${activeClass}" onclick="SAKIP_AUDIT_TRAIL.goToPage(${i})">${i}</button>`);
            }

            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    pages.push('<span class="sakip-pagination-ellipsis">...</span>');
                }
                pages.push(`<button class="sakip-btn sakip-btn-sm sakip-btn-link" onclick="SAKIP_AUDIT_TRAIL.goToPage(${totalPages})">${totalPages}</button>`);
            }

            return pages.join('');
        }

        /**
         * Setup timeline event listeners
         */
        setupTimelineEventListeners(container) {
            // Refresh button
            const refreshBtn = container.querySelector('#refresh-timeline');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => {
                    if (typeof window !== 'undefined' && window.SAKIP_AUDIT_TRAIL) {
                        window.SAKIP_AUDIT_TRAIL.refreshTimeline();
                    }
                });
            }

            // Export button
            const exportBtn = container.querySelector('#export-timeline');
            if (exportBtn) {
                exportBtn.addEventListener('click', () => {
                    if (typeof window !== 'undefined' && window.SAKIT_AUDIT_TRAIL) {
                        window.SAKIP_AUDIT_TRAIL.exportTimeline();
                    }
                });
            }
        }

        /**
         * Get action icon
         */
        getActionIcon(action) {
            const icons = {
                create: 'fas fa-plus-circle',
                update: 'fas fa-edit',
                delete: 'fas fa-trash',
                view: 'fas fa-eye',
                export: 'fas fa-download',
                import: 'fas fa-upload',
                login: 'fas fa-sign-in-alt',
                logout: 'fas fa-sign-out-alt',
                approve: 'fas fa-check-circle',
                reject: 'fas fa-times-circle',
                submit: 'fas fa-paper-plane',
                assign: 'fas fa-user-plus',
                unassign: 'fas fa-user-minus',
                configure: 'fas fa-cog',
                backup: 'fas fa-database',
                restore: 'fas fa-history',
                schedule: 'fas fa-calendar-plus',
                cancel: 'fas fa-ban'
            };

            return icons[action] || 'fas fa-info-circle';
        }

        /**
         * Format action text
         */
        formatActionText(action) {
            const actionTexts = {
                create: 'Membuat',
                update: 'Memperbarui',
                delete: 'Menghapus',
                view: 'Melihat',
                export: 'Mengekspor',
                import: 'Mengimpor',
                login: 'Login',
                logout: 'Logout',
                approve: 'Menyetujui',
                reject: 'Menolak',
                submit: 'Mengirim',
                assign: 'Menetapkan',
                unassign: 'Membatalkan penugasan',
                configure: 'Mengkonfigurasi',
                backup: 'Backup',
                restore: 'Restore',
                schedule: 'Menjadwalkan',
                cancel: 'Membatalkan'
            };

            return actionTexts[action] || action;
        }

        /**
         * Format timestamp
         */
        formatTimestamp(timestamp) {
            const date = new Date(timestamp);
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: this.options.timezone
            };
            return date.toLocaleString('id-ID', options);
        }

        /**
         * Format time
         */
        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                timeZone: this.options.timezone
            });
        }

        /**
         * Format details
         */
        formatDetails(details) {
            if (!details) return '';
            
            let html = '<div class="sakip-details-content">';
            
            Object.entries(details).forEach(([key, value]) => {
                html += `<div class="sakip-detail-item">`;
                html += `<span class="sakip-detail-key">${key}:</span>`;
                html += `<span class="sakip-detail-value">${JSON.stringify(value)}</span>`;
                html += `</div>`;
            });
            
            html += '</div>';
            return html;
        }

        /**
         * Format metadata
         */
        formatMetadata(metadata) {
            if (!metadata) return '';
            
            let html = '<div class="sakip-metadata-content">';
            html += '<h6>Metadata:</h6>';
            
            Object.entries(metadata).forEach(([key, value]) => {
                html += `<div class="sakip-metadata-item">`;
                html += `<span class="sakip-metadata-key">${key}:</span>`;
                html += `<span class="sakip-metadata-value">${value}</span>`;
                html += `</div>`;
            });
            
            html += '</div>';
            return html;
        }
    }

    /**
     * Filter Manager
     */
    class FilterManager {
        constructor() {
            this.activeFilters = {};
            this.filterPresets = new Map();
            this.initializeDefaultPresets();
        }

        /**
         * Initialize default filter presets
         */
        initializeDefaultPresets() {
            this.filterPresets.set('recent_activity', {
                name: 'Aktivitas Terbaru',
                description: 'Menampilkan aktivitas dalam 24 jam terakhir',
                filters: {
                    dateRange: {
                        type: AUDIT_CONSTANTS.TIME_RANGES.TODAY
                    }
                }
            });

            this.filterPresets.set('high_severity', {
                name: 'Keparahan Tinggi',
                description: 'Menampilkan aktivitas dengan keparahan tinggi dan kritis',
                filters: {
                    severity: [AUDIT_CONSTANTS.SEVERITY_LEVELS.HIGH, AUDIT_CONSTANTS.SEVERITY_LEVELS.CRITICAL]
                }
            });

            this.filterPresets.set('failed_actions', {
                name: 'Aksi Gagal',
                description: 'Menampilkan semua aksi yang gagal',
                filters: {
                    status: AUDIT_CONSTANTS.STATUS_TYPES.FAILED
                }
            });

            this.filterPresets.set('user_management', {
                name: 'Manajemen User',
                description: 'Menampilkan aktivitas terkait manajemen user',
                filters: {
                    resource: AUDIT_CONSTANTS.RESOURCE_TYPES.USER
                }
            });

            this.filterPresets.set('system_changes', {
                name: 'Perubahan Sistem',
                description: 'Menampilkan perubahan konfigurasi sistem',
                filters: {
                    action: [AUDIT_CONSTANTS.ACTION_TYPES.CONFIGURE, AUDIT_CONSTANTS.ACTION_TYPES.UPDATE],
                    resource: AUDIT_CONSTANTS.RESOURCE_TYPES.SYSTEM
                }
            });
        }

        /**
         * Create filter UI
         */
        createFilterUI(container, options = {}) {
            const filterHTML = `
                <div class="sakip-audit-filters">
                    <div class="sakip-filter-header">
                        <h4>Filter Audit Trail</h4>
                        <div class="sakip-filter-actions">
                            <button class="sakip-btn sakip-btn-sm sakip-btn-secondary" id="clear-filters">
                                <i class="fas fa-times"></i> Clear All
                            </button>
                            <button class="sakip-btn sakip-btn-sm sakip-btn-primary" id="apply-filters">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                        </div>
                    </div>
                    
                    <div class="sakip-filter-content">
                        <div class="sakip-filter-section">
                            <h5>Quick Filters</h5>
                            <div class="sakip-filter-presets" id="filter-presets">
                                ${this.generateFilterPresets()}
                            </div>
                        </div>
                        
                        <div class="sakip-filter-section">
                            <h5>Date Range</h5>
                            <div class="sakip-date-filter">
                                <select id="date-range-type" class="sakip-form-control">
                                    <option value="">Select Date Range</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_year">This Year</option>
                                    <option value="last_year">Last Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <div class="sakip-custom-date-range" id="custom-date-range" style="display: none;">
                                    <input type="date" id="start-date" class="sakip-form-control">
                                    <input type="date" id="end-date" class="sakip-form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="sakip-filter-section">
                            <h5>Filters</h5>
                            <div class="sakip-filter-grid">
                                <div class="sakip-filter-field">
                                    <label>User</label>
                                    <input type="text" id="filter-user" class="sakip-form-control" placeholder="Enter user ID">
                                </div>
                                <div class="sakip-filter-field">
                                    <label>Action</label>
                                    <select id="filter-action" class="sakip-form-control">
                                        <option value="">All Actions</option>
                                        ${this.generateActionOptions()}
                                    </select>
                                </div>
                                <div class="sakip-filter-field">
                                    <label>Resource</label>
                                    <select id="filter-resource" class="sakip-form-control">
                                        <option value="">All Resources</option>
                                        ${this.generateResourceOptions()}
                                    </select>
                                </div>
                                <div class="sakip-filter-field">
                                    <label>Severity</label>
                                    <select id="filter-severity" class="sakip-form-control">
                                        <option value="">All Severities</option>
                                        ${this.generateSeverityOptions()}
                                    </select>
                                </div>
                                <div class="sakip-filter-field">
                                    <label>Status</label>
                                    <select id="filter-status" class="sakip-form-control">
                                        <option value="">All Statuses</option>
                                        ${this.generateStatusOptions()}
                                    </select>
                                </div>
                                <div class="sakip-filter-field">
                                    <label>Search</label>
                                    <input type="text" id="filter-search" class="sakip-form-control" placeholder="Search keywords...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = filterHTML;
            this.setupFilterEventListeners(container);
        }

        /**
         * Generate filter presets
         */
        generateFilterPresets() {
            return Array.from(this.filterPresets.entries()).map(([id, preset]) => `
                <div class="sakip-filter-preset" data-preset-id="${id}">
                    <div class="sakip-preset-info">
                        <h6>${preset.name}</h6>
                        <p>${preset.description}</p>
                    </div>
                    <button class="sakip-btn sakip-btn-sm sakip-btn-link" onclick="SAKIP_AUDIT_TRAIL.applyPreset('${id}')">
                        Apply
                    </button>
                </div>
            `).join('');
        }

        /**
         * Generate action options
         */
        generateActionOptions() {
            return Object.values(AUDIT_CONSTANTS.ACTION_TYPES).map(action => `
                <option value="${action}">${this.formatActionText(action)}</option>
            `).join('');
        }

        /**
         * Generate resource options
         */
        generateResourceOptions() {
            return Object.values(AUDIT_CONSTANTS.RESOURCE_TYPES).map(resource => `
                <option value="${resource}">${resource.charAt(0).toUpperCase() + resource.slice(1)}</option>
            `).join('');
        }

        /**
         * Generate severity options
         */
        generateSeverityOptions() {
            return Object.values(AUDIT_CONSTANTS.SEVERITY_LEVELS).map(severity => `
                <option value="${severity}">${severity.charAt(0).toUpperCase() + severity.slice(1)}</option>
            `).join('');
        }

        /**
         * Generate status options
         */
        generateStatusOptions() {
            return Object.values(AUDIT_CONSTANTS.STATUS_TYPES).map(status => `
                <option value="${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</option>
            `).join('');
        }

        /**
         * Setup filter event listeners
         */
        setupFilterEventListeners(container) {
            // Date range type change
            const dateRangeType = container.querySelector('#date-range-type');
            if (dateRangeType) {
                dateRangeType.addEventListener('change', (e) => {
                    const customDateRange = container.querySelector('#custom-date-range');
                    customDateRange.style.display = e.target.value === 'custom' ? 'block' : 'none';
                });
            }

            // Apply filters button
            const applyFiltersBtn = container.querySelector('#apply-filters');
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', () => {
                    const filters = this.collectFilterValues(container);
                    if (typeof window !== 'undefined' && window.SAKIP_AUDIT_TRAIL) {
                        window.SAKIP_AUDIT_TRAIL.applyFilters(filters);
                    }
                });
            }

            // Clear filters button
            const clearFiltersBtn = container.querySelector('#clear-filters');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', () => {
                    this.clearFilterValues(container);
                    if (typeof window !== 'undefined' && window.SAKIP_AUDIT_TRAIL) {
                        window.SAKIP_AUDIT_TRAIL.clearFilters();
                    }
                });
            }
        }

        /**
         * Collect filter values
         */
        collectFilterValues(container) {
            const filters = {};
            
            // Date range
            const dateRangeType = container.querySelector('#date-range-type')?.value;
            if (dateRangeType && dateRangeType !== '') {
                if (dateRangeType === 'custom') {
                    const startDate = container.querySelector('#start-date')?.value;
                    const endDate = container.querySelector('#end-date')?.value;
                    if (startDate && endDate) {
                        filters.dateRange = {
                            type: 'custom',
                            startDate: startDate,
                            endDate: endDate
                        };
                    }
                } else {
                    filters.dateRange = { type: dateRangeType };
                }
            }

            // Other filters
            const userId = container.querySelector('#filter-user')?.value;
            if (userId) filters.userId = userId;

            const action = container.querySelector('#filter-action')?.value;
            if (action) filters.action = action;

            const resource = container.querySelector('#filter-resource')?.value;
            if (resource) filters.resource = resource;

            const severity = container.querySelector('#filter-severity')?.value;
            if (severity) filters.severity = severity;

            const status = container.querySelector('#filter-status')?.value;
            if (status) filters.status = status;

            const searchQuery = container.querySelector('#filter-search')?.value;
            if (searchQuery) filters.searchQuery = searchQuery;

            return filters;
        }

        /**
         * Clear filter values
         */
        clearFilterValues(container) {
            container.querySelectorAll('input, select').forEach(element => {
                if (element.type === 'checkbox' || element.type === 'radio') {
                    element.checked = false;
                } else {
                    element.value = '';
                }
            });
            
            container.querySelector('#custom-date-range').style.display = 'none';
        }

        /**
         * Apply preset filters
         */
        applyPreset(presetId) {
            const preset = this.filterPresets.get(presetId);
            if (preset) {
                this.activeFilters = { ...preset.filters };
                return preset.filters;
            }
            return {};
        }

        /**
         * Get active filters
         */
        getActiveFilters() {
            return this.activeFilters;
        }

        /**
         * Set active filters
         */
        setActiveFilters(filters) {
            this.activeFilters = { ...filters };
        }

        /**
         * Clear active filters
         */
        clearActiveFilters() {
            this.activeFilters = {};
        }
    }

    /**
     * Audit Trail Manager
     */
    class AuditTrailManager {
        constructor() {
            this.dataManager = new AuditTrailDataManager();
            this.timelineRenderer = new TimelineRenderer();
            this.filterManager = new FilterManager();
            this.activeInterfaces = new Map();
            this.currentInterface = null;
        }

        /**
         * Create audit trail interface
         */
        createAuditTrailInterface(containerId, options = {}) {
            const container = document.getElementById(containerId);
            if (!container) {
                throw new Error(`Container with ID '${containerId}' not found`);
            }

            const interface = new AuditTrailInterface(container, this, options);
            this.activeInterfaces.set(containerId, interface);
            this.currentInterface = interface;
            
            return interface;
        }

        /**
         * Get audit entries
         */
        async getAuditEntries(filters = {}, options = {}) {
            return this.dataManager.getAuditEntries(filters, options);
        }

        /**
         * Add audit entry
         */
        async addAuditEntry(entry) {
            return this.dataManager.addAuditEntry(entry);
        }

        /**
         * Export audit data
         */
        async exportAuditData(format, filters) {
            return this.dataManager.exportAuditData(format, filters);
        }

        /**
         * Get statistics
         */
        getStatistics(filters = {}) {
            return this.dataManager.getStatistics(filters);
        }

        /**
         * Apply filters
         */
        applyFilters(filters) {
            this.filterManager.setActiveFilters(filters);
            if (this.currentInterface) {
                this.currentInterface.refreshTimeline();
            }
        }

        /**
         * Clear filters
         */
        clearFilters() {
            this.filterManager.clearActiveFilters();
            if (this.currentInterface) {
                this.currentInterface.refreshTimeline();
            }
        }

        /**
         * Apply preset
         */
        applyPreset(presetId) {
            const filters = this.filterManager.applyPreset(presetId);
            if (this.currentInterface) {
                this.currentInterface.applyFilters(filters);
            }
        }

        /**
         * Refresh timeline
         */
        refreshTimeline() {
            if (this.currentInterface) {
                this.currentInterface.refreshTimeline();
            }
        }

        /**
         * Export timeline
         */
        exportTimeline() {
            if (this.currentInterface) {
                this.currentInterface.exportTimeline();
            }
        }

        /**
         * Show entry details
         */
        showEntryDetails(entryId) {
            if (this.currentInterface) {
                this.currentInterface.showEntryDetails(entryId);
            }
        }

        /**
         * Go to page
         */
        goToPage(page) {
            if (this.currentInterface) {
                this.currentInterface.goToPage(page);
            }
        }
    }

    /**
     * Audit Trail Interface
     */
    class AuditTrailInterface {
        constructor(container, manager, options = {}) {
            this.container = container;
            this.manager = manager;
            this.options = { ...AUDIT_CONSTANTS.DEFAULT_SETTINGS, ...options };
            this.currentFilters = {};
            this.currentPage = 1;
            this.isLoading = false;
            
            this.setupInterface();
        }

        /**
         * Setup interface
         */
        setupInterface() {
            this.container.innerHTML = `
                <div class="sakip-audit-trail-interface">
                    <div class="sakip-interface-header">
                        <h2>Audit Trail System</h2>
                        <div class="sakip-interface-controls">
                            <div class="sakip-auto-refresh">
                                <label>
                                    <input type="checkbox" id="auto-refresh" ${this.options.autoRefresh ? 'checked' : ''}>
                                    Auto Refresh
                                </label>
                                <select id="refresh-interval" class="sakip-form-control sakip-form-control-sm">
                                    <option value="10000" ${this.options.refreshInterval === 10000 ? 'selected' : ''}>10 seconds</option>
                                    <option value="30000" ${this.options.refreshInterval === 30000 ? 'selected' : ''}>30 seconds</option>
                                    <option value="60000" ${this.options.refreshInterval === 60000 ? 'selected' : ''}>1 minute</option>
                                    <option value="300000" ${this.options.refreshInterval === 300000 ? 'selected' : ''}>5 minutes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sakip-interface-content">
                        <div class="sakip-filter-panel" id="filter-panel">
                            <!-- Filter panel will be populated here -->
                        </div>
                        
                        <div class="sakip-timeline-panel" id="timeline-panel">
                            <!-- Timeline will be populated here -->
                        </div>
                        
                        <div class="sakip-statistics-panel" id="statistics-panel">
                            <!-- Statistics will be populated here -->
                        </div>
                    </div>
                </div>
            `;

            this.setupPanels();
            this.setupEventListeners();
            this.refreshTimeline();
        }

        /**
         * Setup panels
         */
        setupPanels() {
            // Create filter panel
            const filterPanel = this.container.querySelector('#filter-panel');
            this.manager.filterManager.createFilterUI(filterPanel);

            // Create timeline panel
            const timelinePanel = this.container.querySelector('#timeline-panel');
            this.manager.timelineRenderer.renderTimeline(timelinePanel, [], {
                currentPage: 1,
                totalPages: 1,
                totalRecords: 0,
                itemsPerPage: this.options.itemsPerPage,
                hasNext: false,
                hasPrevious: false
            }, this.options);

            // Create statistics panel if enabled
            if (this.options.showStatistics) {
                const statisticsPanel = this.container.querySelector('#statistics-panel');
                this.renderStatistics(statisticsPanel);
            }
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Auto refresh toggle
            const autoRefreshCheckbox = this.container.querySelector('#auto-refresh');
            if (autoRefreshCheckbox) {
                autoRefreshCheckbox.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        const interval = parseInt(this.container.querySelector('#refresh-interval').value);
                        this.manager.dataManager.enableAutoRefresh(interval);
                    } else {
                        this.manager.dataManager.disableAutoRefresh();
                    }
                });
            }

            // Refresh interval change
            const refreshIntervalSelect = this.container.querySelector('#refresh-interval');
            if (refreshIntervalSelect) {
                refreshIntervalSelect.addEventListener('change', (e) => {
                    const autoRefreshCheckbox = this.container.querySelector('#auto-refresh');
                    if (autoRefreshCheckbox.checked) {
                        const interval = parseInt(e.target.value);
                        this.manager.dataManager.enableAutoRefresh(interval);
                    }
                });
            }
        }

        /**
         * Refresh timeline
         */
        async refreshTimeline() {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showLoadingState();
            
            try {
                const result = await this.manager.getAuditEntries(this.currentFilters, {
                    currentPage: this.currentPage,
                    itemsPerPage: this.options.itemsPerPage
                });
                
                const timelinePanel = this.container.querySelector('#timeline-panel');
                this.manager.timelineRenderer.renderTimeline(timelinePanel, result.data, result.pagination, this.options);
                
                // Update statistics if enabled
                if (this.options.showStatistics) {
                    const statisticsPanel = this.container.querySelector('#statistics-panel');
                    this.renderStatistics(statisticsPanel, result.data);
                }
                
            } catch (error) {
                this.showError('Failed to load audit data: ' + error.message);
            } finally {
                this.isLoading = false;
                this.hideLoadingState();
            }
        }

        /**
         * Apply filters
         */
        async applyFilters(filters) {
            this.currentFilters = { ...filters };
            this.currentPage = 1; // Reset to first page
            await this.refreshTimeline();
        }

        /**
         * Export timeline
         */
        async exportTimeline() {
            try {
                const result = await this.manager.exportAuditData('csv', this.currentFilters);
                
                // Create download link
                const blob = new Blob([result.data], { type: 'text/csv' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `audit_trail_export_${new Date().toISOString().slice(0, 10)}.csv`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
                
                this.showSuccess('Audit data exported successfully');
            } catch (error) {
                this.showError('Failed to export audit data: ' + error.message);
            }
        }

        /**
         * Go to page
         */
        async goToPage(page) {
            this.currentPage = page;
            await this.refreshTimeline();
        }

        /**
         * Show entry details
         */
        showEntryDetails(entryId) {
            const entry = this.manager.dataManager.auditEntries.find(e => e.id === entryId);
            if (!entry) {
                this.showError('Entry not found');
                return;
            }

            // Create modal for entry details
            const modal = document.createElement('div');
            modal.className = 'sakip-modal';
            modal.innerHTML = `
                <div class="sakip-modal-content">
                    <div class="sakip-modal-header">
                        <h3>Audit Entry Details</h3>
                        <button class="sakip-modal-close" onclick="this.closest('.sakip-modal').remove()">&times;</button>
                    </div>
                    <div class="sakip-modal-body">
                        <div class="sakip-entry-details">
                            <div class="sakip-detail-section">
                                <h4>Basic Information</h4>
                                <div class="sakip-detail-grid">
                                    <div class="sakip-detail-item">
                                        <label>ID:</label>
                                        <span>${entry.id}</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>Timestamp:</label>
                                        <span>${this.manager.timelineRenderer.formatTimestamp(entry.timestamp)}</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>User:</label>
                                        <span>${entry.userName} (${entry.userId})</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>Action:</label>
                                        <span>${this.manager.timelineRenderer.formatActionText(entry.action)}</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>Resource:</label>
                                        <span>${entry.resourceName} (${entry.resource})</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>Resource ID:</label>
                                        <span>${entry.resourceId}</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>Severity:</label>
                                        <span class="sakip-severity-badge sakip-severity-${entry.severity}">${entry.severity}</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>Status:</label>
                                        <span class="sakip-status-badge sakip-status-${entry.status}">${entry.status}</span>
                                    </div>
                                    <div class="sakip-detail-item">
                                        <label>IP Address:</label>
                                        <span>${entry.ipAddress}</span>
                                    </div>
                                </div>
                            </div>
                            
                            ${entry.details ? `
                                <div class="sakip-detail-section">
                                    <h4>Action Details</h4>
                                    <div class="sakip-details-content">
                                        ${Object.entries(entry.details).map(([key, value]) => `
                                            <div class="sakip-detail-item">
                                                <label>${key}:</label>
                                                <span>${JSON.stringify(value, null, 2)}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            
                            ${entry.metadata ? `
                                <div class="sakip-detail-section">
                                    <h4>Metadata</h4>
                                    <div class="sakip-metadata-content">
                                        ${Object.entries(entry.metadata).map(([key, value]) => `
                                            <div class="sakip-metadata-item">
                                                <label>${key}:</label>
                                                <span>${value}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="sakip-modal-footer">
                        <button class="sakip-btn sakip-btn-secondary" onclick="this.closest('.sakip-modal').remove()">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            modal.style.display = 'block';
        }

        /**
         * Render statistics
         */
        renderStatistics(container, entries = []) {
            const stats = this.manager.getStatistics(this.currentFilters);
            
            const statsHTML = `
                <div class="sakip-audit-statistics">
                    <h4>Audit Trail Statistics</h4>
                    <div class="sakip-statistics-grid">
                        <div class="sakip-stat-card">
                            <div class="sakip-stat-value">${stats.total}</div>
                            <div class="sakip-stat-label">Total Entries</div>
                        </div>
                        <div class="sakip-stat-card">
                            <div class="sakip-stat-value">${Object.keys(stats.byAction).length}</div>
                            <div class="sakip-stat-label">Action Types</div>
                        </div>
                        <div class="sakip-stat-card">
                            <div class="sakip-stat-value">${Object.keys(stats.byResource).length}</div>
                            <div class="sakip-stat-label">Resource Types</div>
                        </div>
                        <div class="sakip-stat-card">
                            <div class="sakip-stat-value">${Object.keys(stats.byUser).length}</div>
                            <div class="sakip-stat-label">Active Users</div>
                        </div>
                    </div>
                    
                    <div class="sakip-statistics-charts">
                        <div class="sakip-chart-container">
                            <h5>Actions Distribution</h5>
                            <canvas id="actions-chart"></canvas>
                        </div>
                        <div class="sakip-chart-container">
                            <h5>Severity Distribution</h5>
                            <canvas id="severity-chart"></canvas>
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = statsHTML;
            
            // Render charts if Chart.js is available
            if (typeof Chart !== 'undefined') {
                setTimeout(() => {
                    this.renderStatisticsCharts(stats);
                }, 100);
            }
        }

        /**
         * Render statistics charts
         */
        renderStatisticsCharts(stats) {
            // Actions chart
            const actionsCtx = document.getElementById('actions-chart');
            if (actionsCtx) {
                new Chart(actionsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(stats.byAction),
                        datasets: [{
                            data: Object.values(stats.byAction),
                            backgroundColor: [
                                '#FF6384',
                                '#36A2EB',
                                '#FFCE56',
                                '#4BC0C0',
                                '#9966FF',
                                '#FF9F40'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Severity chart
            const severityCtx = document.getElementById('severity-chart');
            if (severityCtx) {
                new Chart(severityCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(stats.bySeverity),
                        datasets: [{
                            label: 'Count',
                            data: Object.values(stats.bySeverity),
                            backgroundColor: [
                                '#28a745',
                                '#ffc107',
                                '#fd7e14',
                                '#dc3545'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        /**
         * Show loading state
         */
        showLoadingState() {
            const timelinePanel = this.container.querySelector('#timeline-panel');
            if (timelinePanel) {
                timelinePanel.innerHTML = '<div class="sakip-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            }
        }

        /**
         * Hide loading state
         */
        hideLoadingState() {
            // Loading state is automatically replaced when timeline is rendered
        }

        /**
         * Show error message
         */
        showError(message) {
            const timelinePanel = this.container.querySelector('#timeline-panel');
            if (timelinePanel) {
                timelinePanel.innerHTML = `<div class="sakip-error"><i class="fas fa-exclamation-triangle"></i> ${message}</div>`;
            }
        }

        /**
         * Show success message
         */
        showSuccess(message) {
            // Create temporary success notification
            const notification = document.createElement('div');
            notification.className = 'sakip-success-notification';
            notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }

    /**
     * Main API Object
     */
    const SAKIP_AUDIT_TRAIL = {
        // Constants
        CONSTANTS: AUDIT_CONSTANTS,
        
        // Core classes
        AuditTrailDataManager: AuditTrailDataManager,
        TimelineRenderer: TimelineRenderer,
        FilterManager: FilterManager,
        AuditTrailManager: AuditTrailManager,
        AuditTrailInterface: AuditTrailInterface,
        
        // Main manager instance
        manager: new AuditTrailManager(),
        
        // Convenience methods
        createAuditTrailInterface: function(containerId, options) {
            return this.manager.createAuditTrailInterface(containerId, options);
        },
        
        getAuditEntries: function(filters, options) {
            return this.manager.getAuditEntries(filters, options);
        },
        
        addAuditEntry: function(entry) {
            return this.manager.addAuditEntry(entry);
        },
        
        exportAuditData: function(format, filters) {
            return this.manager.exportAuditData(format, filters);
        },
        
        getStatistics: function(filters) {
            return this.manager.getStatistics(filters);
        },
        
        applyFilters: function(filters) {
            return this.manager.applyFilters(filters);
        },
        
        clearFilters: function() {
            return this.manager.clearFilters();
        },
        
        applyPreset: function(presetId) {
            return this.manager.applyPreset(presetId);
        },
        
        refreshTimeline: function() {
            return this.manager.refreshTimeline();
        },
        
        exportTimeline: function() {
            return this.manager.exportTimeline();
        },
        
        showEntryDetails: function(entryId) {
            return this.manager.showEntryDetails(entryId);
        },
        
        goToPage: function(page) {
            return this.manager.goToPage(page);
        },
        
        // Utility methods
        formatActionText: function(action) {
            const actionTexts = {
                create: 'Membuat',
                update: 'Memperbarui',
                delete: 'Menghapus',
                view: 'Melihat',
                export: 'Mengekspor',
                import: 'Mengimpor',
                login: 'Login',
                logout: 'Logout',
                approve: 'Menyetujui',
                reject: 'Menolak',
                submit: 'Mengirim',
                assign: 'Menetapkan',
                unassign: 'Membatalkan penugasan',
                configure: 'Mengkonfigurasi',
                backup: 'Backup',
                restore: 'Restore',
                schedule: 'Menjadwalkan',
                cancel: 'Membatalkan'
            };
            return actionTexts[action] || action;
        },
        
        formatTimestamp: function(timestamp) {
            const date = new Date(timestamp);
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            return date.toLocaleString('id-ID', options);
        }
    };

    return SAKIP_AUDIT_TRAIL;
}));