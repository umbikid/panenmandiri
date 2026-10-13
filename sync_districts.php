<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fetching regencies...\n";
$regencies = DB::table('regencies')->get();
$totalRegencies = $regencies->count();
echo "Total regencies: $totalRegencies\n";

$districtsData = [];
$count = 0;

foreach ($regencies as $reg) {
    try {
        $url = "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$reg->id}.json";
        $response = Http::get($url);
        
        if ($response->successful()) {
            $districts = $response->json();
            foreach ($districts as $dist) {
                $districtsData[] = [
                    'id' => $dist['id'],
                    'regency_id' => $dist['regency_id'],
                    'name' => $dist['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        $count++;
        if ($count % 50 === 0) {
            echo "Processed $count / $totalRegencies regencies...\n";
        }
    } catch (\Exception $e) {
        // ignore
    }
}

echo "Total districts fetched: " . count($districtsData) . "\n";

DB::table('districts')->truncate(); // Clear existing
// Insert in chunks
$chunks = array_chunk($districtsData, 500);
foreach ($chunks as $chunk) {
    DB::table('districts')->insert($chunk);
}

echo "All districts synced successfully!\n";
