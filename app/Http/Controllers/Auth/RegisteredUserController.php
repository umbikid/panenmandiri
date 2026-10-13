<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $provinces = \App\Models\Province::orderBy('name')->get();
        return Inertia::render('Auth/Register', [
            'provinces' => $provinces
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'district_id' => 'required|integer|exists:districts,id',
            'role' => 'required|in:pembeli,mitra',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $district = \App\Models\District::with('regency')->findOrFail($request->district_id);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'province_id' => $district->regency->province_id,
            'regency_id' => $district->regency_id,
            'district_id' => $district->id,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles($request->role);

        event(new Registered($user));

        Auth::login($user);

        if ($user->hasRole('mitra')) {
            return redirect(route('mitra.dashboard', absolute: false));
        }

        return redirect(route('catalog.index', absolute: false));
    }
}
