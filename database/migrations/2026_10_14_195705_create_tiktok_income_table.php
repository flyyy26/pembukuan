<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_income', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();

            $table->string('order_id')->index();
            $table->string('jenis_transaksi')->nullable();
            $table->date('waktu_pemesanan')->nullable();
            $table->date('waktu_pembayaran_pesanan')->nullable();
            $table->integer('jumlah_penyelesaian_pembayaran')->default(0);
            $table->integer('total_pendapatan')->default(0);
            $table->integer('subtotal_setelah_diskon_penjual')->default(0);
            $table->integer('subtotal_sebelum_diskon')->default(0);
            $table->integer('diskon_penjual')->default(0);
            $table->integer('total_biaya')->default(0);
            $table->integer('biaya_komisi_platform')->default(0);
            $table->integer('komisi_afiliasi')->default(0);
            $table->integer('komisi_dinamis')->default(0);
            $table->integer('biaya_growth_xtra')->default(0);
            $table->integer('biaya_pemrosesan_pesanan')->default(0);
            $table->integer('pph_22')->default(0);
            $table->integer('ongkir')->default(0);
            $table->string('detail_produk_terjual')->nullable();
            $table->json('detail_produk_terjual_parsed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_income');
    }
};