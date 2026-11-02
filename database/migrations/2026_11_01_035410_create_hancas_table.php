<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hancas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konveksi_karyawan_id')->constrained('konveksi_karyawans')->onDelete('cascade');
            $table->string('kategori'); // Kaos, Kemeja, dll (bisa manual)
            $table->string('nama_produk');
            $table->string('model')->nullable();
            $table->string('warna')->nullable();
            $table->string('ukuran')->nullable();
            $table->integer('jumlah_hanca');
            $table->bigInteger('harga'); // harga satuan
            $table->bigInteger('total'); // jumlah * harga
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hancas');
    }
};