<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_optimizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();

            $table->string('nama_produk');
            $table->string('marketplace_sku')->nullable();

            // ===== Finansial =====
            $table->integer('harga_produk')->default(0);
            $table->integer('biaya_layanan')->default(0);
            $table->integer('ongkir')->default(0);
            $table->integer('diskon_produk')->default(0);
            $table->integer('total_diskon_pengiriman')->default(0);
            $table->integer('voucher_diskon')->default(0);
            $table->integer('harga_final')->default(0);

            // ===== Audit (JSON: ['status' => 'aman|evaluasi|perhatian', 'note' => '...']) =====
            $table->json('audit_ukuran')->nullable();
            $table->json('audit_deskripsi')->nullable();
            $table->json('audit_stok')->nullable();
            $table->json('audit_harga')->nullable();
            $table->json('audit_rating')->nullable();
            $table->json('audit_retur')->nullable();
            $table->json('audit_ongkir')->nullable();
            $table->json('audit_foto')->nullable();
            $table->json('audit_video')->nullable();

            // ===== Meta =====
            $table->tinyInteger('skor_optimasi')->default(0); // 0-100
            $table->enum('status_global', ['aman', 'evaluasi', 'perhatian'])->default('aman');
            $table->date('terakhir_audit')->nullable();

            $table->timestamps();

            $table->index('store_id');
            $table->index('status_global');
            $table->index('marketplace_sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_optimizations');
    }
};