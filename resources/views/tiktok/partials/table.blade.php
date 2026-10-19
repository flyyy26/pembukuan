<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <iconify-icon icon="mdi:table" class="text-gray-600 text-lg"></iconify-icon>
            <h3 class="font-semibold text-gray-800">Daftar Mapping</h3>
        </div>
        <div class="text-xs text-gray-500">
            Halaman {{ $page }} dari {{ max(1, ceil($totalAll / $perPage)) }}
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU Varian</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Produk TikTok</th>
                    <th class="text-left py-3 px-4 w-48 text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Pesanan</th>
                    <th class="text-center py-3 px-4 w-20 text-xs font-semibold text-gray-500 uppercase tracking-wider">Muncul</th>
                    <th class="text-left py-3 px-4 w-72 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mapping ke Produk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($items as $item)
                @php
                    $selectedProduct = $item['product_id'] ? $products->firstWhere('id', $item['product_id']) : null;
                @endphp
                <tr class="hover:bg-gray-50 transition {{ $item['product_id'] ? 'bg-green-50/30' : '' }}">
                    <td class="py-3 px-4 align-top">
                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700 break-all inline-block">
                            {{ $item['sku_varian'] }}
                        </span>
                    </td>
                    <td class="py-3 px-4 align-top">
                        <div class="text-gray-700 text-xs leading-snug break-words">{{ $item['nama_produk'] }}</div>
                        @if($item['nama_variasi'])
                            <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                <iconify-icon icon="mdi:tag-outline" class="text-xs flex-shrink-0"></iconify-icon>
                                <span class="break-all">{{ $item['nama_variasi'] }}</span>
                            </div>
                        @endif
                    </td>
                    <td class="py-3 px-4 align-top">
                        @if(!empty($item['no_pesanan_list']))
                            <div class="flex flex-col gap-1 max-h-32 overflow-y-auto pr-1">
                                @foreach($item['no_pesanan_list'] as $noPesanan)
                                    <span class="font-mono text-[10px] bg-pink-50 text-pink-700 px-1.5 py-0.5 rounded border border-pink-100 inline-block w-fit whitespace-nowrap">
                                        {{ $noPesanan }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center align-top">
                        <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap">
                            {{ $item['total'] }}x
                        </span>
                    </td>
                    <td class="py-3 px-4 align-top">
                        @if($selectedProduct)
                            @php
                                $skuText = $selectedProduct->sku_internal ?? '';
                                $namaShort = \Illuminate\Support\Str::limit($selectedProduct->nama_produk ?? '', 10, '…');
                            @endphp
                            <button type="button"
                                    onclick="openProductPicker('{{ $item['sku_varian'] }}')"
                                    title="{{ $skuText }} — {{ $selectedProduct->nama_produk }}"
                                    class="w-full flex items-center gap-2 bg-green-50 border border-green-400 rounded-lg px-3 py-2 text-xs text-left hover:bg-green-100 transition">
                                <iconify-icon icon="mdi:check-circle" class="text-green-600 text-sm flex-shrink-0"></iconify-icon>
                                <span class="min-w-0 flex-1">
                                    <span class="font-mono block truncate text-green-800 font-medium">{{ $skuText }}</span>
                                    <span class="block truncate text-[10px] text-gray-600">{{ $namaShort }}</span>
                                </span>
                                <iconify-icon icon="mdi:pencil" class="text-green-600 text-sm flex-shrink-0"></iconify-icon>
                            </button>
                        @else
                            <button type="button"
                                    onclick="openProductPicker('{{ $item['sku_varian'] }}')"
                                    class="w-full flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs text-left hover:border-blue-400 hover:bg-blue-50/30 transition">
                                <iconify-icon icon="mdi:link-variant" class="text-gray-400 text-sm flex-shrink-0"></iconify-icon>
                                <span class="text-gray-500 flex-1 truncate">-- Pilih Produk --</span>
                                <iconify-icon icon="mdi:chevron-right" class="text-gray-400 text-sm flex-shrink-0"></iconify-icon>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-gray-500">
                        <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                        Belum ada SKU. Import file Order TikTok dulu.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($totalAll > $perPage)
        @php
            $totalPages = ceil($totalAll / $perPage);
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
        @endphp
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                Menampilkan {{ ($page - 1) * $perPage + 1 }}–{{ min($page * $perPage, $totalAll) }} dari {{ $totalAll }} SKU
            </div>
            <div class="flex gap-1">
                @if($page > 1)
                    <button type="button" onclick="loadMappingPage({{ $page - 1 }})"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                        <iconify-icon icon="mdi:chevron-left" class="text-sm"></iconify-icon>
                    </button>
                @endif

                @for($i = $startPage; $i <= $endPage; $i++)
                    <button type="button" onclick="loadMappingPage({{ $i }})"
                            class="px-3 py-1.5 rounded-lg text-sm transition {{ $i == $page ? 'bg-gray-800 text-white font-medium' : 'border border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                        {{ $i }}
                    </button>
                @endfor

                @if($page < $totalPages)
                    <button type="button" onclick="loadMappingPage({{ $page + 1 }})"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                        <iconify-icon icon="mdi:chevron-right" class="text-sm"></iconify-icon>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
async function loadMappingPage(page) {
    currentPage = page;
    const container = document.getElementById('mappingTableContainer');
    if (!container) return;
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';
    try {
        const url = `/tiktok/mapping/${STORE_ID}/table-html?page=${page}&search=${encodeURIComponent(currentSearch)}`;
        const response = await fetch(url, { headers: { 'Accept': 'text/html' } });
        const html = await response.text();
        container.innerHTML = html;
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
    }
}
</script>