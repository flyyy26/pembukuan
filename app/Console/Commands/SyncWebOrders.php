<?php
// app/Console/Commands/SyncWebOrders.php

namespace App\Console\Commands;

use App\Services\WebOrderSyncService;
use Illuminate\Console\Command;

class SyncWebOrders extends Command
{
    protected $signature = 'web:sync-orders
                            {--since= : Tanggal mulai sync (Y-m-d). Default: 90 hari terakhir}';
    protected $description = 'Sinkronisasi pesanan completed dari DB web ecommerce ke DB lokal BukuBarokah';

    public function handle(WebOrderSyncService $service): int
    {
        $this->info('🔄 Memulai sinkronisasi pesanan web...');

        $log = $service->syncCompletedOrders($this->option('since'));

        if ($log->status === 'success') {
            $this->info("✅ Selesai. Created: {$log->records_created}, Updated: {$log->records_updated}");
            return self::SUCCESS;
        }

        $this->error("❌ Gagal: {$log->error_message}");
        return self::FAILURE;
    }
}