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
        $appName = Setting::where('key', 'app_name')->value('value') ?? 'Panen Mandiri';

        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'cs_whatsapp_number' => $csNumber,
                'app_name' => $appName,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cs_whatsapp_number' => 'required|string|max:20',
            'app_name' => 'required|string|max:100',
        ]);

        Setting::updateOrCreate(
            ['key' => 'cs_whatsapp_number'],
            ['value' => $request->cs_whatsapp_number]
        );

        Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => $request->app_name]
        );

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
