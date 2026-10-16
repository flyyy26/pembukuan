<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductMapping;
use App\Models\ShopeeIncomeRow;
use Illuminate\Console\Command;

class ShopeeProfit extends Command
{
    protected $signature = 'shopee:profit {--store=2} {--from=} {--to=}';
    protected $description = 'Hitung profit Shopee berdasarkan HPP';

    public function handle()
    {
        $storeId = (int) $this->option('store');
        $from    = $this->option('from');
        $to      = $this->option('to');

        // Ambil mapping store ini
        $mappings = ProductMapping::where('store_id', $storeId)
            ->pluck('product_id', 'marketplace_sku')
            ->toArray();

        if (empty($mappings)) {
            $this->warn("Belum ada mapping untuk store {$storeId}");
            return 1;
        }

        // Ambil semua produk beserta HPP
        $products = Product::pluck('hpp', 'id')->toArray();

        // Query
        $query = ShopeeIncomeRow::where('tipe_baris', 'sku');
        if ($from) $query->whereDate('tanggal_dana_dilepaskan', '>=', $from);
        if ($to)   $query->whereDate('tanggal_dana_dilepaskan', '<=', $to);

        $rows = $query->get();

        $this->info("Total baris Sku: " . $rows->count());

        // Akumulasi
        $totalNetRevenue = 0;
        $totalHpp = 0;
        $mappedRows = 0;
        $unmappedRows = 0;
        $unmappedSkus = [];

        foreach ($rows as $row) {
            $netRevenue = (int) $row->total_penghasilan;
            $totalNetRevenue += $netRevenue;

            $idProduk = $row->id_produk;
            if (isset($mappings[$idProduk])) {
                $productId = $mappings[$idProduk];
                $hpp = $products[$productId] ?? 0;
                // Shopee tidak punya kolom qty eksplisit, asumsi 1 baris = 1 qty
                // (karena Shopee memecah order multi-qty jadi multi-baris atau 1 baris dengan harga total)
                $qty = 1;
                $totalHpp += $hpp * $qty;
                $mappedRows++;
            } else {
                $unmappedRows++;
                $unmappedSkus[$idProduk] = ($unmappedSkus[$idProduk] ?? 0) + 1;
            }
        }

        $profit = $totalNetRevenue - $totalHpp;

        $this->newLine();
        $this->info('=== Profit Shopee ===');
        $this->line("Net revenue       : " . number_format($totalNetRevenue, 0, ',', '.'));
        $this->line("Total HPP         : " . number_format($totalHpp, 0, ',', '.'));
        $this->line("Profit            : " . number_format($profit, 0, ',', '.'));
        $this->newLine();
        $this->line("Baris termapping  : {$mappedRows}");
        $this->line("Baris belum map   : {$unmappedRows}");

        if (!empty($unmappedSkus)) {
            $this->newLine();
            $this->warn("ID Produk belum dimapping (top 10):");
            arsort($unmappedSkus);
            $i = 0;
            foreach ($unmappedSkus as $sku => $count) {
                if ($i++ >= 10) break;
                $this->line("  {$sku} : {$count} baris");
            }
        }

        return 0;
    }
}