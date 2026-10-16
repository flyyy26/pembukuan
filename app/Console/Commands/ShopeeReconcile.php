<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductMapping;
use App\Models\Sale;
use App\Models\ShopeeIncomeRow;
use App\Models\ShopeeOrderItem;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShopeeReconcile extends Command
{
    protected $signature = 'shopee:reconcile {store_id}';
    protected $description = 'Rekonsiliasi Shopee: gabungkan order + income jadi sales';

    public function handle()
    {
        $storeId = (int) $this->argument('store_id');
        $store = Store::find($storeId);
        if (!$store) {
            $this->error("Store tidak ditemukan");
            return 1;
        }

        $this->info("Rekonsiliasi untuk: {$store->nama_toko}");

        // Hapus sales Shopee lama untuk store ini
        Sale::where('store_id', $storeId)->where('marketplace', 'shopee')->delete();

        // Ambil mapping
        $mappings = ProductMapping::where('store_id', $storeId)
            ->pluck('product_id', 'marketplace_sku')
            ->toArray();

        $products = Product::pluck('hpp', 'id')->toArray();

        // Ambil data income per SKU (baris "Sku")
        $incomeSkus = ShopeeIncomeRow::where('tipe_baris', 'sku')->get();

        // Ambil semua order items (status Selesai)
        $orderItems = ShopeeOrderItem::where('status_pesanan', 'Selesai')
            ->get()
            ->groupBy('no_pesanan');

        $this->info("Income SKU rows : " . $incomeSkus->count());
        $this->info("Order items     : " . $orderItems->flatten()->count());

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            foreach ($incomeSkus as $incomeSku) {
                $noPesanan = $incomeSku->no_pesanan;
                $idProduk = $incomeSku->id_produk;
                $hargaProdukIncome = $incomeSku->harga_produk;

                // Ambil semua order item untuk order ini
                $items = $orderItems->get($noPesanan);
                if (!$items) {
                    $skipped++;
                    continue;
                }

                // Cocokkan berdasarkan harga produk (income) == harga_setelah_diskon * jumlah (order item)
                // Karena income report memecah per varian berdasarkan harga
                $matchedItem = $items->first(function ($item) use ($hargaProdukIncome) {
                    $nilaiItem = $item->harga_setelah_diskon * $item->jumlah;
                    return abs($nilaiItem - $hargaProdukIncome) < 100; // toleransi 100 rupiah
                });

                // Fallback: cocokkan berdasarkan id_produk (SKU Induk)
                if (!$matchedItem) {
                    $matchedItem = $items->first(function ($item) use ($idProduk) {
                        return $item->sku_induk == $idProduk;
                    });
                }

                if (!$matchedItem) {
                    $skipped++;
                    continue;
                }

                $skuVarian = trim($matchedItem->sku_varian);
                $productId = $mappings[$skuVarian] ?? null;

                $hppTotal = null;
                $profit = null;
                if ($productId && isset($products[$productId])) {
                    $hppTotal = $products[$productId] * $matchedItem->jumlah;
                    $profit   = $incomeSku->total_penghasilan - $hppTotal;
                }

                try {
                    Sale::create([
                        'store_id'        => $storeId,
                        'marketplace'     => 'shopee',
                        'order_id'        => $noPesanan,
                        'tanggal'         => $incomeSku->tanggal_dana_dilepaskan?->format('Y-m-d')
                                            ?? $matchedItem->waktu_pesanan_selesai?->format('Y-m-d')
                                            ?? $matchedItem->waktu_pesanan_dibuat?->format('Y-m-d'),
                        'product_id'      => $productId,
                        'marketplace_sku' => $skuVarian,
                        'qty'             => $matchedItem->jumlah,
                        'harga_jual'      => $matchedItem->harga_awal * $matchedItem->jumlah,
                        'diskon_platform' => $matchedItem->diskon_dari_shopee,
                        'diskon_seller'   => $matchedItem->diskon_dari_penjual,
                        'subtotal'        => $matchedItem->harga_setelah_diskon * $matchedItem->jumlah,
                        'net_revenue'     => $incomeSku->total_penghasilan,
                        'biaya_komisi'    => abs($incomeSku->biaya_administrasi) + abs($incomeSku->biaya_komisi_ams),
                        'biaya_layanan'   => abs($incomeSku->biaya_proses_pesanan) + abs($incomeSku->biaya_gratis_ongkir_xtra),
                        'biaya_ongkir'    => abs($incomeSku->ongkos_kirim_dibayarkan) - $incomeSku->gratis_ongkir_dari_shopee,
                        'biaya_afiliasi'  => 0,
                        'biaya_promosi'   => 0,
                        'biaya_pajak'     => abs($incomeSku->pph_22),
                        'biaya_lainnya'   => abs($incomeSku->biaya_transaksi),
                        'hpp_total'       => $hppTotal,
                        'profit'          => $profit,
                        'status'          => $productId ? 'matched' : 'orphan',
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    $errors[] = "Order {$noPesanan}, SKU {$skuVarian}: " . $e->getMessage();
                    $skipped++;
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
        $this->line("Inserted      : {$inserted}");
        $this->line("Skipped       : {$skipped}");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn("=== Errors (max 10) ===");
            foreach (array_slice($errors, 0, 10) as $err) {
                $this->line($err);
            }
        }

        return 0;
    }
}