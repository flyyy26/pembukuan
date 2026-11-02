<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            // Kolom untuk menyimpan nominal kasbon yang dipotong pada transaksi ini
            $table->bigInteger('kasbon_dipotong')->default(0)->after('nominal');
        });
    }

    public function down(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            $table->dropColumn('kasbon_dipotong');
        });
    }
};