<?php

$registerVue = __DIR__ . '/resources/js/Pages/Auth/Register.vue';
$content = file_get_contents($registerVue);

// 1. Script setup update
$scriptReplacement = <<<JAVASCRIPT
import { ref, watch } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const form = useForm({
    role: 'pembeli',
    name: '',
    email: '',
    phone: '',
    address: '',
    district_id: '',
    password: '',
    password_confirmation: '',
});

const districts = ref([]);
const searchDistrictName = ref('');
let searchTimeout = null;

watch(searchDistrictName, (newVal) => {
    // Cari id berdasarkan nama yang persis sama
    const match = districts.value.find(d => d.name === newVal);
    if (match) {
        form.district_id = match.id;
        return; // Jangan fetch lagi jika user sudah memilih dari datalist
    }
    
    form.district_id = '';
    
    if (newVal.length >= 3) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            try {
                const response = await axios.get('/api/districts/search?q=' + encodeURIComponent(newVal));
                districts.value = response.data;
            } catch (error) {
                console.error(error);
            }
        }, 300);
    }
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
JAVASCRIPT;

$content = preg_replace(
    "/import \{ ref \} from 'vue';.*?const submit = \(\) => \{.*?\}\);.*?\};/s",
    $scriptReplacement,
    $content
);

// 2. HTML update for Roles and District
$htmlSearch = <<<HTML
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
HTML;

$content = preg_replace(
    '/<form @submit\.prevent="submit">\s*<div>/s',
    $htmlSearch,
    $content
);

$htmlDistrictSearch = <<<HTML
            <div class="mt-4">
                <InputLabel for="district_id" value="Kecamatan & Kota" />
                <input 
                    type="text" 
                    list="district-options" 
                    v-model="searchDistrictName"
                    class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700 rounded-md shadow-sm"
                    placeholder="Ketik nama kecamatan..."
                    required
                >
                <datalist id="district-options">
                    <option v-for="dist in districts" :key="dist.id" :value="dist.name"></option>
                </datalist>
                <InputError class="mt-2" :message="form.errors.district_id" />
                <p v-if="searchDistrictName.length > 0 && !form.district_id && districts.length === 0" class="text-xs text-gray-500 mt-1">Ketik minimal 3 huruf untuk mencari.</p>
            </div>
HTML;

$content = preg_replace(
    '/<div class="mt-4">\s*<InputLabel for="regency_id" value="Kota\/Kabupaten" \/>.*?<\/select>\s*<InputError class="mt-2" :message="form\.errors\.district_id" \/>\s*<\/div>/s',
    $htmlDistrictSearch,
    $content
);

file_put_contents($registerVue, $content);
echo "Register.vue updated with roles and district search";
