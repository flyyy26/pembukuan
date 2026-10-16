<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\Store;
use App\Models\TiktokIncome;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportTiktokIncome extends Command
{
    protected $signature = 'import:tiktok-income {file} {store_id}';
    protected $description = 'Import JSON TikTok Income Report ke database';

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

        // Cek duplikat
        $fileHash = hash_file('sha256', $fullPath);
        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'tiktok_income')
            ->first();
        if ($existing) {
            $this->warn("File ini sudah pernah diimport (id: {$existing->id})");
            return 0;
        }

        // Deteksi periode dari data
        $dates = collect($rows)->pluck('Waktu pemesanan')->filter()->sort()->values();
        $periodeStart = $dates->first();
        $periodeEnd = $dates->last();

        $import = Import::create([
            'store_id' => $storeId,
            'tipe' => 'tiktok_income',
            'file_name' => basename($file),
            'file_hash' => $fileHash,
            'periode_start' => $this->toDate($periodeStart),
            'periode_end' => $this->toDate($periodeEnd),
            'total_rows' => count($rows),
            'status' => 'pending',
        ]);

        $this->info("Import id: {$import->id}");
        $this->info("Periode: {$periodeStart} s/d {$periodeEnd}");

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            try {
                TiktokIncome::create([
                    'import_id' => $import->id,
                    'order_id' => $row['ID Pesanan/Penyesuaian'] ?? null,
                    'jenis_transaksi' => $row['Jenis transaksi'] ?? null,
                    'waktu_pemesanan' => $this->toDate($row['Waktu pemesanan'] ?? null),
                    'waktu_pembayaran_pesanan' => $this->toDate($row['Waktu pembayaran pesanan'] ?? null),
                    'jumlah_penyelesaian_pembayaran' => $this->toInt($row['Jumlah penyelesaian pembayaran'] ?? 0),
                    'total_pendapatan' => $this->toInt($row['Total Pendapatan'] ?? 0),
                    'subtotal_setelah_diskon_penjual' => $this->toInt($row['Subtotal setelah diskon penjual'] ?? 0),
                    'subtotal_sebelum_diskon' => $this->toInt($row['Subtotal sebelum diskon'] ?? 0),
                    'diskon_penjual' => $this->toInt($row['Diskon penjual'] ?? 0),
                    'total_biaya' => $this->toInt($row['Total Biaya'] ?? 0),
                    'biaya_komisi_platform' => $this->toInt($row['Biaya komisi platform'] ?? 0),
                    'komisi_afiliasi' => $this->toInt($row['Komisi Afiliasi'] ?? 0),
                    'komisi_dinamis' => $this->toInt($row['Komisi dinamis'] ?? 0),
                    'biaya_growth_xtra' => $this->toInt($row['Biaya layanan Program Growth Xtra'] ?? 0),
                    'biaya_pemrosesan_pesanan' => $this->toInt($row['Biaya pemrosesan pesanan'] ?? 0),
                    'pph_22' => $this->toInt($row['PPh Pasal 22 dipungut'] ?? 0),
                    'ongkir' => $this->toInt($row['Ongkir'] ?? 0),
                    'detail_produk_terjual' => $row['Detail produk terjual'] ?? null,
                    'detail_produk_terjual_parsed' => $row['detail_produk_terjual_parsed'] ?? [],
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $skipped++;
                $this->newLine();
                $this->warn("Skip: " . $e->getMessage());
            }
            $bar->advance();
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
        $clean = preg_replace('/[^0-9\-]/', '', (string) $value);
        return (int) $clean;
    }

    private function toDate($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;
        try {
            // Format: "2026/10/13"
            return Carbon::createFromFormat('Y/m/d', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
}