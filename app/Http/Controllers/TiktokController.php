<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TiktokLiveSession;
use App\Models\TiktokLiveProduct;
use Illuminate\Support\Facades\DB;
use App\Models\Karyawan;

class TiktokController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));
        $page    = (int) $request->input('page', 1);
        $perPage = 25;

        $stores = Store::where('marketplace', 'tiktok')->orderBy('nama_toko')->get();

        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        // ============ QUERY SALES ============
        $query = Sale::with('product')->where('marketplace', 'tiktok');
        if ($storeId) $query->where('store_id', $storeId);
        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('nama_produk', 'LIKE', "%{$search}%")
                         ->orWhere('sku_internal', 'LIKE', "%{$search}%");
                  });
            });
        }

        $rows = $query->get();

        // ============ RINGKASAN ============
        $summary = [
            'total_rows'    => $rows->count(),
            'net_revenue'   => $rows->sum('net_revenue'),
            'total_hpp'     => $rows->sum(fn($r) => $r->hpp_total ?? 0),
            'profit'        => $rows->sum(fn($r) => $r->profit ?? 0),
            'biaya_komisi'  => $rows->sum('biaya_komisi'),
            'biaya_layanan' => $rows->sum('biaya_layanan'),
            'biaya_ongkir'  => $rows->sum('biaya_ongkir'),
            'biaya_afiliasi'=> $rows->sum('biaya_afiliasi'),
            'biaya_pajak'   => $rows->sum('biaya_pajak'),
            'biaya_lainnya' => $rows->sum('biaya_lainnya'),
        ];

        $summary['transaksi_matched'] = $rows->where('status', 'matched')->count();
        $summary['total_transaksi']   = $rows->count();

        $skuUnik = $rows->pluck('marketplace_sku')->filter()->unique();
        $skuDimapping = $rows->where('status', 'matched')->pluck('marketplace_sku')->filter()->unique();
        $summary['sku_mapped'] = $skuDimapping->count();
        $summary['sku_total']  = $skuUnik->count();

        // ============ PER PRODUK ============
        $allPerProduk = $rows->groupBy('marketplace_sku')->map(function ($group) {
            $first = $group->first();
            return [
                'marketplace_sku' => $first->marketplace_sku,
                'product_id'      => $first->product_id,
                'nama_produk'     => $first->product?->nama_produk ?? '-',
                'internal_sku'    => $first->product?->sku_internal ?? '-',
                'total_qty'       => $group->sum('qty'),
                'net_revenue'     => $group->sum('net_revenue'),
                'hpp_total'       => $group->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'          => $group->sum(fn($r) => $r->profit ?? 0),
                'mapped'          => $first->product_id !== null,
            ];
        })->sortByDesc('profit')->values();

        $totalProduk = $allPerProduk->count();
        $perProduk   = $allPerProduk->slice(($page - 1) * $perPage, $perPage)->values();

        // ============ REKAP BULANAN ============
        $monthlyData = $this->buildMonthlySummary($storeId, $search);

        // ============ TOP PRODUCTS ============
        $topProducts = $this->buildTopProducts($allPerProduk);

        return view('tiktok.index', compact(
            'summary',
            'perProduk',
            'from',
            'to',
            'search',
            'stores',
            'store',
            'storeId',
            'page',
            'perPage',
            'totalProduk',
            'monthlyData',
            'topProducts'
        ));
    }

    public function reconcile($storeId)
    {
        \Artisan::call('tiktok:reconcile', ['store_id' => $storeId]);

        return redirect()->route('tiktok.index', ['store' => $storeId])
            ->with('success', 'Rekonsiliasi TikTok selesai.');
    }

    /**
     * AJAX: Return HTML tabel SKU (untuk pagination).
     */
    public function skuTableHtml(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));
        $page    = (int) $request->input('page', 1);
        $perPage = 25;

        $query = Sale::with('product')->where('marketplace', 'tiktok');
        if ($storeId) $query->where('store_id', $storeId);
        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('nama_produk', 'LIKE', "%{$search}%")
                         ->orWhere('sku_internal', 'LIKE', "%{$search}%");
                  });
            });
        }

        $rows = $query->get();

        $allPerProduk = $rows->groupBy('marketplace_sku')->map(function ($group) {
            $first = $group->first();
            return [
                'marketplace_sku' => $first->marketplace_sku,
                'product_id'      => $first->product_id,
                'nama_produk'     => $first->product?->nama_produk ?? '-',
                'internal_sku'    => $first->product?->sku_internal ?? '-',
                'total_qty'       => $group->sum('qty'),
                'net_revenue'     => $group->sum('net_revenue'),
                'hpp_total'       => $group->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'          => $group->sum(fn($r) => $r->profit ?? 0),
                'mapped'          => $first->product_id !== null,
            ];
        })->sortByDesc('profit')->values();

        $totalProduk = $allPerProduk->count();
        $perProduk   = $allPerProduk->slice(($page - 1) * $perPage, $perPage)->values();

        return view('tiktok.partials.sku-table', compact(
            'perProduk', 'totalProduk', 'page', 'perPage', 'storeId', 'from', 'to', 'search'
        ))->render();
    }

    private function buildTopProducts($allPerProduk): array
    {
        $mapped = $allPerProduk->where('mapped', true);

        return [
            'top_qty'       => $mapped->sortByDesc('total_qty')->take(10)->values(),
            'top_profit'    => $mapped->sortByDesc('profit')->take(10)->values(),
            'bottom_profit' => $mapped->sortBy('profit')->take(10)->values(),
            'insight' => [
                'total_sku'  => $mapped->count(),
                'sku_profit' => $mapped->where('profit', '>', 0)->count(),
                'sku_rugi'   => $mapped->where('profit', '<', 0)->count(),
                'avg_profit' => $mapped->count() > 0 ? $mapped->avg('profit') : 0,
            ],
        ];
    }

    private function buildMonthlySummary($storeId, $search = ''): array
    {
        $query = Sale::where('marketplace', 'tiktok');
        if ($storeId) $query->where('store_id', $storeId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('nama_produk', 'LIKE', "%{$search}%")
                         ->orWhere('sku_internal', 'LIKE', "%{$search}%");
                  });
            });
        }

        $rows = $query->get();

        $grouped = $rows->groupBy(function ($row) {
            return Carbon::parse($row->tanggal)->format('Y-m');
        })->sortKeysDesc()->take(12);

        $result = [];
        $prevProfit = null;

        foreach ($grouped->sortKeys() as $yearMonth => $items) {
            $carbon = Carbon::createFromFormat('Y-m', $yearMonth);

            $profit = $items->sum(fn($r) => $r->profit ?? 0);

            $growth = null;
            if ($prevProfit !== null && $prevProfit != 0) {
                $growth = round((($profit - $prevProfit) / abs($prevProfit)) * 100, 1);
            }
            $prevProfit = $profit;

            $result[] = [
                'year_month'   => $yearMonth,
                'label'        => $carbon->translatedFormat('M Y'),
                'transaksi'    => $items->count(),
                'qty'          => $items->sum('qty'),
                'net_revenue'  => $items->sum('net_revenue'),
                'hpp_total'    => $items->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'       => $profit,
                'growth'       => $growth,
            ];
        }

        $result = array_reverse($result);

        return [
            'months'     => $result,
            'best_month' => collect($result)->sortByDesc('profit')->first(),
        ];
    }

    public function compare(Request $request)
    {
        $storeId = $request->input('store');

        $today = Carbon::today();
        $defaultBFrom = $today->copy()->startOfMonth()->format('Y-m-d');
        $defaultBTo   = $today->copy()->endOfMonth()->format('Y-m-d');
        $defaultAFrom = $today->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $defaultATo   = $today->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $aFrom = $request->input('a_from', $defaultAFrom);
        $aTo   = $request->input('a_to', $defaultATo);
        $bFrom = $request->input('b_from', $defaultBFrom);
        $bTo   = $request->input('b_to', $defaultBTo);

        $stores = Store::where('marketplace', 'tiktok')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        // ============ AMBIL DATA PERIODE A ============
        $queryA = Sale::with('product')->where('marketplace', 'tiktok');
        if ($storeId) $queryA->where('store_id', $storeId);
        $queryA->whereDate('tanggal', '>=', $aFrom)->whereDate('tanggal', '<=', $aTo);
        $rowsA = $queryA->get();

        // ============ AMBIL DATA PERIODE B ============
        $queryB = Sale::with('product')->where('marketplace', 'tiktok');
        if ($storeId) $queryB->where('store_id', $storeId);
        $queryB->whereDate('tanggal', '>=', $bFrom)->whereDate('tanggal', '<=', $bTo);
        $rowsB = $queryB->get();

        // ============ SUMMARY ============
        $summaryA = $this->buildSummary($rowsA);
        $summaryB = $this->buildSummary($rowsB);

        // ============ GROWTH ============
        $growth = [
            'net_revenue' => $this->calcGrowth($summaryA['net_revenue'], $summaryB['net_revenue']),
            'total_hpp'   => $this->calcGrowth($summaryA['total_hpp'], $summaryB['total_hpp']),
            'profit'      => $this->calcGrowth($summaryA['profit'], $summaryB['profit']),
            'transaksi'   => $this->calcGrowth($summaryA['total_rows'], $summaryB['total_rows']),
        ];

        // ============ PER SKU ============
        $skuA = $this->groupBySku($rowsA);
        $skuB = $this->groupBySku($rowsB);

        $allSkus = collect(array_unique(array_merge(array_keys($skuA), array_keys($skuB))));

        $comparison = $allSkus->map(function ($sku) use ($skuA, $skuB) {
            $a = $skuA[$sku] ?? null;
            $b = $skuB[$sku] ?? null;

            $profitA = $a['profit'] ?? 0;
            $profitB = $b['profit'] ?? 0;

            return [
                'sku'             => $sku,
                'nama_produk'     => $b['nama_produk'] ?? $a['nama_produk'] ?? '-',
                'qty_a'           => $a['qty'] ?? 0,
                'qty_b'           => $b['qty'] ?? 0,
                'profit_a'        => $profitA,
                'profit_b'        => $profitB,
                'diff_profit'     => $profitB - $profitA,
                'growth_profit'   => $this->calcGrowth($profitA, $profitB),
                'status'          => $this->getSkuStatus($a, $b),
            ];
        })->sortByDesc(fn($item) => abs($item['diff_profit']))->values();

        $skuBaru   = $comparison->where('status', 'baru')->sortByDesc('profit_b')->take(5)->values();
        $skuHilang = $comparison->where('status', 'hilang')->sortByDesc('profit_a')->take(5)->values();

        return view('tiktok.compare', compact(
            'stores', 'store', 'storeId',
            'aFrom', 'aTo', 'bFrom', 'bTo',
            'summaryA', 'summaryB', 'growth',
            'comparison', 'skuBaru', 'skuHilang'
        ));
    }

    /**
     * Export PDF Laporan TikTok.
     */
    public function exportPdf(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));

        $stores = Store::where('marketplace', 'tiktok')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        // Query sama seperti index
        $query = Sale::with('product')->where('marketplace', 'tiktok');
        if ($storeId) $query->where('store_id', $storeId);
        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('nama_produk', 'LIKE', "%{$search}%")
                        ->orWhere('sku_internal', 'LIKE', "%{$search}%");
                });
            });
        }

        $rows = $query->get();

        $summary = $this->buildSummary($rows);
        $summary['biaya_komisi']  = $rows->sum('biaya_komisi');
        $summary['biaya_layanan'] = $rows->sum('biaya_layanan');
        $summary['biaya_ongkir']  = $rows->sum('biaya_ongkir');
        $summary['biaya_afiliasi']= $rows->sum('biaya_afiliasi');
        $summary['biaya_pajak']   = $rows->sum('biaya_pajak');
        $summary['biaya_lainnya'] = $rows->sum('biaya_lainnya');
        $summary['transaksi_matched'] = $rows->where('status', 'matched')->count();
        $summary['total_transaksi']   = $rows->count();

        $skuUnik = $rows->pluck('marketplace_sku')->filter()->unique();
        $skuDimapping = $rows->where('status', 'matched')->pluck('marketplace_sku')->filter()->unique();
        $summary['sku_mapped'] = $skuDimapping->count();
        $summary['sku_total']  = $skuUnik->count();

        $perProduk = $rows->groupBy('marketplace_sku')->map(function ($group) {
            $first = $group->first();
            return [
                'marketplace_sku' => $first->marketplace_sku,
                'product_id'      => $first->product_id,
                'nama_produk'     => $first->product?->nama_produk ?? '-',
                'internal_sku'    => $first->product?->sku_internal ?? '-',
                'total_qty'       => $group->sum('qty'),
                'net_revenue'     => $group->sum('net_revenue'),
                'hpp_total'       => $group->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'          => $group->sum(fn($r) => $r->profit ?? 0),
                'mapped'          => $first->product_id !== null,
            ];
        })->sortByDesc('profit')->values();

        $data = [
            'store'        => $store,
            'from'         => $from,
            'to'           => $to,
            'search'       => $search,
            'summary'      => $summary,
            'perProduk'    => $perProduk,
            'generated_at' => now()->translatedFormat('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('tiktok.pdf.report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'sans-serif',
            ]);

        $fileName = 'laporan-tiktok-' . ($store?->nama_toko ?? 'all') . '-' . now()->format('Ymd-His') . '.pdf';
        $fileName = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $fileName);

        return $pdf->download($fileName);
    }

    // ============ HELPERS ============
    private function buildSummary($rows): array
    {
        return [
            'total_rows'  => $rows->count(),
            'net_revenue' => $rows->sum('net_revenue'),
            'total_hpp'   => $rows->sum(fn($r) => $r->hpp_total ?? 0),
            'profit'      => $rows->sum(fn($r) => $r->profit ?? 0),
        ];
    }

    private function calcGrowth($valueA, $valueB): ?float
    {
        if ($valueA == 0 && $valueB == 0) return 0;
        if ($valueA == 0) return null;
        return round((($valueB - $valueA) / abs($valueA)) * 100, 1);
    }

    private function groupBySku($rows): array
    {
        $grouped = [];
        foreach ($rows->groupBy('marketplace_sku') as $sku => $items) {
            $first = $items->first();
            $grouped[$sku] = [
                'sku'         => $sku,
                'nama_produk' => $first->product?->nama_produk ?? '-',
                'qty'         => $items->sum('qty'),
                'net_revenue' => $items->sum('net_revenue'),
                'hpp_total'   => $items->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'      => $items->sum(fn($r) => $r->profit ?? 0),
            ];
        }
        return $grouped;
    }

    private function getSkuStatus($a, $b): string
    {
        if ($a === null && $b !== null) return 'baru';
        if ($a !== null && $b === null) return 'hilang';
        return 'tetap';
    }

    public function skuDetailHtml(Request $request)
    {
        $storeId = $request->input('store');
        $sku     = $request->input('sku');
        $from    = $request->input('from');
        $to      = $request->input('to');

        $query = Sale::where('marketplace', 'tiktok')
                     ->where('marketplace_sku', $sku);

        if ($storeId) $query->where('store_id', $storeId);
        if ($from)    $query->whereDate('tanggal', '>=', $from);
        if ($to)      $query->whereDate('tanggal', '<=', $to);

        $sales = $query->orderBy('tanggal', 'desc')->get();

        return view('tiktok.partials.sku-detail-modal', compact('sales', 'sku'))->render();
    }

    public function liveIndex(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $search = trim($request->input('search', ''));
        $from = $request->input('from');
        $to = $request->input('to');

        $stores = Store::where('marketplace', 'tiktok')->orderBy('nama_toko')->get();
        
        if (!$storeId) $storeId = 'all';
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        $query = TiktokLiveSession::with(['store', 'karyawan', 'products'])
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to));

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                ->orWhereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $liveSessions = $query->orderByDesc('tanggal')->orderByDesc('id')->get();
        
        // Ambil semua karyawan untuk dropdown modal (termasuk yang tidak aktif untuk history)
        $karyawans = Karyawan::orderBy('nama')->get();

        return view('tiktok.live', compact('liveSessions', 'stores', 'storeId', 'store', 'search', 'from', 'to', 'karyawans'));
    }

    public function liveStoreAjax(Request $request)
    {
        $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'total_transaksi' => 'required',
            'produk' => 'nullable|array',
            'produk.*.nama_produk' => 'required|string',
            'produk.*.ukuran' => 'nullable|string',
            'produk.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $session = TiktokLiveSession::create([
                'store_id' => $request->store_id,
                'karyawan_id' => $request->karyawan_id,
                'tanggal' => $request->tanggal,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'total_transaksi' => (int) $request->total_transaksi,
                'keterangan' => $request->keterangan,
            ]);

            if ($request->has('produk')) {
                foreach ($request->produk as $p) {
                    $session->products()->create([
                        'nama_produk' => $p['nama_produk'],
                        'ukuran' => $p['ukuran'] ?? null,
                        'qty' => $p['qty'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sesi Live berhasil disimpan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    public function liveUpdateAjax(Request $request, TiktokLiveSession $session)
    {
        $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'total_transaksi' => 'required',
            'produk' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $session->update([
                'store_id' => $request->store_id,
                'karyawan_id' => $request->karyawan_id,
                'tanggal' => $request->tanggal,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'total_transaksi' => (int) $request->total_transaksi,
                'keterangan' => $request->keterangan,
            ]);

            // Hapus produk lama, ganti dengan yang baru
            $session->products()->delete();

            if ($request->has('produk')) {
                foreach ($request->produk as $p) {
                    $session->products()->create([
                        'nama_produk' => $p['nama_produk'],
                        'ukuran' => $p['ukuran'] ?? null,
                        'qty' => $p['qty'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sesi Live berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    public function liveDestroyAjax(TiktokLiveSession $session)
    {
        $session->delete();
        return response()->json(['success' => true, 'message' => 'Sesi Live berhasil dihapus.']);
    }
}