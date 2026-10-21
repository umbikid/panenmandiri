<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
});

// Modal state
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
    type: 'livestock',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'livestock';
    isModalOpen.value = true;
};

const openEditModal = (cat) => {
    isEditing.value = true;
    editingCategory.value = cat;
    form.clearErrors();
    form.name = cat.name;
    form.type = cat.type;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditing.value = false;
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (isEditing.value && editingCategory.value) {
        form.put(route('admin.categories.update', editingCategory.value.id), {
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => {
                closeModal();
            },
        });
    }
};

const deleteCategory = (cat) => {
    if (cat.products_count > 0) {
        alert('Kategori tidak dapat dihapus karena masih memiliki ' + cat.products_count + ' produk terdaftar.');
        return;
    }

    if (!confirm(`Hapus kategori "${cat.name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        return;
    }

    router.delete(route('admin.categories.destroy', cat.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Kelola Kategori - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-emerald-950">
                        Kelola Kategori Produk
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Atur daftar kategori komoditas, tambah kategori baru, atau ubah nama kategori produk.
                    </p>
                </div>
                <div>
                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-800 hover:bg-emerald-900 text-white shadow-sm transition cursor-pointer"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Kategori
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Tabel Kategori -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                            <thead class="bg-gray-50/80 text-gray-600 font-semibold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th scope="col" class="px-5 py-3.5 w-12 text-center">No</th>
                                    <th scope="col" class="px-5 py-3.5">Nama Kategori</th>
                                    <th scope="col" class="px-5 py-3.5">Tipe Komoditas</th>
                                    <th scope="col" class="px-5 py-3.5">Subkategori Terdaftar</th>
                                    <th scope="col" class="px-5 py-3.5 text-center">Produk Mitra</th>
                                    <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr
                                    v-for="(cat, index) in categories"
                                    :key="cat.id"
                                    class="hover:bg-gray-50/70 transition"
                                >
                                    <td class="px-5 py-4 text-center font-medium text-gray-500">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-900 text-sm">
                                            {{ cat.name }}
                                        </div>
                                        <span class="text-[11px] text-gray-400 font-mono">
                                            slug: {{ cat.slug }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span
                                            v-if="cat.type === 'rice'"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200"
                                        >
                                            🌾 Beras & Pangan
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"
                                        >
                                            🐄 Ternak & Qurban
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div v-if="cat.subcategories && cat.subcategories.length > 0" class="flex flex-wrap gap-1.5 max-w-md">
                                            <span
                                                v-for="sub in cat.subcategories"
                                                :key="sub.id"
                                                class="px-2 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700 font-medium"
                                            >
                                                {{ sub.name }}
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-400 italic text-[11px]">
                                            Belum ada subkategori
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span
                                            class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold"
                                            :class="cat.products_count > 0 ? 'bg-emerald-50 text-emerald-800' : 'bg-gray-100 text-gray-500'"
                                        >
                                            {{ cat.products_count }} produk
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="openEditModal(cat)"
                                                class="px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition cursor-pointer"
                                            >
                                                Ubah Nama
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteCategory(cat)"
                                                :disabled="cat.products_count > 0"
                                                :class="cat.products_count > 0 ? 'opacity-40 cursor-not-allowed text-gray-400 bg-gray-100' : 'text-red-700 bg-red-50 hover:bg-red-100 cursor-pointer'"
                                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition"
                                                :title="cat.products_count > 0 ? 'Tidak bisa dihapus (memiliki produk)' : 'Hapus kategori'"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="categories.length === 0">
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                                        Belum ada kategori yang ditambahkan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit Kategori -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">
                            {{ isEditing ? 'Ubah Nama Kategori' : 'Tambah Kategori Baru' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ isEditing ? 'Perbarui informasi dan nama kategori komoditas.' : 'Masukkan nama dan tipe kategori komoditas baru.' }}
                        </p>
                    </div>
                    <button
                        @click="closeModal"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <!-- Nama Kategori -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            maxlength="100"
                            placeholder="Contoh: Sapi, Kambing, Beras, Jagung..."
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Tipe Komoditas -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Tipe Komoditas <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3 mt-1">
                            <label
                                class="border rounded-lg p-3 flex items-center gap-2 cursor-pointer transition"
                                :class="form.type === 'livestock' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-950 font-bold' : 'border-gray-200 text-gray-700'"
                            >
                                <input
                                    type="radio"
                                    value="livestock"
                                    v-model="form.type"
                                    class="text-emerald-700 focus:ring-emerald-600"
                                />
                                <span class="text-xs">🐄 Ternak</span>
                            </label>

                            <label
                                class="border rounded-lg p-3 flex items-center gap-2 cursor-pointer transition"
                                :class="form.type === 'rice' ? 'border-amber-600 bg-amber-50/50 text-amber-950 font-bold' : 'border-gray-200 text-gray-700'"
                            >
                                <input
                                    type="radio"
                                    value="rice"
                                    v-model="form.type"
                                    class="text-amber-700 focus:ring-amber-600"
                                />
                                <span class="text-xs">🌾 Beras / Pangan</span>
                            </label>
                        </div>
                        <p v-if="form.errors.type" class="text-xs text-red-600 mt-1">
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 rounded-lg text-xs font-semibold bg-emerald-800 hover:bg-emerald-900 text-white shadow-sm transition cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Kategori') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
