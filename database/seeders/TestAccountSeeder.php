<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'staging'])) {
            return;
        }

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
                'province_id'          => 1, // dummy
                'regency_id'           => 1, // dummy
                'district_id'          => 1, // dummy
            ]);
            $user->syncRoles($a['role']);
        }
    }
}
