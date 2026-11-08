<?php

namespace App\Services;

use App\Http\Controllers\BrilinkController;
use App\Models\Product;
use App\Models\Sale;
use App\Models\WebOrderItemSnapshot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AllProfitReportService
{
    public function __construct(private SkuResolver $skuResolver) {}

    public function build(string $from, string $to): array
    {
        $from = Carbon::parse($from)->toDateString();
        $to   = Carbon::parse($to)->toDateString();

        // 1. Ambil Summary Total & Data Harian dari BrilinkController (SEKALI SAJA)
        $brilinkData = $this->getBrilinkData($from, $to);

        // 2. Ambil Summary Total Sumber Lain
        $summary = [
            'shopee'  => $this->aggregateMarketplace('shopee', $from, $to),
            'tiktok'  => $this->aggregateMarketplace('tiktok', $from, $to),
            'web'     => $this->aggregateWeb($from, $to),
            'brilink' => $brilinkData['summary'],
        ];

        // Grand Total
        $total = [
            'revenue' => $summary['shopee']['revenue'] + $summary['tiktok']['revenue'] + $summary['web']['revenue'] + $summary['brilink']['revenue'],
            'hpp'     => $summary['shopee']['hpp']     + $summary['tiktok']['hpp']     + $summary['web']['hpp']     + $summary['brilink']['hpp'],
            'profit'  => $summary['shopee']['profit']  + $summary['tiktok']['profit']  + $summary['web']['profit']  + $summary['brilink']['profit'],
            'count'   => $summary['shopee']['count']   + $summary['tiktok']['count']   + $summary['web']['count']   + $summary['brilink']['count'],
        ];
        $total['margin'] = $this->margin($total['revenue'], $total['profit']);

        return [
            'summary' => $summary,
            'total'   => $total,
            'monthly' => $this->buildMonthly($from, $to),
            'daily'   => $this->buildDaily($from, $to, $brilinkData['daily']),
        ];
    }

    private function aggregateMarketplace(string $marketplace, string $from, string $to): array
    {
        $rows = Sale::where('marketplace', $marketplace)
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->get(['net_revenue', 'hpp_total', 'profit']);

        $revenue = (int) $rows->sum('net_revenue');
        $hpp     = (int) $rows->sum(fn($r) => $r->hpp_total ?? 0);
        $profit  = (int) $rows->sum(fn($r) => $r->profit ?? 0);

        return [
            'revenue' => $revenue,
            'hpp'     => $hpp,
            'profit'  => $profit,
            'margin'  => $this->margin($revenue, $profit),
            'count'   => $rows->count(),
        ];
    }

    private function aggregateWeb(string $from, string $to): array
    {
        $items = WebOrderItemSnapshot::query()
            ->join('web_orders_snapshot as o', 'o.web_order_id', '=', 'web_order_items_snapshot.web_order_id')
            ->where('o.status', 'delivered')
            ->whereDate('o.web_created_at', '>=', $from)
            ->whereDate('o.web_created_at', '<=', $to)
            ->get([
                'web_order_items_snapshot.web_order_id',
                'web_order_items_snapshot.sku',
                'web_order_items_snapshot.quantity',
                'web_order_items_snapshot.subtotal',
            ]);

        if ($items->isEmpty()) {
            return ['revenue' => 0, 'hpp' => 0, 'profit' => 0, 'margin' => 0, 'count' => 0];
        }

        $uniqueSkus   = $items->pluck('sku')->filter()->unique()->values()->all();
        $skuToProduct = $this->skuResolver->resolveMany($uniqueSkus);
        $productIds   = array_values(array_filter($skuToProduct));

        $products = empty($productIds)
            ? collect()
            : Product::whereIn('id', array_unique($productIds))->get(['id', 'hpp'])->keyBy('id');

        $revenue = 0;
        $hpp     = 0;
        $profit  = 0;

        foreach ($items as $item) {
            $qty      = (int) $item->quantity;
            $subtotal = (int) $item->subtotal;
            $pid      = $item->sku ? ($skuToProduct[$item->sku] ?? null) : null;
            $hppItem  = $pid && ($p = $products->get($pid)) ? ((int) $p->hpp * $qty) : 0;

            $revenue += $subtotal;
            $hpp     += $hppItem;
            $profit  += ($subtotal - $hppItem);
        }

        $orderCount = $items->pluck('web_order_id')->unique()->count();

        return [
            'revenue' => $revenue,
            'hpp'     => $hpp,
            'profit'  => $profit,
            'margin'  => $this->margin($revenue, $profit),
            'count'   => $orderCount,
        ];
    }

    /**
     * Mengambil Summary Total & Data Harian BRILink dari Controller
     */
    private function getBrilinkData(string $from, string $to): array
    {
        $request = new Request([
            'from' => $from,
            'to' => $to,
            'store' => 'all'
        ]);

        try {
            $controller = app(BrilinkController::class);
            $response = $controller->profit($request);
            $data = $response->getData(); 

            // --- SUMMARY TOTAL ---
            $profitBersih = (int) ($data['penambahanSaldo'] ?? 0);
            $totalProfitKotor = (int) ($data['totalProfitKotor'] ?? 0);
            $totalPrive = (int) ($data['totalPrive'] ?? 0);
            
            // ✅ Ambil Volume Transaksi Agen dari database
            // Controller tidak mengirim variabel volume langsung, jadi kita hitung dari database
            // menggunakan query yang sama persis dengan BrilinkController
            $totalVolume = \App\Models\BrilinkReport::when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
                ->sum('volume_transaksi_agen');

            $summary = [
                'revenue' => $totalProfitKotor,
                'hpp'     => $totalPrive,
                'profit'  => $profitBersih,
                'margin'  => $this->margin($totalProfitKotor, $profitBersih),
                'count'   => (int) $totalVolume, // ✅ Sekarang menggunakan Volume Transaksi Agen
            ];

            // --- DATA HARIAN ---
            $daily = [];
            if (isset($data['dailyProfit']) && is_array($data['dailyProfit'])) {
                foreach ($data['dailyProfit'] as $d) {
                    // Hanya ambil yang ada transaksinya
                    if ($d['pendapatan'] != 0 || $d['prive'] != 0) {
                        $daily[$d['tanggal']] = $d['profit_bersih'];
                    }
                }
            }

            return ['summary' => $summary, 'daily' => $daily];

        } catch (\Throwable $e) {
            return [
                'summary' => ['revenue' => 0, 'hpp' => 0, 'profit' => 0, 'margin' => 0, 'count' => 0],
                'daily' => []
            ];
        }
    }

    private function buildMonthly(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfMonth();
        $end   = Carbon::parse($to)->endOfMonth();

        $result = [];

        while ($start->lte($end)) {
            $mFrom = $start->copy()->startOfMonth()->toDateString();
            $mTo   = $start->copy()->endOfMonth()->toDateString();
            if ($mTo > $to) $mTo = $to;
            if ($mFrom < $from) $mFrom = $from;

            $shopee  = $this->aggregateMarketplace('shopee', $mFrom, $mTo);
            $tiktok  = $this->aggregateMarketplace('tiktok', $mFrom, $mTo);
            $web     = $this->aggregateWeb($mFrom, $mTo);
            
            // Panggil BrilinkData per bulan
            $brilinkData = $this->getBrilinkData($mFrom, $mTo);
            $brilink = $brilinkData['summary'];

            $totalProfit  = $shopee['profit']  + $tiktok['profit']  + $web['profit']  + $brilink['profit'];

            $result[] = [
                'month'   => $start->format('Y-m'),
                'label'   => $start->translatedFormat('M Y'),
                'shopee'  => $shopee['profit'],
                'tiktok'  => $tiktok['profit'],
                'web'     => $web['profit'],
                'brilink' => $brilink['profit'],
                'total'   => $totalProfit,
                'revenue' => $shopee['revenue'] + $tiktok['revenue'] + $web['revenue'] + $brilink['revenue'],
                'margin'  => 0,
            ];

            $start->addMonth();
        }

        return $result;
    }

        private function buildDaily(string $from, string $to, array $brilinkDaily = []): array
    {
        $start = Carbon::parse($from);
        $end   = Carbon::parse($to);

        $result = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dFrom = $cursor->toDateString();
            $dTo   = $cursor->toDateString();

            $shopee  = $this->aggregateMarketplace('shopee', $dFrom, $dTo);
            $tiktok  = $this->aggregateMarketplace('tiktok', $dFrom, $dTo);
            $web     = $this->aggregateWeb($dFrom, $dTo);
            
            // ✅ Ambil profit BRILink dari array yang sudah disiapkan
            $brilinkProfit = $brilinkDaily[$dFrom] ?? 0;

            $totalProfit = $shopee['profit'] + $tiktok['profit'] + $web['profit'] + $brilinkProfit;

            // ✅ TAMPILKAN BARIS JIKA ADA TRANSAKSI (count > 0) ATAU ADA PROFIT
            if (
                $shopee['count'] > 0 || 
                $tiktok['count'] > 0 || 
                $web['count'] > 0 || 
                $brilinkProfit != 0
            ) {
                $result[] = [
                    'date'    => $dFrom,
                    'label'   => $cursor->translatedFormat('d M Y'),
                    'shopee'  => $shopee['profit'],
                    'tiktok'  => $tiktok['profit'],
                    'web'     => $web['profit'],
                    'brilink' => $brilinkProfit,
                    'total'   => $totalProfit,
                ];
            }

            $cursor->addDay();
        }

        return $result;
    }

    private function margin(int $revenue, int $profit): float
    {
        return $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0;
    }
}