/**
 * SAKIP Notifications
 * Government-style real-time notifications and alerts system for SAKIP module
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
        global.SAKIP_NOTIFICATIONS = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Notification Configuration Constants
     */
    const NOTIFICATION_CONSTANTS = {
        NOTIFICATION_TYPES: {
            SUCCESS: 'success',
            ERROR: 'error',
            WARNING: 'warning',
            INFO: 'info',
            SYSTEM: 'system',
            ASSESSMENT: 'assessment',
            REPORT: 'report',
            USER: 'user',
            INSTITUTION: 'institution',
            SYSTEM_ALERT: 'system_alert',
            DEADLINE: 'deadline',
            APPROVAL: 'approval'
        },
        PRIORITY_LEVELS: {
            LOW: 'low',
            MEDIUM: 'medium',
            HIGH: 'high',
            CRITICAL: 'critical',
            URGENT: 'urgent'
        },
        STATUS_TYPES: {
            UNREAD: 'unread',
            READ: 'read',
            ARCHIVED: 'archived',
            DELETED: 'deleted',
            DISMISSED: 'dismissed'
        },
        CHANNEL_TYPES: {
            WEB_SOCKET: 'web_socket',
            PUSHER: 'pusher',
            SERVER_SENT_EVENTS: 'server_sent_events',
            POLLING: 'polling',
            LOCAL: 'local'
        },
        POSITIONS: {
            TOP_RIGHT: 'top-right',
            TOP_LEFT: 'top-left',
            TOP_CENTER: 'top-center',
            BOTTOM_RIGHT: 'bottom-right',
            BOTTOM_LEFT: 'bottom-left',
            BOTTOM_CENTER: 'bottom-center'
        },
        DISPLAY_MODES: {
            TOAST: 'toast',
            BANNER: 'banner',
            MODAL: 'modal',
            INLINE: 'inline',
            DESKTOP: 'desktop'
        },
        TIME_SETTINGS: {
            DEFAULT_DURATION: 5000,
            LONG_DURATION: 10000,
            SHORT_DURATION: 3000,
            PERSISTENT: -1,
            AUTO_DISMISS: true
        },
        MAX_SETTINGS: {
            TOAST_COUNT: 5,
            NOTIFICATION_HISTORY: 1000,
            BANNER_COUNT: 1,
            RETRY_ATTEMPTS: 3,
            RETRY_DELAY: 1000
        },
        CONNECTION_SETTINGS: {
            RECONNECT_INTERVAL: 5000,
            CONNECTION_TIMEOUT: 30000,
            HEARTBEAT_INTERVAL: 30000,
            MAX_RECONNECT_ATTEMPTS: 10
        },
        ERROR_MESSAGES: {
            CONNECTION_FAILED: 'Koneksi ke server gagal',
            AUTHENTICATION_FAILED: 'Autentikasi gagal',
            PERMISSION_DENIED: 'Akses ditolak',
            NOTIFICATION_FAILED: 'Gagal mengirim notifikasi',
            SUBSCRIPTION_FAILED: 'Gagal berlangganan channel',
            INVALID_CHANNEL: 'Channel tidak valid',
            SYSTEM_ERROR: 'Kesalahan sistem',
            NETWORK_ERROR: 'Kesalahan jaringan',
            TIMEOUT_ERROR: 'Waktu koneksi habis'
        },
        SUCCESS_MESSAGES: {
            CONNECTION_SUCCESS: 'Koneksi berhasil',
            SUBSCRIPTION_SUCCESS: 'Berlangganan berhasil',
            NOTIFICATION_SENT: 'Notifikasi terkirim',
            CHANNEL_CONNECTED: 'Channel terhubung',
            MESSAGE_RECEIVED: 'Pesan diterima'
        },
        DEFAULT_SETTINGS: {
            position: 'top-right',
            displayMode: 'toast',
            duration: 5000,
            autoDismiss: true,
            showCloseButton: true,
            showProgressBar: true,
            soundEnabled: true,
            desktopNotifications: true,
            maxNotifications: 5,
            groupSimilar: true,
            enableRealTime: true,
            channelType: 'web_socket',
            reconnectAutomatically: true,
            showTimestamp: true,
            showIcon: true,
            animation: 'slide',
            soundFile: '/sounds/notification.mp3'
        },
        SOUND_FILES: {
            SUCCESS: '/sounds/success.mp3',
            ERROR: '/sounds/error.mp3',
            WARNING: '/sounds/warning.mp3',
            INFO: '/sounds/info.mp3',
            SYSTEM: '/sounds/system.mp3'
        }
    };

    /**
     * Utility helpers
     */
    const utils = {
        uid(prefix = 'notif') {
            return `${prefix}_${Math.random().toString(36).slice(2, 10)}`;
        },
        now() { return new Date(); },
        isFunction(fn) { return typeof fn === 'function'; },
        clamp(n, min, max) { return Math.min(Math.max(n, min), max); },
        toArray(v) { return Array.isArray(v) ? v : (v == null ? [] : [v]); }
    };

    /**
     * Notification Data Manager
     */
    class NotificationDataManager {
        constructor() {
            this.notifications = new Map();
            this.archivedNotifications = new Map();
            this.deletedNotifications = new Map();
            this.unreadCount = 0;
            this.totalCount = 0;
            this.subscriptions = new Map();
            this.notificationHistory = [];
            this.settings = { ...NOTIFICATION_CONSTANTS.DEFAULT_SETTINGS };

            this.initializeMockData();
            this.setupStorage();
        }

        setupStorage() {
            try {
                const cached = typeof localStorage !== 'undefined' ? localStorage.getItem('sakip_notifications') : null;
                if (cached) {
                    const parsed = JSON.parse(cached);
                    (parsed.notifications || []).forEach(n => this.notifications.set(n.id, n));
                    (parsed.archived || []).forEach(n => this.archivedNotifications.set(n.id, n));
                    (parsed.deleted || []).forEach(n => this.deletedNotifications.set(n.id, n));
                    this.unreadCount = parsed.unreadCount || 0;
                    this.totalCount = parsed.totalCount || this.notifications.size;
                }
            } catch (e) {
                // ignore storage errors
            }
        }

        persist() {
            try {
                if (typeof localStorage === 'undefined') return;
                const data = {
                    notifications: Array.from(this.notifications.values()),
                    archived: Array.from(this.archivedNotifications.values()),
                    deleted: Array.from(this.deletedNotifications.values()),
                    unreadCount: this.unreadCount,
                    totalCount: this.totalCount
                };
                localStorage.setItem('sakip_notifications', JSON.stringify(data));
            } catch (e) {
                // ignore
            }
        }

        initializeMockData() {
            const mockNotifications = [
                {
                    id: 'notif_001',
                    type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.SUCCESS,
                    title: 'Assessment Submitted Successfully',
                    message: 'Your assessment has been submitted and is now under review.',
                    priority: NOTIFICATION_CONSTANTS.PRIORITY_LEVELS.MEDIUM,
                    timestamp: new Date(Date.now() - 5 * 60 * 1000),
                    status: NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD,
                    userId: 'user123',
                    metadata: {
                        assessmentId: 'assess_001',
                        institutionId: 'inst_001',
                        category: 'assessment'
                    },
                    actions: [
                        { id: 'view_assessment', label: 'View Assessment', type: 'link', url: '/assessments/assess_001' }
                    ]
                },
                {
                    id: 'notif_002',
                    type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.WARNING,
                    title: 'Deadline Approaching',
                    message: 'Assessment deadline is in 2 days. Please complete your submission.',
                    priority: NOTIFICATION_CONSTANTS.PRIORITY_LEVELS.HIGH,
                    timestamp: new Date(Date.now() - 15 * 60 * 1000),
                    status: NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD,
                    userId: 'user123',
                    metadata: {
                        deadline: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000),
                        assessmentId: 'assess_002',
                        category: 'deadline'
                    },
                    actions: [
                        { id: 'complete_assessment', label: 'Complete Now', type: 'button', action: 'complete_assessment', data: { assessmentId: 'assess_002' } }
                    ]
                },
                {
                    id: 'notif_003',
                    type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.INFO,
                    title: 'New Report Available',
                    message: 'Monthly performance report for December 2024 is now available.',
                    priority: NOTIFICATION_CONSTANTS.PRIORITY_LEVELS.LOW,
                    timestamp: new Date(Date.now() - 60 * 60 * 1000),
                    status: NOTIFICATION_CONSTANTS.STATUS_TYPES.READ,
                    userId: 'user123',
                    metadata: {
                        reportId: 'report_001',
                        reportType: 'monthly',
                        category: 'report'
                    },
                    actions: [
                        { id: 'view_report', label: 'View Report', type: 'link', url: '/reports/report_001' }
                    ]
                },
                {
                    id: 'notif_004',
                    type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.ERROR,
                    title: 'Import Failed',
                    message: 'Bulk import of institution data failed. Please check the file format and try again.',
                    priority: NOTIFICATION_CONSTANTS.PRIORITY_LEVELS.CRITICAL,
                    timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000),
                    status: NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD,
                    userId: 'user123',
                    metadata: {
                        importId: 'import_2024_12_01',
                        fileName: 'institutions.xlsx',
                        category: 'system'
                    },
                    actions: [
                        { id: 'view_errors', label: 'View Errors', type: 'link', url: '/imports/errors/import_2024_12_01' }
                    ]
                }
            ];

            mockNotifications.forEach(n => this.addNotification(n, { silent: true }));
            this.persist();
        }

        addNotification(notification, opts = {}) {
            const n = {
                id: notification.id || utils.uid(),
                type: notification.type || NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.INFO,
                title: notification.title || 'Notification',
                message: notification.message || '',
                priority: notification.priority || NOTIFICATION_CONSTANTS.PRIORITY_LEVELS.LOW,
                timestamp: notification.timestamp || utils.now(),
                status: notification.status || NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD,
                userId: notification.userId || null,
                metadata: notification.metadata || {},
                actions: utils.toArray(notification.actions)
            };

            this.notifications.set(n.id, n);
            this.totalCount = this.notifications.size;
            if (n.status === NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD) this.unreadCount += 1;
            this.logEvent('added', n);
            this.persist();

            if (!opts.silent) this.notifySubscribers('new', n);
            return n.id;
        }

        updateNotification(id, patch = {}) {
            const n = this.notifications.get(id);
            if (!n) return null;
            const merged = { ...n, ...patch };
            this.notifications.set(id, merged);
            this.logEvent('updated', merged);
            this.persist();
            this.notifySubscribers('update', merged);
            return merged;
        }

        getNotification(id) {
            return this.notifications.get(id) || this.archivedNotifications.get(id) || this.deletedNotifications.get(id) || null;
        }

        getAll(filter = {}) {
            const list = Array.from(this.notifications.values());
            return list.filter(n => {
                if (filter.type && n.type !== filter.type) return false;
                if (filter.status && n.status !== filter.status) return false;
                if (filter.priority && n.priority !== filter.priority) return false;
                return true;
            }).sort((a, b) => b.timestamp - a.timestamp);
        }

        markAsRead(id) {
            const n = this.notifications.get(id);
            if (!n || n.status === NOTIFICATION_CONSTANTS.STATUS_TYPES.READ) return false;
            n.status = NOTIFICATION_CONSTANTS.STATUS_TYPES.READ;
            this.notifications.set(id, n);
            this.unreadCount = utils.clamp(this.unreadCount - 1, 0, Number.MAX_SAFE_INTEGER);
            this.logEvent('read', n);
            this.persist();
            this.notifySubscribers('read', n);
            return true;
        }

        markAllAsRead() {
            let changed = 0;
            this.notifications.forEach(n => {
                if (n.status === NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD) {
                    n.status = NOTIFICATION_CONSTANTS.STATUS_TYPES.READ;
                    changed += 1;
                }
            });
            this.unreadCount = utils.clamp(this.unreadCount - changed, 0, Number.MAX_SAFE_INTEGER);
            this.logEvent('read_all', { count: changed });
            this.persist();
            this.notifySubscribers('read_all', { count: changed });
            return changed;
        }

        archiveNotification(id) {
            const n = this.notifications.get(id);
            if (!n) return false;
            n.status = NOTIFICATION_CONSTANTS.STATUS_TYPES.ARCHIVED;
            this.notifications.delete(id);
            this.archivedNotifications.set(id, n);
            if (n.status === NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD) {
                this.unreadCount = utils.clamp(this.unreadCount - 1, 0, Number.MAX_SAFE_INTEGER);
            }
            this.logEvent('archived', n);
            this.persist();
            this.notifySubscribers('archive', n);
            return true;
        }

        deleteNotification(id) {
            const n = this.notifications.get(id) || this.archivedNotifications.get(id);
            if (!n) return false;
            this.notifications.delete(id);
            this.archivedNotifications.delete(id);
            n.status = NOTIFICATION_CONSTANTS.STATUS_TYPES.DELETED;
            this.deletedNotifications.set(id, n);
            if (n.status === NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD) {
                this.unreadCount = utils.clamp(this.unreadCount - 1, 0, Number.MAX_SAFE_INTEGER);
            }
            this.logEvent('deleted', n);
            this.persist();
            this.notifySubscribers('delete', n);
            return true;
        }

        restoreNotification(id) {
            const n = this.deletedNotifications.get(id);
            if (!n) return false;
            this.deletedNotifications.delete(id);
            n.status = NOTIFICATION_CONSTANTS.STATUS_TYPES.UNREAD;
            this.notifications.set(id, n);
            this.unreadCount += 1;
            this.logEvent('restored', n);
            this.persist();
            this.notifySubscribers('restore', n);
            return true;
        }

        getUnreadCount() { return this.unreadCount; }
        getTotalCount() { return this.totalCount; }

        logEvent(type, data) {
            this.notificationHistory.push({ type, data, at: utils.now() });
            if (this.notificationHistory.length > NOTIFICATION_CONSTANTS.MAX_SETTINGS.NOTIFICATION_HISTORY) {
                this.notificationHistory.shift();
            }
        }

        getHistory() { return this.notificationHistory.slice(); }

        subscribe(event, handler) {
            const list = this.subscriptions.get(event) || [];
            list.push(handler);
            this.subscriptions.set(event, list);
            return () => this.unsubscribe(event, handler);
        }

        unsubscribe(event, handler) {
            const list = this.subscriptions.get(event) || [];
            const next = list.filter(h => h !== handler);
            this.subscriptions.set(event, next);
        }

        notifySubscribers(event, payload) {
            const list = this.subscriptions.get(event) || [];
            list.forEach(h => {
                try { h(payload); } catch (e) { /* ignore */ }
            });
        }
    }

    /**
     * UI Manager (toast/banner minimal implementation)
     */
    class NotificationUIManager {
        constructor(dataManager) {
            this.dm = dataManager;
            this.container = null;
        }

        ensureContainer() {
            if (typeof document === 'undefined') return null;
            if (this.container && document.body.contains(this.container)) return this.container;
            const c = document.createElement('div');
            c.id = 'sakip-notifications-container';
            c.className = `sakip-notifications ${this.dm.settings.position || NOTIFICATION_CONSTANTS.DEFAULT_SETTINGS.position}`;
            document.body.appendChild(c);
            this.container = c;
            return c;
        }

        renderToast(notification) {
            if (typeof document === 'undefined') return;
            const c = this.ensureContainer();
            if (!c) return;

            const el = document.createElement('div');
            el.className = `sakip-toast sakip-toast-${notification.type}`;
            el.setAttribute('data-id', notification.id);

            const closeBtn = this.dm.settings.showCloseButton ? '<button class="sakip-toast-close" aria-label="Close">×</button>' : '';
            const timestamp = this.dm.settings.showTimestamp ? `<span class="sakip-toast-time">${new Date(notification.timestamp).toLocaleTimeString()}</span>` : '';
            const icon = this.dm.settings.showIcon ? `<span class="sakip-toast-icon"></span>` : '';

            el.innerHTML = `${icon}<div class="sakip-toast-content"><div class="sakip-toast-title">${notification.title}</div><div class="sakip-toast-message">${notification.message}</div>${timestamp}</div>${closeBtn}`;

            if (this.dm.settings.showCloseButton) {
                el.querySelector('.sakip-toast-close').addEventListener('click', () => {
                    el.remove();
                    this.dm.updateNotification(notification.id, { status: NOTIFICATION_CONSTANTS.STATUS_TYPES.DISMISSED });
                });
            }

            c.appendChild(el);

            const max = NOTIFICATION_CONSTANTS.MAX_SETTINGS.TOAST_COUNT;
            while (c.childElementCount > max) {
                c.removeChild(c.firstElementChild);
            }

            if (this.dm.settings.autoDismiss && this.dm.settings.duration !== NOTIFICATION_CONSTANTS.TIME_SETTINGS.PERSISTENT) {
                setTimeout(() => {
                    if (el && el.parentNode) el.remove();
                }, this.dm.settings.duration || NOTIFICATION_CONSTANTS.TIME_SETTINGS.DEFAULT_DURATION);
            }
        }

        destroy() {
            if (this.container && typeof document !== 'undefined') {
                try { this.container.remove(); } catch (e) { /* ignore */ }
                this.container = null;
            }
        }
    }

    /**
     * High-level Notifications Manager
     */
    class NotificationsManager {
        constructor() {
            this.dm = new NotificationDataManager();
            this.ui = new NotificationUIManager(this.dm);
        }

        init(options = {}) {
            this.dm.settings = { ...this.dm.settings, ...options };
            return true;
        }

        notify(notification) {
            const id = this.dm.addNotification(notification);
            const n = this.dm.getNotification(id);
            this.ui.renderToast(n);
            return id;
        }

        success(message, title = 'Success', extra = {}) {
            return this.notify({ type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.SUCCESS, message, title, ...extra });
        }
        error(message, title = 'Error', extra = {}) {
            return this.notify({ type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.ERROR, message, title, ...extra });
        }
        warning(message, title = 'Warning', extra = {}) {
            return this.notify({ type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.WARNING, message, title, ...extra });
        }
        info(message, title = 'Info', extra = {}) {
            return this.notify({ type: NOTIFICATION_CONSTANTS.NOTIFICATION_TYPES.INFO, message, title, ...extra });
        }

        markRead(id) { return this.dm.markAsRead(id); }
        markAllRead() { return this.dm.markAllAsRead(); }
        archive(id) { return this.dm.archiveNotification(id); }
        delete(id) { return this.dm.deleteNotification(id); }
        restore(id) { return this.dm.restoreNotification(id); }

        getUnreadCount() { return this.dm.getUnreadCount(); }
        getAll(filter) { return this.dm.getAll(filter); }
        get(id) { return this.dm.getNotification(id); }
        history() { return this.dm.getHistory(); }

        subscribe(event, handler) { return this.dm.subscribe(event, handler); }
        unsubscribe(event, handler) { return this.dm.unsubscribe(event, handler); }

        destroy() {
            this.ui.destroy();
            // no persistent connections here, so just clear container
        }
    }

    /**
     * Public API exposure
     */
    const SAKIP_NOTIFICATIONS = {
        CONSTANTS: NOTIFICATION_CONSTANTS,
        NotificationDataManager,
        NotificationUIManager,
        NotificationsManager,
        createManager(options = {}) {
            const m = new NotificationsManager();
            m.init(options);
            return m;
        },
        init(options = {}) {
            if (!this._manager) this._manager = new NotificationsManager();
            this._manager.init(options);
            return this._manager;
        },
        notify(notification) {
            if (!this._manager) this.init();
            return this._manager.notify(notification);
        },
        success(message, title, extra) { if (!this._manager) this.init(); return this._manager.success(message, title, extra); },
        error(message, title, extra) { if (!this._manager) this.init(); return this._manager.error(message, title, extra); },
        warning(message, title, extra) { if (!this._manager) this.init(); return this._manager.warning(message, title, extra); },
        info(message, title, extra) { if (!this._manager) this.init(); return this._manager.info(message, title, extra); },
        markRead(id) { if (!this._manager) this.init(); return this._manager.markRead(id); },
        markAllRead() { if (!this._manager) this.init(); return this._manager.markAllRead(); },
        archive(id) { if (!this._manager) this.init(); return this._manager.archive(id); },
        delete(id) { if (!this._manager) this.init(); return this._manager.delete(id); },
        restore(id) { if (!this._manager) this.init(); return this._manager.restore(id); },
        getUnreadCount() { if (!this._manager) this.init(); return this._manager.getUnreadCount(); },
        getAll(filter) { if (!this._manager) this.init(); return this._manager.getAll(filter); },
        get(id) { if (!this._manager) this.init(); return this._manager.get(id); },
        history() { if (!this._manager) this.init(); return this._manager.history(); },
        subscribe(event, handler) { if (!this._manager) this.init(); return this._manager.subscribe(event, handler); },
        unsubscribe(event, handler) { if (!this._manager) this.init(); return this._manager.unsubscribe(event, handler); },
        destroy() { if (this._manager) this._manager.destroy(); this._manager = null; }
    };

    return SAKIP_NOTIFICATIONS;
}));
