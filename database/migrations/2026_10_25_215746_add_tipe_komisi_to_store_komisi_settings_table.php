<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            // 'rp' atau 'persen', default 'rp'
            $table->string('tipe_komisi', 10)->default('rp')->after('komisi_per_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            $table->dropColumn('tipe_komisi');
        });
    }
};