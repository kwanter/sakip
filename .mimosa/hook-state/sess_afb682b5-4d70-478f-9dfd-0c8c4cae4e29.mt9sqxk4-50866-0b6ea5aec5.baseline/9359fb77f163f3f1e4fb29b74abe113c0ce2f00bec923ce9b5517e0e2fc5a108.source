/**
 * SAKIP Notification Module
 * Provides notification and alert functionality
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.SAKIP_NOTIFICATION = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {

    /**
     * Notification system
     */
    class NotificationManager {
        constructor() {
            this.container = null;
            this.init();
        }

        /**
         * Initialize notification system
         */
        init() {
            this.createContainer();
        }

        /**
         * Create notification container
         */
        createContainer() {
            if (this.container) return;

            this.container = document.createElement('div');
            this.container.id = 'sakip-notifications';
            this.container.className = 'sakip-notification-container';
            document.body.appendChild(this.container);

            // Add styles
            this.addStyles();
        }

        /**
         * Add notification styles
         */
        addStyles() {
            if (document.getElementById('sakip-notification-styles')) return;

            const styles = `
                .sakip-notification-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 10000;
                    max-width: 400px;
                }

                .sakip-notification {
                    margin-bottom: 10px;
                    padding: 16px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    background: white;
                    border-left: 4px solid;
                    animation: slideIn 0.3s ease-out;
                    position: relative;
                }

                .sakip-notification.success {
                    border-left-color: #10b981;
                    background: #f0fdf4;
                }

                .sakip-notification.error {
                    border-left-color: #ef4444;
                    background: #fef2f2;
                }

                .sakip-notification.warning {
                    border-left-color: #f59e0b;
                    background: #fffbeb;
                }

                .sakip-notification.info {
                    border-left-color: #3b82f6;
                    background: #eff6ff;
                }

                .sakip-notification-header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 4px;
                }

                .sakip-notification-icon {
                    width: 20px;
                    height: 20px;
                    margin-right: 8px;
                    flex-shrink: 0;
                }

                .sakip-notification-title {
                    font-weight: 600;
                    font-size: 14px;
                    margin: 0;
                }

                .sakip-notification-message {
                    font-size: 13px;
                    line-height: 1.4;
                    margin: 0;
                    color: #374151;
                }

                .sakip-notification-close {
                    position: absolute;
                    top: 8px;
                    right: 8px;
                    background: none;
                    border: none;
                    font-size: 16px;
                    cursor: pointer;
                    color: #6b7280;
                    padding: 4px;
                    border-radius: 4px;
                }

                .sakip-notification-close:hover {
                    background: rgba(0, 0, 0, 0.05);
                    color: #374151;
                }

                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }

                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }

                .sakip-notification.hiding {
                    animation: slideOut 0.3s ease-out;
                }
            `;

            const styleElement = document.createElement('style');
            styleElement.id = 'sakip-notification-styles';
            styleElement.textContent = styles;
            document.head.appendChild(styleElement);
        }

        /**
         * Show notification
         */
        show(options) {
            const notification = this.createNotification(options);
            this.container.appendChild(notification);

            // Auto hide after duration
            if (options.duration !== false) {
                setTimeout(() => {
                    this.hide(notification);
                }, options.duration || 5000);
            }

            return notification;
        }

        /**
         * Create notification element
         */
        createNotification(options) {
            const notification = document.createElement('div');
            notification.className = `sakip-notification ${options.type || 'info'}`;

            // Icon based on type
            const iconSvg = this.getIconSvg(options.type || 'info');

            notification.innerHTML = `
                <div class="sakip-notification-header">
                    ${iconSvg}
                    <h4 class="sakip-notification-title">${this.escapeHtml(options.title || 'Notification')}</h4>
                </div>
                <div class="sakip-notification-message">${this.escapeHtml(options.message || '')}</div>
                <button class="sakip-notification-close" onclick="this.parentElement.remove()">&times;</button>
            `;

            return notification;
        }

        /**
         * Get icon SVG
         */
        getIconSvg(type) {
            const icons = {
                success: `<svg class="sakip-notification-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>`,
                error: `<svg class="sakip-notification-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>`,
                warning: `<svg class="sakip-notification-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>`,
                info: `<svg class="sakip-notification-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>`
            };

            return icons[type] || icons.info;
        }

        /**
         * Escape HTML
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Hide notification
         */
        hide(notification) {
            notification.classList.add('hiding');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }

        /**
         * Show success notification
         */
        success(title, message, options = {}) {
            return this.show({
                type: 'success',
                title,
                message,
                ...options
            });
        }

        /**
         * Show error notification
         */
        error(title, message, options = {}) {
            return this.show({
                type: 'error',
                title,
                message,
                ...options
            });
        }

        /**
         * Show warning notification
         */
        warning(title, message, options = {}) {
            return this.show({
                type: 'warning',
                title,
                message,
                ...options
            });
        }

        /**
         * Show info notification
         */
        info(title, message, options = {}) {
            return this.show({
                type: 'info',
                title,
                message,
                ...options
            });
        }

        /**
         * Clear all notifications
         */
        clear() {
            if (this.container) {
                this.container.innerHTML = '';
            }
        }
    }

    /**
     * Create and return notification manager instance
     */
    const notificationManager = new NotificationManager();

    /**
     * Public API
     */
    return {
        show: notificationManager.show.bind(notificationManager),
        success: notificationManager.success.bind(notificationManager),
        error: notificationManager.error.bind(notificationManager),
        warning: notificationManager.warning.bind(notificationManager),
        info: notificationManager.info.bind(notificationManager),
        clear: notificationManager.clear.bind(notificationManager)
    };

}));