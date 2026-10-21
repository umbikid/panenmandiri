<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    metrics: Object,
    recent_orders: Array,
    recent_products: Array,
});

const activeStockModal = ref(null); // 'rice' | 'livestock' | null

const openStockModal = (type) => {
    activeStockModal.value = type;
};

const closeStockModal = () => {
    activeStockModal.value = null;
};

const groupedLivestock = computed(() => {
    if (!props.metrics?.livestock_subcategories) return {};
    const groups = {};
    props.metrics.livestock_subcategories.forEach((sub) => {
        const cat = sub.category_name || 'Lainnya';
        if (!groups[cat]) {
            groups[cat] = {
                category_name: cat,
                total_stock: 0,
                items: [],
            };
        }
        groups[cat].items.push(sub);
        groups[cat].total_stock += sub.total_stock;
    });
    return groups;
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(val || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(new Date(dateStr));
};

const statusBadge = (status) => {
    switch (status) {
        case 'pending': return { label: 'Menunggu', class: 'bg-yellow-100 text-yellow-800 border-yellow-200' };
        case 'forwarded_to_cs': return { label: 'Diteruskan ke CS', class: 'bg-blue-100 text-blue-800 border-blue-200' };
        case 'confirmed': return { label: 'Dikonfirmasi', class: 'bg-emerald-100 text-emerald-800 border-emerald-200' };
        case 'completed': return { label: 'Selesai', class: 'bg-green-100 text-green-800 border-green-200' };
        case 'cancelled': return { label: 'Dibatalkan', class: 'bg-red-100 text-red-800 border-red-200' };
        default: return { label: status, class: 'bg-gray-100 text-gray-800 border-gray-200' };
    }
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-emerald-950">
                        Dashboard Admin / KMM
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Pusat kendali produk mitra, penyesuaian harga jual, dan pemantauan transaksi.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('admin.orders.index')"
                        class="inline-flex items-center px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition"
                    >
                        Lihat Transaksi
                    </Link>
                    <Link
                        :href="route('admin.products.index')"
                        class="inline-flex items-center px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-emerald-800 hover:bg-emerald-900 text-white shadow-sm transition"
                    >
                        Kelola Harga Produk
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                    <!-- Total Produk -->
                    <Link
                        :href="route('admin.products.index')"
                        class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:border-emerald-400 hover:shadow-md transition flex items-center gap-4 group"
                    >
                        <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-700 group-hover:bg-emerald-100 transition flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-emerald-800 transition flex items-center gap-1">
                                Total Produk
                                <span class="text-[10px] text-gray-300 group-hover:text-emerald-600 transition">&rarr;</span>
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ metrics.total_products }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Kelola produk & harga</p>
                        </div>
                    </Link>

                    <!-- Total Pesanan -->
                    <Link
                        :href="route('admin.orders.index')"
                        class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:border-amber-400 hover:shadow-md transition flex items-center gap-4 group"
                    >
                        <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-100 transition flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-amber-800 transition flex items-center gap-1">
                                Total Pesanan
                                <span class="text-[10px] text-gray-300 group-hover:text-amber-600 transition">&rarr;</span>
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ metrics.total_orders }}</p>
                            <span class="inline-block text-[10px] font-semibold text-amber-700 bg-amber-100/80 px-1.5 py-0.5 rounded mt-0.5">
                                {{ metrics.pending_orders }} menunggu
                            </span>
                        </div>
                    </Link>

                    <!-- Total Mitra -->
                    <Link
                        :href="route('admin.users.index', { role: 'mitra' })"
                        class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:border-green-400 hover:shadow-md transition flex items-center gap-4 group"
                    >
                        <div class="w-12 h-12 rounded-lg bg-green-50 text-green-700 group-hover:bg-green-100 transition flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-green-800 transition flex items-center gap-1">
                                Mitra Petani/Peternak
                                <span class="text-[10px] text-gray-300 group-hover:text-green-600 transition">&rarr;</span>
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ metrics.total_partners }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Lihat daftar mitra</p>
                        </div>
                    </Link>

                    <!-- Total Pembeli -->
                    <Link
                        :href="route('admin.users.index', { role: 'pembeli' })"
                        class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:border-blue-400 hover:shadow-md transition flex items-center gap-4 group"
                    >
                        <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-700 group-hover:bg-blue-100 transition flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-blue-800 transition flex items-center gap-1">
                                Pembeli / Donatur
                                <span class="text-[10px] text-gray-300 group-hover:text-blue-600 transition">&rarr;</span>
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ metrics.total_buyers }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Lihat daftar pembeli</p>
                        </div>
                    </Link>
                </div>

                <!-- Stok Komoditas Ringkas -->
                <div class="bg-gradient-to-r from-emerald-900 to-green-900 text-white rounded-2xl p-6 shadow-md border-b-4 border-amber-500 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <span class="text-xs font-bold text-amber-400 tracking-wider uppercase">Ringkasan Stok Komoditas</span>
                        <h3 class="text-lg font-bold mt-1">Total Ketersediaan Hasil Panen & Hewan</h3>
                        <p class="text-xs text-emerald-200 mt-0.5">Dihimpun dari seluruh produk mitra yang sedang aktif di pasar.</p>
                    </div>
                    <div class="flex items-center gap-4 sm:gap-6 flex-wrap">
                        <!-- Blok Stok Beras (Klik untuk rincian) -->
                        <button
                            type="button"
                            @click="openStockModal('rice')"
                            class="bg-white/10 hover:bg-white/20 active:scale-95 transition rounded-xl px-5 py-3 border border-white/20 text-left group cursor-pointer shadow-sm hover:border-amber-400 focus:outline-none"
                            title="Klik untuk melihat rincian stok subkategori beras"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] text-emerald-200 font-medium group-hover:text-white transition">Stok Beras</p>
                                <span class="text-[10px] text-amber-300 font-semibold group-hover:underline flex items-center gap-0.5">
                                    Lihat Rincian &rarr;
                                </span>
                            </div>
                            <p class="text-xl font-black text-amber-300 mt-0.5">
                                {{ metrics.total_stock_rice }} <span class="text-xs text-white font-normal">kg</span>
                            </p>
                            <p class="text-[10px] text-emerald-200/80 mt-0.5">Klik untuk detail subkategori</p>
                        </button>

                        <!-- Blok Stok Ternak (Klik untuk rincian) -->
                        <button
                            type="button"
                            @click="openStockModal('livestock')"
                            class="bg-white/10 hover:bg-white/20 active:scale-95 transition rounded-xl px-5 py-3 border border-white/20 text-left group cursor-pointer shadow-sm hover:border-amber-400 focus:outline-none"
                            title="Klik untuk melihat rincian stok subkategori ternak"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] text-emerald-200 font-medium group-hover:text-white transition">Stok Ternak</p>
                                <span class="text-[10px] text-amber-300 font-semibold group-hover:underline flex items-center gap-0.5">
                                    Lihat Rincian &rarr;
                                </span>
                            </div>
                            <p class="text-xl font-black text-amber-300 mt-0.5">
                                {{ metrics.total_stock_livestock }} <span class="text-xs text-white font-normal">ekor</span>
                            </p>
                            <p class="text-[10px] text-emerald-200/80 mt-0.5">Klik untuk detail subkategori</p>
                        </button>
                    </div>
                </div>

                <!-- 2 Columns: Pesanan Terbaru & Produk Terbaru -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Pesanan Terbaru -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Pesanan Masuk Terbaru</h3>
                                <p class="text-[11px] text-gray-400">Transaksi checkout dari pembeli</p>
                            </div>
                            <Link :href="route('admin.orders.index')" class="text-xs font-semibold text-emerald-700 hover:text-amber-600">
                                Lihat Semua &rarr;
                            </Link>
                        </div>

                        <div v-if="recent_orders.length === 0" class="p-8 text-center text-sm text-gray-400">
                            Belum ada transaksi pesanan yang tercatat.
                        </div>

                        <div v-else class="divide-y divide-gray-100">
                            <div v-for="order in recent_orders" :key="order.id" class="p-4 hover:bg-gray-50/80 transition flex items-center justify-between">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-mono font-bold text-gray-900">{{ order.code }}</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-medium border" :class="statusBadge(order.status).class">
                                            {{ statusBadge(order.status).label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600">
                                        <strong>{{ order.buyer?.name }}</strong> &bull; {{ order.delivery_regency?.name || 'Tujuan -' }}
                                    </p>
                                    <p class="text-[10px] text-gray-400">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-emerald-800">{{ formatRupiah(order.total_estimate) }}</p>
                                    <Link :href="route('admin.orders.index')" class="text-[11px] text-amber-600 hover:underline">
                                        Detail
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Produk Terbaru -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Produk Terbaru Mitra</h3>
                                <p class="text-[11px] text-gray-400">Didaftarkan oleh mitra petani/peternak</p>
                            </div>
                            <Link :href="route('admin.products.index')" class="text-xs font-semibold text-emerald-700 hover:text-amber-600">
                                Kelola &rarr;
                            </Link>
                        </div>

                        <div v-if="recent_products.length === 0" class="p-8 text-center text-sm text-gray-400">
                            Belum ada produk yang didaftarkan oleh mitra.
                        </div>

                        <div v-else class="divide-y divide-gray-100">
                            <div v-for="product in recent_products" :key="product.id" class="p-4 hover:bg-gray-50/80 transition flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 overflow-hidden relative border border-gray-200">
                                    <img
                                        v-if="product.primary_photo"
                                        :src="'/storage/' + product.primary_photo.path"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-[10px] text-gray-400">Foto</div>
                                </div>

                                <div class="flex-grow min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ product.name }}</p>
                                    <p class="text-[11px] text-gray-500">
                                        Mitra: <span class="font-medium text-gray-700">{{ product.partner?.name }}</span>
                                    </p>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] text-gray-400">
                                        <span>Stok: {{ product.stock }} {{ product.unit }}</span>
                                        &bull;
                                        <span>Modal: {{ formatRupiah(product.partner_price) }}</span>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-[10px] font-semibold text-gray-400 block">Harga Jual:</span>
                                    <span class="text-xs font-bold text-emerald-700">{{ formatRupiah(product.selling_price) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Rincian Stok Subkategori -->
        <div
            v-if="activeStockModal"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs overflow-y-auto flex justify-center items-start sm:items-center p-3 sm:p-4"
        >
            <div
                class="bg-white rounded-2xl max-w-lg w-full shadow-2xl my-3 sm:my-8 flex flex-col max-h-[92vh] sm:max-h-[90vh] overflow-hidden border border-gray-100"
            >
                <!-- Modal Header -->
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center shrink-0 bg-white">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 font-bold"
                            :class="activeStockModal === 'rice' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                        >
                            <!-- Rice icon -->
                            <svg v-if="activeStockModal === 'rice'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <!-- Livestock icon -->
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">
                                {{ activeStockModal === 'rice' ? 'Rincian Stok Subkategori Beras' : 'Rincian Stok Subkategori Ternak' }}
                            </h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                <span v-if="activeStockModal === 'rice'">
                                    Total Ketersediaan Beras: <strong class="text-emerald-700">{{ metrics.total_stock_rice }} kg</strong>
                                </span>
                                <span v-else>
                                    Total Ketersediaan Ternak: <strong class="text-emerald-700">{{ metrics.total_stock_livestock }} ekor</strong>
                                </span>
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeStockModal"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Export Toolbar -->
                <div class="px-5 py-2.5 bg-gray-50/90 border-b border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-600 font-medium text-[11px]">Ekspor Rincian Stok:</span>
                    <div class="flex items-center gap-2">
                        <a
                            :href="route('admin.dashboard.export-stock', { type: activeStockModal, format: 'excel' })"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 transition shadow-2xs"
                            title="Unduh format spreadsheet Excel"
                        >
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ekspor Excel (.xls)
                        </a>
                        <a
                            :href="route('admin.dashboard.export-stock', { type: activeStockModal, format: 'pdf', auto_print: 1 })"
                            target="_blank"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-red-50 text-red-800 hover:bg-red-100 border border-red-200 transition shadow-2xs"
                            title="Buka dokumen siap cetak atau simpan ke PDF"
                        >
                            <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak / PDF
                        </a>
                    </div>
                </div>

                <!-- Modal Body: Scrollable -->
                <div class="p-5 overflow-y-auto space-y-4 flex-grow text-xs">
                    <!-- Konten Beras -->
                    <div v-if="activeStockModal === 'rice'" class="space-y-2.5">
                        <div
                            v-for="sub in metrics.rice_subcategories"
                            :key="sub.id"
                            class="p-3 rounded-xl border border-gray-100 hover:border-emerald-200 transition bg-gray-50/70 flex items-center justify-between"
                        >
                            <div>
                                <h4 class="font-bold text-gray-900 text-xs">{{ sub.name }}</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    {{ sub.total_products > 0 ? `${sub.total_products} produk mitra terdaftar` : 'Belum ada produk terdaftar' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-sm font-black"
                                    :class="sub.total_stock > 0 ? 'text-emerald-800' : 'text-gray-400'"
                                >
                                    {{ sub.total_stock }} <span class="text-xs font-semibold text-gray-500">kg</span>
                                </span>
                                <div class="mt-0.5">
                                    <span
                                        v-if="sub.total_stock > 0"
                                        class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800"
                                    >
                                        Tersedia
                                    </span>
                                    <span
                                        v-else
                                        class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-gray-200/80 text-gray-500"
                                    >
                                        Kosong
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Konten Ternak -->
                    <div v-else-if="activeStockModal === 'livestock'" class="space-y-4">
                        <div
                            v-for="(group, catName) in groupedLivestock"
                            :key="catName"
                            class="border border-gray-200 rounded-xl p-3.5 bg-white space-y-2.5 shadow-xs"
                        >
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                    <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wide">
                                        Komoditas {{ catName }}
                                    </h4>
                                </div>
                                <span class="text-xs font-black text-emerald-800">
                                    Total: {{ group.total_stock }} ekor
                                </span>
                            </div>

                            <div class="space-y-1.5 pt-1">
                                <div
                                    v-for="sub in group.items"
                                    :key="sub.id"
                                    class="p-2.5 rounded-lg bg-gray-50 flex items-center justify-between text-xs"
                                >
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ sub.label }}</p>
                                        <p class="text-[10px] text-gray-400">
                                            {{ sub.total_products > 0 ? `${sub.total_products} produk mitra` : 'Belum ada produk' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="font-bold"
                                            :class="sub.total_stock > 0 ? 'text-emerald-800' : 'text-gray-400'"
                                        >
                                            {{ sub.total_stock }} ekor
                                        </span>
                                        <span
                                            v-if="sub.total_stock > 0"
                                            class="block text-[9px] font-semibold text-emerald-700"
                                        >
                                            Tersedia
                                        </span>
                                        <span
                                            v-else
                                            class="block text-[9px] font-semibold text-gray-400"
                                        >
                                            Kosong
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex justify-between items-center shrink-0">
                    <Link
                        :href="route('admin.products.index')"
                        class="text-xs font-semibold text-emerald-800 hover:text-emerald-900 hover:underline flex items-center gap-1"
                    >
                        Kelola Seluruh Produk &rarr;
                    </Link>

                    <button
                        type="button"
                        @click="closeStockModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-xs font-semibold text-gray-800 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
