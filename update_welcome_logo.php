<?php
$file = __DIR__ . '/resources/js/Pages/Welcome.vue';
$content = file_get_contents($file);

// The Laravel logo in Welcome.vue
$newLogo = '<img src="/images/logo.png" class="h-12 w-auto lg:h-16" alt="Panen Mandiri Logo" />';

// It is between <div class="flex lg:col-start-2 lg:justify-center"> and </div>
$pattern = '/<div class="flex lg:col-start-2 lg:justify-center">\s*<svg.*?<\/svg>\s*<\/div>/s';
$replacement = '<div class="flex lg:col-start-2 lg:justify-center">' . "\n                            " . $newLogo . "\n                        </div>";

$newContent = preg_replace($pattern, $replacement, $content);

// Let's also remove the red laravel background svg
$bgPattern = '/<img\s+id="background".*?src="https:\/\/laravel\.com\/assets\/img\/welcome\/background\.svg".*?\/>/s';
$newContent = preg_replace($bgPattern, '', $newContent);

file_put_contents($file, $newContent);
