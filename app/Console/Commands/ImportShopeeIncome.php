<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\ShopeeIncomeRow;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportShopeeIncome extends Command
{
    protected $signature = 'import:shopee-income {orders_file} {skus_file} {store_id}';
    protected $description = 'Import JSON Shopee Income (orders + skus) ke database';

    public function handle()
    {
        $ordersFile = base_path($this->argument('orders_file'));
        $skusFile = base_path($this->argument('skus_file'));
        $storeId = (int) $this->argument('store_id');

        foreach ([$ordersFile, $skusFile] as $f) {
            if (!file_exists($f)) {
                $this->error("File tidak ditemukan: {$f}");
                return 1;
            }
        }

        $store = Store::find($storeId);
        if (!$store) {
            $this->error("Store id {$storeId} tidak ditemukan");
            return 1;
        }

        $orders = json_decode(file_get_contents($ordersFile), true);
        $skus = json_decode(file_get_contents($skusFile), true);

        $this->info("Store: {$store->nama_toko}");
        $this->info("Orders: " . count($orders) . " baris");
        $this->info("SKUs  : " . count($skus) . " baris");

        // Hash gabungan
        $fileHash = hash('sha256', hash_file('sha256', $ordersFile) . hash_file('sha256', $skusFile));
        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'shopee_income')
            ->first();
        if ($existing) {
            $this->warn("Sudah pernah diimport (id: {$existing->id})");
            return 0;
        }

        // Deteksi periode dari orders
        $dates = collect($orders)->pluck('Tanggal Dana Dilepaskan')->filter()->sort()->values();
        $periodeStart = $dates->first();
        $periodeEnd = $dates->last();

        $import = Import::create([
            'store_id' => $storeId,
            'tipe' => 'shopee_income',
            'file_name' => basename($this->argument('orders_file')),
            'file_hash' => $fileHash,
            'periode_start' => $this->toDate($periodeStart),
            'periode_end' => $this->toDate($periodeEnd),
            'total_rows' => count($orders) + count($skus),
            'status' => 'pending',
        ]);

        $this->info("Import id: {$import->id}");
        $this->info("Periode: {$periodeStart} s/d {$periodeEnd}");

        $total = count($orders) + count($skus);
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $inserted = 0;
        $skipped = 0;

        foreach (array_merge($orders, $skus) as $row) {
            try {
                $tipe = strtolower($row['Lihat berdasarkan'] ?? '');
                if (!in_array($tipe, ['order', 'sku'])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                ShopeeIncomeRow::create([
                    'import_id' => $import->id,
                    'tipe_baris' => $tipe,
                    'no_pesanan' => $row['No. Pesanan'] ?? null,
                    'no_pengajuan' => $this->nullify($row['No. Pengajuan'] ?? null),
                    'id_produk' => $this->nullify($row['ID Produk'] ?? null),
                    'nama_produk' => $this->nullify($row['Nama Produk'] ?? null),
                    'waktu_pesanan_dibuat' => $this->toDate($row['Waktu Pesanan Dibuat'] ?? null),
                    'tanggal_dana_dilepaskan' => $this->toDate($row['Tanggal Dana Dilepaskan'] ?? null),
                    'tipe_pesanan' => $this->nullify($row['Tipe Pesanan'] ?? null),
                    'total_penghasilan' => $this->toInt($row['Total Penghasilan'] ?? 0),
                    'harga_produk' => $this->toInt($row['Harga Produk'] ?? 0),
                    'biaya_administrasi' => $this->toInt($row['Biaya Administrasi'] ?? 0),
                    'biaya_proses_pesanan' => $this->toInt($row['Biaya Proses Pesanan'] ?? 0),
                    'biaya_gratis_ongkir_xtra' => $this->toInt($row['Biaya Gratis Ongkir XTRA - Ukuran Biasa (Kategori H)'] ?? 0),
                    'biaya_transaksi' => $this->toInt($row['Biaya Transaksi'] ?? 0),
                    'biaya_komisi_ams' => $this->toInt($row['Biaya Komisi AMS'] ?? 0),
                    'pph_22' => $this->toInt($row['PPh 22'] ?? 0),
                    'ongkos_kirim_dibayarkan' => $this->toInt($row['Ongkos Kirim yang Dibayarkan ke Jasa Kirim'] ?? 0),
                    'gratis_ongkir_dari_shopee' => $this->toInt($row['Gratis Ongkir dari Shopee'] ?? 0),
                    'jumlah_pengembalian_dana' => $this->toInt($row['Jumlah Pengembalian Dana ke Pembeli'] ?? 0),
                    'biaya_kampanye' => $this->toInt($row['Biaya Kampanye'] ?? 0),
                    'biaya_isi_saldo_otomatis' => $this->toInt($row['Biaya Isi Saldo Otomatis (dari Penghasilan)'] ?? 0),
                    'premi' => $this->toInt($row['Premi'] ?? 0),
                    'fbs_fee' => $this->toInt($row['FBS Fee'] ?? 0),
                    'voucher_disponsor_penjual' => $this->toInt($row['Voucher disponsor oleh Penjual'] ?? 0),
                    'cashback_koin_disponsori_penjual' => $this->toInt($row['Cashback Koin disponsori Penjual'] ?? 0),
                    'diskon_produk_dari_shopee' => $this->toInt($row['Diskon Produk dari Shopee'] ?? 0),
                    'voucher_cofund_disponsor_penjual' => $this->toInt($row['Voucher co-fund disponsor oleh Penjual'] ?? 0),
                    'cashback_koin_cofund_disponsori_penjual' => $this->toInt($row['Cashback Koin Co-fund disponsori Penjual'] ?? 0),
                    'ongkos_kirim_pengembalian_barang' => $this->toInt($row['Ongkos Kirim Pengembalian Barang'] ?? 0),
                    'return_to_seller_fee' => $this->toInt($row['Return to Seller Fee'] ?? 0),
                    'pengembalian_biaya_kirim' => $this->toInt($row['Pengembalian Biaya Kirim'] ?? 0),
                    'potongan_ongkos_kirim_dari_jasa_kirim' => $this->toInt($row['Potongan Ongkos Kirim dari Jasa Kirim'] ?? 0),
                    'raw_data' => $row, 
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
        // Handle angka negatif dan desimal
        $clean = preg_replace('/[^0-9\-]/', '', (string) $value);
        return (int) $clean;
    }

    private function toDate($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;
        try {
            // Format: "2026-10-07"
            return Carbon::parse($value)->format('Y-m-d');
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