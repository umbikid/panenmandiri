<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
