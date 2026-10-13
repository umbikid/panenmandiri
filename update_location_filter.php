<?php

$indexVue = __DIR__ . '/resources/js/Pages/Catalog/Index.vue';
$indexContent = file_get_contents($indexVue);

// 1. Update the JS part
$jsReplacement = <<<JAVASCRIPT
const searchRegency = ref(props.filters.regency_id || '');
const searchQuery = ref(props.filters.q || '');

let initialRegencyName = '';
if (props.filters.regency_id) {
    for (const prov of props.provinces) {
        const reg = prov.regencies.find(r => r.id == props.filters.regency_id);
        if (reg) {
            initialRegencyName = reg.type + ' ' + reg.name;
            break;
        }
    }
}
const searchRegencyName = ref(initialRegencyName);

const updateRegencyId = () => {
    let foundId = '';
    for (const prov of props.provinces) {
        const reg = prov.regencies.find(r => (r.type + ' ' + r.name) === searchRegencyName.value);
        if (reg) {
            foundId = reg.id;
            break;
        }
    }
    
    // Jika input dikosongkan
    if (searchRegencyName.value === '') {
        foundId = '';
    }
    
    searchRegency.value = foundId;
};
JAVASCRIPT;

$indexContent = preg_replace(
    "/const searchRegency = ref\(props\.filters\.regency_id \|\| ''\);\s*const searchQuery = ref\(props\.filters\.q \|\| ''\);/s",
    $jsReplacement,
    $indexContent
);

// We must also update the watch to trigger when searchCategory or searchRegency changes.
// Since searchRegencyName triggers updateRegencyId which updates searchRegency,
// the existing `watch([searchCategory, searchRegency], () => { applyFilters(); });` will still work.

// 2. Update the HTML part
$htmlReplacement = <<<HTML
                    <label class="block text-xs text-gray-500 mb-1">Pilih Lokasi</label>
                    <input 
                        type="text" 
                        list="regency-options" 
                        v-model="searchRegencyName" 
                        @change="updateRegencyId"
                        class="w-full border-gray-300 rounded-md text-sm focus:border-green-700 focus:ring-green-700"
                        placeholder="Ketik lokasi pengiriman..."
                    >
                    <datalist id="regency-options">
                        <template v-for="prov in provinces" :key="prov.id">
                            <option v-for="reg in prov.regencies" :key="reg.id" :value="reg.type + ' ' + reg.name"></option>
                        </template>
                    </datalist>
                </div>
HTML;

$indexContent = preg_replace(
    '/<label class="block text-xs text-gray-500 mb-1">Pilih Lokasi<\/label>\s*<select v-model="searchRegency".*?<\/select>\s*<\/div>/s',
    $htmlReplacement,
    $indexContent
);

file_put_contents($indexVue, $indexContent);

echo "Location filter updated to searchable datalist";
