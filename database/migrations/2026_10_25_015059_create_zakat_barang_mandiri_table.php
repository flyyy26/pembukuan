<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_barang_mandiri', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->integer('qty');
            $table->integer('harga_satuan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_barang_mandiri');
    }
};