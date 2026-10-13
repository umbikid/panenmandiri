<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $province = Province::firstOrCreate(['name' => 'Jawa Barat']);
        $regency1 = Regency::firstOrCreate(['province_id' => $province->id, 'name' => 'Kabupaten Bogor', 'type' => 'Kabupaten']);
        $regency2 = Regency::firstOrCreate(['province_id' => $province->id, 'name' => 'Kota Depok', 'type' => 'Kota']);
        
        District::firstOrCreate(['regency_id' => $regency1->id, 'name' => 'Cibinong']);
        District::firstOrCreate(['regency_id' => $regency1->id, 'name' => 'Gunung Putri']);
        
        District::firstOrCreate(['regency_id' => $regency2->id, 'name' => 'Margonda']);
        District::firstOrCreate(['regency_id' => $regency2->id, 'name' => 'Beji']);
    }
}
