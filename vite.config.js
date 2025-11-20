import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/livecam/broadcaster-mse.js',
                'resources/js/livecam/viewer-mse.js',
                'resources/js/livecam/trail-classifier.js'
            ],
            refresh: true,
        }),
    ],
    define: {
        'global': 'globalThis',
        'process.env': {},
    },
    resolve: {
        alias: {
            'process': 'process/browser',
            'util': 'util'
        }
    }
});
