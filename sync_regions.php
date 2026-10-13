<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

DB::statement('PRAGMA foreign_keys=OFF;');
DB::table('product_coverages')->truncate();
DB::table('regencies')->truncate();
DB::table('provinces')->truncate();

$provincesResp = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
$provinces = $provincesResp->json();

foreach ($provinces as $p) {
    DB::table('provinces')->insert([
        'id' => $p['id'],
        'name' => $p['name'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $regenciesResp = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$p['id']}.json");
    $regencies = $regenciesResp->json();
    
    $inserts = [];
    foreach ($regencies as $r) {
        $inserts[] = [
            'id' => $r['id'],
            'province_id' => $r['province_id'],
            'name' => $r['name'],
            'type' => str_starts_with($r['name'], 'KOTA ') ? 'Kota' : 'Kabupaten',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    DB::table('regencies')->insert($inserts);
    echo "Synced province {$p['name']} with " . count($regencies) . " regencies.\n";
}

DB::statement('PRAGMA foreign_keys=ON;');
echo "All regions synced successfully.\n";
