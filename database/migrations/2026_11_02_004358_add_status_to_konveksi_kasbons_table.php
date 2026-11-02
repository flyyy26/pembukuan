<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konveksi_kasbons', function (Blueprint $table) {
            // Tambahkan kolom status, default 'belum_lunas'
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas')->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('konveksi_kasbons', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};