<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductMapping;
use App\Models\ShopeeOrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

// Simulate what happens in the save method
$storeId = 1;

// Get the SKU from ShopeeOrderItem (the first one)
$firstItem = ShopeeOrderItem::where('status_pesanan', 'Selesai')
    ->whereNotNull('sku_varian')
    ->where('sku_varian', '!=', '')
    ->first();

echo "First ShopeeOrderItem SKU: [" . $firstItem->sku_varian . "]" . PHP_EOL;
echo "First SKU hex: " . bin2hex($firstItem->sku_varian) . PHP_EOL;
echo "First SKU length: " . strlen($firstItem->sku_varian) . PHP_EOL;

$sku = trim($firstItem->sku_varian);
echo "Trimmed SKU: [" . $sku . "]" . PHP_EOL;
echo "Trimmed SKU hex: " . bin2hex($sku) . PHP_EOL;

// Get a product to map to
$product = Product::first();
echo "Product id: " . $product->id . " sku_internal: " . $product->sku_internal . PHP_EOL;

// Try to save the mapping directly
try {
    $mapping = ProductMapping::updateOrCreate(
        [
            'store_id'        => $storeId,
            'marketplace_sku' => $sku,
        ],
        [
            'product_id'               => $product->id,
            'marketplace_product_name' => $firstItem->nama_produk,
        ]
    );
    echo "Mapping saved! id={$mapping->id} store_id={$mapping->store_id} sku=[" . $mapping->marketplace_sku . "] product_id={$mapping->product_id}" . PHP_EOL;
} catch (\Exception $e) {
    echo "Error saving mapping: " . $e->getMessage() . PHP_EOL;
}

// Now verify
echo PHP_EOL . "=== Verify ===" . PHP_EOL;
$mappings = ProductMapping::where('store_id', $storeId)->get(['marketplace_sku', 'product_id']);
echo "Mappings count: " . $mappings->count() . PHP_EOL;
foreach ($mappings as $m) {
    echo "  key=[" . trim((string)$m->marketplace_sku) . "] hex=" . bin2hex(trim((string)$m->marketplace_sku)) . " product_id=" . $m->product_id . PHP_EOL;
}

// Now simulate the index method logic
echo PHP_EOL . "=== Simulate index method ===" . PHP_EOL;
$skuGroups = ShopeeOrderItem::where('status_pesanan', 'Selesai')
    ->selectRaw('TRIM(sku_varian) as sku_varian, MAX(nama_produk) as nama_produk, MAX(nama_variasi) as nama_variasi, COUNT(*) as total')
    ->whereNotNull('sku_varian')
    ->where('sku_varian', '!=', '')
    ->groupBy('sku_varian')
    ->get();

$mappingsRaw = ProductMapping::where('store_id', $storeId)
    ->get(['marketplace_sku', 'product_id']);

$mappings = [];
foreach ($mappingsRaw as $m) {
    $key = trim((string) $m->marketplace_sku);
    $mappings[$key] = (int) $m->product_id;
    echo "Mapping key: [" . $key . "] hex=" . bin2hex($key) . " => " . $mappings[$key] . PHP_EOL;
}

$firstSkuGroup = $skuGroups->first();
$skuKey = trim((string) $firstSkuGroup->sku_varian);
echo "Item SKU key: [" . $skuKey . "] hex=" . bin2hex($skuKey) . PHP_EOL;
echo "Item product_id: " . json_encode($mappings[$skuKey] ?? null) . PHP_EOL;
echo "Match: " . ($mappings[$skuKey] ?? null) . PHP_EOL;

// Check with strict comparison
$productId = $mappings[$skuKey] ?? null;
if ($productId !== null) {
    $itemProductId = (int) $productId;
    $currentProductId = (int) $product->id;
    echo "itemProductId === currentProductId: " . ($itemProductId === $currentProductId ? 'true' : 'false') . PHP_EOL;
    echo "itemProductId: " . $itemProductId . " (type: " . gettype($itemProductId) . ")" . PHP_EOL;
    echo "currentProductId: " . $currentProductId . " (type: " . gettype($currentProductId) . ")" . PHP_EOL;
}
