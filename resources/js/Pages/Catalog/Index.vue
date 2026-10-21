<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    provinces: Array,
    filters: Object,
    metrics: {
        type: Object,
        default: () => ({ total_stock_rice: 0, total_stock_livestock: 0 })
    }
});

const formatNumber = (number) => {
    return new Intl.NumberFormat('id-ID').format(number || 0);
};

const searchCategory = ref(props.filters.category || '');
const searchRegency = ref(props.filters.regency_id || '');
const searchQuery = ref(props.filters.q || '');

let initialRegencyName = '';
if (props.filters.regency_id) {
    for (const prov of props.provinces) {
        const reg = prov.regencies.find(r => r.id == props.filters.regency_id);
        if (reg) {
            initialRegencyName = reg.name;
            break;
        }
    }
}
const searchRegencyName = ref(initialRegencyName);

const updateRegencyId = () => {
    let foundId = '';
    const query = (searchRegencyName.value || '').trim().toLowerCase();
    if (query) {
        for (const prov of props.provinces) {
            const reg = prov.regencies.find(r => 
                r.name.toLowerCase() === query || 
                (r.type && (r.type + ' ' + r.name).toLowerCase() === query)
            );
            if (reg) {
                foundId = reg.id;
                break;
            }
        }
    }
    
    // Jika input dikosongkan
    if (searchRegencyName.value === '') {
        foundId = '';
    }
    
    searchRegency.value = foundId;
};

const failedImages = ref({});

const handleImageError = (productId) => {
    failedImages.value[productId] = true;
};

const getProductImage = (product) => {
    if (failedImages.value[product.id]) {
        return null;
    }
    if (product.primary_photo?.path) {
        return '/storage/' + product.primary_photo.path;
    }
    if (product.photos && product.photos.length > 0 && product.photos[0]?.path) {
        return '/storage/' + product.photos[0].path;
    }
    return null;
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
            <!-- Header Judul Katalog & Menu B2B Banner -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            Koperasi Mitra Mandiri (KMM)
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                            Katalog Produk KMM
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Pasokan hasil panen pangan dan hewan ternak segar langsung dari petani & peternak terpercaya.
                        </p>
                    </div>

                    <!-- Shortcut B2B Landing Page -->
                    <Link
                        :href="route('b2b.index')"
                        class="inline-flex items-center justify-between sm:justify-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 hover:from-amber-400 hover:to-amber-300 text-emerald-950 font-bold text-sm shadow-sm hover:shadow-md transition group border border-amber-300/80"
                    >
                        <div class="flex items-center gap-2">
                            <span class="bg-emerald-950 text-amber-300 text-[10px] px-2 py-0.5 rounded font-black tracking-wider uppercase">B2B</span>
                            <span>Layanan Pengadaan & Grosir</span>
                        </div>
                        <svg class="w-4 h-4 text-emerald-950 group-hover:translate-x-1 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </Link>
                </div>

                <!-- Blok Total Stok Hasil Panen dan Ternak -->
                <div class="mt-5 bg-gradient-to-br from-emerald-900 via-green-900 to-emerald-950 rounded-2xl p-5 sm:p-6 text-white shadow-md border border-emerald-700/60 relative overflow-hidden">
                    <!-- Aksen dekoratif background -->
                    <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute -left-10 -top-10 w-44 h-44 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-emerald-700/60 mb-5 gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-emerald-800/90 border border-emerald-600/60 text-amber-400 shadow-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                                        Total Stok Hasil Panen dan Ternak
                                    </h2>
                                    <p class="text-xs text-emerald-200">
                                        Informasi ketersediaan pasokan komoditas aktif yang siap dipesan
                                    </p>
                                </div>
                            </div>

                            <div class="inline-flex items-center gap-1.5 text-xs text-amber-300 font-semibold bg-emerald-950/70 px-3 py-1.5 rounded-full border border-emerald-700/60 self-start sm:self-auto">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Real-Time KMM
                            </div>
                        </div>

                        <!-- Grid 2 Kartu Komoditas: Beras & Ternak -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Kartu Komoditas Beras -->
                            <div class="bg-emerald-950/50 hover:bg-emerald-950/70 border border-emerald-600/40 rounded-xl p-4 sm:p-5 transition flex items-center justify-between group shadow-xs">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 shadow-xs"></span>
                                        <span class="text-xs uppercase tracking-wider font-bold text-amber-300">Stok Komoditas Beras</span>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                            {{ formatNumber(metrics?.total_stock_rice || 0) }}
                                        </span>
                                        <span class="text-base font-bold text-amber-300">kg</span>
                                    </div>
                                    <p class="text-xs text-emerald-200/90">
                                        Varietas Pandan Wangi, IR64, Rojolele, IR 42, C4 & Lainnya
                                    </p>
                                </div>
                                <div class="p-3 bg-amber-400/20 text-amber-400 rounded-xl group-hover:scale-105 transition-transform shrink-0 border border-amber-400/30">
                                    <!-- Icon Karung Padi / Beras -->
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Kartu Komoditas Ternak -->
                            <div class="bg-emerald-950/50 hover:bg-emerald-950/70 border border-emerald-600/40 rounded-xl p-4 sm:p-5 transition flex items-center justify-between group shadow-xs">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-xs"></span>
                                        <span class="text-xs uppercase tracking-wider font-bold text-emerald-300">Stok Komoditas Ternak</span>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                            {{ formatNumber(metrics?.total_stock_livestock || 0) }}
                                        </span>
                                        <span class="text-base font-bold text-emerald-300">ekor</span>
                                    </div>
                                    <p class="text-xs text-emerald-200/90">
                                        Sapi, Kambing & Domba Sehat Terverifikasi
                                    </p>
                                </div>
                                <div class="p-3 bg-emerald-400/20 text-emerald-300 rounded-xl group-hover:scale-105 transition-transform shrink-0 border border-emerald-400/30">
                                    <!-- Icon Hewan Ternak -->
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                            <option v-for="reg in prov.regencies" :key="reg.id" :value="reg.name"></option>
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
                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                        <img 
                            v-if="getProductImage(product)" 
                            :src="getProductImage(product)" 
                            :alt="product.name" 
                            class="w-full h-full object-cover"
                            @error="handleImageError(product.id)"
                        >
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 text-xs gap-1 bg-gray-50">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>No Image</span>
                        </div>
                        
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