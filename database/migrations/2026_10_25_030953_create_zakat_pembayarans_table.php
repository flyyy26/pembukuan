<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->integer('total_zakat');
            $table->integer('total_saldo');
            $table->integer('total_piutang');
            $table->integer('total_barang');
            $table->integer('total_hutang');
            $table->integer('total_tabungan');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_pembayarans');
    }
};