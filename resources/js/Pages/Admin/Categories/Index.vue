<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
});

// Category Modal state
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

// Subcategory / Menu Kategori Modal state
const isSubModalOpen = ref(false);
const activeCategoryForSub = ref(null);
const editingSubId = ref(null);

const subForm = useForm({
    name: '',
    label: '',
    min_weight_kg: '',
    max_weight_kg: '',
});

const editSubForm = useForm({
    name: '',
    label: '',
    min_weight_kg: '',
    max_weight_kg: '',
});

const openSubcategoryModal = (cat) => {
    activeCategoryForSub.value = cat;
    editingSubId.value = null;
    subForm.reset();
    subForm.clearErrors();
    isSubModalOpen.value = true;
};

const closeSubcategoryModal = () => {
    isSubModalOpen.value = false;
    activeCategoryForSub.value = null;
    editingSubId.value = null;
    subForm.reset();
    subForm.clearErrors();
};

const addSubcategory = () => {
    if (!activeCategoryForSub.value) return;
    subForm.post(route('admin.categories.subcategories.store', activeCategoryForSub.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            subForm.reset();
            const updated = props.categories.find(c => c.id === activeCategoryForSub.value.id);
            if (updated) activeCategoryForSub.value = updated;
        },
    });
};

const startEditSub = (sub) => {
    editingSubId.value = sub.id;
    editSubForm.clearErrors();
    editSubForm.name = sub.name;
    editSubForm.label = sub.label || sub.name;
    editSubForm.min_weight_kg = sub.min_weight_kg || '';
    editSubForm.max_weight_kg = sub.max_weight_kg || '';
};

const cancelEditSub = () => {
    editingSubId.value = null;
    editSubForm.reset();
};

const updateSubcategory = (sub) => {
    if (!activeCategoryForSub.value) return;
    editSubForm.put(route('admin.categories.subcategories.update', [activeCategoryForSub.value.id, sub.id]), {
        preserveScroll: true,
        onSuccess: () => {
            editingSubId.value = null;
            const updated = props.categories.find(c => c.id === activeCategoryForSub.value.id);
            if (updated) activeCategoryForSub.value = updated;
        },
    });
};

const deleteSubcategory = (sub) => {
    if (!activeCategoryForSub.value) return;
    if (!confirm(`Hapus subkategori/menu "${sub.name}"?`)) return;

    router.delete(route('admin.categories.subcategories.destroy', [activeCategoryForSub.value.id, sub.id]), {
        preserveScroll: true,
        onSuccess: () => {
            const updated = props.categories.find(c => c.id === activeCategoryForSub.value.id);
            if (updated) activeCategoryForSub.value = updated;
        },
    });
};
</script>

<template>
    <Head title="Kelola Kategori & Menu - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-emerald-950">
                        Kelola Kategori & Menu Kategori
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Atur daftar kategori komoditas, tambah kategori baru, ubah nama, dan kelola menu subkategori.
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
                        Tambah Kategori Baru
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
                                    <th scope="col" class="px-5 py-3.5">Subkategori / Menu Terdaftar</th>
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
                                                class="px-2 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700 font-medium border border-gray-200"
                                            >
                                                {{ sub.name }}
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-400 italic text-[11px]">
                                            Belum ada subkategori / menu
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
                                                @click="openSubcategoryModal(cat)"
                                                class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition cursor-pointer flex items-center gap-1"
                                                title="Kelola menu / subkategori di kategori ini"
                                            >
                                                <span>Kelola Menu</span>
                                                <span class="text-[10px] bg-amber-200 text-amber-900 px-1 rounded-full font-bold">
                                                    {{ cat.subcategories?.length || 0 }}
                                                </span>
                                            </button>
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

        <!-- Modal Kelola Subkategori / Menu Kategori -->
        <div
            v-if="isSubModalOpen && activeCategoryForSub"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between shrink-0 bg-gray-50/60">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold" :class="activeCategoryForSub.type === 'rice' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                                {{ activeCategoryForSub.type === 'rice' ? '🌾 Beras' : '🐄 Ternak' }}
                            </span>
                            <h3 class="text-base font-bold text-gray-900">
                                Menu Subkategori: {{ activeCategoryForSub.name }}
                            </h3>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Tambah menu subkategori baru atau ubah nama dan varian subkategori yang sudah ada.
                        </p>
                    </div>
                    <button
                        @click="closeSubcategoryModal"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition cursor-pointer"
                    >
                        &times;
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-6">
                    <!-- Form Tambah Subkategori Baru -->
                    <div class="bg-emerald-50/50 border border-emerald-200/80 rounded-xl p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-900 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Tambah Subkategori / Menu Baru
                        </h4>

                        <form @submit.prevent="addSubcategory" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                        Nama Subkategori <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="subForm.name"
                                        required
                                        placeholder="Contoh: Premium, Super, IR 64..."
                                        class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                    />
                                    <p v-if="subForm.errors.name" class="text-[10px] text-red-600 mt-0.5">{{ subForm.errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                        Label Tampilan
                                    </label>
                                    <input
                                        type="text"
                                        v-model="subForm.label"
                                        placeholder="Contoh: Premium (29–35 kg) atau kosongkan"
                                        class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                    />
                                    <p v-if="subForm.errors.label" class="text-[10px] text-red-600 mt-0.5">{{ subForm.errors.label }}</p>
                                </div>

                                <!-- Extra livestock weights if applicable -->
                                <template v-if="activeCategoryForSub.type === 'livestock'">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                            Bobot Minimum (kg)
                                        </label>
                                        <input
                                            type="number"
                                            step="0.1"
                                            v-model="subForm.min_weight_kg"
                                            placeholder="Opsional, misal: 25"
                                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">
                                            Bobot Maksimum (kg)
                                        </label>
                                        <input
                                            type="number"
                                            step="0.1"
                                            v-model="subForm.max_weight_kg"
                                            placeholder="Opsional, misal: 35"
                                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                        />
                                    </div>
                                </template>
                            </div>

                            <div class="text-right pt-1">
                                <button
                                    type="submit"
                                    :disabled="subForm.processing"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-sm transition disabled:opacity-50 cursor-pointer"
                                >
                                    {{ subForm.processing ? 'Menambahkan...' : '+ Tambahkan Subkategori' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Daftar Subkategori yang Ada -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                            Daftar Menu Subkategori Saat Ini ({{ activeCategoryForSub.subcategories?.length || 0 }})
                        </h4>

                        <div class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100">
                            <div
                                v-for="sub in activeCategoryForSub.subcategories"
                                :key="sub.id"
                                class="p-3 bg-white hover:bg-gray-50/80 transition"
                            >
                                <!-- Mode Edit -->
                                <div v-if="editingSubId === sub.id" class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600">Nama Subkategori</label>
                                            <input
                                                type="text"
                                                v-model="editSubForm.name"
                                                required
                                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600">Label Tampilan</label>
                                            <input
                                                type="text"
                                                v-model="editSubForm.label"
                                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                            />
                                        </div>
                                        <template v-if="activeCategoryForSub.type === 'livestock'">
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-600">Min Bobot (kg)</label>
                                                <input
                                                    type="number"
                                                    step="0.1"
                                                    v-model="editSubForm.min_weight_kg"
                                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-600">Max Bobot (kg)</label>
                                                <input
                                                    type="number"
                                                    step="0.1"
                                                    v-model="editSubForm.max_weight_kg"
                                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                                />
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="cancelEditSub"
                                            class="px-2.5 py-1 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold cursor-pointer"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            type="button"
                                            @click="updateSubcategory(sub)"
                                            :disabled="editSubForm.processing"
                                            class="px-3 py-1 text-xs rounded bg-emerald-800 hover:bg-emerald-900 text-white font-semibold shadow-xs cursor-pointer"
                                        >
                                            {{ editSubForm.processing ? 'Menyimpan...' : 'Simpan' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Mode Tampil Normal -->
                                <div v-else class="flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-bold text-gray-900">{{ sub.name }}</span>
                                        <span v-if="sub.label && sub.label !== sub.name" class="ml-2 text-gray-500 font-normal">
                                            ({{ sub.label }})
                                        </span>
                                        <div v-if="sub.min_weight_kg || sub.max_weight_kg" class="text-[10px] text-gray-400 mt-0.5">
                                            Rentang bobot: {{ sub.min_weight_kg ? sub.min_weight_kg + 'kg' : '0' }} &mdash; {{ sub.max_weight_kg ? sub.max_weight_kg + 'kg' : 'bebas' }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button
                                            type="button"
                                            @click="startEditSub(sub)"
                                            class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition cursor-pointer"
                                        >
                                            Ubah
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteSubcategory(sub)"
                                            class="px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 hover:bg-red-100 transition cursor-pointer"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!activeCategoryForSub.subcategories || activeCategoryForSub.subcategories.length === 0" class="p-6 text-center text-gray-400 text-xs">
                                Belum ada subkategori pada kategori ini. Silakan tambahkan pada form di atas.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end shrink-0">
                    <button
                        type="button"
                        @click="closeSubcategoryModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-xs font-semibold text-gray-800 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
