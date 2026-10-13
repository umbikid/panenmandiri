<?php

// 1. Update Register.vue
$regVue = __DIR__ . '/resources/js/Pages/Auth/Register.vue';
$regVueContent = file_get_contents($regVue);

$phoneHtml = <<<HTML
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
                <InputLabel for="password" value="Password" />
HTML;

$regVueContent = preg_replace(
    '/\s*<div class="mt-4">\s*<InputLabel for="password" value="Password" \/>/',
    "\n$phoneHtml",
    $regVueContent
);
file_put_contents($regVue, $regVueContent);


// 2. Update UpdateProfileInformationForm.vue
$profVue = __DIR__ . '/resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
$profVueContent = file_get_contents($profVue);

$profPhoneHtml = <<<HTML
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
HTML;

$profVueContent = preg_replace(
    '/\s*<div>\s*<InputLabel for="email" value="Email" \/>/',
    "\n$profPhoneHtml",
    $profVueContent
);
file_put_contents($profVue, $profVueContent);

echo "Vue files updated";
