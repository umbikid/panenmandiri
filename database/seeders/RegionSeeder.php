<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $provFile = database_path('data/provinces.json');
        $regFile = database_path('data/regencies.json');
        $distFile = database_path('data/districts.json');

        if (file_exists($provFile) && file_exists($regFile) && file_exists($distFile)) {
            $now = now();

            // 1. Provinsi
            $provinces = json_decode(file_get_contents($provFile), true);
            foreach ($provinces as &$p) {
                $p['created_at'] = $now;
                $p['updated_at'] = $now;
            }
            DB::table('provinces')->upsert($provinces, ['id'], ['name', 'updated_at']);

            // 2. Kabupaten / Kota
            $regencies = json_decode(file_get_contents($regFile), true);
            foreach ($regencies as &$r) {
                $r['created_at'] = $now;
                $r['updated_at'] = $now;
            }
            foreach (array_chunk($regencies, 200) as $chunk) {
                DB::table('regencies')->upsert($chunk, ['id'], ['province_id', 'name', 'type', 'updated_at']);
            }

            // 3. Kecamatan
            $districts = json_decode(file_get_contents($distFile), true);
            foreach ($districts as &$d) {
                $d['created_at'] = $now;
                $d['updated_at'] = $now;
            }
            foreach (array_chunk($districts, 500) as $chunk) {
                DB::table('districts')->upsert($chunk, ['id'], ['regency_id', 'name', 'updated_at']);
            }

            return;
        }

        // Fallback jika file JSON tidak tersedia
        $province = Province::firstOrCreate(['name' => 'Jawa Barat']);
        $regency1 = Regency::firstOrCreate(['province_id' => $province->id, 'name' => 'Kabupaten Bogor', 'type' => 'Kabupaten']);
        $regency2 = Regency::firstOrCreate(['province_id' => $province->id, 'name' => 'Kota Depok', 'type' => 'Kota']);
        
        District::firstOrCreate(['regency_id' => $regency1->id, 'name' => 'Cibinong']);
        District::firstOrCreate(['regency_id' => $regency1->id, 'name' => 'Gunung Putri']);
        District::firstOrCreate(['regency_id' => $regency2->id, 'name' => 'Margonda']);
        District::firstOrCreate(['regency_id' => $regency2->id, 'name' => 'Beji']);
    }
}
