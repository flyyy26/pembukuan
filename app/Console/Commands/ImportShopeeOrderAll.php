<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\ShopeeOrderItem;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportShopeeOrderAll extends Command
{
    protected $signature = 'import:shopee-order-all {file} {store_id}';
    protected $description = 'Import JSON Shopee Order.all ke database';

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
            $this->error("Store id {$storeId} tidak ditemukan");
            return 1;
        }

        $rows = json_decode(file_get_contents($fullPath), true);
        if (!is_array($rows) || empty($rows)) {
            $this->error("File JSON kosong atau format salah");
            return 1;
        }

        $this->info("Store: {$store->nama_toko}");
        $this->info("Total baris: " . count($rows));

        $fileHash = hash_file('sha256', $fullPath);
        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'shopee_order')
            ->first();

        if ($existing) {
            $this->warn("File ini sudah pernah diimport (id: {$existing->id})");
            return 0;
        }

        // Deteksi periode
        $dates = collect($rows)->pluck('Waktu Pesanan Dibuat')
            ->filter()
            ->map(fn($d) => $this->toDateTime($d))
            ->filter()
            ->sort()
            ->values();

        $firstDate = $dates->first();
        $lastDate = $dates->last();

        $import = Import::create([
            'store_id' => $storeId,
            'tipe' => 'shopee_order',
            'file_name' => basename($file),
            'file_hash' => $fileHash,
            'periode_start' => $firstDate ? substr($firstDate, 0, 10) : null,
            'periode_end'   => $lastDate ? substr($lastDate, 0, 10) : null,
            'total_rows' => count($rows),
            'status' => 'pending',
        ]);

        $this->info("Import id: {$import->id}");

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $inserted = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                try {
                    ShopeeOrderItem::create([
                        'import_id' => $import->id,
                        'no_pesanan' => $row['No. Pesanan'] ?? null,
                        'status_pesanan' => $this->nullify($row['Status Pesanan'] ?? null),
                        'status_pembatalan' => $this->nullify($row['Status Pembatalan/ Pengembalian'] ?? null),
                        'no_resi' => $this->nullify($row['No. Resi'] ?? null),
                        'opsi_pengiriman' => $this->nullify($row['Opsi Pengiriman'] ?? null),
                        'waktu_pesanan_dibuat' => $this->toDateTime($row['Waktu Pesanan Dibuat'] ?? null),
                        'waktu_pembayaran' => $this->toDateTime($row['Waktu Pembayaran Dilakukan'] ?? null),
                        'waktu_pesanan_selesai' => $this->toDateTime($row['Waktu Pesanan Selesai'] ?? null),
                        'tipe_pesanan' => $this->nullify($row['Tipe Pesanan'] ?? null),
                        'metode_pembayaran' => $this->nullify($row['Metode Pembayaran'] ?? null),
                        'sku_induk' => $this->nullify($row['SKU Induk'] ?? null),
                        'nama_produk' => $this->nullify($row['Nama Produk'] ?? null),
                        'sku_varian' => $this->nullify($row['Nomor Referensi SKU'] ?? null),
                        'nama_variasi' => $this->nullify($row['Nama Variasi'] ?? null),
                        'harga_awal' => $this->toInt($row['Harga Awal'] ?? 0),
                        'harga_setelah_diskon' => $this->toInt($row['Harga Setelah Diskon'] ?? 0),
                        'jumlah' => $this->toInt($row['Jumlah'] ?? 0),
                        'returned_quantity' => $this->toInt($row['Returned quantity'] ?? 0),
                        'subtotal_pesanan' => $this->toInt($row['Subtotal Pesanan'] ?? 0),
                        'total_diskon' => $this->toInt($row['Total Diskon'] ?? 0),
                        'diskon_dari_penjual' => $this->toInt($row['Diskon Dari Penjual'] ?? 0),
                        'diskon_dari_shopee' => $this->toInt($row['Diskon Dari Shopee'] ?? 0),
                        'ongkos_kirim_dibayar_pembeli' => $this->toInt($row['Ongkos Kirim Dibayar oleh Pembeli'] ?? 0),
                        'total_pembayaran' => $this->toInt($row['Total Pembayaran'] ?? 0),
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    $skipped++;
                    $this->newLine();
                    $this->warn("Skip: " . $e->getMessage());
                }
                $bar->advance();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal: " . $e->getMessage());
            return 1;
        }

        $bar->finish();
        $this->newLine(2);

        $import->update([
            'status' => $skipped === 0 ? 'imported' : 'parsed',
            'error_message' => $skipped > 0 ? "{$skipped} baris di-skip" : null,
        ]);

        $this->info("Inserted: {$inserted}");
        if ($skipped > 0) $this->warn("Skipped : {$skipped}");

        return 0;
    }

    private function toInt($value): int
    {
        if ($value === null || $value === '' || $value === '-') return 0;
        // Shopee pakai format "110.000" (titik = ribuan)
        // Handle juga kalau ada koma desimal
        $clean = preg_replace('/[^0-9\-]/', '', (string) $value);
        return (int) $clean;
    }

    private function toDateTime($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;
        try {
            // Format: "2026-10-01 00:04" atau "2026-10-01 00:04:00"
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function nullify($value): ?string
    {
        if ($value === null || $value === '' || $value === '-') return null;
        return $value;
    }
}