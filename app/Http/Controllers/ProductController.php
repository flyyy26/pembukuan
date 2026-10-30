<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ============================================
    // WEB (Non-AJAX — fallback)
    // ============================================
    public function index()
    {
        $products = Product::orderByDesc('id')->paginate(25);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return redirect()->route('products.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku_prefix'  => 'required|string|max:100|unique:products,sku_prefix',
            'nama_produk' => 'required|string|max:255',
            'hpp'         => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
        ]);

        $validated['is_active'] = true;

        Product::create($validated);

        $this->reconcileAllStores();

        return redirect()->route('products.index')
            ->with('success', "Kategori '{$validated['nama_produk']}' berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku_prefix'  => 'required|string|max:100|unique:products,sku_prefix,' . $product->id,
            'nama_produk' => 'required|string|max:255',
            'hpp'         => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
        ]);

        $product->update($validated);

        $this->reconcileAllStores();

        return redirect()->route('products.index')
            ->with('success', "Kategori '{$product->nama_produk}' berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        $nama = $product->nama_produk;
        $product->delete();

        $this->reconcileAllStores();

        return redirect()->route('products.index')
            ->with('success', "Kategori '{$nama}' berhasil dihapus.");
    }

    // ============================================
    // AJAX
    // ============================================
    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'sku_prefix'  => 'required|string|max:100|unique:products,sku_prefix',
            'nama_produk' => 'required|string|max:255',
            'hpp'         => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
        ]);

        $validated['is_active'] = true;

        $product = Product::create($validated);

        $this->reconcileAllStores();

        return response()->json([
            'success' => true,
            'message' => "Kategori '{$product->nama_produk}' berhasil ditambahkan.",
            'product' => $product,
        ]);
    }

    public function updateAjax(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku_prefix'  => 'required|string|max:100|unique:products,sku_prefix,' . $product->id,
            'nama_produk' => 'required|string|max:255',
            'hpp'         => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
        ]);

        $product->update($validated);

        $this->reconcileAllStores();

        return response()->json([
            'success' => true,
            'message' => "Kategori '{$product->nama_produk}' berhasil diperbarui.",
            'product' => $product,
        ]);
    }

    public function destroyAjax(Product $product)
    {
        $nama = $product->nama_produk;
        $product->delete();

        $this->reconcileAllStores();

        return response()->json([
            'success' => true,
            'message' => "Kategori '{$nama}' berhasil dihapus.",
        ]);
    }

    public function tableHtml()
    {
        $products = Product::orderByDesc('id')->paginate(25);
        return view('products.partials.table', compact('products'))->render();
    }

    // ============================================
    // HELPER
    // ============================================
    private function reconcileAllStores(): void
    {
        $stores = Store::where('is_active', true)->get();

        foreach ($stores as $store) {
            try {
                match ($store->marketplace) {
                    'shopee' => \Artisan::call('shopee:reconcile', ['store_id' => $store->id]),
                    'tiktok' => \Artisan::call('tiktok:reconcile', ['store_id' => $store->id]),
                    default  => null,
                };
            } catch (\Throwable $e) {
                \Log::warning(
                    "Reconcile gagal untuk store #{$store->id} ({$store->marketplace}): "
                    . $e->getMessage()
                );
            }
        }
    }
}