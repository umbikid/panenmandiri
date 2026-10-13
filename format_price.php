<?php

function updatePriceInput($path) {
    $content = file_get_contents($path);

    // 1. Add formattedPrice computed property
    $computedScript = <<<EOT
const autoUnit = computed(() => {
EOT;
    $computedReplacement = <<<EOT
const formattedPrice = computed({
    get() {
        if (!form.price && form.price !== 0) return '';
        return form.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    set(value) {
        const rawValue = value.replace(/\D/g, '');
        form.price = rawValue ? parseInt(rawValue, 10) : '';
    }
});

const autoUnit = computed(() => {
EOT;
    
    if (!str_contains($content, 'const formattedPrice')) {
        $content = str_replace($computedScript, $computedReplacement, $content);
    }

    // 2. Change price input to use text type and v-model="formattedPrice"
    $inputPattern = '/<TextInput\s*id="price"\s*type="number"\s*min="0"\s*class="block w-full rounded-l-none"\s*v-model="form\.price"\s*required\s*\/>/s';
    
    $inputReplacement = <<<EOT
<TextInput
                                    id="price"
                                    type="text"
                                    class="block w-full rounded-l-none"
                                    v-model="formattedPrice"
                                    required
                                />
EOT;

    $content = preg_replace($inputPattern, $inputReplacement, $content);

    file_put_contents($path, $content);
}

updatePriceInput(__DIR__ . '/resources/js/Pages/Mitra/Product/Create.vue');
updatePriceInput(__DIR__ . '/resources/js/Pages/Mitra/Product/Edit.vue');
