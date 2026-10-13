<script setup>
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
            reg.name.toLowerCase().includes(q)
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

const toggleExpand = (id) => {
    expandedProvinces.value[id] = !expandedProvinces.value[id];
};

const isProvinceSelected = (prov) => {
    if (!prov.regencies || prov.regencies.length === 0) return false;
    return prov.regencies.every(r => props.modelValue.includes(r.id));
};

const isProvinceIndeterminate = (prov) => {
    if (!prov.regencies || prov.regencies.length === 0) return false;
    const selectedCount = prov.regencies.filter(r => props.modelValue.includes(r.id)).length;
    return selectedCount > 0 && selectedCount < prov.regencies.length;
};

const toggleProvince = (prov, event) => {
    const checked = event.target.checked;
    let newValue = [...props.modelValue];
    
    if (checked) {
        prov.regencies.forEach(r => {
            if (!newValue.includes(r.id)) {
                newValue.push(r.id);
            }
        });
    } else {
        const regencyIds = prov.regencies.map(r => r.id);
        newValue = newValue.filter(id => !regencyIds.includes(id));
    }
    
    emit('update:modelValue', newValue);
};

const toggleRegency = (regencyId, event) => {
    const checked = event.target.checked;
    let newValue = [...props.modelValue];
    
    if (checked) {
        newValue.push(regencyId);
    } else {
        newValue = newValue.filter(id => id !== regencyId);
    }
    
    emit('update:modelValue', newValue);
};

const vIndeterminate = {
    updated(el, binding) {
        el.indeterminate = binding.value;
    },
    mounted(el, binding) {
        el.indeterminate = binding.value;
    }
};
</script>

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
            <div class="flex items-center">
                <button type="button" @click="toggleExpand(prov.id)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-700 bg-gray-200 rounded mr-2 focus:outline-none shrink-0">
                    <span v-if="expandedProvinces[prov.id]">-</span>
                    <span v-else>+</span>
                </button>
                <label class="inline-flex items-center cursor-pointer">
                    <input 
                        type="checkbox" 
                        class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                        :checked="isProvinceSelected(prov)"
                        v-indeterminate="isProvinceIndeterminate(prov)"
                        @change="toggleProvince(prov, $event)"
                    >
                    <span class="ml-2 font-semibold text-gray-700">{{ prov.name }}</span>
                </label>
            </div>
            
            <div v-show="expandedProvinces[prov.id]" class="ml-8 mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 bg-white p-3 border border-gray-100 rounded">
                <label v-for="reg in prov.regencies" :key="reg.id" class="inline-flex items-center cursor-pointer">
                    <input 
                        type="checkbox" 
                        class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                        :checked="modelValue.includes(reg.id)"
                        @change="toggleRegency(reg.id, $event)"
                    >
                    <span class="ml-2 text-sm text-gray-600 truncate" :title="reg.name">{{ reg.name }}</span>
                </label>
            </div>
        </div>
    </div>
    </div>
</template>
