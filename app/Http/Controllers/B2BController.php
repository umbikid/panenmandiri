<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class B2BController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with(['subcategories' => function ($q) {
                $q->where('is_active', true)
                    ->orderBy('sort_order')
                    ->withSum(['products as total_stock' => function ($sq) {
                        $sq->where('status', 'active');
                    }], 'stock')
                    ->withCount(['products as active_products_count' => function ($sq) {
                        $sq->where('status', 'active');
                    }])
                    ->withMin(['products as min_price' => function ($sq) {
                        $sq->where('status', 'active');
                    }], 'selling_price')
                    ->withMax(['products as max_price' => function ($sq) {
                        $sq->where('status', 'active');
                    }], 'selling_price');
            }])
            ->orderBy('sort_order')
            ->get();

        $totalStockRice = (float) Product::where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('type', 'rice'))
            ->sum('stock');

        $totalStockLivestock = (float) Product::where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('type', 'livestock'))
            ->sum('stock');

        $csWhatsapp = Setting::where('key', 'cs_whatsapp_number')->value('value') ?? '6281234567890';
        $cleanWhatsapp = preg_replace('/[^0-9]/', '', (string)$csWhatsapp);
        if (str_starts_with($cleanWhatsapp, '0')) {
            $cleanWhatsapp = '62' . substr($cleanWhatsapp, 1);
        }

        $defaultMessage = "Halo Tim B2B Panen Mandiri / KMM, saya tertarik dengan kerja sama pengadaan komoditas skala besar (B2B). Mohon informasi penawaran kerja sama, ketersediaan stok, dan katalog harga resmi.";
        $whatsappUrl = "https://wa.me/{$cleanWhatsapp}?text=" . urlencode($defaultMessage);

        return Inertia::render('B2B/Index', [
            'categories' => $categories,
            'metrics' => [
                'total_stock_rice' => $totalStockRice,
                'total_stock_livestock' => $totalStockLivestock,
            ],
            'cs_whatsapp' => $csWhatsapp,
            'clean_whatsapp' => $cleanWhatsapp,
            'whatsapp_url' => $whatsappUrl,
        ]);
    }
}
