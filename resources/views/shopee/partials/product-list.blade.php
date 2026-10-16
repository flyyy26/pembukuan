@if($products->count() === 0)
    <div class="text-center py-12 text-gray-400 text-sm">
        <iconify-icon icon="mdi:package-variant" class="text-4xl block mb-2"></iconify-icon>
        <div>Tidak ada produk yang cocok.</div>
    </div>
@else
    <div class="space-y-1">
        @foreach($products as $p)
            <button type="button"
                    onclick="selectProductForMapping('{{ $p->id }}', '{{ addslashes($p->sku_internal) }}', '{{ addslashes($p->nama_produk) }}')"
                    class="w-full text-left px-3 py-2.5 hover:bg-indigo-50 rounded-lg transition flex items-center gap-3 border border-transparent hover:border-indigo-200 group">
                {{-- Gambar --}}
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                    @if($p->gambar)
                        <img src="{{ $p->gambar_url }}" class="w-full h-full object-cover">
                    @else
                        <iconify-icon icon="mdi:package-variant-closed" class="text-gray-400 text-lg"></iconify-icon>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="font-mono text-xs font-medium text-indigo-700 truncate">{{ $p->sku_internal }}</span>
                        @if($p->warna || $p->ukuran)
                            <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded flex-shrink-0">
                                {{ implode(', ', array_filter([$p->warna, $p->ukuran])) }}
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-700 truncate">{{ $p->nama_produk }}</div>
                </div>

                {{-- HPP --}}
                <div class="text-right flex-shrink-0">
                    <div class="text-[10px] text-gray-400">HPP</div>
                    <div class="text-xs font-semibold text-orange-600 whitespace-nowrap">
                        Rp {{ number_format($p->hpp, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Arrow --}}
                <iconify-icon icon="mdi:chevron-right" class="text-gray-300 text-lg group-hover:text-indigo-500 transition flex-shrink-0"></iconify-icon>
            </button>
        @endforeach
    </div>
@endif