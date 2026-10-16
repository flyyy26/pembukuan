<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\Store;
use App\Models\TiktokOrderItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportTiktokOrder extends Command
{
    protected $signature = 'import:tiktok-order {file} {store_id}';
    protected $description = 'Import JSON TikTok Order Report ke database';

    public function handle()
    {
        $file = $this->argument('file');
        $storeId = (int) $this->argument('store_id');

        $fullPath = base_path($file);

        if (!file_exists($fullPath)) {
            $this->error("File tidak ditemukan: {$fullPath}");
            return 1;
        }

        $store = Store::find($storeId);
        if (!$store) {
            $this->error("Store dengan id {$storeId} tidak ditemukan");
            $this->line("Buat dulu dengan: php artisan tinker, lalu Store::create([...])");
            return 1;
        }

        $this->info("Store: {$store->nama_toko} ({$store->marketplace})");
        $this->info("File: {$file}");

        // Baca JSON
        $json = file_get_contents($fullPath);
        $rows = json_decode($json, true);

        if (!is_array($rows) || empty($rows)) {
            $this->error("File JSON kosong atau format salah");
            return 1;
        }

        $this->info("Total baris di JSON: " . count($rows));

        // Cek duplikat: apakah file ini sudah pernah diimport?
        $fileHash = hash_file('sha256', $fullPath);
        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'tiktok_order')
            ->first();

        if ($existing) {
            $this->warn("File ini sudah pernah diimport (import_id: {$existing->id})");
            $this->warn("Skip. Kalau mau re-import, hapus record import lama dulu.");
            return 0;
        }

        // Bikin record import
        $import = Import::create([
            'store_id' => $storeId,
            'tipe' => 'tiktok_order',
            'file_name' => basename($file),
            'file_hash' => $fileHash,
            'total_rows' => count($rows),
            'status' => 'pending',
        ]);

        $this->info("Import record dibuat: id={$import->id}");

        // Insert data satu per satu (untuk sekarang, nanti bisa dioptimasi)
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            try {
                TiktokOrderItem::create([
                    'import_id' => $import->id,
                    'order_id' => $row['Order ID'] ?? null,
                    'order_status' => $row['Order Status'] ?? null,
                    'order_substatus' => $row['Order Substatus'] ?? null,
                    'sku_id' => $row['SKU ID'] ?? null,
                    'seller_sku' => $row['Seller SKU'] ?? null,
                    'product_name' => $row['Product Name'] ?? null,
                    'variation' => $row['Variation'] ?? null,
                    'quantity' => $this->toInt($row['Quantity'] ?? 0),
                    'sku_unit_original_price' => $this->toInt($row['SKU Unit Original Price'] ?? 0),
                    'sku_subtotal_before_discount' => $this->toInt($row['SKU Subtotal Before Discount'] ?? 0),
                    'sku_platform_discount' => $this->toInt($row['SKU Platform Discount'] ?? 0),
                    'sku_seller_discount' => $this->toInt($row['SKU Seller Discount'] ?? 0),
                    'sku_subtotal_after_discount' => $this->toInt($row['SKU Subtotal After Discount'] ?? 0),
                    'order_amount' => $this->toInt($row['Order Amount'] ?? 0),
                    'created_time' => $this->toDateTime($row['Created Time'] ?? null),
                    'paid_time' => $this->toDateTime($row['Paid Time'] ?? null),
                    'shipped_time' => $this->toDateTime($row['Shipped Time'] ?? null),
                    'delivered_time' => $this->toDateTime($row['Delivered Time'] ?? null),
                    'cancelled_time' => $this->toDateTime($row['Cancelled Time'] ?? null),
                    'cancel_reason' => $row['Cancel Reason'] ?? null,
                    'payment_method' => $row['Payment Method'] ?? null,
                    'order_channel' => $row['Order Channel'] ?? null,
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $skipped++;
                $this->newLine();
                $this->warn("Skip baris: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Update status import
        $import->update([
            'status' => $skipped === 0 ? 'imported' : 'parsed',
            'total_rows' => $inserted,
            'error_message' => $skipped > 0 ? "{$skipped} baris di-skip" : null,
        ]);

        $this->info("Inserted: {$inserted}");
        if ($skipped > 0) {
            $this->warn("Skipped : {$skipped}");
        }
        $this->info("Selesai.");

        return 0;
    }

    private function toInt($value): int
    {
        if ($value === null || $value === '' || $value === '-') return 0;
        // Hilangkan koma desimal, titik ribuan
        $clean = preg_replace('/[^0-9\-]/', '', (string) $value);
        return (int) $clean;
    }

    private function toDateTime($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;

        try {
            // Format: "14/10/2026 18:33:19"
            return Carbon::createFromFormat('d/m/Y H:i:s', $value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
}