<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hutangs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_pemberi_hutang'); // cth: Toko Bangunan, Pak Ahmad
            $table->string('keterangan')->nullable();
            $table->integer('nominal');
            $table->boolean('is_lunas')->default(false);
            $table->date('tanggal_lunas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutangs');
    }
};