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
        Schema::create('orders', function (Blueprint $table) {
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
