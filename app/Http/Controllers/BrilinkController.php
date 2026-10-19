<?php

namespace App\Http\Controllers;

use App\Models\BrilinkAccount;
use App\Models\BrilinkPengeluaran;
use App\Models\BrilinkReport;
use App\Models\BrilinkPpobTransaction;
use App\Models\BrilinkSetoran;
use App\Models\BrilinkTarikTunai;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrilinkController extends Controller
{
    /**
     * Dashboard utama BRILink.
     * Menampilkan agregat lintas report untuk store + filter periode.
     */
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d'],
        ]);

        $storeId = $request->input('store'); // Bisa berisi ID toko atau 'all'
        
        // Simpan input asli untuk View (HTML)
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Nilai untuk Query Database (Default: Hari Ini)
        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

        // JIKA HANYA SALAH SATU YANG DIISI, JADIKAN FILTER 1 HARI
        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $stores = Store::where('marketplace', 'brilink')
            ->orderBy('nama_toko')
            ->get();

        // Jika belum pilih toko, default ke 'all'
        if (!$storeId) {
            $storeId = 'all';
        }
        
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // ================= QUERY REPORTS =================
        $reportQuery = BrilinkReport::with([
                'accounts',
                'ppobItems',
                'setoranItems',
                'tarikTunaiItems',
                'pengeluaranItems',
            ])
            // Hanya filter store_id jika bukan 'all'
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereRaw(
                'COALESCE(periode_end, periode_start, DATE(created_at)) >= ?', [$from]
            ))
            ->when($to, fn($q) => $q->whereRaw(
                'COALESCE(periode_start, DATE(created_at)) <= ?', [$to]
            ))
            ->orderByDesc('periode_start')
            ->orderByDesc('id');

        $reports = $reportQuery->get();

        $totalOmsetPpobDariDetail = $reports->flatMap->ppobItems->sum('nominal');

        // ================= SUMMARY (agregat awal) =================
        $summary = [
            'total_omset_ppob'         => (int) $totalOmsetPpobDariDetail,
            'total_tarik_edc'          => (int) $reports->sum('total_tarik_tunai_edc'),
            'total_tarik_mbanking'     => (int) $reports->sum('total_tarik_tunai_mbanking'),
            'total_setoran'            => (int) $reports->sum('total_setoran_brilink'),
            'total_pendapatan_admin'   => (int) $reports->sum('total_pendapatan_admin'),
            'total_pengeluaran_riil'   => (int) $reports->sum('total_pengeluaran_riil'),
            'total_pindah_saldo'       => (int) $reports->sum('total_pindah_saldo'),
            'total_volume_transaksi'   => (int) $reports->sum('volume_transaksi_agen'),
            'saldo_awal_total'         => (int) $reports->sum('saldo_awal_total'),
            'saldo_akhir_total'        => (int) $reports->sum('saldo_akhir_total'),
            'validasi_kas_uang'        => (int) $reports->sum('validasi_kas_uang'),
            'total_reports'            => $reports->count(),
        ];
        $summary['profit'] = $summary['total_pendapatan_admin'] - $summary['total_pengeluaran_riil'];
        $summary['selisih_saldo'] = $summary['saldo_akhir_total'] - $summary['saldo_awal_total'];

        // ================= BREAKDOWN PENDAPATAN ADMIN =================
        $setoranAdmin = $reports->flatMap->setoranItems->sum('administrasi_toko');
        $tarikEdcAdmin = $reports->flatMap->tarikTunaiItems->where('tipe', 'edc_bri')->sum('admin_toko');
        $tarikMbAdmin = $reports->flatMap->tarikTunaiItems->where('tipe', 'mbanking')->sum('admin_toko');
        $kmcTotal = $reports->flatMap->tarikTunaiItems->sum('kmc');

        $breakdownPendapatan = [
            'setoran_admin' => (int) $setoranAdmin,
            'tarik_edc'     => (int) $tarikEdcAdmin,
            'tarik_mb'      => (int) $tarikMbAdmin,
            'kmc'           => (int) $kmcTotal,
        ];

        // ================= BREAKDOWN PENGELUARAN =================
        $allPengeluaran = $reports->flatMap->pengeluaranItems;

        // Filter Pengeluaran Riil (Bukan Pindah Saldo Internal)
        $pengeluaranRiil = $allPengeluaran->filter(function ($p) {
            $kategori = strtolower(trim($p->kategori ?? ''));
            return $kategori !== 'pindah saldo internal';
        });

        $pengeluaranPindah = $allPengeluaran->filter(function ($p) {
            $kategori = strtolower(trim($p->kategori ?? ''));
            return $kategori === 'pindah saldo internal';
        });

        $totalPengeluaranRiil = $pengeluaranRiil->sum('nominal_kas');
        $totalPindahSaldo = $pengeluaranPindah->sum('nominal_kas');

        // Timpa nilai summary dengan hasil filter yang lebih akurat
        $summary['total_pengeluaran_riil'] = (int) $totalPengeluaranRiil;
        $summary['total_pindah_saldo']     = (int) $totalPindahSaldo;

        $summary['tarik_edc_admin']     = (int) $tarikEdcAdmin;
        $summary['tarik_mb_admin']     = (int) $tarikMbAdmin;
        $summary['setoran_admin']     = (int) $setoranAdmin;

        $breakdownPengeluaran = [
            'riil' => $pengeluaranRiil
                ->groupBy(fn($p) => $p->kategori ?: 'Tanpa Kategori')
                ->map(fn($g, $k) => [
                    'kategori' => $k,
                    'total'    => (int) $g->sum('nominal_kas'),
                    'jumlah'   => $g->count(),
                    'items'    => $g->map(fn($item) => [
                        'keterangan' => $item->keterangan,
                        'nominal'    => $item->nominal_kas
                    ])->values()->toArray(),
                ])
                ->sortByDesc('total')
                ->values()
                ->toArray(),
            'pindah_saldo' => $summary['total_pindah_saldo'],
            'pindah_count' => $pengeluaranPindah->count(),
        ];

        // ===================================================
        // HITUNG UANG MASUK & UANG KELUAR (SETELAH DATA FINAL)
        // ===================================================
        $summary['uang_masuk'] = $summary['total_omset_ppob']
                                + $summary['total_setoran']
                                + $summary['tarik_edc_admin']
                                + $summary['tarik_mb_admin']
                                + $summary['setoran_admin'];

        $summary['uang_keluar'] = $summary['total_tarik_edc'] 
                                + $summary['total_tarik_mbanking'] 
                                + $summary['total_pengeluaran_riil'] 
                                + $summary['total_pindah_saldo'];

        $summary['jml_penjualan'] = $summary['uang_masuk'] - $summary['uang_keluar'];

        // ================= BREAKDOWN PPOB =================
        $breakdownPpob = $reports
            ->flatMap->ppobItems
            ->groupBy('kategori')
            ->map(fn($g, $k) => [
                'kategori' => $k,
                'total'    => (int) $g->sum('nominal'),
                'jumlah'   => $g->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->toArray();

        // ================= BREAKDOWN TARIK TUNAI =================
        $allTarik = $reports->flatMap->tarikTunaiItems;
        $breakdownTarik = [
            'edc' => [
                'nominal' => (int) $allTarik->where('tipe', 'edc_bri')->sum('nominal'),
                'admin'   => (int) $allTarik->where('tipe', 'edc_bri')->sum('admin_toko'),
                'kmc'     => (int) $allTarik->where('tipe', 'edc_bri')->sum('kmc'),
                'count'   => $allTarik->where('tipe', 'edc_bri')->count(),
            ],
            'mbanking' => [
                'nominal' => (int) $allTarik->where('tipe', 'mbanking')->sum('nominal'),
                'admin'   => (int) $allTarik->where('tipe', 'mbanking')->sum('admin_toko'),
                'count'   => $allTarik->where('tipe', 'mbanking')->count(),
            ],
        ];

        // ================= SALDO PER REKENING =================
        $latestReport = $reports->first();

        $saldoPerRekening = [];
        if ($latestReport) {
            // Definisikan urutan prioritas
            $prioritasUrutan = [
                'brangkas'  => 1,
                'kas'       => 2,
                'mbanking'  => 3,
                'ppob'      => 4,
                'pulsa'     => 5,
            ];

            // Kumpulkan semua akun dari report terbaru (bisa dari banyak toko)
            $allAccounts = $reports->flatMap->accounts;

            // Gabungkan berdasarkan nama rekening
            $groupedAccounts = $allAccounts->groupBy('nama_rekening');

            $saldoPerRekening = $groupedAccounts->map(function ($items, $nama) {
                return [
                    'nama'      => $nama,
                    'keterangan'=> $items->first()->keterangan, // Ambil keterangan pertama
                    'saldo_awal'=> $items->sum('saldo_awal'),   // Jumlahkan saldo awal
                    'saldo'     => $items->sum('saldo_akhir'),  // Jumlahkan saldo akhir
                    'selisih'   => $items->sum('selisih'),      // Jumlahkan selisih
                ];
            })
            ->sortBy(function ($item) use ($prioritasUrutan) {
                $namaBawah = strtolower($item['nama']);
                foreach ($prioritasUrutan as $keyword => $bobot) {
                    if (str_contains($namaBawah, $keyword)) {
                        return $bobot;
                    }
                }
                return 99;
            })
            ->values()
            ->toArray();
        }

        $saldoPerKategori = collect($saldoPerRekening)
            ->groupBy(fn($s) => $s['keterangan'] ?: 'LAINNYA')
            ->map(fn($g, $k) => [
                'kategori' => $k,
                'total'    => (int) $g->sum('saldo'),
                'rekening' => $g->values()->toArray(),
            ])
            ->sortByDesc('total')
            ->values()
            ->toArray();

        // Hitung Jumlah Kas Uang dari Rekening "KAS"
        $kasData = collect($saldoPerRekening)->firstWhere('nama', 'KAS');
        $summary['jml_kas_uang'] = $kasData ? $kasData['saldo'] : 0;

        // Hitung Selisih Kas Uang (Jumlah Kas - Validasi Kas dari Excel)
        $summary['selisih_kas_uang'] = $summary['jml_kas_uang'] - $summary['jml_penjualan'];

        $summary['validasi_kas_uang'] = $latestReport ? (int) $latestReport->manual_validasi_kas_uang : 0;

        $summary['selisih_validasi'] = $summary['selisih_kas_uang'] - $summary['validasi_kas_uang'];

        // ================= TREND BULANAN =================
        $monthlyTrend = $this->buildMonthlyTrend($reports);

        // ================= LIST REPORT =================
        $reportList = $reports->map(fn($r) => [
            'id'             => $r->id,
            'file_name'      => $r->file_name,
            'periode_label'  => $r->periode_label,
            'periode_start'  => $r->periode_start?->format('Y-m-d'),
            'periode_end'    => $r->periode_end?->format('Y-m-d'),
            'omset_ppob'     => $r->total_omset_ppob,
            'setoran'        => $r->total_setoran_brilink,
            'tarik_edc'      => $r->total_tarik_tunai_edc,
            'tarik_mb'       => $r->total_tarik_tunai_mbanking,
            'pendapatan'     => $r->total_pendapatan_admin,
            'pengeluaran'    => $r->total_pengeluaran_riil,
            'profit'         => $r->profit,
            'saldo_akhir'    => $r->saldo_akhir_total,
            'status'         => $r->status,
        ])->toArray();

        return view('brilink.index', [
            'stores'             => $stores,
            'store'              => $store,
            'storeId'            => $storeId,
            'from'               => $fromInput ?? now()->format('Y-m-d'), // Kirim input asli atau hari ini
            'to'                 => $toInput, // Kirim input asli (bisa kosong)
            'summary'            => $summary,
            'breakdownPendapatan'=> $breakdownPendapatan,
            'breakdownPengeluaran' => $breakdownPengeluaran,
            'breakdownPpob'      => $breakdownPpob,
            'breakdownTarik'     => $breakdownTarik,
            'saldoPerRekening'   => $saldoPerRekening,
            'saldoPerKategori'   => $saldoPerKategori,
            'monthlyTrend'       => $monthlyTrend,
            'reportList'         => $reportList,
            'latestReport'       => $latestReport,
        ]);
    }

    /**
     * Detail 1 report.
     */
    public function show(BrilinkReport $report)
    {
        $report->load([
            'accounts',
            'ppobItems',
            'setoranItems',
            'tarikTunaiItems',
            'pengeluaranItems',
            'store',
        ]);

        return view('brilink.show', compact('report'));
    }

    /**
     * Hapus report (dan semua child via cascade).
     */
    public function destroy(BrilinkReport $report)
    {
        $importId = $report->import_id;
        $report->delete();
        \App\Models\Import::where('id', $importId)->delete();

        return redirect()->route('brilink.index', ['store' => $report->store_id])
            ->with('success', 'Report BRILink berhasil dihapus.');
    }

    /**
     * Build trend bulanan dari report (group by Y-m).
     */
    private function buildMonthlyTrend($reports): array
    {
        $grouped = $reports
            ->filter(fn($r) => $r->periode_start)
            ->groupBy(fn($r) => $r->periode_start->format('Y-m'))
            ->sortKeys();

        $result = [];
        $prevProfit = null;

        foreach ($grouped as $ym => $items) {
            $carbon = Carbon::createFromFormat('Y-m', $ym);

            $omset       = (int) $items->sum('total_omset_ppob');
            $pendapatan  = (int) $items->sum('total_pendapatan_admin');
            $pengeluaran = (int) $items->sum('total_pengeluaran_riil');
            $profit      = $pendapatan - $pengeluaran;

            $growth = null;
            if ($prevProfit !== null && $prevProfit != 0) {
                $growth = round((($profit - $prevProfit) / abs($prevProfit)) * 100, 1);
            }
            $prevProfit = $profit;

            $result[] = [
                'year_month'  => $ym,
                'label'       => $carbon->translatedFormat('M Y'),
                'reports'     => $items->count(),
                'omset'       => $omset,
                'pendapatan'  => $pendapatan,
                'pengeluaran' => $pengeluaran,
                'profit'      => $profit,
                'growth'      => $growth,
            ];
        }

        // Urutkan desc untuk tampilan
        return array_reverse($result);
    }

    public function pengeluaran(Request $request)
    {
        $storeId = $request->input('store');
        $activeTab = $request->input('tab', 'semua');
        $search = trim($request->input('search', ''));

        // Simpan input asli untuk View (HTML)
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Nilai untuk Query Database
        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

        // Logika 1 tanggal (untuk Query)
        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        // Default ke 'all' jika belum dipilih
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // Query dasar
        $query = BrilinkPengeluaran::whereHas('report', function($q) use ($storeId, $from, $to) {
            // Filter store_id hanya jika bukan 'all'
            $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
              ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
              ->when($to, fn($sq) => $sq->whereDate('periode_start', '<=', $to));
        })->with('report.store');

        // Filter Pencarian
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                  ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        // Ambil semua data (untuk mendapatkan daftar kategori/tab)
        $allData = $query->get();

        // Dapatkan daftar kategori unik untuk Tab
        $kategoriList = $allData->pluck('kategori')->filter()->unique()->sort()->values();

        // Filter data sesuai Tab yang aktif
        if ($activeTab !== 'semua') {
            $allData = $allData->filter(function($item) use ($activeTab) {
                return strtolower(trim($item->kategori)) === strtolower($activeTab);
            });
        }

        // Grouping untuk tabel horizontal
        $groupedByReport = $allData->groupBy('brilink_report_id');

        return view('brilink.pengeluaran', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'groupedByReport' => $groupedByReport,
            'kategoriList' => $kategoriList,
            'activeTab' => $activeTab,
            'search' => $search,
            'from' => $fromInput ?? now()->format('Y-m-d'),
            'to' => $toInput,
        ]);
    }

    public function updateValidasi(Request $request, BrilinkReport $report)
    {
        $request->validate([
            'manual_validasi_kas_uang' => 'required|integer|min:0'
        ]);

        $report->update([
            'manual_validasi_kas_uang' => $request->input('manual_validasi_kas_uang')
        ]);

        return back()->with('success', 'Validasi kas uang berhasil diperbarui.');
    }

    public function logPpob(Request $request)
    {
        $storeId = $request->input('store');
        $search = trim($request->input('search', ''));

        // Simpan input asli untuk View (HTML)
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Nilai untuk Query Database
        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

        // Logika 1 tanggal (untuk Query)
        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // Ambil data PPOB
        $query = BrilinkPpobTransaction::whereHas('report', function($q) use ($storeId, $from, $to) {
            $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
              ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
              ->when($to, fn($sq) => $sq->whereDate('periode_start', '<=', $to));
        })->with(['report']); // Cukup relasi report

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('kategori', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        $ppobData = $query->get();
        $totalOmset = $ppobData->sum('nominal');

        return view('brilink.log-ppob', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'ppobData' => $ppobData,
            'totalOmset' => $totalOmset,
            'search' => $search,
            'from' => $fromInput ?? now()->format('Y-m-d'),
            'to' => $toInput,
        ]);
    }

    public function storeLogPpob(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'kategori' => 'required',
            'nominal'  => 'required|integer',
            'tanggal'  => 'required|date'
        ]);

        // Cari atau buat report untuk tanggal tersebut
        $report = BrilinkReport::firstOrCreate(
            [
                'store_id' => $request->store_id,
                'periode_start' => $request->tanggal,
            ],
            [
                'import_id' => 1, // Atau sesuaikan dengan logic import Anda
                'file_name' => 'Input Manual',
                'file_hash' => uniqid(),
                'status'    => 'imported',
            ]
        );

        // Simpan transaksi
        BrilinkPpobTransaction::create([
            'brilink_report_id' => $report->id,
            'kategori'          => $request->kategori,
            'nominal'           => $request->nominal,
            'keterangan'        => 'Input Manual'
        ]);

        return back()->with('success', 'Transaksi PPOB berhasil ditambahkan.');
    }

    public function storeLogPpobAjax(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'kategori' => 'required',
            'nominal'  => 'required|numeric|min:1',
            'tanggal'  => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            $report = BrilinkReport::firstOrCreate(
                [
                    'store_id'      => $request->store_id,
                    'periode_start' => $request->tanggal,
                ],
                [
                    'import_id'     => 1,
                    'file_name'     => 'Input Manual',
                    'file_hash'     => uniqid(),
                    'periode_end'   => $request->tanggal,
                    'status'        => 'imported',
                ]
            );

            BrilinkPpobTransaction::create([
                'brilink_report_id' => $report->id,
                'kategori'          => $request->kategori,
                'nominal'           => $request->nominal,
                'keterangan'        => 'Input Manual'
            ]);

            // 🔥 Recalculate total_omset_ppob di report
            $this->recalculateReportPpobTotal($report);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi PPOB berhasil ditambahkan.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    public function storePengeluaranAjax(Request $request)
    {
        $request->validate([
            'store_id'   => 'required',
            'kategori'   => 'required|string',
            'keterangan' => 'required|string',
            'tanggal'    => 'required|date'
        ]);

        // Bersihkan nominal dari karakter non-angka
        $nominalBersih = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);

        if ($nominalBersih <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        DB::beginTransaction();
        try {
            // Cari atau buat report untuk tanggal tersebut
            $report = BrilinkReport::firstOrCreate(
                [
                    'store_id'      => $request->store_id,
                    'periode_start' => $request->tanggal,
                ],
                [
                    'import_id'     => 1,
                    'file_name'     => 'Input Manual',
                    'file_hash'     => uniqid(),
                    'periode_end'   => $request->tanggal,
                    'status'        => 'imported',
                ]
            );

            // Simpan pengeluaran
            BrilinkPengeluaran::create([
                'brilink_report_id' => $report->id,
                'kategori'          => $request->kategori,
                'keterangan'        => $request->keterangan,
                'nominal_kas'       => $nominalBersih,
            ]);

            // 🔥 PENTING: Recalculate total di report
            $this->recalculateReportTotals($report);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil ditambahkan.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Helper: Recalculate total_pengeluaran_riil & total_pindah_saldo
     * di tabel brilink_reports berdasarkan child items.
     * 
     * WAJIB dipanggil setiap kali ada CRUD pada BrilinkPengeluaran.
     */
    private function recalculateReportTotals(BrilinkReport $report): void
    {
        $riil = $report->pengeluaranItems()
            ->where(function ($q) {
                $q->whereNull('kategori')
                ->orWhere('kategori', '!=', 'Pindah Saldo Internal');
            })
            ->sum('nominal_kas');

        $pindah = $report->pengeluaranItems()
            ->where('kategori', 'Pindah Saldo Internal')
            ->sum('nominal_kas');

        $report->update([
            'total_pengeluaran_riil' => (int) $riil,
            'total_pindah_saldo'     => (int) $pindah,
        ]);
    }

    /**
     * AJAX: Update 1 pengeluaran.
     * Tanggal (periode) TIDAK diubah — hanya keterangan, kategori, nominal.
     */
    public function updatePengeluaranAjax(Request $request, BrilinkPengeluaran $pengeluaran)
    {
        $request->validate([
            'kategori'   => 'required|string',
            'keterangan' => 'required|string',
            'nominal'    => 'required',
        ]);

        // Bersihkan nominal dari karakter non-angka
        $nominalBersih = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);

        if ($nominalBersih <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        DB::beginTransaction();
        try {
            $pengeluaran->update([
                'kategori'    => $request->kategori,
                'keterangan'  => $request->keterangan,
                'nominal_kas' => $nominalBersih,
            ]);

            // Recalculate report totals
            $this->recalculateReportTotals($pengeluaran->report);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Hapus 1 pengeluaran.
     */
    public function destroyPengeluaranAjax(BrilinkPengeluaran $pengeluaran)
    {
        DB::beginTransaction();
        try {
            $report = $pengeluaran->report;
            $keterangan = $pengeluaran->keterangan;

            $pengeluaran->delete();

            // Recalculate report totals
            if ($report) {
                $this->recalculateReportTotals($report);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengeluaran '{$keterangan}' berhasil dihapus.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Recalculate total_omset_ppob di report
     * berdasarkan child ppobItems.
     */
    private function recalculateReportPpobTotal(BrilinkReport $report): void
    {
        $total = $report->ppobItems()->sum('nominal');

        $report->update([
            'total_omset_ppob' => (int) $total,
        ]);
    }

    /**
     * AJAX: Update 1 transaksi PPOB (kategori & nominal).
     * Keterangan otomatis di-set "Input Manual".
     */
    public function updateLogPpobAjax(Request $request, BrilinkPpobTransaction $ppob)
    {
        $request->validate([
            'kategori' => 'required|string',
            'nominal'  => 'required',
        ]);

        $nominalBersih = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);

        if ($nominalBersih <= 0) {
            return response()->json([
                'message' => 'Nominal harus lebih dari 0. Untuk menghapus, gunakan tombol Hapus.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $ppob->update([
                'kategori'   => $request->kategori,
                'nominal'    => $nominalBersih,
                'keterangan' => 'Input Manual',
            ]);

            if ($ppob->report) {
                $this->recalculateReportPpobTotal($ppob->report);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi PPOB berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Hapus (hard delete) 1 transaksi PPOB.
     */
    public function destroyLogPpobAjax(BrilinkPpobTransaction $ppob)
    {
        DB::beginTransaction();
        try {
            $report   = $ppob->report;
            $kategori = $ppob->kategori;

            $ppob->delete();

            if ($report) {
                $this->recalculateReportPpobTotal($report);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transaksi '{$kategori}' berhasil dihapus.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    public function logSetoran(Request $request)
    {
        $storeId = $request->input('store');
        $search = trim($request->input('search', ''));

        // Simpan input asli untuk View (HTML)
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Nilai untuk Query Database
        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

        // Logika 1 tanggal (untuk Query)
        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        // Default ke 'all'
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // Query Report beserta item setorannya
        $query = BrilinkReport::with(['setoranItems'])
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('periode_start')
            ->orderByDesc('id');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'LIKE', "%{$search}%")
                ->orWhere('periode_label', 'LIKE', "%{$search}%");
            });
        }

        $reports = $query->get();
        $totalSetoranKeseluruhan = $reports->flatMap->setoranItems->sum('jumlah_setoran_riil');

        return view('brilink.log-setoran', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'reports' => $reports,
            'totalSetoranKeseluruhan' => $totalSetoranKeseluruhan,
            'search' => $search,
            'from' => $fromInput ?? now()->format('Y-m-d'),
            'to' => $toInput,
        ]);
    }

    public function updateValidasiMutasi(Request $request, BrilinkReport $report)
    {
        $request->validate([
            'manual_validasi_mutasi_rek' => 'required|string'
        ]);

        // Bersihkan format Rupiah (titik) menjadi integer murni
        $nominalBersih = (int) preg_replace('/[^0-9]/', '', $request->input('manual_validasi_mutasi_rek'));

        $report->update([
            'manual_validasi_mutasi_rek' => $nominalBersih
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Validasi mutasi rekening berhasil diperbarui.'
        ]);
    }

    public function storeLogSetoranAjax(Request $request)
    {
        $request->validate([
            'store_id'      => 'required',
            'tanggal'       => 'required|date',
            'nominal_edc'   => 'required',
            'admin_toko'    => 'required',
        ]);

        // Bersihkan format Rupiah (titik) menjadi integer murni
        $nominalEdc = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal_edc);
        $adminToko  = (int) preg_replace('/[^0-9]/', '', (string) $request->admin_toko);

        if ($nominalEdc <= 0 && $adminToko <= 0) {
            return response()->json(['message' => 'Nominal EDC dan Admin tidak boleh kosong keduanya.'], 422);
        }

        DB::beginTransaction();
        try {
            // Cari atau buat report untuk tanggal tersebut
            $report = BrilinkReport::firstOrCreate(
                [
                    'store_id'      => $request->store_id,
                    'periode_start' => $request->tanggal,
                ],
                [
                    'import_id'     => 1,
                    'file_name'     => 'Input Manual',
                    'file_hash'     => uniqid(),
                    'periode_end'   => $request->tanggal,
                    'status'        => 'imported',
                ]
            );

            // Simpan ke tabel brilink_setoran
            BrilinkSetoran::create([
                'brilink_report_id'   => $report->id,
                'nominal_edc'         => $nominalEdc,
                'administrasi_toko'   => $adminToko,
                'jumlah_setoran_riil'=> $nominalEdc, // Asumsi setoran riil = nominal EDC
            ]);

            // 🔥 PENTING: Update total_setoran_brilink di report agar Ringkasan & Saldo akurat
            $totalSetoranBaru = $report->setoranItems()->sum('jumlah_setoran_riil');
            $report->update([
                'total_setoran_brilink' => $totalSetoranBaru
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setoran BRILink berhasil ditambahkan.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Update 1 setoran manual.
     */
    public function updateLogSetoranAjax(Request $request, BrilinkSetoran $setoran)
    {
        $request->validate([
            'nominal_edc' => 'required',
            'admin_toko'  => 'required',
        ]);

        $nominalEdc = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal_edc);
        $adminToko  = (int) preg_replace('/[^0-9]/', '', (string) $request->admin_toko);

        if ($nominalEdc <= 0 && $adminToko <= 0) {
            return response()->json(['message' => 'Nominal EDC dan Admin tidak boleh kosong keduanya.'], 422);
        }

        DB::beginTransaction();
        try {
            $setoran->update([
                'nominal_edc'          => $nominalEdc,
                'administrasi_toko'    => $adminToko,
                'jumlah_setoran_riil'  => $nominalEdc,
            ]);

            // Recalculate total_setoran_brilink di report
            if ($setoran->report) {
                $totalSetoranBaru = $setoran->report->setoranItems()->sum('jumlah_setoran_riil');
                $setoran->report->update(['total_setoran_brilink' => $totalSetoranBaru]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setoran BRILink berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Hapus 1 setoran manual.
     */
    public function destroyLogSetoranAjax(BrilinkSetoran $setoran)
    {
        DB::beginTransaction();
        try {
            $report = $setoran->report;
            
            $setoran->delete();

            // Recalculate total_setoran_brilink di report
            if ($report) {
                $totalSetoranBaru = $report->setoranItems()->sum('jumlah_setoran_riil');
                $report->update(['total_setoran_brilink' => $totalSetoranBaru]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setoran BRILink berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    public function logTarikTunai(Request $request)
    {
        $storeId = $request->input('store');
        $search = trim($request->input('search', ''));

        // Simpan input asli untuk View (HTML)
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Nilai untuk Query Database
        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

        // Logika 1 tanggal (untuk Query)
        if ($from && !$to) $to = $from;
        elseif ($to && !$from) $from = $to;

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        // Default ke 'all'
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        $query = BrilinkReport::with(['tarikTunaiItems'])
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('periode_start')
            ->orderByDesc('id');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'LIKE', "%{$search}%")
                ->orWhere('periode_label', 'LIKE', "%{$search}%");
            });
        }

        $reports = $query->get();

        return view('brilink.log-tarik-tunai', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'reports' => $reports,
            'search' => $search,
            'from' => $fromInput ?? now()->format('Y-m-d'),
            'to' => $toInput,
        ]);
    }

    public function updateValidasiFisik(Request $request, BrilinkReport $report)
    {
        $request->validate([
            'manual_validasi_mutasi_fisik' => 'required|string'
        ]);

        $nominalBersih = (int) preg_replace('/[^0-9]/', '', $request->input('manual_validasi_mutasi_fisik'));

        $report->update([
            'manual_validasi_mutasi_fisik' => $nominalBersih
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Validasi mutasi fisik berhasil diperbarui.'
        ]);
    }

    public function storeLogTarikTunaiAjax(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'tanggal'  => 'required|date',
        ]);

        // Bersihkan format Rupiah menjadi integer murni
        $edcNominal = (int) preg_replace('/[^0-9]/', '', (string) $request->edc_nominal);
        $edcAdmin   = (int) preg_replace('/[^0-9]/', '', (string) $request->edc_admin);
        $edcKmc     = (int) preg_replace('/[^0-9]/', '', (string) $request->edc_kmc);
        
        $mbNominal  = (int) preg_replace('/[^0-9]/', '', (string) $request->mb_nominal);
        $mbAdmin    = (int) preg_replace('/[^0-9]/', '', (string) $request->mb_admin);
        $mbSaldo    = (int) preg_replace('/[^0-9]/', '', (string) $request->mb_saldo);
        
        $keterangan = $request->keterangan;
        $saldoPindah = (int) preg_replace('/[^0-9]/', '', (string) $request->saldo_pindah);

        if ($edcNominal <= 0 && $mbNominal <= 0) {
            return response()->json(['message' => 'Nominal EDC dan MBanking tidak boleh kosong keduanya.'], 422);
        }

        DB::beginTransaction();
        try {
            // Cari atau buat report untuk tanggal tersebut
            $report = BrilinkReport::firstOrCreate(
                [
                    'store_id'      => $request->store_id,
                    'periode_start' => $request->tanggal,
                ],
                [
                    'import_id'     => 1,
                    'file_name'     => 'Input Manual',
                    'file_hash'     => uniqid(),
                    'periode_end'   => $request->tanggal,
                    'status'        => 'imported',
                ]
            );

            // 1. Simpan EDC jika diisi
            if ($edcNominal > 0 || $edcAdmin > 0 || $edcKmc > 0) {
                BrilinkTarikTunai::create([
                    'brilink_report_id' => $report->id,
                    'tipe'              => 'edc_bri',
                    'nominal'           => $edcNominal,
                    'admin_toko'        => $edcAdmin,
                    'kmc'               => $edcKmc,
                    'saldo_masuk'       => $saldoPindah, // Simpan saldo pindah di sini
                    'keterangan_rek'    => $keterangan,
                ]);
            }

            // 2. Simpan MBanking jika diisi
            if ($mbNominal > 0 || $mbAdmin > 0) {
                BrilinkTarikTunai::create([
                    'brilink_report_id' => $report->id,
                    'tipe'              => 'mbanking',
                    'nominal'           => $mbNominal,
                    'admin_toko'        => $mbAdmin,
                    'kmc'               => 0,
                    'saldo_masuk'       => $mbSaldo,
                    'keterangan_rek'    => $keterangan,
                ]);
            }

            // 3. Recalculate Report Totals (Agar Ringkasan & Saldo akurat)
            $totalEdc = $report->tarikTunaiItems()->where('tipe', 'edc_bri')->sum('nominal');
            $totalMb  = $report->tarikTunaiItems()->where('tipe', 'mbanking')->sum('nominal');
            
            $totalAdmin = $report->tarikTunaiItems()->sum('admin_toko') + $report->tarikTunaiItems()->sum('kmc');

            $report->update([
                'total_tarik_tunai_edc'      => $totalEdc,
                'total_tarik_tunai_mbanking' => $totalMb,
                'total_pendapatan_admin'     => $report->total_pendapatan_admin + $totalAdmin, 
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Tarik Tunai berhasil ditambahkan.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Update 1 baris Tarik Tunai (bisa update EDC dan MBanking sekaligus).
     */
    public function updateLogTarikTunaiAjax(Request $request)
    {
        $request->validate([
            'edc_id' => 'nullable|exists:brilink_tarik_tunai,id',
            'mb_id'  => 'nullable|exists:brilink_tarik_tunai,id',
        ]);

        // Bersihkan format Rupiah
        $edcNominal = (int) preg_replace('/[^0-9]/', '', (string) $request->edc_nominal);
        $edcAdmin   = (int) preg_replace('/[^0-9]/', '', (string) $request->edc_admin);
        $edcKmc     = (int) preg_replace('/[^0-9]/', '', (string) $request->edc_kmc);
        
        $mbNominal  = (int) preg_replace('/[^0-9]/', '', (string) $request->mb_nominal);
        $mbAdmin    = (int) preg_replace('/[^0-9]/', '', (string) $request->mb_admin);
        $mbSaldo    = (int) preg_replace('/[^0-9]/', '', (string) $request->mb_saldo);
        
        $keterangan = $request->keterangan;
        $saldoPindah = (int) preg_replace('/[^0-9]/', '', (string) $request->saldo_pindah);

        DB::beginTransaction();
        try {
            $reportId = null;

            // 1. Update atau Create EDC
            if ($request->filled('edc_id')) {
                $edc = BrilinkTarikTunai::find($request->edc_id);
                $edc->update([
                    'nominal'        => $edcNominal,
                    'admin_toko'     => $edcAdmin,
                    'kmc'            => $edcKmc,
                    'saldo_masuk'    => $saldoPindah,
                    'keterangan_rek' => $keterangan,
                ]);
                $reportId = $edc->brilink_report_id;
            } elseif ($edcNominal > 0 || $edcAdmin > 0 || $edcKmc > 0) {
                $newEdc = BrilinkTarikTunai::create([
                    'brilink_report_id' => $request->report_id,
                    'tipe'              => 'edc_bri',
                    'nominal'           => $edcNominal,
                    'admin_toko'        => $edcAdmin,
                    'kmc'               => $edcKmc,
                    'saldo_masuk'       => $saldoPindah,
                    'keterangan_rek'    => $keterangan,
                ]);
                $reportId = $newEdc->brilink_report_id;
            } elseif ($request->filled('edc_id')) {
                // Jika diinput kosong (0) tapi sebelumnya ada ID, hapus datanya
                BrilinkTarikTunai::find($request->edc_id)->delete();
            }

            // 2. Update atau Create MBanking
            if ($request->filled('mb_id')) {
                $mb = BrilinkTarikTunai::find($request->mb_id);
                $mb->update([
                    'nominal'        => $mbNominal,
                    'admin_toko'     => $mbAdmin,
                    'saldo_masuk'    => $mbSaldo,
                    'keterangan_rek' => $keterangan,
                ]);
                $reportId = $reportId ?: $mb->brilink_report_id;
            } elseif ($mbNominal > 0 || $mbAdmin > 0) {
                $newMb = BrilinkTarikTunai::create([
                    'brilink_report_id' => $request->report_id,
                    'tipe'              => 'mbanking',
                    'nominal'           => $mbNominal,
                    'admin_toko'        => $mbAdmin,
                    'kmc'               => 0,
                    'saldo_masuk'       => $mbSaldo,
                    'keterangan_rek'    => $keterangan,
                ]);
                $reportId = $reportId ?: $newMb->brilink_report_id;
            } elseif ($request->filled('mb_id')) {
                // Jika diinput kosong (0) tapi sebelumnya ada ID, hapus datanya
                BrilinkTarikTunai::find($request->mb_id)->delete();
            }

            // 3. Recalculate Report Totals
            if ($reportId) {
                $report = BrilinkReport::find($reportId);
                if ($report) {
                    $totalEdc = $report->tarikTunaiItems()->where('tipe', 'edc_bri')->sum('nominal');
                    $totalMb  = $report->tarikTunaiItems()->where('tipe', 'mbanking')->sum('nominal');
                    
                    $totalAdmin = $report->tarikTunaiItems()->sum('admin_toko') + $report->tarikTunaiItems()->sum('kmc');

                    $report->update([
                        'total_tarik_tunai_edc'      => $totalEdc,
                        'total_tarik_tunai_mbanking' => $totalMb,
                        'total_pendapatan_admin'     => $totalAdmin,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Tarik Tunai berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Hapus sepasang Tarik Tunai (EDC & MBanking).
     */
    public function destroyLogTarikTunaiAjax(Request $request)
    {
        $edcId = $request->input('edc_id');
        $mbId  = $request->input('mb_id');

        DB::beginTransaction();
        try {
            $reportId = null;

            if ($edcId) {
                $edc = BrilinkTarikTunai::find($edcId);
                if ($edc) {
                    $reportId = $edc->brilink_report_id;
                    $edc->delete();
                }
            }

            if ($mbId) {
                $mb = BrilinkTarikTunai::find($mbId);
                if ($mb) {
                    $reportId = $reportId ?: $mb->brilink_report_id;
                    $mb->delete();
                }
            }

            // Recalculate Report Totals
            if ($reportId) {
                $report = BrilinkReport::find($reportId);
                if ($report) {
                    $totalEdc = $report->tarikTunaiItems()->where('tipe', 'edc_bri')->sum('nominal');
                    $totalMb  = $report->tarikTunaiItems()->where('tipe', 'mbanking')->sum('nominal');
                    
                    $totalAdmin = $report->tarikTunaiItems()->sum('admin_toko') + $report->tarikTunaiItems()->sum('kmc');

                    $report->update([
                        'total_tarik_tunai_edc'      => $totalEdc,
                        'total_tarik_tunai_mbanking' => $totalMb,
                        'total_pendapatan_admin'     => $totalAdmin,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Tarik Tunai berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    public function profit(Request $request)
    {
        $storeId = $request->input('store');

        // 1. Simpan input asli untuk View (HTML)
        $fromInput = $request->input('from', now()->format('Y-m-d'));
        $toInput   = $request->input('to');

        // 2. Nilai untuk Query Database
        $from = $fromInput;
        $to   = $toInput;

        // Logika 1 tanggal (Jika hanya isi 1 tanggal, jadikan filter 1 hari)
        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        // Default ke 'all'
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // =====================================================================
        // SISA KODE DI BAWAH INI TETAP SAMA, TAPI TAMBAHKAN KONDISI storeId
        // =====================================================================
        $latestReportQuery = BrilinkReport::when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('periode_start')->orderByDesc('id');
        $latestReport = $latestReportQuery->first();

        $oldestReportQuery = BrilinkReport::when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderBy('periode_start')->orderBy('id');
        $oldestReport = $oldestReportQuery->first();

        // 2. Helper untuk Saldo
        $getSaldoAkhir = function($report, $keyword) {
            if (!$report) return 0;
            return $report->accounts()
                ->where(function($q) use ($keyword) {
                    $q->where('keterangan', 'LIKE', "%{$keyword}%")
                    ->orWhere('nama_rekening', 'LIKE', "%{$keyword}%");
                })->sum('saldo_akhir');
        };
        
        $getSaldoAwal = function($report, $keyword) {
            if (!$report) return 0;
            return $report->accounts()
                ->where(function($q) use ($keyword) {
                    $q->where('keterangan', 'LIKE', "%{$keyword}%")
                    ->orWhere('nama_rekening', 'LIKE', "%{$keyword}%");
                })->sum('saldo_awal');
        };

        // 3. Ambil semua report dalam rentang filter untuk menghitung Total Penjualan (Omset)
        $reportsInPeriod = BrilinkReport::with(['ppobItems', 'setoranItems', 'tarikTunaiItems', 'pengeluaranItems'])
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->get();

        // Kalkulasi Omset dari transaksi PPOB
        $allPpob = $reportsInPeriod->flatMap->ppobItems;
    
        // A. Pulsa
        $penjualanPulsa = $allPpob->filter(fn($i) => stripos($i->kategori, 'Pulsa') !== false)->sum('nominal');
        
        // B. Kasir Pintar
        $penjualanKasirPintar = $allPpob->filter(fn($i) => stripos($i->kategori, 'Kasir Pintar') !== false)->sum('nominal');
        
        // C. PPOB Murni (Fastpay, Payfazz, Buku Warung, dll). 
        // Abaikan kategori yang mengandung 'Pulsa', 'Kasir Pintar', 'Lain', dan 'Penambahan Kas'
        $penjualanPpob = $allPpob->filter(function($i) {
            $kat = strtolower($i->kategori);
            return strpos($kat, 'pulsa') === false 
                && strpos($kat, 'kasir pintar') === false 
                && strpos($kat, 'lain') === false
                && strpos($kat, 'penambahan kas') === false;
        })->sum('nominal');

        // Kalkulasi Omset dari transaksi Lainnya
        $penjualanBrilink = $reportsInPeriod->sum('total_setoran_brilink') 
                            + $reportsInPeriod->sum('total_tarik_tunai_edc'); 
                            
        $penjualanMbanking = $reportsInPeriod->sum('total_tarik_tunai_mbanking') 
                            + $reportsInPeriod->flatMap->tarikTunaiItems->where('tipe', 'mbanking')->sum('admin_toko');

        $labaKasirPintar = $reportsInPeriod->sum('laba_kasir_pintar');
        
        $allPengeluaran = $reportsInPeriod->flatMap->pengeluaranItems;
        $pengeluaranBrangkas = $allPengeluaran->filter(fn($i) => stripos($i->kategori, 'Brangkas') !== false || stripos($i->kategori, 'Pindah Saldo') !== false)->sum('nominal_kas');

        $adminSetoran = $reportsInPeriod->flatMap->setoranItems->sum('administrasi_toko');
        $adminTarikEdc = $reportsInPeriod->flatMap->tarikTunaiItems->where('tipe', 'edc_bri')->sum('admin_toko');
        $pendapatanAdminBrilink = $adminSetoran + $adminTarikEdc;

        $adminTarikMb = $reportsInPeriod->flatMap->tarikTunaiItems->where('tipe', 'mbanking')->sum('admin_toko');

        $penambahanBrangkas = $reportsInPeriod->sum('penambahan_brangkas');
        
        // Pemakaian Brangkas diambil dari total kategori "Penambahan Kas" di PPOB
        $pemakaianBrangkas = $allPpob->filter(fn($i) => stripos($i->kategori, 'Penambahan Kas') !== false)->sum('nominal');

        $totalPrive = $allPengeluaran->filter(fn($i) => stripos($i->kategori, 'Prive') !== false)->sum('nominal_kas');
        
        // Ambil Saldo Awal & Saldo Akhir Brangkas
        $brangkasAwal = $getSaldoAwal($oldestReport, 'Brangkas');
        $brangkasAkhir = $getSaldoAkhir($latestReport, 'Brangkas');

        // 4. Susun Data Profit
        $dataProfit = [
            'pulsa' => [
                'saldo_awal' => $getSaldoAwal($oldestReport, 'Pulsa'),
                'saldo_akhir' => $getSaldoAkhir($latestReport, 'Pulsa'),
                'penjualan' => $penjualanPulsa,
            ],
            'ppob' => [
                'saldo_awal' => $getSaldoAwal($oldestReport, 'PPOB'),
                'saldo_akhir' => $getSaldoAkhir($latestReport, 'PPOB'),
                'penjualan' => $penjualanPpob,
            ],
            'brilink' => [
                'saldo_awal' => $getSaldoAwal($oldestReport, 'BRILink'),
                'saldo_akhir' => $getSaldoAkhir($latestReport, 'BRILink'),
                'pendapatan_admin' => $pendapatanAdminBrilink,
            ],
            'mbanking' => [
                'saldo_awal' => $getSaldoAwal($oldestReport, 'MBanking'),
                'saldo_akhir' => $getSaldoAkhir($latestReport, 'MBanking'),
                'pendapatan_admin' => $adminTarikMb,
            ],
            'kasir_pintar' => [
                'saldo_awal' => $getSaldoAwal($oldestReport, 'SALDO BARANG') ?: $getSaldoAwal($oldestReport, 'POS'),
                'saldo_akhir' => $getSaldoAkhir($latestReport, 'SALDO BARANG') ?: $getSaldoAkhir($latestReport, 'POS'),
                'penjualan' => $penjualanKasirPintar,
                'laba' => $labaKasirPintar,
            ],
            'brangkas' => [
                'saldo_awal' => $brangkasAwal,
                'penambahan' => $penambahanBrangkas,
                'jumlah_brangkas' => $brangkasAwal + $penambahanBrangkas,
                'pemakaian' => $pemakaianBrangkas,
                'saldo_akhir' => ($brangkasAwal + $penambahanBrangkas) - $pemakaianBrangkas, 
            ],
        ];

        // 5. Hitung Saldo Terpakai dan Profit (Hanya untuk Pulsa, PPOB, BRILink, MBanking)
        foreach (['pulsa', 'ppob'] as $key) {
            $dataProfit[$key]['saldo_terpakai'] = $dataProfit[$key]['saldo_awal'] - $dataProfit[$key]['saldo_akhir'];
            $dataProfit[$key]['profit'] = $dataProfit[$key]['penjualan'] - $dataProfit[$key]['saldo_terpakai'];
        }

        foreach (['brilink', 'mbanking'] as $key) {
            $dataProfit[$key]['saldo_terpakai'] = $dataProfit[$key]['saldo_awal'] - $dataProfit[$key]['saldo_akhir'];
        }

        // 6. Rekapitulasi Saldo Keseluruhan
        $totalSaldoAwal = array_sum(array_column($dataProfit, 'saldo_awal'));
        $totalSaldoAkhir = array_sum(array_column($dataProfit, 'saldo_akhir'));

        // 7. Hitung Rangkuman Profit & Pengeluaran
        $profitPulsa = $dataProfit['pulsa']['profit'];
        $profitPpob = $dataProfit['ppob']['profit'];
        $adminBrilink = $dataProfit['brilink']['pendapatan_admin'];
        $adminMbanking = $dataProfit['mbanking']['pendapatan_admin'];
        $labaKasirPintar = $dataProfit['kasir_pintar']['laba'];

        $totalProfitKotor = $profitPulsa + $profitPpob + $adminBrilink + $adminMbanking + $labaKasirPintar;
        
        $pendapatanHarian = $totalProfitKotor - $totalPrive;
        $pengeluaranHarian = $totalPrive;
        $penambahanSaldo = $pendapatanHarian - $pengeluaranHarian;

        return view('brilink.profit', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'from' => $fromInput,
            'to' => $toInput,
            'dataProfit' => $dataProfit,
            'totalSaldoAwal' => $totalSaldoAwal,
            'totalSaldoAkhir' => $totalSaldoAkhir,
            
            // 🔥 Variabel untuk Card Rangkuman Bawah
            'profitPulsa' => $profitPulsa,
            'profitPpob' => $profitPpob,
            'adminBrilink' => $adminBrilink,
            'adminMbanking' => $adminMbanking,
            'labaKasirPintar' => $labaKasirPintar,
            'totalProfitKotor' => $totalProfitKotor,
            'totalPrive' => $totalPrive,
            'pendapatanHarian' => $pendapatanHarian,
            'pengeluaranHarian' => $pengeluaranHarian,
            'penambahanSaldo' => $penambahanSaldo,
        ]);
    }

    public function saldoKeseluruhan(Request $request)
    {
        $storeId = $request->input('store');
        $search = trim($request->input('search', ''));
        
        // Simpan input asli untuk View (HTML)
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Nilai untuk Query Database
        $from = $fromInput;
        $to   = $toInput;

        // Logika 1 tanggal (untuk Query)
        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // 1. Ambil semua data akun yang report-nya masuk filter tanggal
        $query = BrilinkAccount::with('report')
            ->whereHas('report', function($q) use ($storeId, $from, $to) {
                $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
                  ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
                  ->when($to, fn($sq) => $sq->whereDate('periode_start', '<=', $to));
            });

        // 2. Filter Search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama_rekening', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        // 3. Ambil data
        $allAccounts = $query->get();

        // 4. Kelompokkan per TANGGAL IMPORT (Bukan ID Report)
        $groupedByDate = $allAccounts->groupBy(function($item) {
            return $item->report->periode_start->format('Y-m-d');
        });

        // 5. Urutkan grup tanggal berdasarkan tanggal terbaru
        $groupedByDate = $groupedByDate->sortKeysDesc();

        // 6. Format data untuk View (Agregasi / Penjumlahan)
        $dataPerReport = [];
        foreach ($groupedByDate as $date => $accounts) {
            $firstReport = $accounts->first()->report;

            // 🔥 Gabungkan data rekening yang namanya sama di tanggal tersebut
            $aggregatedAccounts = $accounts->groupBy('nama_rekening')->map(function ($items) {
                return [
                    'nama_rekening' => $items->first()->nama_rekening,
                    'keterangan'    => $items->first()->keterangan,
                    'saldo_awal'    => $items->sum('saldo_awal'),
                    'saldo_akhir'   => $items->sum('saldo_akhir'),
                    'selisih'       => $items->sum('selisih'),
                ];
            })->values()->toArray();

            $dataPerReport[] = [
                'date' => $date,
                'label' => $firstReport->periode_label ?? \Carbon\Carbon::parse($date)->format('d/m/Y'),
                'accounts' => $aggregatedAccounts,
            ];
        }

        return view('brilink.saldo-keseluruhan', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'dataPerReport' => $dataPerReport,
            'search' => $search,
            'from' => $fromInput,
            'to' => $toInput,
        ]);
    }
}