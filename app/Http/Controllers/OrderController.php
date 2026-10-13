<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Regency;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function create(Product $product)
    {
        if ($product->status !== 'active' || $product->stock <= 0) {
            abort(404, 'Produk tidak tersedia.');
        }

        $product->load(['coverages.regency', 'primaryPhoto']);

        return Inertia::render('Catalog/Checkout', [
            'product' => $product
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'qty' => 'required|numeric|min:1|max:'.$product->stock,
            'delivery_regency_id' => 'required|exists:regencies,id',
            'note' => 'nullable|string|max:500'
        ]);

        $user = $request->user();

        // Cek lokasi
        $isCoverageValid = $product->coverages()->where('regency_id', $request->delivery_regency_id)->exists();
        if (!$isCoverageValid) {
            return back()->withErrors(['delivery_regency_id' => 'Wilayah pengiriman tidak didukung oleh Mitra untuk produk ini.']);
        }

        $code = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        
        $order = Order::create([
            'code' => $code,
            'buyer_id' => $user->id,
            'status' => 'pending',
            'delivery_regency_id' => $request->delivery_regency_id,
            'note' => $request->note,
            'total_estimate' => $product->selling_price * $request->qty,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => $request->qty,
            'unit' => $product->unit,
            'selling_price_snapshot' => $product->selling_price,
            'partner_price_snapshot' => $product->partner_price,
            'title_snapshot' => $product->name,
        ]);

        // Reduce product stock
        $product->decrement('stock', $request->qty);

        $csNumber = Setting::where('key', 'cs_whatsapp_number')->value('value') ?? '6281234567890';
        $location = Regency::find($request->delivery_regency_id)->name ?? '';
        
        $message = "Halo CS, saya ingin memesan:\n";
        $message .= "Kode Pesanan: {$order->code}\n";
        $message .= "- {$product->name} x {$request->qty} {$product->unit}\n";
        $message .= "Lokasi tujuan: {$location}\n";
        if ($request->note) {
            $message .= "Catatan: {$request->note}\n";
        }
        $message .= "Nama: {$user->name} | HP: {$user->phone}";

        $waUrl = "https://wa.me/{$csNumber}?text=" . rawurlencode($message);

        $order->update(['whatsapp_url' => $waUrl]);

        return Inertia::location($waUrl);
    }

    public function index(Request $request)
    {
        $orders = Order::where('buyer_id', $request->user()->id)
            ->with(['items.product.primaryPhoto'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Buyer/Orders/Index', [
            'orders' => $orders
        ]);
    }
}
