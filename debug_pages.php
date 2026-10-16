<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ShopeeMappingController;
use App\Models\ProductMapping;
use Illuminate\Http\Request;

// Mapping was saved from previous test, check all pages
$storeId = 1;

// Check total pages
$totalItems = \App\Models\ShopeeOrderItem::where('status_pesanan', 'Selesai')
    ->whereNotNull('sku_varian')
    ->where('sku_varian', '!=', '')
    ->groupByRaw('TRIM(sku_varian)')
    ->count();
$perPage = 25;
$totalPages = ceil($totalItems / $perPage);
echo "Total items: $totalItems, Total pages: $totalPages" . PHP_EOL;

$controller = new ShopeeMappingController();

// Check the last page where the mapped item should be
$lastPage = $totalPages;
echo PHP_EOL . "=== Checking page $lastPage ===" . PHP_EOL;

$indexRequest = Request::create("/shopee/mapping/1?page=$lastPage", 'GET');
$view = $controller->index($indexRequest, $storeId);
$rendered = $view->render();

// Check for 'selected'
$hasSelected = strpos($rendered, 'selected') !== false;
echo "Has 'selected' attribute: " . ($hasSelected ? 'YES' : 'NO') . PHP_EOL;

// Check for 'Sudah dimapping'
$hasMapped = strpos($rendered, 'Sudah dimapping') !== false;
echo "Has 'Sudah dimapping': " . ($hasMapped ? 'YES' : 'NO') . PHP_EOL;

// Check for 'bg-green-50'
$hasGreen = strpos($rendered, 'bg-green-50') !== false;
echo "Has 'bg-green-50': " . ($hasGreen ? 'YES' : 'NO') . PHP_EOL;

// Look for the mapped SKU
$sku = 'JK SILANG-HITAM BIRU-M';
$pos = strpos($rendered, $sku);
if ($pos !== false) {
    $start = max(0, $pos - 100);
    $end = min(strlen($rendered), $pos + 600);
    echo PHP_EOL . "HTML around SKU [" . $sku . "]:" . PHP_EOL;
    echo substr($rendered, $start, $end - $start) . PHP_EOL;
} else {
    echo "SKU not found on page $lastPage" . PHP_EOL;
}

// Check all pages for 'selected'
echo PHP_EOL . "=== Checking all pages for 'selected' ===" . PHP_EOL;
for ($i = 1; $i <= $totalPages; $i++) {
    $req = Request::create("/shopee/mapping/1?page=$i", 'GET');
    $v = $controller->index($req, $storeId);
    $r = $v->render();
    $hasSel = strpos($r, 'selected') !== false;
    echo "Page $i: selected=" . ($hasSel ? 'YES' : 'NO') . PHP_EOL;
}

// Cleanup
ProductMapping::query()->delete();
echo PHP_EOL . "Cleaned up test data." . PHP_EOL;
