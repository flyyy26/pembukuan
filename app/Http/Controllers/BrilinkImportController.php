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
use App\Exceptions\BrilinkImportException;
use App\Services\BrilinkFileValidator;
use App\Services\BrilinkRowValidator;

class BrilinkImportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d'],
        ]);

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Default: hari ini (jika user belum mengisi apa pun)
        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput ?? now()->format('Y-m-d');

        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $imports = Import::with('store')
            ->where('tipe', 'brilink')
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->only(['from', 'to'])); // agar paginasi bawa filter

        $stores = Store::where('marketplace', 'brilink')
            ->where('is_active', true)
            ->orderBy('nama_toko')
            ->get();

        return view('brilink.import', compact('imports', 'stores', 'fromInput', 'toInput'));
    }

    public function import(Request $request)
    {
        // ============================================
        // 1. VALIDASI INPUT
        // ============================================
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'file'     => 'required|file|mimes:xlsx,xls|max:20480',
            'tanggal'  => 'required|date_format:Y-m-d',
        ], [
            'store_id.required' => 'Silakan pilih toko terlebih dahulu.',
            'store_id.exists'   => 'Toko yang dipilih tidak ditemukan.',
            'file.required'     => 'Silakan pilih file Excel Buku BRILink.',
            'file.mimes'        => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max'          => 'Ukuran file maksimal 20 MB.',
            'tanggal.required'  => 'Silakan isi tanggal laporan.',
            'tanggal.date_format' => 'Format tanggal tidak valid (harus YYYY-MM-DD).',
        ]);

        $store = Store::findOrFail($request->store_id);

        // ============================================
        // 2. SIMPAN FILE
        // ============================================
        $uploaded = $request->file('file');
        $storedPath = $uploaded->store('imports/brilink', 'local');
        $fullPath = Storage::disk('local')->path($storedPath);
        $fileHash = hash_file('sha256', $fullPath);

        // ============================================
        // 3. CEK DUPLIKAT
        // ============================================
        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'brilink')
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($storedPath);

            return back()->with('warning',
                "File ini sudah pernah diimport sebelumnya (Import #{$existing->id}).\n" .
                "Jika ingin import ulang, hapus dulu data import lama di menu Riwayat."
            );
        }

        // ============================================
        // 4. LOAD SPREADSHEET
        // ============================================
        try {
            $spreadsheet = IOFactory::load($fullPath);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($storedPath);

            return back()->with('error',
                "File tidak bisa dibaca sebagai Excel.\n\n" .
                "Kemungkinan penyebab:\n" .
                "• File corrupt atau rusak\n" .
                "• File bukan format Excel asli (mis. CSV yang di-rename jadi .xlsx)\n" .
                "• File sedang dibuka di aplikasi lain\n\n" .
                "Solusi: coba buka file di Excel, lalu save ulang sebagai .xlsx, kemudian upload lagi."
            );
        }

        // ============================================
        // 5. VALIDASI STRUKTUR FILE
        // ============================================
        try {
            $validator = new BrilinkFileValidator();
            $validator->validate($spreadsheet);
        } catch (BrilinkImportException $e) {
            Storage::disk('local')->delete($storedPath);

            return back()->with('error', $e->toDisplayMessage());
        }

        // ============================================
        // 6. PARSE DATA
        // ============================================
        try {
            $parsed = $this->parseBrilinkFile($spreadsheet);
        } catch (BrilinkImportException $e) {
            Storage::disk('local')->delete($storedPath);

            return back()->with('error', $e->toDisplayMessage());
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($storedPath);

            report($e);

            return back()->with('error',
                "Gagal membaca data dari file Excel.\n\n" .
                "Kemungkinan penyebab:\n" .
                "• Ada kolom yang berisi teks padahal seharusnya angka\n" .
                "• Struktur kolom diubah dari format asli BRILink\n" .
                "• Ada rumus Excel yang error (#REF!, #VALUE!, dll)\n\n" .
                "Solusi:\n" .
                "1. Buka file di Excel, periksa sel yang error\n" .
                "2. Pastikan kolom nominal hanya berisi angka\n" .
                "3. Save ulang sebagai .xlsx lalu upload kembali"
            );
        }

        // ============================================
        // 7. VALIDASI BARIS DATA (sebelum simpan DB)
        // ============================================
        try {
            $rowValidator = new BrilinkRowValidator();
            $rowValidator->validateAll($parsed);
        } catch (BrilinkImportException $e) {
            Storage::disk('local')->delete($storedPath);

            return back()->with('error', $e->toDisplayMessage());
        }

        // ============================================
        // 8. CEK ADA DATA?
        // ============================================
        $totalData = count($parsed['accounts'])
            + count($parsed['ppob'])
            + count($parsed['setoran'])
            + count($parsed['tarik_tunai'])
            + count($parsed['pengeluaran']);

        if ($totalData === 0) {
            Storage::disk('local')->delete($storedPath);

            return back()->with('error',
                "File BRILink berhasil dibaca, tetapi tidak ada data transaksi yang ditemukan.\n\n" .
                "Kemungkinan penyebab:\n" .
                "• File Excel hanya berisi template kosong\n" .
                "• Semua sheet transaksi (PPOB, Setoran, Tarik Tunai, Pengeluaran) kosong\n" .
                "• Rentang tanggal tidak memiliki transaksi\n\n" .
                "Pastikan file yang diupload berisi data transaksi BRILink yang valid."
            );
        }

        // ============================================
        // 9. SIMPAN KE DATABASE
        // ============================================
        DB::beginTransaction();
        try {
            $tanggalLaporan = $request->input('tanggal');

            $import = Import::create([
                'store_id'      => $store->id,
                'tipe'          => 'brilink',
                'file_name'     => $uploaded->getClientOriginalName(),
                'file_hash'     => $fileHash,
                'periode_start' => $tanggalLaporan,
                'periode_end'   => $tanggalLaporan,
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
                    'periode_start' => $tanggalLaporan,
                    'periode_end'   => $tanggalLaporan,
                ]
            ));

            $report->accounts()->createMany($parsed['accounts']);
            $report->ppobItems()->createMany($parsed['ppob']);
            $report->setoranItems()->createMany($parsed['setoran']);
            $report->tarikTunaiItems()->createMany($parsed['tarik_tunai']);
            $report->pengeluaranItems()->createMany($parsed['pengeluaran']);

            $import->update(['status' => 'imported']);

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPath);

            report($e);

            $sqlState  = $e->errorInfo[0] ?? '';
            $driverMsg = $e->errorInfo[2] ?? $e->getMessage();

            // 22001 = string data too long
            if ($sqlState === '22001') {
                $kolom = 'tidak diketahui';
                $sheet = 'tidak diketahui';

                if (preg_match("/Data too long for column '([^']+)'/", $driverMsg, $m)) {
                    $kolom = $m[1];
                    $sheet = $this->guessSheetFromColumn($kolom);
                }

                return back()->with('error',
                    "Ada data yang terlalu panjang di kolom **{$kolom}** pada sheet **{$sheet}**.\n\n" .
                    "Solusi: perpendek teks di kolom tersebut (maksimal 255 karakter), lalu upload ulang."
                );
            }

            // 22003 = numeric value out of range
            if ($sqlState === '22003') {
                return back()->with('error',
                    "Ada angka yang terlalu besar atau tidak valid di file Excel.\n\n" .
                    "Solusi: periksa kembali semua kolom nominal, pastikan tidak ada angka melebihi batas wajar."
                );
            }

            // 23000 = integrity constraint violation (NOT NULL, FK, etc)
            if ($sqlState === '23000') {
                return back()->with('error',
                    "Ada data yang wajib diisi tetapi kosong.\n\n" .
                    "Kemungkinan penyebab:\n" .
                    "• Ada kolom yang seharusnya berisi angka tapi kosong\n" .
                    "• Ada baris transaksi tanpa kategori atau keterangan\n\n" .
                    "Solusi: periksa file Excel, pastikan semua baris transaksi punya data lengkap."
                );
            }

            return back()->with('error',
                "Data berhasil dibaca, tetapi gagal disimpan ke database.\n\n" .
                "Silakan coba lagi. Jika error berlanjut, hubungi admin.\n" .
                "Kode error: " . ($sqlState ?: 'unknown')
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPath);

            report($e);

            return back()->with('error',
                "Terjadi kesalahan tak terduga saat menyimpan data.\n\n" .
                "Silakan coba lagi. Jika error berlanjut, hubungi admin."
            );
        }

        // ============================================
        // 10. SUKSES
        // ============================================
        return redirect()->route('brilink.import')
            ->with('success',
                "Import BRILink berhasil!\n" .
                "• Total baris: {$parsed['total_rows']}\n" .
                "• Rekening: " . count($parsed['accounts']) . "\n" .
                "• PPOB: " . count($parsed['ppob']) . "\n" .
                "• Setoran: " . count($parsed['setoran']) . "\n" .
                "• Tarik Tunai: " . count($parsed['tarik_tunai']) . "\n" .
                "• Pengeluaran: " . count($parsed['pengeluaran'])
            );
    }

    /**
     * Parse 6 sheet dari file BRILink.
     */
    private function parseBrilinkFile($spreadsheet): array
    {
        $sheetDashboard   = $this->findSheet($spreadsheet, 'DASHBOARD UTAMA');
        $sheetPpob        = $this->findSheet($spreadsheet, 'TRANSAKSI KASIR PPOB');
        $sheetSetoran     = $this->findSheet($spreadsheet, 'TRANSAKSI SETORAN BRILINK');
        $sheetTarik       = $this->findSheet($spreadsheet, 'TRANSAKSI TARIK TUNAI');
        $sheetPengeluaran = $this->findSheet($spreadsheet, 'REKAP PENGELUARAN');

        if (!$sheetDashboard) {
            throw new BrilinkImportException(
                'Sheet "DASHBOARD UTAMA" tidak ditemukan.',
                ['Pastikan file adalah Buku BRILink asli dari BRI.']
            );
        }

        $report      = $this->parseDashboard($sheetDashboard);
        $accounts    = $this->parseAccounts($sheetDashboard);
        $ppob        = $sheetPpob        ? $this->parsePpob($sheetPpob)              : [];
        $setoran     = $sheetSetoran     ? $this->parseSetoran($sheetSetoran)        : [];
        $tarikTunai  = $sheetTarik       ? $this->parseTarikTunai($sheetTarik)       : [];
        $pengeluaran = $sheetPengeluaran ? $this->parsePengeluaran($sheetPengeluaran) : [];

        $totalRows = count($accounts) + count($ppob) + count($setoran)
                + count($tarikTunai) + count($pengeluaran);

        return [
            'report'        => $report,
            'accounts'      => $accounts,
            'ppob'          => $ppob,
            'setoran'       => $setoran,
            'tarik_tunai'   => $tarikTunai,
            'pengeluaran'   => $pengeluaran,
            'total_rows'    => $totalRows,
            'periode_start' => $report['periode_start'] ?? null,
            'periode_end'   => $report['periode_end']   ?? null,
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

    private function parseDashboard($sheet): array
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

            'total_pengeluaran_riil'     => 0,
            'total_pindah_saldo'         => 0,
            
            'volume_transaksi_agen'      => $safeInt('B19'),
            'saldo_awal_total'           => $safeInt('B45'),
            'saldo_akhir_total'          => $safeInt('B69'),
            'manual_validasi_kas_uang'   => $safeInt('B24'),
            
            'laba_kasir_pintar'          => $safeInt('B73'),
            'penambahan_brangkas'        => $safeInt('B74'),
        ];
    }

    private function parseAccounts($sheet): array
    {
        $accounts = [];
        $awalMap = [];

        // ✅ SALDO AWAL: Dari baris 32 sampai 48 (sebelumnya 27-47)
        for ($r = 32; $r <= 48; $r++) {
            $nama = $sheet->getCell("A{$r}")->getValue();
            if (!$nama) continue;

            $namaStr = strtoupper(trim((string)$nama));
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

        // ✅ SALDO AKHIR: Dari baris 55 sampai 71 (sebelumnya 51-72)
        for ($r = 55; $r <= 71; $r++) {
            $nama = $sheet->getCell("A{$r}")->getValue();
            if (!$nama) continue;

            $namaStr = strtoupper(trim((string)$nama));
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
        $maxRow = $sheet->getHighestRow();

        for ($r = 4; $r <= $maxRow; $r++) {
            $keteranganTitipJual = trim((string) $sheet->getCell("D{$r}")->getValue());
            $keteranganPenambahanKas = trim((string) $sheet->getCell("I{$r}")->getValue());

            $mapping = [
                'A' => 'Pulsa & Deposit',
                'B' => 'Barang Kasir Pintar',
                'C' => 'Barang Titip Jual',
                'E' => 'Fastpay',
                'F' => 'Payfazz',
                'G' => 'Buku Warung',
                'H' => 'Penambahan Kas',
                'J' => 'Lain-lain'
            ];

            foreach ($mapping as $kolom => $kategoriName) {
                $nominal = $this->cellValue($sheet, "{$kolom}{$r}");

                if ($nominal > 0) {
                    $keterangan = "Transaksi baris {$r}";

                    if ($kategoriName === 'Barang Titip Jual' && $keteranganTitipJual !== '') {
                        $keterangan = $keteranganTitipJual;
                    } elseif ($kategoriName === 'Penambahan Kas' && $keteranganPenambahanKas !== '') {
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
            $ket      = $sheet->getCell("C{$r}")->getValue();

            if (!$nominal && !$admin) continue;

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
            $edcKetMesin= $sheet->getCell("C{$r}")->getValue();
            $edcKmc     = $this->cellValue($sheet, "D{$r}");
            $saldoMasuk = $this->cellValue($sheet, "E{$r}");
            $mbNominal  = $this->cellValue($sheet, "F{$r}");
            $mbAdmin    = $this->cellValue($sheet, "G{$r}");
            $mbBank     = $sheet->getCell("H{$r}")->getValue();
            $keterangan = $sheet->getCell("I{$r}")->getValue();

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

            if ($mbNominal || $mbAdmin) {
                $rows[] = [
                    'tipe'           => 'mbanking',
                    'nominal'        => (int)($mbNominal ?: 0),
                    'admin_toko'     => (int)($mbAdmin ?: 0),
                    'ket_mesin_edc'  => null,
                    'kmc'            => 0,
                    'saldo_masuk'    => (int)($saldoMasuk ?: 0),
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

        for ($r = 4; $r <= $maxRow; $r++) {
            $ket     = $sheet->getCell("A{$r}")->getValue();
            $nominal = $this->cellValue($sheet, "B{$r}");
            $kategori= $sheet->getCell("C{$r}")->getValue();

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

    /**
     * Tebak sheet dari nama kolom (untuk pesan error yang informatif).
     */
    private function guessSheetFromColumn(string $column): string
    {
        $map = [
            'kategori'         => 'TRANSAKSI KASIR PPOB',
            'nominal'          => 'TRANSAKSI KASIR PPOB / REKAP PENGELUARAN / TARIK TUNAI',
            'keterangan'       => 'PPOB / SETORAN / TARIK TUNAI / PENGELUARAN',
            'nama_rekening'    => 'DASHBOARD UTAMA (Rekapitulasi Saldo)',
            'saldo_awal'       => 'DASHBOARD UTAMA (Rekapitulasi Saldo)',
            'saldo_akhir'      => 'DASHBOARD UTAMA (Rekapitulasi Saldo)',
            'nominal_edc'      => 'TRANSAKSI SETORAN BRILINK',
            'administrasi_toko'=> 'TRANSAKSI SETORAN BRILINK / TARIK TUNAI',
            'tipe'             => 'TRANSAKSI TARIK TUNAI',
            'admin_toko'       => 'TRANSAKSI TARIK TUNAI / SETORAN',
            'ket_mesin_edc'    => 'TRANSAKSI TARIK TUNAI / SETORAN',
            'keterangan_rek'   => 'TRANSAKSI TARIK TUNAI',
            'kmc'              => 'TRANSAKSI TARIK TUNAI',
            'saldo_masuk'      => 'TRANSAKSI TARIK TUNAI',
            'nominal_kas'      => 'REKAP PENGELUARAN',
            'total_omset_ppob' => 'DASHBOARD UTAMA',
            'periode_start'    => 'DASHBOARD UTAMA / Import',
            'periode_end'      => 'DASHBOARD UTAMA / Import',
        ];

        return $map[$column] ?? 'tidak diketahui';
    }

        /**
     * Hapus data Import BRILink beserta seluruh transaksinya.
     */
    public function destroy($id)
    {
        $import = Import::where('tipe', 'brilink')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Cari report yang terhubung dengan import ini
            $report = BrilinkReport::where('import_id', $import->id)->first();

            if ($report) {
                // Hapus manual relasi turunan untuk memastikan bersih (jika tidak pakai cascade DB)
                $report->accounts()->delete();
                $report->ppobItems()->delete();
                $report->setoranItems()->delete();
                $report->tarikTunaiItems()->delete();
                $report->pengeluaranItems()->delete();
                
                $report->delete();
            }

            $import->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data import dan seluruh transaksinya berhasil dihapus.'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }
}