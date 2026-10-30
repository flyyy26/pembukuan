<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\TiktokIncome;
use App\Models\TiktokOrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Import;
use App\Services\SkuResolver;

class TiktokReconcile extends Command
{
    protected $signature = 'tiktok:reconcile {store_id}';
    protected $description = 'Rekonsiliasi TikTok: order + income menjadi sales';

    /**
     * Alokasikan biaya bulat berdasarkan proporsi subtotal.
     * Sisa pembulatan dibagikan ke SKU dengan pecahan terbesar,
     * sehingga total alokasi selalu sama dengan total biaya.
     */
    private function allocateAmount(
        int $amount,
        array $weights,
        int $totalWeight
    ): array {
        $amount = max(0, $amount);
        $result = array_fill(0, count($weights), 0);

        if ($amount === 0 || $totalWeight <= 0 || count($weights) === 0) {
            return $result;
        }

        $remainders = [];
        $allocated = 0;

        foreach ($weights as $index => $weight) {
            $exact = $amount * max(0, (int) $weight) / $totalWeight;
            $base = (int) floor($exact);

            $result[$index] = $base;
            $allocated += $base;

            $remainders[$index] = $exact - $base;
        }

        $remaining = $amount - $allocated;

        // Bagikan sisa rupiah berdasarkan pecahan terbesar.
        arsort($remainders, SORT_NUMERIC);

        foreach (array_keys($remainders) as $index) {
            if ($remaining <= 0) {
                break;
            }

            if ((int) ($weights[$index] ?? 0) > 0) {
                $result[$index]++;
                $remaining--;
            }
        }

        return $result;
    }

    public function handle()
    {
        $storeId = (int) $this->argument('store_id');
        $store = Store::find($storeId);

        if (!$store) {
            $this->error('Store tidak ditemukan.');
            return 1;
        }

        $this->info("Rekonsiliasi TikTok untuk: {$store->nama_toko}");

        $income = TiktokIncome::whereHas('import', function ($query) use ($storeId) {
            $query->where('store_id', $storeId)
                ->where('tipe', 'tiktok_income')
                ->where('status', 'imported');
        })->get()->groupBy('order_id');

        $orderItems = TiktokOrderItem::whereHas('import', function ($query) use ($storeId) {
            $query->where('store_id', $storeId)
                ->where('tipe', 'tiktok_order')
                ->where('status', 'imported');
        })->whereIn('order_status', [
            'Selesai',
            'Delivered',
            'Completed',
        ])->get()->groupBy('order_id');

        $resolver = new SkuResolver();
        $products = Product::pluck('hpp', 'id')->toArray();

        $this->info('Income orders : ' . $income->count());
        $this->info('Order groups  : ' . $orderItems->count());

        $inserted = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            // Hapus hanya sales TikTok milik toko ini.
            Sale::where('store_id', $storeId)
                ->where('marketplace', 'tiktok')
                ->delete();

            foreach ($orderItems as $orderId => $items) {
                $incomeRows = $income->get($orderId);

                if (!$incomeRows || $incomeRows->isEmpty()) {
                    $skipped++;
                    continue;
                }

                $items = $items->values();

                // Dasar alokasi adalah subtotal SKU setelah diskon.
                $weights = $items->map(
                    fn ($item) => max(
                        0,
                        (int) $item->sku_subtotal_after_discount
                    )
                )->all();

                $totalSubtotal = array_sum($weights);

                if ($totalSubtotal <= 0) {
                    $skipped++;
                    continue;
                }

                /*
                 * Biaya tiap order:
                 * jumlahkan nilai absolut setiap baris Income untuk
                 * menghindari keyBy() yang membuang baris duplikat.
                 *
                 * Jika file Income Anda memiliki baris koreksi/refund,
                 * aturan agregasinya perlu disesuaikan dengan makna
                 * transaksi pada file tersebut.
                 */
                $feeFields = [
                    'komisi' => 'biaya_komisi_platform',
                    'layanan' => null,
                    'ongkir' => 'ongkir',
                    'afiliasi' => 'komisi_afiliasi',
                    'pajak' => 'pph_22',
                    'lainnya' => 'komisi_dinamis',
                ];

                $orderFees = [];

                foreach ($feeFields as $key => $field) {
                    if ($key === 'layanan') {
                        $orderFees[$key] = $incomeRows->sum(
                            fn ($row) =>
                                abs((int) ($row->biaya_pemrosesan_pesanan ?? 0))
                                + abs((int) ($row->biaya_growth_xtra ?? 0))
                        );
                    } else {
                        $orderFees[$key] = $incomeRows->sum(
                            fn ($row) => abs((int) ($row->{$field} ?? 0))
                        );
                    }
                }

                // Alokasikan setiap kelompok biaya dengan aturan sama.
                $allocatedFees = [];

                foreach ($orderFees as $key => $amount) {
                    $allocatedFees[$key] = $this->allocateAmount(
                        (int) $amount,
                        $weights,
                        $totalSubtotal
                    );
                }

                foreach ($items as $index => $item) {
                    $nilaiItem = $weights[$index];

                    $biayaKomisi = $allocatedFees['komisi'][$index];
                    $biayaLayanan = $allocatedFees['layanan'][$index];
                    $biayaOngkir = $allocatedFees['ongkir'][$index];
                    $biayaAfiliasi = $allocatedFees['afiliasi'][$index];
                    $biayaPajak = $allocatedFees['pajak'][$index];
                    $biayaLainnya = $allocatedFees['lainnya'][$index];

                    $totalBiaya = $biayaKomisi
                        + $biayaLayanan
                        + $biayaOngkir
                        + $biayaAfiliasi
                        + $biayaPajak
                        + $biayaLainnya;

                    // Net Revenue = subtotal setelah diskon - biaya.
                    $netRevenue = $nilaiItem - $totalBiaya;

                    $sku = trim($item->seller_sku ?? '');
                    $productId = $resolver->resolveProductId($sku);

                    $hppTotal = null;
                    $profit = null;

                    if ($productId && isset($products[$productId])) {
                        $hppTotal = (int) $products[$productId]
                            * (int) $item->quantity;

                        $profit = $netRevenue - $hppTotal;
                    }

                    Sale::create([
                        'store_id' => $storeId,
                        'marketplace' => 'tiktok',
                        'order_id' => $orderId,
                        'tanggal' => $item->delivered_time?->format('Y-m-d')
                            ?? $item->paid_time?->format('Y-m-d')
                            ?? $item->created_time?->format('Y-m-d'),

                        'product_id' => $productId,
                        'marketplace_sku' => $sku,
                        'qty' => $item->quantity,

                        'harga_jual' =>
                            $item->sku_unit_original_price * $item->quantity,

                        'diskon_platform' => $item->sku_platform_discount,
                        'diskon_seller' => $item->sku_seller_discount,
                        'subtotal' => $nilaiItem,

                        'net_revenue' => $netRevenue,

                        'biaya_komisi' => $biayaKomisi,
                        'biaya_layanan' => $biayaLayanan,
                        'biaya_ongkir' => $biayaOngkir,
                        'biaya_afiliasi' => $biayaAfiliasi,
                        'biaya_promosi' => 0,
                        'biaya_pajak' => $biayaPajak,
                        'biaya_lainnya' => $biayaLainnya,

                        'hpp_total' => $hppTotal,
                        'profit' => $profit,
                        'status' => $productId ? 'matched' : 'orphan',
                    ]);

                    $inserted++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->error('Gagal rekonsiliasi: ' . $e->getMessage());

            report($e);

            return 1;
        }

        $this->newLine();
        $this->info('=== Hasil Rekonsiliasi ===');
        $this->line("Inserted : {$inserted}");
        $this->line("Skipped  : {$skipped}");

        return 0;
    }
}
