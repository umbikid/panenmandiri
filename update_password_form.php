<?php

$file = __DIR__ . '/resources/js/Pages/Profile/Partials/UpdatePasswordForm.vue';
$content = file_get_contents($file);

$content = str_replace(
    '<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Update Password
            </h2>',
    '<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Perbarui Password
            </h2>',
    $content
);

$content = str_replace(
    'value="Current Password"',
    'value="Password saat ini"',
    $content
);

$content = str_replace(
    'value="New Password"',
    'value="Password baru"',
    $content
);

$content = str_replace(
    'value="Confirm Password"',
    'value="Konfirmasi password"',
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
echo "UpdatePasswordForm.vue updated.\n";
