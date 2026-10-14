<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $csNumber = Setting::where('key', 'cs_whatsapp_number')->value('value') ?? '6281234567890';

        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'cs_whatsapp_number' => $csNumber,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cs_whatsapp_number' => 'required|string|max:20',
        ]);

        Setting::updateOrCreate(
            ['key' => 'cs_whatsapp_number'],
            ['value' => $request->cs_whatsapp_number]
        );

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
