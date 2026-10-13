<?php

// 1. Update CatalogController.php
$controllerPath = __DIR__ . '/app/Http/Controllers/CatalogController.php';
$controllerContent = file_get_contents($controllerPath);
$controllerContent = str_replace(
    "->when(\$request->category",
    "->when(\$request->q, function (\$q, \$kw) {
                \$q->where('name', 'like', '%' . addcslashes(\$kw, '%_') . '%');
            })
            ->when(\$request->category",
    $controllerContent
);
$controllerContent = str_replace(
    "'filters' => \$request->only(['category', 'regency_id'])",
    "'filters' => \$request->only(['q', 'category', 'regency_id'])",
    $controllerContent
);
file_put_contents($controllerPath, $controllerContent);

// 2. Update Index.vue
$indexVue = __DIR__ . '/resources/js/Pages/Catalog/Index.vue';
$indexContent = file_get_contents($indexVue);

// Update Script setup
$indexContent = str_replace(
    "const searchRegency = ref(props.filters.regency_id || '');",
    "const searchRegency = ref(props.filters.regency_id || '');\nconst searchQuery = ref(props.filters.q || '');",
    $indexContent
);

$indexContent = str_replace(
    "category: searchCategory.value,",
    "q: searchQuery.value,\n        category: searchCategory.value,",
    $indexContent
);

// We want to trigger applyFilters when Enter is pressed or button is clicked, but auto-apply on category/regency change.
// The watch only watches category and regency_id.
// So watch([searchCategory, searchRegency]) remains.

// Add Search Bar HTML before Filters
$searchHtml = <<<HTML
            <!-- Search Bar -->
            <div class="mb-4 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <form @submit.prevent="applyFilters" class="relative">
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Cari nama produk..." 
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-700 focus:border-green-700"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 px-4 text-sm font-medium text-white bg-green-700 rounded-r-lg hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-700">
                        Cari
                    </button>
                </form>
            </div>
HTML;

$indexContent = preg_replace(
    '/<!-- Filters -->/',
    $searchHtml . "\n\n            <!-- Filters -->",
    $indexContent
);

// Remove Mitra name from Index.vue card
$indexContent = preg_replace(
    '/<div class="text-\[10px\] text-gray-500 mt-2 flex items-center">.*?Mitra: \{\{ product\.partner\?\.name \}\}\s*<\/div>/s',
    '',
    $indexContent
);
file_put_contents($indexVue, $indexContent);

// 3. Update Show.vue
$showVue = __DIR__ . '/resources/js/Pages/Catalog/Show.vue';
$showContent = file_get_contents($showVue);
$showContent = preg_replace(
    '/<div class="flex items-center justify-between">\s*<span class="text-gray-600 text-sm">Mitra Petani\/Peternak:<\/span>\s*<span class="font-bold text-gray-900">\{\{ product\.partner\?\.name \}\}<\/span>\s*<\/div>/s',
    '',
    $showContent
);
file_put_contents($showVue, $showContent);

echo "Search added and partner hidden";
