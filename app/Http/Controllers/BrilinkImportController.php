<?php

namespace App\Http\Controllers;

use App\Models\BrilinkReport;
use App\Models\Import;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BrilinkImportController extends Controller
{
    public function index()
    {
        $imports = Import::with('store')
            ->where('tipe', 'brilink')
            ->orderByDesc('id')
            ->paginate(15);

        $stores = Store::where('marketplace', 'brilink')
            ->where('is_active', true)
            ->orderBy('nama_toko')
            ->get();

        return view('brilink.import', compact('imports', 'stores'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'file'     => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        $store = Store::findOrFail($request->store_id);

        $uploaded = $request->file('file');
        $storedPath = $uploaded->store('imports/brilink', 'local');
        $fullPath = Storage::disk('local')->path($storedPath);
        $fileHash = hash_file('sha256', $fullPath);

        // Cek duplikat
        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'brilink')
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('warning', "File ini sudah pernah diimport (import #{$existing->id}). Skip.");
        }

        try {
            $parsed = $this->parseBrilinkFile($fullPath);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal parse file BRILink: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $import = Import::create([
                'store_id'      => $store->id,
                'tipe'          => 'brilink',
                'file_name'     => $uploaded->getClientOriginalName(),
                'file_hash'     => $fileHash,
                'periode_start' => $parsed['periode_start'],
                'periode_end'   => $parsed['periode_end'],
                'total_rows'    => $parsed['total_rows'],
                'status'        => 'pending',
            ]);

            $report = BrilinkReport::create(array_merge(
                $parsed['report'],
                [
                    'store_id'      => $store->id,
                    'import_id'     => $import->id,
                    'file_name'     => $uploaded->getClientOriginalName(),
                    'file_hash'     => $fileHash,
                    'status'        => 'imported',
                    'periode_start' => $parsed['periode_start'] ?? now()->format('Y-m-d'),
                    'periode_end'   => $parsed['periode_end']   ?? now()->format('Y-m-d'),
                ]
            ));

            $report->accounts()->createMany($parsed['accounts']);
            $report->ppobItems()->createMany($parsed['ppob']);
            $report->setoranItems()->createMany($parsed['setoran']);
            $report->tarikTunaiItems()->createMany($parsed['tarik_tunai']);
            $report->pengeluaranItems()->createMany($parsed['pengeluaran']);

            $import->update(['status' => 'imported']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal simpan BRILink ke DB: ' . $e->getMessage());
        }

        return redirect()->route('brilink.import')
            ->with('success', "Import BRILink berhasil. Report #{$report->id}, total {$parsed['total_rows']} baris log.");
    }

    /**
     * Parse 6 sheet dari file BRILink.
     */
    private function parseBrilinkFile(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);

        $sheetDashboard   = $this->findSheet($spreadsheet, 'DASHBOARD UTAMA');
        $sheetPpob        = $this->findSheet($spreadsheet, 'TRANSAKSI KASIR PPOB');
        $sheetSetoran     = $this->findSheet($spreadsheet, 'TRANSAKSI SETORAN BRILINK');
        $sheetTarik       = $this->findSheet($spreadsheet, 'TRANSAKSI TARIK TUNAI');
        $sheetPengeluaran = $this->findSheet($spreadsheet, 'REKAP PENGELUARAN');

        if (!$sheetDashboard) throw new \Exception("Sheet 'DASHBOARD UTAMA' tidak ditemukan");

        $calc = new \PhpOffice\PhpSpreadsheet\Calculation\Calculation($spreadsheet);

        $report      = $this->parseDashboard($sheetDashboard, $spreadsheet, $calc);
        $accounts    = $this->parseAccounts($sheetDashboard);
        $ppob        = $sheetPpob      ? $this->parsePpob($sheetPpob)            : [];
        $setoran     = $sheetSetoran   ? $this->parseSetoran($sheetSetoran)      : [];
        $tarikTunai  = $sheetTarik     ? $this->parseTarikTunai($sheetTarik)     : [];
        
        // 🔥 Baca data riil dari Sheet REKAP PENGELUARAN
        $pengeluaran = $sheetPengeluaran ? $this->parsePengeluaran($sheetPengeluaran) : [];

        $totalRows = count($accounts) + count($ppob) + count($setoran) + count($tarikTunai) + count($pengeluaran);

        $periodeStart = $report['periode_start'] ?? null;
        $periodeEnd   = $report['periode_end']   ?? null;

        return [
            'report'      => $report,
            'accounts'    => $accounts,
            'ppob'        => $ppob,
            'setoran'     => $setoran,
            'tarik_tunai' => $tarikTunai,
            'pengeluaran' => $pengeluaran,
            'total_rows'  => $totalRows,
            'periode_start' => $periodeStart,
            'periode_end'   => $periodeEnd,
        ];
    }

    private function findSheet($spreadsheet, string $name)
    {
        foreach ($spreadsheet->getAllSheets() as $s) {
            if (strtoupper(trim($s->getTitle())) === strtoupper($name)) return $s;
        }
        return null;
    }

    private function cellValue($sheet, string $coord)
    {
        $cell = $sheet->getCell($coord);
        $val  = $cell->getCalculatedValue();
        return is_numeric($val) ? (int) round((float) $val) : $val;
    }

    private function parseDashboard($sheet, $spreadsheet, $calc): array
    {
        $safeInt = function($coord) use ($sheet) {
            $val = $this->cellValue($sheet, $coord);
            return (int) round((float) ($val ?: 0));
        };

        return [
            'total_omset_ppob'           => $safeInt('B5'),
            'total_tarik_tunai_edc'      => $safeInt('B6'),
            'total_tarik_tunai_mbanking' => $safeInt('B7'),
            'total_setoran_brilink'      => $safeInt('B8'),
            'total_pendapatan_admin'     => $safeInt('B9'),
            
            // Nilai-nilai ini akan dihitung ulang otomatis oleh Controller setelah import
            'total_pengeluaran_riil'     => 0, 
            'total_pindah_saldo'         => 0,
            'volume_transaksi_agen'      => $safeInt('B18'),
            'saldo_awal_total'           => $safeInt('B44'),
            'saldo_akhir_total'          => $safeInt('B68'),
            'manual_validasi_kas_uang'   => $safeInt('B24'), 
            
            // 🔥 PERBAIKAN: Ambil dari baris 72 dan 73 sesuai Excel
            'laba_kasir_pintar'          => $safeInt('B72'),
            'penambahan_brangkas'        => $safeInt('B73'),
        ];
    }

        private function parseAccounts($sheet): array
    {
        $accounts = [];
        $awalMap = [];

        for ($r = 27; $r <= 47; $r++) {
            $nama = $sheet->getCell("A{$r}")->getValue();
            if (!$nama) continue;
            
            $namaStr = strtoupper(trim((string)$nama));
            // 🔥 TAMBAHKAN 'LABA RUGI' DAN 'PENAMBAHAN BRANGKAS' DI SINI
            if (
                str_contains($namaStr, 'TOTAL') || 
                str_contains($namaStr, 'NAMA REKENING') || 
                str_contains($namaStr, 'REKAPITULASI') ||
                str_contains($namaStr, 'LABA RUGI') ||
                str_contains($namaStr, 'PENAMBAHAN BRANGKAS')
            ) continue;

            $awalMap[(string) $nama] = [
                'nama_rekening' => (string) $nama,
                'keterangan'    => (string) $sheet->getCell("C{$r}")->getValue(),
                'saldo_awal'    => (int) round((float) ($this->cellValue($sheet, "B{$r}") ?: 0)),
            ];
        }

        for ($r = 51; $r <= 72; $r++) {
            $nama = $sheet->getCell("A{$r}")->getValue();
            if (!$nama) continue;
            
            $namaStr = strtoupper(trim((string)$nama));
            // 🔥 TERAPKAN FILTER YANG SAMA UNTUK SALDO AKHIR
            if (
                str_contains($namaStr, 'TOTAL') || 
                str_contains($namaStr, 'NAMA REKENING') || 
                str_contains($namaStr, 'REKAPITULASI') ||
                str_contains($namaStr, 'LABA RUGI') ||
                str_contains($namaStr, 'PENAMBAHAN BRANGKAS')
            ) continue;

            $key = (string) $nama;
            $akhir = (int) round((float) ($this->cellValue($sheet, "B{$r}") ?: 0));

            if (isset($awalMap[$key])) {
                $awalMap[$key]['saldo_akhir'] = $akhir;
                $awalMap[$key]['selisih']     = $akhir - $awalMap[$key]['saldo_awal'];
            } else {
                $awalMap[$key] = [
                    'nama_rekening' => $key,
                    'keterangan'    => (string) $sheet->getCell("C{$r}")->getValue(),
                    'saldo_awal'    => 0,
                    'saldo_akhir'   => $akhir,
                    'selisih'       => $akhir,
                ];
            }
        }

        foreach ($awalMap as $a) {
            $accounts[] = [
                'nama_rekening' => $a['nama_rekening'],
                'keterangan'    => $a['keterangan'] ?? null,
                'saldo_awal'    => $a['saldo_awal'] ?? 0,
                'saldo_akhir'   => $a['saldo_akhir'] ?? 0,
                'selisih'       => ($a['saldo_akhir'] ?? 0) - ($a['saldo_awal'] ?? 0),
            ];
        }

        return $accounts;
    }

    private function parsePpob($sheet): array
    {
        $rows = [];
        
        // Ambil baris tertinggi
        $maxRow = $sheet->getHighestRow();
        
        // Looping mulai baris 4 (sesuai Excel)
        for ($r = 4; $r <= $maxRow; $r++) {
            // Baca keterangan umum di kolom D (untuk Barang Titip Jual)
            $keteranganTitipJual = trim((string) $sheet->getCell("D{$r}")->getValue());
            
            // Baca keterangan khusus untuk Penambahan Kas di kolom I
            $keteranganPenambahanKas = trim((string) $sheet->getCell("I{$r}")->getValue());

            // Mapping Manual Kolom -> Kategori
            // PERBAIKAN: Kolom I adalah Keterangan, Lain-lain dipindah ke Kolom J
            $mapping = [
                'A' => 'Pulsa & Deposit',
                'B' => 'Barang Kasir Pintar',
                'C' => 'Barang Titip Jual',
                'E' => 'Fastpay',
                'F' => 'Payfazz',
                'G' => 'Buku Warung',
                'H' => 'Penambahan Kas',
                'J' => 'Lain-lain' // 🔥 Pindah dari I ke J
            ];

            foreach ($mapping as $kolom => $kategoriName) {
                // Ambil nilai nominal
                $nominal = $this->cellValue($sheet, "{$kolom}{$r}");
                
                // Jika nominal lebih dari 0, simpan
                if ($nominal > 0) {
                    // Default keterangan
                    $keterangan = "Transaksi baris {$r}";
                    
                    // Logika Keterangan
                    if ($kategoriName === 'Barang Titip Jual' && $keteranganTitipJual !== '') {
                        $keterangan = $keteranganTitipJual;
                    } elseif ($kategoriName === 'Penambahan Kas' && $keteranganPenambahanKas !== '') {
                        // Simpan "BRANGKAS" atau "BRILINK ATUNG" ke kolom keterangan
                        $keterangan = $keteranganPenambahanKas;
                    }

                    $rows[] = [
                        'kategori'   => $kategoriName,
                        'nominal'    => $nominal,
                        'keterangan' => $keterangan
                    ];
                }
            }
        }

        return $rows;
    }

    private function parseSetoran($sheet): array
    {
        $rows = [];
        $maxRow = $sheet->getHighestRow();
        for ($r = 4; $r <= $maxRow; $r++) {
            $nominal  = $this->cellValue($sheet, "A{$r}");
            $admin    = $this->cellValue($sheet, "B{$r}");
            $ket      = $sheet->getCell("C{$r}")->getValue(); // Kolom Keterangan

            if (!$nominal && !$admin) continue;
            
            // Abaikan baris rekap/total
            if (stripos((string)$ket, 'VALIDASI') !== false || stripos((string)$ket, 'SELISIH') !== false) continue;
            if (stripos((string)$nominal, 'TOTAL') !== false) continue;

            $rows[] = [
                'nominal_edc'          => (int)($nominal ?: 0),
                'administrasi_toko'    => (int)($admin ?: 0),
                'jumlah_setoran_riil'  => (int)($nominal ?: 0),
                'keterangan'           => $ket ? (string)$ket : null,
            ];
        }
        return $rows;
    }

    private function parseTarikTunai($sheet): array
    {
        $rows = [];
        $maxRow = $sheet->getHighestRow();
        for ($r = 4; $r <= $maxRow; $r++) {
            $edcNominal = $this->cellValue($sheet, "A{$r}");
            $edcAdmin   = $this->cellValue($sheet, "B{$r}");
            $edcKetMesin= $sheet->getCell("C{$r}")->getValue(); // 🔥 KET. MESIN EDC
            $edcKmc     = $this->cellValue($sheet, "D{$r}");    // 🔥 KMC pindah ke D
            $saldoMasuk = $this->cellValue($sheet, "E{$r}");    // 🔥 Saldo Masuk pindah ke E
            $mbNominal  = $this->cellValue($sheet, "F{$r}");    // 🔥 MBanking pindah ke F
            $mbAdmin    = $this->cellValue($sheet, "G{$r}");    // 🔥 Admin MB pindah ke G
            $mbBank     = $sheet->getCell("H{$r}")->getValue(); // 🔥 Rekening Bank
            $keterangan = $sheet->getCell("I{$r}")->getValue(); // 🔥 Keterangan Umum

            // Simpan EDC jika ada nominal atau admin
            if ($edcNominal || $edcAdmin) {
                $rows[] = [
                    'tipe'           => 'edc_bri',
                    'nominal'        => (int)($edcNominal ?: 0),
                    'admin_toko'     => (int)($edcAdmin ?: 0),
                    'ket_mesin_edc'  => $edcKetMesin ? (string)$edcKetMesin : null,
                    'kmc'            => (int)($edcKmc ?: 0),
                    'saldo_masuk'    => (int)($saldoMasuk ?: 0),
                    'keterangan_rek' => null,
                    'keterangan'     => $keterangan ? (string)$keterangan : null,
                ];
            }
            
            // Simpan MBanking jika ada nominal atau admin
            if ($mbNominal || $mbAdmin) {
                $rows[] = [
                    'tipe'           => 'mbanking',
                    'nominal'        => (int)($mbNominal ?: 0),
                    'admin_toko'     => (int)($mbAdmin ?: 0),
                    'ket_mesin_edc'  => null,
                    'kmc'            => 0,
                    'saldo_masuk'    => (int)($saldoMasuk ?: 0), // Asumsi saldo masuk hanya 1x per baris, bisa dipindah ke EDC
                    'keterangan_rek' => $mbBank ? (string)$mbBank : null,
                    'keterangan'     => $keterangan ? (string)$keterangan : null,
                ];
            }
        }
        return $rows;
    }

    private function parsePengeluaran($sheet): array
    {
        $rows = [];
        $maxRow = $sheet->getHighestRow();
        
        // Data dimulai dari baris 4 sesuai Excel
        for ($r = 4; $r <= $maxRow; $r++) {
            $ket     = $sheet->getCell("A{$r}")->getValue(); // Keterangan ada di Kolom A
            $nominal = $this->cellValue($sheet, "B{$r}");    // Nominal ada di Kolom B
            $kategori= $sheet->getCell("C{$r}")->getValue(); // Kategori ada di Kolom C

            // Abaikan baris kosong atau baris total
            if (!$ket && !$nominal) continue;
            if (stripos((string)$ket, 'TOTAL') !== false) continue;

            $rows[] = [
                'keterangan'  => (string) $ket,
                'nominal_kas' => (int)($nominal ?: 0),
                'kategori'    => $kategori ? (string)$kategori : 'TANPA KATEGORI',
            ];
        }
        return $rows;
    }
}