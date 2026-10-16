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

// Simulate a POST request to the save method
$storeId = 1;

// Get a real SKU and product
$firstItem = \App\Models\ShopeeOrderItem::where('status_pesanan', 'Selesai')
    ->whereNotNull('sku_varian')
    ->where('sku_varian', '!=', '')
    ->first();

$sku = trim($firstItem->sku_varian);
$product = \App\Models\Product::first();

echo "=== Simulating POST to save with storeId=$storeId ===" . PHP_EOL;
echo "SKU: [" . $sku . "]" . PHP_EOL;
echo "Product ID: " . $product->id . PHP_EOL;

// Create a mock request
$request = Request::create('/shopee/mapping/1', 'POST');
$request->setMethod('POST');
$request->request->set('sku_varian', $sku);
$request->request->set('product_id', $product->id);
$request->request->set('page', '1');
$request->headers->set('X-CSRF-TOKEN', csrf_token());

// Call the save method directly
$controller = new ShopeeMappingController();
try {
    $response = $controller->save($request, $storeId);
    echo "Response type: " . get_class($response) . PHP_EOL;
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirect URL: " . $response->getTargetUrl() . PHP_EOL;
        $session = $response->getSession();
        if ($session) {
            echo "Session flash: " . json_encode($session->get('success')) . PHP_EOL;
        }
        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            echo "Cookie: " . $cookie->getName() . " = " . $cookie->getValue() . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}

// Check the database
echo PHP_EOL . "=== After save ===" . PHP_EOL;
$mappings = ProductMapping::where('store_id', $storeId)->get(['marketplace_sku', 'product_id']);
echo "Mappings count: " . $mappings->count() . PHP_EOL;
foreach ($mappings as $m) {
    echo "  store_id={$m->store_id} sku=[" . $m->marketplace_sku . "] hex=" . bin2hex($m->marketplace_sku) . " product_id={$m->product_id}" . PHP_EOL;
}

// Now simulate the index method
echo PHP_EOL . "=== Simulating index method ===" . PHP_EOL;
$indexRequest = Request::create('/shopee/mapping/1?page=1', 'GET');
try {
    $indexResponse = $controller->index($indexRequest, $storeId);
    echo "Index response type: " . get_class($indexResponse) . PHP_EOL;
    if ($indexResponse instanceof \Illuminate\Http\Response) {
        $content = $indexResponse->getContent();
        echo "Content length: " . strlen($content) . PHP_EOL;
        
        // Check if the selected attribute appears in the content
        $hasSelected = strpos($content, 'selected') !== false;
        echo "Has 'selected' attribute in HTML: " . ($hasSelected ? 'YES' : 'NO') . PHP_EOL;
        
        // Find the select element for the mapped SKU
        $pos = strpos($content, 'JK' ); // Look for the SKU
        if ($pos !== false) {
            // Get surrounding HTML
            $start = max(0, $pos - 200);
            $end = min(strlen($content), $pos + 500);
            $snippet = substr($content, $start, $end - $start);
            echo "HTML around SKU:" . PHP_EOL;
            echo $snippet . PHP_EOL;
        }
        
        // Search for 'selected' in the content
        if ($hasSelected) {
            $pos = strpos($content, 'selected');
            $start = max(0, $pos - 200);
            $end = min(strlen($content), $pos + 200);
            $snippet = substr($content, $start, $end - $start);
            echo "HTML around 'selected':" . PHP_EOL;
            echo $snippet . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
