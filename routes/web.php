<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopeeController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ShopeeMappingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TiktokController;
use App\Http\Controllers\TiktokImportController;
use App\Http\Controllers\TiktokMappingController;

Route::get('/', function () {
    return redirect('/shopee');
});

// Shopee
Route::get('/shopee', [ShopeeController::class, 'index'])->name('shopee.index');
Route::get('/shopee/mapping/{store}', [ShopeeMappingController::class, 'index'])->name('shopee.mapping');
Route::post('/shopee/mapping/{store}', [ShopeeMappingController::class, 'save'])->name('shopee.mapping.save');

// Shopee Mapping (AJAX)
Route::post('/shopee/mapping/{store}/ajax', [ShopeeMappingController::class, 'saveAjax'])->name('shopee.mapping.save.ajax');
Route::get('/shopee/mapping/{store}/products-html', [ShopeeMappingController::class, 'productsHtml'])->name('shopee.mapping.products.html');
Route::get('/shopee/mapping/{store}/table-html', [ShopeeMappingController::class, 'tableHtml'])->name('shopee.mapping.table.html');

Route::post('/shopee/reconcile/{store}', [ShopeeController::class, 'reconcile'])->name('shopee.reconcile');

// Produk (Web biasa)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// Produk (AJAX)
Route::post('/products/ajax', [ProductController::class, 'storeAjax'])->name('products.store.ajax');
Route::put('/products/{product}/ajax', [ProductController::class, 'updateAjax'])->name('products.update.ajax');
Route::delete('/products/{product}/ajax', [ProductController::class, 'destroyAjax'])->name('products.destroy.ajax');
Route::get('/products/table-html', [ProductController::class, 'tableHtml'])->name('products.table.html');

// Toko (Web biasa)
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');
Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->name('stores.destroy');

// Toko (AJAX)
Route::post('/stores/ajax', [StoreController::class, 'storeAjax'])->name('stores.store.ajax');
Route::put('/stores/{store}/ajax', [StoreController::class, 'updateAjax'])->name('stores.update.ajax');
Route::delete('/stores/{store}/ajax', [StoreController::class, 'destroyAjax'])->name('stores.destroy.ajax');
Route::get('/stores/table-html', [StoreController::class, 'tableHtml'])->name('stores.table.html');

// Import
Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::post('/import/shopee-order', [ImportController::class, 'importShopeeOrder'])->name('import.shopee.order');
Route::post('/import/shopee', [ImportController::class, 'importShopee'])->name('import.shopee');
Route::post('/import/shopee-both', [ImportController::class, 'importShopeeBoth'])->name('import.shopee.both');

Route::get('/shopee/compare', [ShopeeController::class, 'compare'])->name('shopee.compare');
Route::get('/shopee/sku-table', [ShopeeController::class, 'skuTableHtml'])->name('shopee.sku.table');
Route::get('/shopee/export-pdf', [ShopeeController::class, 'exportPdf'])->name('shopee.export.pdf');

// TikTok
Route::get('/tiktok/import', [TiktokImportController::class, 'index'])->name('tiktok.import');
Route::post('/tiktok/import', [TiktokImportController::class, 'importBoth'])->name('tiktok.import.both');

// TikTok Mapping
Route::get('/tiktok/mapping/{store}', [TiktokMappingController::class, 'index'])->name('tiktok.mapping');
Route::post('/tiktok/mapping/{store}/ajax', [TiktokMappingController::class, 'saveAjax'])->name('tiktok.mapping.save.ajax');
Route::get('/tiktok/mapping/{store}/products-html', [TiktokMappingController::class, 'productsHtml'])->name('tiktok.mapping.products.html');
Route::get('/tiktok/mapping/{store}/table-html', [TiktokMappingController::class, 'tableHtml'])->name('tiktok.mapping.table.html');

// TikTok Laporan
Route::get('/tiktok', [TiktokController::class, 'index'])->name('tiktok.index');
Route::post('/tiktok/reconcile/{store}', [TiktokController::class, 'reconcile'])->name('tiktok.reconcile');
Route::get('/tiktok/sku-table', [TiktokController::class, 'skuTableHtml'])->name('tiktok.sku.table');

// TikTok Perbandingan
Route::get('/tiktok/compare', [TiktokController::class, 'compare'])->name('tiktok.compare');

// TikTok Export PDF
Route::get('/tiktok/export-pdf', [TiktokController::class, 'exportPdf'])->name('tiktok.export.pdf');