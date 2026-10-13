<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Beras', 'type' => 'rice', 'allowed_units' => ['kg', 'kwintal', 'ton'],
                'subcategories' => [
                    ['name' => 'Pandan Wangi', 'label' => 'Pandan Wangi'],
                    ['name' => 'IR64', 'label' => 'IR64'],
                    ['name' => 'Rojolele', 'label' => 'Rojolele'],
                    ['name' => 'IR 42', 'label' => 'IR 42'],
                    ['name' => 'C4', 'label' => 'C4'],
                    ['name' => 'Lainnya', 'label' => 'Lainnya'],
                ]
            ],
            [
                'name' => 'Sapi', 'type' => 'livestock', 'allowed_units' => ['ekor'],
                'subcategories' => [
                    ['name' => 'Ringan', 'label' => 'Ringan (< 450 kg)', 'max_weight_kg' => 450],
                    ['name' => 'Sedang', 'label' => 'Sedang (450–700 kg)', 'min_weight_kg' => 450, 'max_weight_kg' => 700],
                    ['name' => 'Berat / Jumbo', 'label' => 'Berat / Jumbo (> 700 kg)', 'min_weight_kg' => 700],
                ]
            ],
            [
                'name' => 'Domba', 'type' => 'livestock', 'allowed_units' => ['ekor'],
                'subcategories' => [
                    ['name' => 'Standar', 'label' => 'Standar (18–22 kg)', 'min_weight_kg' => 18, 'max_weight_kg' => 22],
                    ['name' => 'Medium', 'label' => 'Medium (23–28 kg)', 'min_weight_kg' => 23, 'max_weight_kg' => 28],
                    ['name' => 'Premium', 'label' => 'Premium (29–35 kg)', 'min_weight_kg' => 29, 'max_weight_kg' => 35],
                    ['name' => 'Jumbo', 'label' => 'Jumbo (40–60 kg)', 'min_weight_kg' => 40, 'max_weight_kg' => 60],
                ]
            ],
            [
                'name' => 'Kambing', 'type' => 'livestock', 'allowed_units' => ['ekor'],
                'subcategories' => [
                    ['name' => 'Standar', 'label' => 'Standar (18–22 kg)', 'min_weight_kg' => 18, 'max_weight_kg' => 22],
                    ['name' => 'Medium', 'label' => 'Medium (23–28 kg)', 'min_weight_kg' => 23, 'max_weight_kg' => 28],
                    ['name' => 'Premium', 'label' => 'Premium (29–35 kg)', 'min_weight_kg' => 29, 'max_weight_kg' => 35],
                    ['name' => 'Jumbo', 'label' => 'Jumbo (40–60 kg)', 'min_weight_kg' => 40, 'max_weight_kg' => 60],
                ]
            ]
        ];

        foreach ($categories as $idx => $cat) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                    'allowed_units' => json_encode($cat['allowed_units']),
                    'sort_order' => $idx,
                    'is_active' => true,
                ]
            );

            foreach ($cat['subcategories'] as $subIdx => $sub) {
                Subcategory::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $sub['name'],
                    ],
                    [
                        'label' => $sub['label'],
                        'min_weight_kg' => $sub['min_weight_kg'] ?? null,
                        'max_weight_kg' => $sub['max_weight_kg'] ?? null,
                        'sort_order' => $subIdx,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
