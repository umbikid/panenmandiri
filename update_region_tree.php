<?php

function updateRegionTree($path) {
    $content = file_get_contents($path);

    // 1. Add import for RegionTree
    $importStatement = "import RegionTree from '@/Components/RegionTree.vue';";
    if (!str_contains($content, $importStatement)) {
        $content = str_replace("import TextInput from '@/Components/TextInput.vue';", "import TextInput from '@/Components/TextInput.vue';\n" . $importStatement, $content);
    }

    // 2. Replace the Wilayah section
    $pattern = '/<div class="mt-2 border border-gray-300 rounded-md p-4 max-h-64 overflow-y-auto bg-gray-50">.*?<\/div>\s*<\/div>\s*<InputError class="mt-2" :message="form\.errors\.regency_ids" \/>\s*<\/div>/s';
    
    $replacement = <<<EOT
<RegionTree :provinces="provinces" v-model="form.regency_ids" class="mt-2" />
                            <InputError class="mt-2" :message="form.errors.regency_ids" />
                        </div>
EOT;

    $content = preg_replace($pattern, $replacement, $content);

    file_put_contents($path, $content);
}

updateRegionTree(__DIR__ . '/resources/js/Pages/Mitra/Product/Create.vue');
updateRegionTree(__DIR__ . '/resources/js/Pages/Mitra/Product/Edit.vue');
