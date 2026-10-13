<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import RegionTree from '@/Components/RegionTree.vue';
import imageCompression from 'browser-image-compression';

const props = defineProps({
    product: Object,
    categories: Array,
    provinces: Array,
});

const form = useForm({
    _method: 'put',
    name: props.product.name,
    category_id: props.product.category_id,
    subcategory_id: props.product.subcategory_id,
    stock: props.product.stock,
    unit: props.product.unit,
    price: props.product.partner_price,
    regency_ids: props.product.coverages.map(c => c.regency_id),
    photos: []
});

const existingPhotos = ref(props.product.photos || []);
const imagePreviews = ref([]);

const handleFileChange = async (event) => {
    const files = Array.from(event.target.files);
    
    if (existingPhotos.value.length + form.photos.length + files.length > 5) {
        alert('Maksimal 5 foto produk.');
        event.target.value = '';
        return;
    }

    const options = {
        maxSizeMB: 2,
        maxWidthOrHeight: 1024,
        useWebWorker: true
    };

    for (let i = 0; i < files.length; i++) {
        try {
            const compressedFile = await imageCompression(files[i], options);
            form.photos.push(compressedFile);
            
            const reader = new FileReader();
            reader.readAsDataURL(compressedFile);
            reader.onload = () => {
                imagePreviews.value.push(reader.result);
            };
        } catch (error) {
            console.error('Error compressing image:', error);
            alert('Gagal memproses gambar ' + files[i].name);
        }
    }
    
    event.target.value = '';
};

const removeNewPhoto = (index) => {
    form.photos.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

// Functionality to delete existing photo via API could go here
// For now we just let them add up to 5 photos

const activeCategory = computed(() => {
    return props.categories.find(c => c.id === form.category_id);
});

const subcategories = computed(() => {
    return activeCategory.value ? activeCategory.value.subcategories : [];
});

const formattedPrice = computed({
    get() {
        if (!form.price && form.price !== 0) return '';
        return form.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    set(value) {
        const rawValue = value.replace(/\D/g, '');
        form.price = rawValue ? parseInt(rawValue, 10) : '';
    }
});

const autoUnit = computed(() => {
    if (activeCategory.value) {
        return activeCategory.value.name.toLowerCase() === 'beras' ? 'Kg' : 'Ekor';
    }
    return '';
});

watch(activeCategory, (newCat) => {
    if (newCat) {
        form.unit = newCat.name.toLowerCase() === 'beras' ? 'Kg' : 'Ekor';
    }
}, { immediate: true });

const submit = () => {
    // Note: Because of file uploads, we must use post with _method=put
    form.post(route('mitra.products.update', props.product.id));
};
</script>

<template>
    <Head title="Edit Produk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('mitra.products.index')" class="text-gray-500 hover:text-gray-700">
                    &larr; Kembali
                </Link>
                <h2 class="font-semibold text-xl text-green-900 leading-tight">Edit Produk: {{ product.name }}</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Nama Produk -->
                        <div>
                            <InputLabel for="name" value="Nama Produk (maks 200 karakter)" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                maxlength="200"
                            />
                            <div class="text-right text-xs text-gray-500 mt-1">{{ form.name.length }}/200</div>
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Foto Produk -->
                        <div>
                            <InputLabel value="Foto Produk (Wajib 1, Maks 5)" />
                            <p class="text-xs text-gray-500 mb-2">Tambah foto baru. Gambar otomatis dikompres sebelum diunggah.</p>
                            
                            <div class="flex flex-wrap gap-4 mt-2">
                                <!-- Existing Photos (Readonly for now) -->
                                <div v-for="(photo, index) in existingPhotos" :key="'ex'+index" class="relative border border-gray-300 rounded p-1 w-24 h-24">
                                    <img :src="'/storage/' + photo.path" class="w-full h-full object-cover rounded" />
                                    <div v-if="photo.is_primary" class="absolute bottom-0 left-0 right-0 bg-green-700 text-white text-[10px] text-center bg-opacity-80 py-0.5 rounded-b">Utama</div>
                                </div>

                                <!-- New Photos Preview -->
                                <div v-for="(preview, index) in imagePreviews" :key="'new'+index" class="relative border border-blue-300 rounded p-1 w-24 h-24">
                                    <img :src="preview" class="w-full h-full object-cover rounded" />
                                    <button type="button" @click="removeNewPhoto(index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-red-700">
                                        &times;
                                    </button>
                                    <div class="absolute bottom-0 left-0 right-0 bg-blue-500 text-white text-[10px] text-center bg-opacity-80 py-0.5 rounded-b">Baru</div>
                                </div>
                                
                                <label v-if="(existingPhotos.length + form.photos.length) < 5" class="w-24 h-24 border-2 border-dashed border-gray-300 rounded flex flex-col items-center justify-center text-gray-500 hover:text-green-600 hover:border-green-600 cursor-pointer">
                                    <span class="text-2xl">+</span>
                                    <span class="text-xs">Tambah</span>
                                    <input type="file" class="hidden" accept="image/jpeg, image/png, image/webp" multiple @change="handleFileChange" />
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.photos" />
                            <InputError class="mt-2" v-for="(err, idx) in form.errors" :key="idx" :message="idx.startsWith('photos.') ? err : ''" />
                        </div>

                        <!-- Kategori & Subkategori -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="category_id" value="Kategori" />
                                <select v-model="form.category_id" id="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-green-700 rounded-md shadow-sm" required @change="form.subcategory_id = ''">
                                    <option value="" disabled>Pilih Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.category_id" />
                            </div>

                            <div>
                                <InputLabel for="subcategory_id" value="Sub Kategori" />
                                <select v-model="form.subcategory_id" id="subcategory_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-green-700 rounded-md shadow-sm" required :disabled="!form.category_id">
                                    <option value="" disabled>Pilih Sub Kategori</option>
                                    <option v-for="sub in subcategories" :key="sub.id" :value="sub.id">{{ sub.label }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.subcategory_id" />
                            </div>
                        </div>

                        <!-- Stok & Satuan -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="col-span-2">
                                <InputLabel for="stock" value="Jumlah Stok" />
                                <TextInput
                                    id="stock"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    v-model="form.stock"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.stock" />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Satuan" />
                                <div class="mt-1 flex items-center h-10 px-3 bg-gray-50 border border-gray-300 rounded-md text-gray-700 font-semibold shadow-sm">
                                    {{ autoUnit || '-' }}
                                </div>
                                <InputError class="mt-2" :message="form.errors.unit" />
                            </div>
                        </div>

                        <!-- Harga -->
                        <div>
                            <InputLabel for="price" value="Harga Mitra (per satuan)" />
                            <div class="flex items-center mt-1">
                                <span class="px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-md text-gray-500">Rp</span>
                                <TextInput
                                    id="price"
                                    type="text"
                                    class="block w-full rounded-l-none"
                                    v-model="formattedPrice"
                                    required
                                />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Harga yang Anda patok. Harga jual ke pembeli akan ditetapkan oleh Admin.</p>
                            <InputError class="mt-2" :message="form.errors.price" />
                        </div>

                        <!-- Wilayah -->
                        <div>
                            <InputLabel value="Jangkauan Area Penjualan (Kota/Kabupaten)" />
                            <RegionTree :provinces="provinces" v-model="form.regency_ids" class="mt-2" />
                            <InputError class="mt-2" :message="form.errors.regency_ids" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton class="ml-4 bg-green-700 hover:bg-green-800" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan Perubahan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>