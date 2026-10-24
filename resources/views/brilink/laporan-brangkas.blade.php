@extends('layouts.app')

@section('title', 'Laporan Brangkas BRILink')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:safe" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Brangkas</h2>
                <p class="text-xs sm:text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }}</p>
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
            <div class="text-2xl sm:text-3xl font-bold mt-1">
                <span class="text-sm text-white/70 font-normal">Rp</span>
                {{ number_format($summary['total_akhir'], 0, ',', '.') }}
            </div>
        </div>

        {{-- KIRI: FORM FILTER --}}
        <div class="bg-white rounded-xl shadow-sm flex items-center border border-gray-100 p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-row items-end gap-3 w-full">
                <div class="w-full md:flex-1 md:min-w-[160px]">
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

                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                    <button type="submit"
                            class="flex-1 md:flex-none bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center justify-center gap-2">
                        <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                        Filter
                    </button>
                    @if($from || $to)
                        <a href="{{ route('brilink.laporan-brangkas', ['store' => $storeId]) }}"
                        class="flex-1 md:flex-none px-4 py-2 text-sm bg-white border border-gray-300 text-gray-500 hover:text-gray-700 rounded-lg text-center transition flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

    </div>

    {{-- ============================================ --}}
    {{-- TABEL DETAIL HARIAN (DESKTOP) --}}
    {{-- ============================================ --}}
    <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                        @if($storeId === 'all')
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Toko</th>
                        @endif
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Brangkas Awal</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-emerald-600 uppercase whitespace-nowrap">Penambahan</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-red-600 uppercase whitespace-nowrap">Pemakaian</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-blue-600 uppercase whitespace-nowrap">Sisa</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Brangkas Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dailyData as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-medium text-gray-800">{{ $row['tanggal_label'] }}</div>
                            <div class="text-[10px] text-gray-400 font-mono">{{ $row['file_name'] }}</div>
                        </td>
                        @if($storeId === 'all')
                            <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                {{ $row['store_name'] }}
                            </td>
                        @endif
                        <td class="py-3 px-4 text-right text-gray-700 whitespace-nowrap">
                            {{ number_format($row['brangkas_awal'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-emerald-600 font-semibold whitespace-nowrap">
                            @if($row['penambahan'] > 0)
                                + {{ number_format($row['penambahan'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-red-600 font-semibold whitespace-nowrap">
                            @if($row['pemakaian'] > 0)
                                - {{ number_format($row['pemakaian'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-blue-600 font-bold whitespace-nowrap">
                            {{ number_format($row['sisa'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-gray-800 font-semibold whitespace-nowrap">
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
                        <td class="py-3 px-4 text-right font-bold text-gray-800 whitespace-nowrap">
                            {{ number_format($summary['total_awal'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-600 whitespace-nowrap">
                            {{ number_format($summary['total_penambahan'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-red-600 whitespace-nowrap">
                            {{ number_format($summary['total_pemakaian'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-blue-600 whitespace-nowrap">
                            {{ number_format($summary['total_sisa'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-800 whitespace-nowrap">
                            {{ number_format($summary['total_akhir'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TAMPILAN CARD / LIST (MOBILE) --}}
    {{-- ============================================ --}}
    <div class="md:hidden bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:format-list-bulleted" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm">Pergerakan Harian</h3>
            </div>
            <div class="text-[10px] text-gray-500 bg-white px-2 py-1 rounded border">{{ $summary['total_hari'] }} hari</div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($dailyData as $row)
                <div class="p-4 space-y-2 text-xs">
                    {{-- Tanggal & Toko --}}
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <div class="font-bold text-gray-800 text-sm">{{ $row['tanggal_label'] }}</div>
                            <div class="text-[10px] text-gray-400 font-mono">{{ $row['file_name'] }}</div>
                        </div>
                        @if($storeId === 'all')
                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                <iconify-icon icon="mdi:store" class="text-[10px]"></iconify-icon>
                                {{ $row['store_name'] }}
                            </span>
                        @endif
                    </div>

                    {{-- Grid 2 Kolom untuk Rincian --}}
                    <div class="grid grid-cols-2 gap-y-2 gap-x-4 pt-2 border-t border-gray-50">
                        <div>
                            <div class="text-gray-500 text-[10px]">Brangkas Awal</div>
                            <div class="text-gray-700">{{ number_format($row['brangkas_awal'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-[10px]">Brangkas Akhir</div>
                            <div class="text-gray-800 font-semibold">{{ number_format($row['brangkas_akhir'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-[10px]">Penambahan</div>
                            <div class="text-emerald-600 font-semibold">
                                @if($row['penambahan'] > 0)
                                    + {{ number_format($row['penambahan'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-[10px]">Pemakaian</div>
                            <div class="text-red-600 font-semibold">
                                @if($row['pemakaian'] > 0)
                                    - {{ number_format($row['pemakaian'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Sisa & Selisih --}}
                    <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                        <div>
                            <div class="text-gray-500 text-[10px]">Sisa Brangkas</div>
                            <div class="text-blue-600 font-bold">{{ number_format($row['sisa'], 0, ',', '.') }}</div>
                        </div>
                        @if($row['selisih'] != 0)
                            <div class="text-right">
                                <div class="text-gray-500 text-[10px]">Selisih</div>
                                <div class="{{ $row['selisih'] > 0 ? 'text-orange-600' : 'text-red-600' }} font-semibold">
                                    {{ $row['selisih'] > 0 ? '+' : '' }}{{ number_format($row['selisih'], 0, ',', '.') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 text-xs">
                    <iconify-icon icon="mdi:inbox-outline" class="text-3xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    Belum ada data brangkas untuk filter ini.
                </div>
            @endforelse
        </div>

        {{-- FOOTER TOTAL (MOBILE) --}}
        @if($dailyData->isNotEmpty())
            <div class="p-4 bg-gray-50 border-t-2 border-gray-200 space-y-2 text-xs">
                <div class="font-bold text-gray-800 text-sm mb-2">TOTAL KESELURUHAN ({{ $summary['total_hari'] }} hari)</div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex justify-between sm:block">
                        <span class="text-gray-500">Awal: </span>
                        <span class="font-semibold text-gray-800">{{ number_format($summary['total_awal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between sm:block">
                        <span class="text-gray-500">Akhir: </span>
                        <span class="font-semibold text-gray-800">{{ number_format($summary['total_akhir'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between sm:block mt-1">
                        <span class="text-gray-500">Penambahan: </span>
                        <span class="font-semibold text-emerald-600">{{ number_format($summary['total_penambahan'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between sm:block mt-1">
                        <span class="text-gray-500">Pemakaian: </span>
                        <span class="font-semibold text-red-600">{{ number_format($summary['total_pemakaian'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-200">
                    <span class="font-bold text-blue-700 text-sm">Total Sisa</span>
                    <span class="font-bold text-blue-700 text-base">{{ number_format($summary['total_sisa'], 0, ',', '.') }}</span>
                </div>
            </div>
        @endif
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