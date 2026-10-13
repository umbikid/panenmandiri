<?php

$file = __DIR__ . '/resources/js/Layouts/AuthenticatedLayout.vue';
$content = file_get_contents($file);

// Replace desktop navigation links
$desktopLinks = <<<EOT
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <!-- Umum -->
    <NavLink v-if="!\$page.props.auth.user.roles.includes('mitra') && !\$page.props.auth.user.roles.includes('admin')" :href="route('dashboard')" :active="route().current('dashboard')">
        Dashboard
    </NavLink>
    
    <!-- Mitra -->
    <template v-if="\$page.props.auth.user.roles.includes('mitra')">
        <NavLink :href="route('mitra.dashboard')" :active="route().current('mitra.dashboard')">
            Dashboard Mitra
        </NavLink>
        <NavLink :href="route('mitra.products.index')" :active="route().current('mitra.products.*')">
            Kelola Produk
        </NavLink>
    </template>
</div>
EOT;

$content = preg_replace(
    '/<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">.*?<\/div>/s',
    $desktopLinks,
    $content
);

// Replace mobile navigation links
$mobileLinks = <<<EOT
<div class="space-y-1 pb-3 pt-2">
    <!-- Umum -->
    <ResponsiveNavLink v-if="!\$page.props.auth.user.roles.includes('mitra') && !\$page.props.auth.user.roles.includes('admin')" :href="route('dashboard')" :active="route().current('dashboard')">
        Dashboard
    </ResponsiveNavLink>
    
    <!-- Mitra -->
    <template v-if="\$page.props.auth.user.roles.includes('mitra')">
        <ResponsiveNavLink :href="route('mitra.dashboard')" :active="route().current('mitra.dashboard')">
            Dashboard Mitra
        </ResponsiveNavLink>
        <ResponsiveNavLink :href="route('mitra.products.index')" :active="route().current('mitra.products.*')">
            Kelola Produk
        </ResponsiveNavLink>
    </template>
</div>
EOT;

$content = preg_replace(
    '/<div class="space-y-1 pb-3 pt-2">.*?<\/div>/s',
    $mobileLinks,
    $content
);

file_put_contents($file, $content);
