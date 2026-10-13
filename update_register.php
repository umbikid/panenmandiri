<?php

$registerVue = __DIR__ . '/resources/js/Pages/Auth/Register.vue';
$content = file_get_contents($registerVue);

// 1. Update Script setup
$scriptReplacement = <<<JAVASCRIPT
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    regencies: Array,
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    regency_id: '',
    district_id: '',
    password: '',
    password_confirmation: '',
});

const districts = ref([]);
const fetchingDistricts = ref(false);

const fetchDistricts = async () => {
    form.district_id = '';
    districts.value = [];
    if (!form.regency_id) return;
    
    fetchingDistricts.value = true;
    try {
        const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/\${form.regency_id}.json`);
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
JAVASCRIPT;

$content = preg_replace(
    "/import GuestLayout from '.*?const submit = \(\) => \{.*?\}\);.*?\};/s",
    $scriptReplacement,
    $content
);

// 2. Add HTML inputs for Address, Regency, District
$htmlReplacement = <<<HTML
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
                <InputLabel for="regency_id" value="Kota/Kabupaten" />
                <select
                    id="regency_id"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm"
                    v-model="form.regency_id"
                    @change="fetchDistricts"
                    required
                >
                    <option value="" disabled>Pilih Kota/Kabupaten</option>
                    <option v-for="reg in regencies" :key="reg.id" :value="reg.id">{{ reg.type }} {{ reg.name }}</option>
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
                    <option value="" disabled>{{ fetchingDistricts ? 'Memuat...' : 'Pilih Kecamatan' }}</option>
                    <option v-for="dist in districts" :key="dist.id" :value="dist.id">{{ dist.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.district_id" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
HTML;

$content = preg_replace(
    '/<div class="mt-4">\s*<InputLabel for="password" value="Password" \/>/s',
    $htmlReplacement,
    $content
);

file_put_contents($registerVue, $content);

echo "Register.vue updated with address and districts";
