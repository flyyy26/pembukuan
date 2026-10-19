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
                    
                    // TAMBAHKAN BARIS INI:
                    // Jika file Excel tidak punya tanggal, gunakan tanggal hari ini
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

        $sheetDashboard = $this->findSheet($spreadsheet, 'DASHBOARD UTAMA');
        $sheetPpob      = $this->findSheet($spreadsheet, 'TRANSAKSI KASIR PPOB');
        $sheetSetoran   = $this->findSheet($spreadsheet, 'TRANSAKSI SETORAN BRILINK');
        $sheetTarik     = $this->findSheet($spreadsheet, 'TRANSAKSI TARIK TUNAI');
        $sheetPengeluaran = $this->findSheet($spreadsheet, 'REKAP PENGELUARAN');

        if (!$sheetDashboard) throw new \Exception("Sheet 'DASHBOARD UTAMA' tidak ditemukan");

        // Nilai formula bisa cached — gunakan calculated value
        $calc = new \PhpOffice\PhpSpreadsheet\Calculation\Calculation($spreadsheet);

        $report      = $this->parseDashboard($sheetDashboard, $spreadsheet, $calc);
        $accounts    = $this->parseAccounts($sheetDashboard);
        $ppob        = $sheetPpob      ? $this->parsePpob($sheetPpob)            : [];
        $setoran     = $sheetSetoran   ? $this->parseSetoran($sheetSetoran)      : [];
        $tarikTunai  = $sheetTarik     ? $this->parseTarikTunai($sheetTarik)     : [];
        $pengeluaran = $sheetPengeluaran ? $this->parsePengeluaran($sheetPengeluaran) : [];

        $totalRows = count($accounts) + count($ppob) + count($setoran) + count($tarikTunai) + count($pengeluaran);

        // Deteksi periode: kalau ada tanggal di log, pakai; kalau tidak, null
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

    /**
     * Ambil nilai cached (formula hasil hitung) supaya tidak perlu re-calc.
     */
    private function cellValue($sheet, string $coord)
    {
        $cell = $sheet->getCell($coord);
        $val  = $cell->getCalculatedValue(); // gunakan cached if ada
        return is_numeric($val) ? (int) round((float) $val) : $val;
    }

    private function parseDashboard($sheet, $spreadsheet, $calc): array
    {
        // Helper untuk memastikan hasilnya selalu integer, tidak pernah null
        $safeInt = function($coord) use ($sheet) {
            $val = $this->cellValue($sheet, $coord);
            return (int) round((float) ($val ?: 0));
        };

        // Jika posisi sel bergeser, coba fallback mencari string "Total Omset Kasir PPOB" secara manual
        // (Opsional, tapi sangat membantu jika template Excel berubah)
        
        return [
            'total_omset_ppob'           => $safeInt('B5'),
            'total_tarik_tunai_edc'      => $safeInt('B6'),
            'total_tarik_tunai_mbanking' => $safeInt('B7'),
            'total_setoran_brilink'      => $safeInt('B8'),
            'total_pendapatan_admin'     => $safeInt('B9'),
            'total_pengeluaran_riil'     => $safeInt('B10'),
            'total_pindah_saldo'         => $safeInt('B11'),
            'volume_transaksi_agen'      => (int) $this->cellValue($sheet, 'B12'),
            'saldo_awal_total'           => $safeInt('B44'),
            'saldo_akhir_total'          => $safeInt('B68'),
            'validasi_kas_uang'          => $safeInt('D18'), 
            'laba_kasir_pintar' => $safeInt('B72'),
            'penambahan_brangkas' => $safeInt('B73'),
        ];
    }

    /**
     * Saldo awal & saldo akhir ada di blok 24-43 dan 48-67.
     */
        private function parseAccounts($sheet): array
    {
        $accounts = [];
        $awalMap = [];

        // Saldo AWAL: baris 24–44
        for ($r = 24; $r <= 44; $r++) {
            $nama = $sheet->getCell("A{$r}")->getValue();
            if (!$nama) continue;
            
            // Abaikan baris header atau total
            $namaStr = strtoupper(trim((string)$nama));
            if (str_contains($namaStr, 'TOTAL') || str_contains($namaStr, 'NAMA REKENING')) continue;

            $awalMap[(string) $nama] = [
                'nama_rekening' => (string) $nama,
                'keterangan'    => (string) $sheet->getCell("C{$r}")->getValue(),
                'saldo_awal'    => (int) round((float) ($this->cellValue($sheet, "B{$r}") ?: 0)),
            ];
        }

        // Saldo AKHIR: baris 48–69
        for ($r = 48; $r <= 69; $r++) {
            $nama = $sheet->getCell("A{$r}")->getValue();
            if (!$nama) continue;
            
            // Abaikan baris header atau total
            $namaStr = strtoupper(trim((string)$nama));
            if (str_contains($namaStr, 'TOTAL') || str_contains($namaStr, 'NAMA REKENING')) continue;

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

    /**
     * Sheet "TRANSAKSI KASIR PPOB" — baris 4-9 = rekap per kategori, bukan log harian.
     * Kolom A=E pulsa, B=KP, C=Fastpay, D=BukuWarung, E=Payfazz, F=Penambahan Kas, G=Lain-lain
     */
    private function parsePpob($sheet): array
    {
        $rows = [];
        
        // Mapping Kolom ke Kategori
        $kolomMap = [
            'A' => 'Pulsa & Deposit',
            'B' => 'Barang Kasir Pintar',
            'C' => 'Fastpay',
            'D' => 'Payfazz',
            'E' => 'Buku Warung',
            'F' => 'Penambahan Kas',
            'G' => 'Lain-lain'
        ];

        // Looping baris dari baris ke-4 sampai batas maksimal data di Excel
        $maxRow = $sheet->getHighestRow();
        
        for ($r = 4; $r <= $maxRow; $r++) {
            foreach ($kolomMap as $kolom => $kategoriName) {
                $nominal = $this->cellValue($sheet, "{$kolom}{$r}");
                
                // Jika ada nominal lebih dari 0, simpan ke database per baris
                if ($nominal > 0) {
                    $rows[] = [
                        'kategori'   => $kategoriName,
                        'nominal'    => $nominal,
                        'keterangan' => "Transaksi baris {$r}"
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * Sheet "TRANSAKSI SETORAN BRILINK" — baris 4+ log, kolom A & B.
     * Nilai agregat: E3=jumlah setoran riil (SUM A:A), E4=validasi, E5=selisih, E12=jumlah nota, E13=total admin
     */
    private function parseSetoran($sheet): array
    {
        $rows = [];
        $maxRow = $sheet->getHighestRow();
        for ($r = 4; $r <= $maxRow; $r++) {
            $nominal = $this->cellValue($sheet, "A{$r}");
            $admin   = $this->cellValue($sheet, "B{$r}");
            if (!$nominal && !$admin) continue;
            $rows[] = [
                'nominal_edc'          => (int)($nominal ?: 0),
                'administrasi_toko'    => (int)($admin ?: 0),
                'jumlah_setoran_riil'  => (int)($nominal ?: 0),
            ];
        }
        return $rows;
    }

    /**
     * Sheet "TRANSAKSI TARIK TUNAI" — kolom A=EDC BRI, B=Admin, C=KMC, D=Saldo masuk, E=MBanking, F=Admin MB, G=Ket
     */
    private function parseTarikTunai($sheet): array
    {
        $rows = [];
        $maxRow = $sheet->getHighestRow();
        for ($r = 4; $r <= $maxRow; $r++) {
            $edcNominal = $this->cellValue($sheet, "A{$r}");
            $edcAdmin   = $this->cellValue($sheet, "B{$r}");
            $edcKmc     = $this->cellValue($sheet, "C{$r}");
            $mbNominal  = $this->cellValue($sheet, "E{$r}");
            $mbAdmin    = $this->cellValue($sheet, "F{$r}");
            $mbKet      = $sheet->getCell("G{$r}")->getValue();

            if ($edcNominal || $edcAdmin || $edcKmc) {
                $rows[] = [
                    'tipe'        => 'edc_bri',
                    'nominal'     => (int)($edcNominal ?: 0),
                    'admin_toko'  => (int)($edcAdmin   ?: 0),
                    'kmc'         => (int)($edcKmc     ?: 0),
                    'saldo_masuk' => 0,
                    'keterangan_rek' => null,
                ];
            }
            if ($mbNominal || $mbAdmin) {
                $rows[] = [
                    'tipe'        => 'mbanking',
                    'nominal'     => (int)($mbNominal ?: 0),
                    'admin_toko'  => (int)($mbAdmin   ?: 0),
                    'kmc'         => 0,
                    'saldo_masuk' => (int)($this->cellValue($sheet, "D{$r}") ?: 0),
                    'keterangan_rek' => $mbKet ? (string)$mbKet : null,
                ];
            }
        }
        return $rows;
    }

    /**
     * Sheet "REKAP PENGELUARAN" — kolom A=no, B=keterangan, C=nominal, D=kategori
     * Baris 4+ = data
     */
    private function parsePengeluaran($sheet): array
    {
        $rows = [];
        $maxRow = $sheet->getHighestRow();
        for ($r = 4; $r <= $maxRow; $r++) {
            $ket     = $sheet->getCell("B{$r}")->getValue();
            $nominal = $this->cellValue($sheet, "C{$r}");
            $kategori= $sheet->getCell("D{$r}")->getValue();

            if (!$ket && !$nominal) continue;
            if (stripos((string)$ket, 'TOTAL') !== false) continue;

            $rows[] = [
                'keterangan'  => (string) $ket,
                'nominal_kas' => (int)($nominal ?: 0),
                'kategori'    => $kategori ? (string)$kategori : null,
            ];
        }
        return $rows;
    }
}