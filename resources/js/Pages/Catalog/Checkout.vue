<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const hasImageError = ref(false);

const props = defineProps({
    product: Object
});

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    qty: 1,
    delivery_regency_id: '',
    note: ''
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const totalEstimate = computed(() => {
    return props.product.selling_price * form.qty;
});

const submit = () => {
    form.post(route('checkout.store', props.product.id));
};
</script>

<template>
    <Head title="Checkout" />

    <PublicLayout>
        <div class="max-w-3xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout Pesanan</h1>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-100 flex items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded overflow-hidden mr-4 shrink-0 relative flex items-center justify-center">
                        <img 
                            v-if="(product.primary_photo?.path || product.photos?.[0]?.path) && !hasImageError" 
                            :src="'/storage/' + (product.primary_photo?.path || product.photos[0].path)" 
                            class="w-full h-full object-cover"
                            @error="hasImageError = true"
                        >
                        <div v-else class="text-gray-400 text-[10px] text-center">
                            No Image
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">{{ product.name }}</h3>
                        <div class="text-green-700 font-bold">
                            {{ formatRupiah(product.selling_price) }} <span class="text-xs text-gray-500 font-normal">/ {{ product.unit }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 flex justify-between items-center">
                    <span class="text-gray-600">Sisa Stok:</span>
                    <span class="font-bold">{{ product.stock }} {{ product.unit }}</span>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sm:p-6">
                <!-- Data Pembeli -->
                <div class="mb-6 pb-6 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-3">Informasi Pembeli</h3>
                    <div class="text-sm text-gray-600">
                        <p><span class="font-semibold w-24 inline-block">Nama</span> : {{ user.name }}</p>
                        <p><span class="font-semibold w-24 inline-block">WhatsApp</span> : {{ user.phone || '-' }}</p>
                        <Link v-if="!user.phone" :href="route('profile.edit')" class="text-green-700 underline text-xs mt-1 inline-block">Lengkapi profil Anda</Link>
                    </div>
                </div>

                <!-- Form Order -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pesanan ({{ product.unit }})</label>
                        <div class="flex items-center">
                            <button type="button" @click="form.qty > 1 ? form.qty-- : null" class="w-10 h-10 border border-gray-300 rounded-l flex items-center justify-center bg-gray-50 text-gray-600 hover:bg-gray-100">-</button>
                            <input type="number" v-model="form.qty" min="1" :max="product.stock" class="w-20 h-10 border-y border-x-0 border-gray-300 text-center focus:ring-0 focus:border-green-600">
                            <button type="button" @click="form.qty < product.stock ? form.qty++ : null" class="w-10 h-10 border border-gray-300 rounded-r flex items-center justify-center bg-gray-50 text-gray-600 hover:bg-gray-100">+</button>
                        </div>
                        <p v-if="form.errors.qty" class="text-red-500 text-xs mt-1">{{ form.errors.qty }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Wilayah Tujuan Pengiriman</label>
                        <select v-model="form.delivery_regency_id" required class="w-full border-gray-300 rounded-md focus:border-green-700 focus:ring-green-700">
                            <option value="" disabled>Pilih wilayah pengiriman</option>
                            <option v-for="cov in product.coverages" :key="cov.id" :value="cov.regency_id">
                                {{ cov.regency?.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.delivery_regency_id" class="text-red-500 text-xs mt-1">{{ form.errors.delivery_regency_id }}</p>
                        <p class="text-xs text-gray-500 mt-1">Hanya mencakup wilayah yang didukung oleh Mitra.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea v-model="form.note" rows="2" class="w-full border-gray-300 rounded-md focus:border-green-700 focus:ring-green-700" placeholder="Misal: Patokan alamat, jam pengiriman yang diharapkan..."></textarea>
                        <p v-if="form.errors.note" class="text-red-500 text-xs mt-1">{{ form.errors.note }}</p>
                    </div>
                </div>

                <!-- Total -->
                <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-lg text-gray-700">Total Estimasi:</span>
                    <span class="text-2xl font-bold text-green-800">{{ formatRupiah(totalEstimate) }}</span>
                </div>
                
                <div class="mt-6">
                    <button type="submit" :disabled="form.processing || !user.phone" class="w-full bg-green-800 text-white font-bold text-lg py-3 rounded-lg hover:bg-green-900 transition shadow-sm flex justify-center items-center disabled:opacity-50 disabled:cursor-not-allowed">
                        Lanjut ke WhatsApp CS
                    </button>
                    <p v-if="!user.phone" class="text-center text-red-500 text-xs mt-2">Anda harus melengkapi Nomor WhatsApp di profil Anda sebelum dapat memesan.</p>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
