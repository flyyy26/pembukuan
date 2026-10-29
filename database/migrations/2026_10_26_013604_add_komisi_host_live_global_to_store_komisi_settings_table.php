<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom baru
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            $table->integer('komisi_host_live_global')
                ->default(0)
                ->after('komisi_host_live_per_trx')
                ->comment('Komisi host live global per transaksi (berlaku semua toko non-BRILink)');
        });

        // 2. Migrasi data lama: ambil nilai terbesar dari komisi_host_live_per_trx
        //    (karena dulu bisa beda per toko, kita ambil yang paling relevan)
        $existing = DB::table('store_komisi_settings')
            ->where('komisi_host_live_per_trx', '>', 0)
            ->orderByDesc('komisi_host_live_per_trx')
            ->first();

        if ($existing) {
            DB::table('store_komisi_settings')->update([
                'komisi_host_live_global' => (int) $existing->komisi_host_live_per_trx,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            $table->dropColumn('komisi_host_live_global');
        });
    }
};