<?php

$registerVue = __DIR__ . '/resources/js/Pages/Auth/Register.vue';
$content = file_get_contents($registerVue);

// Update fetch Regencies to use local API
$content = str_replace(
    'https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${form.province_id}.json',
    '/api/provinces/${form.province_id}/regencies',
    $content
);

// Update fetch Districts to use local API
$content = str_replace(
    'https://www.emsifa.com/api-wilayah-indonesia/api/districts/${form.regency_id}.json',
    '/api/regencies/${form.regency_id}/districts',
    $content
);

// Update option to show type + name because local DB regencies have type
$content = str_replace(
    '<option v-for="reg in regencies" :key="reg.id" :value="reg.id">{{ reg.name }}</option>',
    '<option v-for="reg in regencies" :key="reg.id" :value="reg.id">{{ reg.type }} {{ reg.name }}</option>',
    $content
);

file_put_contents($registerVue, $content);

echo "Register.vue updated to use local API";
