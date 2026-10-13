<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    orders: Array
});

const formatDate = (dateString) => {
    const d = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(d);
};

const formatRupiah = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
};
</script>

<template>
    <Head title="Riwayat Pembelian" />

    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mb-20">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Riwayat Pembelian</h1>

            <div v-if="orders.length === 0" class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada pembelian</h3>
                <p class="mt-1 text-sm text-gray-500">Anda belum pernah melakukan pemesanan produk apa pun.</p>
                <div class="mt-6">
                    <Link :href="route('catalog.index')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-700 hover:bg-green-800">
                        Mulai Belanja
                    </Link>
                </div>
            </div>

            <div v-else class="space-y-4">
                <div v-for="order in orders" :key="order.id" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col md:flex-row">
                    <!-- Photo -->
                    <div class="w-full md:w-48 h-48 md:h-full shrink-0 relative bg-gray-100">
                        <img 
                            v-if="order.items[0]?.product?.primary_photo" 
                            :src="'/storage/' + order.items[0].product.primary_photo.path" 
                            class="absolute inset-0 w-full h-full object-cover"
                        >
                        <div v-else class="absolute inset-0 flex items-center justify-center text-gray-400">
                            No Image
                        </div>
                    </div>
                    
                    <div class="p-4 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-semibold text-gray-500">{{ formatDate(order.created_at) }}</span>
                                    <h3 class="text-lg font-bold text-gray-900 mt-1">{{ order.items[0]?.title_snapshot }}</h3>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ order.status === 'pending' ? 'Menunggu Konfirmasi' : order.status }}
                                </span>
                            </div>
                            
                            <div class="mt-4 text-sm text-gray-600">
                                <p><strong>Jumlah Dibeli:</strong> {{ order.items[0]?.qty }} {{ order.items[0]?.unit }}</p>
                                <p><strong>Harga per Item (saat beli):</strong> {{ formatRupiah(order.items[0]?.selling_price_snapshot) }}</p>
                                <p><strong>Total Estimasi:</strong> {{ formatRupiah(order.total_estimate) }}</p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                            <a 
                                :href="order.whatsapp_url" 
                                target="_blank"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none"
                            >
                                Hubungi CS
                            </a>
                            <Link 
                                v-if="order.items[0]?.product_id"
                                :href="route('catalog.show', order.items[0].product_id)"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                            >
                                Beli Lagi
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
