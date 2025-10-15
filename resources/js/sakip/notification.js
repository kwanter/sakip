/**
 * SAKIP Notification System JavaScript Module
 * Handles real-time notifications, alerts, and user communication
 */

class SakipNotification {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.wsConnection = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.init();
    }

    /**
     * Initialize notification system
     */
    init() {
        this.initializeUI();
        this.setupWebSocket();
        this.loadNotifications();
        this.setupEventHandlers();
        this.startPolling();
    }

    /**
     * Initialize notification UI
     */
    initializeUI() {
        // Create notification dropdown if not exists
        this.createNotificationDropdown();
        
        // Create notification container
        this.createNotificationContainer();
        
        // Update unread count badge
        this.updateUnreadBadge();
    }

    /**
     * Create notification dropdown
     */
    createNotificationDropdown() {
        const notificationBell = document.getElementById('notificationBell');
        if (!notificationBell) return;
        
        // Create dropdown menu
        const dropdownMenu = document.createElement('div');
        dropdownMenu.className = 'dropdown-menu dropdown-menu-end notification-dropdown';
        dropdownMenu.style.width = '400px';
        dropdownMenu.style.maxHeight = '500px';
        dropdownMenu.style.overflowY = 'auto';
        dropdownMenu.innerHTML = `
            <div class="notification-header">
                <h6 class="dropdown-header">Notifikasi</h6>
                <div class="notification-actions">
                    <button class="btn btn-sm btn-link mark-all-read" id="markAllRead">
                        Tandai semua sudah dibaca
                    </button>
                    <button class="btn btn-sm btn-link clear-all" id="clearAllNotifications">
                        Hapus semua
                    </button>
                </div>
            </div>
            <div class="notification-list" id="notificationList">
                <div class="notification-loading" id="notificationLoading">
                    <div class="text-center p-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Memuat notifikasi...</div>
                    </div>
                </div>
            </div>
            <div class="notification-footer">
                <a href="/sakip/notifications" class="dropdown-item text-center">
                    Lihat semua notifikasi
                </a>
            </div>
        `;
        
        // Insert after bell
        notificationBell.parentNode.insertBefore(dropdownMenu, notificationBell.nextSibling);
        
        // Setup dropdown behavior
        notificationBell.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleNotificationDropdown();
        });
        
        // Setup action handlers
        this.setupNotificationActions();
    }

    /**
     * Setup notification actions
     */
    setupNotificationActions() {
        // Mark all as read
        const markAllReadBtn = document.getElementById('markAllRead');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });
        }
        
        // Clear all notifications
        const clearAllBtn = document.getElementById('clearAllNotifications');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.clearAllNotifications();
            });
        }
    }

    /**
     * Create notification container for toast notifications
     */
    createNotificationContainer() {
        // Create container if not exists
        if (!document.getElementById('notificationContainer')) {
            const container = document.createElement('div');
            container.id = 'notificationContainer';
            container.className = 'notification-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            container.style.maxWidth = '400px';
            
            document.body.appendChild(container);
        }
    }

    /**
     * Setup WebSocket connection
     */
    setupWebSocket() {
        if (!window.WebSocket) {
            console.warn('WebSocket not supported, falling back to polling');
            return;
        }
        
        try {
            // Connect to WebSocket server
            this.wsConnection = new WebSocket(`ws://${window.location.host}/ws/notifications`);
            
            this.wsConnection.onopen = () => {
                console.log('WebSocket connection established');
                this.reconnectAttempts = 0;
                this.showConnectionStatus('connected');
            };
            
            this.wsConnection.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleWebSocketMessage(data);
                } catch (error) {
                    console.error('Error parsing WebSocket message:', error);
                }
            };
            
            this.wsConnection.onclose = () => {
                console.log('WebSocket connection closed');
                this.showConnectionStatus('disconnected');
                this.attemptReconnection();
            };
            
            this.wsConnection.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.showConnectionStatus('error');
            };
            
        } catch (error) {
            console.error('Error establishing WebSocket connection:', error);
            this.showConnectionStatus('error');
        }
    }

    /**
     * Handle WebSocket message
     */
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'notification':
                this.addNotification(data.notification);
                break;
            case 'notification_update':
                this.updateNotification(data.notification);
                break;
            case 'notification_delete':
                this.deleteNotification(data.notification_id);
                break;
            case 'unread_count':
                this.updateUnreadCount(data.count);
                break;
            default:
                console.log('Unknown message type:', data.type);
        }
    }

    /**
     * Attempt reconnection
     */
    attemptReconnection() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.log('Max reconnection attempts reached');
            return;
        }
        
        this.reconnectAttempts++;
        const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000); // Exponential backoff
        
        console.log(`Attempting reconnection ${this.reconnectAttempts}/${this.maxReconnectAttempts} in ${delay}ms`);
        
        setTimeout(() => {
            this.setupWebSocket();
        }, delay);
    }

    /**
     * Show connection status
     */
    showConnectionStatus(status) {
        const statusElement = document.getElementById('notificationStatus');
        if (!statusElement) return;
        
        const statusConfig = {
            connected: { text: 'Terhubung', class: 'text-success' },
            disconnected: { text: 'Terputus', class: 'text-warning' },
            error: { text: 'Error', class: 'text-danger' }
        };
        
        const config = statusConfig[status] || statusConfig.disconnected;
        
        statusElement.textContent = config.text;
        statusElement.className = `notification-status ${config.class}`;
    }

    /**
     * Load notifications via AJAX
     */
    async loadNotifications() {
        try {
            const response = await fetch('/sakip/api/notifications');
            const result = await response.json();
            
            if (result.success) {
                this.notifications = result.data;
                this.unreadCount = result.unread_count || 0;
                this.renderNotifications();
                this.updateUnreadBadge();
            } else {
                console.error('Failed to load notifications:', result.message);
            }
            
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    /**
     * Setup event handlers
     */
    setupEventHandlers() {
        // Handle notification clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item')) {
                const notificationId = e.target.closest('.notification-item').dataset.notificationId;
                this.handleNotificationClick(notificationId);
            }
        });
        
        // Handle mark as read
        document.addEventListener('click', (e) => {
            if (e.target.closest('.mark-as-read-btn')) {
                const notificationId = e.target.closest('.mark-as-read-btn').dataset.notificationId;
                this.markAsRead(notificationId);
            }
        });
        
        // Handle delete notification
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-notification-btn')) {
                const notificationId = e.target.closest('.delete-notification-btn').dataset.notificationId;
                this.deleteNotification(notificationId);
            }
        });
        
        // Handle dropdown close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-dropdown') && !e.target.closest('#notificationBell')) {
                this.hideNotificationDropdown();
            }
        });
    }

    /**
     * Start polling for notifications (fallback when WebSocket is not available)
     */
    startPolling() {
        // Poll every 30 seconds
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    /**
     * Toggle notification dropdown
     */
    toggleNotificationDropdown() {
        const dropdown = document.querySelector('.notification-dropdown');
        if (!dropdown) return;
        
        const isVisible = dropdown.style.display === 'block';
        
        if (isVisible) {
            this.hideNotificationDropdown();
        } else {
            this.showNotificationDropdown();
        }
    }

    /**
     * Show notification dropdown
     */
    showNotificationDropdown() {
        const dropdown = document.querySelector('.notification-dropdown');
        if (!dropdown) return;
        
        dropdown.style.display = 'block';
        
        // Mark notifications as read when dropdown is opened
        if (this.unreadCount > 0) {
            setTimeout(() => {
                this.markAllAsRead();
            }, 1000);
        }
    }

    /**
     * Hide notification dropdown
     */
    hideNotificationDropdown() {
        const dropdown = document.querySelector('.notification-dropdown');
        if (!dropdown) return;
        
        dropdown.style.display = 'none';
    }

    /**
     * Add notification
     */
    addNotification(notification) {
        // Add to beginning of array
        this.notifications.unshift(notification);
        
        // Update unread count
        if (!notification.is_read) {
            this.unreadCount++;
        }
        
        // Render notification
        this.renderNotification(notification, true);
        
        // Update badge
        this.updateUnreadBadge();
        
        // Show toast notification
        this.showToastNotification(notification);
    }

    /**
     * Update notification
     */
    updateNotification(updatedNotification) {
        const index = this.notifications.findIndex(n => n.id === updatedNotification.id);
        if (index !== -1) {
            this.notifications[index] = updatedNotification;
            this.renderNotifications();
            this.updateUnreadBadge();
        }
    }

    /**
     * Delete notification
     */
    deleteNotification(notificationId) {
        this.notifications = this.notifications.filter(n => n.id !== parseInt(notificationId));
        this.renderNotifications();
        this.updateUnreadBadge();
        
        // Remove from UI
        const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.remove();
        }
    }

    /**
     * Render all notifications
     */
    renderNotifications() {
        const notificationList = document.getElementById('notificationList');
        if (!notificationList) return;
        
        // Clear existing notifications
        const existingItems = notificationList.querySelectorAll('.notification-item');
        existingItems.forEach(item => item.remove());
        
        // Hide loading
        const loadingElement = document.getElementById('notificationLoading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        // Render notifications
        this.notifications.slice(0, 10).forEach(notification => {
            this.renderNotification(notification, false);
        });
        
        // Show empty state if no notifications
        if (this.notifications.length === 0) {
            this.showEmptyState();
        }
    }

    /**
     * Render single notification
     */
    renderNotification(notification, prepend = false) {
        const notificationList = document.getElementById('notificationList');
        if (!notificationList) return;
        
        const notificationElement = this.createNotificationElement(notification);
        
        if (prepend) {
            notificationList.insertBefore(notificationElement, notificationList.firstChild);
        } else {
            notificationList.appendChild(notificationElement);
        }
    }

    /**
     * Create notification element
     */
    createNotificationElement(notification) {
        const element = document.createElement('div');
        element.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
        element.dataset.notificationId = notification.id;
        
        const iconClass = this.getNotificationIcon(notification.type);
        const timeAgo = this.getTimeAgo(notification.created_at);
        
        element.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="${iconClass}"></i>
                </div>
                <div class="notification-body">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-meta">
                        <span class="notification-time">${timeAgo}</span>
                        ${notification.institution_name ? `<span class="notification-institution">${notification.institution_name}</span>` : ''}
                    </div>
                </div>
                <div class="notification-actions">
                    <button class="btn btn-sm btn-link mark-as-read-btn" data-notification-id="${notification.id}" 
                            title="Tandai sudah dibaca" style="display: ${notification.is_read ? 'none' : 'block'}">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-sm btn-link delete-notification-btn" data-notification-id="${notification.id}" 
                            title="Hapus notifikasi">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        return element;
    }

    /**
     * Get notification icon
     */
    getNotificationIcon(type) {
        const icons = {
            'info': 'fas fa-info-circle text-info',
            'success': 'fas fa-check-circle text-success',
            'warning': 'fas fa-exclamation-triangle text-warning',
            'error': 'fas fa-times-circle text-danger',
            'assessment': 'fas fa-clipboard-check text-primary',
            'report': 'fas fa-chart-bar text-primary',
            'audit': 'fas fa-search text-secondary',
            'system': 'fas fa-cog text-muted'
        };
        
        return icons[type] || icons['info'];
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
     * Show empty state
     */
    showEmptyState() {
        const notificationList = document.getElementById('notificationList');
        if (!notificationList) return;
        
        const emptyElement = document.createElement('div');
        emptyElement.className = 'notification-empty';
        emptyElement.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-bell-slash fa-2x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada notifikasi</p>
            </div>
        `;
        
        notificationList.appendChild(emptyElement);
    }

    /**
     * Update unread badge
     */
    updateUnreadBadge() {
        const badge = document.getElementById('notificationBadge');
        if (!badge) return;
        
        if (this.unreadCount > 0) {
            badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/sakip/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update local data
                const notification = this.notifications.find(n => n.id === parseInt(notificationId));
                if (notification) {
                    notification.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
                
                // Update UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                }
                
                const markAsReadBtn = document.querySelector(`[data-notification-id="${notificationId}"].mark-as-read-btn`);
                if (markAsReadBtn) {
                    markAsReadBtn.style.display = 'none';
                }
                
                this.updateUnreadBadge();
            }
            
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await fetch('/sakip/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update local data
                this.notifications.forEach(notification => {
                    notification.is_read = true;
                });
                this.unreadCount = 0;
                
                // Update UI
                document.querySelectorAll('.notification-item').forEach(element => {
                    element.classList.remove('unread');
                });
                
                document.querySelectorAll('.mark-as-read-btn').forEach(btn => {
                    btn.style.display = 'none';
                });
                
                this.updateUnreadBadge();
            }
            
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    /**
     * Clear all notifications
     */
    async clearAllNotifications() {
        if (!confirm('Apakah Anda yakin ingin menghapus semua notifikasi?')) {
            return;
        }
        
        try {
            const response = await fetch('/sakip/api/notifications/clear-all', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Clear local data
                this.notifications = [];
                this.unreadCount = 0;
                
                // Clear UI
                this.renderNotifications();
                this.updateUnreadBadge();
            }
            
        } catch (error) {
            console.error('Error clearing notifications:', error);
        }
    }

    /**
     * Handle notification click
     */
    handleNotificationClick(notificationId) {
        const notification = this.notifications.find(n => n.id === parseInt(notificationId));
        if (!notification) return;
        
        // Mark as read
        if (!notification.is_read) {
            this.markAsRead(notificationId);
        }
        
        // Navigate to related content
        if (notification.url) {
            window.location.href = notification.url;
        }
    }

    /**
     * Show toast notification
     */
    showToastNotification(notification) {
        const container = document.getElementById('notificationContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast notification-toast show`;
        toast.style.marginBottom = '10px';
        
        const iconClass = this.getNotificationIcon(notification.type);
        
        toast.innerHTML = `
            <div class="toast-header">
                <i class="${iconClass} me-2"></i>
                <strong class="me-auto">${notification.title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${notification.message}
                ${notification.url ? `<div class="mt-2"><a href="${notification.url}" class="btn btn-sm btn-primary">Lihat Detail</a></div>` : ''}
            </div>
        `;
        
        container.appendChild(toast);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
        
        // Handle close button
        const closeBtn = toast.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.remove();
            });
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Implementation for showing notifications
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

// Initialize notification system when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.sakipNotification = new SakipNotification();
});