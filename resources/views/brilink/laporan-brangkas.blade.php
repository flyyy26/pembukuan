@extends('layouts.app')

@section('title', 'Laporan Brangkas BRILink')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:safe" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Laporan Brangkas</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }}</p>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- BAGIAN ATAS: FILTER (KIRI) + 2 CARD (KANAN) --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

        {{-- KANAN: CARD BRANGKAS SAAT INI --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-sm p-5 text-white flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:wallet" class="text-white text-lg"></iconify-icon>
                </div>
                <div class="text-xs text-white/80 font-medium">Brangkas Saat Ini</div>
            </div>
            <div class="text-3xl font-bold mt-1">
                <span class="text-sm text-white/70 font-normal">Rp</span>
                {{ number_format($summary['total_akhir'], 0, ',', '.') }}
            </div>
        </div>

        {{-- KIRI: FORM FILTER --}}
        <div class="bg-white rounded-xl shadow-sm flex items-center border border-gray-100 p-4">
            <form method="GET" class="flex flex-row items-end gap-3 w-full">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Outlet</label>
                    <select name="store" onchange="submitOutletFilter(this)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @forelse($stores as $s)
                            <option value="{{ $s->id }}" {{ (string) $storeId === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->nama_toko }}
                            </option>
                        @empty
                            <option value="">Belum ada outlet dengan brangkas</option>
                        @endforelse

                        @if($stores->count() > 1 || $storeId === 'all')
                            <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>
                                — Semua Outlet (perbandingan) —
                            </option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center justify-center gap-2">
                        <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                        Filter
                    </button>
                    @if($from || $to)
                        <a href="{{ route('brilink.laporan-brangkas', ['store' => $storeId]) }}"
                        class="px-4 py-2 text-sm bg-white border border-gray-300 text-gray-500 hover:text-gray-700 rounded-lg text-center transition flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

    </div>

    {{-- ============================================ --}}
    {{-- TABEL DETAIL HARIAN                          --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Detail Pergerakan Brangkas Harian</h3>
            </div>
            <div class="text-xs text-gray-500">{{ $summary['total_hari'] }} hari</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        @if($storeId === 'all')
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Toko</th>
                        @endif
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Brangkas Awal</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-emerald-600 uppercase">Penambahan</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-red-600 uppercase">Pemakaian</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-blue-600 uppercase">Sisa</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Brangkas Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dailyData as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-800">{{ $row['tanggal_label'] }}</div>
                            <div class="text-[10px] text-gray-400 font-mono">{{ $row['file_name'] }}</div>
                        </td>
                        @if($storeId === 'all')
                            <td class="py-3 px-4 text-xs text-gray-600">
                                <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                {{ $row['store_name'] }}
                            </td>
                        @endif
                        <td class="py-3 px-4 text-right text-gray-700">
                            {{ number_format($row['brangkas_awal'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-emerald-600 font-semibold">
                            @if($row['penambahan'] > 0)
                                + {{ number_format($row['penambahan'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-red-600 font-semibold">
                            @if($row['pemakaian'] > 0)
                                - {{ number_format($row['pemakaian'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-blue-600 font-bold">
                            {{ number_format($row['sisa'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-gray-800 font-semibold">
                            {{ number_format($row['brangkas_akhir'], 0, ',', '.') }}
                            @if($row['selisih'] != 0)
                                <div class="text-[10px] {{ $row['selisih'] > 0 ? 'text-orange-600' : 'text-red-600' }}">
                                    Selisih: {{ $row['selisih'] > 0 ? '+' : '' }}{{ number_format($row['selisih'], 0, ',', '.') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $storeId === 'all' ? 7 : 6 }}" class="py-12 text-center text-gray-500">
                            <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                            Belum ada data brangkas untuk filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($dailyData->isNotEmpty())
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="{{ $storeId === 'all' ? 2 : 1 }}" class="py-3 px-4 font-bold text-gray-800">
                            TOTAL ({{ $summary['total_hari'] }} hari)
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-800">
                            {{-- Awal = nilai dari hari pertama --}}
                            {{ number_format($summary['total_awal'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-600">
                            {{ number_format($summary['total_penambahan'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-red-600">
                            {{ number_format($summary['total_pemakaian'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-blue-600">
                            {{-- Sisa = nilai dari hari terakhir --}}
                            {{ number_format($summary['total_sisa'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-800">
                            {{-- Akhir = nilai dari hari terakhir --}}
                            {{ number_format($summary['total_akhir'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<script>
function submitOutletFilter(selectEl) {
    const form = selectEl.closest('form');
    // Tandai bahwa ini pilihan manual user
    let manualInput = form.querySelector('input[name="manual"]');
    if (!manualInput) {
        manualInput = document.createElement('input');
        manualInput.type = 'hidden';
        manualInput.name = 'manual';
        form.appendChild(manualInput);
    }
    manualInput.value = '1';
    form.submit();
}
</script>
@endsection