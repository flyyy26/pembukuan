<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gray-50">
        <div class="flex items-center gap-2">
            <iconify-icon icon="mdi:format-list-bulleted" class="text-gray-600 text-lg"></iconify-icon>
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Produk</h3>
        </div>
        <div class="text-[10px] sm:text-xs text-gray-500 bg-white px-2 py-1 rounded border w-max">
            {{ $products->total() }} produk
        </div>
    </div>

    @if($products->isEmpty())
        <div class="py-12 text-center text-gray-500">
            <iconify-icon icon="mdi:package-variant" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
            <div class="mb-2 text-sm">Belum ada produk.</div>
            <button type="button" onclick="openProductModal('create')" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah produk pertama
            </button>
        </div>
    @else
        {{-- TAMPILAN TABEL (DESKTOP) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 w-20 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Gambar</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">SKU Internal</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Nama Produk</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Varian</th>
                        <th class="text-right py-3 px-4 w-32 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">HPP</th>
                        <th class="text-center py-3 px-4 w-20 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Stok</th>
                        <th class="text-center py-3 px-4 w-32 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($products as $product)
                    <tr id="product-row-{{ $product->id }}" class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            @if($product->gambar)
                                <img src="{{ $product->gambar_url }}" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            @else
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                    <iconify-icon icon="mdi:image-off-outline" class="text-lg"></iconify-icon>
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $product->sku_internal }}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700">
                            <div class="max-w-[200px] lg:max-w-[300px] truncate" title="{{ $product->nama_produk }}">
                                {{ $product->nama_produk }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($product->warna || $product->ukuran)
                                <div class="flex flex-wrap gap-1">
                                    @if($product->warna)
                                        <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                                            <iconify-icon icon="mdi:palette" class="text-xs"></iconify-icon> {{ $product->warna }}
                                        </span>
                                    @endif
                                    @if($product->ukuran)
                                        <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                            <iconify-icon icon="mdi:ruler" class="text-xs"></iconify-icon> {{ $product->ukuran }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <span class="text-sm font-medium text-orange-600">Rp {{ number_format($product->hpp, 0, ',', '.') }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-sm font-medium text-gray-800">{{ $product->stok ?? 0 }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" onclick="openProductModal('edit', {{ $product->id }})"
                                        class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded-lg text-xs font-medium transition border sm:border-0 border-blue-200">
                                    <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                </button>
                                <button type="button" onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->nama_produk) }}')"
                                        class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg text-xs font-medium transition border sm:border-0 border-red-200">
                                    <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- TAMPILAN CARD (MOBILE) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @foreach($products as $product)
                <div id="product-row-{{ $product->id }}" class="p-4 flex gap-3 text-xs">
                    {{-- Gambar --}}
                    <div class="flex-shrink-0">
                        @if($product->gambar)
                            <img src="{{ $product->gambar_url }}" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                        @else
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                <iconify-icon icon="mdi:image-off-outline" class="text-lg"></iconify-icon>
                            </div>
                        @endif
                    </div>

                    {{-- Detail --}}
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-gray-800 text-sm truncate mb-1" title="{{ $product->nama_produk }}">
                            {{ $product->nama_produk }}
                        </div>
                        
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="font-mono text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">{{ $product->sku_internal }}</span>
                            @if($product->is_active)
                                <span class="text-[10px] text-green-600 bg-green-50 px-1.5 py-0.5 rounded">Aktif</span>
                            @else
                                <span class="text-[10px] text-gray-500 bg-gray-50 px-1.5 py-0.5 rounded">Nonaktif</span>
                            @endif
                        </div>

                        @if($product->warna || $product->ukuran)
                            <div class="flex flex-wrap gap-1 mb-1.5">
                                @if($product->warna)
                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded text-[10px]">
                                        <iconify-icon icon="mdi:palette" class="text-[10px]"></iconify-icon> {{ $product->warna }}
                                    </span>
                                @endif
                                @if($product->ukuran)
                                    <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-1.5 py-0.5 rounded text-[10px]">
                                        <iconify-icon icon="mdi:ruler" class="text-[10px]"></iconify-icon> {{ $product->ukuran }}
                                    </span>
                                @endif
                            </div>
                        @endif
                        
                        <div class="grid grid-cols-2 gap-2 pt-1 mt-1 border-t border-gray-50">
                            <div>
                                <span class="text-gray-400 block text-[10px]">HPP</span>
                                <span class="font-semibold text-orange-600">Rp {{ number_format($product->hpp, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-400 block text-[10px]">Stok</span>
                                <span class="font-semibold text-gray-800">{{ $product->stok ?? 0 }}</span>
                            </div>
                        </div>

                        {{-- Tombol Aksi Mobile --}}
                        <div class="flex gap-2 pt-2 mt-2 border-t border-gray-50">
                            <button type="button" onclick="openProductModal('edit', {{ $product->id }})"
                                    class="flex-1 inline-flex justify-center items-center gap-1 text-blue-600 bg-blue-50 border border-blue-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                            </button>
                            <button type="button" onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->nama_produk) }}')"
                                    class="flex-1 inline-flex justify-center items-center gap-1 text-red-600 bg-red-50 border border-red-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($products->hasPages())
    <div class="px-4 sm:px-5 py-4 border-t border-gray-100">
        {{ $products->links() }}
    </div>
    @endif
</div>