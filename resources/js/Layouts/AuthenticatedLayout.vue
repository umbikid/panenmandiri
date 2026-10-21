<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="bg-gradient-to-r from-emerald-800 via-green-800 to-emerald-900 border-b-4 border-amber-500 shadow-md text-white sticky top-0 z-40"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="$page.props.auth.user.roles.includes('admin') ? route('admin.dashboard') : ($page.props.auth.user.roles.includes('mitra') ? route('mitra.dashboard') : route('dashboard'))" class="flex items-center group">
                                    <div class="bg-white/95 p-1 rounded-full shadow-sm ring-2 ring-amber-400/80 mr-2.5">
                                        <ApplicationLogo class="block h-8 w-auto" />
                                    </div>
                                    <span class="text-xl font-black text-white tracking-tight drop-shadow-sm">
                                        Panen <span class="text-amber-400 group-hover:text-amber-300 transition-colors">Mandiri</span>
                                    </span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <!-- Admin Nav -->
                                <template v-if="$page.props.auth.user.roles.includes('admin')">
                                    <NavLink
                                        :href="route('admin.dashboard')"
                                        :active="route().current('admin.dashboard')"
                                    >
                                        Dashboard
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.products.index')"
                                        :active="route().current('admin.products.*')"
                                    >
                                        Produk
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.categories.index')"
                                        :active="route().current('admin.categories.*')"
                                    >
                                        Kategori
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.orders.index')"
                                        :active="route().current('admin.orders.*')"
                                    >
                                        Pesanan
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.users.index')"
                                        :active="route().current('admin.users.*')"
                                    >
                                        Pengguna
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.settings.index')"
                                        :active="route().current('admin.settings.*')"
                                    >
                                        Pengaturan
                                    </NavLink>
                                </template>

                                <!-- Mitra Nav -->
                                <template v-else-if="$page.props.auth.user.roles.includes('mitra')">
                                    <NavLink
                                        :href="route('mitra.dashboard')"
                                        :active="route().current('mitra.dashboard')"
                                    >
                                        Dashboard Mitra
                                    </NavLink>
                                    <NavLink
                                        :href="route('mitra.products.index')"
                                        :active="route().current('mitra.products.*')"
                                    >
                                        Kelola Produk
                                    </NavLink>
                                </template>

                                <!-- Pembeli / Default Nav -->
                                <NavLink
                                    v-else
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>

                                <NavLink
                                    :href="route('catalog.index')"
                                    :active="route().current('catalog.*')"
                                >
                                    Katalog Publik
                                </NavLink>

                                <NavLink
                                    v-if="$page.props.auth.user.roles.includes('pembeli')"
                                    :href="route('cart.index')"
                                    :active="route().current('cart.*')"
                                    class="relative"
                                >
                                    <span>Keranjang</span>
                                    <span
                                        v-if="$page.props.auth.cart_count > 0"
                                        class="ml-1.5 bg-amber-400 text-emerald-950 font-black text-[10px] px-1.5 py-0.5 rounded-full"
                                    >
                                        {{ $page.props.auth.cart_count }}
                                    </span>
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-lg border border-emerald-700/80 bg-emerald-900/60 px-3.5 py-2 text-sm font-medium leading-4 text-emerald-100 transition duration-150 ease-in-out hover:text-amber-300 hover:border-amber-400 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-lg p-2 text-emerald-100 transition duration-150 ease-in-out hover:bg-emerald-700/60 hover:text-amber-300 focus:bg-emerald-700/60 focus:text-amber-300 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden bg-emerald-900 border-t border-emerald-700/80 shadow-inner"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <!-- Admin -->
                        <template v-if="$page.props.auth.user.roles.includes('admin')">
                            <ResponsiveNavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                                Dashboard Admin
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.products.index')" :active="route().current('admin.products.*')">
                                Kelola Produk & Harga
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.categories.index')" :active="route().current('admin.categories.*')">
                                Kelola Kategori
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.orders.index')" :active="route().current('admin.orders.*')">
                                Transaksi & Pesanan
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                                Manajemen Pengguna
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.settings.index')" :active="route().current('admin.settings.*')">
                                Pengaturan Sistem
                            </ResponsiveNavLink>
                        </template>

                        <!-- Mitra -->
                        <template v-else-if="$page.props.auth.user.roles.includes('mitra')">
                            <ResponsiveNavLink :href="route('mitra.dashboard')" :active="route().current('mitra.dashboard')">
                                Dashboard Mitra
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('mitra.products.index')" :active="route().current('mitra.products.*')">
                                Kelola Produk
                            </ResponsiveNavLink>
                        </template>

                        <!-- Umum / Pembeli -->
                        <ResponsiveNavLink v-else :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('catalog.index')" :active="route().current('catalog.*')">
                            Katalog Publik
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.roles.includes('pembeli')"
                            :href="route('cart.index')"
                            :active="route().current('cart.*')"
                        >
                            Keranjang Belanja ({{ $page.props.auth.cart_count || 0 }})
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-emerald-700/80 pb-3 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-semibold text-white"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-emerald-200">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white border-b border-emerald-100/80 shadow-sm dark:bg-emerald-900/40"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
