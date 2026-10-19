@extends('layouts.app')

@section('title', 'Perbandingan Periode Shopee')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:compare" class="text-purple-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Perbandingan Periode</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>

        <a href="{{ route('shopee.index', ['store' => $storeId]) }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            <iconify-icon icon="mdi:arrow-left" class="text-lg"></iconify-icon>
            Kembali ke Laporan
        </a>
    </div>

    {{-- ============ FILTER PERIODE ============ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" action="{{ route('shopee.compare') }}">

            {{-- Pilih Toko --}}
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store"
                        class="w-full md:w-64 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_toko }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Dua Periode Side by Side --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Periode A --}}
                <div class="border-2 border-blue-200 rounded-lg p-4 bg-blue-50/30">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold text-sm">
                            A
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm">Periode A</div>
                            <div class="text-[10px] text-gray-500">Periode pembanding (lama)</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Dari</label>
                            <input type="date" name="a_from" value="{{ $aFrom }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Sampai</label>
                            <input type="date" name="a_to" value="{{ $aTo }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                {{-- Periode B --}}
                <div class="border-2 border-purple-200 rounded-lg p-4 bg-purple-50/30">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-purple-500 text-white rounded-lg flex items-center justify-center font-bold text-sm">
                            B
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm">Periode B</div>
                            <div class="text-[10px] text-gray-500">Periode utama (baru)</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Dari</label>
                            <input type="date" name="b_from" value="{{ $bFrom }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Sampai</label>
                            <input type="date" name="b_to" value="{{ $bTo }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="flex gap-2 mt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                    <iconify-icon icon="mdi:compare-horizontal" class="text-lg"></iconify-icon>
                    Bandingkan
                </button>

                <button type="button"
                        onclick="setQuickRange('this-vs-last')"
                        class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-xs font-medium">
                    <iconify-icon icon="mdi:calendar-arrow-left" class="text-base"></iconify-icon>
                    Bulan ini vs Bulan lalu
                </button>
            </div>
        </form>
    </div>

    {{-- ============ SUMMARY CARDS PERBANDINGAN ============ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Net Revenue --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:cash-multiple" class="text-blue-600 text-base"></iconify-icon>
                </div>
                <div class="text-xs text-gray-500 font-medium">Net Revenue</div>
            </div>
            <div class="space-y-1.5">
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-blue-600 font-medium">A</span>
                    <span class="text-sm text-gray-600">{{ number_format($summaryA['net_revenue'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-purple-600 font-medium">B</span>
                    <span class="text-base font-bold text-gray-800">{{ number_format($summaryB['net_revenue'], 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    @include('shopee.partials.growth-badge', ['growth' => $growth['net_revenue']])
                </div>
            </div>
        </div>

        {{-- HPP --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:package-variant-closed" class="text-orange-600 text-base"></iconify-icon>
                </div>
                <div class="text-xs text-gray-500 font-medium">HPP</div>
            </div>
            <div class="space-y-1.5">
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-blue-600 font-medium">A</span>
                    <span class="text-sm text-gray-600">{{ number_format($summaryA['total_hpp'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-purple-600 font-medium">B</span>
                    <span class="text-base font-bold text-gray-800">{{ number_format($summaryB['total_hpp'], 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    @include('shopee.partials.growth-badge', ['growth' => $growth['total_hpp']])
                </div>
            </div>
        </div>

        {{-- Profit --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:chart-line" class="text-green-600 text-base"></iconify-icon>
                </div>
                <div class="text-xs text-gray-500 font-medium">Profit</div>
            </div>
            <div class="space-y-1.5">
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-blue-600 font-medium">A</span>
                    <span class="text-sm text-gray-600">{{ number_format($summaryA['profit'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-purple-600 font-medium">B</span>
                    <span class="text-base font-bold text-green-600">{{ number_format($summaryB['profit'], 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    @include('shopee.partials.growth-badge', ['growth' => $growth['profit']])
                </div>
            </div>
        </div>

        {{-- Transaksi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:receipt-text-outline" class="text-gray-600 text-base"></iconify-icon>
                </div>
                <div class="text-xs text-gray-500 font-medium">Transaksi</div>
            </div>
            <div class="space-y-1.5">
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-blue-600 font-medium">A</span>
                    <span class="text-sm text-gray-600">{{ number_format($summaryA['total_rows']) }}</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-[10px] text-purple-600 font-medium">B</span>
                    <span class="text-base font-bold text-gray-800">{{ number_format($summaryB['total_rows']) }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    @include('shopee.partials.growth-badge', ['growth' => $growth['transaksi']])
                </div>
            </div>
        </div>
    </div>

    {{-- ============ TABEL PERBANDINGAN PER SKU ============ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table-compare" class="text-gray-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Perbandingan Per SKU</h3>
            </div>
            <div class="text-xs text-gray-500">
                {{ $comparison->count() }} SKU · urut selisih profit terbesar
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU Varian</th>
                        <th class="text-left py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="text-center py-3 px-2 text-xs font-semibold text-blue-600 uppercase tracking-wider bg-blue-50/50" colspan="2">Periode A</th>
                        <th class="text-center py-3 px-2 text-xs font-semibold text-purple-600 uppercase tracking-wider bg-purple-50/50" colspan="2">Periode B</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Selisih</th>
                    </tr>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th></th>
                        <th></th>
                        <th class="text-right py-2 px-2 text-[10px] text-gray-500 font-medium bg-blue-50/30">Qty</th>
                        <th class="text-right py-2 px-2 text-[10px] text-gray-500 font-medium bg-blue-50/30">Profit</th>
                        <th class="text-right py-2 px-2 text-[10px] text-gray-500 font-medium bg-purple-50/30">Qty</th>
                        <th class="text-right py-2 px-2 text-[10px] text-gray-500 font-medium bg-purple-50/30">Profit</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($comparison->take(50) as $c)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-1.5">
                                @if($c['status'] === 'baru')
                                    <span class="text-[9px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-bold">NEW</span>
                                @elseif($c['status'] === 'hilang')
                                    <span class="text-[9px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold">LOST</span>
                                @endif
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700 truncate max-w-[200px]" title="{{ $c['sku'] }}">
                                    {{ $c['sku'] }}
                                </span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-xs text-gray-600 truncate max-w-[180px]" title="{{ $c['nama_produk'] }}">
                            {{ \Illuminate\Support\Str::limit($c['nama_produk'], 25, '…') }}
                        </td>
                        <td class="py-2.5 px-2 text-right text-xs text-gray-600 bg-blue-50/20">
                            {{ $c['qty_a'] }}
                        </td>
                        <td class="py-2.5 px-2 text-right text-xs {{ $c['profit_a'] > 0 ? 'text-gray-700' : ($c['profit_a'] < 0 ? 'text-red-600' : 'text-gray-400') }} bg-blue-50/20">
                            {{ number_format($c['profit_a'], 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-2 text-right text-xs text-gray-600 bg-purple-50/20">
                            {{ $c['qty_b'] }}
                        </td>
                        <td class="py-2.5 px-2 text-right text-xs font-semibold {{ $c['profit_b'] > 0 ? 'text-green-600' : ($c['profit_b'] < 0 ? 'text-red-600' : 'text-gray-400') }} bg-purple-50/20">
                            {{ number_format($c['profit_b'], 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            <div class="text-xs font-bold {{ $c['diff_profit'] > 0 ? 'text-green-600' : ($c['diff_profit'] < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                {{ $c['diff_profit'] > 0 ? '+' : '' }}{{ number_format($c['diff_profit'], 0, ',', '.') }}
                            </div>
                            @include('shopee.partials.growth-badge', ['growth' => $c['growth_profit'], 'small' => true])
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-500">
                            <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                            Belum ada data untuk dibandingkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($comparison->count() > 50)
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 text-center text-xs text-gray-500">
            Menampilkan 50 SKU dengan selisih profit terbesar dari {{ $comparison->count() }} total
        </div>
        @endif
    </div>

</div>

<script>
function setQuickRange(type) {
    const today = new Date();
    const y = today.getFullYear();
    const m = today.getMonth();

    function fmt(date) {
        const yy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yy}-${mm}-${dd}`;
    }

    if (type === 'this-vs-last') {
        // Bulan ini
        const bFrom = new Date(y, m, 1);
        const bTo = new Date(y, m + 1, 0);
        // Bulan lalu
        const aFrom = new Date(y, m - 1, 1);
        const aTo = new Date(y, m, 0);

        document.querySelector('input[name="a_from"]').value = fmt(aFrom);
        document.querySelector('input[name="a_to"]').value = fmt(aTo);
        document.querySelector('input[name="b_from"]').value = fmt(bFrom);
        document.querySelector('input[name="b_to"]').value = fmt(bTo);
    }
}
</script>
@endsection