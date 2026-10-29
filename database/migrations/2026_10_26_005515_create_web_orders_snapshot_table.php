<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_web_orders_snapshot_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_orders_snapshot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('web_order_id')->unique(); // id dari DB web
            $table->string('order_number')->index();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('status')->default('completed')->index();
            $table->string('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->string('marketplace')->nullable(); // shopee, tiktok, web, offline
            $table->timestamp('web_created_at')->nullable()->index();
            $table->timestamp('web_updated_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['web_created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_orders_snapshot');
    }
};