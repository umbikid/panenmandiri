import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';


export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
                VitePWA({
            outDir: 'public/build',
            buildBase: '/build/',
            scope: '/',
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            workbox: {
                navigateFallback: null,
                globPatterns: ['**/*.{js,css,ico,png,svg}']
            },
            manifest: {
                name: 'Panen Mandiri',
                short_name: 'Panen Mandiri',
                description: 'Aplikasi Manajemen Stok Beras & Ternak KMM',
                start_url: '/',
                display: 'standalone',
                background_color: '#ffffff',
                theme_color: '#1B5E20',
                icons: [
                    {
                        src: '/images/logo.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable'
                    }
                ]
            }
        })
    ],
});
