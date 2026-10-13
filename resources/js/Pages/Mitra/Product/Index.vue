<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Array,
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const showDeleteModal = ref(false);
const productToDelete = ref(null);

const confirmDelete = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const deleteProduct = () => {
    if (productToDelete.value) {
        router.delete(route('mitra.products.destroy', productToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                productToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};
</script>

<template>
    <Head title="Kelola Produk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-green-900 leading-tight">Kelola Produk</h2>
                <Link :href="route('mitra.products.create')" class="bg-green-700 text-white px-4 py-2 rounded text-sm hover:bg-green-800">
                    + Tambah Produk
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                </div>

                <div v-if="products.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    Belum ada produk. Silakan tambah produk baru.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="product in products" :key="product.id" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                        <div class="flex p-4">
                            <!-- Thumbnail -->
                            <div class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden mr-4">
                                <img v-if="product.primary_photo" :src="'/storage/' + product.primary_photo.path" :alt="product.name" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No Image</div>
                            </div>
                            
                            <!-- Info -->
                            <div class="flex flex-col justify-center">
                                <h3 class="text-lg font-bold text-green-900 leading-tight line-clamp-2" :title="product.name">{{ product.name }}</h3>
                                <div class="text-xs text-gray-500 mt-1 mb-2">{{ product.category?.name }} &bull; {{ product.subcategory?.name }}</div>
                                
                                <div class="text-green-700 font-bold">{{ formatRupiah(product.partner_price) }}</div>
                                <div class="text-sm text-gray-600">Stok: {{ product.stock }} {{ product.unit }}</div>
                                
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold" :class="product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-green-900'">
                                        {{ product.status === 'active' ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="mt-auto border-t border-gray-100 bg-gray-50 p-3 flex justify-end space-x-2">
                            <Link :href="route('mitra.products.edit', product.id)" class="px-3 py-1.5 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition">
                                Edit
                            </Link>
                            <button @click="confirmDelete(product)" class="px-3 py-1.5 text-sm bg-red-50 text-red-600 border border-red-200 rounded hover:bg-red-100 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Konfirmasi Hapus</h3>
                <p class="text-gray-600 mb-6">Apakah Produk <strong>{{ productToDelete?.name }}</strong> akan dihapus?</p>
                
                <div class="flex justify-end space-x-3">
                    <button @click="cancelDelete" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                        Tidak
                    </button>
                    <button @click="deleteProduct" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition shadow-sm">
                        Ya
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>