/**
 * SAKIP Audit Trail JavaScript Module
 * Handles audit trail filtering, search, and detailed view functionality
 */

class SakipAuditTrail {
    constructor() {
        this.auditData = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.itemsPerPage = 20;
        this.filters = {
            search: '',
            dateRange: '',
            actionType: '',
            user: '',
            institution: ''
        };
        this.init();
    }

    /**
     * Initialize audit trail functionality
     */
    init() {
        this.initializeFilters();
        this.setupSearchFunctionality();
        this.setupPagination();
        this.setupDetailView();
        this.loadAuditData();
    }

    /**
     * Initialize filter controls
     */
    initializeFilters() {
        // Search input
        const searchInput = document.getElementById('auditSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filters.search = e.target.value;
                this.applyFilters();
            });
        }
        
        // Date range filter
        const dateRangeFilter = document.getElementById('auditDateRange');
        if (dateRangeFilter) {
            dateRangeFilter.addEventListener('change', (e) => {
                this.filters.dateRange = e.target.value;
                this.applyFilters();
            });
        }
        
        // Action type filter
        const actionTypeFilter = document.getElementById('auditActionType');
        if (actionTypeFilter) {
            actionTypeFilter.addEventListener('change', (e) => {
                this.filters.actionType = e.target.value;
                this.applyFilters();
            });
        }
        
        // User filter
        const userFilter = document.getElementById('auditUser');
        if (userFilter) {
            userFilter.addEventListener('change', (e) => {
                this.filters.user = e.target.value;
                this.applyFilters();
            });
        }
        
        // Institution filter
        const institutionFilter = document.getElementById('auditInstitution');
        if (institutionFilter) {
            institutionFilter.addEventListener('change', (e) => {
                this.filters.institution = e.target.value;
                this.applyFilters();
            });
        }
        
        // Clear filters button
        const clearFiltersBtn = document.getElementById('clearAuditFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                this.clearFilters();
            });
        }
    }

    /**
     * Setup search functionality
     */
    setupSearchFunctionality() {
        // Advanced search
        const advancedSearchToggle = document.getElementById('advancedSearchToggle');
        if (advancedSearchToggle) {
            advancedSearchToggle.addEventListener('click', () => {
                this.toggleAdvancedSearch();
            });
        }
        
        // Quick filters
        const quickFilters = document.querySelectorAll('.quick-filter');
        quickFilters.forEach(filter => {
            filter.addEventListener('click', (e) => {
                e.preventDefault();
                this.applyQuickFilter(filter.dataset.filter);
            });
        });
    }

    /**
     * Toggle advanced search panel
     */
    toggleAdvancedSearch() {
        const advancedPanel = document.getElementById('advancedSearchPanel');
        const toggleIcon = document.getElementById('advancedSearchIcon');
        
        if (advancedPanel && toggleIcon) {
            const isVisible = advancedPanel.style.display !== 'none';
            advancedPanel.style.display = isVisible ? 'none' : 'block';
            toggleIcon.className = isVisible ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
        }
    }

    /**
     * Apply quick filter
     */
    applyQuickFilter(filterType) {
        const today = new Date();
        
        switch (filterType) {
            case 'today':
                this.filters.dateRange = this.formatDate(today);
                break;
            case 'week':
                const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                this.filters.dateRange = `${this.formatDate(weekAgo)} - ${this.formatDate(today)}`;
                break;
            case 'month':
                const monthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
                this.filters.dateRange = `${this.formatDate(monthAgo)} - ${this.formatDate(today)}`;
                break;
        }
        
        // Update UI
        const dateRangeFilter = document.getElementById('auditDateRange');
        if (dateRangeFilter) {
            dateRangeFilter.value = this.filters.dateRange;
        }
        
        this.applyFilters();
    }

    /**
     * Format date for filter
     */
    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    /**
     * Load audit data via AJAX
     */
    async loadAuditData() {
        try {
            const response = await fetch('/sakip/api/audit-logs');
            const result = await response.json();
            
            if (result.success) {
                this.auditData = result.data;
                this.filteredData = [...this.auditData];
                this.renderAuditTable();
                this.updateFilterOptions();
            } else {
                this.showNotification('Gagal memuat data audit', 'error');
            }
            
        } catch (error) {
            console.error('Error loading audit data:', error);
            this.showNotification('Terjadi kesalahan saat memuat data', 'error');
        }
    }

    /**
     * Update filter options based on data
     */
    updateFilterOptions() {
        // Extract unique values for dropdown filters
        const actionTypes = [...new Set(this.auditData.map(item => item.action_type))];
        const users = [...new Set(this.auditData.map(item => item.user_name))];
        const institutions = [...new Set(this.auditData.map(item => item.institution_name))];
        
        // Update action type filter
        this.updateSelectOptions('auditActionType', actionTypes);
        
        // Update user filter
        this.updateSelectOptions('auditUser', users);
        
        // Update institution filter
        this.updateSelectOptions('auditInstitution', institutions);
    }

    /**
     * Update select element options
     */
    updateSelectOptions(selectId, options) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        // Clear existing options except the first one (placeholder)
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        // Add new options
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option;
            optionElement.textContent = option;
            select.appendChild(optionElement);
        });
    }

    /**
     * Apply filters to audit data
     */
    applyFilters() {
        this.filteredData = this.auditData.filter(item => {
            // Search filter
            if (this.filters.search) {
                const searchTerm = this.filters.search.toLowerCase();
                const searchableText = `${item.user_name} ${item.action} ${item.description}`.toLowerCase();
                if (!searchableText.includes(searchTerm)) {
                    return false;
                }
            }
            
            // Date range filter
            if (this.filters.dateRange) {
                const itemDate = new Date(item.created_at);
                if (!this.isDateInRange(itemDate, this.filters.dateRange)) {
                    return false;
                }
            }
            
            // Action type filter
            if (this.filters.actionType && item.action_type !== this.filters.actionType) {
                return false;
            }
            
            // User filter
            if (this.filters.user && item.user_name !== this.filters.user) {
                return false;
            }
            
            // Institution filter
            if (this.filters.institution && item.institution_name !== this.filters.institution) {
                return false;
            }
            
            return true;
        });
        
        this.currentPage = 1; // Reset to first page
        this.renderAuditTable();
        this.updateFilterSummary();
    }

    /**
     * Check if date is in range
     */
    isDateInRange(date, dateRange) {
        if (!dateRange.includes(' - ')) {
            // Single date
            return this.formatDate(date) === dateRange;
        }
        
        // Date range
        const [startDate, endDate] = dateRange.split(' - ');
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        return date >= start && date <= end;
    }

    /**
     * Clear all filters
     */
    clearFilters() {
        this.filters = {
            search: '',
            dateRange: '',
            actionType: '',
            user: '',
            institution: ''
        };
        
        // Clear UI elements
        const filterElements = document.querySelectorAll('#auditSearch, #auditDateRange, #auditActionType, #auditUser, #auditInstitution');
        filterElements.forEach(element => {
            element.value = '';
        });
        
        this.applyFilters();
    }

    /**
     * Update filter summary
     */
    updateFilterSummary() {
        const summaryElement = document.getElementById('filterSummary');
        if (!summaryElement) return;
        
        const activeFilters = Object.entries(this.filters).filter(([key, value]) => value !== '');
        
        if (activeFilters.length === 0) {
            summaryElement.style.display = 'none';
            return;
        }
        
        summaryElement.style.display = 'block';
        summaryElement.innerHTML = `
            <div class="filter-summary">
                <span class="filter-count">${this.filteredData.length} hasil</span>
                <span class="filter-label">Filter aktif:</span>
                ${activeFilters.map(([key, value]) => `<span class="filter-tag">${this.getFilterLabel(key)}: ${value}</span>`).join('')}
            </div>
        `;
    }

    /**
     * Get filter label
     */
    getFilterLabel(filterKey) {
        const labels = {
            search: 'Pencarian',
            dateRange: 'Tanggal',
            actionType: 'Tindakan',
            user: 'Pengguna',
            institution: 'Instansi'
        };
        
        return labels[filterKey] || filterKey;
    }

    /**
     * Render audit table
     */
    renderAuditTable() {
        const tableBody = document.getElementById('auditTableBody');
        if (!tableBody) return;
        
        // Clear existing rows
        tableBody.innerHTML = '';
        
        // Calculate pagination
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageData = this.filteredData.slice(startIndex, endIndex);
        
        // Render rows
        pageData.forEach(audit => {
            const row = this.createAuditRow(audit);
            tableBody.appendChild(row);
        });
        
        // Update pagination
        this.updatePagination();
        
        // Update results count
        this.updateResultsCount();
    }

    /**
     * Create audit row
     */
    createAuditRow(audit) {
        const row = document.createElement('tr');
        row.className = 'audit-row';
        row.dataset.auditId = audit.id;
        
        const actionIcon = this.getActionIcon(audit.action_type);
        const actionClass = this.getActionClass(audit.action_type);
        const timeAgo = this.getTimeAgo(audit.created_at);
        
        row.innerHTML = `
            <td class="text-center">
                <i class="${actionIcon} ${actionClass}"></i>
            </td>
            <td>
                <div class="audit-action">${audit.action}</div>
                <div class="audit-description text-muted">${audit.description}</div>
            </td>
            <td>${audit.user_name}</td>
            <td>${audit.institution_name}</td>
            <td>${timeAgo}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary view-detail-btn" data-audit-id="${audit.id}">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        
        // Add click handler for detail view
        const viewButton = row.querySelector('.view-detail-btn');
        viewButton.addEventListener('click', () => {
            this.showAuditDetail(audit.id);
        });
        
        return row;
    }

    /**
     * Get action icon
     */
    getActionIcon(actionType) {
        const icons = {
            'create': 'fas fa-plus-circle',
            'update': 'fas fa-edit',
            'delete': 'fas fa-trash',
            'view': 'fas fa-eye',
            'export': 'fas fa-download',
            'import': 'fas fa-upload',
            'login': 'fas fa-sign-in-alt',
            'logout': 'fas fa-sign-out-alt',
            'approve': 'fas fa-check-circle',
            'reject': 'fas fa-times-circle'
        };
        
        return icons[actionType] || 'fas fa-info-circle';
    }

    /**
     * Get action class
     */
    getActionClass(actionType) {
        const classes = {
            'create': 'text-success',
            'update': 'text-warning',
            'delete': 'text-danger',
            'view': 'text-info',
            'export': 'text-primary',
            'import': 'text-primary',
            'login': 'text-success',
            'logout': 'text-muted',
            'approve': 'text-success',
            'reject': 'text-danger'
        };
        
        return classes[actionType] || 'text-secondary';
    }

    /**
     * Get time ago
     */
    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'Baru saja';
        if (diffMins < 60) return `${diffMins} menit lalu`;
        if (diffHours < 24) return `${diffHours} jam lalu`;
        if (diffDays < 30) return `${diffDays} hari lalu`;
        
        return date.toLocaleDateString('id-ID');
    }

    /**
     * Setup pagination
     */
    setupPagination() {
        const paginationContainer = document.getElementById('auditPagination');
        if (!paginationContainer) return;
        
        // Pagination will be updated in updatePagination method
    }

    /**
     * Update pagination
     */
    updatePagination() {
        const paginationContainer = document.getElementById('auditPagination');
        if (!paginationContainer) return;
        
        const totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
        
        if (totalPages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        }
        
        paginationContainer.style.display = 'flex';
        
        // Generate pagination HTML
        let paginationHTML = '';
        
        // Previous button
        paginationHTML += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(totalPages, this.currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Next button
        paginationHTML += `
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationContainer.innerHTML = paginationHTML;
        
        // Add click handlers
        const pageLinks = paginationContainer.querySelectorAll('.page-link');
        pageLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.dataset.page);
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    this.renderAuditTable();
                }
            });
        });
    }

    /**
     * Update results count
     */
    updateResultsCount() {
        const countElement = document.getElementById('auditResultsCount');
        if (!countElement) return;
        
        const startIndex = (this.currentPage - 1) * this.itemsPerPage + 1;
        const endIndex = Math.min(this.currentPage * this.itemsPerPage, this.filteredData.length);
        
        countElement.textContent = `Menampilkan ${startIndex} - ${endIndex} dari ${this.filteredData.length} hasil`;
    }

    /**
     * Setup detail view
     */
    setupDetailView() {
        // Detail modal
        const detailModal = document.getElementById('auditDetailModal');
        if (detailModal) {
            detailModal.addEventListener('hidden.bs.modal', () => {
                this.clearDetailView();
            });
        }
        
        // Close button
        const closeButton = document.getElementById('closeAuditDetail');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                this.hideAuditDetail();
            });
        }
    }

    /**
     * Show audit detail
     */
    async showAuditDetail(auditId) {
        const audit = this.auditData.find(item => item.id === parseInt(auditId));
        if (!audit) return;
        
        this.populateDetailView(audit);
        this.showDetailModal();
    }

    /**
     * Populate detail view
     */
    populateDetailView(audit) {
        // Basic information
        const detailContent = document.getElementById('auditDetailContent');
        if (detailContent) {
            detailContent.innerHTML = `
                <div class="audit-detail-section">
                    <h5>Informasi Umum</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Aksi:</strong></td>
                            <td>${audit.action}</td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td>${audit.description}</td>
                        </tr>
                        <tr>
                            <td><strong>Pengguna:</strong></td>
                            <td>${audit.user_name} (${audit.user_email})</td>
                        </tr>
                        <tr>
                            <td><strong>Instansi:</strong></td>
                            <td>${audit.institution_name}</td>
                        </tr>
                        <tr>
                            <td><strong>Waktu:</strong></td>
                            <td>${new Date(audit.created_at).toLocaleString('id-ID')}</td>
                        </tr>
                        <tr>
                            <td><strong>Alamat IP:</strong></td>
                            <td>${audit.ip_address || '-'}</td>
                        </tr>
                        <tr>
                            <td><strong>Perangkat:</strong></td>
                            <td>${audit.user_agent || '-'}</td>
                        </tr>
                    </table>
                </div>
                
                ${audit.old_values || audit.new_values ? `
                    <div class="audit-detail-section">
                        <h5>Perubahan Data</h5>
                        ${this.generateChangeDetails(audit)}
                    </div>
                ` : ''}
                
                ${audit.metadata ? `
                    <div class="audit-detail-section">
                        <h5>Metadata Tambahan</h5>
                        <pre class="audit-metadata">${JSON.stringify(audit.metadata, null, 2)}</pre>
                    </div>
                ` : ''}
            `;
        }
    }

    /**
     * Generate change details
     */
    generateChangeDetails(audit) {
        let html = '<div class="change-details">';
        
        if (audit.old_values && audit.new_values) {
            // Compare old and new values
            const oldKeys = Object.keys(audit.old_values);
            const newKeys = Object.keys(audit.new_values);
            const allKeys = [...new Set([...oldKeys, ...newKeys])];
            
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Field</th><th>Nilai Lama</th><th>Nilai Baru</th></tr></thead>';
            html += '<tbody>';
            
            allKeys.forEach(key => {
                const oldValue = audit.old_values[key] || '-';
                const newValue = audit.new_values[key] || '-';
                
                if (oldValue !== newValue) {
                    html += `
                        <tr>
                            <td><strong>${this.formatFieldName(key)}</strong></td>
                            <td class="old-value">${this.formatValue(oldValue)}</td>
                            <td class="new-value">${this.formatValue(newValue)}</td>
                        </tr>
                    `;
                }
            });
            
            html += '</tbody></table>';
        } else if (audit.new_values) {
            // Show new values only (create action)
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Field</th><th>Nilai</th></tr></thead>';
            html += '<tbody>';
            
            Object.entries(audit.new_values).forEach(([key, value]) => {
                html += `
                    <tr>
                        <td><strong>${this.formatFieldName(key)}</strong></td>
                        <td>${this.formatValue(value)}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
        }
        
        html += '</div>';
        return html;
    }

    /**
     * Format field name
     */
    formatFieldName(fieldName) {
        // Convert snake_case to readable format
        return fieldName
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    /**
     * Format value
     */
    formatValue(value) {
        if (value === null || value === undefined) return '-';
        if (value === true) return 'Ya';
        if (value === false) return 'Tidak';
        if (typeof value === 'object') return JSON.stringify(value);
        return value;
    }

    /**
     * Show detail modal
     */
    showDetailModal() {
        const detailModal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
        detailModal.show();
    }

    /**
     * Hide audit detail
     */
    hideAuditDetail() {
        const detailModal = bootstrap.Modal.getInstance(document.getElementById('auditDetailModal'));
        if (detailModal) {
            detailModal.hide();
        }
    }

    /**
     * Clear detail view
     */
    clearDetailView() {
        const detailContent = document.getElementById('auditDetailContent');
        if (detailContent) {
            detailContent.innerHTML = '';
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Implementation similar to other modules
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

// Initialize audit trail when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.sakipAuditTrail = new SakipAuditTrail();
});