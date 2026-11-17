<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Sale;
use App\Models\ShopeeIncomeRow;
use App\Models\ShopeeOrderItem;
use App\Models\Store;
use App\Services\SkuResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShopeeReconcile extends Command
{
    protected $signature = 'shopee:reconcile {store_id}';
    protected $description = 'Rekonsiliasi Shopee dari Order.all & Split dari Income Order';

    public function handle()
    {
        $storeId = (int) $this->argument('store_id');
        $store = Store::find($storeId);
        if (!$store) {
            $this->error("Store tidak ditemukan");
            return 1;
        }

        $this->info("Rekonsiliasi untuk: {$store->nama_toko}");

        // 1. Hapus sales Shopee lama
        Sale::where('store_id', $storeId)->where('marketplace', 'shopee')->delete();

        // 2. Mapping SKU Varian → Produk Internal
        $resolver = new SkuResolver();

        // 3. HPP Produk
        $products = Product::pluck('hpp', 'id')->toArray();

        // 4. Income per pesanan
        $incomeOrders = ShopeeIncomeRow::where('tipe_baris', 'order')->get()->keyBy('no_pesanan');
        $incomeSkus   = ShopeeIncomeRow::where('tipe_baris', 'sku')->get()->groupBy('no_pesanan');

        // ============================================================
        // ✅ SET is_in_income untuk SEMUA ShopeeOrderItem store ini
        //    (juga untuk data lama yang masih null)
        // ============================================================
        $validOrderNumbers = $incomeOrders->keys()
            ->merge($incomeSkus->keys())
            ->unique()
            ->values();

        // Reset dulu semua ke false untuk store ini
        ShopeeOrderItem::query()
            ->whereHas('import', fn($q) => $q->where('store_id', $storeId))
            ->update(['is_in_income' => false]);

        // Tandai true untuk yang ada di Income
        $chunks = $validOrderNumbers->chunk(500);
        foreach ($chunks as $chunk) {
            ShopeeOrderItem::query()
                ->whereHas('import', fn($q) => $q->where('store_id', $storeId))
                ->whereIn('no_pesanan', $chunk->all())
                ->update(['is_in_income' => true]);
        }

        $yesCount = ShopeeOrderItem::query()
            ->whereHas('import', fn($q) => $q->where('store_id', $storeId))
            ->where('is_in_income', true)
            ->count();
        $noCount = ShopeeOrderItem::query()
            ->whereHas('import', fn($q) => $q->where('store_id', $storeId))
            ->where('is_in_income', false)
            ->count();

        // ============================================================
        // ✅ Ambil HANYA order yang is_in_income = true
        //    Inilah yang akan dibuat Sale.
        // ============================================================
        $orderItems = ShopeeOrderItem::query()
            ->whereHas('import', fn($q) => $q->where('store_id', $storeId))
            ->where('is_in_income', true)
            ->get()
            ->groupBy('no_pesanan');

        $this->info("Total baris Order.all (YES) : {$yesCount}");
        $this->info("Total baris Order.all (NO)  : {$noCount}");
        $this->info("Total Pesanan di Income     : " . $incomeOrders->count());
        $this->info("Total Pesanan yang diproses : " . $orderItems->count());

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        $totalNetRevenueSales = 0;
        $totalIncomeOrder     = 0;

        DB::beginTransaction();
        try {
            foreach ($orderItems as $noPesanan => $items) {

                $incomeOrder = $incomeOrders->get($noPesanan);

                // Fallback: agregasi dari baris SKU
                if (!$incomeOrder) {
                    $skusInOrder = $incomeSkus->get($noPesanan);
                    if ($skusInOrder && $skusInOrder->count() > 0) {
                        $incomeOrder = new \stdClass();
                        $incomeOrder->total_penghasilan            = $skusInOrder->sum('total_penghasilan');
                        $incomeOrder->harga_produk                 = $skusInOrder->sum('harga_produk');
                        $incomeOrder->biaya_administrasi           = $skusInOrder->sum('biaya_administrasi');
                        $incomeOrder->biaya_proses_pesanan         = $skusInOrder->sum('biaya_proses_pesanan');
                        $incomeOrder->biaya_gratis_ongkir_xtra     = $skusInOrder->sum('biaya_gratis_ongkir_xtra');
                        $incomeOrder->biaya_transaksi              = $skusInOrder->sum('biaya_transaksi');
                        $incomeOrder->biaya_komisi_ams             = $skusInOrder->sum('biaya_komisi_ams');
                        $incomeOrder->pph_22                       = $skusInOrder->sum('pph_22');
                        $incomeOrder->biaya_kampanye               = $skusInOrder->sum('biaya_kampanye');
                        $incomeOrder->biaya_isi_saldo_otomatis     = $skusInOrder->sum('biaya_isi_saldo_otomatis');
                        $incomeOrder->premi                        = $skusInOrder->sum('premi');
                        $incomeOrder->fbs_fee                      = $skusInOrder->sum('fbs_fee');
                        $incomeOrder->voucher_disponsor_penjual    = $skusInOrder->sum('voucher_disponsor_penjual');
                        $incomeOrder->cashback_koin_disponsori_penjual = $skusInOrder->sum('cashback_koin_disponsori_penjual');
                        $incomeOrder->voucher_cofund_disponsor_penjual = $skusInOrder->sum('voucher_cofund_disponsor_penjual');
                        $incomeOrder->cashback_koin_cofund_disponsori_penjual = $skusInOrder->sum('cashback_koin_cofund_disponsori_penjual');
                        $incomeOrder->ongkos_kirim_dibayarkan      = $skusInOrder->sum('ongkos_kirim_dibayarkan');
                        $incomeOrder->gratis_ongkir_dari_shopee    = $skusInOrder->sum('gratis_ongkir_dari_shopee');
                        $incomeOrder->tanggal_dana_dilepaskan      = $skusInOrder->first()->tanggal_dana_dilepaskan;
                    }
                }

                if (!$incomeOrder) {
                    $skipped++;
                    continue;
                }

                // ============================================
                // TOTAL BIAYA per order
                // ============================================
                $totalKomisi = abs((int) $incomeOrder->biaya_administrasi)
                            + abs((int) $incomeOrder->biaya_komisi_ams);

                $totalLayanan = abs((int) $incomeOrder->biaya_proses_pesanan)
                            + abs((int) $incomeOrder->biaya_gratis_ongkir_xtra);

                $totalPajak = abs((int) $incomeOrder->pph_22);

                $totalLain = abs((int) $incomeOrder->biaya_transaksi)
                        + abs((int) ($incomeOrder->premi ?? 0))
                        + abs((int) ($incomeOrder->fbs_fee ?? 0));

                $totalPromosi = abs((int) ($incomeOrder->biaya_kampanye ?? 0))
                            + abs((int) ($incomeOrder->voucher_disponsor_penjual ?? 0))
                            + abs((int) ($incomeOrder->cashback_koin_disponsori_penjual ?? 0))
                            + abs((int) ($incomeOrder->voucher_cofund_disponsor_penjual ?? 0))
                            + abs((int) ($incomeOrder->cashback_koin_cofund_disponsori_penjual ?? 0))
                            + abs((int) ($incomeOrder->biaya_isi_saldo_otomatis ?? 0));

                $totalOngkir = abs((int) $incomeOrder->ongkos_kirim_dibayarkan)
                            - (int) $incomeOrder->gratis_ongkir_dari_shopee;

                $totalNetRevenue = (int) $incomeOrder->total_penghasilan;
                $tanggal = $incomeOrder->tanggal_dana_dilepaskan
                    ?? $items->first()->waktu_pesanan_dibuat;

                $totalIncomeOrder += $totalNetRevenue;

                // Dasar alokasi = subtotal SKU
                $totalDasarHarga = $items->sum(function ($item) {
                    return $item->harga_setelah_diskon * $item->jumlah;
                });

                if ($totalDasarHarga <= 0) {
                    $totalDasarHarga = $items->count();
                }

                $itemCount = $items->count();

                $allocated = [
                    'net_revenue' => 0,
                    'komisi'      => 0,
                    'layanan'     => 0,
                    'pajak'       => 0,
                    'lainnya'     => 0,
                    'promosi'     => 0,
                    'ongkir'      => 0,
                ];

                $itemIndex = 0;
                $lastIndex = $itemCount - 1;

                foreach ($items as $item) {
                    $skuVarian = trim(preg_replace('/\s+/', ' ', $item->sku_varian ?? ''));
                    $productId = $resolver->resolveProductId($skuVarian);

                    $dasarHargaItem = $item->harga_setelah_diskon * $item->jumlah;
                    $rasio = ($totalDasarHarga == $itemCount)
                        ? 1 / $itemCount
                        : $dasarHargaItem / $totalDasarHarga;

                    // ============================================================
                    // ✅ Alokasi proporsional, item TERAKHIR menerima SISA
                    //    supaya total persis = total_penghasilan (tidak ada selisih)
                    // ============================================================
                    if ($itemIndex === $lastIndex) {
                        // Item terakhir: terima sisa (total - yang sudah dialokasikan)
                        $netRevenueItem = $totalNetRevenue - $allocated['net_revenue'];
                        $komisiItem     = $totalKomisi     - $allocated['komisi'];
                        $layananItem    = $totalLayanan    - $allocated['layanan'];
                        $pajakItem      = $totalPajak      - $allocated['pajak'];
                        $lainnyaItem    = $totalLain       - $allocated['lainnya'];
                        $promosiItem    = $totalPromosi    - $allocated['promosi'];
                        $ongkirItem     = $totalOngkir     - $allocated['ongkir'];
                    } else {
                        // Item selain terakhir: alokasi proporsional + round
                        $netRevenueItem = (int) round($totalNetRevenue * $rasio);
                        $komisiItem     = (int) round($totalKomisi     * $rasio);
                        $layananItem    = (int) round($totalLayanan    * $rasio);
                        $pajakItem      = (int) round($totalPajak      * $rasio);
                        $lainnyaItem    = (int) round($totalLain       * $rasio);
                        $promosiItem    = (int) round($totalPromosi    * $rasio);
                        $ongkirItem     = (int) round($totalOngkir     * $rasio);

                        $allocated['net_revenue'] += $netRevenueItem;
                        $allocated['komisi']      += $komisiItem;
                        $allocated['layanan']     += $layananItem;
                        $allocated['pajak']       += $pajakItem;
                        $allocated['lainnya']     += $lainnyaItem;
                        $allocated['promosi']     += $promosiItem;
                        $allocated['ongkir']      += $ongkirItem;
                    }

                    $hppTotal = null;
                    $profit = null;
                    if ($productId && isset($products[$productId])) {
                        $hppTotal = $products[$productId] * $item->jumlah;
                        $profit   = $netRevenueItem - $hppTotal;
                    }

                    Sale::create([
                        'store_id'        => $storeId,
                        'marketplace'     => 'shopee',
                        'order_id'        => $noPesanan,
                        'tanggal'         => $tanggal ? \Carbon\Carbon::parse($tanggal)->format('Y-m-d') : now()->format('Y-m-d'),
                        'product_id'      => $productId,
                        'marketplace_sku' => $skuVarian,
                        'qty'             => $item->jumlah,
                        'harga_jual'      => $item->harga_awal * $item->jumlah,
                        'diskon_platform' => $item->diskon_dari_shopee,
                        'diskon_seller'   => $item->diskon_dari_penjual,
                        'subtotal'        => $item->harga_setelah_diskon * $item->jumlah,
                        'net_revenue'     => $netRevenueItem,
                        'biaya_komisi'    => $komisiItem,
                        'biaya_layanan'   => $layananItem,
                        'biaya_ongkir'    => $ongkirItem,
                        'biaya_afiliasi'  => 0,
                        'biaya_promosi'   => $promosiItem,
                        'biaya_pajak'     => $pajakItem,
                        'biaya_lainnya'   => $lainnyaItem,
                        'hpp_total'       => $hppTotal,
                        'profit'          => $profit,
                        'status'          => $productId ? 'matched' : 'orphan',
                    ]);

                    $inserted++;
                    $totalNetRevenueSales += $netRevenueItem;
                    $itemIndex++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal: " . $e->getMessage());
            return 1;
        }

        // ============================================
        // HASIL + VALIDASI
        // ============================================
        $this->newLine();
        $this->info("=== Hasil ===");
        $this->line("Baris Sale Dibuat : {$inserted}");
        $this->line("Pesanan di-skip   : {$skipped}");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn("=== Errors (max 10) ===");
            foreach (array_slice($errors, 0, 10) as $err) {
                $this->line($err);
            }
        }

        // Validasi
        $this->newLine();
        $this->info("=== Validasi Net Revenue ===");
        $this->line("Income order total  : " . number_format($totalIncomeOrder, 0, ',', '.'));
        $this->line("Net revenue di Sale : " . number_format($totalNetRevenueSales, 0, ',', '.'));

        $diff = $totalIncomeOrder - $totalNetRevenueSales;
        $this->line("Selisih             : " . number_format($diff, 0, ',', '.'));

        if (abs($diff) <= 100) {
            $this->info("OK: Konsisten (selisih <= 100 karena pembulatan).");
        } else {
            $this->warn("WARNING: Selisih > 100. Kemungkinan ada order tanpa SKU match atau pembulatan.");
        }

        return 0;
    }
}