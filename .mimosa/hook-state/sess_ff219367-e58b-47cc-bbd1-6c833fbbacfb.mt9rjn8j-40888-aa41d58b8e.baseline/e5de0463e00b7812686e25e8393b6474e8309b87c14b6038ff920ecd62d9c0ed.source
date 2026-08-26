/**
 * SAKIP Real-time API
 * Government-style real-time updates and notifications for SAKIP module
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
        global.SAKIP_REAL_TIME = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Real-time Configuration
     */
    const REAL_TIME_CONFIG = {
        // WebSocket configuration
        wsUrl: null, // Will be set dynamically
        wsProtocol: 'wss',
        wsReconnectInterval: 5000,
        wsMaxReconnectAttempts: 10,
        wsHeartbeatInterval: 30000,

        // Pusher configuration (alternative to WebSocket)
        pusherKey: null,
        pusherCluster: 'ap1',
        pusherEncrypted: true,

        // Server-Sent Events configuration
        sseUrl: null,
        sseReconnectInterval: 3000,

        // General configuration
        enableNotifications: true,
        enableSound: true,
        maxRetries: 3,
        retryDelay: 1000,
        messageQueueSize: 1000,

        // Channel configuration
        channels: {
            notifications: 'sakip-notifications',
            assessments: 'sakip-assessments',
            reports: 'sakip-reports',
            users: 'sakip-users',
            system: 'sakip-system'
        },

        // Event types
        eventTypes: {
            // Assessment events
            ASSESSMENT_CREATED: 'assessment.created',
            ASSESSMENT_UPDATED: 'assessment.updated',
            ASSESSMENT_SUBMITTED: 'assessment.submitted',
            ASSESSMENT_APPROVED: 'assessment.approved',
            ASSESSMENT_REJECTED: 'assessment.rejected',
            ASSESSMENT_SCORED: 'assessment.scored',

            // Report events
            REPORT_GENERATED: 'report.generated',
            REPORT_EXPORTED: 'report.exported',
            REPORT_SHARED: 'report.shared',

            // User events
            USER_LOGIN: 'user.login',
            USER_LOGOUT: 'user.logout',
            USER_PROFILE_UPDATED: 'user.profile_updated',

            // System events
            SYSTEM_MAINTENANCE: 'system.maintenance',
            SYSTEM_UPDATE: 'system.update',
            SYSTEM_BACKUP_COMPLETED: 'system.backup_completed',

            // Notification events
            NOTIFICATION_CREATED: 'notification.created',
            NOTIFICATION_READ: 'notification.read',
            NOTIFICATION_DELETED: 'notification.deleted',

            // File events
            FILE_UPLOADED: 'file.uploaded',
            FILE_PROCESSED: 'file.processed',
            FILE_DELETED: 'file.deleted'
        }
    };

    /**
     * Connection Manager
     */
    class ConnectionManager {
        constructor() {
            this.connection = null;
            this.connectionType = null;
            this.reconnectAttempts = 0;
            this.isConnected = false;
            this.connectionListeners = new Map();
            this.messageHandlers = new Map();
        }

        /**
         * Initialize connection
         * @param {Object} config - Connection configuration
         */
        async initialize(config = {}) {
            const mergedConfig = { ...REAL_TIME_CONFIG, ...config };

            // Try WebSocket first
            if (mergedConfig.wsUrl) {
                return this.connectWebSocket(mergedConfig);
            }

            // Try Pusher if configured
            if (mergedConfig.pusherKey && typeof Pusher !== 'undefined') {
                return this.connectPusher(mergedConfig);
            }

            // Fallback to Server-Sent Events
            if (mergedConfig.sseUrl) {
                return this.connectSSE(mergedConfig);
            }

            throw new Error('No real-time connection method available');
        }

        /**
         * Connect via WebSocket
         */
        connectWebSocket(config) {
            return new Promise((resolve, reject) => {
                try {
                    this.connection = new WebSocket(config.wsUrl);
                    this.connectionType = 'websocket';

                    this.connection.onopen = () => {
                        this.isConnected = true;
                        this.reconnectAttempts = 0;
                        this.startHeartbeat();
                        this.emit('connected', { type: 'websocket' });
                        resolve();
                    };

                    this.connection.onmessage = (event) => {
                        this.handleMessage(event.data);
                    };

                    this.connection.onclose = () => {
                        this.isConnected = false;
                        this.stopHeartbeat();
                        this.emit('disconnected');
                        this.attemptReconnect(config);
                    };

                    this.connection.onerror = (error) => {
                        this.emit('error', error);
                        reject(error);
                    };

                } catch (error) {
                    reject(error);
                }
            });
        }

        /**
         * Connect via Pusher
         */
        connectPusher(config) {
            return new Promise((resolve, reject) => {
                try {
                    this.connection = new Pusher(config.pusherKey, {
                        cluster: config.pusherCluster,
                        encrypted: config.pusherEncrypted
                    });
                    this.connectionType = 'pusher';

                    this.connection.connection.bind('connected', () => {
                        this.isConnected = true;
                        this.reconnectAttempts = 0;
                        this.emit('connected', { type: 'pusher' });
                        resolve();
                    });

                    this.connection.connection.bind('disconnected', () => {
                        this.isConnected = false;
                        this.emit('disconnected');
                        this.attemptReconnect(config);
                    });

                    this.connection.connection.bind('error', (error) => {
                        this.emit('error', error);
                        reject(error);
                    });

                } catch (error) {
                    reject(error);
                }
            });
        }

        /**
         * Connect via Server-Sent Events
         */
        connectSSE(config) {
            return new Promise((resolve, reject) => {
                try {
                    this.connection = new EventSource(config.sseUrl);
                    this.connectionType = 'sse';

                    this.connection.onopen = () => {
                        this.isConnected = true;
                        this.reconnectAttempts = 0;
                        this.emit('connected', { type: 'sse' });
                        resolve();
                    };

                    this.connection.onmessage = (event) => {
                        this.handleMessage(event.data);
                    };

                    this.connection.onerror = (error) => {
                        this.isConnected = false;
                        this.emit('error', error);
                        this.attemptReconnect(config);
                    };

                } catch (error) {
                    reject(error);
                }
            });
        }

        /**
         * Attempt reconnection
         */
        attemptReconnect(config) {
            if (this.reconnectAttempts >= config.wsMaxReconnectAttempts) {
                this.emit('max_reconnect_attempts_reached');
                return;
            }

            this.reconnectAttempts++;
            const delay = config.wsReconnectInterval * Math.pow(2, this.reconnectAttempts - 1);

            setTimeout(() => {
                this.initialize(config).catch(error => {
                    this.emit('reconnect_failed', error);
                });
            }, delay);
        }

        /**
         * Start heartbeat for WebSocket
         */
        startHeartbeat() {
            if (this.connectionType === 'websocket') {
                this.heartbeatInterval = setInterval(() => {
                    if (this.isConnected) {
                        this.send({ type: 'ping' });
                    }
                }, REAL_TIME_CONFIG.wsHeartbeatInterval);
            }
        }

        /**
         * Stop heartbeat
         */
        stopHeartbeat() {
            if (this.heartbeatInterval) {
                clearInterval(this.heartbeatInterval);
                this.heartbeatInterval = null;
            }
        }

        /**
         * Send message
         */
        send(data) {
            if (!this.isConnected) {
                throw new Error('Connection not established');
            }

            const message = typeof data === 'string' ? data : JSON.stringify(data);

            switch (this.connectionType) {
                case 'websocket':
                    this.connection.send(message);
                    break;
                case 'pusher':
                    // Pusher is channel-based, handled by subscribe
                    break;
                case 'sse':
                    // SSE is server-to-client only
                    break;
            }
        }

        /**
         * Handle incoming message
         */
        handleMessage(data) {
            try {
                const message = typeof data === 'string' ? JSON.parse(data) : data;
                this.emit('message', message);

                // Handle specific message types
                if (message.type) {
                    this.emit(message.type, message);
                }

                // Handle event types
                if (message.event) {
                    this.emit(message.event, message);
                }

            } catch (error) {
                console.error('Error handling message:', error);
                this.emit('message_error', error);
            }
        }

        /**
         * Subscribe to channel (Pusher-specific)
         */
        subscribe(channelName) {
            if (this.connectionType === 'pusher' && this.connection) {
                const channel = this.connection.subscribe(channelName);

                channel.bind('pusher:subscription_succeeded', () => {
                    this.emit('subscribed', { channel: channelName });
                });

                channel.bind('pusher:subscription_error', (error) => {
                    this.emit('subscription_error', { channel: channelName, error });
                });

                return channel;
            }

            return null;
        }

        /**
         * Unsubscribe from channel (Pusher-specific)
         */
        unsubscribe(channelName) {
            if (this.connectionType === 'pusher' && this.connection) {
                this.connection.unsubscribe(channelName);
                this.emit('unsubscribed', { channel: channelName });
            }
        }

        /**
         * Disconnect
         */
        disconnect() {
            this.stopHeartbeat();

            if (this.connection) {
                switch (this.connectionType) {
                    case 'websocket':
                        this.connection.close();
                        break;
                    case 'pusher':
                        this.connection.disconnect();
                        break;
                    case 'sse':
                        this.connection.close();
                        break;
                }

                this.connection = null;
                this.connectionType = null;
                this.isConnected = false;
                this.emit('disconnected');
            }
        }

        /**
         * Add event listener
         */
        on(event, handler) {
            if (!this.connectionListeners.has(event)) {
                this.connectionListeners.set(event, []);
            }
            this.connectionListeners.get(event).push(handler);
        }

        /**
         * Remove event listener
         */
        off(event, handler) {
            if (this.connectionListeners.has(event)) {
                const handlers = this.connectionListeners.get(event);
                const index = handlers.indexOf(handler);
                if (index > -1) {
                    handlers.splice(index, 1);
                }
            }
        }

        /**
         * Emit event
         */
        emit(event, data) {
            if (this.connectionListeners.has(event)) {
                this.connectionListeners.get(event).forEach(handler => {
                    try {
                        handler(data);
                    } catch (error) {
                        console.error('Error in event handler:', error);
                    }
                });
            }
        }
    }

    /**
     * Notification Manager
     */
    class NotificationManager {
        constructor() {
            this.notifications = new Map();
            this.unreadCount = 0;
            this.notificationHandlers = new Map();
            this.soundEnabled = REAL_TIME_CONFIG.enableSound;
            this.maxQueueSize = REAL_TIME_CONFIG.messageQueueSize;
        }

        /**
         * Add notification
         */
        addNotification(notification) {
            const id = notification.id || this.generateId();
            const notificationData = {
                id,
                type: notification.type || 'info',
                title: notification.title || 'Notification',
                message: notification.message || '',
                timestamp: notification.timestamp || new Date().toISOString(),
                read: notification.read || false,
                priority: notification.priority || 'normal',
                data: notification.data || null,
                actions: notification.actions || []
            };

            this.notifications.set(id, notificationData);

            if (!notificationData.read) {
                this.unreadCount++;
                this.showNotification(notificationData);
            }

            // Maintain queue size
            if (this.notifications.size > this.maxQueueSize) {
                const oldestId = this.notifications.keys().next().value;
                this.removeNotification(oldestId);
            }

            this.emit('notification_added', notificationData);
            return notificationData;
        }

        /**
         * Remove notification
         */
        removeNotification(id) {
            const notification = this.notifications.get(id);
            if (notification) {
                this.notifications.delete(id);
                if (!notification.read) {
                    this.unreadCount--;
                }
                this.emit('notification_removed', { id });
            }
        }

        /**
         * Mark notification as read
         */
        markAsRead(id) {
            const notification = this.notifications.get(id);
            if (notification && !notification.read) {
                notification.read = true;
                this.unreadCount--;
                this.emit('notification_read', notification);
            }
        }

        /**
         * Mark all notifications as read
         */
        markAllAsRead() {
            this.notifications.forEach(notification => {
                if (!notification.read) {
                    notification.read = true;
                }
            });
            this.unreadCount = 0;
            this.emit('all_notifications_read');
        }

        /**
         * Clear all notifications
         */
        clearAll() {
            this.notifications.clear();
            this.unreadCount = 0;
            this.emit('notifications_cleared');
        }

        /**
         * Get notifications
         */
        getNotifications(options = {}) {
            const { type = null, read = null, limit = null } = options;

            let notifications = Array.from(this.notifications.values());

            if (type) {
                notifications = notifications.filter(n => n.type === type);
            }

            if (read !== null) {
                notifications = notifications.filter(n => n.read === read);
            }

            // Sort by timestamp (newest first)
            notifications.sort((a, b) =>
                new Date(b.timestamp) - new Date(a.timestamp)
            );

            if (limit) {
                notifications = notifications.slice(0, limit);
            }

            return notifications;
        }

        /**
         * Show notification
         */
        showNotification(notification) {
            // Browser notification
            if ('Notification' in window && Notification.permission === 'granted') {
                const browserNotification = new Notification(notification.title, {
                    body: notification.message,
                    icon: '/images/notification-icon.png',
                    tag: notification.id,
                    requireInteraction: notification.priority === 'high'
                });

                browserNotification.onclick = () => {
                    this.emit('notification_clicked', notification);
                    window.focus();
                    browserNotification.close();
                };
            }

            // Sound notification
            if (this.soundEnabled) {
                this.playNotificationSound(notification.priority);
            }

            // DOM notification (if handler registered)
            this.emit('show_notification', notification);
        }

        /**
         * Play notification sound
         */
        playNotificationSound(priority = 'normal') {
            try {
                const audio = new Audio();
                audio.volume = 0.3;

                switch (priority) {
                    case 'high':
                        audio.src = '/sounds/notification-high.mp3';
                        break;
                    case 'medium':
                        audio.src = '/sounds/notification-medium.mp3';
                        break;
                    default:
                        audio.src = '/sounds/notification-low.mp3';
                }

                audio.play().catch(error => {
                    console.warn('Could not play notification sound:', error);
                });
            } catch (error) {
                console.warn('Could not play notification sound:', error);
            }
        }

        /**
         * Request notification permission
         */
        async requestPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                try {
                    const permission = await Notification.requestPermission();
                    return permission === 'granted';
                } catch (error) {
                    console.error('Error requesting notification permission:', error);
                    return false;
                }
            }
            return Notification.permission === 'granted';
        }

        /**
         * Generate unique ID
         */
        generateId() {
            return `notification_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        }

        /**
         * Add event listener
         */
        on(event, handler) {
            if (!this.notificationHandlers.has(event)) {
                this.notificationHandlers.set(event, []);
            }
            this.notificationHandlers.get(event).push(handler);
        }

        /**
         * Emit event
         */
        emit(event, data) {
            if (this.notificationHandlers.has(event)) {
                this.notificationHandlers.get(event).forEach(handler => {
                    try {
                        handler(data);
                    } catch (error) {
                        console.error('Error in notification handler:', error);
                    }
                });
            }
        }
    }

    /**
     * Event Handler
     */
    class EventHandler {
        constructor() {
            this.eventHandlers = new Map();
            this.setupDefaultHandlers();
        }

        /**
         * Setup default event handlers
         */
        setupDefaultHandlers() {
            // Assessment events
            this.on(REAL_TIME_CONFIG.eventTypes.ASSESSMENT_CREATED, this.handleAssessmentCreated.bind(this));
            this.on(REAL_TIME_CONFIG.eventTypes.ASSESSMENT_SUBMITTED, this.handleAssessmentSubmitted.bind(this));
            this.on(REAL_TIME_CONFIG.eventTypes.ASSESSMENT_APPROVED, this.handleAssessmentApproved.bind(this));

            // Report events
            this.on(REAL_TIME_CONFIG.eventTypes.REPORT_GENERATED, this.handleReportGenerated.bind(this));

            // System events
            this.on(REAL_TIME_CONFIG.eventTypes.SYSTEM_MAINTENANCE, this.handleSystemMaintenance.bind(this));
        }

        /**
         * Handle assessment created event
         */
        handleAssessmentCreated(event) {
            if (window.SAKIP_NOTIFICATIONS) {
                SAKIP_NOTIFICATIONS.addNotification({
                    type: 'info',
                    title: 'Penilaian Baru',
                    message: `Penilaian baru telah dibuat: ${event.data.assessmentName}`,
                    priority: 'normal',
                    data: event.data
                });
            }
        }

        /**
         * Handle assessment submitted event
         */
        handleAssessmentSubmitted(event) {
            if (window.SAKIP_NOTIFICATIONS) {
                SAKIP_NOTIFICATIONS.addNotification({
                    type: 'success',
                    title: 'Penilaian Diserahkan',
                    message: `Penilaian telah diserahkan oleh ${event.data.submitterName}`,
                    priority: 'high',
                    data: event.data
                });
            }
        }

        /**
         * Handle assessment approved event
         */
        handleAssessmentApproved(event) {
            if (window.SAKIP_NOTIFICATIONS) {
                SAKIP_NOTIFICATIONS.addNotification({
                    type: 'success',
                    title: 'Penilaian Disetujui',
                    message: `Penilaian telah disetujui oleh ${event.data.approverName}`,
                    priority: 'high',
                    data: event.data
                });
            }
        }

        /**
         * Handle report generated event
         */
        handleReportGenerated(event) {
            if (window.SAKIP_NOTIFICATIONS) {
                SAKIP_NOTIFICATIONS.addNotification({
                    type: 'info',
                    title: 'Laporan Selesai',
                    message: `Laporan telah selesai dibuat: ${event.data.reportName}`,
                    priority: 'normal',
                    data: event.data,
                    actions: [
                        {
                            label: 'Unduh',
                            action: 'downloadReport',
                            data: { reportId: event.data.reportId }
                        }
                    ]
                });
            }
        }

        /**
         * Handle system maintenance event
         */
        handleSystemMaintenance(event) {
            if (window.SAKIP_NOTIFICATIONS) {
                SAKIP_NOTIFICATIONS.addNotification({
                    type: 'warning',
                    title: 'Pemeliharaan Sistem',
                    message: event.data.message || 'Sistem akan dalam pemeliharaan',
                    priority: 'high',
                    data: event.data
                });
            }
        }

        /**
         * Add event handler
         */
        on(eventType, handler) {
            if (!this.eventHandlers.has(eventType)) {
                this.eventHandlers.set(eventType, []);
            }
            this.eventHandlers.get(eventType).push(handler);
        }

        /**
         * Handle event
         */
        handleEvent(eventType, eventData) {
            if (this.eventHandlers.has(eventType)) {
                this.eventHandlers.get(eventType).forEach(handler => {
                    try {
                        handler(eventData);
                    } catch (error) {
                        console.error(`Error handling event ${eventType}:`, error);
                    }
                });
            }
        }
    }

    /**
     * Main Real-time Manager
     */
    class RealTimeManager {
        constructor() {
            this.connectionManager = new ConnectionManager();
            this.notificationManager = new NotificationManager();
            this.eventHandler = new EventHandler();
            this.isInitialized = false;
            this.config = { ...REAL_TIME_CONFIG };
        }

        /**
         * Initialize real-time system
         */
        async initialize(config = {}) {
            if (this.isInitialized) {
                return;
            }

            // Merge configuration
            this.config = { ...this.config, ...config };

            try {
                // Initialize connection
                await this.connectionManager.initialize(this.config);

                // Setup connection event handlers
                this.setupConnectionHandlers();

                // Request notification permission
                await this.notificationManager.requestPermission();

                this.isInitialized = true;
                this.eventHandler.handleEvent('initialized', { config: this.config });

            } catch (error) {
                console.error('Failed to initialize real-time system:', error);
                throw error;
            }
        }

        /**
         * Setup connection event handlers
         */
        setupConnectionHandlers() {
            this.connectionManager.on('connected', (data) => {
                console.log('Real-time connection established:', data);
                this.subscribeToChannels();
            });

            this.connectionManager.on('disconnected', () => {
                console.log('Real-time connection lost');
            });

            this.connectionManager.on('message', (message) => {
                this.handleIncomingMessage(message);
            });

            this.connectionManager.on('error', (error) => {
                console.error('Real-time connection error:', error);
            });
        }

        /**
         * Subscribe to channels
         */
        subscribeToChannels() {
            const { channels } = this.config;

            Object.keys(channels).forEach(channelKey => {
                const channelName = channels[channelKey];
                const channel = this.connectionManager.subscribe(channelName);

                if (channel) {
                    console.log(`Subscribed to channel: ${channelName}`);
                }
            });
        }

        /**
         * Handle incoming message
         */
        handleIncomingMessage(message) {
            // Handle different message types
            if (message.type === 'notification') {
                this.notificationManager.addNotification(message.data);
            } else if (message.type === 'event') {
                this.eventHandler.handleEvent(message.event, message.data);
            }

            // Emit for external handlers
            this.eventHandler.handleEvent('message_received', message);
        }

        /**
         * Send message
         */
        sendMessage(type, data) {
            const message = {
                type,
                data,
                timestamp: new Date().toISOString(),
                userId: this.getCurrentUserId()
            };

            this.connectionManager.send(message);
        }

        /**
         * Get current user ID
         */
        getCurrentUserId() {
            // This should be implemented based on your authentication system
            return window.currentUserId || null;
        }

        /**
         * Get notification manager
         */
        getNotificationManager() {
            return this.notificationManager;
        }

        /**
         * Get event handler
         */
        getEventHandler() {
            return this.eventHandler;
        }

        /**
         * Get connection status
         */
        getConnectionStatus() {
            return {
                isConnected: this.connectionManager.isConnected,
                connectionType: this.connectionManager.connectionType,
                reconnectAttempts: this.connectionManager.reconnectAttempts
            };
        }

        /**
         * Disconnect
         */
        disconnect() {
            this.connectionManager.disconnect();
            this.isInitialized = false;
        }
    }

    /**
     * Main SAKIP Real-time API
     */
    const SAKIP_REAL_TIME = {
        // Configuration
        config: REAL_TIME_CONFIG,

        // Core classes
        ConnectionManager,
        NotificationManager,
        EventHandler,
        RealTimeManager,

        // Create main instance
        manager: new RealTimeManager(),

        // Convenience methods
        initialize: (config) => manager.initialize(config),
        sendMessage: (type, data) => manager.sendMessage(type, data),
        getConnectionStatus: () => manager.getConnectionStatus(),
        disconnect: () => manager.disconnect(),

        // Notification methods
        addNotification: (notification) => manager.notificationManager.addNotification(notification),
        getNotifications: (options) => manager.notificationManager.getNotifications(options),
        markAsRead: (id) => manager.notificationManager.markAsRead(id),
        markAllAsRead: () => manager.notificationManager.markAllAsRead(),
        clearAll: () => manager.notificationManager.clearAll(),
        getUnreadCount: () => manager.notificationManager.unreadCount,

        // Event handling methods
        on: (event, handler) => manager.eventHandler.on(event, handler),
        off: (event, handler) => manager.eventHandler.off(event, handler),

        // Utility methods
        requestNotificationPermission: () => manager.notificationManager.requestPermission(),

        // Event types
        eventTypes: REAL_TIME_CONFIG.eventTypes,

        // Channel names
        channels: REAL_TIME_CONFIG.channels
    };

    // Return for UMD wrapper
    return SAKIP_REAL_TIME;
}));