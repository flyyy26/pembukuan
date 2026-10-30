<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;

class ReconcileAllProducts extends Command
{
    protected $signature = 'products:reconcile-all 
                            {--store= : Reconcile hanya 1 store (opsional)} 
                            {--marketplace= : Filter marketplace: shopee|tiktok (opsional)}';
    protected $description = 'Reconcile semua store (Shopee & TikTok) berdasarkan kategori & prefix SKU';

    public function handle()
    {
        $storeId     = $this->option('store');
        $marketplace = $this->option('marketplace');

        $query = Store::where('is_active', true);

        if ($storeId) {
            $query->where('id', (int) $storeId);
        }

        if ($marketplace) {
            $query->where('marketplace', $marketplace);
        }

        $stores = $query->orderBy('marketplace')->orderBy('nama_toko')->get();

        if ($stores->isEmpty()) {
            $this->warn('Tidak ada store yang cocok dengan filter.');
            return 0;
        }

        $this->info("Reconcile {$stores->count()} store...");
        $this->newLine();

        $bar = $this->output->createProgressBar($stores->count());
        $bar->start();

        $success = 0;
        $failed  = 0;
        $skipped = 0;

        foreach ($stores as $store) {
            $cmd = match ($store->marketplace) {
                'shopee' => 'shopee:reconcile',
                'tiktok' => 'tiktok:reconcile',
                default  => null,
            };

            if ($cmd === null) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                \Artisan::call($cmd, ['store_id' => $store->id]);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn("Gagal [{$store->marketplace} #{$store->id} {$store->nama_toko}]: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("=== Ringkasan ===");
        $this->line("Berhasil : {$success}");
        if ($failed > 0) {
            $this->warn("Gagal    : {$failed}");
        }
        if ($skipped > 0) {
            $this->line("Skip     : {$skipped} (marketplace tidak didukung)");
        }

        return $failed > 0 ? 1 : 0;
    }
}