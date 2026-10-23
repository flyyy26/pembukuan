<?php

namespace App\Services;

use App\Exceptions\BrilinkImportException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BrilinkFileValidator
{
    public const REQUIRED_SHEETS = [
        'DASHBOARD UTAMA',
        'TRANSAKSI KASIR PPOB',
        'TRANSAKSI SETORAN BRILINK',
        'TRANSAKSI TARIK TUNAI',
        'REKAP PENGELUARAN',
    ];

    /**
     * Validasi struktur file BRILink sebelum parsing.
     * Melempar BrilinkImportException dengan pesan user-friendly.
     */
    public function validate(Spreadsheet $spreadsheet): void
    {
        // 1. Minimal ada 1 sheet
        $allSheets = $spreadsheet->getAllSheets();
        if (count($allSheets) === 0) {
            throw new BrilinkImportException(
                'File Excel tidak memiliki sheet apa pun.',
                ['Pastikan file tidak corrupt dan bisa dibuka di Excel.']
            );
        }

        // 2. Cek sheet wajib
        $sheetNames = array_map(
            fn($s) => strtoupper(trim($s->getTitle())),
            $allSheets
        );

        $missing = [];
        foreach (self::REQUIRED_SHEETS as $required) {
            if (!in_array(strtoupper($required), $sheetNames, true)) {
                $missing[] = $required;
            }
        }

        if (!empty($missing)) {
            $found = implode(', ', $sheetNames);

            throw new BrilinkImportException(
                'Struktur file tidak sesuai format Buku BRILink.',
                [
                    'Sheet yang tidak ditemukan: ' . implode(', ', $missing),
                    'Sheet yang ada di file: ' . $found,
                    'Pastikan file yang diupload adalah file Excel Buku BRILink asli dari BRI.',
                    'Jangan upload file hasil edit yang menghapus/mengganti nama sheet.',
                ]
            );
        }

        // 3. Validasi sheet DASHBOARD UTAMA punya data minimal
        $dashboard = $this->findSheet($spreadsheet, 'DASHBOARD UTAMA');
        if ($dashboard) {
            $this->validateDashboard($dashboard);
        }
    }

    private function validateDashboard($sheet): void
    {
        // Cek apakah sel B5 (omset PPOB) bisa dibaca
        try {
            $cellB5 = $sheet->getCell('B5');
            $value = $cellB5->getValue();

            // Kalau kosong dan tidak ada data sama sekali
            if ($value === null) {
                // Cek apakah sheet benar-benar kosong
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                if ($highestRow < 5 || $highestCol < 'B') {
                    throw new BrilinkImportException(
                        'Sheet "DASHBOARD UTAMA" kosong atau tidak lengkap.',
                        [
                            'Sheet dashboard harus berisi data omset, saldo, dan rekening.',
                            'Pastikan file Excel belum diedit dan masih asli dari BRI.',
                        ]
                    );
                }
            }
        } catch (BrilinkImportException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new BrilinkImportException(
                'Gagal membaca sheet "DASHBOARD UTAMA".',
                [
                    'Sheet dashboard rusak atau format tidak dikenali.',
                    'Coba buka file di Excel, lalu save ulang sebagai .xlsx.',
                ],
                'Dashboard read error: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function findSheet(Spreadsheet $spreadsheet, string $name)
    {
        foreach ($spreadsheet->getAllSheets() as $s) {
            if (strtoupper(trim($s->getTitle())) === strtoupper($name)) {
                return $s;
            }
        }
        return null;
    }
}