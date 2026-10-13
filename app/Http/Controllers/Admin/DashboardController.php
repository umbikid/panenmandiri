<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalPartners = User::role('mitra')->count();
        $totalBuyers = User::role('pembeli')->count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
        
        $totalStockRice = Product::whereHas('category', fn($q) => $q->where('type', 'rice'))->sum('stock');
        $totalStockLivestock = Product::whereHas('category', fn($q) => $q->where('type', 'livestock'))->sum('stock');
        
        $riceSubcategories = Subcategory::whereHas('category', fn($q) => $q->where('type', 'rice'))
            ->with(['category:id,name,type'])
            ->withSum('products as total_stock', 'stock')
            ->withCount('products as total_products')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'label' => $sub->label ?: $sub->name,
                    'category_name' => $sub->category->name ?? 'Beras',
                    'total_stock' => (float) ($sub->total_stock ?? 0),
                    'unit' => 'kg',
                    'total_products' => (int) ($sub->total_products ?? 0),
                ];
            });

        $livestockSubcategories = Subcategory::whereHas('category', fn($q) => $q->where('type', 'livestock'))
            ->with(['category:id,name,type'])
            ->withSum('products as total_stock', 'stock')
            ->withCount('products as total_products')
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'label' => $sub->label ?: $sub->name,
                    'category_name' => $sub->category->name ?? 'Ternak',
                    'min_weight_kg' => $sub->min_weight_kg,
                    'max_weight_kg' => $sub->max_weight_kg,
                    'total_stock' => (float) ($sub->total_stock ?? 0),
                    'unit' => 'ekor',
                    'total_products' => (int) ($sub->total_products ?? 0),
                ];
            });

        $recentOrders = Order::with(['buyer', 'deliveryRegency', 'items.product'])
            ->latest()
            ->take(5)
            ->get();

        $recentProducts = Product::with(['partner', 'category', 'subcategory', 'primaryPhoto'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'total_products' => $totalProducts,
                'total_partners' => $totalPartners,
                'total_buyers' => $totalBuyers,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'confirmed_orders' => $confirmedOrders,
                'total_stock_rice' => $totalStockRice,
                'total_stock_livestock' => $totalStockLivestock,
                'rice_subcategories' => $riceSubcategories,
                'livestock_subcategories' => $livestockSubcategories,
            ],
            'recent_orders' => $recentOrders,
            'recent_products' => $recentProducts,
        ]);
    }
}
