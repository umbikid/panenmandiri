<?php

function updateFile($path) {
    $content = file_get_contents($path);
    
    // Add watch to imports if not present
    if (!str_contains($content, 'import { ref, computed, watch }')) {
        $content = str_replace('import { ref, computed }', 'import { ref, computed, watch }', $content);
    }

    // Add watch logic before submit function
    $watchLogic = <<<EOT
const autoUnit = computed(() => {
    if (activeCategory.value) {
        return activeCategory.value.name.toLowerCase() === 'beras' ? 'Kg' : 'Ekor';
    }
    return '';
});

watch(activeCategory, (newCat) => {
    if (newCat) {
        form.unit = newCat.name.toLowerCase() === 'beras' ? 'Kg' : 'Ekor';
    }
}, { immediate: true });
EOT;
    
    // Remove old allowedUnits computed
    $content = preg_replace('/const allowedUnits = computed\(\(\) => \{.*?\n\}\);/s', $watchLogic, $content);
    
    // Replace the unit select with plain text
    $unitHtml = <<<EOT
<div class="col-span-2">
                                <InputLabel value="Satuan" />
                                <div class="mt-1 flex items-center h-10 px-3 bg-gray-50 border border-gray-300 rounded-md text-gray-700 font-semibold shadow-sm">
                                    {{ autoUnit || '-' }}
                                </div>
                                <InputError class="mt-2" :message="form.errors.unit" />
                            </div>
EOT;

    $content = preg_replace('/<div class="col-span-2">\s*<InputLabel for="unit" value="Satuan" \/>\s*<select v-model="form\.unit".*?<\/select>\s*<InputError class="mt-2" :message="form\.errors\.unit" \/>\s*<\/div>/s', $unitHtml, $content);
    
    // Make sure unit clears when category clears? Watch does this if we handle empty. But category shouldn't clear once selected usually, or if it does, form.unit is empty.

    file_put_contents($path, $content);
}

updateFile(__DIR__ . '/resources/js/Pages/Mitra/Product/Create.vue');
updateFile(__DIR__ . '/resources/js/Pages/Mitra/Product/Edit.vue');

