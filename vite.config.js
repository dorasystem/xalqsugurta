import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/osago/main.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        minify: 'esbuild',
        rollupOptions: {
            output: {
                manualChunks: {
                    // 'insurence-main': ['resources/js/pages/insurence/main.js']
                }
            }
        }
    }
});
