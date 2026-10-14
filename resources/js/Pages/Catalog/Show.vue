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

import { router } from '@inertiajs/vue3';

const activePhotoIndex = ref(0);
const photoErrors = ref({});

const handlePhotoError = (index) => {
    photoErrors.value[index] = true;
};

const buy = () => {
    router.get(route('checkout.create', props.product.id));
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
                        <img 
                            v-if="product.photos && product.photos.length > 0 && !photoErrors[activePhotoIndex]" 
                            :src="'/storage/' + product.photos[activePhotoIndex].path" 
                            class="w-full h-full object-cover"
                            @error="handlePhotoError(activePhotoIndex)"
                        >
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-50">
                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Tidak ada gambar</span>
                        </div>
                    </div>
                    
                    <div v-if="product.photos && product.photos.length > 1" class="flex space-x-2 overflow-x-auto pb-2">
                        <button v-for="(photo, index) in product.photos" :key="photo.id" @click="activePhotoIndex = index" class="w-16 h-16 shrink-0 rounded-md overflow-hidden border-2 bg-gray-100 relative" :class="activePhotoIndex === index ? 'border-green-600' : 'border-transparent opacity-70'">
                            <img 
                                v-if="!photoErrors[index]" 
                                :src="'/storage/' + photo.path" 
                                class="w-full h-full object-cover"
                                @error="handlePhotoError(index)"
                            >
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-[10px]">
                                No Image
                            </div>
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
                        
                    </div>

                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-2">Jangkauan Pengiriman:</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="cov in product.coverages" :key="cov.id" class="px-2 py-1 bg-green-50 text-green-800 text-xs rounded border border-green-100">
                                {{ cov.regency?.name }}
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