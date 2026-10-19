{{-- Flash Messages --}}
@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
        <iconify-icon icon="mdi:check-circle" class="text-green-600 text-lg mt-0.5"></iconify-icon>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
        <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5"></iconify-icon>
        <div>{{ session('error') }}</div>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <iconify-icon icon="mdi:format-list-bulleted" class="text-gray-600 text-lg"></iconify-icon>
            <h3 class="font-semibold text-gray-800">Daftar Toko</h3>
        </div>
        <div class="text-xs text-gray-500">
            {{ $stores->total() }} toko
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Toko</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Marketplace</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Seller ID</th>
                    <th class="text-center py-3 px-4 w-40 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($stores as $store)
                @php
                    $market = match($store->marketplace) {
                        'shopee'    => ['icon' => 'mdi:shopping',         'bg' => 'bg-orange-50',  'text' => 'text-orange-700',  'badge' => 'bg-orange-100 text-orange-700'],
                        'tiktok'    => ['icon' => 'mdi:music-note',       'bg' => 'bg-gray-900',   'text' => 'text-white',       'badge' => 'bg-gray-100 text-gray-800'],
                        'tokopedia' => ['icon' => 'mdi:storefront',       'bg' => 'bg-green-50',   'text' => 'text-green-700',   'badge' => 'bg-green-100 text-green-700'],
                        'lazada'    => ['icon' => 'mdi:shopping-outline', 'bg' => 'bg-red-50',     'text' => 'text-red-700',     'badge' => 'bg-red-100 text-red-700'],
                        'brilink'   => ['icon' => 'mdi:bank',             'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700'],
                        default     => ['icon' => 'mdi:store',            'bg' => 'bg-gray-100',   'text' => 'text-gray-700',    'badge' => 'bg-gray-100 text-gray-700'],
                    };
                @endphp
                <tr id="store-row-{{ $store->id }}" class="hover:bg-gray-50 transition">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $market['bg'] }} rounded-lg flex items-center justify-center">
                                <iconify-icon icon="{{ $market['icon'] }}" class="{{ $market['text'] }} text-lg"></iconify-icon>
                            </div>
                            <span class="font-medium text-gray-800">{{ $store->nama_toko }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center gap-1 {{ $market['badge'] }} px-2 py-1 rounded-full text-xs font-medium">
                            {{ $store->marketplace_label }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        @if($store->seller_id)
                            <span class="font-mono text-xs text-gray-600">{{ $store->seller_id }}</span>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="inline-flex items-center gap-1">
                            <button type="button"
                                    onclick="openModal('edit', {{ $store->id }})"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded-lg text-xs font-medium transition">
                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                Edit
                            </button>
                            <button type="button"
                                    onclick="deleteStore({{ $store->id }}, '{{ addslashes($store->nama_toko) }}')"
                                    class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg text-xs font-medium transition">
                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-gray-500">
                        <iconify-icon icon="mdi:store-off-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                        <div class="mb-2">Belum ada toko.</div>
                        <button type="button"
                                onclick="openModal('create')"
                                class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                            <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon>
                            Tambah toko pertama
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($stores->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $stores->links() }}
    </div>
    @endif
</div>