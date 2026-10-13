<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $totalProducts = Product::where('partner_id', $userId)->count();
        $lowStockProducts = Product::where('partner_id', $userId)->where('stock', '<=', 5)->count();

        $stockPerCategory = \App\Models\Category::withSum(['products' => function ($query) use ($userId) {
            $query->where('partner_id', $userId);
        }], 'stock')->get()->map(function ($category) {
            return [
                'name' => $category->name,
                'stock' => $category->products_sum_stock ?? 0,
                'unit' => strtolower($category->name) === 'beras' ? 'Kg' : 'Ekor'
            ];
        });

        return Inertia::render('Mitra/Dashboard', [
            'totalProducts' => $totalProducts,
            'lowStockProducts' => $lowStockProducts,
            'stockPerCategory' => $stockPerCategory,
        ]);
    }
}
