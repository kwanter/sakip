/**
 * SAKIP Dashboard JavaScript Module
 * Handles dashboard charts, KPI widgets, and real-time updates
 */

class SakipDashboard {
    constructor() {
        this.charts = {};
        this.updateInterval = null;
        this.init();
    }

    /**
     * Initialize dashboard functionality
     */
    init() {
        this.initializeCharts();
        this.setupEventListeners();
        this.startRealTimeUpdates();
        this.initializeKPIWidgets();
    }

    /**
     * Initialize all dashboard charts
     */
    initializeCharts() {
        this.initializeAchievementChart();
        this.initializeTrendChart();
        this.initializeCategoryChart();
        this.initializeComplianceChart();
    }

    /**
     * Initialize achievement chart
     */
    initializeAchievementChart() {
        const ctx = document.getElementById('achievementChart');
        if (!ctx) return;

        const data = JSON.parse(ctx.dataset.chartData || '{}');
        
        this.charts.achievement = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels || ['Q1', 'Q2', 'Q3', 'Q4'],
                datasets: [{
                    label: 'Pencapaian (%)',
                    data: data.values || [0, 0, 0, 0],
                    backgroundColor: '#1e40af',
                    borderColor: '#1e3a8a',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Pencapaian Kinerja Triwulan'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize trend chart
     */
    initializeTrendChart() {
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;

        const data = JSON.parse(ctx.dataset.chartData || '{}');
        
        this.charts.trend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Target',
                    data: data.targetValues || [0, 0, 0, 0, 0, 0],
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Realisasi',
                    data: data.actualValues || [0, 0, 0, 0, 0, 0],
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tren Kinerja 6 Bulan Terakhir'
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

    /**
     * Initialize category chart
     */
    initializeCategoryChart() {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;

        const data = JSON.parse(ctx.dataset.chartData || '{}');
        
        this.charts.category = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels || ['Input', 'Output', 'Outcome', 'Impact'],
                datasets: [{
                    data: data.values || [25, 25, 25, 25],
                    backgroundColor: [
                        '#1e40af',
                        '#059669',
                        '#d97706',
                        '#dc2626'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribusi Indikator berdasarkan Kategori'
                    }
                }
            }
        });
    }

    /**
     * Initialize compliance chart
     */
    initializeComplianceChart() {
        const ctx = document.getElementById('complianceChart');
        if (!ctx) return;

        const data = JSON.parse(ctx.dataset.chartData || '{}');
        
        this.charts.compliance = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: data.labels || ['Kelengkapan Data', 'Ketepatan Waktu', 'Kualitas Data', 'Dokumentasi', 'Validasi'],
                datasets: [{
                    label: 'Kepatuhan',
                    data: data.values || [80, 75, 90, 85, 88],
                    borderColor: '#1e40af',
                    backgroundColor: 'rgba(30, 64, 175, 0.2)',
                    pointBackgroundColor: '#1e40af'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Status Kepatuhan SAKIP'
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Period filter change
        document.getElementById('periodFilter')?.addEventListener('change', (e) => {
            this.updateDashboardData(e.target.value);
        });

        // Institution filter change
        document.getElementById('institutionFilter')?.addEventListener('change', (e) => {
            this.updateDashboardData(null, e.target.value);
        });

        // Refresh button
        document.getElementById('refreshDashboard')?.addEventListener('click', () => {
            this.refreshDashboard();
        });
    }

    /**
     * Initialize KPI widgets
     */
    initializeKPIWidgets() {
        this.updateKPIWidgets();
        
        // Set up periodic updates
        setInterval(() => {
            this.updateKPIWidgets();
        }, 30000); // Update every 30 seconds
    }

    /**
     * Update KPI widgets with animation
     */
    updateKPIWidgets() {
        const widgets = document.querySelectorAll('.kpi-widget');
        
        widgets.forEach(widget => {
            const valueElement = widget.querySelector('.kpi-value');
            const targetValue = parseFloat(valueElement.dataset.targetValue || '0');
            const currentValue = parseFloat(valueElement.textContent) || 0;
            
            this.animateValue(valueElement, currentValue, targetValue, 1000);
        });
    }

    /**
     * Animate number value changes
     */
    animateValue(element, start, end, duration) {
        const startTime = performance.now();
        const endTime = startTime + duration;
        
        const animate = (currentTime) => {
            if (currentTime >= endTime) {
                element.textContent = this.formatNumber(end);
                return;
            }
            
            const timeFraction = (currentTime - startTime) / duration;
            const value = start + (end - start) * timeFraction;
            
            element.textContent = this.formatNumber(value);
            requestAnimationFrame(animate);
        };
        
        requestAnimationFrame(animate);
    }

    /**
     * Format number for display
     */
    formatNumber(value) {
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(1) + 'K';
        } else {
            return Math.round(value).toString();
        }
    }

    /**
     * Update dashboard data
     */
    async updateDashboardData(period = null, institution = null) {
        try {
            const params = new URLSearchParams();
            if (period) params.append('period', period);
            if (institution) params.append('institution', institution);
            
            const response = await fetch(`/sakip/api/dashboard-data?${params}`);
            const data = await response.json();
            
            this.updateCharts(data);
            this.updateAlerts(data.alerts);
            
        } catch (error) {
            console.error('Error updating dashboard data:', error);
            this.showNotification('Error memperbarui data dashboard', 'error');
        }
    }

    /**
     * Update charts with new data
     */
    updateCharts(data) {
        if (data.achievementData && this.charts.achievement) {
            this.charts.achievement.data.datasets[0].data = data.achievementData;
            this.charts.achievement.update();
        }
        
        if (data.trendData && this.charts.trend) {
            this.charts.trend.data.datasets[0].data = data.trendData.target;
            this.charts.trend.data.datasets[1].data = data.trendData.actual;
            this.charts.trend.update();
        }
        
        if (data.categoryData && this.charts.category) {
            this.charts.category.data.datasets[0].data = data.categoryData;
            this.charts.category.update();
        }
        
        if (data.complianceData && this.charts.compliance) {
            this.charts.compliance.data.datasets[0].data = data.complianceData;
            this.charts.compliance.update();
        }
    }

    /**
     * Update alerts section
     */
    updateAlerts(alerts) {
        const alertsContainer = document.getElementById('dashboardAlerts');
        if (!alertsContainer) return;
        
        alertsContainer.innerHTML = alerts.map(alert => `
            <div class="alert alert-${alert.type} alert-dismissible fade show" role="alert">
                <strong>${alert.title}</strong> ${alert.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `).join('');
    }

    /**
     * Refresh dashboard
     */
    refreshDashboard() {
        const refreshButton = document.getElementById('refreshDashboard');
        const originalText = refreshButton.innerHTML;
        
        refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
        refreshButton.disabled = true;
        
        this.updateDashboardData()
            .then(() => {
                refreshButton.innerHTML = originalText;
                refreshButton.disabled = false;
                this.showNotification('Dashboard berhasil diperbarui', 'success');
            })
            .catch(() => {
                refreshButton.innerHTML = originalText;
                refreshButton.disabled = false;
            });
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        // Update every 5 minutes
        this.updateInterval = setInterval(() => {
            this.updateDashboardData();
        }, 300000);
    }

    /**
     * Stop real-time updates
     */
    stopRealTimeUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Use Laravel's notification system or custom implementation
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    /**
     * Cleanup method
     */
    destroy() {
        this.stopRealTimeUpdates();
        
        // Destroy all charts
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        
        this.charts = {};
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.sakipDashboard = new SakipDashboard();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.sakipDashboard) {
        window.sakipDashboard.destroy();
    }
});