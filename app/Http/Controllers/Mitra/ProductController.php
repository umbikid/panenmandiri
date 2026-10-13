<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\Province;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('partner_id', $request->user()->id)
            ->with(['category', 'subcategory', 'primaryPhoto'])
            ->latest()
            ->get();

        return Inertia::render('Mitra/Product/Index', [
            'products' => $products
        ]);
    }

    public function create()
    {
        $categories = Category::with('subcategories')->orderBy('sort_order')->get();
        $provinces = Province::with('regencies')->get();

        return Inertia::render('Mitra/Product/Create', [
            'categories' => $categories,
            'provinces' => $provinces,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('unit')) {
            $request->merge(['unit' => strtolower($request->unit)]);
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:200'],
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['required', 'integer', 'exists:subcategories,id'],
            'stock'          => ['required', 'numeric', 'min:0', 'max:99999999'],
            'unit'           => ['required', 'string'],
            'price'          => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'regency_ids'    => ['required', 'array', 'min:1', 'max:600'],
            'regency_ids.*'  => ['integer', 'distinct', 'exists:regencies,id'],
            'photos'         => ['required', 'array', 'min:1', 'max:5'],
            'photos.*'       => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $product = Product::create([
            'partner_id' => $request->user()->id,
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'],
            'name' => $validated['name'],
            'partner_price' => $validated['price'],
            'selling_price' => $validated['price'], // admin can change this later
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
            'status' => 'active',
        ]);

        foreach ($validated['regency_ids'] as $regencyId) {
            $product->coverages()->create(['regency_id' => $regencyId]);
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photoFile) {
                $path = $photoFile->store('products', 'public');
                $product->photos()->create([
                    'path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('mitra.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, Product $product)
    {
        // Pastikan milik mitra yang login
        if ($product->partner_id !== $request->user()->id) {
            abort(403);
        }

        $product->load('coverages', 'photos');
        $categories = Category::with('subcategories')->orderBy('sort_order')->get();
        $provinces = Province::with('regencies')->get();

        return Inertia::render('Mitra/Product/Edit', [
            'product' => $product,
            'categories' => $categories,
            'provinces' => $provinces,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->partner_id !== $request->user()->id) {
            abort(403);
        }

        if ($request->has('unit')) {
            $request->merge(['unit' => strtolower($request->unit)]);
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:200'],
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['required', 'integer', 'exists:subcategories,id'],
            'stock'          => ['required', 'numeric', 'min:0', 'max:99999999'],
            'unit'           => ['required', 'string'],
            'price'          => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'regency_ids'    => ['required', 'array', 'min:1', 'max:600'],
            'regency_ids.*'  => ['integer', 'distinct', 'exists:regencies,id'],
            'photos'         => ['nullable', 'array', 'max:5'],
            'photos.*'       => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'],
            'name' => $validated['name'],
            'partner_price' => $validated['price'],
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
        ]);

        // Sync coverages
        $product->coverages()->delete();
        foreach ($validated['regency_ids'] as $regencyId) {
            $product->coverages()->create(['regency_id' => $regencyId]);
        }

        if ($request->hasFile('photos')) {
            $existingCount = $product->photos()->count();
            foreach ($request->file('photos') as $index => $photoFile) {
                $path = $photoFile->store('products', 'public');
                $product->photos()->create([
                    'path' => $path,
                    'is_primary' => $existingCount === 0 && $index === 0,
                    'sort_order' => $existingCount + $index,
                ]);
            }
        }

        return redirect()->route('mitra.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->partner_id !== $request->user()->id) {
            abort(403);
        }

        // Hapus file gambar dari storage
        $photos = $product->photos;
        foreach ($photos as $photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->path);
        }

        // Hapus produk (cascade delete covers photos and coverages because of DB foreign keys, 
        // tapi pastikan didelete di model jika on delete cascade belum diset, atau secara implisit photos akan terhapus karena constrained cascade, tapi amannya delete relations via model atau biarkan cascade. Di create_models script, foreign key menggunakan onDelete('cascade'))
        $product->delete();

        return redirect()->route('mitra.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}

