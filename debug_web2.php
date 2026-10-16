<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ShopeeMappingController;
use App\Models\ProductMapping;
use Illuminate\Http\Request;

// Clean up any test data first
ProductMapping::query()->delete();

$storeId = 1;

// Get a real SKU and product
$firstItem = \App\Models\ShopeeOrderItem::where('status_pesanan', 'Selesai')
    ->whereNotNull('sku_varian')
    ->where('sku_varian', '!=', '')
    ->first();

$sku = trim($firstItem->sku_varian);
$product = \App\Models\Product::first();

echo "Simulating save with SKU=[" . $sku . "] product_id=" . $product->id . PHP_EOL;

// Create a mock request
$request = Request::create('/shopee/mapping/1', 'POST');
$request->setMethod('POST');
$request->request->set('sku_varian', $sku);
$request->request->set('product_id', $product->id);
$request->request->set('page', '1');

// Call the save method
$controller = new ShopeeMappingController();
$response = $controller->save($request, $storeId);

// Check DB after save
echo PHP_EOL . "=== After save (DB check) ===" . PHP_EOL;
$allMappings = ProductMapping::all(['store_id', 'marketplace_sku', 'product_id']);
echo "Total mappings in DB: " . $allMappings->count() . PHP_EOL;
foreach ($allMappings as $m) {
    echo "  store_id={$m->store_id} sku=[" . $m->marketplace_sku . "] product_id={$m->product_id}" . PHP_EOL;
}

// Now simulate the index method and render the view
echo PHP_EOL . "=== Simulating index method ===" . PHP_EOL;
$indexRequest = Request::create('/shopee/mapping/1?page=1', 'GET');
$view = $controller->index($indexRequest, $storeId);

// The index method returns a View object, need to render it
$rendered = $view->render();

// Check for 'selected' in the rendered HTML
$hasSelected = strpos($rendered, 'selected') !== false;
echo "Has 'selected' attribute in rendered HTML: " . ($hasSelected ? 'YES' : 'NO') . PHP_EOL;

// Find the row for our SKU
$pos = strpos($rendered, $sku);
if ($pos !== false) {
    $start = max(0, $pos - 100);
    $end = min(strlen($rendered), $pos + 800);
    $snippet = substr($rendered, $start, $end - $start);
    echo PHP_EOL . "HTML around SKU [" . $sku . "]:" . PHP_EOL;
    echo $snippet . PHP_EOL;
} else {
    echo "SKU not found in rendered HTML!" . PHP_EOL;
}

// Look for our product_id in the select options
$pos2 = strpos($rendered, 'value="' . $product->id . '"');
if ($pos2 !== false) {
    $start = max(0, $pos2 - 100);
    $end = min(strlen($rendered), $pos2 + 300);
    $snippet = substr($rendered, $start, $end - $start);
    echo PHP_EOL . "HTML around option value=" . $product->id . ":" . PHP_EOL;
    echo $snippet . PHP_EOL;
} else {
    echo "Product option not found in rendered HTML!" . PHP_EOL;
}

// Check if the green background class is applied
$hasGreen = strpos($rendered, 'bg-green-50') !== false;
echo PHP_EOL . "Has 'bg-green-50' class: " . ($hasGreen ? 'YES' : 'NO') . PHP_EOL;

// Check if 'Sudah dimapping' appears
$hasMapped = strpos($rendered, 'Sudah dimapping') !== false;
echo "Has 'Sudah dimapping' text: " . ($hasMapped ? 'YES' : 'NO') . PHP_EOL;

// Check the debug section (from compiled view)
if (strpos($rendered, 'DEBUG') !== false) {
    echo PHP_EOL . "DEBUG section found in rendered HTML!" . PHP_EOL;
    $debugPos = strpos($rendered, 'DEBUG');
    $start = max(0, $debugPos - 50);
    $end = min(strlen($rendered), $debugPos + 800);
    $snippet = substr($rendered, $start, $end - $start);
    echo "Debug section:" . PHP_EOL;
    echo $snippet . PHP_EOL;
}
