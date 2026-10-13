<?php

$registerVue = __DIR__ . '/resources/js/Pages/Auth/Register.vue';
$content = file_get_contents($registerVue);

$scriptReplacement = <<<JAVASCRIPT
import { ref, watch } from 'vue';
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
const searchDistrictName = ref('');

const fetchRegencies = async () => {
    form.regency_id = '';
    form.district_id = '';
    searchDistrictName.value = '';
    regencies.value = [];
    districts.value = [];
    
    if (!form.province_id) return;
    
    fetchingRegencies.value = true;
    try {
        const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/\${form.province_id}.json`);
        regencies.value = response.data;
    } catch (error) {
        console.error("Failed to fetch regencies", error);
    } finally {
        fetchingRegencies.value = false;
    }
};

const fetchDistricts = async () => {
    form.district_id = '';
    searchDistrictName.value = '';
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

watch(searchDistrictName, (newVal) => {
    const match = districts.value.find(d => d.name === newVal);
    if (match) {
        form.district_id = match.id;
    } else {
        form.district_id = '';
    }
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
JAVASCRIPT;

$content = preg_replace(
    "/import \{ ref, watch \} from 'vue';.*?const submit = \(\) => \{.*?\}\);.*?\};/s",
    $scriptReplacement,
    $content
);


$htmlReplacement = <<<HTML
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
                    <option v-for="reg in regencies" :key="reg.id" :value="reg.id">{{ reg.type }} {{ reg.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.regency_id" />
            </div>

            <div class="mt-4">
                <InputLabel for="district_id" value="Kecamatan" />
                <input 
                    type="text" 
                    list="district-options" 
                    v-model="searchDistrictName"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm disabled:bg-gray-100"
                    :placeholder="fetchingDistricts ? 'Memuat Kecamatan...' : 'Ketik dan pilih kecamatan...'"
                    required
                    :disabled="!form.regency_id || fetchingDistricts"
                >
                <datalist id="district-options">
                    <option v-for="dist in districts" :key="dist.id" :value="dist.name"></option>
                </datalist>
                <InputError class="mt-2" :message="form.errors.district_id" />
            </div>
HTML;

$content = preg_replace(
    '/<div class="mt-4">\s*<InputLabel for="district_id" value="Kecamatan & Kota" \/>.*?<p v-if="searchDistrictName\.length > 0 && !form\.district_id && districts\.length === 0" class="text-xs text-gray-500 mt-1">Ketik minimal 3 huruf untuk mencari\.<\/p>\s*<\/div>/s',
    $htmlReplacement,
    $content
);

file_put_contents($registerVue, $content);

echo "Register.vue updated with Province, Regency, and District logic";
