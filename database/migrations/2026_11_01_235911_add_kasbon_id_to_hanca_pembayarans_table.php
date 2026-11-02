<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            // Tambahkan kasbon_id (nullable karena tidak semua pembayaran memotong kasbon)
            $table->foreignId('kasbon_id')->nullable()->after('hanca_detail_id')
                  ->constrained('konveksi_kasbons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['kasbon_id']);
            $table->dropColumn('kasbon_id');
        });
    }
};