<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductMapping;
use App\Models\Sale;
use App\Models\Store;
use App\Models\TiktokIncome;
use App\Models\TiktokOrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TiktokReconcile extends Command
{
    protected $signature = 'tiktok:reconcile {store_id}';
    protected $description = 'Rekonsiliasi TikTok: gabungkan order + income jadi sales';

    public function handle()
    {
        $storeId = (int) $this->argument('store_id');
        $store = Store::find($storeId);
        if (!$store) {
            $this->error("Store tidak ditemukan");
            return 1;
        }

        $this->info("Rekonsiliasi TikTok untuk: {$store->nama_toko}");

        // Hapus sales TikTok lama untuk store ini
        Sale::where('store_id', $storeId)->where('marketplace', 'tiktok')->delete();

        // Mapping
        $mappings = ProductMapping::where('store_id', $storeId)
            ->pluck('product_id', 'marketplace_sku')
            ->toArray();

        $products = Product::pluck('hpp', 'id')->toArray();

        // Income per order
        $incomeByOrder = TiktokIncome::all()->keyBy('order_id');

        // Order items (status Selesai / Delivered)
        $orderItems = TiktokOrderItem::whereIn('order_status', ['Selesai', 'Delivered', 'Completed'])
            ->get()
            ->groupBy('order_id');

        $this->info("Income rows  : " . $incomeByOrder->count());
        $this->info("Order items  : " . $orderItems->flatten()->count());

        $inserted = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($orderItems as $orderId => $items) {
                $income = $incomeByOrder->get($orderId);
                if (!$income) {
                    $skipped++;
                    continue;
                }

                // Hitung total untuk proporsi
                $totalHarga = $items->sum(fn($item) => $item->sku_subtotal_after_discount);

                if ($totalHarga <= 0) {
                    $skipped++;
                    continue;
                }

                $netRevenueOrder = (int) $income->total_pendapatan;

                foreach ($items as $item) {
                    $nilaiItem = $item->sku_subtotal_after_discount;
                    $proporsi = $nilaiItem / $totalHarga;

                    $netRevenue = (int) round($netRevenueOrder * $proporsi);

                    $sku = trim($item->seller_sku ?? '');
                    $productId = $mappings[$sku] ?? null;

                    $hppTotal = null;
                    $profit = null;
                    if ($productId && isset($products[$productId])) {
                        $hppTotal = $products[$productId] * $item->quantity;
                        $profit = $netRevenue - $hppTotal;
                    }

                    Sale::create([
                        'store_id'        => $storeId,
                        'marketplace'     => 'tiktok',
                        'order_id'        => $orderId,
                        'tanggal'         => $item->delivered_time?->format('Y-m-d')
                                             ?? $item->paid_time?->format('Y-m-d')
                                             ?? $item->created_time?->format('Y-m-d'),
                        'product_id'      => $productId,
                        'marketplace_sku' => $sku,
                        'qty'             => $item->quantity,
                        'harga_jual'      => $item->sku_unit_original_price * $item->quantity,
                        'diskon_platform' => $item->sku_platform_discount,
                        'diskon_seller'   => $item->sku_seller_discount,
                        'subtotal'        => $item->sku_subtotal_after_discount,
                        'net_revenue'     => $netRevenue,
                        'biaya_komisi'    => (int) round(abs($income->biaya_komisi_platform) * $proporsi),
                        'biaya_layanan'   => (int) round((abs($income->biaya_pemrosesan_pesanan) + abs($income->biaya_growth_xtra)) * $proporsi),
                        'biaya_ongkir'    => (int) round(abs($income->ongkir) * $proporsi),
                        'biaya_afiliasi'  => (int) round(abs($income->komisi_afiliasi) * $proporsi),
                        'biaya_promosi'   => 0,
                        'biaya_pajak'     => (int) round(abs($income->pph_22) * $proporsi),
                        'biaya_lainnya'   => (int) round(abs($income->komisi_dinamis) * $proporsi),
                        'hpp_total'       => $hppTotal,
                        'profit'          => $profit,
                        'status'          => $productId ? 'matched' : 'orphan',
                    ]);
                    $inserted++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal: " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info("=== Hasil ===");
        $this->line("Inserted : {$inserted}");
        $this->line("Skipped  : {$skipped}");

        return 0;
    }
}