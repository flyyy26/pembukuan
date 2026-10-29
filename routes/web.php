<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopeeController;
use App\Http\Controllers\ShopeeMappingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TiktokController;
use App\Http\Controllers\TiktokImportController;
use App\Http\Controllers\TiktokMappingController;
use App\Http\Controllers\BrilinkImportController;
use App\Http\Controllers\BrilinkController;
use App\Http\Controllers\GajiKaryawanController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\ZakatController;
use App\Http\Controllers\HutangController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\ZakatPembayaranController;
use App\Http\Controllers\WebSalesReportController;
use Illuminate\Support\Facades\Route;

// ============================================
// AUTH (tidak perlu login)
// ============================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/test-web-db', function () {
        try {
            $tables = DB::connection('mysql_web')->select('SHOW TABLES');
            return response()->json([
                'status' => 'OK',
                'tables' => $tables,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

// ============================================
// SEMUA ROUTE YANG BUTUH LOGIN
// ============================================
Route::middleware('auth')->group(function () {



    Route::get('/', function () {
        return redirect('/shopee');
    });

    Route::prefix('reports/web-sales')->group(function () {
        Route::get('/', [WebSalesReportController::class, 'index'])->name('reports.web-sales.index');
        Route::get('/{id}', [WebSalesReportController::class, 'show'])->name('reports.web-sales.show');
        Route::post('/sync', [WebSalesReportController::class, 'syncNow'])->name('reports.web-sales.sync');
        Route::delete('/web-sales/{id}', [WebSalesReportController::class, 'destroy'])->name('reports.web-sales.destroy');
    });

    // ============ SHOPEE ============
    Route::get('/shopee', [ShopeeController::class, 'index'])->name('shopee.index');
    Route::get('/shopee/mapping/{store}', [ShopeeMappingController::class, 'index'])->name('shopee.mapping');
    Route::post('/shopee/mapping/{store}', [ShopeeMappingController::class, 'save'])->name('shopee.mapping.save');
    Route::post('/shopee/reconcile/{store}', [ShopeeController::class, 'reconcile'])->name('shopee.reconcile');
    Route::get('/shopee/compare', [ShopeeController::class, 'compare'])->name('shopee.compare');
    Route::get('/shopee/sku-table', [ShopeeController::class, 'skuTableHtml'])->name('shopee.sku.table');
    Route::get('/shopee/export-pdf', [ShopeeController::class, 'exportPdf'])->name('shopee.export.pdf');

    Route::get('/shopee/sku-detail', [ShopeeController::class, 'skuDetailHtml'])->name('shopee.sku.detail');

    // Shopee Mapping (AJAX)
    Route::post('/shopee/mapping/{store}/ajax', [ShopeeMappingController::class, 'saveAjax'])->name('shopee.mapping.save.ajax');
    Route::get('/shopee/mapping/{store}/products-html', [ShopeeMappingController::class, 'productsHtml'])->name('shopee.mapping.products.html');
    Route::get('/shopee/mapping/{store}/table-html', [ShopeeMappingController::class, 'tableHtml'])->name('shopee.mapping.table.html');

    // ============ TIKTOK ============
    Route::get('/tiktok', [TiktokController::class, 'index'])->name('tiktok.index');
    Route::post('/tiktok/reconcile/{store}', [TiktokController::class, 'reconcile'])->name('tiktok.reconcile');
    Route::get('/tiktok/sku-table', [TiktokController::class, 'skuTableHtml'])->name('tiktok.sku.table');
    Route::get('/tiktok/compare', [TiktokController::class, 'compare'])->name('tiktok.compare');
    Route::get('/tiktok/export-pdf', [TiktokController::class, 'exportPdf'])->name('tiktok.export.pdf');

    // TikTok Mapping
    Route::get('/tiktok/mapping/{store}', [TiktokMappingController::class, 'index'])->name('tiktok.mapping');
    Route::post('/tiktok/mapping/{store}/ajax', [TiktokMappingController::class, 'saveAjax'])->name('tiktok.mapping.save.ajax');
    Route::get('/tiktok/mapping/{store}/products-html', [TiktokMappingController::class, 'productsHtml'])->name('tiktok.mapping.products.html');
    Route::get('/tiktok/mapping/{store}/table-html', [TiktokMappingController::class, 'tableHtml'])->name('tiktok.mapping.table.html');

    // ============ IMPORT ============
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/shopee-order', [ImportController::class, 'importShopeeOrder'])->name('import.shopee.order');
    Route::post('/import/shopee', [ImportController::class, 'importShopee'])->name('import.shopee');
    Route::post('/import/shopee-both', [ImportController::class, 'importShopeeBoth'])->name('import.shopee.both');

    Route::get('/tiktok/import', [TiktokImportController::class, 'index'])->name('tiktok.import');
    Route::post('/tiktok/import', [TiktokImportController::class, 'importBoth'])->name('tiktok.import.both');

    // ============ PRODUK ============
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Produk (AJAX)
    Route::post('/products/ajax', [ProductController::class, 'storeAjax'])->name('products.store.ajax');
    Route::put('/products/{product}/ajax', [ProductController::class, 'updateAjax'])->name('products.update.ajax');
    Route::delete('/products/{product}/ajax', [ProductController::class, 'destroyAjax'])->name('products.destroy.ajax');
    Route::get('/products/table-html', [ProductController::class, 'tableHtml'])->name('products.table.html');

    // ============ TOKO ============
    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->name('stores.destroy');

    // Toko (AJAX)
    Route::post('/stores/ajax', [StoreController::class, 'storeAjax'])->name('stores.store.ajax');
    Route::put('/stores/{store}/ajax', [StoreController::class, 'updateAjax'])->name('stores.update.ajax');
    Route::delete('/stores/{store}/ajax', [StoreController::class, 'destroyAjax'])->name('stores.destroy.ajax');
    Route::get('/stores/table-html', [StoreController::class, 'tableHtml'])->name('stores.table.html');

    Route::get('/tiktok/sku-detail', [TiktokController::class, 'skuDetailHtml'])->name('tiktok.sku.detail');

    Route::get('/brilink/import',  [BrilinkImportController::class, 'index'])->name('brilink.import');
    Route::post('/brilink/import', [BrilinkImportController::class, 'import'])->name('brilink.import.store');

    Route::get('/brilink/log-ppob', [BrilinkController::class, 'logPpob'])->name('brilink.log-ppob');
    Route::post('/brilink/pengeluaran/ajax', [BrilinkController::class, 'storePengeluaranAjax'])->name('brilink.pengeluaran.store-ajax');
    Route::put('/brilink/pengeluaran/{pengeluaran}/ajax', [BrilinkController::class, 'updatePengeluaranAjax'])->name('brilink.pengeluaran.update-ajax');
    Route::delete('/brilink/pengeluaran/{pengeluaran}/ajax', [BrilinkController::class, 'destroyPengeluaranAjax'])->name('brilink.pengeluaran.destroy-ajax');
    Route::put('/brilink/log-ppob/{ppob}/ajax', [BrilinkController::class, 'updateLogPpobAjax'])->name('brilink.log-ppob.update-ajax');
    Route::delete('/brilink/log-ppob/{ppob}/ajax', [BrilinkController::class, 'destroyLogPpobAjax'])->name('brilink.log-ppob.destroy-ajax');

    Route::get('/brilink/log-setoran', [BrilinkController::class, 'logSetoran'])->name('brilink.log-setoran');
    Route::post('/brilink/{report}/validasi-mutasi', [BrilinkController::class, 'updateValidasiMutasi'])->name('brilink.update-validasi-mutasi');

    Route::get('/brilink/log-tarik-tunai', [BrilinkController::class, 'logTarikTunai'])->name('brilink.log-tarik-tunai');
    Route::post('/brilink/{report}/validasi-fisik', [BrilinkController::class, 'updateValidasiFisik'])->name('brilink.update-validasi-fisik');

    Route::post('/brilink/log-tarik-tunai/ajax', [BrilinkController::class, 'storeLogTarikTunaiAjax'])->name('brilink.log-tarik-tunai.store-ajax');
    Route::post('/brilink/log-tarik-tunai/update', [BrilinkController::class, 'updateLogTarikTunaiAjax'])->name('brilink.log-tarik-tunai.update-ajax');
    Route::delete('/brilink/log-tarik-tunai/destroy', [BrilinkController::class, 'destroyLogTarikTunaiAjax'])->name('brilink.log-tarik-tunai.destroy-ajax');

    Route::get('/brilink/saldo-keseluruhan', [BrilinkController::class, 'saldoKeseluruhan'])->name('brilink.saldo-keseluruhan');

    // Print invoice 1 item piutang (per baris)
    Route::get('/brilink/pengeluaran/{pengeluaran}/invoice-pdf', [BrilinkController::class, 'printInvoicePiutang'])->name('brilink.pengeluaran.invoice-pdf');

    Route::post('brilink/piutang/{pengeluaran}/bayar', [BrilinkController::class, 'bayarPiutangSingle'])->name('brilink.piutang.bayar-single');
    Route::post('brilink/piutang/bayar-bulk', [BrilinkController::class, 'bayarPiutangBulk'])->name('brilink.piutang.bayar-bulk');
    // Print invoice SEMUA piutang pada filter aktif (dari tombol di header tab)
    Route::get('/brilink/pengeluaran/invoice-bulk-pdf', [BrilinkController::class, 'printInvoicePiutangBulk'])->name('brilink.pengeluaran.invoice-bulk-pdf');
    Route::get('/brilink/laporan-brangkas', [BrilinkController::class, 'laporanBrangkas'])->name('brilink.laporan-brangkas');
    Route::get('/brilink/pertumbuhan-saldo', [BrilinkController::class, 'pertumbuhanSaldo'])->name('brilink.pertumbuhan-saldo');

    Route::get('/brilink/profit', [BrilinkController::class, 'profit'])->name('brilink.profit');
    Route::post('/brilink/log-ppob', [BrilinkController::class, 'storeLogPpob'])->name('brilink.log-ppob.store');
    Route::post('/brilink/log-ppob/ajax', [BrilinkController::class, 'storeLogPpobAjax'])->name('brilink.log-ppob.store-ajax');
    Route::post('/brilink/log-setoran/ajax', [BrilinkController::class, 'storeLogSetoranAjax'])->name('brilink.log-setoran.store-ajax');
    Route::put('/brilink/log-setoran/{setoran}/ajax', [BrilinkController::class, 'updateLogSetoranAjax'])->name('brilink.log-setoran.update-ajax');
    Route::delete('/brilink/log-setoran/{setoran}/ajax', [BrilinkController::class, 'destroyLogSetoranAjax'])->name('brilink.log-setoran.destroy-ajax');
    Route::post('/brilink/{report}/validasi', [BrilinkController::class, 'updateValidasi'])->name('brilink.update-validasi');
    Route::get('/brilink/pengeluaran', [BrilinkController::class, 'pengeluaran'])->name('brilink.pengeluaran');
    Route::get('/brilink',          [BrilinkController::class, 'index'])->name('brilink.index');
    Route::get('/brilink/{report}', [BrilinkController::class, 'show'])->name('brilink.show');
    Route::delete('/brilink/{report}', [BrilinkController::class, 'destroy'])->name('brilink.destroy');
    Route::delete('/brilink/import/{id}', [BrilinkImportController::class, 'destroy'])->name('brilink.import.destroy');

    // ===== KARYAWAN =====
    Route::get('karyawan',                 [GajiKaryawanController::class, 'indexKaryawan'])->name('karyawan.index');
    Route::post('karyawan/ajax',           [GajiKaryawanController::class, 'storeKaryawanAjax'])->name('karyawan.store-ajax');
    Route::match(['post', 'put'], 'karyawan/{karyawan}/ajax', [GajiKaryawanController::class, 'updateKaryawanAjax'])->name('karyawan.update-ajax');
    Route::delete('karyawan/{karyawan}/ajax',[GajiKaryawanController::class, 'destroyKaryawanAjax'])->name('karyawan.destroy-ajax');

    // ===== GAJI =====
    Route::get('gaji',                     [GajiKaryawanController::class, 'indexGaji'])->name('gaji.index');
    Route::post('gaji/generate',           [GajiKaryawanController::class, 'generateGaji'])->name('gaji.generate');
    Route::match(['post', 'put'], 'gaji/{gaji}/ajax', [GajiKaryawanController::class, 'updateGajiAjax'])->name('gaji.update-ajax');
    Route::post('gaji/{gaji}/bayar',       [GajiKaryawanController::class, 'bayarGajiAjax'])->name('gaji.bayar-ajax');
    Route::delete('gaji/{gaji}/ajax',      [GajiKaryawanController::class, 'destroyGajiAjax'])->name('gaji.destroy-ajax');
    Route::post('gaji/komisi-settings', [GajiKaryawanController::class, 'updateKomisiSettings'])->name('gaji.komisi-settings');

    // ===== LAPORAN MINUS =====
    Route::get('minus-karyawan',           [GajiKaryawanController::class, 'indexMinus'])->name('minus.index');
    Route::post('minus-karyawan/ajax',     [GajiKaryawanController::class, 'storeMinusAjax'])->name('minus.store-ajax');
    Route::match(['post', 'put'], 'minus-karyawan/{minus}/ajax', [GajiKaryawanController::class, 'updateMinusAjax'])->name('minus.update-ajax');
    Route::delete('minus-karyawan/{minus}/ajax',[GajiKaryawanController::class, 'destroyMinusAjax'])->name('minus.destroy-ajax');

    // ============ TABUNGAN ============
    Route::get('/tabungan', [TabunganController::class, 'index'])->name('tabungan.index');
    Route::post('/tabungan/account/ajax', [TabunganController::class, 'storeAccountAjax'])->name('tabungan.account.store-ajax');
    Route::put('/tabungan/account/{account}/ajax', [TabunganController::class, 'updateAccountAjax'])->name('tabungan.account.update-ajax');
    Route::delete('/tabungan/account/{account}/ajax', [TabunganController::class, 'destroyAccountAjax'])->name('tabungan.account.destroy-ajax');
    Route::post('/tabungan/transaction/ajax', [TabunganController::class, 'storeTransactionAjax'])->name('tabungan.transaction.store-ajax');
    Route::put('/tabungan/transaction/{transaction}/ajax', [TabunganController::class, 'updateTransactionAjax'])->name('tabungan.transaction.update-ajax');
    Route::delete('/tabungan/transaction/{transaction}/ajax', [TabunganController::class, 'destroyTransactionAjax'])->name('tabungan.transaction.destroy-ajax');
    Route::get('/tabungan/transaction/{transaction}/invoice-pdf', [TabunganController::class, 'printInvoice'])->name('tabungan.transaction.invoice-pdf');

    // ============ ZAKAT ============
    Route::get('/zakat', [ZakatController::class, 'index'])->name('zakat.index');
    Route::post('/zakat/barang/ajax', [ZakatController::class, 'storeBarangAjax'])->name('zakat.barang.store-ajax');
    Route::put('/zakat/barang/{barang}/ajax', [ZakatController::class, 'updateBarangAjax'])->name('zakat.barang.update-ajax');
    Route::delete('/zakat/barang/{barang}/ajax', [ZakatController::class, 'destroyBarangAjax'])->name('zakat.barang.destroy-ajax');
    Route::post('/zakat/kategori/ajax', [ZakatController::class, 'storeKategoriAjax'])->name('zakat.kategori.store-ajax');
    Route::delete('/zakat/kategori/{kategori}/ajax', [ZakatController::class, 'destroyKategoriAjax'])->name('zakat.kategori.destroy-ajax');
    Route::get('/zakat/kategori/list-ajax', [ZakatController::class, 'listKategoriAjax'])->name('zakat.kategori.list-ajax');
    Route::get('/zakat/invoice-pdf', [ZakatController::class, 'printPdf'])->name('zakat.invoice-pdf');

    Route::post('/zakat/bayar', [ZakatController::class, 'bayarZakat'])->name('zakat.bayar');
    Route::get('/laporan-bayar-zakat', [ZakatPembayaranController::class, 'index'])->name('laporan-zakat.index');
    Route::delete('/laporan-bayar-zakat/{pembayaran}', [ZakatPembayaranController::class, 'destroy'])->name('laporan-zakat.destroy');

    // CRUD Saldo Online
    Route::post('/zakat/saldo-online/ajax', [ZakatController::class, 'storeSaldoOnlineAjax'])->name('zakat.saldo-online.store-ajax');
    Route::delete('/zakat/saldo-online/{saldoOnline}/ajax', [ZakatController::class, 'destroySaldoOnlineAjax'])->name('zakat.saldo-online.destroy-ajax');

    // ============ HUTANG ============
    Route::get('/hutang', [HutangController::class, 'index'])->name('hutang.index');
    Route::post('/hutang/ajax', [HutangController::class, 'storeAjax'])->name('hutang.store-ajax');
    Route::put('/hutang/{hutang}/ajax', [HutangController::class, 'updateAjax'])->name('hutang.update-ajax');
    Route::post('/hutang/{hutang}/bayar', [HutangController::class, 'bayarAjax'])->name('hutang.bayar');
    Route::delete('/hutang/{hutang}/ajax', [HutangController::class, 'destroyAjax'])->name('hutang.destroy-ajax');

    // ============ PIUTANG ============
    Route::get('/piutang', [PiutangController::class, 'index'])->name('piutang.index');
    Route::post('/piutang/{pengeluaran}/bayar', [PiutangController::class, 'bayarPiutang'])->name('piutang.bayar');
    Route::get('/piutang/invoice-pdf', [PiutangController::class, 'printInvoiceBulk'])->name('piutang.invoice-pdf');
    Route::post('/piutang/bayar-bulk', [PiutangController::class, 'bayarPiutangBulk'])->name('piutang.bayar-bulk');

    // Laporan Live TikTok
    Route::get('/tiktok/live', [TiktokController::class, 'liveIndex'])->name('tiktok.live.index');
    Route::post('/tiktok/live/ajax', [TiktokController::class, 'liveStoreAjax'])->name('tiktok.live.store-ajax');
    Route::put('/tiktok/live/{session}/ajax', [TiktokController::class, 'liveUpdateAjax'])->name('tiktok.live.update-ajax');
    Route::delete('/tiktok/live/{session}/ajax', [TiktokController::class, 'liveDestroyAjax'])->name('tiktok.live.destroy-ajax');
    Route::delete('/tiktok/import/{id}', [TiktokImportController::class, 'destroy'])->name('tiktok.import.destroy');
});