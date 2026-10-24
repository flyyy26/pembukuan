<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang', 100);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('harga_satuan');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index('nama_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_barang');
    }
};