<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    provinces: Array,
});

const form = useForm({
    role: 'pembeli',
    name: '',
    email: '',
    phone: '',
    address: '',
    province_id: '',
    regency_id: '',
    district_id: '',
    password: '',
    password_confirmation: '',
});

const regencies = ref([]);
const districts = ref([]);
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

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

                <form @submit.prevent="submit">
            <!-- Pilihan Role -->
            <div class="mb-6 flex gap-6">
                <label class="flex items-center">
                    <input type="radio" v-model="form.role" value="pembeli" class="text-green-700 focus:ring-green-700">
                    <span class="ml-2 text-gray-700">Daftar sebagai Pembeli</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" v-model="form.role" value="mitra" class="text-green-700 focus:ring-green-700">
                    <span class="ml-2 text-gray-700">Daftar sebagai Mitra</span>
                </label>
            </div>
            
            <div>
                <InputLabel for="name" value="Name" />

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

            <div class="mt-4">
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
            <div class="mt-4">
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

                        <div class="mt-4">
                <InputLabel for="address" value="Alamat Lengkap" />
                <textarea
                    id="address"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm"
                    v-model="form.address"
                    required
                    rows="2"
                ></textarea>
                <InputError class="mt-2" :message="form.errors.address" />
            </div>

                                                <div class="mt-4">
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

            <div class="mt-4">
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

            <div class="mt-4">
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

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
