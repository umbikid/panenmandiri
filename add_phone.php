<?php

// 1. Update RegisteredUserController
$regCtrl = __DIR__ . '/app/Http/Controllers/Auth/RegisteredUserController.php';
$regContent = file_get_contents($regCtrl);
$regContent = str_replace(
    "'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],",
    "'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],\n            'phone' => ['required', 'string', 'max:20'],",
    $regContent
);
$regContent = str_replace(
    "'email' => \$request->email,",
    "'email' => \$request->email,\n            'phone' => \$request->phone,",
    $regContent
);
file_put_contents($regCtrl, $regContent);

// 2. Update ProfileUpdateRequest
$profReq = __DIR__ . '/app/Http/Requests/ProfileUpdateRequest.php';
$profReqContent = file_get_contents($profReq);
$profReqContent = preg_replace(
    "/'email' => \['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique\(User::class\)->ignore\(\\\$this->user\(\)->id\)\],/",
    "'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore(\$this->user()->id)],\n            'phone' => ['required', 'string', 'max:20'],",
    $profReqContent
);
file_put_contents($profReq, $profReqContent);

// 3. Update Register.vue
$regVue = __DIR__ . '/resources/js/Pages/Auth/Register.vue';
$regVueContent = file_get_contents($regVue);

// Add to form object
$regVueContent = str_replace(
    "email: '',",
    "email: '',\n    phone: '',",
    $regVueContent
);

// Add the HTML input
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

$regVueContent = str_replace(
    '        <div class="mt-4">
            <InputLabel for="password" value="Password" />',
    $phoneHtml,
    $regVueContent
);
file_put_contents($regVue, $regVueContent);


// 4. Update UpdateProfileInformationForm.vue
$profVue = __DIR__ . '/resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
$profVueContent = file_get_contents($profVue);

// Add to form object
$profVueContent = str_replace(
    "email: user.email,",
    "email: user.email,\n    phone: user.phone,",
    $profVueContent
);

// Add the HTML input
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

$profVueContent = str_replace(
    '        <div>
            <InputLabel for="email" value="Email" />',
    $profPhoneHtml,
    $profVueContent
);
file_put_contents($profVue, $profVueContent);

echo "All updated";
