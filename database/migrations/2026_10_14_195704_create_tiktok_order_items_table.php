<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();

            $table->string('order_id')->index();
            $table->string('order_status')->nullable();
            $table->string('order_substatus')->nullable();
            $table->string('sku_id')->nullable()->index();
            $table->string('seller_sku')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('variation')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('sku_unit_original_price')->default(0);
            $table->integer('sku_subtotal_before_discount')->default(0);
            $table->integer('sku_platform_discount')->default(0);
            $table->integer('sku_seller_discount')->default(0);
            $table->integer('sku_subtotal_after_discount')->default(0);
            $table->integer('order_amount')->default(0);
            $table->dateTime('created_time')->nullable();
            $table->dateTime('paid_time')->nullable();
            $table->dateTime('shipped_time')->nullable();
            $table->dateTime('delivered_time')->nullable();
            $table->dateTime('cancelled_time')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('order_channel')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_order_items');
    }
};