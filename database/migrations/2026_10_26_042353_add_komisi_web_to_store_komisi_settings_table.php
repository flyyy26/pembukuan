<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            // Menyimpan rate komisi web (bisa Rp atau Persen)
            $table->integer('komisi_web_per_trx')->default(0)->after('komisi_host_live_global');
            // Menyimpan tipe komisi web ('rp' atau 'persen')
            $table->string('tipe_komisi_web', 10)->default('rp')->after('komisi_web_per_trx');
        });
    }

    public function down(): void
    {
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            $table->dropColumn(['komisi_web_per_trx', 'tipe_komisi_web']);
        });
    }
};