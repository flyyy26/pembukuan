<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ShopeeParseIncome extends Command
{
    protected $signature = 'shopee:parse-income {file}';
    protected $description = 'Parse Shopee Income Report ke JSON';

    public function handle()
    {
        $fullPath = base_path($this->argument('file'));

        if (!file_exists($fullPath)) {
            $this->error("File tidak ditemukan: {$fullPath}");
            return 1;
        }

        $this->info("Membaca: {$fullPath}");

        $spreadsheet = IOFactory::load($fullPath);

        // Cari sheet 'Penghasilan'
        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            $this->line("Sheet ditemukan: " . $s->getTitle());
            if ($s->getTitle() === 'Penghasilan') {
                $sheet = $s;
            }
        }

        if (!$sheet) {
            $this->error("Sheet 'Penghasilan' tidak ditemukan");
            return 1;
        }

        $data = $sheet->toArray(null, true, true, false);

        // Struktur Shopee:
        // Baris 0 = kategori besar
        // Baris 1 = sub-kategori
        // Baris 2 = nama kolom sebenarnya
        // Baris 3+ = data
        if (count($data) < 3) {
            $this->error("File terlalu pendek, tidak ada header di baris 3");
            return 1;
        }

        $headers = array_map('trim', $data[2]);

        $orderRows = [];
        $skuRows = [];

        for ($i = 3; $i < count($data); $i++) {
            $row = $data[$i];

            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') continue;
                $value = $row[$index] ?? null;
                $assoc[$header] = is_string($value) ? trim($value) : $value;
            }

            $tipe = $assoc['Lihat berdasarkan'] ?? '';

            if ($tipe === 'Order') {
                $orderRows[] = $assoc;
            } elseif ($tipe === 'Sku') {
                $skuRows[] = $assoc;
            }
        }

        $this->newLine();
        $this->info("=== Statistik ===");
        $this->line("Total baris 'Order' : " . count($orderRows));
        $this->line("Total baris 'Sku'   : " . count($skuRows));

        $this->newLine();
        $this->info("=== Contoh baris 'Order' pertama ===");
        $this->line(json_encode($orderRows[0] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->newLine();
        $this->info("=== Contoh baris 'Sku' pertama ===");
        $this->line(json_encode($skuRows[0] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // Simpan JSON
        $outputDir = storage_path('app/parsed');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents(
            $outputDir . '/shopee-orders.json',
            json_encode($orderRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        file_put_contents(
            $outputDir . '/shopee-skus.json',
            json_encode($skuRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->newLine();
        $this->info("Tersimpan di:");
        $this->line("  - {$outputDir}/shopee-orders.json");
        $this->line("  - {$outputDir}/shopee-skus.json");

        return 0;
    }
}