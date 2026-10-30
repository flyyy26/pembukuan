<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <iconify-icon icon="mdi:format-list-bulleted" class="text-gray-600 text-lg"></iconify-icon>
            <h3 class="font-semibold text-gray-800">Daftar Kategori</h3>
        </div>
        <div class="text-xs text-gray-500">
            {{ $products->total() }} kategori
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-center py-3 px-3 w-12 text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                    <th class="text-left py-3 px-3 w-32 text-xs font-semibold text-gray-500 uppercase tracking-wider">Prefix</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                    <th class="text-right py-3 px-3 w-32 text-xs font-semibold text-gray-500 uppercase tracking-wider">HPP</th>
                    <th class="text-center py-3 px-3 w-24 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok</th>
                    <th class="text-center py-3 px-3 w-32 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $p)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-3 px-3 text-center text-gray-400 text-xs">
                        {{ $products->firstItem() + $loop->index }}
                    </td>
                    <td class="py-3 px-3">
                        <span class="inline-block font-mono text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-md">
                            {{ $p->sku_prefix }}
                        </span>
                    </td>
                    <td class="py-3 px-3">
                        <div class="text-gray-800 font-medium text-sm">{{ $p->nama_produk }}</div>
                    </td>
                    <td class="py-3 px-3 text-right">
                        <span class="font-semibold text-orange-600 whitespace-nowrap">
                            Rp {{ number_format($p->hpp, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                        <span class="inline-block font-semibold text-gray-700">
                            {{ number_format($p->stok, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="py-3 px-3">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button"
                                    onclick="openProductModal('edit', {{ $p->id }})"
                                    class="inline-flex items-center gap-1 text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded text-xs font-medium transition"
                                    title="Edit">
                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                Edit
                            </button>
                            <button type="button"
                                    onclick="deleteProduct({{ $p->id }}, @js($p->nama_produk))"
                                    class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition"
                                    title="Hapus">
                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-500">
                        <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                        <div class="text-sm">Belum ada kategori.</div>
                        <button type="button" onclick="openProductModal('create')"
                                class="inline-flex items-center gap-1 mt-3 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-xs font-medium">
                            <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon>
                            Tambah Kategori Pertama
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                dari {{ $products->total() }} kategori
            </div>
            <div>
                {{ $products->links() }}
            </div>
        </div>
    @endif
</div>