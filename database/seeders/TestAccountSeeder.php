<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestAccountSeeder extends Seeder
{
    public function run(): void
    {
        $province = \App\Models\Province::first();
        $regency = \App\Models\Regency::first();
        $district = \App\Models\District::first();

        $accounts = [
            ['name' => 'Admin',          'email' => 'umbikidtv@gmail.com', 'password' => 'admin123',   'role' => 'admin',   'phone' => '6281200000001'],
            ['name' => 'Mitra contoh',   'email' => 'mitra@gmail.com',     'password' => 'mitra123',   'role' => 'mitra',   'phone' => '6281200000002'],
            ['name' => 'Pembeli contoh', 'email' => 'pembeli@gmail.com',   'password' => 'pembeli123', 'role' => 'pembeli', 'phone' => '6281200000003'],
        ];

        foreach ($accounts as $a) {
            $user = User::updateOrCreate(['email' => $a['email']], [
                'name'                 => $a['name'],
                'password'             => Hash::make($a['password']),
                'phone'                => $a['phone'],
                'status'               => 'active',
                'email_verified_at'    => now(),
                'profile_completed_at' => now(),
                'province_id'          => $province?->id,
                'regency_id'           => $regency?->id,
                'district_id'          => $district?->id,
            ]);
            $user->syncRoles($a['role']);
        }
    }
}
