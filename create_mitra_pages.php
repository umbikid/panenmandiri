<?php

$dir = __DIR__ . '/resources/js/Pages/Mitra';
if (!is_dir($dir)) mkdir($dir, 0777, true);
if (!is_dir($dir . '/Product')) mkdir($dir . '/Product', 0777, true);

$files = [
    'Dashboard.vue' => <<<EOT
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    totalProducts: Number,
    totalStock: Number,
    lowStockProducts: Number,
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Cards -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm">Total Produk</div>
                        <div class="text-3xl font-bold text-green-700">{{ totalProducts }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm">Total Stok</div>
                        <div class="text-3xl font-bold text-blue-700">{{ totalStock }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm">Stok Menipis</div>
                        <div class="text-3xl font-bold text-red-600">{{ lowStockProducts }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <Link :href="route('mitra.products.index')" class="bg-green-700 text-white px-4 py-2 rounded-md hover:bg-green-800 inline-block">
                        Kelola Produk
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
EOT,
    'Product/Index.vue' => <<<EOT
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    products: Array,
});
</script>

<template>
    <Head title="Kelola Produk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Produk</h2>
                <Link :href="route('mitra.products.create')" class="bg-green-700 text-white px-4 py-2 rounded text-sm hover:bg-green-800">
                    + Tambah Produk
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Stok</th>
                                <th class="py-3 px-4">Harga Mitra</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in products" :key="product.id" class="border-b">
                                <td class="py-3 px-4">{{ product.name }}</td>
                                <td class="py-3 px-4">{{ product.category?.name }} - {{ product.subcategory?.name }}</td>
                                <td class="py-3 px-4">{{ product.stock }} {{ product.unit }}</td>
                                <td class="py-3 px-4">Rp {{ product.partner_price }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded text-xs" :class="product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                        {{ product.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <Link :href="route('mitra.products.edit', product.id)" class="text-blue-600 hover:underline">Edit</Link>
                                </td>
                            </tr>
                            <tr v-if="products.length === 0">
                                <td colspan="6" class="text-center py-4 text-gray-500">Belum ada produk.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
EOT,
    'Product/Create.vue' => <<<EOT
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    categories: Array,
    provinces: Array,
});

const form = useForm({
    name: '',
    category_id: '',
    subcategory_id: '',
    stock: '',
    unit: '',
    price: '',
    regency_ids: [],
    photos: []
});

const activeCategory = computed(() => {
    return props.categories.find(c => c.id === form.category_id);
});

const subcategories = computed(() => {
    return activeCategory.value ? activeCategory.value.subcategories : [];
});

const allowedUnits = computed(() => {
    if (activeCategory.value && activeCategory.value.allowed_units) {
        return JSON.parse(activeCategory.value.allowed_units);
    }
    return [];
});

const submit = () => {
    form.post(route('mitra.products.store'));
};
</script>

<template>
    <Head title="Tambah Produk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('mitra.products.index')" class="text-gray-500 hover:text-gray-700">
                    &larr; Kembali
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Produk Baru</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Nama Produk -->
                        <div>
                            <InputLabel for="name" value="Nama Produk (maks 200 karakter)" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                maxlength="200"
                            />
                            <div class="text-right text-xs text-gray-500 mt-1">{{ form.name.length }}/200</div>
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Kategori & Subkategori -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="category_id" value="Kategori" />
                                <select v-model="form.category_id" id="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required @change="form.subcategory_id = ''; form.unit = ''">
                                    <option value="" disabled>Pilih Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.category_id" />
                            </div>

                            <div>
                                <InputLabel for="subcategory_id" value="Sub Kategori" />
                                <select v-model="form.subcategory_id" id="subcategory_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required :disabled="!form.category_id">
                                    <option value="" disabled>Pilih Sub Kategori</option>
                                    <option v-for="sub in subcategories" :key="sub.id" :value="sub.id">{{ sub.label }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.subcategory_id" />
                            </div>
                        </div>

                        <!-- Stok & Satuan -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="col-span-2">
                                <InputLabel for="stock" value="Jumlah Stok" />
                                <TextInput
                                    id="stock"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    v-model="form.stock"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.stock" />
                            </div>

                            <div class="col-span-2">
                                <InputLabel for="unit" value="Satuan" />
                                <select v-model="form.unit" id="unit" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required :disabled="!form.category_id">
                                    <option value="" disabled>Pilih Satuan</option>
                                    <option v-for="unit in allowedUnits" :key="unit" :value="unit">{{ unit }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.unit" />
                            </div>
                        </div>

                        <!-- Harga -->
                        <div>
                            <InputLabel for="price" value="Harga Mitra (per satuan)" />
                            <div class="flex items-center mt-1">
                                <span class="px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-md text-gray-500">Rp</span>
                                <TextInput
                                    id="price"
                                    type="number"
                                    min="0"
                                    class="block w-full rounded-l-none"
                                    v-model="form.price"
                                    required
                                />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Harga yang Anda patok. Harga jual ke pembeli akan ditetapkan oleh Admin.</p>
                            <InputError class="mt-2" :message="form.errors.price" />
                        </div>

                        <!-- Wilayah -->
                        <div>
                            <InputLabel value="Jangkauan Area Penjualan (Kota/Kabupaten)" />
                            <div class="mt-2 border border-gray-300 rounded-md p-4 max-h-64 overflow-y-auto bg-gray-50">
                                <div v-for="prov in provinces" :key="prov.id" class="mb-4">
                                    <div class="font-semibold text-gray-700 mb-2">{{ prov.name }}</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-2">
                                        <label v-for="reg in prov.regencies" :key="reg.id" class="inline-flex items-center">
                                            <input type="checkbox" :value="reg.id" v-model="form.regency_ids" class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                            <span class="ml-2 text-sm text-gray-600">{{ reg.type }} {{ reg.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.regency_ids" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton class="ml-4 bg-green-700 hover:bg-green-800" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan & Tayangkan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
EOT,
];

foreach ($files as $name => $content) {
    file_put_contents($dir . '/' . $name, $content);
}
