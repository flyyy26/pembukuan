<?php

namespace App\Services;

use App\Models\Product;
use App\Models\WebOrderItemSnapshot;
use Illuminate\Support\Collection;

class WebSalesReportService
{
    public function __construct(private SkuResolver $skuResolver) {}

    /**
     * Hitung total HPP & profit untuk rentang tanggal + filter yang sama
     * dengan query order di controller.
     *
     * @return array{
     *   total_hpp: int,
     *   total_profit: int,
     *   margin_pct: float,
     *   per_order: array<int, array{hpp:int, profit:int}>,
     *   unresolved_count: int,
     *   unresolved_revenue: int
     * }
     */
    public function buildProfitSummary(
        string $startDate,
        string $endDate,
        ?string $search = null,
        ?string $paymentStatus = null
    ): array {
        $itemsQuery = WebOrderItemSnapshot::query()
            ->join('web_orders_snapshot as o', 'o.web_order_id', '=', 'web_order_items_snapshot.web_order_id')
            ->where('o.status', 'delivered')
            ->whereBetween('o.web_created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);

        if ($search) {
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('o.order_number', 'like', "%{$search}%")
                  ->orWhere('o.customer_name', 'like', "%{$search}%")
                  ->orWhere('o.customer_email', 'like', "%{$search}%");
            });
        }

        if ($paymentStatus && $paymentStatus !== 'all') {
            $itemsQuery->where('o.payment_status', $paymentStatus);
        }

        $items = $itemsQuery->get([
            'web_order_items_snapshot.web_order_id',
            'web_order_items_snapshot.sku',
            'web_order_items_snapshot.quantity',
            'web_order_items_snapshot.subtotal',
        ]);

        return $this->computeFromItems($items);
    }

    /**
     * Hitung HPP & profit per order untuk kumpulan order yang sudah di-paginate.
     * Hanya query item untuk web_order_id yang tampil di halaman.
     *
     * @param  Collection<int, \App\Models\WebOrderSnapshot>  $orders
     * @return array<int, array{hpp:int, profit:int}>
     */
    public function profitPerOrder(Collection $orders): array
    {
        if ($orders->isEmpty()) {
            return [];
        }

        $orderIds = $orders->pluck('web_order_id')->all();

        $items = WebOrderItemSnapshot::query()
            ->whereIn('web_order_id', $orderIds)
            ->get(['web_order_id', 'sku', 'quantity', 'subtotal']);

        // group per order
        $grouped = $items->groupBy('web_order_id');
        $result  = [];

        foreach ($orderIds as $id) {
            $orderItems = $grouped->get($id, collect());
            $computed   = $this->computeFromItems($orderItems);
            $result[$id] = [
                'hpp'    => $computed['total_hpp'],
                'profit' => $computed['total_profit'],
            ];
        }

        return $result;
    }

    /**
     * Hitung HPP & profit dari sekumpulan item (sudah di-load dari DB).
     *
     * @param  Collection<int, WebOrderItemSnapshot>  $items
     * @return array{
     *   total_hpp:int, total_profit:int, margin_pct:float,
     *   per_order:array, unresolved_count:int, unresolved_revenue:int
     * }
     */
    private function computeFromItems(Collection $items): array
    {
        if ($items->isEmpty()) {
            return [
                'total_hpp'          => 0,
                'total_profit'       => 0,
                'margin_pct'         => 0.0,
                'per_order'          => [],
                'unresolved_count'   => 0,
                'unresolved_revenue' => 0,
            ];
        }

        // 1. Resolve semua SKU unik ke product_id (in-memory prefix map)
        $uniqueSkus   = $items->pluck('sku')->filter()->unique()->values()->all();
        $skuToProduct = $this->skuResolver->resolveMany($uniqueSkus);

        // 2. Load semua product yang terpakai sekaligus
        $productIds = array_values(array_filter($skuToProduct));
        $products   = empty($productIds)
            ? collect()
            : Product::whereIn('id', array_unique($productIds))
                ->get(['id', 'hpp'])
                ->keyBy('id');

        // 3. Loop item, akumulasi
        $totalHpp          = 0;
        $totalProfit       = 0;
        $unresolvedCount   = 0;
        $unresolvedRevenue = 0;

        foreach ($items as $item) {
            $sku        = $item->sku;
            $productId  = $sku ? ($skuToProduct[$sku] ?? null) : null;
            $product    = $productId ? $products->get($productId) : null;

            $qty      = (int) $item->quantity;
            $subtotal = (int) $item->subtotal;

            if ($product) {
                $hpp = $product->hpp * $qty;
            } else {
                $hpp = 0;
                $unresolvedCount++;
                $unresolvedRevenue += $subtotal;
            }

            $totalHpp    += $hpp;
            $totalProfit += ($subtotal - $hpp);
        }

        $totalRevenue = $totalHpp + $totalProfit;
        $marginPct    = $totalRevenue > 0
            ? round(($totalProfit / $totalRevenue) * 100, 1)
            : 0.0;

        return [
            'total_hpp'          => $totalHpp,
            'total_profit'       => $totalProfit,
            'margin_pct'         => $marginPct,
            'per_order'          => [],
            'unresolved_count'   => $unresolvedCount,
            'unresolved_revenue' => $unresolvedRevenue,
        ];
    }
}