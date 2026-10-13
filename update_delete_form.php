<?php

$file = __DIR__ . '/resources/js/Pages/Profile/Partials/DeleteUserForm.vue';
$content = file_get_contents($file);

$content = str_replace(
    '<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Delete Account
            </h2>',
    '<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Hapus Akun
            </h2>',
    $content
);

$content = str_replace(
    '>Delete Account</DangerButton>',
    '>Hapus Akun</DangerButton>',
    $content
);

file_put_contents($file, $content);
echo "DeleteUserForm.vue updated.\n";
