import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/sakip/data-tables.js',
                'resources/js/sakip/dashboard.js',
                'resources/js/sakip/assessment.js',
                'resources/js/sakip/report.js',
                'resources/js/sakip/data-collection.js',
                'resources/js/sakip/audit-trail.js',
                'resources/js/sakip/notification.js',
                'resources/js/sakip/helpers.js',
                'resources/js/sakip/data-table-init.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'sakip-vendor': ['react', 'react-dom'],
                    'sakip-core': [
                        'resources/js/sakip/helpers.js',
                        'resources/js/sakip/notification.js',
                        'resources/js/sakip/data-table-init.js'
                    ],
                    'sakip-components': [
                        'resources/js/sakip/data-tables.js',
                        'resources/js/sakip/dashboard.js',
                        'resources/js/sakip/assessment.js',
                        'resources/js/sakip/report.js',
                        'resources/js/sakip/data-collection.js',
                        'resources/js/sakip/audit-trail.js'
                    ]
                }
            }
        }
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            '@sakip': '/resources/js/sakip',
            '@components': '/resources/js/components',
            '@pages': '/resources/js/Pages',
        }
    }
});
