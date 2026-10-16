<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ShopeeParseOrderAll extends Command
{
    protected $signature = 'shopee:parse-order-all {file}';
    protected $description = 'Parse Shopee Order.all report ke JSON';

    public function handle()
    {
        $fullPath = base_path($this->argument('file'));

        if (!file_exists($fullPath)) {
            $this->error("File tidak ditemukan: {$fullPath}");
            return 1;
        }

        $this->info("Membaca: {$fullPath}");

        $spreadsheet = IOFactory::load($fullPath);

        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            $this->line("Sheet: " . $s->getTitle());
            if ($s->getTitle() === 'orders') {
                $sheet = $s;
                break;
            }
        }

        if (!$sheet) {
            $this->error("Sheet 'orders' tidak ditemukan");
            return 1;
        }

        $data = $sheet->toArray(null, true, true, false);
        if (count($data) < 2) {
            $this->error("File terlalu pendek");
            return 1;
        }

        // Baris 1 = header
        $headers = array_map('trim', $data[0]);

        $rows = [];
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') continue;
                $value = $row[$index] ?? null;
                $assoc[$header] = is_string($value) ? trim($value) : $value;
            }

            $rows[] = $assoc;
        }

        $this->info("Total baris data: " . count($rows));

        // Statistik
        $statusCount = [];
        foreach ($rows as $row) {
            $s = $row['Status Pesanan'] ?? 'Unknown';
            $statusCount[$s] = ($statusCount[$s] ?? 0) + 1;
        }

        $this->newLine();
        $this->info("=== Statistik Status ===");
        foreach ($statusCount as $status => $count) {
            $this->line(str_pad($status, 30) . " : {$count}");
        }

        // Simpan
        $outputDir = storage_path('app/parsed');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        $outputFile = $outputDir . '/shopee-order-all.json';
        file_put_contents(
            $outputFile,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->newLine();
        $this->info("Tersimpan di: {$outputFile}");

        return 0;
    }
}