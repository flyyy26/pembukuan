<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konveksi_kasbons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konveksi_karyawan_id')->constrained('konveksi_karyawans')->onDelete('cascade');
            $table->bigInteger('nominal');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konveksi_kasbons');
    }
};