<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ShopeeController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));
        $page    = (int) $request->input('page', 1);
        $perPage = 25;

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();

        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }

        $store = $storeId ? Store::find($storeId) : null;

        // ============ QUERY SALES ============
        $query = Sale::with('product')->where('marketplace', 'shopee');
        if ($storeId) $query->where('store_id', $storeId);
        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);

        // Filter search
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
            'biaya_promosi'  => $rows->sum('biaya_promosi'),
            'biaya_pajak'   => $rows->sum('biaya_pajak'),
            'biaya_lainnya' => $rows->sum('biaya_lainnya'),
        ];

        $summary['transaksi_matched'] = $rows->where('status', 'matched')->count();
        $summary['total_transaksi']   = $rows->count();

        $skuUnik = $rows->pluck('marketplace_sku')->filter()->unique();
        $skuDimapping = $rows->where('status', 'matched')
            ->pluck('marketplace_sku')
            ->filter()
            ->unique();

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
        // Filter search juga diterapkan di monthly summary
        $monthlyData = $this->buildMonthlySummary($storeId, $search);

        // ============ TOP PRODUCTS ============
        $topProducts = $this->buildTopProducts($allPerProduk);

        return view('shopee.index', compact(
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

    /**
     * Top 10 produk berdasarkan Qty, Profit, dan Bottom Profit.
     */
    private function buildTopProducts($allPerProduk): array
    {
        // Filter hanya SKU yang mapped (punya HPP & profit valid)
        $mapped = $allPerProduk->where('mapped', true);

        $topByQty = $mapped->sortByDesc('total_qty')->take(10)->values();
        $topByProfit = $mapped->sortByDesc('profit')->take(10)->values();
        $bottomByProfit = $mapped->sortBy('profit')->take(10)->values();

        // Ringkasan insight
        $totalSku = $mapped->count();
        $skuProfit = $mapped->where('profit', '>', 0)->count();
        $skuRugi   = $mapped->where('profit', '<', 0)->count();
        $avgProfit = $totalSku > 0 ? $mapped->avg('profit') : 0;

        return [
            'top_qty'       => $topByQty,
            'top_profit'    => $topByProfit,
            'bottom_profit' => $bottomByProfit,
            'insight' => [
                'total_sku'  => $totalSku,
                'sku_profit' => $skuProfit,
                'sku_rugi'   => $skuRugi,
                'avg_profit' => $avgProfit,
            ],
        ];
    }

    /**
     * Rekap per bulan (12 bulan terakhir).
     */
    private function buildMonthlySummary($storeId, $search = ''): array
    {
        $query = Sale::where('marketplace', 'shopee');
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
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
            return \Carbon\Carbon::parse($row->tanggal)->format('Y-m');
        })->sortKeysDesc()->take(12);

        $result = [];
        $prevProfit = null;

        foreach ($grouped->sortKeys() as $yearMonth => $items) {
            $carbon = \Carbon\Carbon::createFromFormat('Y-m', $yearMonth);

            $netRevenue = $items->sum('net_revenue');
            $hppTotal   = $items->sum(fn($r) => $r->hpp_total ?? 0);
            $profit     = $items->sum(fn($r) => $r->profit ?? 0);
            $qty        = $items->sum('qty');
            $transaksi  = $items->count();

            $growth = null;
            if ($prevProfit !== null && $prevProfit != 0) {
                $growth = round((($profit - $prevProfit) / abs($prevProfit)) * 100, 1);
            }
            $prevProfit = $profit;

            $result[] = [
                'year_month'   => $yearMonth,
                'label'        => $carbon->translatedFormat('M Y'),
                'label_short'  => $carbon->translatedFormat('M'),
                'transaksi'    => $transaksi,
                'qty'          => $qty,
                'net_revenue'  => $netRevenue,
                'hpp_total'    => $hppTotal,
                'profit'       => $profit,
                'growth'       => $growth,
            ];
        }

        $result = array_reverse($result);

        $bestMonth = collect($result)->sortByDesc('profit')->first();

        return [
            'months'      => $result,
            'best_month'  => $bestMonth,
            'total_profit' => collect($result)->sum('profit'),
            'total_net'    => collect($result)->sum('net_revenue'),
        ];
    }

    public function reconcile($storeId)
    {
        \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);

        return redirect()->route('shopee.index', ['store' => $storeId])
            ->with('success', 'Rekonsiliasi selesai.');
    }

     public function compare(Request $request)
    {
        $storeId = $request->input('store');

        // Default: bulan ini vs bulan lalu
        $today = Carbon::today();
        $defaultBFrom = $today->copy()->startOfMonth()->format('Y-m-d');
        $defaultBTo   = $today->copy()->endOfMonth()->format('Y-m-d');
        $defaultAFrom = $today->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $defaultATo   = $today->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $aFrom = $request->input('a_from', $defaultAFrom);
        $aTo   = $request->input('a_to', $defaultATo);
        $bFrom = $request->input('b_from', $defaultBFrom);
        $bTo   = $request->input('b_to', $defaultBTo);

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        // ============ AMBIL DATA PERIODE A ============
        $queryA = Sale::with('product')->where('marketplace', 'shopee');
        if ($storeId) $queryA->where('store_id', $storeId);
        $queryA->whereDate('tanggal', '>=', $aFrom)->whereDate('tanggal', '<=', $aTo);
        $rowsA = $queryA->get();

        // ============ AMBIL DATA PERIODE B ============
        $queryB = Sale::with('product')->where('marketplace', 'shopee');
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

        // Gabungkan semua SKU unik
        $allSkus = collect(array_unique(array_merge(
            array_keys($skuA),
            array_keys($skuB)
        )));

        $comparison = $allSkus->map(function ($sku) use ($skuA, $skuB) {
            $a = $skuA[$sku] ?? null;
            $b = $skuB[$sku] ?? null;

            $profitA = $a['profit'] ?? 0;
            $profitB = $b['profit'] ?? 0;
            $diffProfit = $profitB - $profitA;

            return [
                'sku'             => $sku,
                'nama_produk'     => $b['nama_produk'] ?? $a['nama_produk'] ?? '-',
                'qty_a'           => $a['qty'] ?? 0,
                'qty_b'           => $b['qty'] ?? 0,
                'net_revenue_a'   => $a['net_revenue'] ?? 0,
                'net_revenue_b'   => $b['net_revenue'] ?? 0,
                'profit_a'        => $profitA,
                'profit_b'        => $profitB,
                'diff_profit'     => $diffProfit,
                'growth_profit'   => $this->calcGrowth($profitA, $profitB),
                'status'          => $this->getSkuStatus($a, $b),
            ];
        })->sortByDesc(function ($item) {
            return abs($item['diff_profit']);
        })->values();

        // ============ SKU BARU & HILANG ============
        $skuBaru = $comparison->where('status', 'baru')->sortByDesc('profit_b')->take(5)->values();
        $skuHilang = $comparison->where('status', 'hilang')->sortByDesc('profit_a')->take(5)->values();

        return view('shopee.compare', compact(
            'stores', 'store', 'storeId',
            'aFrom', 'aTo', 'bFrom', 'bTo',
            'summaryA', 'summaryB', 'growth',
            'comparison', 'skuBaru', 'skuHilang'
        ));
    }

    /**
     * Build summary dari collection sales.
     */
    private function buildSummary($rows): array
    {
        return [
            'total_rows'  => $rows->count(),
            'net_revenue' => $rows->sum('net_revenue'),
            'total_hpp'   => $rows->sum(fn($r) => $r->hpp_total ?? 0),
            'profit'      => $rows->sum(fn($r) => $r->profit ?? 0),
        ];
    }

    /**
     * Hitung growth % dari nilai A ke B.
     */
    private function calcGrowth($valueA, $valueB): ?float
    {
        if ($valueA == 0 && $valueB == 0) return 0;
        if ($valueA == 0) return null; // tidak bisa hitung growth dari 0
        return round((($valueB - $valueA) / abs($valueA)) * 100, 1);
    }

    /**
     * Group sales by SKU.
     */
    private function groupBySku($rows): array
    {
        $grouped = [];
        foreach ($rows->groupBy('marketplace_sku') as $sku => $items) {
            $first = $items->first();
            $grouped[$sku] = [
                'sku'          => $sku,
                'nama_produk'  => $first->product?->nama_produk ?? '-',
                'qty'          => $items->sum('qty'),
                'net_revenue'  => $items->sum('net_revenue'),
                'hpp_total'    => $items->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'       => $items->sum(fn($r) => $r->profit ?? 0),
            ];
        }
        return $grouped;
    }

    /**
     * Tentukan status SKU: baru / hilang / tetap.
     */
    private function getSkuStatus($a, $b): string
    {
        if ($a === null && $b !== null) return 'baru';
        if ($a !== null && $b === null) return 'hilang';
        return 'tetap';
    }

    /**
     * AJAX: Return HTML tabel SKU (untuk pagination tanpa reload).
     */
    public function skuTableHtml(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));
        $page    = (int) $request->input('page', 1);
        $perPage = 25;

        $query = Sale::with('product')->where('marketplace', 'shopee');
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

        return view('shopee.partials.sku-table', compact(
            'perProduk', 'totalProduk', 'page', 'perPage', 'storeId', 'from', 'to', 'search'
        ))->render();
    }

    public function exportPdf(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        // ============ QUERY SALES ============
        $query = Sale::with('product')->where('marketplace', 'shopee');
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

        // ============ SUMMARY ============
        $summary = [
            'total_rows'    => $rows->count(),
            'net_revenue'   => $rows->sum('net_revenue'),
            'total_hpp'     => $rows->sum(fn($r) => $r->hpp_total ?? 0),
            'profit'        => $rows->sum(fn($r) => $r->profit ?? 0),
            'biaya_komisi'  => $rows->sum('biaya_komisi'),
            'biaya_layanan' => $rows->sum('biaya_layanan'),
            'biaya_ongkir'  => $rows->sum('biaya_ongkir'),
            'biaya_afiliasi'=> $rows->sum('biaya_afiliasi'),
            'biaya_promosi'  => $rows->sum('biaya_promosi'),
            'biaya_pajak'   => $rows->sum('biaya_pajak'),
            'biaya_lainnya' => $rows->sum('biaya_lainnya'),
        ];

        $summary['transaksi_matched'] = $rows->where('status', 'matched')->count();
        $summary['total_transaksi']   = $rows->count();

        $skuUnik = $rows->pluck('marketplace_sku')->filter()->unique();
        $skuDimapping = $rows->where('status', 'matched')->pluck('marketplace_sku')->filter()->unique();
        $summary['sku_mapped'] = $skuDimapping->count();
        $summary['sku_total']  = $skuUnik->count();

        // ============ PER PRODUK (SEMUA, TANPA PAGINATION) ============
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

        // ============ DATA UNTUK PDF ============
        $data = [
            'store'         => $store,
            'from'          => $from,
            'to'            => $to,
            'search'        => $search,
            'summary'       => $summary,
            'perProduk'     => $perProduk,
            'generated_at'  => now()->translatedFormat('d F Y, H:i'),
        ];

        // ============ GENERATE PDF ============
        $pdf = Pdf::loadView('shopee.pdf.report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'sans-serif',
            ]);

        $fileName = 'laporan-shopee-' . ($store?->nama_toko ?? 'all') . '-' . now()->format('Ymd-His') . '.pdf';
        $fileName = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $fileName);

        return $pdf->download($fileName);
    }

    public function skuDetailHtml(Request $request)
    {
        $storeId = $request->input('store');
        $sku     = $request->input('sku');
        $from    = $request->input('from');
        $to      = $request->input('to');

        $query = Sale::where('marketplace', 'shopee')
                     ->where('marketplace_sku', $sku);

        if ($storeId) $query->where('store_id', $storeId);
        if ($from)    $query->whereDate('tanggal', '>=', $from);
        if ($to)      $query->whereDate('tanggal', '<=', $to);

        $sales = $query->orderBy('tanggal', 'desc')->get();

        return view('shopee.partials.sku-detail-modal', compact('sales', 'sku'))->render();
    }
}