<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    categories: Array,
    metrics: {
        type: Object,
        default: () => ({ total_stock_rice: 0, total_stock_livestock: 0 })
    },
    cs_whatsapp: String,
    clean_whatsapp: String,
    whatsapp_url: String,
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const formatRupiah = (num) => {
    if (!num) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
};

// Tab aktif untuk rincian stok: 'all', 'rice', 'livestock'
const activeTab = ref('all');

const filteredCategories = computed(() => {
    if (activeTab.value === 'rice') {
        return props.categories.filter(c => c.type === 'rice');
    }
    if (activeTab.value === 'livestock') {
        return props.categories.filter(c => c.type === 'livestock');
    }
    return props.categories;
});

// Helper deskripsi varietas/subkategori agar sangat profesional bagi pembeli korporasi
const getSubcategoryDesc = (category, sub) => {
    const name = sub.name.toLowerCase();
    if (category.type === 'rice') {
        if (name.includes('pandan wangi')) return 'Beras aromatik khas dengan wangi pandan alami, tekstur pulen istimewa. Sangat digemari restoran premium & katering eksklusif.';
        if (name.includes('ir64') || name.includes('setra ramos')) return 'Beras bulir panjang (long grain), pulen sedang, sangat stabil untuk pengadaan skala masal, katering, & kantin korporasi.';
        if (name.includes('rojolele')) return 'Beras bulat putih susu alami tanpa pemutih, rasa manis gurih dan tekstur sangat pulen.';
        if (name.includes('ir 42') || name.includes('pera')) return 'Beras tekstur pera kering, butiran tidak lengket saat dimasak. Standar utama untuk resto nasi goreng, lontong, & ketupat.';
        if (name.includes('c4')) return 'Beras pulen dengan butir bening mengkilap, kadar air rendah & daya tahan simpan tinggi di gudang.';
        return 'Beras lokal pilihan dari gabungan kelompok tani binaan KMM dengan proses penggilingan higienis & sortir ketat.';
    } else {
        if (name.includes('ringan')) return 'Sapi sehat berbobot proporsional (< 450 kg), pemeliharaan kandang bersih, cocok untuk qurban keluarga & pengadaan terjangkau.';
        if (name.includes('sedang')) return 'Sapi kelas medium (450–700 kg), karkas daging padat dan rasio daging tinggi. Favorit panitia qurban masjid & institusi.';
        if (name.includes('jumbo') || name.includes('berat')) return 'Sapi pejantan unggul (> 700 kg up to 1 ton), performa prima, sangat cocok untuk qurban korporasi, tokoh, & eksekutif.';
        if (name.includes('standar')) return 'Ternak sehat lincah sesuai syariat (18–22 kg), pakan hijau alami berkualitas.';
        if (name.includes('medium')) return 'Ternak berbobot ideal (23–28 kg), daging tebal, nafsu makan tinggi, dan bebas cacat fisik.';
        if (name.includes('premium')) return 'Ternak pilihan berpostur tegap (29–35 kg), rasio tulang dan daging optimal untuk aqiqah & qurban institusi.';
        return 'Ternak terawat optimal dengan pantauan medis dokter hewan berkala dan sertifikat kesehatan (SKKH) lengkap.';
    }
};

// Buat link WA khusus untuk subkategori tertentu
const makeSubcategoryWaUrl = (category, sub) => {
    const message = `Halo Tim B2B Panen Mandiri / KMM, saya tertarik dengan pengadaan partai besar untuk subkategori: *${sub.label || sub.name}* (${category.name}). Mohon info ketersediaan kapasitas pasokan, spesifikasi sampel, dan surat penawaran harga resmi (B2B).`;
    return `https://wa.me/${props.clean_whatsapp}?text=${encodeURIComponent(message)}`;
};

// Smooth scroll ke bagian stok
const scrollToStock = () => {
    const el = document.getElementById('stock-breakdown');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <Head title="Layanan Pengadaan B2B & Kemitraan Komoditas - KMM" />

    <PublicLayout>
        <!-- HERO SECTION -->
        <section class="relative bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 text-white overflow-hidden py-16 sm:py-24 border-b-4 border-amber-500">
            <!-- Decorative Glow Elements -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 -right-24 w-96 h-96 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-4xl mx-auto space-y-6">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-800/80 border border-emerald-600/60 text-amber-300 text-xs sm:text-sm font-bold shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                        <span>Solusi Pasokan Agribisnis & Peternakan B2B Terpercaya</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight sm:leading-none">
                        Pasokan Komoditas Pangan & Ternak <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-amber-400 to-amber-200">Skala Besar</span> Langsung dari Petani
                    </h1>

                    <!-- Persuasive Subheadline -->
                    <p class="text-base sm:text-lg lg:text-xl text-emerald-100/90 leading-relaxed font-normal">
                        Koperasi Mitra Mandiri (KMM) menghubungkan langsung jaringan penggilingan padi dan peternak binaan dengan mitra korporasi: <strong>HOREKA, Supermarket, Perusahaan BUMN/Swasta, Distributor, serta Panitia Qurban</strong>. Jaminan kontinuitas pasokan, standar uji mutu ketat, dan legalitas kontrak resmi.
                    </p>

                    <!-- Real-Time Metrics Highlights -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 pt-4 pb-2">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 sm:p-4 border border-white/10 text-center">
                            <span class="block text-2xl sm:text-3xl font-black text-amber-400">
                                {{ formatNumber(metrics.total_stock_rice) }} <span class="text-xs font-semibold text-emerald-200">kg</span>
                            </span>
                            <span class="text-[11px] sm:text-xs text-emerald-200 uppercase tracking-wider font-semibold">Stok Beras Aktif</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 sm:p-4 border border-white/10 text-center">
                            <span class="block text-2xl sm:text-3xl font-black text-emerald-300">
                                {{ formatNumber(metrics.total_stock_livestock) }} <span class="text-xs font-semibold text-emerald-200">ekor</span>
                            </span>
                            <span class="text-[11px] sm:text-xs text-emerald-200 uppercase tracking-wider font-semibold">Ternak Siap Suplai</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 sm:p-4 border border-white/10 text-center">
                            <span class="block text-2xl sm:text-3xl font-black text-white">100%</span>
                            <span class="text-[11px] sm:text-xs text-emerald-200 uppercase tracking-wider font-semibold">Legalitas & Faktur</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 sm:p-4 border border-white/10 text-center">
                            <span class="block text-2xl sm:text-3xl font-black text-amber-400">Tonase</span>
                            <span class="text-[11px] sm:text-xs text-emerald-200 uppercase tracking-wider font-semibold">Kapasitas Pasokan</span>
                        </div>
                    </div>

                    <!-- Dual CTAs -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                        <a
                            :href="whatsapp_url"
                            target="_blank"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400 hover:from-amber-300 hover:to-amber-400 text-emerald-950 font-black text-base shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform group"
                        >
                            <svg class="w-6 h-6 text-emerald-950 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span>Konsultasi Pengadaan via CS WhatsApp</span>
                        </a>

                        <button
                            @click="scrollToStock"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-base border border-white/20 transition backdrop-blur-sm"
                        >
                            <span>Cek Rincian Stok Subkategori</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Trust Bar Badges -->
                    <div class="pt-6 flex flex-wrap items-center justify-center gap-6 text-xs sm:text-sm text-emerald-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Harga Tangan Pertama (Farm Gate)
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Ternak Bebas PMK & Ber-SKKH
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Faktur Pajak & Kontrak Resmi (PKS)
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Beras Murni Tanpa Pemutih/Kimia
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEGMEN KLIEN B2B YANG KAMI LAYANI -->
        <section class="py-14 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="text-xs uppercase tracking-widest font-extrabold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                        Target Kemitraan Kami
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mt-3 tracking-tight">
                        Siap Memasok Segala Kebutuhan Skala Industri & Institusi
                    </h2>
                    <p class="text-sm sm:text-base text-gray-600 mt-2">
                        Kami merancang spesifikasi produk, sistem pengemasan, dan jadwal logistik yang disesuaikan dengan alur bisnis Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Card 1: HOREKA -->
                    <div class="bg-gray-50 hover:bg-emerald-50/50 p-6 rounded-2xl border border-gray-200 hover:border-emerald-300 transition shadow-xs group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-700 text-white flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <!-- Hotel / Resto Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">HOREKA & Katering</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Restoran chain, hotel berbintang, dan catering industri yang membutuhkan beras berkualitas konsisten dengan stabilitas rasa & tekstur nasi setiap hari.
                        </p>
                    </div>

                    <!-- Card 2: Korporasi & BUMN -->
                    <div class="bg-gray-50 hover:bg-emerald-50/50 p-6 rounded-2xl border border-gray-200 hover:border-emerald-300 transition shadow-xs group">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-emerald-950 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <!-- Corporation / Package Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Korporasi & BUMN</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Pengadaan paket sembako beras bulanan untuk ribuan karyawan, program CSR perusahaan, atau bingkisan hari raya dengan custom packaging khusus.
                        </p>
                    </div>

                    <!-- Card 3: Panitia Qurban & Filantropi -->
                    <div class="bg-gray-50 hover:bg-emerald-50/50 p-6 rounded-2xl border border-gray-200 hover:border-emerald-300 transition shadow-xs group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-800 text-white flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <!-- Livestock / Heart Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Qurban & Filantropi</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Panitia Qurban DKM Masjid, Lembaga Amil Zakat (LAZ), dan Yayasan Sosial. Kami sediakan ratusan ekor sapi, domba, kambing bersertifikat dengan fasilitas penitipan gratis.
                        </p>
                    </div>

                    <!-- Card 4: Distributor & Toko Grosir -->
                    <div class="bg-gray-50 hover:bg-emerald-50/50 p-6 rounded-2xl border border-gray-200 hover:border-emerald-300 transition shadow-xs group">
                        <div class="w-12 h-12 rounded-xl bg-green-700 text-white flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <!-- Store / Wholesale Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Distributor & Agen Grosir</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Pedagang besar pasar induk, agen beras daerah, serta toko bahan pokok yang membutuhkan pasokan rutin truk/kontainer dengan margin yang menguntungkan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- KEUNGGULAN UTAMA B2B KMM -->
        <section class="py-14 sm:py-20 bg-emerald-950 text-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs uppercase tracking-widest font-extrabold text-amber-300 bg-amber-400/20 px-3 py-1 rounded-full border border-amber-400/40">
                        Keunggulan Kompetitif
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white mt-3 tracking-tight">
                        Mengapa Korporasi Memilih Koperasi Mitra Mandiri (KMM)?
                    </h2>
                    <p class="text-sm sm:text-base text-emerald-200 mt-2">
                        Efisiensi rantai pasok agribisnis kami memberikan keuntungan nyata pada performa dan anggaran perusahaan Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-emerald-900/40 border border-emerald-700/60 rounded-2xl p-6 backdrop-blur-sm hover:border-amber-400/60 transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-emerald-950 font-black text-lg flex items-center justify-center mb-4">
                            1
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Harga Tangan Pertama (Farm Gate)</h3>
                        <p class="text-sm text-emerald-200 leading-relaxed">
                            Dihubungkan langsung ke sentra lumbung padi dan kandang ternak terpadu tanpa tengkulak bertingkat, memberikan margin terbaik bagi usaha Anda.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-emerald-900/40 border border-emerald-700/60 rounded-2xl p-6 backdrop-blur-sm hover:border-amber-400/60 transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-emerald-950 font-black text-lg flex items-center justify-center mb-4">
                            2
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Jaminan Kontinuitas & Tonase</h3>
                        <p class="text-sm text-emerald-200 leading-relaxed">
                            Kapasitas pasokan hingga puluhan ton beras per minggu dan ratusan ekor hewan kurban. Kami menjamin pasokan tidak terputus sepanjang tahun.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-emerald-900/40 border border-emerald-700/60 rounded-2xl p-6 backdrop-blur-sm hover:border-amber-400/60 transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-emerald-950 font-black text-lg flex items-center justify-center mb-4">
                            3
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Standarisasi & Uji Kesehatan</h3>
                        <p class="text-sm text-emerald-200 leading-relaxed">
                            Beras 100% alami tanpa pemutih & pengawet sintetis. Hewan ternak divaksinasi lengkap, bebas PMK, dan dilengkapi sertifikat SKKH resmi dari Dinas Peternakan.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-emerald-900/40 border border-emerald-700/60 rounded-2xl p-6 backdrop-blur-sm hover:border-amber-400/60 transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-emerald-950 font-black text-lg flex items-center justify-center mb-4">
                            4
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Legalitas & Dokumen Pajak Lengkap</h3>
                        <p class="text-sm text-emerald-200 leading-relaxed">
                            Transaksi aman dan akuntabel dengan Perjanjian Kerja Sama (PKS), Purchase Order (PO), Surat Jalan Resmi, Berita Acara (BAST), hingga Faktur Pajak.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-emerald-900/40 border border-emerald-700/60 rounded-2xl p-6 backdrop-blur-sm hover:border-amber-400/60 transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-emerald-950 font-black text-lg flex items-center justify-center mb-4">
                            5
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Custom Packaging & Private Label</h3>
                        <p class="text-sm text-emerald-200 leading-relaxed">
                            Dukungan pengemasan sesuai kebutuhan: Karung 50kg, 25kg, atau kemasan ritel 5kg dan 10kg dengan sablon logo/brand korporat khusus Anda.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-emerald-900/40 border border-emerald-700/60 rounded-2xl p-6 backdrop-blur-sm hover:border-amber-400/60 transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-emerald-950 font-black text-lg flex items-center justify-center mb-4">
                            6
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Layanan Logistik & Antar Gudang</h3>
                        <p class="text-sm text-emerald-200 leading-relaxed">
                            Dukungan armada truk mandiri siap mengirimkan barang langsung ke gudang, dapur hotel, atau lokasi titik penyembelihan qurban tepat waktu.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TABEL RINCIAN STOK REAL-TIME SEMUA SUBKATEGORI -->
        <section id="stock-breakdown" class="py-14 sm:py-20 bg-gray-50 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8 gap-4">
                    <div>
                        <span class="text-xs uppercase tracking-widest font-extrabold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-200">
                            Inventori & Pasokan Real-Time
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mt-2 tracking-tight">
                            Rincian Ketersediaan Stok Semua Subkategori
                        </h2>
                        <p class="text-sm text-gray-600 mt-1 max-w-2xl">
                            Data ketersediaan stok riil yang aktif dan siap dipesan dalam partai besar. Klik "Minta Penawaran" untuk terhubung langsung dengan Tim Sales B2B kami.
                        </p>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex items-center gap-1.5 bg-white p-1 rounded-xl border border-gray-200 shadow-xs self-start md:self-auto">
                        <button
                            @click="activeTab = 'all'"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition"
                            :class="activeTab === 'all' ? 'bg-emerald-800 text-white shadow-xs' : 'text-gray-600 hover:text-emerald-900 hover:bg-gray-100'"
                        >
                            Semua Subkategori
                        </button>
                        <button
                            @click="activeTab = 'rice'"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition"
                            :class="activeTab === 'rice' ? 'bg-emerald-800 text-white shadow-xs' : 'text-gray-600 hover:text-emerald-900 hover:bg-gray-100'"
                        >
                            🌾 Komoditas Beras
                        </button>
                        <button
                            @click="activeTab = 'livestock'"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition"
                            :class="activeTab === 'livestock' ? 'bg-emerald-800 text-white shadow-xs' : 'text-gray-600 hover:text-emerald-900 hover:bg-gray-100'"
                        >
                            🐄 Komoditas Ternak
                        </button>
                    </div>
                </div>

                <!-- Loop Kategori & Subkategori -->
                <div class="space-y-8">
                    <div
                        v-for="category in filteredCategories"
                        :key="category.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <!-- Kategori Card Header -->
                        <div class="px-6 py-4 bg-gradient-to-r from-emerald-900 to-green-900 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-800/80 rounded-xl text-amber-400 border border-emerald-700/60">
                                    <svg v-if="category.type === 'rice'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xl font-bold tracking-tight text-white">
                                            Kategori {{ category.name }}
                                        </h3>
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-400 text-emerald-950">
                                            {{ category.type === 'rice' ? 'Pangan Pokok' : 'Hewan Ternak' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-emerald-200">
                                        {{ category.subcategories?.length || 0 }} Subkategori/Kelas Tersedia
                                    </p>
                                </div>
                            </div>

                            <div class="text-xs text-emerald-200 sm:text-right">
                                Satuan Standar: <span class="text-amber-300 font-bold uppercase">{{ category.type === 'rice' ? 'Kilogram (kg) & Ton' : 'Ekor' }}</span>
                            </div>
                        </div>

                        <!-- Subcategory Table (Desktop) / Cards (Mobile) -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-left hidden sm:table">
                                <thead class="bg-gray-50 text-[11px] font-extrabold uppercase text-gray-500 tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3.5">Subkategori / Varietas</th>
                                        <th class="px-6 py-3.5">Karakteristik & Mutu</th>
                                        <th class="px-6 py-3.5 text-center">Spesifikasi Bobot</th>
                                        <th class="px-6 py-3.5 text-center">Ketersediaan Stok</th>
                                        <th class="px-6 py-3.5 text-right">Indikasi Harga</th>
                                        <th class="px-6 py-3.5 text-center">Aksi B2B</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-sm">
                                    <tr
                                        v-for="sub in category.subcategories"
                                        :key="sub.id"
                                        class="hover:bg-emerald-50/40 transition group"
                                    >
                                        <!-- Nama Subkategori -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-gray-900 group-hover:text-emerald-800 transition">
                                                {{ sub.label || sub.name }}
                                            </div>
                                            <span class="text-[11px] text-gray-400">
                                                {{ sub.active_products_count }} produk listing aktif
                                            </span>
                                        </td>

                                        <!-- Deskripsi Mutu -->
                                        <td class="px-6 py-4 max-w-xs text-xs text-gray-600 leading-relaxed">
                                            {{ getSubcategoryDesc(category, sub) }}
                                        </td>

                                        <!-- Spesifikasi Bobot -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-700">
                                            <template v-if="sub.min_weight_kg || sub.max_weight_kg">
                                                <span class="inline-block px-2.5 py-1 rounded-md bg-gray-100 font-semibold text-gray-800 border border-gray-200">
                                                    {{ sub.min_weight_kg ? sub.min_weight_kg : '<' }} 
                                                    {{ sub.min_weight_kg && sub.max_weight_kg ? '–' : '' }} 
                                                    {{ sub.max_weight_kg ? sub.max_weight_kg : '>' }} kg
                                                </span>
                                            </template>
                                            <template v-else>
                                                <span class="text-gray-400 italic">Standar Varietas</span>
                                            </template>
                                        </td>

                                        <!-- Ketersediaan Stok -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="text-base font-extrabold text-gray-900">
                                                    {{ formatNumber(sub.total_stock) }}
                                                    <span class="text-xs font-semibold text-gray-500">
                                                        {{ category.type === 'rice' ? 'kg' : 'ekor' }}
                                                    </span>
                                                </div>
                                                <span
                                                    v-if="(sub.total_stock || 0) > 0"
                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full mt-0.5"
                                                >
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    Siap Kirim
                                                </span>
                                                <span
                                                    v-else
                                                    class="inline-flex items-center gap-1 text-[10px] font-medium text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full mt-0.5"
                                                >
                                                    Open Pre-Order / Kontrak
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Indikasi Harga -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <template v-if="sub.min_price">
                                                <div class="font-extrabold text-emerald-800">
                                                    {{ formatRupiah(sub.min_price) }}
                                                </div>
                                                <div v-if="sub.max_price && sub.max_price !== sub.min_price" class="text-[11px] text-gray-400">
                                                    s/d {{ formatRupiah(sub.max_price) }}
                                                </div>
                                                <span class="text-[10px] text-gray-400 italic block mt-0.5">
                                                    *Nego partai besar
                                                </span>
                                            </template>
                                            <template v-else>
                                                <span class="text-xs text-gray-500 italic font-medium">
                                                    Hubungi CS
                                                </span>
                                            </template>
                                        </td>

                                        <!-- Aksi WA -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <a
                                                :href="makeSubcategoryWaUrl(category, sub)"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs hover:shadow transition"
                                            >
                                                <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                                </svg>
                                                <span>Minta Penawaran</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Mobile Card Layout -->
                            <div class="sm:hidden divide-y divide-gray-100">
                                <div
                                    v-for="sub in category.subcategories"
                                    :key="sub.id"
                                    class="p-4 space-y-2.5"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-base">
                                                {{ sub.label || sub.name }}
                                            </h4>
                                            <span v-if="sub.min_weight_kg || sub.max_weight_kg" class="text-xs font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded">
                                                {{ sub.min_weight_kg || '<' }} – {{ sub.max_weight_kg || '>' }} kg
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-base font-extrabold text-gray-900">
                                                {{ formatNumber(sub.total_stock) }} {{ category.type === 'rice' ? 'kg' : 'ekor' }}
                                            </div>
                                            <span
                                                v-if="(sub.total_stock || 0) > 0"
                                                class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full"
                                            >
                                                Siap Kirim
                                            </span>
                                            <span
                                                v-else
                                                class="text-[10px] font-medium text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full"
                                            >
                                                Open PO
                                            </span>
                                        </div>
                                    </div>

                                    <p class="text-xs text-gray-600">
                                        {{ getSubcategoryDesc(category, sub) }}
                                    </p>

                                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                        <div>
                                            <span class="text-[10px] text-gray-400 block uppercase">Indikasi Harga</span>
                                            <span class="text-sm font-extrabold text-emerald-800">
                                                {{ sub.min_price ? formatRupiah(sub.min_price) : 'Nego B2B' }}
                                            </span>
                                        </div>
                                        <a
                                            :href="makeSubcategoryWaUrl(category, sub)"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-700 text-white font-bold text-xs"
                                        >
                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                            <span>Minta Penawaran</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ALUR PENGADAAN (5 LANGKAH MUDAH) -->
        <section class="py-14 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs uppercase tracking-widest font-extrabold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                        Prosedur Transparan & Profesional
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mt-3 tracking-tight">
                        5 Tahap Mudah Menjadi Mitra Pengadaan KMM
                    </h2>
                    <p class="text-sm sm:text-base text-gray-600 mt-2">
                        Proses pengadaan yang terstruktur rapi, cepat, dan akuntabel dari awal negosiasi hingga barang diterima di gudang Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 relative">
                    <!-- Step 1 -->
                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200 relative text-center">
                        <div class="w-10 h-10 rounded-full bg-emerald-800 text-white font-extrabold text-sm flex items-center justify-center mx-auto mb-3">
                            01
                        </div>
                        <h4 class="font-bold text-gray-900 text-base mb-1">Konsultasi</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Diskusikan varietas, estimasi volume tonase/ekor, dan jadwal pengiriman yang Anda butuhkan.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200 relative text-center">
                        <div class="w-10 h-10 rounded-full bg-emerald-800 text-white font-extrabold text-sm flex items-center justify-center mx-auto mb-3">
                            02
                        </div>
                        <h4 class="font-bold text-gray-900 text-base mb-1">Sampel & SPH</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Kami kirimkan sampel uji fisik beras serta Surat Penawaran Harga resmi (B2B Price List).
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200 relative text-center">
                        <div class="w-10 h-10 rounded-full bg-emerald-800 text-white font-extrabold text-sm flex items-center justify-center mx-auto mb-3">
                            03
                        </div>
                        <h4 class="font-bold text-gray-900 text-base mb-1">Kontrak / PO</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Penandatanganan SPK/MOU pasokan berkala, kesepakatan termin bayar, dan spesifikasi kemasan.
                        </p>
                    </div>

                    <!-- Step 4 -->
                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200 relative text-center">
                        <div class="w-10 h-10 rounded-full bg-emerald-800 text-white font-extrabold text-sm flex items-center justify-center mx-auto mb-3">
                            04
                        </div>
                        <h4 class="font-bold text-gray-900 text-base mb-1">QC & Logistik</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Sortir ketat, uji mutu kadar air, pengecekan dokter hewan, dan pengiriman armada berjadwal.
                        </p>
                    </div>

                    <!-- Step 5 -->
                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200 relative text-center">
                        <div class="w-10 h-10 rounded-full bg-amber-500 text-emerald-950 font-black text-sm flex items-center justify-center mx-auto mb-3">
                            05
                        </div>
                        <h4 class="font-bold text-gray-900 text-base mb-1">BAST & Faktur</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Penerimaan barang di gudang Anda, penandatanganan BAST, dan penerbitan faktur pajak resmi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ SINGKAT B2B -->
        <section class="py-14 bg-gray-50 border-t border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h3 class="text-2xl font-extrabold text-gray-900">
                        Pertanyaan Seputar Pengadaan B2B
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Informasi teknis yang sering ditanyakan oleh mitra korporasi & pengadaan
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs">
                        <h4 class="text-base font-bold text-gray-900 mb-1">Berapa batas minimum pemesanan (MOQ) untuk harga grosir B2B?</h4>
                        <p class="text-sm text-gray-600">
                            Untuk komoditas beras, batas minimum pemesanan harga B2B mulai dari 500 kg (atau akumulasi mingguan). Untuk komoditas hewan ternak, minimum pemesanan mulai dari 5 ekor sapi atau 10 ekor domba/kambing. Untuk volume lebih besar, tersedia skema negosiasi harga khusus.
                        </p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs">
                        <h4 class="text-base font-bold text-gray-900 mb-1">Apakah perusahaan bisa mengajukan termin pembayaran (Term of Payment)?</h4>
                        <p class="text-sm text-gray-600">
                            Bisa. Untuk korporasi, institusi pemerintah, dan BUMN yang memiliki legalitas usaha lengkap, kami menyediakan opsi sistem pembayaran termin (TOP 14 hingga 30 hari) setelah melalui verifikasi berkas kerja sama resmi.
                        </p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs">
                        <h4 class="text-base font-bold text-gray-900 mb-1">Apakah beras bisa dikemas dengan merek/logo perusahaan kami sendiri (Private Label)?</h4>
                        <p class="text-sm text-gray-600">
                            Sangat bisa. Kami melayani fasilitas custom packaging karung beras (kemasan 5kg, 10kg, 25kg, hingga 50kg) yang sangat diminati untuk program bansos CSR, bingkisan Idul Fitri, maupun kemasan private brand ritel Anda.
                        </p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs">
                        <h4 class="text-base font-bold text-gray-900 mb-1">Bagaimana dengan pengantaran hewan qurban saat hari raya?</h4>
                        <p class="text-sm text-gray-600">
                            Kami menyediakan fasilitas karantina dan penitipan hewan qurban secara gratis hingga H-1 atau hari H penyembelihan, lengkap dengan pengiriman langsung menggunakan armada kami ke lokasi masjid, kantor, atau lapangan penyembelihan Anda.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA UTAMA: HUBUNGI CS B2B -->
        <section class="py-16 sm:py-24 bg-gradient-to-r from-emerald-900 via-green-900 to-emerald-950 text-white relative overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-6">
                <span class="inline-block px-3 py-1 rounded-full bg-amber-400 text-emerald-950 text-xs font-black tracking-wider uppercase">
                    Mulai Kerja Sama Hari Ini
                </span>

                <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">
                    Siap Mengamankan Rantai Pasok Pangan & Ternak Perusahaan Anda?
                </h2>

                <p class="text-base sm:text-lg text-emerald-100/90 max-w-3xl mx-auto leading-relaxed">
                    Diskusikan kebutuhan volume, jadwal suplai berkala, serta permohonan surat penawaran harga (SPH) resmi dengan Tim Sales B2B & Customer Service KMM sekarang.
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        :href="whatsapp_url"
                        target="_blank"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400 hover:from-amber-300 hover:to-amber-400 text-emerald-950 font-black text-lg shadow-xl hover:shadow-2xl hover:-translate-y-1 transition transform"
                    >
                        <svg class="w-6 h-6 text-emerald-950" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Chat WhatsApp Tim CS B2B ({{ cs_whatsapp }})</span>
                    </a>

                    <Link
                        :href="route('catalog.index')"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-base border border-white/20 transition backdrop-blur-sm"
                    >
                        <span>Kembali ke Katalog Produk</span>
                    </Link>
                </div>

                <div class="pt-6 text-xs text-emerald-200/80 flex flex-wrap items-center justify-center gap-6">
                    <span>🕒 Jam Operasional CS: Senin – Sabtu, 08.00 – 17.00 WIB</span>
                    <span>📍 Jaringan Petani & Peternak Binaan KMM</span>
                    <span>🤝 Kemitraan Resmi Berbadan Hukum</span>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
