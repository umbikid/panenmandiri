<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Province;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('status', 'active')
            ->when($request->q, function ($q, $kw) {
                $q->where('name', 'like', '%' . addcslashes($kw, '%_') . '%');
            })
            ->when($request->category, function ($q, $c) {
                $q->whereHas('category', function ($q) use ($c) {
                    $q->where('name', $c);
                });
            })
            ->when($request->regency_id, function ($q, $id) {
                $q->whereHas('coverages', function ($q) use ($id) {
                    $q->where('regency_id', $id);
                });
            })
            ->with(['category', 'subcategory', 'primaryPhoto', 'photos', 'partner'])
            ->latest()
            ->paginate(12);

        $categories = Category::orderBy('sort_order')->get();
        // Caching provinces could be good but for now we just load them
        $provinces = Province::with('regencies')->get();

        return Inertia::render('Catalog/Index', [
            'products' => $products,
            'categories' => $categories,
            'provinces' => $provinces,
            'filters' => $request->only(['q', 'category', 'regency_id'])
        ]);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        if ($product->status !== 'active') {
            abort(404);
        }

        $product->load(['category', 'subcategory', 'photos', 'partner', 'coverages.regency']);
        
        return Inertia::render('Catalog/Show', [
            'product' => $product
        ]);
    }
}
