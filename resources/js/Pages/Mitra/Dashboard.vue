<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    totalProducts: Number,
    lowStockProducts: Number,
    stockPerCategory: Array,
});
</script>

<template>
    <Head title="Mitra Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Mitra</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Cards -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm">Total Produk</div>
                        <div class="text-3xl font-bold text-green-700">{{ totalProducts }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm">Stok Menipis</div>
                        <div class="text-3xl font-bold text-red-600">{{ lowStockProducts }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Total Stok Berdasarkan Kategori</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div v-for="cat in stockPerCategory" :key="cat.name" class="bg-gray-50 border border-gray-200 rounded p-4 text-center">
                            <div class="text-gray-600 font-semibold mb-1">{{ cat.name }}</div>
                            <div class="text-2xl font-bold text-green-800">{{ cat.stock }} <span class="text-sm font-normal text-gray-500">{{ cat.unit }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <Link :href="route('mitra.products.index')" class="bg-green-800 text-white px-4 py-2 rounded-md hover:bg-green-900 inline-block">
                        Kelola Produk
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>