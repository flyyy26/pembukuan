<?php

namespace App\Console\Commands;

use App\Models\ShopeeIncomeRow;
use Illuminate\Console\Command;

class ShopeeReparseIncome extends Command
{
    protected $signature = 'shopee:reparse-income {--store=} {--from=} {--to=}';
    protected $description = 'Isi ulang kolom biaya baru dari raw_data JSON yang sudah tersimpan';

    public function handle()
    {
        $query = ShopeeIncomeRow::whereNotNull('raw_data');

        if ($this->option('store')) {
            $storeId = (int) $this->option('store');
            $query->whereHas('import', fn($q) => $q->where('store_id', $storeId));
        }
        if ($this->option('from')) {
            $query->whereDate('tanggal_dana_dilepaskan', '>=', $this->option('from'));
        }
        if ($this->option('to')) {
            $query->whereDate('tanggal_dana_dilepaskan', '<=', $this->option('to'));
        }

        $total = $query->count();
        if ($total === 0) {
            $this->warn('Tidak ada baris dengan raw_data.');
            return 0;
        }

        $this->info("Total baris: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $skipped = 0;

        $query->chunkById(500, function ($rows) use (&$updated, &$skipped, $bar) {
            foreach ($rows as $row) {
                $raw = $row->raw_data;
                if (!is_array($raw) || empty($raw)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $row->update([
                    'biaya_kampanye'                          => $this->toInt($raw['Biaya Kampanye'] ?? 0),
                    'biaya_isi_saldo_otomatis'                => $this->toInt($raw['Biaya Isi Saldo Otomatis (dari Penghasilan)'] ?? 0),
                    'premi'                                   => $this->toInt($raw['Premi'] ?? 0),
                    'fbs_fee'                                 => $this->toInt($raw['FBS Fee'] ?? 0),
                    'voucher_disponsor_penjual'               => $this->toInt($raw['Voucher disponsor oleh Penjual'] ?? 0),
                    'cashback_koin_disponsori_penjual'        => $this->toInt($raw['Cashback Koin disponsori Penjual'] ?? 0),
                    'diskon_produk_dari_shopee'               => $this->toInt($raw['Diskon Produk dari Shopee'] ?? 0),
                    'voucher_cofund_disponsor_penjual'        => $this->toInt($raw['Voucher co-fund disponsor oleh Penjual'] ?? 0),
                    'cashback_koin_cofund_disponsori_penjual' => $this->toInt($raw['Cashback Koin Co-fund disponsori Penjual'] ?? 0),
                    'ongkos_kirim_pengembalian_barang'        => $this->toInt($raw['Ongkos Kirim Pengembalian Barang'] ?? 0),
                    'return_to_seller_fee'                    => $this->toInt($raw['Return to Seller Fee'] ?? 0),
                    'pengembalian_biaya_kirim'                => $this->toInt($raw['Pengembalian Biaya Kirim'] ?? 0),
                    'potongan_ongkos_kirim_dari_jasa_kirim'   => $this->toInt($raw['Potongan Ongkos Kirim dari Jasa Kirim'] ?? 0),
                ]);

                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Updated: {$updated}");
        if ($skipped > 0) {
            $this->warn("Skipped (raw_data kosong): {$skipped}");
        }

        return 0;
    }

    private function toInt($value): int
    {
        if ($value === null || $value === '' || $value === '-') return 0;
        return (int) preg_replace('/[^0-9\-]/', '', (string) $value);
    }
}