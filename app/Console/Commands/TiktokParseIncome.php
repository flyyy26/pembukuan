<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TiktokParseIncome extends Command
{
    protected $signature = 'tiktok:parse-income {file}';
    protected $description = 'Parse TikTok Income Report ke JSON';

    public function handle()
    {
        $fullPath = base_path($this->argument('file'));

        if (!file_exists($fullPath)) {
            $this->error("File tidak ditemukan: {$fullPath}");
            return 1;
        }

        $this->info("Membaca: {$fullPath}");

        $spreadsheet = IOFactory::load($fullPath);

        // Cari sheet 'Detail pesanan'
        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            $this->line("Sheet ditemukan: " . $s->getTitle());
            if ($s->getTitle() === 'Detail pesanan') {
                $sheet = $s;
            }
        }

        if (!$sheet) {
            $this->error("Sheet 'Detail pesanan' tidak ditemukan");
            return 1;
        }

        $data = $sheet->toArray(null, true, true, false);

        // Baris 0 = header, baris 1+ = data
        $headers = array_map('trim', $data[0]);

        $rows = [];
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];

            // Skip baris kosong
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') continue;
                $value = $row[$index] ?? null;
                $assoc[$header] = is_string($value) ? trim($value) : $value;
            }

            $rows[] = $assoc;
        }

        // Parsing kolom 'Detail produk terjual' jadi array terstruktur
        foreach ($rows as &$row) {
            $detail = $row['Detail produk terjual'] ?? '';

            if ($detail === '/' || $detail === '' || $detail === null) {
                $row['detail_produk_terjual_parsed'] = [];
                continue;
            }

            $items = [];
            // Format: "123 * 1; 456 * 2;"
            $parts = array_filter(array_map('trim', explode(';', $detail)));
            foreach ($parts as $part) {
                if (preg_match('/^(\d+)\s*\*\s*(\d+)$/', $part, $m)) {
                    $items[] = [
                        'sku_id' => $m[1],
                        'qty'    => (int) $m[2],
                    ];
                }
            }
            $row['detail_produk_terjual_parsed'] = $items;
        }
        unset($row);

        // Statistik ringkas
        $totalRows        = count($rows);
        $refundRows       = 0;
        $withProductRows  = 0;
        $multiSkuRows     = 0;

        foreach ($rows as $row) {
            $parsed = $row['detail_produk_terjual_parsed'] ?? [];
            if (empty($parsed)) {
                $refundRows++;
            } else {
                $withProductRows++;
                if (count($parsed) > 1) {
                    $multiSkuRows++;
                }
            }
        }

        $this->newLine();
        $this->info("=== Statistik ===");
        $this->line("Total baris data        : {$totalRows}");
        $this->line("Tanpa produk (refund)   : {$refundRows}");
        $this->line("Dengan produk           : {$withProductRows}");
        $this->line("Multi-SKU (1 order >1)  : {$multiSkuRows}");

        // Tampilkan 2 baris pertama
        $this->newLine();
        $this->info("=== Contoh 2 baris pertama ===");
        for ($i = 0; $i < min(2, count($rows)); $i++) {
            $preview = $rows[$i];
            // Biar output tidak terlalu panjang, tampilkan kolom penting saja
            $slim = [
                'ID Pesanan/Penyesuaian'            => $preview['ID Pesanan/Penyesuaian'] ?? null,
                'Jenis transaksi'                   => $preview['Jenis transaksi'] ?? null,
                'Waktu pemesanan'                   => $preview['Waktu pemesanan'] ?? null,
                'Jumlah penyelesaian pembayaran'    => $preview['Jumlah penyelesaian pembayaran'] ?? null,
                'Total Pendapatan'                  => $preview['Total Pendapatan'] ?? null,
                'Subtotal setelah diskon penjual'   => $preview['Subtotal setelah diskon penjual'] ?? null,
                'Total Biaya'                       => $preview['Total Biaya'] ?? null,
                'Detail produk terjual'             => $preview['Detail produk terjual'] ?? null,
                'detail_produk_terjual_parsed'      => $preview['detail_produk_terjual_parsed'] ?? null,
            ];
            $this->line(json_encode($slim, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->newLine();
        }

        // Simpan JSON
        $outputDir = storage_path('app/parsed');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        $outputFile = $outputDir . '/tiktok-income.json';
        file_put_contents(
            $outputFile,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info("Tersimpan di: {$outputFile}");

        return 0;
    }
}