<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <iconify-icon icon="mdi:format-list-bulleted" class="text-gray-600 text-lg"></iconify-icon>
            <h3 class="font-semibold text-gray-800">Daftar Produk</h3>
        </div>
        <div class="text-xs text-gray-500">
            {{ $products->total() }} produk
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left py-3 px-4 w-20 text-xs font-semibold text-gray-500 uppercase tracking-wider">Gambar</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU Internal</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Produk</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Varian</th>
                    <th class="text-right py-3 px-4 w-32 text-xs font-semibold text-gray-500 uppercase tracking-wider">HPP</th>
                    <th class="text-center py-3 px-4 w-32 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr id="product-row-{{ $product->id }}" class="hover:bg-gray-50 transition">
                    <td class="py-3 px-4">
                        @if($product->gambar)
                            <img src="{{ $product->gambar_url }}"
                                 class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                        @else
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                <iconify-icon icon="mdi:image-off-outline" class="text-lg"></iconify-icon>
                            </div>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">
                            {{ $product->sku_internal }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-gray-700">{{ $product->nama_produk }}</td>
                    <td class="py-3 px-4">
                        @if($product->warna || $product->ukuran)
                            <div class="flex flex-wrap gap-1">
                                @if($product->warna)
                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                                        <iconify-icon icon="mdi:palette" class="text-xs"></iconify-icon>
                                        {{ $product->warna }}
                                    </span>
                                @endif
                                @if($product->ukuran)
                                    <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                        <iconify-icon icon="mdi:ruler" class="text-xs"></iconify-icon>
                                        {{ $product->ukuran }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-sm font-medium text-orange-600">
                            Rp {{ number_format($product->hpp, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="inline-flex items-center gap-1">
                            <button type="button"
                                    onclick="openProductModal('edit', {{ $product->id }})"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded-lg text-xs font-medium transition">
                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                Edit
                            </button>
                            <button type="button"
                                    onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->nama_produk) }}')"
                                    class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg text-xs font-medium transition">
                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-500">
                        <iconify-icon icon="mdi:package-variant" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                        <div class="mb-2">Belum ada produk.</div>
                        <button type="button"
                                onclick="openProductModal('create')"
                                class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon>
                            Tambah produk pertama
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $products->links() }}
    </div>
    @endif
</div>