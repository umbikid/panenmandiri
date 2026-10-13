<?php

$file = __DIR__ . '/resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
$content = file_get_contents($file);

$content = str_replace(
    '<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account\'s profile information and email address.
            </p>',
    '<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Informasi Profil
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Akun anda adalah akun {{ user.roles[0] }}. Perbarui informasi profil dan alamat email Anda.
            </p>',
    $content
);

$content = str_replace(
    '<InputLabel for="name" value="Name" />',
    '<InputLabel for="name" value="Nama" />',
    $content
);

$content = str_replace(
    '<PrimaryButton :disabled="form.processing">Save</PrimaryButton>',
    '<PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>',
    $content
);

$content = str_replace(
    '>Saved.</p>',
    '>Tersimpan.</p>',
    $content
);

file_put_contents($file, $content);
echo "UpdateProfileInformationForm.vue updated.\n";
