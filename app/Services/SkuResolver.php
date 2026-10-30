<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class SkuResolver
{
    /**
     * Cache prefix => product_id, diurutkan panjang prefix DESC
     * (longest prefix wins).
     *
     * @var array<string,int>|null
     */
    private ?array $prefixMap = null;

    /**
     * Load semua product, sort by prefix length desc (longest dulu).
     */
    private function loadPrefixMap(): void
    {
        if ($this->prefixMap !== null) {
            return;
        }

        $this->prefixMap = Product::query()
            ->whereNotNull('sku_prefix')
            ->where('sku_prefix', '!=', '')
            ->get(['id', 'sku_prefix'])
            ->sortByDesc(fn($p) => mb_strlen($p->sku_prefix))
            ->mapWithKeys(fn($p) => [mb_strtoupper(trim($p->sku_prefix)) => (int) $p->id])
            ->all();
    }

    /**
     * Cari produk berdasarkan prefix SKU.
     *
     * Aturan:
     * - Case-insensitive
     * - Prefix harus match di AWAL string
     * - Setelah prefix harus non-huruf (spasi, -, _, ., digit, atau string habis)
     * - Longest prefix menang (diurutkan panjang desc)
     *
     * @return int|null  product_id, atau null kalau tidak match
     */
    public function resolveProductId(?string $sku): ?int
    {
        if ($sku === null) {
            return null;
        }

        $sku = mb_strtoupper(trim($sku));
        if ($sku === '') {
            return null;
        }

        $this->loadPrefixMap();

        foreach ($this->prefixMap as $prefix => $productId) {
            $prefixLen = mb_strlen($prefix);

            if (mb_substr($sku, 0, $prefixLen) !== $prefix) {
                continue;
            }

            // Boundary check: karakter setelah prefix
            $nextChar = mb_substr($sku, $prefixLen, 1);

            if ($nextChar === '' || !ctype_alpha($nextChar)) {
                return $productId;
            }
        }

        return null;
    }

    /**
     * Resolve banyak SKU sekaligus dalam 1 batch (lebih efisien).
     *
     * @param  iterable<string>  $skus
     * @return array<string,int|null>  ['SKU_ASLI' => product_id|null]
     */
    public function resolveMany(iterable $skus): array
    {
        $this->loadPrefixMap();

        $result = [];
        foreach ($skus as $sku) {
            $result[$sku] = $this->resolveProductId($sku);
        }

        return $result;
    }

    /**
     * Ambil semua product sekaligus (untuk HPP lookup).
     *
     * @return Collection<int,Product>
     */
    public function allProducts(): Collection
    {
        return Product::all();
    }

    /**
     * Reset cache (berguna untuk test atau ketika ada product baru ditambahkan
     * di tengah proses).
     */
    public function reset(): void
    {
        $this->prefixMap = null;
    }

    /**
     * Untuk testing / debug: dump prefix map.
     */
    public function debugPrefixMap(): array
    {
        $this->loadPrefixMap();
        return $this->prefixMap;
    }
}