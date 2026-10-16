<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_income_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();

            $table->enum('tipe_baris', ['order', 'sku']);
            $table->string('no_pesanan')->index();
            $table->string('no_pengajuan')->nullable();
            $table->string('id_produk')->nullable()->index();
            $table->string('nama_produk', 500)->nullable();
            $table->date('waktu_pesanan_dibuat')->nullable();
            $table->date('tanggal_dana_dilepaskan')->nullable();
            $table->string('tipe_pesanan')->nullable();
            $table->integer('total_penghasilan')->default(0);
            $table->integer('harga_produk')->default(0);
            $table->integer('biaya_administrasi')->default(0);
            $table->integer('biaya_proses_pesanan')->default(0);
            $table->integer('biaya_gratis_ongkir_xtra')->default(0);
            $table->integer('biaya_transaksi')->default(0);
            $table->integer('biaya_komisi_ams')->default(0);
            $table->integer('pph_22')->default(0);
            $table->integer('ongkos_kirim_dibayarkan')->default(0);
            $table->integer('gratis_ongkir_dari_shopee')->default(0);
            $table->integer('jumlah_pengembalian_dana')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_income_rows');
    }
};