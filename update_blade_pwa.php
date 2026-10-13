<?php

$bladeFile = __DIR__ . '/resources/views/app.blade.php';
$content = file_get_contents($bladeFile);

if (!str_contains($content, 'manifest.webmanifest')) {
    $manifestLink = <<<EOT
        <!-- PWA Configuration -->
        <link rel="manifest" href="/build/manifest.webmanifest">
        <meta name="theme-color" content="#1B5E20">
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/build/sw.js', { scope: '/' });
                });
            }
        </script>
EOT;

    $content = str_replace('</head>', $manifestLink . "\n    </head>", $content);
    file_put_contents($bladeFile, $content);
}

echo "app.blade.php updated";
