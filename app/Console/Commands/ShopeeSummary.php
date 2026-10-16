<?php

namespace App\Console\Commands;

use App\Models\ShopeeIncomeRow;
use Illuminate\Console\Command;

class ShopeeSummary extends Command
{
    protected $signature = 'shopee:summary {--store=} {--from=} {--to=}';
    protected $description = 'Ringkasan net revenue Shopee per periode';

    public function handle()
    {
        $query = ShopeeIncomeRow::where('tipe_baris', 'sku');

        if ($this->option('store')) {
            $storeId = $this->option('store');
            $query->where('import_id', function ($q) use ($storeId) {
                $q->select('id')->from('imports')
                    ->where('store_id', $storeId)
                    ->where('tipe', 'shopee_income');
            });
        }
        if ($this->option('from')) $query->whereDate('tanggal_dana_dilepaskan', '>=', $this->option('from'));
        if ($this->option('to'))   $query->whereDate('tanggal_dana_dilepaskan', '<=', $this->option('to'));

        $rows = $query->get();

        // Kolom-kolom yang kita kenali
        $kolomBiaya = [
            'Biaya Administrasi',
            'Biaya Proses Pesanan',
            'Biaya Gratis Ongkir XTRA - Ukuran Biasa (Kategori H)',
            'Biaya Transaksi',
            'Biaya Kampanye',
            'Biaya Komisi AMS',
            'Biaya Isi Saldo Otomatis (dari Penghasilan)',
            'Premi',
            'FBS Fee',
            'PPh 22',                        // <-- tambah
            'Promo Gratis Ongkir dari Penjual', // <-- tambah (biaya promo)
        ];

        $kolomDiskon = [
            'Voucher disponsor oleh Penjual',
            'Cashback Koin disponsori Penjual',
            'Diskon Produk dari Shopee',
            'Voucher co-fund disponsor oleh Penjual',
            'Cashback Koin Co-fund disponsori Penjual',
        ];

        $kolomOngkir = [
            'Ongkir Dibayar Pembeli',
            'Ongkos Kirim yang Dibayarkan ke Jasa Kirim',
            'Potongan Ongkos Kirim dari Jasa Kirim',
            'Gratis Ongkir dari Shopee',
            'Ongkos Kirim Pengembalian Barang',
            'Return to Seller Fee',
            'Pengembalian Biaya Kirim',
            'Pengembalian Dana ke Pembeli',  // <-- tambah (refund)
            'Kompensasi',                    // <-- tambah
        ];

        // Hitung total per kolom
        $sum = function ($key) use ($rows) {
            return $rows->sum(fn($r) => $this->toInt($r->raw_data[$key] ?? 0));
        };

        $totalHargaProduk      = $sum('Harga Produk');
        $totalPengembalianDana = $sum('Jumlah Pengembalian Dana ke Pembeli');
        $totalPenghasilan      = $rows->sum('total_penghasilan');

        $this->newLine();
        $this->info('=== Ringkasan Shopee (baris Sku) ===');
        $this->line("Total baris: " . $rows->count());

        $this->newLine();
        $this->info('--- Pendapatan ---');
        $this->line("Harga produk          : " . number_format($totalHargaProduk, 0, ',', '.'));
        $this->line("Pengembalian dana     : " . number_format($totalPengembalianDana, 0, ',', '.'));
        $this->line("Net penghasilan       : " . number_format($totalPenghasilan, 0, ',', '.'));

        $this->newLine();
        $this->info('--- Biaya Platform ---');
        $totalBiaya = 0;
        foreach ($kolomBiaya as $k) {
            $v = $sum($k);
            if ($v != 0) {
                $this->line(str_pad($k, 55) . " : " . number_format($v, 0, ',', '.'));
                $totalBiaya += $v;
            }
        }
        $this->line(str_pad('TOTAL BIAYA', 55) . " : " . number_format($totalBiaya, 0, ',', '.'));

        $this->newLine();
        $this->info('--- Diskon & Voucher ---');
        $totalDiskon = 0;
        foreach ($kolomDiskon as $k) {
            $v = $sum($k);
            if ($v != 0) {
                $this->line(str_pad($k, 55) . " : " . number_format($v, 0, ',', '.'));
                $totalDiskon += $v;
            }
        }
        $this->line(str_pad('TOTAL DISKON', 55) . " : " . number_format($totalDiskon, 0, ',', '.'));

        $this->newLine();
        $this->info('--- Ongkir ---');
        $totalOngkir = 0;
        foreach ($kolomOngkir as $k) {
            $v = $sum($k);
            if ($v != 0) {
                $this->line(str_pad($k, 55) . " : " . number_format($v, 0, ',', '.'));
                $totalOngkir += $v;
            }
        }
        $this->line(str_pad('TOTAL ONGKIR', 55) . " : " . number_format($totalOngkir, 0, ',', '.'));

        // Validasi
        $expected = $totalHargaProduk
            + $totalPengembalianDana
            + $totalBiaya
            + $totalDiskon
            + $totalOngkir;

        $this->newLine();
        $this->info('=== Validasi ===');
        $this->line("Expected (jumlah komponen) : " . number_format($expected, 0, ',', '.'));
        $this->line("Actual (SUM Penghasilan)   : " . number_format($totalPenghasilan, 0, ',', '.'));
        $diff = $totalPenghasilan - $expected;
        $this->line("Selisih                    : " . number_format($diff, 0, ',', '.'));

        if (abs($diff) < 100) {
            $this->info("OK: Konsisten.");
        } else {
            $this->warn("WARNING: Selisih " . number_format($diff, 0, ',', '.'));
            $this->warn("Mungkin ada kolom yang belum kita masukkan ke salah satu kategori.");
        }

        return 0;
    }

    private function toInt($value): int
    {
        if ($value === null || $value === '' || $value === '-') return 0;
        return (int) preg_replace('/[^0-9\-]/', '', (string) $value);
    }
}