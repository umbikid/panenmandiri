<?php
$regionTreeVue = __DIR__ . '/resources/js/Components/RegionTree.vue';
$content = file_get_contents($regionTreeVue);

$scriptReplacement = <<<JAVASCRIPT
import { ref, computed, watch } from 'vue';

const props = defineProps({
    provinces: Array,
    modelValue: Array
});

const emit = defineEmits(['update:modelValue']);

const expandedProvinces = ref({});
const searchQuery = ref('');

const filteredProvinces = computed(() => {
    if (!searchQuery.value) return props.provinces;
    
    const q = searchQuery.value.toLowerCase();
    
    return props.provinces.map(prov => {
        if (prov.name.toLowerCase().includes(q)) {
            return prov;
        }
        
        const filteredRegencies = prov.regencies.filter(reg => 
            (reg.type + ' ' + reg.name).toLowerCase().includes(q)
        );
        
        if (filteredRegencies.length > 0) {
            return {
                ...prov,
                regencies: filteredRegencies
            };
        }
        
        return null;
    }).filter(p => p !== null);
});

watch(searchQuery, (newVal) => {
    if (newVal) {
        filteredProvinces.value.forEach(prov => {
            expandedProvinces.value[prov.id] = true;
        });
    }
});
JAVASCRIPT;

$content = preg_replace(
    "/import \{ ref \} from 'vue';.*?const expandedProvinces = ref\(\{\}\);/s",
    $scriptReplacement,
    $content
);

$templateSearch = <<<HTML
<template>
    <div>
        <div class="mb-3 relative">
            <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Cari provinsi atau kota..." 
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-green-700 focus:border-green-700"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
        <div class="border border-gray-300 rounded-md p-4 max-h-64 overflow-y-auto bg-gray-50">
            <div v-if="filteredProvinces.length === 0" class="text-gray-500 text-sm text-center py-4">
                Pencarian tidak ditemukan.
            </div>
            <div v-for="prov in filteredProvinces" :key="prov.id" class="mb-2">
HTML;

$content = preg_replace(
    '/<template>\s*<div class="border border-gray-300 rounded-md p-4 max-h-64 overflow-y-auto bg-gray-50">\s*<div v-for="prov in provinces" :key="prov.id" class="mb-2">/s',
    $templateSearch,
    $content
);

$content = str_replace("</template>", "    </div>\n</template>", $content);

file_put_contents($regionTreeVue, $content);
echo "RegionTree updated with search filter";
