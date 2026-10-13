<?php

$configFile = __DIR__ . '/vite.config.js';
$content = file_get_contents($configFile);

$importString = "import { VitePWA } from 'vite-plugin-pwa';\n";

if (!str_contains($content, 'VitePWA')) {
    $content = str_replace("import vue from '@vitejs/plugin-vue';", "import vue from '@vitejs/plugin-vue';\n" . $importString, $content);
    
    $pwaConfig = <<<EOT
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
EOT;

    $content = str_replace("],", "    $pwaConfig\n    ],", $content);
    
    file_put_contents($configFile, $content);
}

echo "vite.config.js updated";
