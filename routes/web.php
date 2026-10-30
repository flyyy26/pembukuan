<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopeeController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TiktokController;
use App\Http\Controllers\TiktokImportController;
use App\Http\Controllers\BrilinkImportController;
use App\Http\Controllers\BrilinkController;
use App\Http\Controllers\GajiKaryawanController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\ZakatController;
use App\Http\Controllers\HutangController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\ZakatPembayaranController;
use App\Http\Controllers\WebSalesReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
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
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect('/shopee');
        }

        $map = [
            'view shopee'         => '/shopee',
            'view tiktok'         => '/tiktok',
            'view tiktok live'    => '/tiktok/live',
            'view web-sales'      => '/reports/web-sales',
            'view brilink'        => '/brilink',
            'view tabungan'       => '/tabungan',
            'view hutang'         => '/hutang',
            'view piutang'        => '/piutang',
            'view zakat'          => '/zakat',
            'view karyawan'       => '/karyawan',
            'view gaji'           => '/gaji',
            'view minus'          => '/minus-karyawan',
            'view import shopee'  => '/import',
            'view import tiktok'  => '/tiktok/import',
            'view import brilink' => '/brilink/import',
            'view products'       => '/products',
            'view stores'         => '/stores',
        ];

        foreach ($map as $perm => $url) {
            if ($user->can($perm)) {
                return redirect($url);
            }
        }

        return redirect('/no-access');
    });

    // Halaman "tidak ada akses" (biar tidak 403 mentah)
    Route::get('/no-access', function () {
        return response()->view('errors.no-access', [], 200);
    })->name('no-access');

    // ============ PROFILE (SEMUA USER) ============
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    // ============ WEB SALES ============
    Route::prefix('reports/web-sales')->middleware('permission:view web-sales')->group(function () {
        Route::get('/', [WebSalesReportController::class, 'index'])->name('reports.web-sales.index');
        Route::get('/{id}', [WebSalesReportController::class, 'show'])->name('reports.web-sales.show');
        Route::post('/sync', [WebSalesReportController::class, 'syncNow'])->name('reports.web-sales.sync');
        Route::delete('/web-sales/{id}', [WebSalesReportController::class, 'destroy'])->name('reports.web-sales.destroy');
    });

    // ============ SHOPEE ============
    Route::middleware('permission:view shopee')->group(function () {
        Route::get('/shopee', [ShopeeController::class, 'index'])->name('shopee.index');

        Route::get('/shopee/mapping/{store}', function () {
            return redirect()->route('products.index')
                ->with('info', 'Mapping produk sekarang otomatis. Silakan atur Kategori & Prefix di sini.');
        })->name('shopee.mapping');

        Route::post('/shopee/reconcile/{store}', [ShopeeController::class, 'reconcile'])->name('shopee.reconcile');
        Route::get('/shopee/compare', [ShopeeController::class, 'compare'])->name('shopee.compare');
        Route::get('/shopee/sku-table', [ShopeeController::class, 'skuTableHtml'])->name('shopee.sku.table');
        Route::get('/shopee/export-pdf', [ShopeeController::class, 'exportPdf'])->name('shopee.export.pdf');
        Route::get('/shopee/sku-detail', [ShopeeController::class, 'skuDetailHtml'])->name('shopee.sku.detail');
    });

    // ============ TIKTOK ============
    Route::middleware('permission:view tiktok')->group(function () {
        Route::get('/tiktok', [TiktokController::class, 'index'])->name('tiktok.index');
        Route::post('/tiktok/reconcile/{store}', [TiktokController::class, 'reconcile'])->name('tiktok.reconcile');
        Route::get('/tiktok/sku-table', [TiktokController::class, 'skuTableHtml'])->name('tiktok.sku.table');
        Route::get('/tiktok/compare', [TiktokController::class, 'compare'])->name('tiktok.compare');
        Route::get('/tiktok/export-pdf', [TiktokController::class, 'exportPdf'])->name('tiktok.export.pdf');
        Route::get('/tiktok/sku-detail', [TiktokController::class, 'skuDetailHtml'])->name('tiktok.sku.detail');

        Route::get('/tiktok/mapping/{store}', function () {
            return redirect()->route('products.index')
                ->with('info', 'Mapping produk sekarang otomatis. Silakan atur Kategori & Prefix di sini.');
        })->name('tiktok.mapping');
    });

    // ============ TIKTOK LIVE ============
    Route::middleware('permission:view tiktok live')->group(function () {
        Route::get('/tiktok/live', [TiktokController::class, 'liveIndex'])->name('tiktok.live.index');
        Route::post('/tiktok/live/ajax', [TiktokController::class, 'liveStoreAjax'])->name('tiktok.live.store-ajax');
        Route::put('/tiktok/live/{session}/ajax', [TiktokController::class, 'liveUpdateAjax'])->name('tiktok.live.update-ajax');
        Route::delete('/tiktok/live/{session}/ajax', [TiktokController::class, 'liveDestroyAjax'])->name('tiktok.live.destroy-ajax');
    });

    // ============ IMPORT SHOPEE ============
    Route::middleware('permission:view import shopee')->group(function () {
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/shopee-order', [ImportController::class, 'importShopeeOrder'])->name('import.shopee.order');
        Route::post('/import/shopee', [ImportController::class, 'importShopee'])->name('import.shopee');
        Route::post('/import/shopee-both', [ImportController::class, 'importShopeeBoth'])->name('import.shopee.both');
        Route::delete('/import/{id}', [ImportController::class, 'destroy'])->name('import.destroy');
    });

    // ============ IMPORT TIKTOK ============
    Route::middleware('permission:view import tiktok')->group(function () {
        Route::get('/tiktok/import', [TiktokImportController::class, 'index'])->name('tiktok.import');
        Route::post('/tiktok/import', [TiktokImportController::class, 'importBoth'])->name('tiktok.import.both');
        Route::delete('/tiktok/import/{id}', [TiktokImportController::class, 'destroy'])->name('tiktok.import.destroy');
    });

    // ============ PRODUK ============
    Route::middleware('permission:view products')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::post('/products/ajax', [ProductController::class, 'storeAjax'])->name('products.store.ajax');
        Route::put('/products/{product}/ajax', [ProductController::class, 'updateAjax'])->name('products.update.ajax');
        Route::delete('/products/{product}/ajax', [ProductController::class, 'destroyAjax'])->name('products.destroy.ajax');
        Route::get('/products/table-html', [ProductController::class, 'tableHtml'])->name('products.table.html');
    });

    // ============ TOKO ============
    Route::middleware('permission:view stores')->group(function () {
        Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
        Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
        Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');
        Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->name('stores.destroy');

        Route::post('/stores/ajax', [StoreController::class, 'storeAjax'])->name('stores.store.ajax');
        Route::put('/stores/{store}/ajax', [StoreController::class, 'updateAjax'])->name('stores.update.ajax');
        Route::delete('/stores/{store}/ajax', [StoreController::class, 'destroyAjax'])->name('stores.destroy.ajax');
        Route::get('/stores/table-html', [StoreController::class, 'tableHtml'])->name('stores.table.html');
    });

    // ============ IMPORT BRILINK ============
    Route::middleware('permission:view import brilink')->group(function () {
        Route::get('/brilink/import',  [BrilinkImportController::class, 'index'])->name('brilink.import');
        Route::post('/brilink/import', [BrilinkImportController::class, 'import'])->name('brilink.import.store');
        Route::delete('/brilink/import/{id}', [BrilinkImportController::class, 'destroy'])->name('brilink.import.destroy');
    });

    // ============ BRILINK — GRUP UTAMA (view brilink) ============
    Route::middleware('permission:view brilink')->group(function () {

        // ---------- SUBMENU: SALDO ----------
        Route::middleware('permission:view brilink saldo')->group(function () {
            Route::get('/brilink/saldo-keseluruhan', [BrilinkController::class, 'saldoKeseluruhan'])->name('brilink.saldo-keseluruhan');
        });

        // ---------- SUBMENU: PERTUMBUHAN ----------
        Route::middleware('permission:view brilink pertumbuhan')->group(function () {
            Route::get('/brilink/pertumbuhan-saldo', [BrilinkController::class, 'pertumbuhanSaldo'])->name('brilink.pertumbuhan-saldo');
        });

        // ---------- SUBMENU: PENGELUARAN ----------
        Route::middleware('permission:view brilink pengeluaran')->group(function () {
            Route::get('/brilink/pengeluaran', [BrilinkController::class, 'pengeluaran'])->name('brilink.pengeluaran');
            Route::post('/brilink/pengeluaran/ajax', [BrilinkController::class, 'storePengeluaranAjax'])->name('brilink.pengeluaran.store-ajax');
            Route::put('/brilink/pengeluaran/{pengeluaran}/ajax', [BrilinkController::class, 'updatePengeluaranAjax'])->name('brilink.pengeluaran.update-ajax');
            Route::delete('/brilink/pengeluaran/{pengeluaran}/ajax', [BrilinkController::class, 'destroyPengeluaranAjax'])->name('brilink.pengeluaran.destroy-ajax');
            Route::get('/brilink/pengeluaran/{pengeluaran}/invoice-pdf', [BrilinkController::class, 'printInvoicePiutang'])->name('brilink.pengeluaran.invoice-pdf');
            Route::get('/brilink/pengeluaran/invoice-bulk-pdf', [BrilinkController::class, 'printInvoicePiutangBulk'])->name('brilink.pengeluaran.invoice-bulk-pdf');
            Route::post('brilink/piutang/{pengeluaran}/bayar', [BrilinkController::class, 'bayarPiutangSingle'])->name('brilink.piutang.bayar-single');
            Route::post('brilink/piutang/bayar-bulk', [BrilinkController::class, 'bayarPiutangBulk'])->name('brilink.piutang.bayar-bulk');
        });

        // ---------- SUBMENU: PPOB ----------
        Route::middleware('permission:view brilink ppob')->group(function () {
            Route::get('/brilink/log-ppob', [BrilinkController::class, 'logPpob'])->name('brilink.log-ppob');
            Route::post('/brilink/log-ppob', [BrilinkController::class, 'storeLogPpob'])->name('brilink.log-ppob.store');
            Route::post('/brilink/log-ppob/ajax', [BrilinkController::class, 'storeLogPpobAjax'])->name('brilink.log-ppob.store-ajax');
            Route::put('/brilink/log-ppob/{ppob}/ajax', [BrilinkController::class, 'updateLogPpobAjax'])->name('brilink.log-ppob.update-ajax');
            Route::delete('/brilink/log-ppob/{ppob}/ajax', [BrilinkController::class, 'destroyLogPpobAjax'])->name('brilink.log-ppob.destroy-ajax');
        });

        // ---------- SUBMENU: SETORAN ----------
        Route::middleware('permission:view brilink setoran')->group(function () {
            Route::get('/brilink/log-setoran', [BrilinkController::class, 'logSetoran'])->name('brilink.log-setoran');
            Route::post('/brilink/log-setoran/ajax', [BrilinkController::class, 'storeLogSetoranAjax'])->name('brilink.log-setoran.store-ajax');
            Route::put('/brilink/log-setoran/{setoran}/ajax', [BrilinkController::class, 'updateLogSetoranAjax'])->name('brilink.log-setoran.update-ajax');
            Route::delete('/brilink/log-setoran/{setoran}/ajax', [BrilinkController::class, 'destroyLogSetoranAjax'])->name('brilink.log-setoran.destroy-ajax');
        });

        // ---------- SUBMENU: TARIK TUNAI ----------
        Route::middleware('permission:view brilink tarik-tunai')->group(function () {
            Route::get('/brilink/log-tarik-tunai', [BrilinkController::class, 'logTarikTunai'])->name('brilink.log-tarik-tunai');
            Route::post('/brilink/log-tarik-tunai/ajax', [BrilinkController::class, 'storeLogTarikTunaiAjax'])->name('brilink.log-tarik-tunai.store-ajax');
            Route::post('/brilink/log-tarik-tunai/update', [BrilinkController::class, 'updateLogTarikTunaiAjax'])->name('brilink.log-tarik-tunai.update-ajax');
            Route::delete('/brilink/log-tarik-tunai/destroy', [BrilinkController::class, 'destroyLogTarikTunaiAjax'])->name('brilink.log-tarik-tunai.destroy-ajax');
        });

        // ---------- SUBMENU: PROFIT ----------
        Route::middleware('permission:view brilink profit')->group(function () {
            Route::get('/brilink/profit', [BrilinkController::class, 'profit'])->name('brilink.profit');
        });

        // ---------- SUBMENU: BRANGKAS ----------
        Route::middleware('permission:view brilink brangkas')->group(function () {
            Route::get('/brilink/laporan-brangkas', [BrilinkController::class, 'laporanBrangkas'])->name('brilink.laporan-brangkas');
        });

        // ---------- ROUTE UTAMA BRILINK ----------
        Route::get('/brilink', [BrilinkController::class, 'index'])->name('brilink.index');

        // ---------- WILDCARD {report} — PALING AKHIR ----------
        Route::post('/brilink/{report}/validasi', [BrilinkController::class, 'updateValidasi'])->name('brilink.update-validasi');
        Route::post('/brilink/{report}/validasi-mutasi', [BrilinkController::class, 'updateValidasiMutasi'])->name('brilink.update-validasi-mutasi');
        Route::post('/brilink/{report}/validasi-fisik', [BrilinkController::class, 'updateValidasiFisik'])->name('brilink.update-validasi-fisik');
        Route::get('/brilink/{report}', [BrilinkController::class, 'show'])->name('brilink.show');
        Route::delete('/brilink/{report}', [BrilinkController::class, 'destroy'])->name('brilink.destroy');
    });

    // ===== KARYAWAN =====
    Route::middleware('permission:view karyawan')->group(function () {
        Route::get('karyawan', [GajiKaryawanController::class, 'indexKaryawan'])->name('karyawan.index');
        Route::post('karyawan/ajax', [GajiKaryawanController::class, 'storeKaryawanAjax'])->name('karyawan.store-ajax');
        Route::match(['post', 'put'], 'karyawan/{karyawan}/ajax', [GajiKaryawanController::class, 'updateKaryawanAjax'])->name('karyawan.update-ajax');
        Route::delete('karyawan/{karyawan}/ajax', [GajiKaryawanController::class, 'destroyKaryawanAjax'])->name('karyawan.destroy-ajax');
    });

    // ===== GAJI =====
    Route::middleware('permission:view gaji')->group(function () {
        Route::get('gaji', [GajiKaryawanController::class, 'indexGaji'])->name('gaji.index');
        Route::post('gaji/generate', [GajiKaryawanController::class, 'generateGaji'])->name('gaji.generate');
        Route::match(['post', 'put'], 'gaji/{gaji}/ajax', [GajiKaryawanController::class, 'updateGajiAjax'])->name('gaji.update-ajax');
        Route::post('gaji/{gaji}/bayar', [GajiKaryawanController::class, 'bayarGajiAjax'])->name('gaji.bayar-ajax');
        Route::delete('gaji/{gaji}/ajax', [GajiKaryawanController::class, 'destroyGajiAjax'])->name('gaji.destroy-ajax');
        Route::post('gaji/komisi-settings', [GajiKaryawanController::class, 'updateKomisiSettings'])->name('gaji.komisi-settings');
    });

    // ===== LAPORAN MINUS =====
    Route::middleware('permission:view minus')->group(function () {
        Route::get('minus-karyawan', [GajiKaryawanController::class, 'indexMinus'])->name('minus.index');
        Route::post('minus-karyawan/ajax', [GajiKaryawanController::class, 'storeMinusAjax'])->name('minus.store-ajax');
        Route::match(['post', 'put'], 'minus-karyawan/{minus}/ajax', [GajiKaryawanController::class, 'updateMinusAjax'])->name('minus.update-ajax');
        Route::delete('minus-karyawan/{minus}/ajax', [GajiKaryawanController::class, 'destroyMinusAjax'])->name('minus.destroy-ajax');
    });

    // ============ TABUNGAN ============
    Route::middleware('permission:view tabungan')->group(function () {
        Route::get('/tabungan', [TabunganController::class, 'index'])->name('tabungan.index');
        Route::post('/tabungan/account/ajax', [TabunganController::class, 'storeAccountAjax'])->name('tabungan.account.store-ajax');
        Route::put('/tabungan/account/{account}/ajax', [TabunganController::class, 'updateAccountAjax'])->name('tabungan.account.update-ajax');
        Route::delete('/tabungan/account/{account}/ajax', [TabunganController::class, 'destroyAccountAjax'])->name('tabungan.account.destroy-ajax');
        Route::post('/tabungan/transaction/ajax', [TabunganController::class, 'storeTransactionAjax'])->name('tabungan.transaction.store-ajax');
        Route::put('/tabungan/transaction/{transaction}/ajax', [TabunganController::class, 'updateTransactionAjax'])->name('tabungan.transaction.update-ajax');
        Route::delete('/tabungan/transaction/{transaction}/ajax', [TabunganController::class, 'destroyTransactionAjax'])->name('tabungan.transaction.destroy-ajax');
        Route::get('/tabungan/transaction/{transaction}/invoice-pdf', [TabunganController::class, 'printInvoice'])->name('tabungan.transaction.invoice-pdf');
    });

    // ============ ZAKAT ============
    Route::middleware('permission:view zakat')->group(function () {
        Route::get('/zakat', [ZakatController::class, 'index'])->name('zakat.index');

        // CRUD zakat barang mandiri (lama — akan dihapus dari UI)
        Route::post('/zakat/barang/ajax', [ZakatController::class, 'storeBarangAjax'])->name('zakat.barang.store-ajax');
        Route::put('/zakat/barang/{barang}/ajax', [ZakatController::class, 'updateBarangAjax'])->name('zakat.barang.update-ajax');
        Route::delete('/zakat/barang/{barang}/ajax', [ZakatController::class, 'destroyBarangAjax'])->name('zakat.barang.destroy-ajax');

        // Kategori
        Route::post('/zakat/kategori/ajax', [ZakatController::class, 'storeKategoriAjax'])->name('zakat.kategori.store-ajax');
        Route::delete('/zakat/kategori/{kategori}/ajax', [ZakatController::class, 'destroyKategoriAjax'])->name('zakat.kategori.destroy-ajax');
        Route::get('/zakat/kategori/list-ajax', [ZakatController::class, 'listKategoriAjax'])->name('zakat.kategori.list-ajax');

        Route::get('/zakat/invoice-pdf', [ZakatController::class, 'printPdf'])->name('zakat.invoice-pdf');
        Route::post('/zakat/bayar', [ZakatController::class, 'bayarZakat'])->name('zakat.bayar');

        Route::post('/zakat/saldo-online/ajax', [ZakatController::class, 'storeSaldoOnlineAjax'])->name('zakat.saldo-online.store-ajax');
        Route::delete('/zakat/saldo-online/{saldoOnline}/ajax', [ZakatController::class, 'destroySaldoOnlineAjax'])->name('zakat.saldo-online.destroy-ajax');

        // Laporan bayar zakat
        Route::get('/laporan-bayar-zakat', [ZakatPembayaranController::class, 'index'])->name('laporan-zakat.index');
        Route::delete('/laporan-bayar-zakat/{pembayaran}', [ZakatPembayaranController::class, 'destroy'])->name('laporan-zakat.destroy');
    });

    // ============ HUTANG ============
    Route::middleware('permission:view hutang')->group(function () {
        Route::get('/hutang', [HutangController::class, 'index'])->name('hutang.index');
        Route::post('/hutang/ajax', [HutangController::class, 'storeAjax'])->name('hutang.store-ajax');
        Route::put('/hutang/{hutang}/ajax', [HutangController::class, 'updateAjax'])->name('hutang.update-ajax');
        Route::post('/hutang/{hutang}/bayar', [HutangController::class, 'bayarAjax'])->name('hutang.bayar');
        Route::delete('/hutang/{hutang}/ajax', [HutangController::class, 'destroyAjax'])->name('hutang.destroy-ajax');
    });

    // ============ PIUTANG ============
    Route::middleware('permission:view piutang')->group(function () {
        Route::get('/piutang', [PiutangController::class, 'index'])->name('piutang.index');
        Route::post('/piutang/{pengeluaran}/bayar', [PiutangController::class, 'bayarPiutang'])->name('piutang.bayar');
        Route::get('/piutang/invoice-pdf', [PiutangController::class, 'printInvoiceBulk'])->name('piutang.invoice-pdf');
        Route::post('/piutang/bayar-bulk', [PiutangController::class, 'bayarPiutangBulk'])->name('piutang.bayar-bulk');
    });

    // ============================================
    // USER MANAGEMENT (KHUSUS ADMIN)
    // ============================================
    Route::middleware('role:admin')->group(function () {
        Route::post('/karyawan/{karyawan}/attach-user', [UserManagementController::class, 'attachToKaryawan'])->name('karyawan.attach-user');
        Route::put('/users/{user}/permissions', [UserManagementController::class, 'updatePermissions'])->name('users.update-permissions');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle-login', [UserManagementController::class, 'toggleLogin'])->name('users.toggle-login');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/permissions', [UserManagementController::class, 'listPermissions'])->name('users.permissions');
    });
});