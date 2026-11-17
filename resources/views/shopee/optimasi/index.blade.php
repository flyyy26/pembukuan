@extends('layouts.app')

@section('title', 'Optimasi Produk Shopee')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                <iconify-icon icon="mdi:rocket-launch-outline" class="text-amber-600 text-xl"></iconify-icon>
            </div>
            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 truncate">Optimasi Produk Shopee</h2>
                <p class="text-sm text-gray-500 truncate">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('shopee.optimasi.create', ['store' => $storeId]) }}"
               class="inline-flex items-center justify-center gap-2 bg-amber-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-amber-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:plus" class="text-lg shrink-0"></iconify-icon>
                <span>Tambah Produk</span>
            </a>
            <a href="{{ route('shopee.optimasi.export.pdf', request()->only(['store','status','search'])) }}"
               target="_blank"
               class="inline-flex items-center justify-center gap-2 bg-red-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:file-pdf-box" class="text-lg shrink-0"></iconify-icon>
                <span>PDF</span>
            </a>
            <a href="{{ route('shopee.optimasi.export.excel', request()->only(['store','status','search'])) }}"
               class="inline-flex items-center justify-center gap-2 bg-green-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:microsoft-excel" class="text-lg shrink-0"></iconify-icon>
                <span>Excel</span>
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:check-circle" class="text-green-600 text-lg mt-0.5"></iconify-icon>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($stores->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-4 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:alert" class="text-yellow-600 text-lg mt-0.5"></iconify-icon>
            <div>
                <strong>Belum ada toko Shopee.</strong>
                Silakan <a href="{{ route('stores.index', ['modal' => 'create']) }}" class="underline font-semibold">tambah toko</a> dulu.
            </div>
        </div>
    @else

        {{-- ============ FILTER ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" class="flex gap-3 items-end flex-wrap">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                    <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">Semua</option>
                        <option value="aman"      {{ $status === 'aman' ? 'selected' : '' }}>Aman</option>
                        <option value="evaluasi"  {{ $status === 'evaluasi' ? 'selected' : '' }}>Perlu Evaluasi</option>
                        <option value="perhatian" {{ $status === 'perhatian' ? 'selected' : '' }}>Perlu Perhatian</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama produk / SKU..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition text-sm font-medium">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                    Filter
                </button>
                @if($status || $search)
                    <a href="{{ route('shopee.optimasi.index', ['store' => $storeId]) }}"
                       class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">
                        <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon> Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- ============ SUMMARY CARDS ============ --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 mb-1">Total Produk</div>
                <div class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-green-100 p-4">
                <div class="text-xs text-green-600 mb-1">Aman</div>
                <div class="text-2xl font-bold text-green-600">{{ $summary['aman'] }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-yellow-100 p-4">
                <div class="text-xs text-yellow-600 mb-1">Perlu Evaluasi</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $summary['evaluasi'] }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-red-100 p-4">
                <div class="text-xs text-red-600 mb-1">Perlu Perhatian</div>
                <div class="text-2xl font-bold text-red-600">{{ $summary['perhatian'] }}</div>
            </div>
        </div>

        {{-- ============ ASPEK PROBLEM ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <iconify-icon icon="mdi:clipboard-list-outline" class="text-gray-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm">Masalah per Aspek</h3>
            </div>
            <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-2">
                @foreach(\App\Models\ProductOptimization::ASPEK_AUDIT as $key => $meta)
                    @php $p = $aspekProblem[$key]; @endphp
                    <div class="border border-gray-100 rounded-lg p-2 text-center">
                        <iconify-icon icon="{{ $meta['icon'] }}" class="text-gray-400 text-base"></iconify-icon>
                        <div class="text-[10px] text-gray-500 mt-1 leading-tight">{{ $meta['label'] }}</div>
                        <div class="mt-1 flex items-center justify-center gap-1">
                            @if($p['perhatian'] > 0)
                                <span class="text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold">{{ $p['perhatian'] }}</span>
                            @endif
                            @if($p['evaluasi'] > 0)
                                <span class="text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded font-bold">{{ $p['evaluasi'] }}</span>
                            @endif
                            @if($p['total'] === 0)
                                <span class="text-[10px] text-gray-300">—</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-[10px] text-gray-400 flex items-center gap-3">
                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 bg-red-400 rounded-full"></span> Perlu Perhatian</span>
                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 bg-yellow-400 rounded-full"></span> Perlu Evaluasi</span>
            </div>
        </div>

        {{-- ============ DAFTAR PRODUK ============ --}}
        @if($items->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <iconify-icon icon="mdi:rocket-launch-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <div class="text-gray-500 mb-3">Belum ada produk optimasi.</div>
                <a href="{{ route('shopee.optimasi.create', ['store' => $storeId]) }}"
                   class="inline-flex items-center gap-2 bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition text-sm font-medium">
                    <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
                    Tambah Produk Pertama
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($items as $item)
                    @php
                        $statusMeta = \App\Models\ProductOptimization::STATUS_META[$item->status_global];
                        $statusColor = $statusMeta['color'];
                        $progress = $item->progressPerbaikan();
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition overflow-hidden flex flex-col">
                        {{-- Header status --}}
                        <div class="px-4 py-2 bg-{{ $statusColor }}-50 border-b border-{{ $statusColor }}-100 flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-{{ $statusColor }}-700">
                                <iconify-icon icon="{{ $statusMeta['icon'] }}" class="text-base"></iconify-icon>
                                <span class="text-xs font-semibold">{{ $statusMeta['label'] }}</span>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="p-4 flex-1">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold text-gray-800 text-sm truncate" title="{{ $item->nama_produk }}">
                                        {{ $item->nama_produk }}
                                    </h4>
                                    @if($item->marketplace_sku)
                                        <div class="text-[10px] font-mono text-gray-400 truncate">{{ $item->marketplace_sku }}</div>
                                    @endif
                                </div>
                                <div class="flex gap-1 flex-shrink-0">
                                    <a href="{{ route('shopee.optimasi.edit', $item) }}"
                                       class="w-7 h-7 inline-flex items-center justify-center bg-gray-100 text-gray-600 rounded hover:bg-blue-100 hover:text-blue-600 transition">
                                        <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                    </a>
                                    <button type="button" onclick="deleteOptimasi({{ $item->id }}, '{{ addslashes($item->nama_produk) }}')"
                                            class="w-7 h-7 inline-flex items-center justify-center bg-gray-100 text-gray-600 rounded hover:bg-red-100 hover:text-red-600 transition">
                                        <iconify-icon icon="mdi:trash-can-outline" class="text-sm"></iconify-icon>
                                    </button>
                                </div>
                            </div>

                            {{-- Harga --}}
                            <div class="grid grid-cols-2 gap-1 text-[11px] mb-3">
                                <div>
                                    <div class="text-gray-400">Harga</div>
                                    <div class="font-medium text-gray-700">Rp {{ number_format($item->harga_produk, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Harga Final</div>
                                    <div class="font-bold text-green-600">Rp {{ number_format($item->harga_final, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Margin</div>
                                    <div class="font-medium text-purple-600">{{ $item->margin_persen }}%</div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Terakhir Audit</div>
                                    <div class="font-medium text-gray-700">{{ $item->terakhir_audit?->format('d/m/Y') ?? '-' }}</div>
                                </div>
                            </div>

                            {{-- Aspek grid --}}
                            <div class="grid grid-cols-3 gap-1">
                                @foreach(\App\Models\ProductOptimization::ASPEK_AUDIT as $key => $meta)
                                    @php
                                        $a = $item->getAspek($key);
                                        $aMeta = \App\Models\ProductOptimization::STATUS_META[$a['status']];
                                        $sudah = $item->{"sudah_{$key}"};
                                    @endphp
                                    <div class="border border-gray-100 rounded p-1.5 flex items-center gap-1
                                                {{ $a['status'] === 'perhatian' ? 'bg-red-50 border-red-100' : ($a['status'] === 'evaluasi' ? 'bg-yellow-50 border-yellow-100' : '') }}"
                                         title="{{ $meta['label'] }}: {{ $aMeta['label'] }}{{ $a['note'] ? ' — ' . $a['note'] : '' }}">
                                        <iconify-icon icon="{{ $meta['icon'] }}" class="text-{{ $aMeta['color'] }}-500 text-xs"></iconify-icon>
                                        <span class="text-[9px] text-gray-600 truncate flex-1">{{ $meta['label'] }}</span>
                                        @if(in_array($a['status'], ['evaluasi','perhatian']))
                                            <iconify-icon icon="{{ $sudah ? 'mdi:check-circle' : 'mdi:circle-outline' }}"
                                                class="text-[10px] {{ $sudah ? 'text-green-500' : 'text-gray-300' }}"></iconify-icon>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Progress Perbaikan --}}
                            @if($progress['total'] > 0)
                                <div class="mt-2 pt-2 border-t border-gray-100">
                                    <div class="flex items-center justify-between text-[10px] mb-1">
                                        <span class="text-gray-500 flex items-center gap-1">
                                            <iconify-icon icon="mdi:progress-wrench" class="text-xs"></iconify-icon>
                                            Progress Perbaikan
                                        </span>
                                        <span class="font-bold {{ $progress['persen'] === 100 ? 'text-green-600' : ($progress['persen'] >= 50 ? 'text-blue-600' : 'text-amber-600') }}">
                                            {{ $progress['selesai'] }}/{{ $progress['total'] }} ({{ $progress['persen'] }}%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300
                                                    {{ $progress['persen'] === 100 ? 'bg-green-500' : ($progress['persen'] >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}"
                                             style="width: {{ $progress['persen'] }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif
    @endif
</div>

<script>
async function deleteOptimasi(id, nama) {
    if (!confirm(`Hapus produk optimasi "${nama}"?`)) return;

    try {
        const res = await fetch(`{{ url('shopee/optimasi') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal menghapus.');
        window.location.reload();
    } catch (err) {
        alert(err.message);
    }
}
</script>
@endsection