<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    provinces: {
        type: Array,
        default: () => [],
    },
    initialRegencies: {
        type: Array,
        default: () => [],
    },
    initialDistricts: {
        type: Array,
        default: () => [],
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
    address: user.address || '',
    province_id: user.province_id || '',
    regency_id: user.regency_id || '',
    district_id: user.district_id || '',
});

const regencies = ref([...props.initialRegencies]);
const districts = ref([...props.initialDistricts]);
const fetchingRegencies = ref(false);
const fetchingDistricts = ref(false);

const fetchRegencies = async () => {
    form.regency_id = '';
    form.district_id = '';
    regencies.value = [];
    districts.value = [];
    
    if (!form.province_id) return;
    
    fetchingRegencies.value = true;
    try {
        const response = await axios.get(`/api/provinces/${form.province_id}/regencies`);
        regencies.value = response.data;
    } catch (error) {
        console.error("Failed to fetch regencies", error);
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
        const response = await axios.get(`/api/regencies/${form.regency_id}/districts`);
        districts.value = response.data;
    } catch (error) {
        console.error("Failed to fetch districts", error);
    } finally {
        fetchingDistricts.value = false;
    }
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Informasi Profil
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Akun anda adalah akun {{ user.roles?.[0] || 'pengguna' }}. Perbarui informasi profil dan alamat Anda.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Nama" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="phone" value="Nomor WhatsApp" />
                <TextInput
                    id="phone"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    required
                    autocomplete="tel"
                />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="address" value="Alamat Lengkap" />
                <textarea
                    id="address"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm"
                    v-model="form.address"
                    required
                    rows="3"
                    placeholder="Contoh: Jl. Mawar No. 12, RT 01/RW 02"
                ></textarea>
                <InputError class="mt-2" :message="form.errors.address" />
            </div>

            <div>
                <InputLabel for="province_id" value="Provinsi" />
                <select
                    id="province_id"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm"
                    v-model="form.province_id"
                    @change="fetchRegencies"
                    required
                >
                    <option value="" disabled>Pilih Provinsi</option>
                    <option v-for="prov in provinces" :key="prov.id" :value="prov.id">{{ prov.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.province_id" />
            </div>

            <div>
                <InputLabel for="regency_id" value="Kota/Kabupaten" />
                <select
                    id="regency_id"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm disabled:bg-gray-100"
                    v-model="form.regency_id"
                    @change="fetchDistricts"
                    required
                    :disabled="!form.province_id || fetchingRegencies"
                >
                    <option value="" disabled>{{ fetchingRegencies ? 'Memuat Kota...' : 'Pilih Kota/Kabupaten' }}</option>
                    <option v-for="reg in regencies" :key="reg.id" :value="reg.id">{{ reg.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.regency_id" />
            </div>

            <div>
                <InputLabel for="district_id" value="Kecamatan" />
                <select
                    id="district_id"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm disabled:bg-gray-100"
                    v-model="form.district_id"
                    required
                    :disabled="!form.regency_id || fetchingDistricts"
                >
                    <option value="" disabled>{{ fetchingDistricts ? 'Memuat Kecamatan...' : 'Pilih Kecamatan' }}</option>
                    <option v-for="dist in districts" :key="dist.id" :value="dist.id">{{ dist.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.district_id" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-green-900 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
