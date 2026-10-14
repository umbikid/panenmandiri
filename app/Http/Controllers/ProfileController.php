<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $provinces = \App\Models\Province::orderBy('name')->get();

        $initialRegencies = $user->province_id
            ? \App\Models\Regency::where('province_id', $user->province_id)->orderBy('name')->get()
            : [];

        $initialDistricts = $user->regency_id
            ? \App\Models\District::where('regency_id', $user->regency_id)->orderBy('name')->get()
            : [];

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'provinces' => $provinces,
            'initialRegencies' => $initialRegencies,
            'initialDistricts' => $initialDistricts,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->filled('district_id')) {
            $district = \App\Models\District::with('regency')->find($request->district_id);
            if ($district) {
                $user->province_id = $district->regency->province_id;
                $user->regency_id = $district->regency_id;
                $user->district_id = $district->id;
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
