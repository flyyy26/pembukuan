<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->onDelete('cascade');
            $table->string('no_pesanan')->index();
            $table->string('no_pengajuan')->nullable();
            $table->string('tipe_laporan')->default('return'); // 'return' atau 'cancel'
            $table->string('status_pengajuan')->nullable();
            $table->string('alasan_pengajuan')->nullable();
            $table->string('sku_varian')->nullable();
            $table->string('nama_produk')->nullable();
            $table->integer('jumlah')->default(0);
            $table->integer('nominal_pengembalian')->default(0);
            $table->date('waktu_pengajuan')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_returns');
    }
};