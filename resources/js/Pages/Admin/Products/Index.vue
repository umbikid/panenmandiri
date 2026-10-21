<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    partners: Array,
    regencies: Array,
    filters: Object,
});

const searchQuery = ref(props.filters.q || '');
const selectedCategory = ref(props.filters.category_id || '');
const selectedPartner = ref(props.filters.partner_id || '');
const selectedRegency = ref(props.filters.regency_id || '');
const selectedStatus = ref(props.filters.status || '');

const filter = () => {
    router.get(route('admin.products.index'), {
        q: searchQuery.value,
        category_id: selectedCategory.value,
        partner_id: selectedPartner.value,
        regency_id: selectedRegency.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const exportUrl = (format) => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('q', searchQuery.value);
    if (selectedCategory.value) params.append('category_id', selectedCategory.value);
    if (selectedPartner.value) params.append('partner_id', selectedPartner.value);
    if (selectedRegency.value) params.append('regency_id', selectedRegency.value);
    if (selectedStatus.value) params.append('status', selectedStatus.value);
    params.append('format', format);
    if (format === 'pdf') params.append('auto_print', '1');
    return route('admin.products.export') + '?' + params.toString();
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(val || 0);
};

// Modal Edit Harga Jual
const editingProduct = ref(null);
const priceForm = useForm({
    selling_price: 0,
});

const openPriceModal = (product) => {
    editingProduct.value = product;
    priceForm.selling_price = product.selling_price;
};

const savePrice = () => {
    if (!editingProduct.value) return;
    priceForm.patch(route('admin.products.update-price', editingProduct.value.id), {
        onSuccess: () => {
            editingProduct.value = null;
        },
    });
};

// Toggle Status Produk
const changeStatus = (product, newStatus) => {
    router.patch(route('admin.products.update-status', product.id), {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Kelola Produk & Harga - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-emerald-950">
                        Kelola Seluruh Produk & Penetapan Harga Jual
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Atur harga jual ke pembeli (katalog publik), pantau stok, dan ubah status tayang produk mitra.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="exportUrl('excel')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 shadow-2xs transition"
                        title="Unduh daftar produk & penetapan harga ke format Excel / CSV"
                    >
                        <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ekspor Excel (.xls)
                    </a>
                    <a
                        :href="exportUrl('pdf')"
                        target="_blank"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-800 hover:bg-red-100 border border-red-200 shadow-2xs transition"
                        title="Cetak atau simpan laporan produk sebagai PDF"
                    >
                        <svg class="w-4 h-4 mr-1.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak / PDF
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Filters & Search Bar -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-3 items-center justify-between">
                    <div class="w-full md:w-80">
                        <input
                            type="text"
                            v-model="searchQuery"
                            @keyup.enter="filter"
                            placeholder="Cari produk atau nama mitra..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                        />
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                        <!-- Filter Mitra -->
                        <select
                            v-model="selectedPartner"
                            @change="filter"
                            class="text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 max-w-[150px]"
                        >
                            <option value="">Semua Mitra</option>
                            <option v-for="p in partners" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>

                        <!-- Filter Lokasi Jangkauan -->
                        <select
                            v-model="selectedRegency"
                            @change="filter"
                            class="text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 max-w-[160px]"
                        >
                            <option value="">Semua Jangkauan</option>
                            <option v-for="r in regencies" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>

                        <!-- Filter Kategori -->
                        <select
                            v-model="selectedCategory"
                            @change="filter"
                            class="text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>

                        <!-- Filter Status -->
                        <select
                            v-model="selectedStatus"
                            @change="filter"
                            class="text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif (Tayang)</option>
                            <option value="hidden">Disembunyikan</option>
                            <option value="sold_out">Habis</option>
                        </select>

                        <button
                            @click="filter"
                            class="px-3.5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer"
                        >
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Product Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                                    <th class="py-3.5 px-4">Produk</th>
                                    <th class="py-3.5 px-4">Mitra Petani</th>
                                    <th class="py-3.5 px-4">Stok</th>
                                    <th class="py-3.5 px-4">Harga Modal Mitra</th>
                                    <th class="py-3.5 px-4">Harga Jual (Katalog)</th>
                                    <th class="py-3.5 px-4">Margin</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="products.data.length === 0">
                                    <td colspan="8" class="text-center py-8 text-gray-400">
                                        Tidak ada produk yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>

                                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50/70 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 shrink-0 overflow-hidden relative border border-gray-200">
                                                <img
                                                    v-if="product.primary_photo"
                                                    :src="'/storage/' + product.primary_photo.path"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div v-else class="w-full h-full flex items-center justify-center text-[8px] text-gray-400">No Img</div>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 block">{{ product.name }}</span>
                                                <span class="text-[10px] text-gray-500">{{ product.category?.name }} - {{ product.subcategory?.name }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-gray-700 font-medium">
                                        {{ product.partner?.name }}
                                        <span class="block text-[10px] text-gray-400">{{ product.partner?.phone }}</span>
                                    </td>

                                    <td class="py-3.5 px-4 font-semibold text-gray-800">
                                        {{ product.stock }} {{ product.unit }}
                                    </td>

                                    <td class="py-3.5 px-4 text-gray-600">
                                        {{ formatRupiah(product.partner_price) }}
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-emerald-800">{{ formatRupiah(product.selling_price) }}</span>
                                            <button
                                                @click="openPriceModal(product)"
                                                class="text-amber-600 hover:text-amber-700 p-1 rounded hover:bg-amber-50"
                                                title="Ubah harga jual"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <span
                                            class="font-semibold text-[11px]"
                                            :class="(product.selling_price - product.partner_price) >= 0 ? 'text-emerald-700' : 'text-red-600'"
                                        >
                                            {{ formatRupiah(product.selling_price - product.partner_price) }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <select
                                            :value="product.status"
                                            @change="changeStatus(product, $event.target.value)"
                                            class="text-[11px] font-medium py-1 px-2 rounded-lg border-gray-200 focus:ring-emerald-600 focus:border-emerald-600"
                                            :class="{
                                                'bg-emerald-50 text-emerald-800': product.status === 'active',
                                                'bg-gray-100 text-gray-600': product.status === 'hidden',
                                                'bg-red-50 text-red-700': product.status === 'sold_out',
                                            }"
                                        >
                                            <option value="active">Aktif</option>
                                            <option value="hidden">Sembunyi</option>
                                            <option value="sold_out">Habis</option>
                                        </select>
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <button
                                            @click="openPriceModal(product)"
                                            class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition"
                                        >
                                            Set Harga
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.links && products.links.length > 3" class="p-4 border-t border-gray-100 flex justify-center gap-1">
                        <Link
                            v-for="(link, idx) in products.links"
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

        <!-- Modal Ubah Harga Jual -->
        <div v-if="editingProduct" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Tetapkan Harga Jual Produk</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ editingProduct.name }}</p>
                    </div>
                    <button @click="editingProduct = null" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 text-xs space-y-1.5 border border-gray-100">
                    <div class="flex justify-between text-gray-600">
                        <span>Mitra Pemilik:</span>
                        <strong class="text-gray-800">{{ editingProduct.partner?.name }}</strong>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Harga Modal Mitra:</span>
                        <strong class="text-gray-800">{{ formatRupiah(editingProduct.partner_price) }} / {{ editingProduct.unit }}</strong>
                    </div>
                </div>

                <form @submit.prevent="savePrice" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Harga Jual ke Pembeli (Rp) per {{ editingProduct.unit }}
                        </label>
                        <input
                            type="number"
                            v-model="priceForm.selling_price"
                            min="0"
                            required
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 font-bold text-emerald-900"
                        />
                        <p class="text-[10px] text-gray-500 mt-1">
                            Harga ini yang akan tampil di katalog publik dan dilihat oleh pembeli.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="editingProduct = null"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="priceForm.processing"
                            class="px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-xs font-semibold shadow-sm transition"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
