<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::orderBy('marketplace')->orderBy('nama_toko')->paginate(25);

        // Deteksi mode modal
        $openModal = $request->input('modal'); // 'create' atau ID edit
        $editingStore = null;

        if ($openModal === 'create') {
            $editingStore = new Store();
        } elseif ($openModal) {
            $editingStore = Store::find($openModal);
        }

        return view('stores.index', compact('stores', 'editingStore', 'openModal'));
    }

    public function create()
    {
        return redirect()->route('stores.index')
            ->with('success', "Toko '{$validated['nama_toko']}' berhasil ditambahkan.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|in:shopee,tiktok,tokopedia,lazada',
            'nama_toko'   => 'required|string|max:100',
            'seller_id'   => 'nullable|string|max:100',
        ]);

        $validated['is_active'] = true;

        Store::create($validated);

        return redirect()->route('stores.index')
            ->with('success', "Toko '{$validated['nama_toko']}' berhasil ditambahkan.");
    }

    public function edit(Store $store)
    {
        return view('stores.form', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'marketplace' => 'required|in:shopee,tiktok,tokopedia,lazada',
            'nama_toko'   => 'required|string|max:100',
            'seller_id'   => 'nullable|string|max:100',
        ]);

        $store->update($validated);

        return redirect()->route('stores.index')
            ->with('success', "Toko '{$store->nama_toko}' berhasil diperbarui.");
    }

    public function destroy(Store $store)
    {
        // Cek apakah masih ada import / mapping
        if ($store->imports()->count() > 0) {
            return redirect()->route('stores.index')
                ->with('error', 'Toko tidak bisa dihapus karena masih ada data import. Hapus import-nya dulu.');
        }

        if ($store->mappings()->count() > 0) {
            return redirect()->route('stores.index')
                ->with('error', 'Toko tidak bisa dihapus karena masih ada mapping produk.');
        }

        $nama = $store->nama_toko;
        $store->delete();

        return redirect()->route('stores.index')
            ->with('success', "Toko '{$nama}' berhasil dihapus.");
    }

    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|in:shopee,tiktok,tokopedia,lazada',
            'nama_toko'   => 'required|string|max:100',
            'seller_id'   => 'nullable|string|max:100',
        ]);

        $validated['is_active'] = true;

        $store = Store::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Toko '{$store->nama_toko}' berhasil ditambahkan.",
            'store'   => $store,
        ]);
    }

    public function updateAjax(Request $request, Store $store)
    {
        $validated = $request->validate([
            'marketplace' => 'required|in:shopee,tiktok,tokopedia,lazada',
            'nama_toko'   => 'required|string|max:100',
            'seller_id'   => 'nullable|string|max:100',
        ]);

        $store->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Toko '{$store->nama_toko}' berhasil diperbarui.",
            'store'   => $store,
        ]);
    }

    public function destroyAjax(Store $store)
    {
        if ($store->imports()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Toko tidak bisa dihapus karena masih ada data import.',
            ], 422);
        }

        if ($store->mappings()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Toko tidak bisa dihapus karena masih ada mapping produk.',
            ], 422);
        }

        $nama = $store->nama_toko;
        $store->delete();

        return response()->json([
            'success' => true,
            'message' => "Toko '{$nama}' berhasil dihapus.",
        ]);
    }
}