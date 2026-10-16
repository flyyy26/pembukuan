<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->enum('marketplace', ['shopee', 'tiktok', 'tokopedia', 'lazada']);
            $table->string('order_id')->index();
            $table->date('tanggal');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('marketplace_sku')->nullable()->index();
            $table->integer('qty')->default(0);

            // Nilai penjualan
            $table->integer('harga_jual')->default(0);
            $table->integer('diskon_platform')->default(0);
            $table->integer('diskon_seller')->default(0);
            $table->integer('subtotal')->default(0);
            $table->integer('net_revenue')->default(0);

            // Breakdown biaya platform (semua positif)
            $table->integer('biaya_komisi')->default(0);
            $table->integer('biaya_layanan')->default(0);
            $table->integer('biaya_ongkir')->default(0);
            $table->integer('biaya_afiliasi')->default(0);
            $table->integer('biaya_promosi')->default(0);
            $table->integer('biaya_pajak')->default(0);
            $table->integer('biaya_lainnya')->default(0);

            // HPP & profit
            $table->integer('hpp_total')->nullable();
            $table->integer('profit')->nullable();

            // Status rekonsiliasi
            $table->enum('status', ['matched', 'orphan', 'pending', 'adjustment', 'refund', 'manual'])->default('pending');
            $table->text('reconciliation_notes')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'tanggal']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};