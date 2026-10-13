<?php

// 1. Create PublicLayout.vue
$layoutContent = <<<'EOT'
<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col pb-16 md:pb-0">
        <!-- Top Nav (Mobile: minimal, Desktop: full) -->
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('catalog.index')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-green-900" />
                            </Link>
                        </div>
                    </div>

                    <div class="hidden md:flex md:items-center md:ms-6">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm text-gray-700 underline">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-sm text-gray-700 underline mr-4">Log in</Link>
                            <Link :href="route('register')" class="text-sm text-gray-700 underline">Register</Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Bottom Nav (Mobile Only) -->
        <div class="md:hidden fixed bottom-0 w-full bg-white border-t border-gray-200 flex justify-around items-center h-16 z-40 px-2 pb-safe">
            <Link :href="route('catalog.index')" class="flex flex-col items-center justify-center w-full h-full text-green-800">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-medium">Beranda</span>
            </Link>
            <!-- Add other links like Keranjang, Pesanan, Akun later -->
            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-[10px] font-medium">Akun</span>
            </Link>
            <Link v-else :href="route('login')" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                <span class="text-[10px] font-medium">Masuk</span>
            </Link>
        </div>
    </div>
</template>
EOT;

$layoutDir = __DIR__ . '/resources/js/Layouts';
if (!is_dir($layoutDir)) mkdir($layoutDir, 0755, true);
file_put_contents($layoutDir . '/PublicLayout.vue', $layoutContent);


// 2. Create Catalog/Index.vue
$indexContent = <<<'EOT'
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

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const applyFilters = () => {
    router.get(route('catalog.index'), {
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
                    <select v-model="searchRegency" class="w-full border-gray-300 rounded-md text-sm focus:border-green-700 focus:ring-green-700">
                        <option value="">Semua Wilayah</option>
                        <optgroup v-for="prov in provinces" :key="prov.id" :label="prov.name">
                            <option v-for="reg in prov.regencies" :key="reg.id" :value="reg.id">{{ reg.type }} {{ reg.name }}</option>
                        </optgroup>
                    </select>
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
                        
                        <div class="text-[10px] text-gray-500 mt-2 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Mitra: {{ product.partner?.name }}
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
EOT;

$catalogDir = __DIR__ . '/resources/js/Pages/Catalog';
if (!is_dir($catalogDir)) mkdir($catalogDir, 0755, true);
file_put_contents($catalogDir . '/Index.vue', $indexContent);

// 3. Create Catalog/Show.vue
$showContent = <<<'EOT'
<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const activePhotoIndex = ref(0);

// We will implement checkout later
const buy = () => {
    alert('Fitur Checkout akan segera hadir!');
};
</script>

<template>
    <Head :title="product.name" />

    <PublicLayout>
        <div class="max-w-4xl mx-auto bg-white sm:my-6 sm:rounded-xl sm:shadow-sm sm:border border-gray-100 overflow-hidden">
            
            <!-- Mobile Back Button -->
            <div class="sm:hidden p-4 sticky top-16 z-30 bg-white bg-opacity-90 backdrop-blur-sm border-b border-gray-100">
                <Link :href="route('catalog.index')" class="flex items-center text-green-800 text-sm font-semibold">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali
                </Link>
            </div>

            <div class="flex flex-col md:flex-row">
                <!-- Gallery -->
                <div class="w-full md:w-1/2 p-4">
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3 relative">
                        <img v-if="product.photos && product.photos.length > 0" :src="'/storage/' + product.photos[activePhotoIndex].path" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                    </div>
                    
                    <div v-if="product.photos && product.photos.length > 1" class="flex space-x-2 overflow-x-auto pb-2">
                        <button v-for="(photo, index) in product.photos" :key="photo.id" @click="activePhotoIndex = index" class="w-16 h-16 shrink-0 rounded-md overflow-hidden border-2" :class="activePhotoIndex === index ? 'border-green-600' : 'border-transparent opacity-70'">
                            <img :src="'/storage/' + photo.path" class="w-full h-full object-cover">
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div class="w-full md:w-1/2 p-4 sm:p-6 sm:pl-2 flex flex-col">
                    <div class="text-xs font-bold text-green-700 uppercase tracking-wider mb-2">
                        {{ product.category?.name }} &bull; {{ product.subcategory?.name }}
                    </div>
                    
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mb-2">{{ product.name }}</h1>
                    
                    <div class="text-3xl font-bold text-green-800 mb-4">
                        {{ formatRupiah(product.selling_price) }}<span class="text-lg text-gray-500 font-normal">/{{ product.unit }}</span>
                    </div>

                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-600 text-sm">Stok Tersedia:</span>
                            <span class="font-bold text-gray-900">{{ product.stock }} {{ product.unit }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 text-sm">Mitra Petani/Peternak:</span>
                            <span class="font-bold text-gray-900">{{ product.partner?.name }}</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-2">Jangkauan Pengiriman:</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="cov in product.coverages" :key="cov.id" class="px-2 py-1 bg-green-50 text-green-800 text-xs rounded border border-green-100">
                                {{ cov.regency?.type }} {{ cov.regency?.name }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-auto pt-6 border-t border-gray-100">
                        <button @click="buy" class="w-full bg-green-800 text-white font-bold text-lg py-3 rounded-lg hover:bg-green-900 transition shadow-sm flex justify-center items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Pesan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
EOT;
file_put_contents($catalogDir . '/Show.vue', $showContent);

echo "Catalog Vue files created successfully.\n";
