<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_mappings');
    }

    public function down(): void
    {
        // Kalau mau rollback, buat ulang struktur lama.
        // Tapi karena mapping tidak dipakai lagi, sebenarnya bisa kosong.
        // Untuk aman, kita buat ulang.
        Schema::create('product_mappings', function ($table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('marketplace_sku');
            $table->string('marketplace_product_name')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'marketplace_sku']);
        });
    }
};