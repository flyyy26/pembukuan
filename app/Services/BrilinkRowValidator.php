<?php

namespace App\Services;

use App\Exceptions\BrilinkImportException;

class BrilinkRowValidator
{
    /**
     * Validasi semua baris hasil parse sebelum disimpan ke DB.
     * Melempar BrilinkImportException dengan lokasi spesifik jika ada yang salah.
     */
    public function validateAll(array $parsed): void
    {
        $this->validateAccounts($parsed['accounts'] ?? []);
        $this->validatePpob($parsed['ppob'] ?? []);
        $this->validateSetoran($parsed['setoran'] ?? []);
        $this->validateTarikTunai($parsed['tarik_tunai'] ?? []);
        $this->validatePengeluaran($parsed['pengeluaran'] ?? []);
    }

    private function validateAccounts(array $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 1;

            if (empty($row['nama_rekening'])) {
                throw new BrilinkImportException(
                    "Data rekening tidak lengkap di sheet DASHBOARD UTAMA.",
                    ["Baris ke-{$line}: nama rekening kosong."]
                );
            }

            if (mb_strlen($row['nama_rekening']) > 255) {
                throw new BrilinkImportException(
                    "Nama rekening terlalu panjang di sheet DASHBOARD UTAMA.",
                    ["Baris ke-{$line}: '{$row['nama_rekening']}' melebihi 255 karakter."]
                );
            }

            if (!is_int($row['saldo_awal']) || !is_int($row['saldo_akhir'])) {
                throw new BrilinkImportException(
                    "Saldo rekening tidak valid di sheet DASHBOARD UTAMA.",
                    ["Baris ke-{$line} ({$row['nama_rekening']}): saldo harus angka."]
                );
            }
        }
    }

    private function validatePpob(array $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 1;

            if (!is_int($row['nominal']) || $row['nominal'] <= 0) {
                throw new BrilinkImportException(
                    "Ada data PPOB yang tidak valid di sheet TRANSAKSI KASIR PPOB.",
                    [
                        "Baris data ke-{$line}: kategori '{$row['kategori']}' nominal tidak valid.",
                        "Nominal harus berupa angka lebih dari 0.",
                    ]
                );
            }

            if (empty($row['kategori'])) {
                throw new BrilinkImportException(
                    "Kategori PPOB kosong di sheet TRANSAKSI KASIR PPOB.",
                    ["Baris data ke-{$line}: kategori tidak boleh kosong."]
                );
            }

            if (mb_strlen($row['kategori']) > 100) {
                throw new BrilinkImportException(
                    "Kategori PPOB terlalu panjang di sheet TRANSAKSI KASIR PPOB.",
                    ["Baris data ke-{$line}: kategori melebihi 100 karakter."]
                );
            }

            if (isset($row['keterangan']) && mb_strlen($row['keterangan']) > 500) {
                throw new BrilinkImportException(
                    "Keterangan PPOB terlalu panjang di sheet TRANSAKSI KASIR PPOB.",
                    ["Baris data ke-{$line}: keterangan melebihi 500 karakter."]
                );
            }
        }
    }

    private function validateSetoran(array $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 1;

            if (!is_int($row['nominal_edc']) || !is_int($row['administrasi_toko'])) {
                throw new BrilinkImportException(
                    "Ada data setoran yang tidak valid di sheet TRANSAKSI SETORAN BRILINK.",
                    ["Baris data ke-{$line}: nominal/admin harus angka."]
                );
            }

            if ($row['nominal_edc'] < 0 || $row['administrasi_toko'] < 0) {
                throw new BrilinkImportException(
                    "Nominal setoran negatif di sheet TRANSAKSI SETORAN BRILINK.",
                    ["Baris data ke-{$line}: nominal tidak boleh negatif."]
                );
            }

            if (isset($row['keterangan']) && mb_strlen($row['keterangan']) > 255) {
                throw new BrilinkImportException(
                    "Keterangan setoran terlalu panjang di sheet TRANSAKSI SETORAN BRILINK.",
                    ["Baris data ke-{$line}: keterangan melebihi 255 karakter."]
                );
            }
        }
    }

    private function validateTarikTunai(array $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 1;

            if (!in_array($row['tipe'] ?? '', ['edc_bri', 'mbanking'])) {
                throw new BrilinkImportException(
                    "Tipe tarik tunai tidak valid di sheet TRANSAKSI TARIK TUNAI.",
                    ["Baris data ke-{$line}: tipe harus 'edc_bri' atau 'mbanking'."]
                );
            }

            foreach (['nominal', 'admin_toko', 'kmc', 'saldo_masuk'] as $field) {
                if (!is_int($row[$field] ?? null)) {
                    throw new BrilinkImportException(
                        "Nilai {$field} tidak valid di sheet TRANSAKSI TARIK TUNAI.",
                        ["Baris data ke-{$line}: harus angka."]
                    );
                }
            }

            if (isset($row['ket_mesin_edc']) && mb_strlen($row['ket_mesin_edc']) > 255) {
                throw new BrilinkImportException(
                    "Keterangan mesin EDC terlalu panjang di sheet TRANSAKSI TARIK TUNAI.",
                    ["Baris data ke-{$line}: melebihi 255 karakter."]
                );
            }

            if (isset($row['keterangan_rek']) && mb_strlen($row['keterangan_rek']) > 255) {
                throw new BrilinkImportException(
                    "Keterangan rekening terlalu panjang di sheet TRANSAKSI TARIK TUNAI.",
                    ["Baris data ke-{$line}: melebihi 255 karakter."]
                );
            }

            if (isset($row['keterangan']) && mb_strlen($row['keterangan']) > 500) {
                throw new BrilinkImportException(
                    "Keterangan terlalu panjang di sheet TRANSAKSI TARIK TUNAI.",
                    ["Baris data ke-{$line}: melebihi 500 karakter."]
                );
            }
        }
    }

    private function validatePengeluaran(array $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 1;

            if (empty($row['keterangan'])) {
                throw new BrilinkImportException(
                    "Keterangan pengeluaran kosong di sheet REKAP PENGELUARAN.",
                    ["Baris data ke-{$line}: keterangan tidak boleh kosong."]
                );
            }

            if (mb_strlen($row['keterangan']) > 255) {
                throw new BrilinkImportException(
                    "Keterangan pengeluaran terlalu panjang di sheet REKAP PENGELUARAN.",
                    ["Baris data ke-{$line}: melebihi 255 karakter."]
                );
            }

            if (!is_int($row['nominal_kas'])) {
                throw new BrilinkImportException(
                    "Nominal pengeluaran tidak valid di sheet REKAP PENGELUARAN.",
                    ["Baris data ke-{$line}: nominal harus angka."]
                );
            }

            if (mb_strlen($row['kategori'] ?? '') > 100) {
                throw new BrilinkImportException(
                    "Kategori pengeluaran terlalu panjang di sheet REKAP PENGELUARAN.",
                    ["Baris data ke-{$line}: kategori melebihi 100 karakter."]
                );
            }
        }
    }
}