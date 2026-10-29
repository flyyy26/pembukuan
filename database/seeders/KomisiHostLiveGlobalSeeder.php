<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KomisiHostLiveGlobalSeeder extends Seeder
{
    /**
     * Migrasi nilai komisi host live lama (per toko) ke nilai global.
     *
     * Logika:
     * - Ambil nilai komisi_host_live_per_trx terbesar (> 0) dari semua toko.
     * - Terapkan nilai tersebut ke kolom komisi_host_live_global di SEMUA baris.
     * - Jika tidak ada data lama sama sekali, tidak melakukan apa pun.
     */
    public function run(): void
    {
        // Pastikan kolom sudah ada (aman kalau seeder dijalankan sebelum migrate)
        if (!Schema::hasTable('store_komisi_settings')) {
            $this->command->warn('Tabel store_komisi_settings tidak ditemukan. Seeder dilewati.');
            return;
        }

        if (!Schema::hasColumn('store_komisi_settings', 'komisi_host_live_global')) {
            $this->command->warn('Kolom komisi_host_live_global belum ada. Jalankan migrate dulu.');
            return;
        }

        // Ambil nilai lama terbesar yang > 0
        $existing = DB::table('store_komisi_settings')
            ->where('komisi_host_live_per_trx', '>', 0)
            ->orderBy('komisi_host_live_per_trx', 'desc')
            ->first();

        if (!$existing) {
            $this->command->info('Tidak ada data komisi host live lama yang perlu dimigrasikan.');
            return;
        }

        $nilaiGlobal = (int) $existing->komisi_host_live_per_trx;

        // Terapkan ke semua baris
        DB::table('store_komisi_settings')->update([
            'komisi_host_live_global' => $nilaiGlobal,
        ]);

        $this->command->info(
            "Berhasil migrasi komisi host live global = Rp " .
            number_format($nilaiGlobal, 0, ',', '.') .
            " (dari toko ID: {$existing->store_id})."
        );
    }
}