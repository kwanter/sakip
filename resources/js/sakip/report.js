/**
 * SAKIP Report Generation JavaScript Module
 * Handles report generation, preview, and export functionality
 */

class SakipReport {
    constructor() {
        this.reportData = {};
        this.chartInstances = {};
        this.init();
    }

    /**
     * Initialize report functionality
     */
    init() {
        this.initializeReportForms();
        this.setupChartGeneration();
        this.setupExportHandlers();
        this.setupPreviewFunctionality();
        this.setupTemplateSystem();
    }

    /**
     * Initialize report forms
     */
    initializeReportForms() {
        const reportTypeSelect = document.getElementById('reportType');
        if (reportTypeSelect) {
            reportTypeSelect.addEventListener('change', (e) => {
                this.updateReportForm(e.target.value);
            });
        }
        
        const generateButton = document.getElementById('generateReport');
        if (generateButton) {
            generateButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.generateReport();
            });
        }
        
        // Setup date range picker
        this.initializeDateRangePicker();
        
        // Setup institution selector
        this.initializeInstitutionSelector();
    }

    /**
     * Update report form based on type
     */
    updateReportForm(reportType) {
        const formFields = document.getElementById('reportFormFields');
        if (!formFields) return;
        
        // Hide all specific fields
        const specificFields = formFields.querySelectorAll('.report-specific-field');
        specificFields.forEach(field => field.style.display = 'none');
        
        // Show relevant fields based on report type
        const relevantFields = formFields.querySelectorAll(`[data-report-type*="${reportType}"]`);
        relevantFields.forEach(field => field.style.display = 'block');
        
        // Update form title and description
        this.updateFormDescription(reportType);
    }

    /**
     * Update form description based on report type
     */
    updateFormDescription(reportType) {
        const descriptions = {
            'performance': 'Laporan kinerja berdasarkan indikator kinja utama (IKU) dan target yang telah ditetapkan',
            'compliance': 'Laporan kepatuhan terhadap standar dan regulasi yang berlaku',
            'achievement': 'Laporan pencapaian kinerja periode tertentu',
            'trend': 'Laporan tren kinerja dalam kurun waktu tertentu',
            'comparison': 'Laporan perbandingan kinerja antar unit/instansi',
            'audit': 'Laporan hasil audit dan temuan yang perlu ditindaklanjuti'
        };
        
        const descriptionElement = document.getElementById('reportTypeDescription');
        if (descriptionElement) {
            descriptionElement.textContent = descriptions[reportType] || '';
        }
    }

    /**
     * Initialize date range picker
     */
    initializeDateRangePicker() {
        const dateRangeInput = document.getElementById('dateRange');
        if (!dateRangeInput) return;
        
        // Initialize flatpickr or similar date picker
        dateRangeInput.addEventListener('focus', () => {
            // Implementation for date range picker
            console.log('Date range picker should be initialized here');
        });
    }

    /**
     * Initialize institution selector
     */
    initializeInstitutionSelector() {
        const institutionSelect = document.getElementById('institutionSelect');
        if (!institutionSelect) return;
        
        // Setup AJAX loading for institutions
        institutionSelect.addEventListener('focus', () => {
            this.loadInstitutions(institutionSelect);
        });
    }

    /**
     * Load institutions via AJAX
     */
    async loadInstitutions(selectElement) {
        if (selectElement.dataset.loaded === 'true') return;
        
        try {
            const response = await fetch('/sakip/api/institutions');
            const data = await response.json();
            
            data.institutions.forEach(institution => {
                const option = document.createElement('option');
                option.value = institution.id;
                option.textContent = institution.name;
                selectElement.appendChild(option);
            });
            
            selectElement.dataset.loaded = 'true';
            
        } catch (error) {
            console.error('Error loading institutions:', error);
        }
    }

    /**
     * Generate report
     */
    async generateReport() {
        const formData = this.collectReportData();
        
        if (!this.validateReportData(formData)) {
            this.showNotification('Mohon lengkapi data laporan', 'error');
            return;
        }
        
        // Show loading state
        this.showLoadingState();
        
        try {
            const response = await fetch('/sakip/reports/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.reportData = result.data;
                this.displayReport(result.data);
                this.showNotification('Laporan berhasil dibuat', 'success');
            } else {
                this.showNotification(result.message || 'Gagal membuat laporan', 'error');
            }
            
        } catch (error) {
            console.error('Report generation error:', error);
            this.showNotification('Terjadi kesalahan saat membuat laporan', 'error');
        } finally {
            this.hideLoadingState();
        }
    }

    /**
     * Collect report data from form
     */
    collectReportData() {
        const form = document.getElementById('reportForm');
        if (!form) return {};
        
        const formData = new FormData(form);
        const data = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // Add additional data
        data.include_charts = document.getElementById('includeCharts')?.checked || false;
        data.include_recommendations = document.getElementById('includeRecommendations')?.checked || false;
        data.format = document.querySelector('input[name="format"]:checked')?.value || 'pdf';
        
        return data;
    }

    /**
     * Validate report data
     */
    validateReportData(data) {
        const requiredFields = ['report_type', 'title'];
        
        for (let field of requiredFields) {
            if (!data[field] || data[field].trim() === '') {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Display generated report
     */
    displayReport(data) {
        const reportPreview = document.getElementById('reportPreview');
        if (!reportPreview) return;
        
        // Show preview section
        reportPreview.style.display = 'block';
        
        // Update report header
        this.updateReportHeader(data);
        
        // Display report content
        this.displayReportContent(data);
        
        // Generate charts if requested
        if (data.include_charts) {
            this.generateCharts(data);
        }
        
        // Scroll to preview
        reportPreview.scrollIntoView({ behavior: 'smooth' });
    }

    /**
     * Update report header
     */
    updateReportHeader(data) {
        const titleElement = document.getElementById('reportTitle');
        const subtitleElement = document.getElementById('reportSubtitle');
        const dateElement = document.getElementById('reportDate');
        
        if (titleElement) {
            titleElement.textContent = data.title;
        }
        
        if (subtitleElement) {
            subtitleElement.textContent = data.subtitle || '';
        }
        
        if (dateElement) {
            dateElement.textContent = new Date().toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }

    /**
     * Display report content
     */
    displayReportContent(data) {
        const contentElement = document.getElementById('reportContent');
        if (!contentElement) return;
        
        // Generate content based on report type
        let content = '';
        
        switch (data.report_type) {
            case 'performance':
                content = this.generatePerformanceContent(data);
                break;
            case 'compliance':
                content = this.generateComplianceContent(data);
                break;
            case 'achievement':
                content = this.generateAchievementContent(data);
                break;
            case 'trend':
                content = this.generateTrendContent(data);
                break;
            case 'comparison':
                content = this.generateComparisonContent(data);
                break;
            case 'audit':
                content = this.generateAuditContent(data);
                break;
            default:
                content = '<p>Content not available</p>';
        }
        
        contentElement.innerHTML = content;
    }

    /**
     * Generate performance report content
     */
    generatePerformanceContent(data) {
        let content = `
            <div class="report-section">
                <h3>Ringkasan Kinerja</h3>
                <div class="performance-summary">
                    <div class="summary-item">
                        <h4>Total Indikator</h4>
                        <span class="summary-value">${data.total_indicators || 0}</span>
                    </div>
                    <div class="summary-item">
                        <h4>Tercapai</h4>
                        <span class="summary-value text-success">${data.achieved_indicators || 0}</span>
                    </div>
                    <div class="summary-item">
                        <h4>Belum Tercapai</h4>
                        <span class="summary-value text-warning">${data.unachieved_indicators || 0}</span>
                    </div>
                </div>
            </div>
        `;
        
        // Add detailed indicators table
        if (data.indicators && data.indicators.length > 0) {
            content += this.generateIndicatorsTable(data.indicators);
        }
        
        return content;
    }

    /**
     * Generate indicators table
     */
    generateIndicatorsTable(indicators) {
        let table = `
            <div class="report-section">
                <h3>Detail Indikator</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Indikator</th>
                            <th>Target</th>
                            <th>Realisasi</th>
                            <th>Pencapaian</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        indicators.forEach(indicator => {
            const achievement = indicator.achievement || 0;
            const statusClass = achievement >= 100 ? 'text-success' : achievement >= 80 ? 'text-warning' : 'text-danger';
            const statusText = achievement >= 100 ? 'Tercapai' : achievement >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai';
            
            table += `
                <tr>
                    <td>${indicator.code}</td>
                    <td>${indicator.name}</td>
                    <td>${indicator.target}</td>
                    <td>${indicator.actual || 0}</td>
                    <td>${achievement.toFixed(2)}%</td>
                    <td class="${statusClass}">${statusText}</td>
                </tr>
            `;
        });
        
        table += `
                    </tbody>
                </table>
            </div>
        `;
        
        return table;
    }

    /**
     * Setup chart generation
     */
    setupChartGeneration() {
        // Chart generation will be handled when report is displayed
    }

    /**
     * Generate charts
     */
    generateCharts(data) {
        const chartsContainer = document.getElementById('reportCharts');
        if (!chartsContainer) return;
        
        chartsContainer.style.display = 'block';
        
        // Generate different chart types based on report type
        switch (data.report_type) {
            case 'performance':
                this.generatePerformanceCharts(data);
                break;
            case 'trend':
                this.generateTrendCharts(data);
                break;
            case 'comparison':
                this.generateComparisonCharts(data);
                break;
        }
    }

    /**
     * Generate performance charts
     */
    generatePerformanceCharts(data) {
        // Achievement by category chart
        if (data.category_data) {
            this.createPieChart('chartByCategory', data.category_data, 'Pencapaian Berdasarkan Kategori');
        }
        
        // Trend chart
        if (data.trend_data) {
            this.createLineChart('chartTrend', data.trend_data, 'Tren Kinerja');
        }
    }

    /**
     * Create pie chart
     */
    createPieChart(containerId, data, title) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // Simple pie chart implementation using Canvas
        const canvas = document.createElement('canvas');
        canvas.width = 400;
        canvas.height = 300;
        container.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 100;
        
        const total = data.reduce((sum, item) => sum + item.value, 0);
        let currentAngle = -Math.PI / 2;
        
        const colors = ['#4CAF50', '#FFC107', '#F44336', '#2196F3', '#9C27B0'];
        
        data.forEach((item, index) => {
            const sliceAngle = (item.value / total) * 2 * Math.PI;
            
            // Draw slice
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
            ctx.lineTo(centerX, centerY);
            ctx.fillStyle = colors[index % colors.length];
            ctx.fill();
            
            // Draw label
            const labelAngle = currentAngle + sliceAngle / 2;
            const labelX = centerX + Math.cos(labelAngle) * (radius + 20);
            const labelY = centerY + Math.sin(labelAngle) * (radius + 20);
            
            ctx.fillStyle = '#333';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(`${item.label}: ${item.value}`, labelX, labelY);
            
            currentAngle += sliceAngle;
        });
        
        // Add title
        ctx.fillStyle = '#333';
        ctx.font = 'bold 14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(title, centerX, 20);
    }

    /**
     * Create line chart
     */
    createLineChart(containerId, data, title) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const canvas = document.createElement('canvas');
        canvas.width = 600;
        canvas.height = 300;
        container.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        const padding = 50;
        const chartWidth = canvas.width - 2 * padding;
        const chartHeight = canvas.height - 2 * padding;
        
        // Find max value
        const maxValue = Math.max(...data.map(item => item.value));
        
        // Draw axes
        ctx.beginPath();
        ctx.moveTo(padding, padding);
        ctx.lineTo(padding, canvas.height - padding);
        ctx.lineTo(canvas.width - padding, canvas.height - padding);
        ctx.strokeStyle = '#333';
        ctx.stroke();
        
        // Draw data points and lines
        ctx.beginPath();
        data.forEach((item, index) => {
            const x = padding + (index / (data.length - 1)) * chartWidth;
            const y = canvas.height - padding - (item.value / maxValue) * chartHeight;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
            
            // Draw point
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fillStyle = '#4CAF50';
            ctx.fill();
            
            // Draw label
            ctx.fillStyle = '#333';
            ctx.font = '10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(item.label, x, canvas.height - padding + 20);
        });
        
        ctx.strokeStyle = '#4CAF50';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        // Add title
        ctx.fillStyle = '#333';
        ctx.font = 'bold 14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(title, canvas.width / 2, 20);
    }

    /**
     * Setup export handlers
     */
    setupExportHandlers() {
        const exportButtons = document.querySelectorAll('.export-report-btn');
        
        exportButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const format = button.dataset.format;
                this.exportReport(format);
            });
        });
    }

    /**
     * Export report
     */
    async exportReport(format) {
        if (!this.reportData || Object.keys(this.reportData).length === 0) {
            this.showNotification('Silakan buat laporan terlebih dahulu', 'error');
            return;
        }
        
        try {
            const response = await fetch(`/sakip/reports/export/${this.reportData.id}?format=${format}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `laporan-${this.reportData.title.toLowerCase().replace(/\s+/g, '-')}.${format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                this.showNotification('Laporan berhasil diunduh', 'success');
            } else {
                this.showNotification('Gagal mengunduh laporan', 'error');
            }
            
        } catch (error) {
            console.error('Export error:', error);
            this.showNotification('Terjadi kesalahan saat mengunduh laporan', 'error');
        }
    }

    /**
     * Setup preview functionality
     */
    setupPreviewFunctionality() {
        const previewButton = document.getElementById('previewReport');
        if (previewButton) {
            previewButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.previewReport();
            });
        }
    }

    /**
     * Preview report
     */
    previewReport() {
        const reportPreview = document.getElementById('reportPreview');
        if (!reportPreview) return;
        
        // Open preview in new window
        const previewWindow = window.open('', '_blank', 'width=1024,height=768');
        
        // Generate preview HTML
        const previewHTML = this.generatePreviewHTML();
        
        previewWindow.document.write(previewHTML);
        previewWindow.document.close();
    }

    /**
     * Generate preview HTML
     */
    generatePreviewHTML() {
        const reportContent = document.getElementById('reportContent').innerHTML;
        
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Preview Laporan SAKIP</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .report-header { text-align: center; margin-bottom: 30px; }
                    .report-title { font-size: 24px; font-weight: bold; }
                    .report-subtitle { font-size: 18px; color: #666; }
                    .report-date { font-size: 14px; color: #999; }
                    .report-section { margin: 20px 0; }
                    .report-table { width: 100%; border-collapse: collapse; }
                    .report-table th, .report-table td { border: 1px solid #ddd; padding: 8px; }
                    .report-table th { background-color: #f5f5f5; }
                    .text-success { color: #28a745; }
                    .text-warning { color: #ffc107; }
                    .text-danger { color: #dc3545; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                <div class="report-header">
                    <div class="report-title">${document.getElementById('reportTitle')?.textContent || ''}</div>
                    <div class="report-subtitle">${document.getElementById('reportSubtitle')?.textContent || ''}</div>
                    <div class="report-date">${document.getElementById('reportDate')?.textContent || ''}</div>
                </div>
                ${reportContent}
            </body>
            </html>
        `;
    }

    /**
     * Setup template system
     */
    setupTemplateSystem() {
        const templateSelect = document.getElementById('reportTemplate');
        if (templateSelect) {
            templateSelect.addEventListener('change', (e) => {
                this.loadTemplate(e.target.value);
            });
        }
    }

    /**
     * Load report template
     */
    loadTemplate(templateId) {
        if (!templateId) return;
        
        // Load template data via AJAX
        fetch(`/sakip/reports/templates/${templateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.applyTemplate(data.template);
                }
            })
            .catch(error => {
                console.error('Template loading error:', error);
            });
    }

    /**
     * Apply template to form
     */
    applyTemplate(template) {
        // Fill form fields with template data
        const fields = ['title', 'subtitle', 'report_type', 'date_range'];
        
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element && template[field]) {
                element.value = template[field];
            }
        });
        
        // Update form based on report type
        if (template.report_type) {
            this.updateReportForm(template.report_type);
        }
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        const loadingElement = document.getElementById('reportLoading');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        
        const generateButton = document.getElementById('generateReport');
        if (generateButton) {
            generateButton.disabled = true;
            generateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuat Laporan...';
        }
    }

    /**
     * Hide loading state
     */
    hideLoadingState() {
        const loadingElement = document.getElementById('reportLoading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        const generateButton = document.getElementById('generateReport');
        if (generateButton) {
            generateButton.disabled = false;
            generateButton.innerHTML = '<i class="fas fa-chart-bar"></i> Buat Laporan';
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

// Initialize report functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.sakipReport = new SakipReport();
});