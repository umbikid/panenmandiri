<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    provinces: Array,
    filters: Object
});

const searchCategory = ref(props.filters.category || '');
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

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const applyFilters = () => {
    router.get(route('catalog.index'), {
        q: searchQuery.value,
        category: searchCategory.value,
        regency_id: searchRegency.value
    }, { preserveState: true, preserveScroll: true });
};

// Auto apply filter on change
watch([searchCategory, searchRegency], () => {
    applyFilters();
});

</script>

<template>
    <Head title="Katalog Produk" />

    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            
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

            <!-- Filters -->
            <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-4">
                <div class="w-full sm:w-1/2">
                    <label class="block text-xs text-gray-500 mb-1">Pilih Kategori</label>
                    <select v-model="searchCategory" class="w-full border-gray-300 rounded-md text-sm focus:border-green-700 focus:ring-green-700">
                        <option value="">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
                    </select>
                </div>
                
                <div class="w-full sm:w-1/2">
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
            </div>

            <!-- Product Grid -->
            <div v-if="products.data.length === 0" class="text-center py-12 text-gray-500 bg-white rounded-lg border border-gray-100">
                Tidak ada produk yang ditemukan untuk kriteria ini.
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                <Link v-for="product in products.data" :key="product.id" :href="route('catalog.show', product.id)" class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition">
                    <div class="aspect-square bg-gray-100 relative">
                        <img v-if="product.primary_photo" :src="'/storage/' + product.primary_photo.path" :alt="product.name" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No Image</div>
                        
                        <div class="absolute top-2 right-2 bg-white bg-opacity-90 px-2 py-0.5 rounded text-[10px] font-bold text-green-800 shadow-sm">
                            {{ product.category?.name }}
                        </div>
                    </div>
                    
                    <div class="p-3 flex flex-col flex-grow">
                        <h3 class="text-sm font-bold text-gray-800 leading-snug line-clamp-2 mb-1" :title="product.name">{{ product.name }}</h3>
                        
                        <div class="text-green-700 font-bold text-base mt-auto">
                            {{ formatRupiah(product.selling_price) }}<span class="text-xs text-gray-500 font-normal">/{{ product.unit }}</span>
                        </div>
                        
                        
                    </div>
                </Link>
            </div>

            <!-- Pagination (if more than 1 page) -->
            <div v-if="products.last_page > 1" class="mt-8 flex justify-center">
                <div class="flex space-x-1">
                    <template v-for="(link, p) in products.links" :key="p">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 border rounded" :class="link.active ? 'bg-green-800 text-white border-green-800' : 'bg-white text-gray-700 border-gray-300'"></Link>
                        <span v-else v-html="link.label" class="px-3 py-1 border border-gray-300 rounded bg-gray-50 text-gray-400"></span>
                    </template>
                </div>
            </div>

        </div>
    </PublicLayout>
</template>