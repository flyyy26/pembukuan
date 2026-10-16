<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMapping;
use App\Models\ShopeeOrderItem;
use App\Models\Store;
use Illuminate\Http\Request;

class ShopeeMappingController extends Controller
{
    public function index(Request $request, $storeId)
    {
        $store = Store::findOrFail($storeId);
        $search = trim($request->input('search', ''));

        $items = $this->buildItems($storeId, $search);
        $products = Product::orderBy('nama_produk')->get();

        // Statistik
        $totalAll = ShopeeOrderItem::where('status_pesanan', 'Selesai')
            ->whereNotNull('sku_varian')
            ->where('sku_varian', '!=', '')
            ->distinct('sku_varian')
            ->count('sku_varian');

        $mappingsRaw = ProductMapping::where('store_id', $storeId)->get(['marketplace_sku', 'product_id']);
        $mappedAll = $mappingsRaw->where('product_id', '!=', null)->count();
        $unmappedAll = max(0, $totalAll - $mappedAll);

        $page = (int) $request->input('page', 1);
        $perPage = 25;

        return view('shopee.mapping', compact(
            'store', 'items', 'products', 'page', 'perPage',
            'totalAll', 'mappedAll', 'unmappedAll', 'storeId', 'search'
        ));
    }

    /**
     * AJAX: Simpan mapping.
     */
    public function saveAjax(Request $request, $storeId)
    {
        $request->validate([
            'sku_varian' => 'required|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $sku = trim($request->input('sku_varian'));
        $productId = $request->input('product_id');

        if ($productId) {
            $item = ShopeeOrderItem::where('sku_varian', $sku)->first();
            $nama = $item?->nama_produk;

            ProductMapping::updateOrCreate(
                [
                    'store_id'        => $storeId,
                    'marketplace_sku' => $sku,
                ],
                [
                    'product_id'               => $productId,
                    'marketplace_product_name' => $nama,
                ]
            );
        } else {
            // Kosongkan mapping
            ProductMapping::where('store_id', $storeId)
                ->where('marketplace_sku', $sku)
                ->delete();
        }

        // Auto-reconcile
        \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);

        return response()->json([
            'success' => true,
            'message' => $productId
                ? "✓ Mapping tersimpan & rekonsiliasi dijalankan."
                : "✓ Mapping dikosongkan.",
        ]);
    }

    /**
     * AJAX: Return HTML tabel (untuk refresh tanpa reload).
     */
    public function tableHtml(Request $request, $storeId)
    {
        $search = trim($request->input('search', ''));
        $items = $this->buildItems($storeId, $search);
        $products = Product::orderBy('nama_produk')->get();
        $page = (int) $request->input('page', 1);
        $perPage = 25;
        $totalAll = ShopeeOrderItem::where('status_pesanan', 'Selesai')
            ->whereNotNull('sku_varian')
            ->where('sku_varian', '!=', '')
            ->distinct('sku_varian')
            ->count('sku_varian');
        $storeId = $storeId;

        return view('shopee.partials.table', compact(
            'items', 'products', 'page', 'perPage', 'totalAll', 'storeId', 'search'
        ))->render();
    }

    /**
     * AJAX: Return HTML daftar produk (untuk popup).
     */
    public function productsHtml(Request $request, $storeId)
    {
        $search = trim($request->input('search', ''));

        $query = Product::orderBy('nama_produk');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sku_internal', 'LIKE', "%{$search}%")
                  ->orWhere('nama_produk', 'LIKE', "%{$search}%")
                  ->orWhere('warna', 'LIKE', "%{$search}%")
                  ->orWhere('ukuran', 'LIKE', "%{$search}%");
            });
        }
        $products = $query->get();

        return view('shopee.partials.product-list', compact('products'))->render();
    }

    /**
     * Helper: build items list.
     */
    private function buildItems($storeId, $search = '')
    {
        $skuQuery = ShopeeOrderItem::where('status_pesanan', 'Selesai')
            ->selectRaw('TRIM(sku_varian) as sku_varian, MAX(nama_produk) as nama_produk, MAX(nama_variasi) as nama_variasi, COUNT(*) as total')
            ->whereNotNull('sku_varian')
            ->where('sku_varian', '!=', '');

        if ($search !== '') {
            $skuQuery->where(function ($q) use ($search) {
                $q->where('sku_varian', 'LIKE', "%{$search}%")
                ->orWhere('nama_produk', 'LIKE', "%{$search}%")
                ->orWhere('nama_variasi', 'LIKE', "%{$search}%");
            });
        }

        $skuGroups = $skuQuery
            ->groupBy('sku_varian')
            ->orderBy('sku_varian')
            ->get();

        // Ambil SEMUA no_pesanan per sku_varian
        $allOrderItems = ShopeeOrderItem::where('status_pesanan', 'Selesai')
            ->whereNotNull('sku_varian')
            ->where('sku_varian', '!=', '')
            ->orderByDesc('waktu_pesanan_selesai')
            ->orderByDesc('waktu_pesanan_dibuat')
            ->get(['no_pesanan', 'sku_varian'])
            ->groupBy(function ($item) {
                return trim($item->sku_varian);
            });

        $mappingsRaw = ProductMapping::where('store_id', $storeId)->get(['marketplace_sku', 'product_id']);
        $mappings = [];
        foreach ($mappingsRaw as $m) {
            $key = trim((string) $m->marketplace_sku);
            $mappings[$key] = (int) $m->product_id;
        }

        $page = (int) request()->input('page', 1);
        $perPage = 25;

        return $skuGroups->map(function ($g) use ($mappings, $allOrderItems) {
            $skuKey = trim((string) $g->sku_varian);
            $orders = $allOrderItems->get($skuKey, collect());

            // Ambil SEMUA no_pesanan unik
            $noPesananList = $orders->pluck('no_pesanan')->unique()->values()->toArray();
            $totalOrders = count($noPesananList);

            return [
                'sku_varian'      => $skuKey,
                'nama_produk'     => $g->nama_produk,
                'nama_variasi'    => $g->nama_variasi,
                'total'           => $g->total,
                'product_id'      => $mappings[$skuKey] ?? null,
                'no_pesanan_list' => $noPesananList,
                'total_orders'    => $totalOrders,
            ];
        })->slice(($page - 1) * $perPage, $perPage)->values();
    }

    /**
     * Web (non-AJAX) — fallback.
     */
    public function save(Request $request, $storeId)
    {
        $request->validate([
            'sku_varian' => 'required|string',
            'product_id' => 'required|exists:products,id',
        ]);

        $sku = trim($request->input('sku_varian'));
        $productId = (int) $request->input('product_id');

        $item = ShopeeOrderItem::where('sku_varian', $sku)->first();
        $nama = $item?->nama_produk;

        ProductMapping::updateOrCreate(
            [
                'store_id'        => $storeId,
                'marketplace_sku' => $sku,
            ],
            [
                'product_id'               => $productId,
                'marketplace_product_name' => $nama,
            ]
        );

        \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);

        $product = Product::find($productId);
        $productName = $product?->sku_internal ?? 'Unknown';

        $params = ['store' => $storeId, 'page' => $request->input('page', 1)];
        if ($request->filled('search')) {
            $params['search'] = $request->input('search');
        }

        return redirect()
            ->route('shopee.mapping', $params)
            ->with('success', "✓ Tersimpan & rekonsiliasi: {$sku} → {$productName}");
    }
}