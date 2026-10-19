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

        // Mapping produk
        $mappings = ProductMapping::where('store_id', $storeId)
            ->pluck('product_id', 'marketplace_sku')
            ->toArray();

        $products = Product::pluck('hpp', 'id')->toArray();

        // Ambil Income per order
        $incomeByOrder = TiktokIncome::all()->keyBy('order_id');

        // Ambil Order Items (status Selesai/Delivered)
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

                // Hitung total subtotal setelah diskon dari Order Items
                $totalSubtotal = $items->sum(fn($item) => $item->sku_subtotal_after_discount);

                if ($totalSubtotal <= 0) {
                    $skipped++;
                    continue;
                }

                $itemsArray = $items->values();

                foreach ($itemsArray as $index => $item) {
                    $nilaiItem = $item->sku_subtotal_after_discount;
                    $proporsi = $nilaiItem / $totalSubtotal;

                    // 1. HITUNG BIAYA-BIAYA PLATFORM (Proporsional)
                    $biayaKomisi    = (int) round(abs($income->biaya_komisi_platform ?? 0) * $proporsi);
                    $biayaLayanan   = (int) round((abs($income->biaya_pemrosesan_pesanan ?? 0) + abs($income->biaya_growth_xtra ?? 0)) * $proporsi);
                    $biayaOngkir    = (int) round(abs($income->ongkir ?? 0) * $proporsi);
                    $biayaAfiliasi  = (int) round(abs($income->komisi_afiliasi ?? 0) * $proporsi);
                    $biayaPajak     = (int) round(abs($income->pph_22 ?? 0) * $proporsi);
                    $biayaLainnya   = (int) round(abs($income->komisi_dinamis ?? 0) * $proporsi);
                    
                    $totalBiaya = $biayaKomisi + $biayaLayanan + $biayaOngkir + $biayaAfiliasi + $biayaPajak + $biayaLainnya;

                    // 2. NET REVENUE (Uang Bersih) = Subtotal Barang - Total Biaya Platform
                    $netRevenue = $nilaiItem - $totalBiaya;

                    // Mapping SKU
                    $sku = trim($item->seller_sku ?? '');
                    $productId = $mappings[$sku] ?? null;

                    $hppTotal = null;
                    $profit = null;
                    
                    if ($productId && isset($products[$productId])) {
                        $hppTotal = $products[$productId] * $item->quantity; // HPP per unit x Qty
                        
                        // 3. PROFIT = Net Revenue (Uang Bersih) - HPP
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
                        
                        // Net Revenue sekarang sudah bersih (dipotong biaya)
                        'net_revenue'     => $netRevenue, 
                        
                        // Tetap simpan rincian biaya untuk arsip/laporan
                        'biaya_komisi'    => $biayaKomisi,
                        'biaya_layanan'   => $biayaLayanan,
                        'biaya_ongkir'    => $biayaOngkir,
                        'biaya_afiliasi'  => $biayaAfiliasi,
                        'biaya_promosi'   => 0,
                        'biaya_pajak'     => $biayaPajak,
                        'biaya_lainnya'   => $biayaLainnya,
                        
                        'hpp_total'       => $hppTotal,
                        'profit'          => $profit, // Profit = Net Revenue - HPP
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