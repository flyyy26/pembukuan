<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();

            $table->string('no_pesanan')->index();
            $table->string('status_pesanan')->nullable();
            $table->string('status_pembatalan')->nullable();
            $table->string('no_resi')->nullable();
            $table->string('opsi_pengiriman')->nullable();
            $table->dateTime('waktu_pesanan_dibuat')->nullable();
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->dateTime('waktu_pesanan_selesai')->nullable();
            $table->string('tipe_pesanan')->nullable();
            $table->string('metode_pembayaran')->nullable();

            $table->string('sku_induk')->nullable()->index();
            $table->string('nama_produk')->nullable();
            $table->string('sku_varian')->nullable()->index(); // Nomor Referensi SKU
            $table->string('nama_variasi')->nullable();

            $table->integer('harga_awal')->default(0);
            $table->integer('harga_setelah_diskon')->default(0);
            $table->integer('jumlah')->default(0);
            $table->integer('returned_quantity')->default(0);
            $table->integer('subtotal_pesanan')->default(0);
            $table->integer('total_diskon')->default(0);
            $table->integer('diskon_dari_penjual')->default(0);
            $table->integer('diskon_dari_shopee')->default(0);
            $table->integer('ongkos_kirim_dibayar_pembeli')->default(0);
            $table->integer('total_pembayaran')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_order_items');
    }
};