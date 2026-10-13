<?php

// 1. Update PublicLayout.vue
$publicVue = __DIR__ . '/resources/js/Layouts/PublicLayout.vue';
$publicContent = file_get_contents($publicVue);

$publicLogoHtml = <<<HTML
                            <Link :href="route('catalog.index')" class="flex items-center">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-green-900" />
                                <span class="ml-2 text-xl font-bold text-green-800 tracking-tight">Panen Mandiri</span>
                            </Link>
HTML;

$publicContent = preg_replace(
    '/<Link :href="route\(\'catalog\.index\'\)">\s*<ApplicationLogo class="block h-9 w-auto fill-current text-green-900" \/>\s*<\/Link>/s',
    $publicLogoHtml,
    $publicContent
);
file_put_contents($publicVue, $publicContent);

// 2. Update AuthenticatedLayout.vue
$authVue = __DIR__ . '/resources/js/Layouts/AuthenticatedLayout.vue';
$authContent = file_get_contents($authVue);

$authLogoHtml = <<<HTML
                                <Link :href="\$page.props.auth.user.roles.includes('mitra') ? route('mitra.dashboard') : route('dashboard')" class="flex items-center">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-green-900" />
                                    <span class="ml-2 text-xl font-bold text-green-800 tracking-tight">Panen Mandiri</span>
                                </Link>
HTML;

$authContent = preg_replace(
    '/<Link :href="\$page\.props\.auth\.user\.roles\.includes\(\'mitra\'\) \? route\(\'mitra\.dashboard\'\) : route\(\'dashboard\'\)">\s*<ApplicationLogo\s*class="block h-9 w-auto fill-current text-green-900 dark:text-gray-200"\s*\/>\s*<\/Link>/s',
    $authLogoHtml,
    $authContent
);
file_put_contents($authVue, $authContent);

echo "Layouts updated with App Name";
