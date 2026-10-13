<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const searchQuery = ref(props.filters.q || '');
const selectedStatus = ref(props.filters.status || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const filter = () => {
    router.get(route('admin.orders.index'), {
        q: searchQuery.value,
        status: selectedStatus.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
    startDate.value = '';
    endDate.value = '';
    filter();
};

const formatDateOnly = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            const date = new Date(parts[0], parts[1] - 1, parts[2]);
            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric'
            }).format(date);
        }
        return dateStr;
    } catch (e) {
        return dateStr;
    }
};

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

const updateOrderStatus = (order, newStatus) => {
    if (newStatus === 'confirmed' && !confirm(`Konfirmasi pesanan ${order.code}? Stok produk akan otomatis dikurangi dari mitra.`)) {
        return;
    }
    if (newStatus === 'cancelled' && !confirm(`Batalkan pesanan ${order.code}? Stok yang telah dikurangi akan dikembalikan ke mitra.`)) {
        return;
    }

    router.patch(route('admin.orders.update-status', order.id), {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};

const getWaLink = (phone, name, orderCode, isPartner = false) => {
    if (!phone) return '#';
    let cleanPhone = phone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.slice(1);
    
    const message = isPartner
        ? `Halo Mitra ${name}, kami dari Admin Panen Mandiri / KMM menginformasikan adanya pesanan baru (${orderCode}). Mohon kesiapan stoknya.`
        : `Halo ${name}, kami dari Admin Panen Mandiri / KMM menindaklanjuti pesanan Anda (${orderCode}).`;

    return `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
};
</script>

<template>
    <Head title="Manajemen Transaksi & Pesanan - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-emerald-950">
                        Manajemen Transaksi & Pesanan Masuk
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Pantau pesanan pembeli, hubungi pembeli dan mitra via WhatsApp, serta konfirmasi untuk pengurangan stok.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Filters -->
                <div class="bg-white p-4 sm:p-5 rounded-xl shadow-sm border border-gray-100 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        <!-- Pencarian Kode / Pembeli -->
                        <div class="lg:col-span-4">
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                Pencarian Transaksi
                            </label>
                            <input
                                type="text"
                                v-model="searchQuery"
                                @keyup.enter="filter"
                                placeholder="Cari kode pesanan atau nama pembeli..."
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            />
                        </div>

                        <!-- Status Pesanan -->
                        <div class="lg:col-span-3">
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                Status Pesanan
                            </label>
                            <select
                                v-model="selectedStatus"
                                @change="filter"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            >
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu Konfirmasi</option>
                                <option value="confirmed">Dikonfirmasi (Stok Dikurangi)</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>

                        <!-- Rentang Tanggal: Mulai Tanggal -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                Mulai Tanggal
                            </label>
                            <input
                                type="date"
                                v-model="startDate"
                                @change="filter"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            />
                        </div>

                        <!-- Rentang Tanggal: Sampai Tanggal -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                Sampai Tanggal
                            </label>
                            <input
                                type="date"
                                v-model="endDate"
                                @change="filter"
                                :min="startDate"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            />
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="lg:col-span-1 flex items-center gap-1.5">
                            <button
                                @click="filter"
                                class="w-full py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center justify-center gap-1 cursor-pointer"
                                title="Terapkan filter"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                <span class="lg:hidden">Filter</span>
                            </button>

                            <button
                                v-if="searchQuery || selectedStatus || startDate || endDate"
                                @click="resetFilter"
                                class="px-2.5 py-2 border border-gray-300 hover:bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold transition cursor-pointer shrink-0"
                                title="Reset seluruh filter"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Indikator Aktif Filter Rentang Waktu -->
                    <div
                        v-if="startDate || endDate"
                        class="pt-2 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs"
                    >
                        <div class="flex items-center gap-2 text-gray-600">
                            <span class="font-semibold text-emerald-800 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Rentang Waktu:
                            </span>
                            <span class="bg-emerald-50 text-emerald-900 px-2 py-0.5 rounded-md border border-emerald-200 font-bold text-[11px]">
                                {{ startDate ? formatDateOnly(startDate) : 'Awal' }} s/d {{ endDate ? formatDateOnly(endDate) : 'Sekarang' }}
                            </span>
                        </div>
                        <button
                            @click="startDate = ''; endDate = ''; filter();"
                            class="text-[11px] text-red-600 hover:text-red-800 font-semibold hover:underline cursor-pointer"
                        >
                            Hapus Rentang Tanggal &times;
                        </button>
                    </div>
                </div>

                <!-- Orders List -->
                <div v-if="orders.data.length === 0" class="bg-white rounded-xl p-12 text-center text-sm text-gray-400 border border-gray-100">
                    Tidak ada transaksi pesanan yang sesuai dengan filter.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4 hover:border-emerald-300 transition"
                    >
                        <!-- Order Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-bold text-sm text-emerald-950 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">
                                    {{ order.code }}
                                </span>
                                <span class="text-xs text-gray-400">&bull;</span>
                                <span class="text-xs text-gray-500 font-medium">
                                    {{ formatDate(order.created_at) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-bold px-3 py-1 rounded-full border"
                                    :class="{
                                        'bg-yellow-50 text-yellow-800 border-yellow-200': order.status === 'pending',
                                        'bg-emerald-50 text-emerald-800 border-emerald-200': order.status === 'confirmed',
                                        'bg-green-50 text-green-800 border-green-200': order.status === 'completed',
                                        'bg-red-50 text-red-800 border-red-200': order.status === 'cancelled',
                                    }"
                                >
                                    {{
                                        order.status === 'pending' ? 'Menunggu Konfirmasi' :
                                        order.status === 'confirmed' ? 'Dikonfirmasi' :
                                        order.status === 'completed' ? 'Selesai' :
                                        order.status === 'cancelled' ? 'Dibatalkan' : order.status
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- 3 Columns: Pembeli, Produk & Mitra, Aksi Admin -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                            <!-- Info Pembeli -->
                            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-100 space-y-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Data Pembeli</span>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ order.buyer?.name }}</p>
                                    <p class="text-gray-500 text-[11px]">{{ order.buyer?.email }}</p>
                                </div>
                                <div class="text-[11px] text-gray-600">
                                    <p><strong>Tujuan Kirim:</strong> {{ order.delivery_regency?.name || '-' }}</p>
                                    <p v-if="order.note"><strong>Catatan:</strong> "{{ order.note }}"</p>
                                </div>

                                <a
                                    :href="getWaLink(order.buyer?.phone, order.buyer?.name, order.code, false)"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-xs shadow-xs transition"
                                >
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.173.45 0.742.965 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.679.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                                    Chat Pembeli ({{ order.buyer?.phone }})
                                </a>
                            </div>

                            <!-- Produk & Mitra -->
                            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-100 space-y-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Produk & Mitra Produsen</span>
                                <div v-for="item in order.items" :key="item.id" class="space-y-1">
                                    <p class="font-bold text-gray-900">{{ item.title_snapshot }}</p>
                                    <p class="text-emerald-800 font-semibold">
                                        {{ item.qty }} {{ item.unit }} &times; {{ formatRupiah(item.selling_price_snapshot) }}
                                    </p>
                                    <div class="pt-1 text-[11px] text-gray-600">
                                        <p><strong>Mitra:</strong> {{ item.product?.partner?.name }}</p>
                                        <p><strong>Lokasi Mitra:</strong> {{ item.product?.partner?.phone }}</p>
                                    </div>

                                    <a
                                        :href="getWaLink(item.product?.partner?.phone, item.product?.partner?.name, order.code, true)"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white font-medium rounded text-[11px] shadow-xs transition mt-1"
                                    >
                                        Hubungi Mitra
                                    </a>
                                </div>
                            </div>

                            <!-- Total & Aksi -->
                            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-100 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Ringkasan Nilai</span>
                                    <p class="text-lg font-black text-emerald-900 mt-1">{{ formatRupiah(order.total_estimate) }}</p>
                                    <p class="text-[10px] text-gray-400">Total estimasi pembayaran</p>
                                    
                                    <div class="mt-2 text-[10px]" :class="order.stock_deducted_at ? 'text-emerald-700' : 'text-gray-400'">
                                        <span v-if="order.stock_deducted_at">
                                            &check; Stok telah dikurangi ({{ formatDate(order.stock_deducted_at) }})
                                        </span>
                                        <span v-else>
                                            Stok belum dipotong
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="pt-3 border-t border-gray-200/80 flex flex-wrap gap-2">
                                    <button
                                        v-if="order.status === 'pending'"
                                        @click="updateOrderStatus(order, 'confirmed')"
                                        class="px-3 py-1.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-lg text-xs shadow-xs transition"
                                    >
                                        Konfirmasi Order
                                    </button>

                                    <button
                                        v-if="order.status === 'confirmed'"
                                        @click="updateOrderStatus(order, 'completed')"
                                        class="px-3 py-1.5 bg-green-700 hover:bg-green-800 text-white font-bold rounded-lg text-xs shadow-xs transition"
                                    >
                                        Tandai Selesai
                                    </button>

                                    <button
                                        v-if="order.status !== 'cancelled' && order.status !== 'completed'"
                                        @click="updateOrderStatus(order, 'cancelled')"
                                        class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 font-bold rounded-lg text-xs transition"
                                    >
                                        Batalkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links && orders.links.length > 3" class="p-4 flex justify-center gap-1">
                        <Link
                            v-for="(link, idx) in orders.links"
                            :key="idx"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1 text-xs rounded border"
                            :class="link.active ? 'bg-emerald-800 text-white border-emerald-800' : 'text-gray-600 border-gray-200 hover:bg-gray-50'"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
