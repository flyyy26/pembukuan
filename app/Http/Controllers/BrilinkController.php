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
use Barryvdh\DomPDF\Facade\Pdf;

class BrilinkController extends Controller
{
        public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d'],
        ]);

        $storeId = $request->input('store');
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

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

        $reportQuery = BrilinkReport::with([
                'accounts',
                'ppobItems',
                'setoranItems',
                'tarikTunaiItems',
                'pengeluaranItems',
            ])
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

        $cariPengeluaran = function($keywords) use ($allPengeluaran) {
            return $allPengeluaran->filter(function ($p) use ($keywords) {
                // Cek di kolom kategori (paling akurat sesuai Excel)
                $kategori = strtolower(trim($p->kategori ?? ''));
                foreach ((array) $keywords as $kw) {
                    if ($kategori === strtolower($kw)) return true;
                }
                // Fallback cek keterangan jika kategori kosong
                $keterangan = strtolower(trim($p->keterangan ?? ''));
                foreach ((array) $keywords as $kw) {
                    if (str_contains($keterangan, strtolower($kw))) return true;
                }
                return false;
            })->sum('nominal_kas');
        };

        $totalPindahSaldo = $cariPengeluaran('PINDAH SALDO KAS INTERNAL');
        $totalTopupPpob   = $cariPengeluaran('TOP UP PPOB');
        $totalTopupPulsa  = $cariPengeluaran('TOP UP PULSA');
        $totalPiutangHaji = $cariPengeluaran('PIUTANG BU HAJI');
        $totalPiutangIyus = $cariPengeluaran('PIUTANG IYUS');
        $totalPiutangLain = $cariPengeluaran('PIUTANG LAIN LAIN');
        $totalPrive       = $cariPengeluaran('PRIVE');
        $totalOperasional = $cariPengeluaran(['OP KONVEKSI', 'OPERASIONAL KONVEKSI']); // Support dua penulisan

        $totalPengeluaranRiil = $totalTopupPpob + $totalTopupPulsa + $totalPiutangHaji + $totalPiutangIyus + $totalPiutangLain + $totalPrive + $totalOperasional;

        $summary['total_pengeluaran_riil'] = (int) $totalPengeluaranRiil;
        $summary['total_pindah_saldo']     = (int) $totalPindahSaldo;

        $summary['tarik_edc_admin'] = (int) $tarikEdcAdmin;
        $summary['tarik_mb_admin']  = (int) $tarikMbAdmin;
        $summary['setoran_admin']   = (int) $setoranAdmin;

        // Rangkuman untuk tabel di View (Pastikan key-nya sama dengan yang dipakai di blade)
        $breakdownPengeluaran = [
            'pindah_saldo' => $totalPindahSaldo,
            'topup_ppob'   => $totalTopupPpob,
            'topup_pulsa'  => $totalTopupPulsa,
            'piutang_haji' => $totalPiutangHaji,
            'piutang_iyus' => $totalPiutangIyus,
            'piutang_lain' => $totalPiutangLain,
            'prive'        => $totalPrive,
            'operasional'  => $totalOperasional,
        ];

        // HITUNG UANG MASUK & UANG KELUAR
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
            $prioritasUrutan = ['brangkas' => 1, 'kas' => 2, 'mbanking' => 3, 'ppob' => 4, 'pulsa' => 5];
            $allAccounts = $reports->flatMap->accounts;
            $groupedAccounts = $allAccounts->groupBy('nama_rekening');

            $saldoPerRekening = $groupedAccounts->map(function ($items, $nama) {
                return [
                    'nama'      => $nama,
                    'keterangan'=> $items->first()->keterangan,
                    'saldo_awal'=> $items->sum('saldo_awal'),
                    'saldo'     => $items->sum('saldo_akhir'),
                    'selisih'   => $items->sum('selisih'),
                ];
            })
            ->sortBy(function ($item) use ($prioritasUrutan) {
                $namaBawah = strtolower($item['nama']);
                foreach ($prioritasUrutan as $keyword => $bobot) {
                    if (str_contains($namaBawah, $keyword)) return $bobot;
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

        // ============================================
        // KALKULASI SISTEM (BLOK 2 - TIDAK BERUBAH)
        // ============================================
        $summary['jml_kas_uang'] = $latestReport ? (int) $latestReport->manual_validasi_kas_uang : 0;
        $summary['selisih_kas_uang'] = $summary['jml_kas_uang'] - $summary['jml_penjualan'];

        // ============================================
        // VALIDASI UANG FISIK (REKONSILIASI KAS - BLOK 3)
        // ============================================
        $kasAkhir = $latestReport ? (int) $latestReport->manual_kas_akhir : 0;
        $kasAwal  = $latestReport ? (int) $latestReport->manual_kas_awal : 0;
        $jmlPenjualanNet = $summary['jml_penjualan'];

        // Rumus: (Kas Akhir - Jumlah Penjualan) - Kas Awal
        $summary['selisih_validasi'] = ($kasAkhir - $jmlPenjualanNet) - $kasAwal;
        
        // Simpan nilai-nilai ini untuk ditampilkan di view
        $summary['manual_kas_akhir'] = $kasAkhir;
        $summary['manual_kas_awal'] = $kasAwal;

        $monthlyTrend = $this->buildMonthlyTrend($reports);

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
            'from'               => $fromInput ?? now()->format('Y-m-d'),
            'to'                 => $toInput,
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

    public function destroy(BrilinkReport $report)
    {
        $importId = $report->import_id;
        $report->delete();
        \App\Models\Import::where('id', $importId)->delete();

        return redirect()->route('brilink.index', ['store' => $report->store_id])
            ->with('success', 'Report BRILink berhasil dihapus.');
    }

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

        return array_reverse($result);
    }

    public function pengeluaran(Request $request)
    {
        $storeId = $request->input('store');
        $activeTab = $request->input('tab', 'semua');
        $search = trim($request->input('search', ''));

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

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

        $query = BrilinkPengeluaran::whereHas('report', function($q) use ($storeId, $from, $to) {
            $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
              ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
              ->when($to, fn($sq) => $sq->whereDate('periode_start', '<=', $to));
        })->with('report.store');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                  ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        $allData = $query->get();
        
        $kategoriList = $allData->pluck('kategori')->filter()->unique()->sort()->values();

        if ($activeTab !== 'semua') {
            $allData = $allData->filter(function($item) use ($activeTab) {
                return strtolower(trim($item->kategori)) === strtolower($activeTab);
            });
        }

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
            'manual_kas_akhir' => 'required|string',
            'manual_kas_awal'  => 'required|string',
        ]);

        $kasAkhir = (int) preg_replace('/[^0-9]/', '', $request->input('manual_kas_akhir'));
        $kasAwal  = (int) preg_replace('/[^0-9]/', '', $request->input('manual_kas_awal'));

        $report->update([
            'manual_kas_akhir' => $kasAkhir,
            'manual_kas_awal'  => $kasAwal,
        ]);

        return back()->with('success', 'Validasi uang fisik berhasil diperbarui.');
    }

    public function logPpob(Request $request)
    {
        $storeId = $request->input('store');
        $search = trim($request->input('search', ''));

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

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

        $query = BrilinkPpobTransaction::whereHas('report', function($q) use ($storeId, $from, $to) {
            $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
              ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
              ->when($to, fn($sq) => $sq->whereDate('periode_start', '<=', $to));
        })->with(['report']);

        if (!empty($search)) {
            // Bersihkan format titik dari angka (jika user mengetik "10.000")
            $searchAngka = preg_replace('/[^0-9]/', '', $search);

            $query->where(function($q) use ($search, $searchAngka) {
                $q->where('kategori', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
                  
                // Jika yang diketik adalah angka, cari juga di kolom nominal
                if ($searchAngka !== '') {
                    $q->orWhere('nominal', 'LIKE', "%{$searchAngka}%");
                }
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

        $report = BrilinkReport::firstOrCreate(
            [
                'store_id' => $request->store_id,
                'periode_start' => $request->tanggal,
            ],
            [
                'import_id' => 1,
                'file_name' => 'Input Manual',
                'file_hash' => uniqid(),
                'status'    => 'imported',
            ]
        );

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

            // 🔥 Ambil keterangan dari request (jika ada)
            $keterangan = $request->keterangan ?: 'Input Manual';

            BrilinkPpobTransaction::create([
                'brilink_report_id' => $report->id,
                'kategori'          => $request->kategori,
                'nominal'           => $request->nominal,
                'keterangan'        => $keterangan
            ]);

            $this->recalculateReportPpobTotal($report);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaksi PPOB berhasil ditambahkan.']);
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

        $nominalBersih = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);

        if ($nominalBersih <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

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

            BrilinkPengeluaran::create([
                'brilink_report_id' => $report->id,
                'kategori'          => $request->kategori,
                'keterangan'        => $request->keterangan,
                'nominal_kas'       => $nominalBersih,
            ]);

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

    public function updatePengeluaranAjax(Request $request, BrilinkPengeluaran $pengeluaran)
    {
        $request->validate([
            'kategori'   => 'required|string',
            'keterangan' => 'required|string',
            'nominal'    => 'required',
        ]);

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

    public function destroyPengeluaranAjax(BrilinkPengeluaran $pengeluaran)
    {
        DB::beginTransaction();
        try {
            $report = $pengeluaran->report;
            $keterangan = $pengeluaran->keterangan;

            $pengeluaran->delete();

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

    private function recalculateReportPpobTotal(BrilinkReport $report): void
    {
        $total = $report->ppobItems()->sum('nominal');
        $report->update(['total_omset_ppob' => (int) $total]);
    }

    public function updateLogPpobAjax(Request $request, BrilinkPpobTransaction $ppob)
    {
        $request->validate([
            'kategori' => 'required|string',
            'nominal'  => 'required',
        ]);

        $nominalBersih = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);

        if ($nominalBersih <= 0) {
            return response()->json([
                'message' => 'Nominal harus lebih dari 0.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 🔥 Ambil keterangan, jika kosong gunakan default
            $keterangan = $request->keterangan ?: 'Input Manual';

            $ppob->update([
                'kategori'   => $request->kategori,
                'nominal'    => $nominalBersih,
                'keterangan' => $keterangan,
            ]);

            if ($ppob->report) {
                $this->recalculateReportPpobTotal($ppob->report);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaksi PPOB berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

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

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

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

        // Siapkan wadah pencarian
        $searchAngka = preg_replace('/[^0-9]/', '', $search);
        
        // Jika hasil pembersihan ada isinya, DAN input aslinya tidak mengandung huruf 
        // (kecuali huruf 'R' dan 'p' dari kata Rp), maka anggap pencarian angka.
        // Lebih aman: Jika $searchAngka ada isinya, kita coba cari di keduanya (angka & teks).
        $isNumericSearch = ($searchAngka !== '');

        // Eager load relasi dengan constraint (filter)
        $query = BrilinkReport::with(['setoranItems' => function($q) use ($searchAngka, $isNumericSearch) {
            if ($isNumericSearch) {
                // Filter relasi setoranItems
                $q->whereRaw('CAST(nominal_edc AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                  ->orWhereRaw('CAST(jumlah_setoran_riil AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                  ->orWhereRaw('CAST(administrasi_toko AS CHAR) LIKE ?', ["%{$searchAngka}%"]);
            }
        }])
        ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
        ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to));

        // Filter utama
        if (!empty($search)) {
            $query->where(function($q) use ($search, $searchAngka, $isNumericSearch) {
                // 1. Cari berdasarkan file_name (teks asli)
                $q->where('file_name', 'LIKE', "%{$search}%");
                
                // 2. Jika ada angkanya, cari juga berdasarkan nominal
                if ($isNumericSearch) {
                    $q->orWhereHas('setoranItems', function($sq) use ($searchAngka) {
                        $sq->whereRaw('CAST(nominal_edc AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                           ->orWhereRaw('CAST(jumlah_setoran_riil AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                           ->orWhereRaw('CAST(administrasi_toko AS CHAR) LIKE ?', ["%{$searchAngka}%"]);
                    });
                }
            });
        }

        $reports = $query->orderByDesc('periode_start')->orderByDesc('id')->get();

        // Jika pencarian teks (file_name), relasi setoranItems akan memuat semua datanya.
        // Tapi jika pencarian angka, relasi setoranItems sudah terfilter di atas, jadi aman.
        
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

        $nominalEdc = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal_edc);
        $adminToko  = (int) preg_replace('/[^0-9]/', '', (string) $request->admin_toko);

        if ($nominalEdc <= 0 && $adminToko <= 0) {
            return response()->json(['message' => 'Nominal EDC dan Admin tidak boleh kosong keduanya.'], 422);
        }

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

            BrilinkSetoran::create([
                'brilink_report_id'   => $report->id,
                'nominal_edc'         => $nominalEdc,
                'administrasi_toko'   => $adminToko,
                'jumlah_setoran_riil'=> $nominalEdc,
                'keterangan'          => $request->keterangan,
            ]);

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
                'keterangan'           => $request->keterangan,
            ]);

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

    public function destroyLogSetoranAjax(BrilinkSetoran $setoran)
    {
        DB::beginTransaction();
        try {
            $report = $setoran->report;
            
            $setoran->delete();

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

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;

        if ($from && !$to) $to = $from;
        elseif ($to && !$from) $from = $to;

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // Ambil murni angka dari input (membuang "Rp", titik, spasi, dll)
        $searchAngka = preg_replace('/[^0-9]/', '', $search);
        $isNumericSearch = ($searchAngka !== '');

        // Eager load relasi dengan constraint (filter)
        $query = BrilinkReport::with(['tarikTunaiItems' => function($q) use ($searchAngka, $isNumericSearch) {
            if ($isNumericSearch) {
                // Filter relasi tarikTunaiItems
                $q->whereRaw('CAST(nominal AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                  ->orWhereRaw('CAST(admin_toko AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                  ->orWhereRaw('CAST(kmc AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                  ->orWhereRaw('CAST(saldo_masuk AS CHAR) LIKE ?', ["%{$searchAngka}%"]);
            }
        }])
        ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
        ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to));

        // Filter utama
        if (!empty($search)) {
            $query->where(function($q) use ($search, $searchAngka, $isNumericSearch) {
                // 1. Cari berdasarkan file_name (teks)
                $q->where('file_name', 'LIKE', "%{$search}%");
                
                // 2. Jika ada angkanya, cari juga berdasarkan nominal di relasi
                if ($isNumericSearch) {
                    $q->orWhereHas('tarikTunaiItems', function($sq) use ($searchAngka) {
                        $sq->whereRaw('CAST(nominal AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                           ->orWhereRaw('CAST(admin_toko AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                           ->orWhereRaw('CAST(kmc AS CHAR) LIKE ?', ["%{$searchAngka}%"])
                           ->orWhereRaw('CAST(saldo_masuk AS CHAR) LIKE ?', ["%{$searchAngka}%"]);
                    });
                }
            });
        }

        $reports = $query->orderByDesc('periode_start')->orderByDesc('id')->get();

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

            if ($edcNominal > 0 || $edcAdmin > 0 || $edcKmc > 0) {
                BrilinkTarikTunai::create([
                    'brilink_report_id' => $report->id,
                    'tipe'              => 'edc_bri',
                    'nominal'           => $edcNominal,
                    'admin_toko'        => $edcAdmin,
                    'ket_mesin_edc'     => $request->ket_mesin_edc,
                    'kmc'               => $edcKmc,
                    'saldo_masuk'       => $saldoPindah,
                    'keterangan_rek'    => null,
                    'keterangan'        => $request->keterangan,  
                ]);
            }

            if ($mbNominal > 0 || $mbAdmin > 0) {
                BrilinkTarikTunai::create([
                    'brilink_report_id' => $report->id,
                    'tipe'              => 'mbanking',
                    'nominal'           => $mbNominal,
                    'admin_toko'        => $mbAdmin,
                    'ket_mesin_edc'     => null,
                    'kmc'               => 0,
                    'saldo_masuk'       => $mbSaldo,
                    'keterangan_rek'    => $request->keterangan_rek,
                    'keterangan'        => $request->keterangan, 
                ]);
            }

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

    public function updateLogTarikTunaiAjax(Request $request)
    {
        $request->validate([
            'edc_id' => 'nullable|exists:brilink_tarik_tunai,id',
            'mb_id'  => 'nullable|exists:brilink_tarik_tunai,id',
        ]);

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
                BrilinkTarikTunai::find($request->edc_id)->delete();
            }

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
                BrilinkTarikTunai::find($request->mb_id)->delete();
            }

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

        $fromInput = $request->input('from', now()->format('Y-m-d'));
        $toInput   = $request->input('to');

        $from = $fromInput;
        $to   = $toInput;

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

        $reportsInPeriod = BrilinkReport::with(['ppobItems', 'setoranItems', 'tarikTunaiItems', 'pengeluaranItems'])
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->get();

        $allPpob = $reportsInPeriod->flatMap->ppobItems;

        $penjualanPulsa = $allPpob->filter(fn($i) => stripos($i->kategori, 'Pulsa') !== false)->sum('nominal');
        $penjualanKasirPintar = $allPpob->filter(fn($i) => 
            stripos($i->kategori, 'Kasir Pintar') !== false || 
            stripos($i->kategori, 'Titip Jual') !== false
        )->sum('nominal');
        
        $penjualanPpob = $allPpob->filter(function($i) {
            $kat = strtolower($i->kategori);
            return strpos($kat, 'pulsa') === false 
                && strpos($kat, 'kasir pintar') === false 
                && strpos($kat, 'lain') === false
                && strpos($kat, 'penambahan kas') === false;
        })->sum('nominal');

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
        
        $pemakaianBrangkas = $allPpob
            ->filter(function($i) {
                $isPenambahanKas = stripos($i->kategori, 'Penambahan Kas') !== false;
                $isBrangkas = stripos($i->keterangan, 'brangkas') !== false;
                return $isPenambahanKas && $isBrangkas;
            })
            ->sum('nominal');

        $topupPpob = $allPengeluaran->filter(fn($i) => str_contains(strtolower($i->kategori ?? ''), 'top up ppob') || str_contains(strtolower($i->keterangan ?? ''), 'top up ppob'))->sum('nominal_kas');
        $topupPulsa = $allPengeluaran->filter(fn($i) => str_contains(strtolower($i->kategori ?? ''), 'top up pulsa') || str_contains(strtolower($i->keterangan ?? ''), 'top up pulsa'))->sum('nominal_kas');
        $totalPrive = $allPengeluaran->filter(fn($i) => str_contains(strtolower($i->kategori ?? ''), 'prive'))->sum('nominal_kas');

        $breakdownPrive = $allPengeluaran
            ->filter(fn($i) => str_contains(strtolower($i->kategori ?? ''), 'prive'))
            ->groupBy(fn($i) => strtolower(trim($i->keterangan ?? 'Tanpa keterangan')))
            ->map(function ($items) {
                return [
                    'keterangan' => $items->first()->keterangan ?? 'Tanpa keterangan',
                    'total'      => (int) $items->sum('nominal_kas'),
                    'jumlah'     => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();
        
        $brangkasAwal = $getSaldoAwal($oldestReport, 'Brangkas');
        $brangkasAkhir = $getSaldoAkhir($latestReport, 'Brangkas');

        $dataProfit = [
            'pulsa' => [
                'saldo_awal' => $getSaldoAwal($oldestReport, 'Pulsa'),
                'saldo_akhir' => $getSaldoAkhir($latestReport, 'Pulsa'),
                'penjualan' => $penjualanPulsa,
                'topup' => $topupPulsa,
            ],
            'ppob' => [
                'saldo_awal' => $getSaldoAwal($oldestReport, 'PPOB'),
                'saldo_akhir' => $getSaldoAkhir($latestReport, 'PPOB'),
                'penjualan' => $penjualanPpob,
                'topup' => $topupPpob,
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

        $dataProfit['pulsa']['saldo_terpakai'] = $dataProfit['pulsa']['saldo_awal'] + $dataProfit['pulsa']['topup'] - $dataProfit['pulsa']['saldo_akhir'];
        $dataProfit['pulsa']['profit'] = $dataProfit['pulsa']['penjualan'] - $dataProfit['pulsa']['saldo_terpakai'];

        $dataProfit['ppob']['saldo_terpakai'] = $dataProfit['ppob']['saldo_awal'] + $dataProfit['ppob']['topup'] - $dataProfit['ppob']['saldo_akhir'];
        $dataProfit['ppob']['profit'] = $dataProfit['ppob']['penjualan'] - $dataProfit['ppob']['saldo_terpakai'];

        foreach (['brilink', 'mbanking'] as $key) {
            $dataProfit[$key]['saldo_terpakai'] = $dataProfit[$key]['saldo_awal'] - $dataProfit[$key]['saldo_akhir'];
        }

        $profitPulsa = $dataProfit['pulsa']['profit'];
        $profitPpob = $dataProfit['ppob']['profit'];
        $adminBrilink = $dataProfit['brilink']['pendapatan_admin'];
        $adminMbanking = $dataProfit['mbanking']['pendapatan_admin'];
        $labaKasirPintar = $dataProfit['kasir_pintar']['laba'];

        $totalProfitKotor = $profitPulsa + $profitPpob + $adminBrilink + $adminMbanking + $labaKasirPintar;
        
        $pendapatanHarian = $totalProfitKotor;

        // Pengeluaran harian murni dari prive
        $pengeluaranHarian = $totalPrive;

        // Penambahan saldo = pendapatan - pengeluaran
        $penambahanSaldo = $pendapatanHarian - $pengeluaranHarian;

        // ============================================
        // ✅ HITUNG TOTAL SALDO AWAL & AKHIR (SAMA PERSIS dengan Ringkasan & Saldo)
        // ============================================
        // 1) Ambil REPORT TERAKHIR per toko dalam rentang filter
        $baseReports = BrilinkReport::query()
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('periode_start')
            ->orderByDesc('id')
            ->get();

        $latestReportsPerStore = $baseReports
            ->groupBy('store_id')
            ->map(fn($items) => $items->first())
            ->values();

        // 2) Ambil report paling AWAL per toko (untuk saldo awal)
        $earliestReportsPerStore = $baseReports
            ->groupBy('store_id')
            ->map(fn($items) => $items->last())   // urutan desc, jadi yang paling akhir = paling lama
            ->values();

        $reportIdsForAkhir = $latestReportsPerStore->pluck('id')->all();
        $reportIdsForAwal  = $earliestReportsPerStore->pluck('id')->all();

        // 3) Group by nama_rekening → sum saldo_awal & saldo_akhir
        $accountsAkhir = BrilinkAccount::whereIn('brilink_report_id', $reportIdsForAkhir)->get()
            ->groupBy(fn($a) => mb_strtolower(trim($a->nama_rekening)));

        $accountsAwal = BrilinkAccount::whereIn('brilink_report_id', $reportIdsForAwal)->get()
            ->groupBy(fn($a) => mb_strtolower(trim($a->nama_rekening)));

        $totalSaldoAwal  = (int) $accountsAwal->sum(fn($items) => $items->sum('saldo_awal'));
        $totalSaldoAkhir = (int) $accountsAkhir->sum(fn($items) => $items->sum('saldo_akhir'));

        return view('brilink.profit', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'from' => $fromInput,
            'to' => $toInput,
            'dataProfit' => $dataProfit,
            'totalSaldoAwal' => $totalSaldoAwal,        // ✅ sekarang dari Ringkasan
            'totalSaldoAkhir' => $totalSaldoAkhir,      // ✅ sekarang dari Ringkasan
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
            'breakdownPrive' => $breakdownPrive,
        ]);
    }

    public function saldoKeseluruhan(Request $request)
    {
        $storeId = $request->input('store');
        $search  = trim($request->input('search', ''));

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput;
        $to   = $toInput;

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

        // ============================================================
        // 1) Ambil REPORT TERAKHIR per toko (snapshot tanggal terakhir)
        // ============================================================
        $baseReports = BrilinkReport::query()
            ->with('store')
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('periode_start')
            ->orderByDesc('id')
            ->get();

        // Group per store_id → ambil report teratas di tiap grup (tanggal terbaru)
        $latestReports = $baseReports
            ->groupBy('store_id')
            ->map(fn($items) => $items->first())
            ->values();

        // ============================================================
        // 2) Ambil ACCOUNT hanya dari report-report terpilih
        // ============================================================
        $reportIds = $latestReports->pluck('id')->all();

        $accountsQuery = BrilinkAccount::with('report.store')
            ->whereIn('brilink_report_id', $reportIds);

        if (!empty($search)) {
            $accountsQuery->where(function ($q) use ($search) {
                $q->where('nama_rekening', 'LIKE', "%{$search}%")
                ->orWhere('keterangan',    'LIKE', "%{$search}%");
            });
        }

        $allAccounts = $accountsQuery->get();

        // ============================================================
        // 3) Agregasi rekening: group by nama_rekening
        //    - nominal dijumlahkan (untuk filter 'all' lintas toko)
        //    - nama toko dikumpulkan agar bisa ditampilkan
        // ============================================================
        $aggregated = $allAccounts
            ->groupBy(fn($a) => mb_strtolower(trim($a->nama_rekening)))
            ->map(function ($items) {
                /** @var \Illuminate\Support\Collection $items */
                $first = $items->first();

                // kumpulkan nama toko unik
                $storeNames = $items
                    ->map(fn($a) => $a->report?->store?->nama_toko)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'nama_rekening' => $first->nama_rekening,
                    'keterangan'    => $items->pluck('keterangan')->filter()->first() ?? '-',
                    'saldo_awal'    => (int) $items->sum('saldo_awal'),
                    'saldo_akhir'   => (int) $items->sum('saldo_akhir'),
                    'selisih'       => (int) $items->sum('selisih'),
                    'toko_list'     => $storeNames,
                    'jumlah_toko'   => count($storeNames),
                ];
            })
            ->values()
            // urutkan: BRANGKAS/KAS di atas, lalu sisanya alfabetis (opsional)
            ->sortBy(fn($r) => mb_strtolower($r['nama_rekening']))
            ->values()
            ->all();

        // ============================================================
        // 4) Summary & snapshot info
        // ============================================================
        $summary = [
            'total_saldo_awal'  => (int) collect($aggregated)->sum('saldo_awal'),
            'total_saldo_akhir' => (int) collect($aggregated)->sum('saldo_akhir'),
            'total_selisih'     => (int) collect($aggregated)->sum('selisih'),
            'total_rekening'    => count($aggregated),
        ];

        // Snapshot: nama toko + tanggal report terakhirnya
        $snapshots = $latestReports->map(function ($r) {
            return [
                'store_name'   => $r->store?->nama_toko ?? '-',
                'periode'      => $r->periode_start?->format('d/m/Y') ?? '-',
                'periode_raw'  => $r->periode_start?->format('Y-m-d'),
            ];
        })->values()->all();

        return view('brilink.saldo-keseluruhan', [
            'stores'      => $stores,
            'store'       => $store,
            'storeId'     => $storeId,
            'accounts'    => $aggregated,
            'summary'     => $summary,
            'snapshots'   => $snapshots,
            'search'      => $search,
            'from'        => $fromInput,
            'to'          => $toInput,
        ]);
    }

    public function printInvoicePiutang(BrilinkPengeluaran $pengeluaran)
    {
        $kategori = strtolower($pengeluaran->kategori ?? '');
        if (!str_contains($kategori, 'piutang')) {
            abort(404, 'Transaksi ini bukan kategori Piutang.');
        }

        $pengeluaran->load('report.store');

        $items = collect([[
            'no_invoice' => $this->generateNoInvoicePiutang($pengeluaran),
            'tanggal'    => $pengeluaran->report->periode_start
                                ? $pengeluaran->report->periode_start->format('d/m/Y')
                                : '-',
            'keterangan' => $pengeluaran->keterangan ?? '-',
            'kategori'   => $pengeluaran->kategori ?? '-',
            'nominal'    => (int) $pengeluaran->nominal_kas,
        ]]);

        return $this->renderInvoicePdf($items, $pengeluaran->report->store, 'single');
    }

    public function printInvoicePiutangBulk(Request $request)
    {
        $storeId  = $request->input('store', 'all');
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');
        $search    = trim($request->input('search', ''));

        $kategoriFilter = trim((string) $request->input('kategori', ''));

        $from = $fromInput ?? now()->format('Y-m-d');
        $to   = $toInput;
        if ($from && !$to)     $to = $from;
        elseif ($to && !$from) $from = $to;

        $query = BrilinkPengeluaran::with('report.store')
            ->whereHas('report', function ($q) use ($storeId, $from, $to) {
                $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
                ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
                ->when($to,   fn($sq) => $sq->whereDate('periode_start', '<=', $to));
            });

        if ($kategoriFilter !== '') {
            $query->whereRaw('LOWER(TRIM(kategori)) = ?', [strtolower($kategoriFilter)]);
        } else {
            $query->whereRaw('LOWER(kategori) LIKE ?', ['%piutang%']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('brilink_report_id')->orderBy('id')->get();

        if ($data->isEmpty()) {
            abort(404, 'Tidak ada transaksi ' . ($kategoriFilter ?: 'piutang') . ' pada filter ini.');
        }

        $items = $data->map(function ($row) {
            return [
                'no_invoice' => $this->generateNoInvoicePiutang($row),
                'tanggal'    => $row->report->periode_start
                                    ? $row->report->periode_start->format('d/m/Y')
                                    : '-',
                'keterangan' => $row->keterangan ?? '-',
                'kategori'   => $row->kategori ?? '-',
                'nominal'    => (int) $row->nominal_kas,
            ];
        });

        $store = $storeId === 'all' ? null : ($data->first()->report->store ?? null);

        return $this->renderInvoicePdf($items, $store, 'bulk');
    }

    private function renderInvoicePdf($items, $store, string $mode)
    {
        $total = collect($items)->sum('nominal');

        $pdf = Pdf::loadView('brilink.invoice-piutang-pdf', [
            'items'        => $items,
            'store'        => $store,
            'total'        => $total,
            'mode'         => $mode,
            'tanggalCetak' => now()->format('d/m/Y H:i'),
        ])->setPaper('A4', 'portrait');

        $fileName = 'Invoice-Piutang-'
            . ($mode === 'single'
                ? (collect($items)->first()['no_invoice'] ?? 'x')
                : now()->format('Ymd-His'))
            . '.pdf';

        return $pdf->stream($fileName);
    }

    private function generateNoInvoicePiutang(BrilinkPengeluaran $pengeluaran): string
    {
        $tgl = $pengeluaran->report?->periode_start
            ? $pengeluaran->report->periode_start->format('Ymd')
            : ($pengeluaran->created_at?->format('Ymd') ?? now()->format('Ymd'));

        return 'INV-PTG-' . $tgl . '-' . str_pad((string) $pengeluaran->id, 4, '0', STR_PAD_LEFT);
    }

    public function bayarPiutangSingle(BrilinkPengeluaran $pengeluaran)
    {
        $kategori = strtolower($pengeluaran->kategori ?? '');
        if (!str_contains($kategori, 'piutang')) {
            return response()->json(['message' => 'Transaksi ini bukan piutang.'], 422);
        }

        $pengeluaran->update([
            'is_paid'     => true,
            'paid_at'     => now()->format('Y-m-d'),
            'paid_amount' => $pengeluaran->nominal_kas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Piutang berhasil ditandai lunas.',
        ]);
    }

    /**
     * Bayar bulk piutang dengan FIFO (lunasi dari atas dulu, sisa dipotong ke bawah).
     */
    public function bayarPiutangBulk(Request $request)
    {
        $request->validate([
            'store'    => 'required',
            'kategori' => 'required|string',
            'nominal'  => 'required', // nominal pembayaran (mungkin tidak pas)
            'from'     => 'nullable|date',
            'to'       => 'nullable|date',
            'search'   => 'nullable|string',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        $storeId = $request->store;
        $kategoriFilter = trim($request->kategori);
        $from = $request->from;
        $to = $request->to;
        $search = trim((string) $request->search);

        // Ambil semua piutang yang BELUM lunas dengan kategori sama, urut dari yang terlama
        $query = BrilinkPengeluaran::with('report')
            ->whereRaw('LOWER(TRIM(kategori)) = ?', [strtolower($kategoriFilter)])
            ->where('is_paid', false)
            ->whereHas('report', function ($q) use ($storeId, $from, $to) {
                $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
                ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
                ->when($to, fn($sq) => $sq->whereDate('periode_start', '<=', $to));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        // Urut berdasarkan tanggal report (paling awal dulu = FIFO)
        $piutangs = $query->get()->sortBy(function ($p) {
            return $p->report->periode_start?->format('Y-m-d') . '|' . $p->id;
        })->values();

        if ($piutangs->isEmpty()) {
            return response()->json(['message' => 'Tidak ada piutang yang belum lunas.'], 422);
        }

        $remaining = $nominal;
        $terbayar = 0;
        $lunasCount = 0;
        $partialCount = 0;

        DB::beginTransaction();
        try {
            foreach ($piutangs as $p) {
                if ($remaining <= 0) break;

                $sisaUtang = $p->nominal_kas - $p->paid_amount;
                if ($sisaUtang <= 0) continue;

                $bayar = min($remaining, $sisaUtang);
                $newPaid = $p->paid_amount + $bayar;

                $p->update([
                    'paid_amount' => $newPaid,
                    'is_paid'     => $newPaid >= $p->nominal_kas,
                    'paid_at'     => $newPaid >= $p->nominal_kas ? now()->format('Y-m-d') : $p->paid_at,
                ]);

                $remaining -= $bayar;
                $terbayar += $bayar;

                if ($newPaid >= $p->nominal_kas) {
                    $lunasCount++;
                } else {
                    $partialCount++;
                }
            }

            DB::commit();

            $sisaKembali = $remaining; // kelebihan bayar (kalau ada)
            $msg = "Pembayaran Rp " . number_format($terbayar, 0, ',', '.') .
                " berhasil. {$lunasCount} piutang lunas";
            if ($partialCount > 0) $msg .= ", {$partialCount} piutang dibayar sebagian";
            if ($sisaKembali > 0) $msg .= ". Kelebihan bayar: Rp " . number_format($sisaKembali, 0, ',', '.');

            return response()->json([
                'success' => true,
                'message' => $msg,
                'lunas_count' => $lunasCount,
                'partial_count' => $partialCount,
                'sisa_kembali' => $sisaKembali,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal bayar: ' . $e->getMessage()], 500);
        }
    }

    public function laporanBrangkas(Request $request)
    {
        $storeId   = $request->input('store');
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');
        $isManual  = $request->boolean('manual'); // true jika user pilih sendiri

        $from = $fromInput;
        $to   = $toInput;

        if ($from && !$to)     $to = $from;
        elseif ($to && !$from) $from = $to;

        // ============================================
        // 1) Ambil ID toko yang punya rekening BRANGKAS
        //    (langsung join — terbukti jalan, tidak pakai whereHas)
        // ============================================
        $storesWithBrangkasIds = \App\Models\BrilinkReport::query()
            ->join('brilink_accounts', 'brilink_accounts.brilink_report_id', '=', 'brilink_reports.id')
            ->where(function ($q) {
                // punya baris BRANGKAS dengan saldo > 0
                $q->where(function ($qq) {
                    $qq->where(function ($q3) {
                        $q3->where('brilink_accounts.nama_rekening', 'LIKE', '%BRANGKAS%')
                        ->orWhere('brilink_accounts.nama_rekening', 'LIKE', '%BRANKAS%');
                    })
                    ->where(function ($q3) {
                        $q3->where('brilink_accounts.saldo_awal', '>', 0)
                        ->orWhere('brilink_accounts.saldo_akhir', '>', 0);
                    });
                })
                // ATAU punya penambahan brangkas > 0 di report
                ->orWhere('brilink_reports.penambahan_brangkas', '>', 0);
            })
            ->distinct()
            ->pluck('brilink_reports.store_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $stores = Store::where('marketplace', 'brilink')
            ->whereIn('id', $storesWithBrangkasIds)
            ->orderBy('nama_toko')
            ->get();

        $validStoreIds = $stores->pluck('id')->map(fn($id) => (string) $id)->values()->all();
        $firstStoreId  = $stores->first()?->id;

        // ============================================
        // 2) Tentukan storeId final
        //    ATURAN:
        //    - Jika belum ada input ATAU input 'all' TANPA manual flag
        //      → paksa ke outlet pertama yang punya brangkas
        //    - Jika user pilih manual 'all' (manual=1) → hormati
        //    - Jika input ID tapi tidak valid → fallback ke outlet pertama
        // ============================================
        $storeId = (string) ($storeId ?? '');

        $shouldAutoSelect = false;

        if ($storeId === '' || $storeId === '0') {
            $shouldAutoSelect = true;
        } elseif ($storeId === 'all' && !$isManual) {
            // 'all' hanya dihormati jika user pilih manual
            $shouldAutoSelect = true;
        } elseif ($storeId !== 'all' && !in_array($storeId, $validStoreIds, true)) {
            $shouldAutoSelect = true;
        }

        if ($shouldAutoSelect) {
            $storeId = $firstStoreId ? (string) $firstStoreId : 'all';
        }

        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // ============================================
        // 3) QUERY REPORTS
        // ============================================
        $reports = BrilinkReport::with(['accounts', 'ppobItems', 'store'])
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('periode_start')
            ->orderByDesc('id')
            ->get();

        // ============================================
        // 4) HITUNG PER REPORT (harian)
        // ============================================
        $dailyData = $reports->map(function ($report) {
            $brangkasAwal = (int) $report->accounts
                ->filter(fn($a) => stripos($a->nama_rekening, 'BRANGKAS') !== false)
                ->sum('saldo_awal');

            $penambahan = (int) $report->penambahan_brangkas;

            $pemakaian = (int) $report->ppobItems
                ->filter(function ($item) {
                    $isPenambahanKas = stripos($item->kategori ?? '', 'Penambahan Kas') !== false;
                    $isBrangkas      = stripos($item->keterangan ?? '', 'brangkas') !== false;
                    return $isPenambahanKas && $isBrangkas;
                })
                ->sum('nominal');

            $sisa = $brangkasAwal + $penambahan - $pemakaian;

            $brangkasAkhir = (int) $report->accounts
                ->filter(fn($a) => stripos($a->nama_rekening, 'BRANGKAS') !== false)
                ->sum('saldo_akhir');

            return [
                'id'             => $report->id,
                'store_id'       => $report->store_id,
                'store_name'     => $report->store?->nama_toko ?? '-',
                'file_name'      => $report->file_name,
                'tanggal'        => $report->periode_start,
                'tanggal_label'  => $report->periode_start
                    ? $report->periode_start->translatedFormat('d M Y')
                    : '-',
                'bulan'          => $report->periode_start
                    ? $report->periode_start->format('Y-m')
                    : 'unknown',
                'brangkas_awal'  => $brangkasAwal,
                'penambahan'     => $penambahan,
                'pemakaian'      => $pemakaian,
                'sisa'           => $sisa,
                'brangkas_akhir' => $brangkasAkhir,
                'selisih'        => $brangkasAkhir - $sisa,
            ];
        });

        if ($storeId === 'all') {
            $dailyData = $dailyData->filter(function ($row) {
                return $row['brangkas_awal'] > 0
                    || $row['penambahan'] > 0
                    || $row['pemakaian'] > 0
                    || $row['brangkas_akhir'] > 0;
            })->values();
        }

        // ============================================
        // 5) GROUP PER BULAN
        // ============================================
        $groupedByMonth = $dailyData->groupBy('bulan')->sortKeysDesc();

        $monthlySummary = [];

        foreach ($groupedByMonth as $bulan => $items) {
            $carbon = $bulan !== 'unknown'
                ? Carbon::createFromFormat('Y-m', $bulan)
                : null;

            $sortedAsc = $items->sortBy('tanggal')->values();
            $firstDay  = $sortedAsc->first();
            $lastDay   = $sortedAsc->last();

            $monthlySummary[] = [
                'bulan'             => $bulan,
                'bulan_label'       => $carbon ? $carbon->translatedFormat('F Y') : 'Tanpa Tanggal',
                'bulan_short'       => $carbon ? $carbon->translatedFormat('M Y') : '-',
                'jumlah_hari'       => $items->count(),
                'brangkas_awal'     => $firstDay['brangkas_awal'],
                'total_penambahan'  => $items->sum('penambahan'),
                'total_pemakaian'   => $items->sum('pemakaian'),
                'brangkas_akhir'    => $lastDay['brangkas_akhir'],
                'sisa'              => $lastDay['sisa'],
                'rata_penambahan'   => $items->count() > 0 ? (int) round($items->sum('penambahan') / $items->count()) : 0,
                'rata_pemakaian'    => $items->count() > 0 ? (int) round($items->sum('pemakaian') / $items->count()) : 0,
            ];
        }

        // ============================================
        // 6) SUMMARY
        // ============================================
        $summary = [
            'total_awal'       => $dailyData->sum('brangkas_awal'),
            'total_penambahan' => $dailyData->sum('penambahan'),
            'total_pemakaian'  => $dailyData->sum('pemakaian'),
            'total_sisa'       => $dailyData->sum('sisa'),
            'total_akhir'      => $dailyData->sortBy('tanggal')->last()['brangkas_akhir'] ?? 0,
            'total_hari'       => $dailyData->count(),
            'total_bulan'      => count($monthlySummary),
        ];

        \Log::info('BRANGKAS FINAL', [
            'storesWithBrangkasIds' => $storesWithBrangkasIds,
            'stores_count'          => $stores->count(),
            'storeId_final'         => $storeId,
            'firstStoreId'          => $firstStoreId,
            'reports_count'         => $reports->count(),
        ]);

        return view('brilink.laporan-brangkas', [
            'stores'          => $stores,
            'store'           => $store,
            'storeId'         => (string) $storeId,
            'from'            => $fromInput,
            'to'              => $toInput,
            'dailyData'       => $dailyData->sortBy('tanggal')->values(),
            'groupedByMonth'  => $groupedByMonth,
            'monthlySummary'  => $monthlySummary,
            'summary'         => $summary,
        ]);
    }

    public function pertumbuhanSaldo(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $mode    = $request->input('mode', 'bulanan');

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        $store  = ($storeId !== 'all') ? Store::find($storeId) : null;

        // 1. Ambil semua report sesuai filter
        $reports = BrilinkReport::with('accounts')
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderBy('periode_start', 'asc')
            ->get();

        $data = [];

        // 2. Grouping per Bulan / Tahun
        $groupedByPeriod = $reports->groupBy(function($r) use ($mode) {
            return $mode === 'tahunan' 
                ? $r->periode_start->format('Y') 
                : $r->periode_start->format('Y-m');
        });

        foreach ($groupedByPeriod as $period => $reportsInPeriod) {
            $totalSaldoAwal = 0;
            $totalSaldoAkhir = 0;

            // 3. Jumlahkan SEMUA saldo awal & akhir dari setiap report harian di periode ini
            foreach ($reportsInPeriod as $report) {
                $totalSaldoAwal += $report->accounts->sum('saldo_awal');
                $totalSaldoAkhir += $report->accounts->sum('saldo_akhir');
            }

            // Label
            if ($mode === 'tahunan') {
                $label = 'Tahun ' . $period;
                $labelShort = $period;
            } else {
                $carbon = \Carbon\Carbon::createFromFormat('Y-m', $period);
                $label = $carbon->translatedFormat('F Y');
                $labelShort = $carbon->translatedFormat('M Y');
            }

            $data[] = [
                'periode'    => $period,
                'label'      => $label,
                'label_short'=> $labelShort,
                'saldo_awal' => (int) $totalSaldoAwal,
                'saldo_akhir'=> (int) $totalSaldoAkhir,
                'penambahan' => (int) ($totalSaldoAkhir - $totalSaldoAwal),
                'jumlah_report' => $reportsInPeriod->count(),
            ];
        }

        // Urutkan dari terbaru ke terlama
        usort($data, fn($a, $b) => strcmp($b['periode'], $a['periode']));

        $summary = [
            'total_saldo_awal'  => collect($data)->sum('saldo_awal'),
            'total_saldo_akhir' => collect($data)->sum('saldo_akhir'),
            'total_penambahan'  => collect($data)->sum('penambahan'),
            'total_periode'     => count($data),
        ];

        return view('brilink.pertumbuhan-saldo', [
            'stores'   => $stores,
            'store'    => $store,
            'storeId'  => $storeId,
            'from'     => $from,
            'to'       => $to,
            'mode'     => $mode,
            'data'     => $data,
            'summary'  => $summary,
        ]);
    }
}