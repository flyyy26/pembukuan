<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TiktokParseOrder extends Command
{
    protected $signature = 'tiktok:parse-order {file}';
    protected $description = 'Parse TikTok Order Report ke JSON';

    public function handle()
    {
        $fullPath = base_path($this->argument('file'));

        if (!file_exists($fullPath)) {
            $this->error("File tidak ditemukan: {$fullPath}");
            return 1;
        }

        $this->info("Membaca: {$fullPath}");

        $spreadsheet = IOFactory::load($fullPath);

        // Cari sheet OrderSKUList
        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            if ($s->getTitle() === 'OrderSKUList') {
                $sheet = $s;
                break;
            }
        }

        if (!$sheet) {
            $this->error("Sheet 'OrderSKUList' tidak ditemukan");
            return 1;
        }

        // Baca semua baris sebagai array (indexed 0-based)
        $data = $sheet->toArray(null, true, true, false);

        // Baris 0 = header
        $headers = array_map('trim', $data[0]);

        // Baris 1 = deskripsi, skip
        // Baris 2+ = data
        $rows = [];
        for ($i = 2; $i < count($data); $i++) {
            $row = $data[$i];

            // Skip baris yang benar-benar kosong
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            // Gabungkan header + value
            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') continue; // skip kolom tanpa nama
                $value = $row[$index] ?? null;
                $assoc[$header] = is_string($value) ? trim($value) : $value;
            }

            $rows[] = $assoc;
        }

        $this->info("Total baris data: " . count($rows));
        $this->newLine();
        $this->info("Contoh baris pertama:");
        $this->line(json_encode($rows[0] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Simpan ke JSON
        $outputDir = storage_path('app/parsed');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputFile = $outputDir . '/tiktok-order.json';
        file_put_contents(
            $outputFile,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->newLine();
        $this->info("Tersimpan di: {$outputFile}");

        return 0;
    }
}