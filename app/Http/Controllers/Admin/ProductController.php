<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ExportHelper;
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

    public function export(Request $request)
    {
        $query = Product::with(['partner', 'category', 'subcategory', 'coverages.regency']);

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

        $products = $query->latest()->get();
        $format = $request->query('format', 'excel');

        $statusLabels = [
            'active' => 'Aktif (Tayang)',
            'hidden' => 'Disembunyikan',
            'sold_out' => 'Habis Terjual',
        ];

        $columns = [
            ['key' => 'no', 'label' => 'No', 'class' => 'text-center'],
            ['key' => 'name', 'label' => 'Nama Produk', 'class' => 'font-bold'],
            ['key' => 'category', 'label' => 'Kategori & Subkategori'],
            ['key' => 'partner_info', 'label' => 'Mitra Produsen'],
            ['key' => 'partner_price', 'label' => 'Harga Mitra', 'class' => 'text-right'],
            ['key' => 'selling_price', 'label' => 'Harga Jual Admin', 'class' => 'text-right font-bold'],
            ['key' => 'margin', 'label' => 'Margin', 'class' => 'text-right'],
            ['key' => 'stock_unit', 'label' => 'Stok Tersedia', 'class' => 'text-right'],
            ['key' => 'coverages', 'label' => 'Jangkauan Wilayah'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'text-center'],
        ];

        $rows = [];
        $totalStock = 0;

        foreach ($products as $idx => $p) {
            $partnerPrice = (float) $p->partner_price;
            $sellingPrice = (float) $p->selling_price;
            $margin = $sellingPrice - $partnerPrice;
            $totalStock += (float) $p->stock;

            $covs = $p->coverages->map(fn($c) => $c->regency->name ?? '')->filter()->implode(', ');
            $statusLabel = $statusLabels[$p->status] ?? $p->status;

            $statusBadgeClass = match ($p->status) {
                'active' => 'badge-success',
                'hidden' => 'badge-gray',
                'sold_out' => 'badge-warning',
                default => 'badge-gray',
            };

            $rows[] = [
                'no' => $idx + 1,
                'name' => $p->name,
                'category' => ($p->category->name ?? '-') . ' &bull; ' . ($p->subcategory->name ?? '-'),
                'raw_category' => ($p->category->name ?? '-') . ' - ' . ($p->subcategory->name ?? '-'),
                'partner_info' => ($p->partner->name ?? '-') . ($p->partner && $p->partner->phone ? ' (' . $p->partner->phone . ')' : ''),
                'partner_name_only' => $p->partner->name ?? '-',
                'partner_phone_only' => $p->partner->phone ?? '-',
                'partner_price' => 'Rp ' . number_format($partnerPrice, 0, ',', '.'),
                'raw_partner_price' => $partnerPrice,
                'selling_price' => 'Rp ' . number_format($sellingPrice, 0, ',', '.'),
                'raw_selling_price' => $sellingPrice,
                'margin' => ($margin >= 0 ? '+' : '') . 'Rp ' . number_format($margin, 0, ',', '.'),
                'raw_margin' => $margin,
                'stock_unit' => $p->stock . ' ' . $p->unit,
                'raw_stock' => $p->stock,
                'unit' => $p->unit,
                'coverages' => $covs ?: 'Semua Wilayah',
                'status' => "<span class=\"badge {$statusBadgeClass}\">{$statusLabel}</span>",
                'raw_status' => $statusLabel,
            ];
        }

        $summaryRow = [
            'no' => '',
            'name' => 'TOTAL KESELURUHAN',
            'category' => count($products) . ' Produk Terdaftar',
            'partner_info' => '',
            'partner_price' => '',
            'selling_price' => '',
            'margin' => '',
            'stock_unit' => number_format($totalStock, 0, ',', '.') . ' Unit',
            'coverages' => '',
            'status' => '',
        ];

        // Active filter description
        $filterParts = [];
        if ($request->filled('q')) $filterParts[] = 'Pencarian: "' . e($request->q) . '"';
        if ($request->filled('category_id')) {
            $cat = Category::find($request->category_id);
            if ($cat) $filterParts[] = 'Kategori: ' . $cat->name;
        }
        if ($request->filled('status')) $filterParts[] = 'Status: ' . ($statusLabels[$request->status] ?? $request->status);
        $filterText = !empty($filterParts) ? implode(' &bull; ', $filterParts) : 'Semua Produk';

        if ($format === 'pdf') {
            return ExportHelper::renderPrintView(
                'Laporan Produk & Penetapan Harga Jual',
                $columns,
                $rows,
                $filterText,
                $summaryRow,
                'landscape'
            );
        }

        // Excel / CSV Export
        $filename = 'Laporan_Produk_Harga_' . date('Y-m-d_His') . '.csv';
        $csvHeaders = [
            'No', 'Nama Produk', 'Kategori & Subkategori', 'Mitra Produsen', 'No HP Mitra',
            'Harga Mitra (Rp)', 'Harga Jual Admin (Rp)', 'Margin (Rp)', 'Stok', 'Satuan',
            'Jangkauan Wilayah', 'Status Produk'
        ];

        $csvRows = [];
        foreach ($rows as $row) {
            $csvRows[] = [
                $row['no'],
                $row['name'],
                $row['raw_category'],
                $row['partner_name_only'],
                $row['partner_phone_only'],
                $row['raw_partner_price'],
                $row['raw_selling_price'],
                $row['raw_margin'],
                $row['raw_stock'],
                $row['unit'],
                $row['coverages'],
                $row['raw_status'],
            ];
        }

        $csvRows[] = [
            '', 'TOTAL KESELURUHAN', count($products) . ' Produk', '', '', '', '', '', $totalStock, 'Unit', '', ''
        ];

        return ExportHelper::downloadCsv($filename, $csvHeaders, $csvRows);
    }
}
