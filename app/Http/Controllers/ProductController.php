<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'sku_internal'       => 'required|string|max:100|unique:products,sku_internal',
            'nama_produk'        => 'required|string|max:255',
            'warna'              => 'nullable|string|max:50',
            'ukuran'             => 'nullable|string|max:50',
            'hpp'                => 'required|integer|min:0',
            'stok'               => 'required|integer|min:0',
            'is_active'          => 'nullable|boolean',
            'gambar'             => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', "Produk '{$validated['nama_produk']}' berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku_internal'       => 'required|string|max:100|unique:products,sku_internal,' . $product->id,
            'nama_produk'        => 'required|string|max:255',
            'warna'              => 'nullable|string|max:50',
            'ukuran'             => 'nullable|string|max:50',
            'hpp'                => 'required|integer|min:0',
            'stok'               => 'required|integer|min:0',
            'is_active'          => 'nullable|boolean',
            'gambar'             => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            if ($product->gambar) {
                Storage::disk('public')->delete($product->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', $product->is_active);

        $product->update($validated);

        $storeIds = ProductMapping::where('product_id', $product->id)
            ->distinct()
            ->pluck('store_id');

        foreach ($storeIds as $storeId) {
            \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);
        }

        return redirect()->route('products.index')
            ->with('success', "Produk '{$product->nama_produk}' berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        if ($product->mappings()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', "Produk tidak bisa dihapus karena sudah dimapping ke SKU marketplace.");
        }

        $storeIds = ProductMapping::where('product_id', $product->id)
            ->distinct()
            ->pluck('store_id');

        if ($product->gambar) {
            Storage::disk('public')->delete($product->gambar);
        }

        $nama = $product->nama_produk;
        $product->delete();

        foreach ($storeIds as $storeId) {
            \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);
        }

        return redirect()->route('products.index')
            ->with('success', "Produk '{$nama}' berhasil dihapus.");
    }

    // ============================================
    // AJAX
    // ============================================
    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'sku_internal'       => 'required|string|max:100|unique:products,sku_internal',
            'nama_produk'        => 'required|string|max:255',
            'warna'              => 'nullable|string|max:50',
            'ukuran'             => 'nullable|string|max:50',
            'hpp'                => 'required|integer|min:0',
            'stok'               => 'required|integer|min:0',
            'is_active'          => 'nullable|boolean',
            'gambar'             => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Produk '{$product->nama_produk}' berhasil ditambahkan.",
            'product' => $product,
        ]);
    }

    public function updateAjax(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku_internal'       => 'required|string|max:100|unique:products,sku_internal,' . $product->id,
            'nama_produk'        => 'required|string|max:255',
            'warna'              => 'nullable|string|max:50',
            'ukuran'             => 'nullable|string|max:50',
            'hpp'                => 'required|integer|min:0',
            'stok'               => 'required|integer|min:0',
            'is_active'          => 'nullable|boolean',
            'gambar'             => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            if ($product->gambar) {
                Storage::disk('public')->delete($product->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', $product->is_active);

        $product->update($validated);

        $storeIds = ProductMapping::where('product_id', $product->id)
            ->distinct()
            ->pluck('store_id');

        foreach ($storeIds as $storeId) {
            \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);
        }

        return response()->json([
            'success' => true,
            'message' => "Produk '{$product->nama_produk}' berhasil diperbarui.",
            'product' => $product,
        ]);
    }

    public function destroyAjax(Product $product)
    {
        if ($product->mappings()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak bisa dihapus karena sudah dimapping ke SKU marketplace.',
            ], 422);
        }

        $storeIds = ProductMapping::where('product_id', $product->id)
            ->distinct()
            ->pluck('store_id');

        if ($product->gambar) {
            Storage::disk('public')->delete($product->gambar);
        }

        $nama = $product->nama_produk;
        $product->delete();

        foreach ($storeIds as $storeId) {
            \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);
        }

        return response()->json([
            'success' => true,
            'message' => "Produk '{$nama}' berhasil dihapus.",
        ]);
    }

    public function tableHtml()
    {
        $products = Product::orderByDesc('id')->paginate(25);
        return view('products.partials.table', compact('products'))->render();
    }
}