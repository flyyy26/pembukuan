<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductMapping;
use App\Models\Store;

echo "=== All Stores ===" . PHP_EOL;
$stores = Store::all();
foreach ($stores as $s) {
    echo "id={$s->id} marketplace={$s->marketplace} nama_toko={$s->nama_toko}" . PHP_EOL;
}

echo PHP_EOL . "=== All ProductMappings (ALL stores) ===" . PHP_EOL;
$allMappings = ProductMapping::all();
echo "Total mappings: " . $allMappings->count() . PHP_EOL;
foreach ($allMappings as $m) {
    echo "id={$m->id} store_id={$m->store_id} marketplace_sku=[" . $m->marketplace_sku . "] product_id={$m->product_id}" . PHP_EOL;
}

echo PHP_EOL . "=== Check with DB::raw query ===" . PHP_EOL;
$raw = \DB::table('product_mappings')->get();
echo "Raw count: " . $raw->count() . PHP_EOL;
foreach ($raw as $r) {
    echo "store_id={$r->store_id} marketplace_sku=[" . $r->marketplace_sku . "] hex=" . bin2hex($r->marketplace_sku) . " product_id={$r->product_id}" . PHP_EOL;
}
