<?php
$blueprint = [
    'create_users_table' => <<<'EOT'
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('google_id')->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->foreignId('province_id')->nullable();
            $table->foreignId('regency_id')->nullable();
            $table->foreignId('district_id')->nullable();
            $table->timestamp('profile_completed_at')->nullable();
            $table->enum('status', ['active', 'pending', 'suspended'])->default('pending');
            $table->rememberToken();
            $table->timestamps();
EOT,
    'create_provinces_table' => <<<'EOT'
            $table->id();
            $table->string('name');
            $table->timestamps();
EOT,
    'create_regencies_table' => <<<'EOT'
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable(); // e.g., 'Kota', 'Kabupaten'
            $table->timestamps();
EOT,
    'create_districts_table' => <<<'EOT'
            $table->id();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
EOT,
    'create_partner_profiles_table' => <<<'EOT'
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('farm_name');
            $table->text('address_detail')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
EOT,
    'create_categories_table' => <<<'EOT'
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['rice', 'livestock']);
            $table->json('allowed_units')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
EOT,
    'create_subcategories_table' => <<<'EOT'
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('label')->nullable();
            $table->integer('min_weight_kg')->nullable();
            $table->integer('max_weight_kg')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
EOT,
    'create_products_table' => <<<'EOT'
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('subcategory_id')->constrained('subcategories');
            $table->string('name', 200);
            $table->decimal('partner_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->foreignId('price_set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('price_set_at')->nullable();
            $table->decimal('stock', 12, 2)->default(0);
            $table->enum('unit', ['kg', 'kwintal', 'ton', 'ekor']);
            $table->enum('status', ['active', 'hidden', 'sold_out'])->default('active');
            $table->timestamps();
            $table->softDeletes();
EOT,
    'create_stock_logs_table' => <<<'EOT'
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable(); // Foreign key to orders will be added separately if needed to avoid circular issues
            $table->decimal('old_stock', 12, 2);
            $table->decimal('new_stock', 12, 2);
            $table->string('reason');
            $table->timestamps();
EOT,
    'create_price_logs_table' => <<<'EOT'
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('old_partner_price', 15, 2);
            $table->decimal('new_partner_price', 15, 2);
            $table->decimal('old_selling_price', 15, 2);
            $table->decimal('new_selling_price', 15, 2);
            $table->timestamps();
EOT,
    'create_product_photos_table' => <<<'EOT'
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('path');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
EOT,
    'create_product_coverages_table' => <<<'EOT'
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->timestamps();
EOT,
    'create_orders_table' => <<<'EOT'
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('buyer_id')->constrained('users');
            $table->enum('status', ['pending', 'forwarded_to_cs', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('stock_deducted_at')->nullable();
            $table->decimal('total_estimate', 15, 2)->default(0);
            $table->foreignId('delivery_regency_id')->nullable()->constrained('regencies');
            $table->text('note')->nullable();
            $table->string('whatsapp_url', 500)->nullable();
            $table->timestamps();
EOT,
    'create_order_items_table' => <<<'EOT'
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty', 12, 2);
            $table->string('unit');
            $table->decimal('selling_price_snapshot', 15, 2);
            $table->decimal('partner_price_snapshot', 15, 2);
            $table->string('title_snapshot');
            $table->timestamps();
EOT,
    'create_inquiries_table' => <<<'EOT'
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
EOT,
    'create_settings_table' => <<<'EOT'
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
EOT,
];

$dir = __DIR__ . '/database/migrations';
$files = scandir($dir);
foreach ($files as $file) {
    if (str_ends_with($file, '.php')) {
        $path = $dir . '/' . $file;
        $content = file_get_contents($path);
        
        foreach ($blueprint as $key => $schema) {
            if (str_contains($file, $key)) {
                // Find Schema::create block
                if (preg_match('/Schema::create\([\'"].*?[\'"],\s*function\s*\(\w+\s+\$table\)\s*\{(.+?)\}\);/s', $content, $matches)) {
                    $newContent = str_replace($matches[1], "\n" . $schema . "\n        ", $content);
                    file_put_contents($path, $newContent);
                    echo "Updated $file\n";
                }
            }
        }
    }
}
