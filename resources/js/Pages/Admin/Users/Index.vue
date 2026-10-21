<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const page = usePage();

const props = defineProps({
    users: Object,
    roles: Array,
    provinces: Array,
    filters: Object,
});

const searchQuery = ref(props.filters.q || '');
const selectedRole = ref(props.filters.role || '');

const filter = () => {
    router.get(route('admin.users.index'), {
        q: searchQuery.value,
        role: selectedRole.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const exportUrl = (format) => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('q', searchQuery.value);
    if (selectedRole.value) params.append('role', selectedRole.value);
    params.append('format', format);
    if (format === 'pdf') params.append('auto_print', '1');
    return route('admin.users.export') + '?' + params.toString();
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
    }).format(new Date(dateStr));
};

const toggleStatus = (user) => {
    const nextStatus = user.status === 'active' ? 'suspended' : 'active';
    const msg = nextStatus === 'suspended'
        ? `Tangguhkan akun ${user.name}? Pengguna ini tidak akan bisa login.`
        : `Aktifkan kembali akun ${user.name}?`;
    
    if (!confirm(msg)) return;

    router.patch(route('admin.users.update-status', user.id), {
        status: nextStatus,
    }, {
        preserveScroll: true,
    });
};

// Modal Detail Pengguna
const isDetailModalOpen = ref(false);
const selectedUser = ref(null);

const openDetailModal = (user) => {
    selectedUser.value = user;
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    selectedUser.value = null;
};

const getWhatsappUrl = (phone, name) => {
    if (!phone) return '#';
    let clean = phone.toString().replace(/\D/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    }
    const text = encodeURIComponent(`Halo ${name}, kami dari tim PanenMandiri...`);
    return `https://wa.me/${clean}?text=${text}`;
};

const isDeleting = ref(false);

const deleteUserPermanently = (user) => {
    if (!user) return;

    if (page.props.auth?.user && user.id === page.props.auth.user.id) {
        alert('Anda tidak dapat menghapus akun Anda sendiri.');
        return;
    }

    const confirmMsg = `PERINGATAN!\n\nApakah Anda yakin ingin menghapus pengguna "${user.name}" secara PERMANEN?\n\nSemua data akun, peran, serta data terkait akan dihapus dari sistem dan TIDAK DAPAT dipulihkan. Lanjutkan?`;
    if (!confirm(confirmMsg)) return;

    isDeleting.value = true;
    router.delete(route('admin.users.destroy', user.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDetailModal();
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

// Modal Tambah Pengguna Baru
const isCreateModalOpen = ref(false);
const regencies = ref([]);
const districts = ref([]);
const fetchingRegencies = ref(false);
const fetchingDistricts = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: 'pembeli',
    password: '',
    address: '',
    province_id: '',
    regency_id: '',
    district_id: '',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    regencies.value = [];
    districts.value = [];
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const fetchRegencies = async () => {
    form.regency_id = '';
    form.district_id = '';
    regencies.value = [];
    districts.value = [];

    if (!form.province_id) return;

    fetchingRegencies.value = true;
    try {
        const res = await axios.get(`/api/provinces/${form.province_id}/regencies`);
        regencies.value = res.data;
    } catch (err) {
        console.error('Gagal mengambil data kota', err);
    } finally {
        fetchingRegencies.value = false;
    }
};

const fetchDistricts = async () => {
    form.district_id = '';
    districts.value = [];

    if (!form.regency_id) return;

    fetchingDistricts.value = true;
    try {
        const res = await axios.get(`/api/regencies/${form.regency_id}/districts`);
        districts.value = res.data;
    } catch (err) {
        console.error('Gagal mengambil data kecamatan', err);
    } finally {
        fetchingDistricts.value = false;
    }
};

const submitUser = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            closeCreateModal();
        },
    });
};
</script>

<template>
    <Head title="Manajemen Pengguna - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-emerald-950">
                        Manajemen Pengguna
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Kelola data mitra produsen, pembeli/donatur, dan akun administrator.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="exportUrl('excel')"
                        class="inline-flex items-center px-3 py-2 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 shadow-2xs transition"
                        title="Unduh data pengguna ke format Excel / CSV"
                    >
                        <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ekspor Excel (.xls)
                    </a>
                    <a
                        :href="exportUrl('pdf')"
                        target="_blank"
                        class="inline-flex items-center px-3 py-2 text-xs font-semibold rounded-lg bg-red-50 text-red-800 hover:bg-red-100 border border-red-200 shadow-2xs transition"
                        title="Cetak atau simpan laporan pengguna sebagai PDF"
                    >
                        <svg class="w-4 h-4 mr-1.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak / PDF
                    </a>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Tambah Pengguna Baru
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Filters -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-3 items-center justify-between">
                    <div class="w-full md:w-80">
                        <input
                            type="text"
                            v-model="searchQuery"
                            @keyup.enter="filter"
                            placeholder="Cari nama, email, atau no HP..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                        />
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <select
                            v-model="selectedRole"
                            @change="filter"
                            class="text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 capitalize"
                        >
                            <option value="">Semua Peran (Role)</option>
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>

                        <button
                            @click="filter"
                            class="px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-semibold rounded-lg shadow-sm transition"
                        >
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                                    <th class="py-3.5 px-4">Nama Pengguna</th>
                                    <th class="py-3.5 px-4">Kontak</th>
                                    <th class="py-3.5 px-4">Peran (Role)</th>
                                    <th class="py-3.5 px-4">Tanggal Daftar</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="users.data.length === 0">
                                    <td colspan="6" class="text-center py-8 text-gray-400">
                                        Tidak ada pengguna yang ditemukan.
                                    </td>
                                </tr>

                                <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50/70 transition">
                                    <td class="py-3.5 px-4">
                                        <button
                                            type="button"
                                            @click="openDetailModal(u)"
                                            class="text-left group flex flex-col focus:outline-none"
                                            title="Klik untuk melihat detail lengkap pengguna"
                                        >
                                            <span class="font-bold text-gray-900 group-hover:text-emerald-700 group-hover:underline flex items-center gap-1.5 transition">
                                                {{ u.name }}
                                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-emerald-600 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </span>
                                            <span class="text-[10px] text-gray-400 group-hover:text-emerald-600 transition">ID: #{{ u.id }} &bull; Klik rincian</span>
                                        </button>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="text-gray-800">{{ u.email }}</p>
                                        <p class="text-gray-500 text-[11px]">{{ u.phone || '-' }}</p>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span
                                            v-for="r in u.roles"
                                            :key="r.id"
                                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                            :class="{
                                                'bg-purple-100 text-purple-800': r.name === 'admin',
                                                'bg-emerald-100 text-emerald-800': r.name === 'mitra',
                                                'bg-blue-100 text-blue-800': r.name === 'pembeli',
                                            }"
                                        >
                                            {{ r.name }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-500">
                                        {{ formatDate(u.created_at) }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
                                            :class="u.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                                        >
                                            {{ u.status === 'active' ? 'Aktif' : 'Ditangguhkan' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button
                                                @click="openDetailModal(u)"
                                                class="px-2 py-1 rounded text-[11px] font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition"
                                                title="Lihat rincian pengguna"
                                            >
                                                Rincian
                                            </button>
                                            <button
                                                v-if="!u.roles.some(r => r.name === 'admin')"
                                                @click="toggleStatus(u)"
                                                class="px-2 py-1 rounded text-[11px] font-semibold transition"
                                                :class="u.status === 'active' ? 'bg-amber-50 text-amber-800 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                                :title="u.status === 'active' ? 'Tangguhkan akun' : 'Aktifkan akun'"
                                            >
                                                {{ u.status === 'active' ? 'Tangguhkan' : 'Aktifkan' }}
                                            </button>
                                            <button
                                                v-if="page.props.auth?.user?.id !== u.id"
                                                @click="deleteUserPermanently(u)"
                                                class="p-1 rounded text-red-600 hover:text-red-800 hover:bg-red-50 transition"
                                                title="Hapus pengguna secara permanen"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.links && users.links.length > 3" class="p-4 border-t border-gray-100 flex justify-center gap-1">
                        <Link
                            v-for="(link, idx) in users.links"
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

        <!-- Modal Tambah Pengguna Baru -->
        <div
            v-if="isCreateModalOpen"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs overflow-y-auto flex justify-center items-start sm:items-center p-3 sm:p-4"
        >
            <div
                class="bg-white rounded-2xl max-w-lg w-full shadow-2xl my-3 sm:my-8 flex flex-col max-h-[92vh] sm:max-h-[90vh] overflow-hidden border border-gray-100"
            >
                <!-- Modal Header (Tetap di atas) -->
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center shrink-0 bg-white">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Tambah Pengguna Baru</h3>
                        <p class="text-[11px] text-gray-500 mt-0.5">Daftarkan akun Admin, Mitra Produsen, atau Pembeli.</p>
                    </div>
                    <button
                        @click="closeCreateModal"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submitUser" class="flex flex-col flex-grow min-h-0">
                    <!-- Modal Scrollable Body -->
                    <div class="p-5 overflow-y-auto space-y-4 flex-grow">
                        <!-- Pilihan Role -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Peran Pengguna (Role) <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <label
                                    class="flex items-center justify-center p-2 rounded-lg border text-xs font-semibold cursor-pointer transition"
                                    :class="form.role === 'pembeli' ? 'border-emerald-600 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                                >
                                    <input type="radio" v-model="form.role" value="pembeli" class="sr-only" />
                                    <span>Pembeli</span>
                                </label>

                                <label
                                    class="flex items-center justify-center p-2 rounded-lg border text-xs font-semibold cursor-pointer transition"
                                    :class="form.role === 'mitra' ? 'border-emerald-600 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                                >
                                    <input type="radio" v-model="form.role" value="mitra" class="sr-only" />
                                    <span>Mitra</span>
                                </label>

                                <label
                                    class="flex items-center justify-center p-2 rounded-lg border text-xs font-semibold cursor-pointer transition"
                                    :class="form.role === 'admin' ? 'border-emerald-600 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                                >
                                    <input type="radio" v-model="form.role" value="admin" class="sr-only" />
                                    <span>Admin</span>
                                </label>
                            </div>
                            <p v-if="form.errors.role" class="text-[11px] text-red-600 mt-1">{{ form.errors.role }}</p>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Contoh: Budi Santoso"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            />
                            <p v-if="form.errors.name" class="text-[11px] text-red-600 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email & No HP -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    required
                                    placeholder="budi@example.com"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                />
                                <p v-if="form.errors.email" class="text-[11px] text-red-600 mt-1">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Nomor WhatsApp / HP <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.phone"
                                    required
                                    placeholder="Contoh: 08123456789"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                />
                                <p v-if="form.errors.phone" class="text-[11px] text-red-600 mt-1">{{ form.errors.phone }}</p>
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Password Akun <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="form.password"
                                required
                                minlength="8"
                                placeholder="Minimal 8 karakter"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                            />
                            <p v-if="form.errors.password" class="text-[11px] text-red-600 mt-1">{{ form.errors.password }}</p>
                        </div>

                        <!-- Wilayah (Opsional) -->
                        <div class="pt-2 border-t border-gray-100 space-y-3">
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block">
                                Informasi Alamat & Wilayah (Opsional)
                            </span>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-[11px] text-gray-600 mb-1">Provinsi</label>
                                    <select
                                        v-model="form.province_id"
                                        @change="fetchRegencies"
                                        class="w-full text-[11px] rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                    >
                                        <option value="">Pilih Provinsi</option>
                                        <option v-for="prov in provinces" :key="prov.id" :value="prov.id">{{ prov.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] text-gray-600 mb-1">Kota / Kabupaten</label>
                                    <select
                                        v-model="form.regency_id"
                                        @change="fetchDistricts"
                                        :disabled="!form.province_id || fetchingRegencies"
                                        class="w-full text-[11px] rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 disabled:bg-gray-100"
                                    >
                                        <option value="">{{ fetchingRegencies ? 'Memuat...' : 'Pilih Kota' }}</option>
                                        <option v-for="reg in regencies" :key="reg.id" :value="reg.id">{{ reg.type }} {{ reg.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] text-gray-600 mb-1">Kecamatan</label>
                                    <select
                                        v-model="form.district_id"
                                        :disabled="!form.regency_id || fetchingDistricts"
                                        class="w-full text-[11px] rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 disabled:bg-gray-100"
                                    >
                                        <option value="">{{ fetchingDistricts ? 'Memuat...' : 'Pilih Kecamatan' }}</option>
                                        <option v-for="dist in districts" :key="dist.id" :value="dist.id">{{ dist.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] text-gray-600 mb-1">Alamat Lengkap</label>
                                <textarea
                                    v-model="form.address"
                                    rows="2"
                                    placeholder="Jalan, RT/RW, nomor rumah atau detail lokasi..."
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer (Tetap di bawah) -->
                    <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex justify-end gap-2 shrink-0">
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-100 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center gap-1.5"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Pengguna</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Detail Pengguna -->
        <div
            v-if="isDetailModalOpen && selectedUser"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs overflow-y-auto flex justify-center items-start sm:items-center p-3 sm:p-4"
        >
            <div
                class="bg-white rounded-2xl max-w-lg w-full shadow-2xl my-3 sm:my-8 flex flex-col max-h-[92vh] sm:max-h-[90vh] overflow-hidden border border-gray-100"
            >
                <!-- Modal Header (Tetap di atas) -->
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center shrink-0 bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm shrink-0">
                            {{ selectedUser.name ? selectedUser.name.charAt(0).toUpperCase() : 'U' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900">{{ selectedUser.name }}</h3>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                    :class="selectedUser.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                                >
                                    {{ selectedUser.status === 'active' ? 'Aktif' : 'Ditangguhkan' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-500">ID Pengguna: #{{ selectedUser.id }} &bull; Terdaftar {{ formatDate(selectedUser.created_at) }}</p>
                        </div>
                    </div>
                    <button
                        @click="closeDetailModal"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Modal Scrollable Body -->
                <div class="p-5 overflow-y-auto space-y-4 flex-grow text-xs">
                    <!-- Peran / Role -->
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex items-center justify-between">
                        <span class="text-gray-600 font-medium">Peran Pengguna (Role):</span>
                        <div class="flex gap-1.5 flex-wrap">
                            <span
                                v-for="r in selectedUser.roles"
                                :key="r.id"
                                class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider"
                                :class="{
                                    'bg-purple-100 text-purple-800': r.name === 'admin',
                                    'bg-emerald-100 text-emerald-800': r.name === 'mitra',
                                    'bg-blue-100 text-blue-800': r.name === 'pembeli',
                                }"
                            >
                                {{ r.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Kontak & Akun -->
                    <div class="border border-gray-100 rounded-xl p-3.5 space-y-3">
                        <h4 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Informasi Kontak & Akun
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <span class="text-[11px] text-gray-400 block">Alamat Email:</span>
                                <a :href="'mailto:' + selectedUser.email" class="font-semibold text-gray-800 hover:text-emerald-700 transition flex items-center gap-1 mt-0.5">
                                    {{ selectedUser.email }}
                                </a>
                                <span class="text-[10px] text-emerald-600 flex items-center gap-1 mt-0.5" v-if="selectedUser.email_verified_at">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Terverifikasi
                                </span>
                                <span class="text-[10px] text-amber-600 mt-0.5 block" v-else>
                                    Belum verifikasi email
                                </span>
                            </div>

                            <div>
                                <span class="text-[11px] text-gray-400 block">Nomor HP / WhatsApp:</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-semibold text-gray-800">{{ selectedUser.phone || '-' }}</span>
                                    <a
                                        v-if="selectedUser.phone"
                                        :href="getWhatsappUrl(selectedUser.phone, selectedUser.name)"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-semibold transition shadow-xs"
                                        title="Kirim pesan WhatsApp"
                                    >
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        Chat WA
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat & Domisili -->
                    <div class="border border-gray-100 rounded-xl p-3.5 space-y-3">
                        <h4 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Informasi Alamat & Domisili
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="text-[10px] text-gray-400 block">Provinsi</span>
                                <span class="font-semibold text-gray-800 text-[11px]">
                                    {{ selectedUser.province?.name || '-' }}
                                </span>
                            </div>

                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="text-[10px] text-gray-400 block">Kota / Kabupaten</span>
                                <span class="font-semibold text-gray-800 text-[11px]">
                                    {{ selectedUser.regency ? (selectedUser.regency.type + ' ' + selectedUser.regency.name) : '-' }}
                                </span>
                            </div>

                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="text-[10px] text-gray-400 block">Kecamatan</span>
                                <span class="font-semibold text-gray-800 text-[11px]">
                                    {{ selectedUser.district?.name || '-' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="text-[11px] text-gray-400 block">Alamat Lengkap:</span>
                            <div class="mt-1 p-2.5 bg-gray-50 rounded-lg text-gray-700 border border-gray-100 whitespace-pre-line">
                                {{ selectedUser.address || 'Belum mencantumkan alamat jalan lengkap.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Profil Usaha Mitra (Jika peran mitra dan punya profil) -->
                    <div v-if="selectedUser.partner_profile" class="border border-emerald-100 bg-emerald-50/40 rounded-xl p-3.5 space-y-2">
                        <h4 class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Profil Usaha Tani / Peternakan Mitra
                        </h4>
                        <div>
                            <span class="text-[10px] text-gray-500 block">Nama Usaha / Peternakan / Kebun:</span>
                            <span class="font-bold text-gray-900">{{ selectedUser.partner_profile.farm_name }}</span>
                        </div>
                        <div v-if="selectedUser.partner_profile.description">
                            <span class="text-[10px] text-gray-500 block">Deskripsi Usaha:</span>
                            <p class="text-gray-700 mt-0.5">{{ selectedUser.partner_profile.description }}</p>
                        </div>
                        <div v-if="selectedUser.partner_profile.address_detail">
                            <span class="text-[10px] text-gray-500 block">Detail Lokasi Kebun / Kandang:</span>
                            <p class="text-gray-700 mt-0.5">{{ selectedUser.partner_profile.address_detail }}</p>
                        </div>
                    </div>

                    <!-- Zona Bahaya: Hapus Pengguna Permanen -->
                    <div class="border border-red-200 bg-red-50/60 rounded-xl p-3.5 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h4 class="text-xs font-bold text-red-800 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Pengguna Permanen
                                </h4>
                                <p class="text-[11px] text-red-600 mt-0.5">
                                    Menghapus akun {{ selectedUser.name }} beserta seluruh data terkait secara permanen dari sistem.
                                </p>
                            </div>
                            <button
                                v-if="page.props.auth?.user?.id !== selectedUser.id"
                                type="button"
                                @click="deleteUserPermanently(selectedUser)"
                                :disabled="isDeleting"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition shrink-0 shadow-xs disabled:opacity-50"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>{{ isDeleting ? 'Menghapus...' : 'Hapus Permanen' }}</span>
                            </button>
                            <span v-else class="text-[11px] text-gray-500 italic shrink-0">
                                (Akun Anda)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer (Tetap di bawah) -->
                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2 justify-between items-center shrink-0">
                    <div class="flex items-center gap-2">
                        <button
                            v-if="page.props.auth?.user?.id !== selectedUser.id"
                            type="button"
                            @click="deleteUserPermanently(selectedUser)"
                            :disabled="isDeleting"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition shadow-xs disabled:opacity-50"
                            title="Hapus akun ini secara permanen"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            <span>{{ isDeleting ? 'Menghapus...' : 'Hapus Permanen' }}</span>
                        </button>

                        <button
                            v-if="!selectedUser.roles.some(r => r.name === 'admin')"
                            @click="toggleStatus(selectedUser); closeDetailModal();"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            :class="selectedUser.status === 'active' ? 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'"
                        >
                            {{ selectedUser.status === 'active' ? 'Tangguhkan Akun' : 'Aktifkan Akun' }}
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="closeDetailModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-xs font-semibold text-gray-800 transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
