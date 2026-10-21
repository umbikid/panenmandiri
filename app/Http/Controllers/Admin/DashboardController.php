<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\ExportHelper;
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

    public function exportStock(Request $request)
    {
        $type = $request->query('type', 'livestock');
        $format = $request->query('format', 'excel');

        if ($type === 'rice') {
            $title = 'Laporan Rincian Stok Subkategori Beras';
            $unit = 'kg';
            $subcategories = Subcategory::whereHas('category', fn($q) => $q->where('type', 'rice'))
                ->with(['category:id,name,type'])
                ->withSum('products as total_stock', 'stock')
                ->withCount('products as total_products')
                ->orderBy('sort_order')
                ->get();

            $columns = [
                ['key' => 'no', 'label' => 'No', 'class' => 'text-center'],
                ['key' => 'category', 'label' => 'Komoditas'],
                ['key' => 'name', 'label' => 'Subkategori Beras'],
                ['key' => 'total_products', 'label' => 'Jumlah Produk Mitra', 'class' => 'text-center'],
                ['key' => 'stock_formatted', 'label' => "Total Stok ($unit)", 'class' => 'text-right font-bold'],
                ['key' => 'status', 'label' => 'Status', 'class' => 'text-center'],
            ];

            $rows = [];
            $sumStock = 0;
            $sumProducts = 0;

            foreach ($subcategories as $idx => $sub) {
                $stock = (float) ($sub->total_stock ?? 0);
                $productsCount = (int) ($sub->total_products ?? 0);
                $sumStock += $stock;
                $sumProducts += $productsCount;

                $rows[] = [
                    'no' => $idx + 1,
                    'category' => $sub->category->name ?? 'Beras',
                    'name' => $sub->label ?: $sub->name,
                    'total_products' => $productsCount,
                    'stock_formatted' => number_format($stock, 2, ',', '.') . " $unit",
                    'status' => $stock > 0 ? '<span class="badge badge-success">Tersedia</span>' : '<span class="badge badge-gray">Kosong</span>',
                    'raw_status' => $stock > 0 ? 'Tersedia' : 'Kosong',
                ];
            }

            $summaryRow = [
                'no' => '',
                'category' => 'TOTAL KESELURUHAN',
                'name' => '',
                'total_products' => $sumProducts,
                'stock_formatted' => number_format($sumStock, 2, ',', '.') . " $unit",
                'status' => '',
            ];
        } else {
            $title = 'Laporan Rincian Stok Subkategori Ternak';
            $unit = 'ekor';
            $subcategories = Subcategory::whereHas('category', fn($q) => $q->where('type', 'livestock'))
                ->with(['category:id,name,type'])
                ->withSum('products as total_stock', 'stock')
                ->withCount('products as total_products')
                ->orderBy('category_id')
                ->orderBy('sort_order')
                ->get();

            $columns = [
                ['key' => 'no', 'label' => 'No', 'class' => 'text-center'],
                ['key' => 'category', 'label' => 'Komoditas Ternak'],
                ['key' => 'name', 'label' => 'Subkategori / Kelas'],
                ['key' => 'weight_range', 'label' => 'Rentang Bobot'],
                ['key' => 'total_products', 'label' => 'Jumlah Produk Mitra', 'class' => 'text-center'],
                ['key' => 'stock_formatted', 'label' => "Total Stok ($unit)", 'class' => 'text-right font-bold'],
                ['key' => 'status', 'label' => 'Status', 'class' => 'text-center'],
            ];

            $rows = [];
            $sumStock = 0;
            $sumProducts = 0;

            foreach ($subcategories as $idx => $sub) {
                $stock = (float) ($sub->total_stock ?? 0);
                $productsCount = (int) ($sub->total_products ?? 0);
                $sumStock += $stock;
                $sumProducts += $productsCount;

                $weightRange = '-';
                if ($sub->min_weight_kg && $sub->max_weight_kg) {
                    $weightRange = "{$sub->min_weight_kg} - {$sub->max_weight_kg} kg";
                } elseif ($sub->min_weight_kg) {
                    $weightRange = "> {$sub->min_weight_kg} kg";
                } elseif ($sub->max_weight_kg) {
                    $weightRange = "< {$sub->max_weight_kg} kg";
                }

                $rows[] = [
                    'no' => $idx + 1,
                    'category' => $sub->category->name ?? 'Ternak',
                    'name' => $sub->label ?: $sub->name,
                    'weight_range' => $weightRange,
                    'total_products' => $productsCount,
                    'stock_formatted' => number_format($stock, 0, ',', '.') . " $unit",
                    'status' => $stock > 0 ? '<span class="badge badge-success">Tersedia</span>' : '<span class="badge badge-gray">Kosong</span>',
                    'raw_status' => $stock > 0 ? 'Tersedia' : 'Kosong',
                ];
            }

            $summaryRow = [
                'no' => '',
                'category' => 'TOTAL KESELURUHAN',
                'name' => '',
                'weight_range' => '',
                'total_products' => $sumProducts,
                'stock_formatted' => number_format($sumStock, 0, ',', '.') . " $unit",
                'status' => '',
            ];
        }

        if ($format === 'pdf') {
            return ExportHelper::renderPrintView(
                $title,
                $columns,
                $rows,
                "Komoditas: " . ($type === 'rice' ? 'Beras & Pangan' : 'Hewan Ternak & Qurban'),
                $summaryRow,
                'portrait'
            );
        }

        // Excel / CSV
        $filename = 'Laporan_Stok_' . ucfirst($type) . '_' . date('Y-m-d_His') . '.csv';
        $csvHeaders = array_map(fn($c) => $c['label'], $columns);
        $csvRows = [];

        foreach ($rows as $row) {
            $csvRow = [];
            foreach ($columns as $col) {
                if ($col['key'] === 'status') {
                    $csvRow[] = $row['raw_status'] ?? 'Kosong';
                } else {
                    $csvRow[] = $row[$col['key']] ?? '';
                }
            }
            $csvRows[] = $csvRow;
        }

        // Add summary row to CSV
        $summaryCsv = [];
        foreach ($columns as $col) {
            $summaryCsv[] = $summaryRow[$col['key']] ?? '';
        }
        $csvRows[] = $summaryCsv;

        return ExportHelper::downloadCsv($filename, $csvHeaders, $csvRows);
    }
}
