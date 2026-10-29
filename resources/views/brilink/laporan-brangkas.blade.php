@extends('layouts.app')

@section('title', 'Laporan Brangkas BRILink')

@section('content')
{{-- KUNCI 1: Tambahkan w-full dan min-w-0 agar halaman tidak melebar --}}
<div class="w-full min-w-0">

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
    {{-- BAGIAN ATAS: FILTER + CARD BRANGKAS --}}
    {{-- ============================================ --}}
    {{-- Gunakan xl:grid-cols-2, agar di iPad (lg) tetap 1 kolom ke bawah --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6 w-full">

        {{-- CARD BRANGKAS SAAT INI --}}
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

        {{-- FORM FILTER --}}
        <div class="bg-white rounded-xl shadow-sm flex items-center border border-gray-100 p-4 w-full">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full">
                <div class="w-full sm:col-span-2 lg:col-span-1">
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

                <div class="w-full">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div class="w-full">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                
                <div class="flex gap-2 w-full sm:col-span-2 lg:col-span-1 items-end">
                    <button type="submit"
                            class="flex-1 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center justify-center gap-2">
                        <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                        Filter
                    </button>
                    @if($from || $to)
                        <a href="{{ route('brilink.laporan-brangkas', ['store' => $storeId]) }}"
                        class="flex-1 px-4 py-2 text-sm bg-white border border-gray-300 text-gray-500 hover:text-gray-700 rounded-lg text-center transition flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

    </div>

    {{-- ============================================ --}}
    {{-- TABEL DETAIL HARIAN (MOBILE, TABLET, DESKTOP) --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Detail Pergerakan Brangkas Harian</h3>
            </div>
            <div class="text-xs text-gray-500">{{ $summary['total_hari'] }} hari</div>
        </div>

        {{-- KUNCI 2: Bungkus tabel dengan overflow-x-auto agar bisa di-scroll di HP/Tablet --}}
        <div class="w-full overflow-x-auto block">
            {{-- KUNCI 3: Gunakan min-w-max agar kolom tabel tidak mengecil dan bisa digeser --}}
            <table class="w-full text-xs sm:text-sm text-left min-w-max">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                        
                        @if($storeId === 'all')
                            {{-- Sembunyikan kolom Toko di HP (di bawah sm) --}}
                            <th class="hidden sm:table-cell py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Toko</th>
                        @endif
                        
                        <th class="py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Brangkas Awal</th>
                        <th class="py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-semibold text-emerald-600 uppercase whitespace-nowrap text-right">Penambahan</th>
                        <th class="py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-semibold text-red-600 uppercase whitespace-nowrap text-right">Pemakaian</th>
                        <th class="py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-semibold text-blue-600 uppercase whitespace-nowrap text-right">Sisa</th>
                        <th class="py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Brangkas Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dailyData as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                            <div class="font-medium text-gray-800">{{ $row['tanggal_label'] }}</div>
                            {{-- Sembunyikan nama file di HP --}}
                            <div class="hidden sm:block text-[10px] text-gray-400 font-mono">{{ $row['file_name'] }}</div>
                        </td>
                        
                        @if($storeId === 'all')
                            <td class="hidden sm:table-cell py-2 px-3 sm:py-3 sm:px-4 text-xs text-gray-600 whitespace-nowrap">
                                <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                {{ $row['store_name'] }}
                            </td>
                        @endif
                        
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right text-gray-700 whitespace-nowrap">
                            {{ number_format($row['brangkas_awal'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right text-emerald-600 font-semibold whitespace-nowrap">
                            @if($row['penambahan'] > 0)
                                + {{ number_format($row['penambahan'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right text-red-600 font-semibold whitespace-nowrap">
                            @if($row['pemakaian'] > 0)
                                - {{ number_format($row['pemakaian'], 0, ',', '.') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right text-blue-600 font-bold whitespace-nowrap">
                            {{ number_format($row['sisa'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right text-gray-800 font-semibold whitespace-nowrap">
                            {{ number_format($row['brangkas_akhir'], 0, ',', '.') }}
                            @if($row['selisih'] != 0)
                                <div class="text-[9px] sm:text-[10px] {{ $row['selisih'] > 0 ? 'text-orange-600' : 'text-red-600' }}">
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
                        <td colspan="{{ $storeId === 'all' ? 2 : 1 }}" class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-gray-800 whitespace-nowrap">
                            TOTAL ({{ $summary['total_hari'] }} hari)
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right font-bold text-gray-800 whitespace-nowrap">
                            {{ number_format($summary['total_awal'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right font-bold text-emerald-600 whitespace-nowrap">
                            {{ number_format($summary['total_penambahan'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right font-bold text-red-600 whitespace-nowrap">
                            {{ number_format($summary['total_pemakaian'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right font-bold text-blue-600 whitespace-nowrap">
                            {{ number_format($summary['total_sisa'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 sm:py-3 sm:px-4 text-right font-bold text-gray-800 whitespace-nowrap">
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