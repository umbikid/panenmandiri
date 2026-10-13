<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['partner', 'category', 'subcategory', 'primaryPhoto', 'coverages.regency']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('partner', function ($qp) use ($search) {
                      $qp->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::all(['id', 'name', 'type']);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'q' => $request->q,
                'category_id' => $request->category_id,
                'status' => $request->status,
            ],
        ]);
    }

    public function updatePrice(Request $request, Product $product)
    {
        $request->validate([
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product->update([
            'selling_price' => $request->selling_price,
            'price_set_by' => $request->user()->id,
            'price_set_at' => now(),
        ]);

        return back()->with('success', "Harga jual produk '{$product->name}' berhasil diperbarui.");
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:active,hidden,sold_out',
        ]);

        $product->update([
            'status' => $request->status,
        ]);

        return back()->with('success', "Status produk '{$product->name}' berhasil diubah ke {$request->status}.");
    }
}
