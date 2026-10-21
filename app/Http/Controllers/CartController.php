<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Regency;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $cartItems = CartItem::where('user_id', $user->id)
            ->with([
                'product.primaryPhoto',
                'product.photos',
                'product.coverages.regency',
                'product.category',
                'product.partner',
            ])
            ->latest()
            ->get();

        // Calculate delivery regencies that are covered by ALL products in the cart
        $supportedRegencies = collect();
        if ($cartItems->isNotEmpty()) {
            // Start with coverages of the first product
            $firstProductRegencies = $cartItems->first()->product->coverages->pluck('regency')->filter();

            // Intersect with remaining products
            $supportedRegencies = $firstProductRegencies;
            foreach ($cartItems->skip(1) as $item) {
                $itemRegencyIds = $item->product->coverages->pluck('regency_id')->toArray();
                $supportedRegencies = $supportedRegencies->filter(function ($reg) use ($itemRegencyIds) {
                    return in_array($reg->id, $itemRegencyIds);
                });
            }
        }

        return Inertia::render('Buyer/Cart/Index', [
            'cart_items' => $cartItems,
            'supported_regencies' => $supportedRegencies->values(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk menambahkan produk ke keranjang.');
        }

        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['nullable', 'numeric', 'min:1'],
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->status !== 'active' || $product->stock <= 0) {
            return back()->with('error', 'Produk tidak tersedia atau stok habis.');
        }

        $addQty = (float) ($request->qty ?? 1);

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQty = min($cartItem->qty + $addQty, (float) $product->stock);
            $cartItem->update(['qty' => $newQty]);
        } else {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'qty' => min($addQty, (float) $product->stock),
            ]);
        }

        return back()->with('success', "'{$product->name}' berhasil ditambahkan ke keranjang.");
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403);
        }

        $product = $cartItem->product;
        $maxStock = (float) ($product ? $product->stock : 9999);

        $request->validate([
            'qty' => ['required', 'numeric', 'min:1', 'max:' . $maxStock],
        ]);

        $cartItem->update([
            'qty' => $request->qty,
        ]);

        return back();
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403);
        }

        $cartItem->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($user->phone)) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp wajib diisi pada profil Anda sebelum checkout.']);
        }

        $request->validate([
            'delivery_regency_id' => ['required', 'exists:regencies,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $cartItems = CartItem::where('user_id', $user->id)
            ->with(['product.coverages'])
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Keranjang belanja Anda kosong.');
        }

        // Validate stock and coverage for all items
        $regencyId = $request->delivery_regency_id;
        $totalEstimate = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (!$product || $product->status !== 'active') {
                return back()->with('error', "Produk {$item->product_id} sudah tidak aktif.");
            }

            if ($product->stock < $item->qty) {
                return back()->with('error', "Stok produk '{$product->name}' tersisa {$product->stock} {$product->unit}, tidak mencukupi untuk jumlah pesanan {$item->qty}.");
            }

            $covers = $product->coverages->contains('regency_id', $regencyId);
            if (!$covers) {
                $regName = Regency::find($regencyId)?->name ?? 'wilayah ini';
                return back()->with('error', "Produk '{$product->name}' tidak mendukung pengiriman ke {$regName}.");
            }

            $totalEstimate += ((float) $product->selling_price * (float) $item->qty);
        }

        $csNumber = Setting::where('key', 'cs_whatsapp_number')->value('value') ?? '6281234567890';
        $location = Regency::find($regencyId)?->name ?? '';

        $order = DB::transaction(function () use ($user, $cartItems, $regencyId, $request, $totalEstimate) {
            $code = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            $order = Order::create([
                'code' => $code,
                'buyer_id' => $user->id,
                'status' => 'pending',
                'delivery_regency_id' => $regencyId,
                'note' => $request->note,
                'total_estimate' => $totalEstimate,
            ]);

            foreach ($cartItems as $item) {
                $product = $item->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $item->qty,
                    'unit' => $product->unit,
                    'selling_price_snapshot' => $product->selling_price,
                    'partner_price_snapshot' => $product->partner_price,
                    'title_snapshot' => $product->name,
                ]);

                // Reduce stock
                $product->decrement('stock', $item->qty);
            }

            // Empty cart
            CartItem::where('user_id', $user->id)->delete();

            return $order;
        });

        // Compose WhatsApp message for multiple products
        $message = "Halo CS Panen Mandiri, saya ingin memesan:\n";
        $message .= "Kode Pesanan: {$order->code}\n";
        $message .= "Rincian Produk:\n";

        $counter = 1;
        foreach ($cartItems as $item) {
            $subtotal = (float) $item->product->selling_price * (float) $item->qty;
            $formattedSubtotal = number_format($subtotal, 0, ',', '.');
            $message .= "{$counter}. {$item->product->name} x {$item->qty} {$item->product->unit} (Rp {$formattedSubtotal})\n";
            $counter++;
        }

        $formattedTotal = number_format($totalEstimate, 0, ',', '.');
        $message .= "Total Estimasi: Rp {$formattedTotal}\n";
        $message .= "Lokasi Tujuan: {$location}\n";
        if ($request->note) {
            $message .= "Catatan: {$request->note}\n";
        }
        $message .= "Nama: {$user->name} | HP: {$user->phone}";

        $waUrl = "https://wa.me/{$csNumber}?text=" . rawurlencode($message);

        $order->update(['whatsapp_url' => $waUrl]);

        return Inertia::location($waUrl);
    }
}
