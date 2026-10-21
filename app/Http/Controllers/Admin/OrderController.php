<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLog;
use App\Services\ExportHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
            'buyer',
            'deliveryRegency.province',
            'items.product.primaryPhoto',
            'items.product.partner'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($qb) use ($search) {
                      $qb->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $request->status,
                'q' => $request->q,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,forwarded_to_cs,confirmed,completed,cancelled',
            'note' => 'nullable|string|max:500',
        ]);

        $newStatus = $request->status;
        $admin = $request->user();

        DB::transaction(function () use ($order, $newStatus, $admin, $request) {
            // Jika konfirmasi order dan stok belum pernah dikurangi
            if ($newStatus === 'confirmed' && !$order->stock_deducted_at) {
                foreach ($order->items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $product->decrement('stock', $item->qty);
                        if ($product->stock <= 0) {
                            $product->update(['status' => 'sold_out']);
                        }
                    }
                }
                $order->stock_deducted_at = now();
                $order->confirmed_by = $admin->id;
                $order->confirmed_at = now();
            }

            // Jika dibatalkan dan stok sebelumnya sudah dikurangi
            if ($newStatus === 'cancelled' && $order->stock_deducted_at) {
                foreach ($order->items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->qty);
                        if ($product->status === 'sold_out' && $product->stock > 0) {
                            $product->update(['status' => 'active']);
                        }
                    }
                }
                $order->stock_deducted_at = null;
            }

            $order->status = $newStatus;
            if ($request->has('note')) {
                $order->note = $request->note;
            }
            $order->save();
        });

        return back()->with('success', "Status pesanan {$order->code} berhasil diubah ke {$newStatus}.");
    }

    public function export(Request $request)
    {
        $query = Order::with([
            'buyer',
            'deliveryRegency.province',
            'items.product.category',
            'items.product.partner'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($qb) use ($search) {
                      $qb->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()->get();
        $format = $request->query('format', 'excel');

        $statusLabels = [
            'pending' => 'Menunggu',
            'forwarded_to_cs' => 'Diteruskan ke CS',
            'confirmed' => 'Dikonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $columns = [
            ['key' => 'no', 'label' => 'No', 'class' => 'text-center'],
            ['key' => 'code', 'label' => 'Kode Pesanan', 'class' => 'font-bold'],
            ['key' => 'date', 'label' => 'Tanggal Pesanan'],
            ['key' => 'buyer_name', 'label' => 'Nama Pembeli'],
            ['key' => 'buyer_phone', 'label' => 'No HP Pembeli'],
            ['key' => 'items', 'label' => 'Rincian Produk'],
            ['key' => 'partner_info', 'label' => 'Mitra Produsen'],
            ['key' => 'total_amount', 'label' => 'Total Biaya', 'class' => 'text-right font-bold'],
            ['key' => 'status', 'label' => 'Status Pesanan', 'class' => 'text-center'],
        ];

        $rows = [];
        $grandTotal = 0;

        foreach ($orders as $idx => $order) {
            $grandTotal += (float) $order->total_price;

            $itemsSummary = [];
            $partnerSummary = [];

            foreach ($order->items as $item) {
                $pName = $item->product->name ?? 'Produk';
                $unit = $item->product->unit ?? 'satuan';
                $itemsSummary[] = "{$pName} ({$item->qty} {$unit})";
                if ($item->product && $item->product->partner) {
                    $partnerSummary[] = "{$item->product->partner->name} ({$item->product->partner->phone})";
                }
            }

            $partnerStr = !empty($partnerSummary) ? implode(', ', array_unique($partnerSummary)) : '-';
            $itemsStr = !empty($itemsSummary) ? implode('; ', $itemsSummary) : '-';
            $statusLabel = $statusLabels[$order->status] ?? $order->status;

            $statusBadgeClass = match ($order->status) {
                'pending' => 'badge-warning',
                'forwarded_to_cs' => 'badge-info',
                'confirmed', 'completed' => 'badge-success',
                default => 'badge-gray',
            };

            $rows[] = [
                'no' => $idx + 1,
                'code' => $order->code,
                'date' => $order->created_at ? $order->created_at->translatedFormat('d M Y, H:i') : '-',
                'buyer_name' => $order->buyer->name ?? 'Tamu',
                'buyer_phone' => $order->buyer->phone ?? '-',
                'items' => $itemsStr,
                'partner_info' => $partnerStr,
                'total_amount' => 'Rp ' . number_format($order->total_price, 0, ',', '.'),
                'raw_total' => (float) $order->total_price,
                'status' => "<span class=\"badge {$statusBadgeClass}\">{$statusLabel}</span>",
                'raw_status' => $statusLabel,
            ];
        }

        $summaryRow = [
            'no' => '',
            'code' => 'TOTAL KESELURUHAN',
            'date' => '',
            'buyer_name' => '',
            'buyer_phone' => '',
            'items' => count($orders) . ' Transaksi',
            'partner_info' => '',
            'total_amount' => 'Rp ' . number_format($grandTotal, 0, ',', '.'),
            'status' => '',
        ];

        // Active filter description
        $filterParts = [];
        if ($request->filled('q')) $filterParts[] = 'Pencarian: "' . e($request->q) . '"';
        if ($request->filled('status')) $filterParts[] = 'Status: ' . ($statusLabels[$request->status] ?? $request->status);
        if ($request->filled('start_date')) $filterParts[] = 'Dari: ' . $request->start_date;
        if ($request->filled('end_date')) $filterParts[] = 'Sampai: ' . $request->end_date;
        $filterText = !empty($filterParts) ? implode(' &bull; ', $filterParts) : 'Semua Pesanan';

        if ($format === 'pdf') {
            return ExportHelper::renderPrintView(
                'Laporan Transaksi & Pesanan Masuk',
                $columns,
                $rows,
                $filterText,
                $summaryRow,
                'landscape'
            );
        }

        // Excel (.xls) Export
        $filename = 'Laporan_Transaksi_Pesanan_' . date('Y-m-d_His') . '.xls';
        $xlsHeaders = [
            'No', 'Kode Pesanan', 'Tanggal Pesanan', 'Nama Pembeli', 'No HP Pembeli',
            'Rincian Produk Dipesan', 'Mitra Produsen', 'Total Biaya (Rp)', 'Status Pesanan'
        ];

        $xlsRows = [];
        foreach ($rows as $row) {
            $xlsRows[] = [
                $row['no'],
                $row['code'],
                $row['date'],
                $row['buyer_name'],
                $row['buyer_phone'],
                $row['items'],
                $row['partner_info'],
                $row['raw_total'],
                $row['raw_status'],
            ];
        }

        $summaryXls = [
            '', 'TOTAL KESELURUHAN', '', '', '', count($orders) . ' Transaksi', '', $grandTotal, ''
        ];

        return ExportHelper::downloadXls($filename, 'Laporan Transaksi & Pesanan Masuk', $xlsHeaders, $xlsRows, $summaryXls);
    }
}
