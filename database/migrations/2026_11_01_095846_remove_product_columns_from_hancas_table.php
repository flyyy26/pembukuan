<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            // ✅ Hapus foreign key dulu (jika ada)
            $table->dropForeign(['kategori_produk_id']);
            
            // ✅ Hapus kolom-kolom produk yang pindah ke hanca_details
            $table->dropColumn([
                'kategori_produk_id',
                'nama_produk',
                'warna',
                'ukuran',
                'jumlah_hanca',
                'sisa_hanca',
                'harga',
                'total',
                'sisa_nominal',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->foreignId('kategori_produk_id')->nullable()->constrained('konveksi_kategori_produks')->onDelete('set null');
            $table->string('nama_produk')->nullable();
            $table->string('warna')->nullable();
            $table->string('ukuran')->nullable();
            $table->integer('jumlah_hanca')->default(0);
            $table->integer('sisa_hanca')->default(0);
            $table->bigInteger('harga')->default(0);
            $table->bigInteger('total')->default(0);
            $table->bigInteger('sisa_nominal')->default(0);
        });
    }
};