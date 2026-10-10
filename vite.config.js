import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(dirname, 'resources/js'),
        },
    },
    build: {
        // exceljs (~930 kB, gzip ~256 kB) sudah lazy-load via import() di
        // resources/js/lib/excel.js, jadi hanya diunduh saat user import .xlsx.
        // Naikkan limit agar warning 500 kB default tidak false-positive.
        chunkSizeWarningLimit: 1024,
    },
});
