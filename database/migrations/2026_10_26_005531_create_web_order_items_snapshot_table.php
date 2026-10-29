<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_web_order_items_snapshot_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_order_items_snapshot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('web_order_id')->index(); // = web_orders_snapshot.web_order_id
            $table->unsignedBigInteger('web_order_item_id')->unique(); // id dari DB web
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable()->index();
            $table->string('category_name')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('weight', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['web_order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_order_items_snapshot');
    }
};