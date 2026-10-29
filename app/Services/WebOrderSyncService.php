<?php

namespace App\Services;

use App\Models\WebOrderSnapshot;
use App\Models\WebOrderItemSnapshot;
use App\Models\WebSyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebOrderSyncService
{
    public function syncCompletedOrders(): WebSyncLog
    {
        $log = WebSyncLog::create([
            'type'       => 'orders',
            'status'     => 'running',
            'started_at' => now(),
        ]);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        try {
            $orders = DB::connection('mysql_web')
                ->table('orders')
                ->where('shipping_status', 'delivered')
                ->orderByDesc('created_at')
                ->get();

            foreach ($orders as $order) {
                try {
                    DB::connection('mysql')->transaction(function () use ($order, &$created, &$updated) {

                        $snapshot = WebOrderSnapshot::updateOrCreate(
                            ['web_order_id' => $order->id],
                            [
                                'order_number'    => $order->order_number,
                                'status'          => $order->shipping_status,
                                'payment_status'  => $order->payment_status,
                                'payment_method'  => $order->payment_method_name,
                                'payment_proof'   => $order->payment_proof, // <-- TAMBAHKAN INI
                                'customer_id'     => $order->user_id,
                                'customer_name'   => $order->shipping_name,
                                'customer_email'  => null,
                                'customer_phone'  => $order->shipping_phone,
                                'total_amount'    => (int) ($order->total ?? 0),
                                'shipping_cost'   => (int) ($order->shipping_cost ?? 0),
                                'discount_amount' => (int) ($order->discount ?? 0),
                                'marketplace'     => 'web',
                                'web_created_at'  => $order->created_at,
                                'web_updated_at'  => $order->updated_at,
                                'synced_at'       => now(),
                            ]
                        );

                        $isNew = $snapshot->wasRecentlyCreated;

                        $items = DB::connection('mysql_web')
                            ->table('order_items as oi')
                            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
                            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                            // ✅ Join ke tabel varian (GANTI jika nama tabelnya berbeda)
                            ->leftJoin('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
                            ->where('oi.order_id', $order->id)
                            ->select([
                                'oi.id as web_order_item_id',
                                'oi.product_id',
                                'oi.product_variant_id',
                                'oi.product_name',
                                'oi.price',
                                'oi.quantity',
                                'oi.subtotal',
                                'oi.sku as oi_sku',       // Fallback 1
                                'pv.sku as pv_sku',       // ✅ SKU dari tabel varian
                                'c.name as category_name' // ✅ Kategori
                            ])
                            ->get();

                        // Hapus item lama
                        WebOrderItemSnapshot::where('web_order_id', $order->id)->delete();

                        foreach ($items as $item) {
                            WebOrderItemSnapshot::create([
                                'web_order_id'       => $order->id,
                                'web_order_item_id'  => $item->web_order_item_id,
                                'product_id'         => $item->product_id,
                                'product_variant_id' => $item->product_variant_id,
                                'product_name'       => $item->product_name,
                                // ✅ Ambil SKU dari varian, jika tidak ada pakai dari order_items
                                'sku'                => $item->pv_sku ?? $item->oi_sku ?? '-', 
                                'category_name'      => $item->category_name ?? '-',
                                'price'              => (int) ($item->price ?? 0),
                                'quantity'           => (int) ($item->quantity ?? 0),
                                'subtotal'           => (int) ($item->subtotal ?? 0),
                            ]);
                        }

                        if ($isNew) $created++;
                        else        $updated++;
                    });
                } catch (\Throwable $e) {
                    $skipped++;
                    Log::warning('Sync order gagal', [
                        'order_id' => $order->id ?? null,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            $log->update([
                'status'          => 'success',
                'records_created' => $created,
                'records_updated' => $updated,
                'records_skipped' => $skipped,
                'finished_at'     => now(),
            ]);

            return $log;

        } catch (\Throwable $e) {
            Log::error('Sync completed orders fatal', ['error' => $e->getMessage()]);

            $log->update([
                'status'          => 'failed',
                'records_created' => $created,
                'records_updated' => $updated,
                'records_skipped' => $skipped,
                'error_message'   => $e->getMessage(),
                'finished_at'     => now(),
            ]);

            return $log;
        }
    }
}