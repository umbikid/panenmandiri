<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    cs_whatsapp_number: props.settings.cs_whatsapp_number,
});

const submit = () => {
    form.post(route('admin.settings.update'));
};
</script>

<template>
    <Head title="Pengaturan Sistem - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-emerald-950">
                        Pengaturan Sistem & Kontak CS
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Konfigurasi nomor WhatsApp CS tujuan pesanan.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Nomor WhatsApp CS -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nomor WhatsApp CS (Customer Service)
                            </label>
                            <input
                                type="text"
                                v-model="form.cs_whatsapp_number"
                                placeholder="Contoh: 6281234567890"
                                required
                                class="w-full text-sm font-mono rounded-lg border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 font-bold text-emerald-900"
                            />
                            <p class="text-[10px] text-gray-500 mt-1">
                                Nomor ini akan menjadi tujuan otomatis saat pembeli menekan tombol <strong>"Lanjut ke WhatsApp CS"</strong> pada saat checkout. Gunakan format diawali kode negara (misal 628...).
                            </p>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span v-if="form.recentlySuccessful" class="text-xs text-emerald-700 font-semibold">
                                &check; Pengaturan berhasil disimpan.
                            </span>
                            <span v-else></span>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold rounded-lg shadow-sm transition"
                            >
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
