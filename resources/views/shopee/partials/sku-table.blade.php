<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <iconify-icon icon="mdi:chart-bar" class="text-gray-600 text-lg"></iconify-icon>
            <h3 class="font-semibold text-gray-800">Profit per SKU Varian</h3>
        </div>
        <div class="text-xs text-gray-500">
            {{ $totalProduk }} SKU
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <colgroup>
        <col style="width: 50px;">   {{-- No --}}
        <col style="width: 160px;">  {{-- SKU Varian --}}
        <col style="width: 200px;">  {{-- Produk Internal --}}
        <col style="width: 60px;">   {{-- Qty --}}
        <col style="width: 110px;">  {{-- Net Revenue --}}
        <col style="width: 100px;">  {{-- HPP --}}
        <col style="width: 110px;">  {{-- Profit --}}
    </colgroup>
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-center py-3 px-3 w-12 text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU Varian</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk Internal</th>
                    <th class="text-center py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="text-right py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Net Revenue</th>
                    <th class="text-right py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">HPP</th>
                    <th class="text-right py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($perProduk as $p)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-3 px-3 text-center text-gray-400 text-xs">
                        {{ ($page - 1) * $perPage + $loop->iteration }}
                    </td>
                    <td class="py-3 px-3">
                        <button type="button" 
                                onclick="showShopeeSkuDetail('{{ $p['marketplace_sku'] }}')"
                                class="font-mono text-xs bg-gray-100 hover:bg-orange-100 hover:text-orange-700 px-2 py-1 rounded text-gray-700 transition flex items-center gap-1">
                            {{ $p['marketplace_sku'] }}
                            <iconify-icon icon="mdi:eye-outline" class="text-xs"></iconify-icon>
                        </button>
                    </td>
                    <td class="py-3 px-3 text-gray-700 text-xs leading-snug break-words">
                        {{ $p['nama_produk'] }}
                    </td>
                    <td class="py-3 px-3 text-center font-medium text-gray-700">{{ $p['total_qty'] }}</td>
                    <td class="py-3 px-3 text-right text-gray-700">{{ number_format($p['net_revenue'], 0, ',', '.') }}</td>
                    <td class="py-3 px-3 text-right text-orange-600">{{ number_format($p['hpp_total'], 0, ',', '.') }}</td>
                    <td class="py-3 px-3 text-right font-bold {{ $p['profit'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($p['profit'], 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-gray-500">
                        <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                        <div class="text-sm mb-3">Belum ada data. Import file dan jalankan rekonsiliasi.</div>
                        <a href="{{ route('products.index') }}"
                           class="inline-flex items-center gap-1 bg-orange-600 text-white px-3 py-1.5 rounded-lg hover:bg-orange-700 transition text-xs font-medium">
                            <iconify-icon icon="mdi:tag-multiple" class="text-sm"></iconify-icon>
                            Atur Kategori & Prefix
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($totalProduk > $perPage)
        @php
            $totalPages = ceil($totalProduk / $perPage);
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
        @endphp
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                Menampilkan {{ ($page - 1) * $perPage + 1 }}–{{ min($page * $perPage, $totalProduk) }} dari {{ $totalProduk }} SKU
            </div>
            <div class="flex gap-1">
                {{-- Prev --}}
                @if($page > 1)
                    <button type="button"
                            onclick="loadSkuPage({{ $page - 1 }})"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                        <iconify-icon icon="mdi:chevron-left" class="text-sm"></iconify-icon>
                    </button>
                @endif

                {{-- First --}}
                @if($startPage > 1)
                    <button type="button"
                            onclick="loadSkuPage(1)"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                        1
                    </button>
                    @if($startPage > 2)
                        <span class="px-2 py-1.5 text-gray-400">…</span>
                    @endif
                @endif

                {{-- Page numbers --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    <button type="button"
                            onclick="loadSkuPage({{ $i }})"
                            class="px-3 py-1.5 rounded-lg text-sm transition
                                    {{ $i == $page
                                        ? 'bg-blue-600 text-white font-medium shadow-sm'
                                        : 'border border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                        {{ $i }}
                    </button>
                @endfor

                {{-- Last --}}
                @if($endPage < $totalPages)
                    @if($endPage < $totalPages - 1)
                        <span class="px-2 py-1.5 text-gray-400">…</span>
                    @endif
                    <button type="button"
                            onclick="loadSkuPage({{ $totalPages }})"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                        {{ $totalPages }}
                    </button>
                @endif

                {{-- Next --}}
                @if($page < $totalPages)
                    <button type="button"
                            onclick="loadSkuPage({{ $page + 1 }})"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                        <iconify-icon icon="mdi:chevron-right" class="text-sm"></iconify-icon>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- MODAL DETAIL SKU SHOPEE --}}
<div id="shopeeSkuDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeShopeeSkuDetail()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-orange-50">
            <h3 class="font-bold text-gray-800">Rincian Biaya & Net Revenue: <span id="shopeeDetailSkuLabel" class="text-orange-600 font-mono"></span></h3>
            <button onclick="closeShopeeSkuDetail()" class="text-gray-400 hover:text-gray-700">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>
        <div id="shopeeSkuDetailContent" class="p-4 overflow-y-auto flex-1">
            {{-- Konten di-load via AJAX --}}
        </div>
    </div>
</div>

<script>
    function showShopeeSkuDetail(sku) {
        const modal = document.getElementById('shopeeSkuDetailModal');
        const content = document.getElementById('shopeeSkuDetailContent');
        document.getElementById('shopeeDetailSkuLabel').textContent = sku;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        content.innerHTML = '<div class="text-center py-8 text-gray-500"><iconify-icon icon="mdi:loading" class="animate-spin text-2xl"></iconify-icon><br>Memuat data...</div>';

        // Ambil data via AJAX
        const params = new URLSearchParams({
            store: '{{ $storeId ?? "" }}',
            sku: sku,
            from: '{{ $from ?? "" }}',
            to: '{{ $to ?? "" }}'
        });

        fetch(`{{ route('shopee.sku.detail') }}?${params}`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
            })
            .catch(err => {
                content.innerHTML = `<div class="text-red-500 text-center py-4">Gagal memuat data: ${err.message}</div>`;
            });
    }

    function closeShopeeSkuDetail() {
        const modal = document.getElementById('shopeeSkuDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>