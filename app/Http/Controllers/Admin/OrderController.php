<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLog;
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
}
