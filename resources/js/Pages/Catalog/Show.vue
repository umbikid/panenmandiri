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
const selectedQty = ref(1);
const addingToCart = ref(false);

const handlePhotoError = (index) => {
    photoErrors.value[index] = true;
};

const addToCart = () => {
    addingToCart.value = true;
    router.post(route('cart.store'), {
        product_id: props.product.id,
        qty: selectedQty.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            addingToCart.value = false;
        },
    });
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

                    <!-- Deskripsi Produk -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-2">Deskripsi Produk:</h3>
                        <div v-if="product.description" class="text-sm text-gray-700 whitespace-pre-line leading-relaxed bg-gray-50/80 rounded-lg p-3.5 border border-gray-100">
                            {{ product.description }}
                        </div>
                        <p v-else class="text-xs text-gray-400 italic">
                            Belum ada rincian deskripsi tambahan untuk produk ini.
                        </p>
                    </div>

                    <!-- Jangkauan Pengiriman -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-2">Jangkauan Pengiriman:</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="cov in product.coverages" :key="cov.id" class="px-2 py-1 bg-green-50 text-green-800 text-xs rounded border border-green-100">
                                {{ cov.regency?.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Pilihan Kuantitas -->
                    <div class="mb-6 p-3.5 bg-gray-50/90 rounded-xl border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700">Jumlah Pesanan:</span>
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white shadow-2xs">
                                <button
                                    type="button"
                                    @click="selectedQty > 1 ? selectedQty-- : null"
                                    :disabled="selectedQty <= 1"
                                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-40 font-bold transition cursor-pointer"
                                >
                                    &minus;
                                </button>
                                <input
                                    type="number"
                                    v-model.number="selectedQty"
                                    min="1"
                                    :max="product.stock"
                                    class="w-14 h-8 border-0 text-center text-xs font-bold text-gray-900 focus:ring-0 p-0"
                                />
                                <button
                                    type="button"
                                    @click="selectedQty < product.stock ? selectedQty++ : null"
                                    :disabled="selectedQty >= product.stock"
                                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-40 font-bold transition cursor-pointer"
                                >
                                    &plus;
                                </button>
                            </div>
                        </div>
                        <div class="text-right text-[10px] text-gray-500 mt-1">
                            Tersedia: <strong>{{ product.stock }} {{ product.unit }}</strong>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                        <button
                            type="button"
                            @click="addToCart"
                            :disabled="addingToCart || product.stock <= 0"
                            class="flex-1 bg-amber-50 hover:bg-amber-100 text-amber-950 font-bold text-sm py-3 px-4 rounded-xl border-2 border-amber-400 transition shadow-2xs flex justify-center items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span>{{ addingToCart ? 'Menambahkan...' : '+ Keranjang' }}</span>
                        </button>

                        <button
                            type="button"
                            @click="buy"
                            :disabled="product.stock <= 0"
                            class="flex-1 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-md flex justify-center items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span>Beli Langsung</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>