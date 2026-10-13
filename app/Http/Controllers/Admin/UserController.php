<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'province', 'regency', 'district', 'partnerProfile']);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::pluck('name');
        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'provinces' => $provinces,
            'filters' => [
                'role' => $request->role,
                'q' => $request->q,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,mitra,pembeli',
            'password' => ['required', 'string', 'min:8'],
            'address' => 'nullable|string|max:500',
            'district_id' => 'nullable|exists:districts,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'email_verified_at' => now(),
            'profile_completed_at' => now(),
        ];

        if ($request->filled('district_id')) {
            $district = \App\Models\District::with('regency')->find($request->district_id);
            if ($district) {
                $data['province_id'] = $district->regency->province_id;
                $data['regency_id'] = $district->regency_id;
                $data['district_id'] = $district->id;
            }
        }

        $user = User::create($data);
        $user->syncRoles($request->role);

        return back()->with('success', "Pengguna {$user->name} berhasil ditambahkan sebagai {$request->role}.");
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,suspended',
        ]);

        if ($user->hasRole('admin') && $user->id === $request->user()->id) {
            return back()->with('error', 'Tidak dapat menangguhkan akun sendiri.');
        }

        $user->update([
            'status' => $request->status,
        ]);

        return back()->with('success', "Status pengguna {$user->name} berhasil diubah ke {$request->status}.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->hasRole('admin')) {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus admin terakhir pada sistem.');
            }
        }

        $userName = $user->name;

        DB::transaction(function () use ($user) {
            // Nullify confirmed_by di pesanan
            \App\Models\Order::where('confirmed_by', $user->id)->update(['confirmed_by' => null]);

            // Jika user adalah pembeli dan memiliki pesanan, bersihkan item pesanan lalu pesanannya
            $buyerOrders = \App\Models\Order::where('buyer_id', $user->id)->get();
            foreach ($buyerOrders as $order) {
                $order->items()->delete();
                $order->delete();
            }

            // Jika user adalah mitra yang memiliki produk, bersihkan order_items produknya dahulu
            $productIds = \App\Models\Product::where('partner_id', $user->id)->pluck('id');
            if ($productIds->isNotEmpty()) {
                \App\Models\OrderItem::whereIn('product_id', $productIds)->delete();
                \App\Models\Product::whereIn('id', $productIds)->delete();
            }

            // Inquiry / Permintaan
            \App\Models\Inquiry::where('buyer_id', $user->id)->delete();

            // Profil mitra jika ada
            if ($user->partnerProfile) {
                $user->partnerProfile->delete();
            }

            // Hapus asosiasi roles
            $user->roles()->detach();

            // Hapus pengguna secara permanen
            $user->delete();
        });

        return back()->with('success', "Pengguna \"{$userName}\" berhasil dihapus secara permanen.");
    }
}
