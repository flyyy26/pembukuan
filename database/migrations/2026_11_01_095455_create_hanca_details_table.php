<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hanca_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hanca_id')->constrained('hancas')->onDelete('cascade');
            $table->foreignId('kategori_produk_id')->nullable()->constrained('konveksi_kategori_produks')->onDelete('set null');
            $table->string('nama_produk');
            $table->string('warna')->nullable();
            $table->string('ukuran')->nullable();
            $table->integer('jumlah_hanca');
            $table->integer('sisa_hanca');
            $table->bigInteger('harga');
            $table->bigInteger('total');
            $table->bigInteger('sisa_nominal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hanca_details');
    }
};