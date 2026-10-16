<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMapping;
use App\Models\Store;
use App\Models\TiktokOrderItem;
use Illuminate\Http\Request;

class TiktokMappingController extends Controller
{
    public function index(Request $request, $storeId)
    {
        $store = Store::findOrFail($storeId);
        $search = trim($request->input('search', ''));

        $items = $this->buildItems($storeId, $search);
        $products = Product::orderBy('nama_produk')->get();

        // Statistik
        $totalAll = TiktokOrderItem::whereIn('order_status', ['Selesai', 'Delivered', 'Completed'])
            ->whereNotNull('seller_sku')
            ->where('seller_sku', '!=', '')
            ->distinct('seller_sku')
            ->count('seller_sku');

        $mappingsRaw = ProductMapping::where('store_id', $storeId)->get(['marketplace_sku', 'product_id']);
        $mappedAll = $mappingsRaw->where('product_id', '!=', null)->count();
        $unmappedAll = max(0, $totalAll - $mappedAll);

        $page = (int) $request->input('page', 1);
        $perPage = 25;

        return view('tiktok.mapping', compact(
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
            $item = TiktokOrderItem::where('seller_sku', $sku)->first();
            $nama = $item?->product_name;

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
            ProductMapping::where('store_id', $storeId)
                ->where('marketplace_sku', $sku)
                ->delete();
        }

        \Artisan::call('tiktok:reconcile', ['store_id' => $storeId]);

        return response()->json([
            'success' => true,
            'message' => $productId
                ? "✓ Mapping tersimpan & rekonsiliasi dijalankan."
                : "✓ Mapping dikosongkan.",
        ]);
    }

    /**
     * AJAX: Return HTML tabel.
     */
    public function tableHtml(Request $request, $storeId)
    {
        $search = trim($request->input('search', ''));
        $items = $this->buildItems($storeId, $search);
        $products = Product::orderBy('nama_produk')->get();
        $page = (int) $request->input('page', 1);
        $perPage = 25;

        $totalAll = TiktokOrderItem::whereIn('order_status', ['Selesai', 'Delivered', 'Completed'])
            ->whereNotNull('seller_sku')
            ->where('seller_sku', '!=', '')
            ->distinct('seller_sku')
            ->count('seller_sku');

        return view('tiktok.partials.table', compact(
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

        return view('tiktok.partials.product-list', compact('products'))->render();
    }

    /**
     * Helper: build items list dari TiktokOrderItem.
     */
    private function buildItems($storeId, $search = '')
    {
        $skuQuery = TiktokOrderItem::whereIn('order_status', ['Selesai', 'Delivered', 'Completed'])
            ->selectRaw('TRIM(seller_sku) as seller_sku, MAX(product_name) as product_name, MAX(variation) as variation, COUNT(*) as total')
            ->whereNotNull('seller_sku')
            ->where('seller_sku', '!=', '');

        if ($search !== '') {
            $skuQuery->where(function ($q) use ($search) {
                $q->where('seller_sku', 'LIKE', "%{$search}%")
                  ->orWhere('product_name', 'LIKE', "%{$search}%")
                  ->orWhere('variation', 'LIKE', "%{$search}%");
            });
        }

        $skuGroups = $skuQuery
            ->groupBy('seller_sku')
            ->orderBy('seller_sku')
            ->get();

        // Ambil semua order_id per seller_sku
        $allOrderItems = TiktokOrderItem::whereIn('order_status', ['Selesai', 'Delivered', 'Completed'])
            ->whereNotNull('seller_sku')
            ->where('seller_sku', '!=', '')
            ->orderByDesc('delivered_time')
            ->orderByDesc('created_time')
            ->get(['order_id', 'seller_sku'])
            ->groupBy(function ($item) {
                return trim($item->seller_sku);
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
            $skuKey = trim((string) $g->seller_sku);
            $orders = $allOrderItems->get($skuKey, collect());

            $noPesananList = $orders->pluck('order_id')->unique()->values()->toArray();

            return [
                'sku_varian'      => $skuKey,
                'nama_produk'     => $g->product_name,
                'nama_variasi'    => $g->variation,
                'total'           => $g->total,
                'product_id'      => $mappings[$skuKey] ?? null,
                'no_pesanan_list' => $noPesananList,
                'total_orders'    => count($noPesananList),
            ];
        })->slice(($page - 1) * $perPage, $perPage)->values();
    }
}