<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductMapping;
use App\Models\ShopeeOrderItem;
use App\Models\Product;

echo "=== All ProductMappings ===" . PHP_EOL;
$mappings = ProductMapping::all(['store_id', 'marketplace_sku', 'product_id']);
foreach ($mappings as $m) {
    echo "store_id={$m->store_id} sku=[" . $m->marketplace_sku . "] hex=" . bin2hex($m->marketplace_sku) . " product_id={$m->product_id}" . PHP_EOL;
}

echo PHP_EOL . "=== ShopeeOrderItem SKUs (TRIM) ===" . PHP_EOL;
$skuGroups = ShopeeOrderItem::where('status_pesanan', 'Selesai')
    ->selectRaw('TRIM(sku_varian) as sku_varian, MAX(nama_produk) as nama_produk, MAX(nama_variasi) as nama_variasi, COUNT(*) as total')
    ->whereNotNull('sku_varian')
    ->where('sku_varian', '!=', '')
    ->groupBy('sku_varian')
    ->get();

foreach ($skuGroups as $g) {
    $sku = $g->sku_varian;
    $skuTrimmed = trim((string) $sku);
    $hex = bin2hex($skuTrimmed);
    
    // Check if this SKU exists in mappings for each store
    $foundMappings = ProductMapping::where('marketplace_sku', 'LIKE', '%' . $skuTrimmed . '%')->get(['store_id', 'marketplace_sku', 'product_id']);
    
    echo "SKU: [" . $skuTrimmed . "] hex=$hex" . PHP_EOL;
    echo "  Raw SKU hex: " . bin2hex($sku) . PHP_EOL;
    foreach ($foundMappings as $fm) {
        echo "  Mapping found: store_id={$fm->store_id} sku_hex=" . bin2hex($fm->marketplace_sku) . " product_id={$fm->product_id}" . PHP_EOL;
        echo "  Exact match: " . ($skuTrimmed === trim((string)$fm->marketplace_sku) ? 'YES' : 'NO') . PHP_EOL;
    }
    if ($foundMappings->isEmpty()) {
        echo "  No mapping found" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Products ===" . PHP_EOL;
$products = Product::orderBy('nama_produk')->get();
foreach ($products as $p) {
    echo "id={$p->id} sku_internal={$p->sku_internal} nama={$p->nama_produk}" . PHP_EOL;
}
