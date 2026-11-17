<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            // Menggunakan text agar bisa menampung keterangan panjang
            $table->text('keterangan_selisih')->nullable()->after('manual_validasi_mutasi_rek');
        });
    }

    public function down(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            $table->dropColumn('keterangan_selisih');
        });
    }
};