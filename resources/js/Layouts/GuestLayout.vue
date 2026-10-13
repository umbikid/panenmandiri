<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
        <div>
            <Link href="/" class="flex flex-col items-center group">
                <div class="bg-white p-2 rounded-full shadow-md ring-4 ring-amber-400/80 mb-2 transition transform group-hover:scale-105">
                    <ApplicationLogo class="h-14 w-14" />
                </div>
                <span class="text-2xl font-black text-emerald-900 tracking-tight">
                    Panen <span class="text-amber-500">Mandiri</span>
                </span>
            </Link>
        </div>

        <div
            class="mt-4 w-full overflow-hidden bg-white px-6 py-5 shadow-lg sm:max-w-md sm:rounded-xl border-t-4 border-amber-500"
        >
            <slot />
        </div>
    </div>
</template>
