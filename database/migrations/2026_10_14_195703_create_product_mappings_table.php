<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('marketplace_sku');
            $table->string('marketplace_product_name')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'marketplace_sku']);
            $table->index('marketplace_sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_mappings');
    }
};