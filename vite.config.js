import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/sakip/data-tables.js',
                'resources/js/sakip/dashboard.js',
                'resources/js/sakip/notification.js',
                'resources/js/sakip/helpers.js',
                'resources/js/sakip/data-table-init.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'sakip-core': [
                        'resources/js/sakip/helpers.js',
                        'resources/js/sakip/notification.js',
                        'resources/js/sakip/data-table-init.js'
                    ]
                }
            }
        }
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            '@sakip': '/resources/js/sakip',
        }
    }
});
