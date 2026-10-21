<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    cart_items: Array,
    supported_regencies: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const form = useForm({
    delivery_regency_id: user.value.regency_id || (props.supported_regencies.length === 1 ? props.supported_regencies[0].id : ''),
    note: '',
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(number || 0);
};

const totalEstimate = computed(() => {
    return (props.cart_items || []).reduce((acc, item) => {
        const price = Number(item.product?.selling_price) || 0;
        const qty = Number(item.qty) || 0;
        return acc + (price * qty);
    }, 0);
});

const totalCount = computed(() => {
    return (props.cart_items || []).reduce((acc, item) => acc + (Number(item.qty) || 0), 0);
});

const updatingId = ref(null);

const updateQty = (item, newQty) => {
    if (newQty < 1) return;
    if (item.product && newQty > item.product.stock) {
        alert(`Jumlah melebihi stok yang tersedia (${item.product.stock} ${item.product.unit}).`);
        return;
    }
    updatingId.value = item.id;
    router.patch(route('cart.update', item.id), {
        qty: newQty,
    }, {
        preserveScroll: true,
        onFinish: () => {
            updatingId.value = null;
        },
    });
};

const removeItem = (item) => {
    if (!confirm(`Hapus "${item.product?.name}" dari keranjang?`)) return;
    router.delete(route('cart.destroy', item.id), {
        preserveScroll: true,
    });
};

const checkout = () => {
    form.post(route('cart.checkout'));
};
</script>

<template>
    <Head title="Keranjang Belanja" />

    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Keranjang Belanja</h1>
                        <p class="text-xs text-gray-500">
                            {{ cart_items.length }} produk di keranjang
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('catalog.index')"
                    class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1"
                >
                    &larr; Lanjut Belanja
                </Link>
            </div>

            <!-- Keranjang Kosong -->
            <div
                v-if="!cart_items || cart_items.length === 0"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center my-6"
            >
                <div class="w-20 h-20 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Keranjang Belanja Masih Kosong</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto mb-6">
                    Anda belum memasukkan produk beras atau hewan ternak ke keranjang. Jelajahi katalog dan pilih produk berkualitas kami.
                </p>
                <Link
                    :href="route('catalog.index')"
                    class="inline-flex items-center px-5 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-semibold text-xs rounded-xl shadow-sm transition"
                >
                    Mulai Belanja Sekarang &rarr;
                </Link>
            </div>

            <!-- Ada Isi Keranjang -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Daftar Produk di Keranjang -->
                <div class="lg:col-span-8 space-y-4">
                    <div
                        v-for="item in cart_items"
                        :key="item.id"
                        class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100 hover:border-emerald-200 transition space-y-3"
                    >
                        <div class="flex gap-4 items-start">
                            <!-- Foto Produk -->
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 relative">
                                <img
                                    v-if="item.product?.primary_photo?.path || item.product?.photos?.[0]?.path"
                                    :src="'/storage/' + (item.product.primary_photo?.path || item.product.photos[0].path)"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-[10px]">
                                    No Image
                                </div>
                            </div>

                            <!-- Detail Produk -->
                            <div class="flex-grow min-w-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">
                                            {{ item.product?.category?.name }}
                                        </div>
                                        <Link
                                            :href="route('catalog.show', item.product_id)"
                                            class="text-sm font-bold text-gray-900 hover:text-emerald-700 transition line-clamp-1"
                                        >
                                            {{ item.product?.name }}
                                        </Link>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            Mitra: <strong>{{ item.product?.partner?.name || '-' }}</strong>
                                        </p>
                                    </div>

                                    <!-- Tombol Hapus -->
                                    <button
                                        type="button"
                                        @click="removeItem(item)"
                                        class="text-gray-400 hover:text-red-600 transition p-1 cursor-pointer"
                                        title="Hapus dari keranjang"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-end justify-between gap-3 mt-3 pt-3 border-t border-gray-100">
                                    <div>
                                        <div class="text-xs text-gray-500">Harga Satuan:</div>
                                        <div class="text-sm font-bold text-emerald-800">
                                            {{ formatRupiah(item.product?.selling_price) }}
                                            <span class="text-xs font-normal text-gray-500">/ {{ item.product?.unit }}</span>
                                        </div>
                                    </div>

                                    <!-- Kuantitas Counter -->
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                            <button
                                                type="button"
                                                @click="updateQty(item, Number(item.qty) - 1)"
                                                :disabled="item.qty <= 1 || updatingId === item.id"
                                                class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-200 disabled:opacity-40 transition cursor-pointer font-bold"
                                            >
                                                &minus;
                                            </button>
                                            <span class="w-12 text-center text-xs font-bold text-gray-800">
                                                {{ item.qty }}
                                            </span>
                                            <button
                                                type="button"
                                                @click="updateQty(item, Number(item.qty) + 1)"
                                                :disabled="item.qty >= (item.product?.stock || 0) || updatingId === item.id"
                                                class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-200 disabled:opacity-40 transition cursor-pointer font-bold"
                                            >
                                                &plus;
                                            </button>
                                        </div>

                                        <div class="text-right ml-2">
                                            <div class="text-[10px] text-gray-400">Subtotal:</div>
                                            <div class="text-sm font-extrabold text-gray-900">
                                                {{ formatRupiah((item.product?.selling_price || 0) * item.qty) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Pesanan & Checkout (Right Sidebar) -->
                <div class="lg:col-span-4 bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-5 sticky top-24">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">
                        Ringkasan Belanja
                    </h3>

                    <!-- Wilayah Pengiriman -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Wilayah Tujuan Pengiriman <span class="text-red-500">*</span>
                        </label>
                        
                        <div v-if="supported_regencies && supported_regencies.length > 0">
                            <select
                                v-model="form.delivery_regency_id"
                                required
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            >
                                <option value="" disabled>Pilih Kota / Kabupaten Tujuan</option>
                                <option
                                    v-for="reg in supported_regencies"
                                    :key="reg.id"
                                    :value="reg.id"
                                >
                                    {{ reg.name }}
                                </option>
                            </select>
                            <p class="text-[10px] text-emerald-700 mt-1">
                                &check; Wilayah yang didukung oleh seluruh mitra produk di keranjang.
                            </p>
                        </div>
                        <div v-else class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-900">
                            ⚠️ Produk di keranjang memiliki cakupan area pengiriman yang berbeda-beda. Harap kurangi atau pesan terpisah agar wilayah pengiriman dapat dipenuhi.
                        </div>
                        <p v-if="form.errors.delivery_regency_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.delivery_regency_id }}
                        </p>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Catatan Pesanan (Opsional)
                        </label>
                        <textarea
                            v-model="form.note"
                            rows="2"
                            placeholder="Contoh: Titik patokan alamat, estimasi waktu kirim..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <!-- Rincian Biaya -->
                    <div class="space-y-2 pt-2 border-t border-gray-100 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Total Item</span>
                            <span class="font-bold text-gray-800">{{ totalCount }} unit</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Estimasi Total Produk</span>
                            <span class="font-bold text-gray-800">{{ formatRupiah(totalEstimate) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold text-emerald-950 pt-2 border-t border-gray-100">
                            <span>Total Estimasi</span>
                            <span class="text-emerald-800">{{ formatRupiah(totalEstimate) }}</span>
                        </div>
                    </div>

                    <!-- Validasi Profil WA -->
                    <div v-if="!user.phone" class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                        <p class="font-semibold">Nomor WhatsApp belum diisi</p>
                        <p class="text-[11px] mt-0.5">
                            Silakan lengkapi nomor WhatsApp di <Link :href="route('profile.edit')" class="underline font-bold">Profil Akun</Link> sebelum checkout.
                        </p>
                    </div>

                    <!-- Tombol Checkout -->
                    <div>
                        <button
                            type="button"
                            @click="checkout"
                            :disabled="form.processing || !user.phone || !form.delivery_regency_id || (supported_regencies.length === 0)"
                            class="w-full py-3 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/>
                            </svg>
                            <span>Checkout & Pesan ke CS</span>
                        </button>
                        <p class="text-[10px] text-gray-400 text-center mt-2">
                            Pesanan Anda akan dibuat dan Anda akan diarahkan ke WhatsApp Customer Service kami.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
