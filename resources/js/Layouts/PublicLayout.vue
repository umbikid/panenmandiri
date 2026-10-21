<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col pb-16 md:pb-0">
        <!-- Top Nav (Mobile: minimal, Desktop: full) -->
        <nav class="bg-gradient-to-r from-emerald-800 via-green-800 to-emerald-900 border-b-4 border-amber-500 shadow-md sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('catalog.index')" class="flex items-center group">
                                <div class="bg-white/95 p-1 rounded-full shadow-sm ring-2 ring-amber-400/80 mr-2.5">
                                    <ApplicationLogo class="block h-8 w-auto" />
                                </div>
                                <span class="text-xl font-black text-white tracking-tight drop-shadow-sm">
                                    Panen <span class="text-amber-400 group-hover:text-amber-300 transition-colors">Mandiri</span>
                                </span>
                            </Link>
                        </div>
                    </div>

                    <div class="hidden md:flex md:items-center md:ms-6 space-x-3">
                        <!-- Menu B2B (Terbuka untuk semua, terutama pengunjung/klien B2B) -->
                        <Link
                            :href="route('b2b.index')"
                            class="text-sm px-3.5 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="route().current('b2b.*') ? 'bg-amber-400 text-emerald-950 shadow-sm' : 'text-amber-300 hover:text-white hover:bg-emerald-700/50 border border-amber-400/40'"
                        >
                            <span class="bg-amber-400 text-emerald-950 text-[10px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider">B2B</span>
                            <span>Layanan B2B</span>
                        </Link>

                        <template v-if="$page.props.auth.user">
                            <!-- Keranjang Pembeli (Desktop) -->
                            <Link
                                v-if="$page.props.auth.user.roles.includes('pembeli')"
                                :href="route('cart.index')"
                                class="relative text-sm px-3.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5"
                                :class="route().current('cart.*') ? 'bg-emerald-900/80 text-amber-300 border border-amber-400/60 font-bold shadow-sm' : 'text-emerald-100 hover:text-amber-300 hover:bg-emerald-700/50'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Keranjang</span>
                                <span
                                    v-if="$page.props.auth.cart_count > 0"
                                    class="bg-amber-400 text-emerald-950 font-black text-[10px] px-1.5 py-0.2 rounded-full shadow-xs"
                                >
                                    {{ $page.props.auth.cart_count }}
                                </span>
                            </Link>

                            <Link
                                v-if="$page.props.auth.user.roles.includes('pembeli')"
                                :href="route('buyer.orders.index')"
                                class="text-sm px-3.5 py-1.5 rounded-lg font-medium transition"
                                :class="route().current('buyer.orders.index') ? 'bg-emerald-900/80 text-amber-300 border border-amber-400/60 font-bold shadow-sm' : 'text-emerald-100 hover:text-amber-300 hover:bg-emerald-700/50'"
                            >
                                Pembelian
                            </Link>
                            <Link
                                :href="$page.props.auth.user.roles.includes('admin') ? route('admin.dashboard') : ($page.props.auth.user.roles.includes('mitra') ? route('mitra.dashboard') : ($page.props.auth.user.roles.includes('pembeli') ? route('profile.edit') : route('dashboard')))"
                                class="text-sm px-3.5 py-1.5 rounded-lg font-medium transition"
                                :class="route().current('admin.*') || route().current('mitra.*') || route().current('profile.edit') || route().current('dashboard') ? 'bg-emerald-900/80 text-amber-300 border border-amber-400/60 font-bold shadow-sm' : 'text-emerald-100 hover:text-amber-300 hover:bg-emerald-700/50'"
                            >
                                {{ $page.props.auth.user.roles.includes('admin') ? 'Panel Admin' : ($page.props.auth.user.roles.includes('mitra') ? 'Panel Mitra' : ($page.props.auth.user.roles.includes('pembeli') ? 'Profil' : 'Dashboard')) }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="text-sm text-emerald-100 hover:text-white font-medium px-3.5 py-1.5 rounded-lg border border-emerald-600/80 hover:border-amber-400 hover:bg-emerald-700/40 transition">
                                Log in
                            </Link>
                            <Link :href="route('register')" class="text-sm font-bold text-emerald-950 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 px-4 py-1.5 rounded-lg shadow-sm transition hover:shadow-md">
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Bottom Nav (Mobile Only) -->
        <div class="md:hidden fixed bottom-0 w-full bg-white border-t border-gray-200 flex justify-around items-center h-16 z-40 px-2 pb-safe">
            <Link :href="route('catalog.index')" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800" :class="{ 'text-green-800 font-bold': route().current('catalog.index') }">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-medium">Beranda</span>
            </Link>

            <Link :href="route('b2b.index')" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800 relative" :class="{ 'text-green-800 font-bold': route().current('b2b.*') }">
                <div class="relative">
                    <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="absolute -top-1 -right-2 bg-amber-400 text-emerald-950 font-black text-[8px] px-1 rounded shadow-xs">B2B</span>
                </div>
                <span class="text-[10px] font-medium">B2B</span>
            </Link>

            <!-- Keranjang Mobile -->
            <Link
                v-if="$page.props.auth.user && $page.props.auth.user.roles.includes('pembeli')"
                :href="route('cart.index')"
                class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800 relative"
                :class="{ 'text-green-800 font-bold': route().current('cart.*') }"
            >
                <div class="relative">
                    <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span
                        v-if="$page.props.auth.cart_count > 0"
                        class="absolute -top-1 -right-2 bg-red-600 text-white font-bold text-[9px] w-4 h-4 rounded-full flex items-center justify-center shadow-xs"
                    >
                        {{ $page.props.auth.cart_count }}
                    </span>
                </div>
                <span class="text-[10px] font-medium">Keranjang</span>
            </Link>
            
            <Link v-if="$page.props.auth.user && $page.props.auth.user.roles.includes('pembeli')" :href="route('buyer.orders.index')" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800" :class="{ 'text-green-800': route().current('buyer.orders.index') }">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-[10px] font-medium">Pembelian</span>
            </Link>
            
            <Link v-if="$page.props.auth.user" :href="$page.props.auth.user.roles.includes('admin') ? route('admin.dashboard') : ($page.props.auth.user.roles.includes('mitra') ? route('mitra.dashboard') : ($page.props.auth.user.roles.includes('pembeli') ? route('profile.edit') : route('dashboard')))" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800" :class="{ 'text-green-800': route().current('admin.*') || route().current('mitra.*') || route().current('profile.edit') || route().current('dashboard') }">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-[10px] font-medium">{{ $page.props.auth.user.roles.includes('admin') ? 'Admin' : ($page.props.auth.user.roles.includes('mitra') ? 'Mitra' : 'Akun') }}</span>
            </Link>
            <Link v-else :href="route('login')" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-green-800" :class="{ 'text-green-800': route().current('login') }">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                <span class="text-[10px] font-medium">Masuk</span>
            </Link>
        </div>
    </div>
</template>