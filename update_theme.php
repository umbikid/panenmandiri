<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/resources/js'));

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'vue') {
        $content = file_get_contents($file->getPathname());
        
        // Convert gray themes to green (Dark Green Theme)
        $newContent = str_replace(
            ['bg-gray-800', 'hover:bg-gray-700', 'active:bg-gray-900', 'focus:ring-indigo-500', 'text-indigo-600', 'hover:text-indigo-900', 'text-gray-800', 'border-gray-800', 'ring-indigo-500'],
            ['bg-green-800', 'hover:bg-green-900', 'active:bg-green-950', 'focus:ring-green-700', 'text-green-700', 'hover:text-green-900', 'text-green-900', 'border-green-800', 'ring-green-700'],
            $content
        );

        // For dark mode (if any, though we are focusing on dark green and white)
        // Ensure white background is maintained where applicable
        // The user specifically asked for "Hijau tua dan putih".
        
        if ($newContent !== $content) {
            file_put_contents($file->getPathname(), $newContent);
        }
    }
}
