<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hanca_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hanca_id')->constrained('hancas')->onDelete('cascade');
            $table->enum('tipe', ['dp', 'cicil', 'kasbon', 'bayaran']); // dp, cicil, kasbon(becer), bayaran(lunas)
            $table->bigInteger('nominal');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hanca_pembayarans');
    }
};