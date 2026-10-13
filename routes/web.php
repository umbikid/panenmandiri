<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Mitra\DashboardController as MitraDashboardController;
use App\Http\Controllers\Mitra\ProductController as MitraProductController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\CatalogController;
use Illuminate\Http\Request;

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/produk/{id}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/api/districts/search', function (Request $request) {
    // ... old search logic, but we can keep or remove it
})->name('api.districts.search');

Route::get('/api/provinces/{province_id}/regencies', function ($province_id) {
    return \App\Models\Regency::where('province_id', $province_id)->orderBy('name')->get();
});

Route::get('/api/regencies/{regency_id}/districts', function ($regency_id) {
    return \App\Models\District::where('regency_id', $regency_id)->orderBy('name')->get();
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routing khusus Mitra
Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/', [MitraDashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', MitraProductController::class);
});

// Routing khusus Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Kelola Produk & Harga Jual
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::patch('/products/{product}/price', [AdminProductController::class, 'updatePrice'])->name('products.update-price');
    Route::patch('/products/{product}/status', [AdminProductController::class, 'updateStatus'])->name('products.update-status');
    
    // Kelola Pesanan / Transaksi
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    
    // Kelola Pengguna
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.update-status');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    
    // Pengaturan
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});

use App\Http\Controllers\OrderController;
Route::middleware(['auth', 'role:pembeli|admin'])->group(function () {
    Route::get('/checkout/{product}', [OrderController::class, 'create'])->name('checkout.create');
    Route::post('/checkout/{product}', [OrderController::class, 'store'])->name('checkout.store');
    
    // Riwayat Pembelian Pembeli
    Route::get('/pembelian', [OrderController::class, 'index'])->name('buyer.orders.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
