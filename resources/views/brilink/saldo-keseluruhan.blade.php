@extends('layouts.app')

@section('title', 'Data Saldo')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                <iconify-icon icon="mdi:wallet" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Saldo Keseluruhan</h2>
                <p class="text-xs sm:text-sm text-gray-500">
                    {{ $store?->nama_toko ?? 'Semua Toko' }}
                </p>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Rekening / Dompet</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama rekening..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $from || $to || $storeId !== 'all')
                    <a href="{{ route('brilink.saldo-keseluruhan') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL & CARD --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- Header --}}
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:wallet" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">
                    Saldo Saat Ini
                    @if($storeId === 'all')
                        <span class="text-[10px] sm:text-xs font-normal text-gray-500 block sm:inline">(Agregasi semua toko)</span>
                    @endif
                </h3>
            </div>
            <div class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $summary['total_rekening'] }} Rekening</div>
        </div>

        @if(empty($accounts))
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Tidak ada data saldo untuk filter ini.</span>
            </div>
        @else
            
            {{-- TAMPILAN TABEL (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Nama Rekening / Dompet</th>
                            <th class="text-left p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Kategori</th>
                            @if($storeId === 'all')
                                <th class="text-left p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Toko</th>
                            @endif
                            <th class="text-right p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Saldo Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($accounts as $acc)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 sm:p-4 font-bold text-gray-800 text-xs sm:text-sm whitespace-nowrap">{{ $acc['nama_rekening'] }}</td>
                                <td class="p-3 sm:p-4 whitespace-nowrap">
                                    @if($acc['keterangan'] && $acc['keterangan'] !== '-')
                                        <span class="text-[10px] sm:text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ $acc['keterangan'] }}</span>
                                    @else
                                        <span class="text-[10px] sm:text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                @if($storeId === 'all')
                                    <td class="p-3 sm:p-4 text-[10px] sm:text-xs text-gray-600 whitespace-nowrap">
                                        @if($acc['jumlah_toko'] === 1)
                                            <span class="inline-flex items-center gap-1">
                                                <iconify-icon icon="mdi:store" class="text-xs"></iconify-icon>
                                                {{ $acc['toko_list'][0] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded">
                                                <iconify-icon icon="mdi:store-multiple" class="text-xs"></iconify-icon>
                                                {{ $acc['jumlah_toko'] }} toko
                                            </span>
                                        @endif
                                    </td>
                                @endif
                                <td class="p-3 sm:p-4 text-right font-bold text-emerald-600 text-sm sm:text-base whitespace-nowrap">
                                    Rp {{ number_format($acc['saldo_akhir'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-emerald-50 border-t-2 border-emerald-200">
                        <tr>
                            <td colspan="{{ $storeId === 'all' ? 3 : 2 }}" class="p-3 sm:p-4 font-bold text-gray-800 text-xs sm:text-sm">
                                TOTAL SALDO SAAT INI
                            </td>
                            <td class="p-3 sm:p-4 text-right font-bold text-emerald-700 text-base sm:text-lg whitespace-nowrap">
                                Rp {{ number_format($summary['total_saldo_akhir'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- TAMPILAN CARD (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($accounts as $acc)
                    <div class="p-4 space-y-1 text-xs">
                        <div class="flex justify-between items-start gap-2">
                            <div class="font-bold text-gray-800 text-sm">{{ $acc['nama_rekening'] }}</div>
                            @if($acc['keterangan'] && $acc['keterangan'] !== '-')
                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-[10px] whitespace-nowrap">{{ $acc['keterangan'] }}</span>
                            @endif
                        </div>
                        
                        @if($storeId === 'all')
                            <div class="flex items-center gap-1 text-[10px] text-gray-500 pt-1">
                                <iconify-icon icon="mdi:store" class="text-xs"></iconify-icon>
                                @if($acc['jumlah_toko'] === 1)
                                    {{ $acc['toko_list'][0] }}
                                @else
                                    {{ $acc['jumlah_toko'] }} toko
                                @endif
                            </div>
                        @endif

                        <div class="flex justify-between items-center pt-2 mt-1 border-t border-gray-50">
                            <span class="text-gray-500">Saldo:</span>
                            <span class="font-bold text-emerald-600 text-sm">Rp {{ number_format($acc['saldo_akhir'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
                
                {{-- Footer Total Mobile --}}
                <div class="p-4 bg-emerald-50 flex justify-between items-center font-bold text-xs">
                    <span class="text-gray-800">TOTAL KESELURUHAN</span>
                    <span class="text-emerald-700 text-sm">Rp {{ number_format($summary['total_saldo_akhir'], 0, ',', '.') }}</span>
                </div>
            </div>

        @endif
    </div>

</div>
@endsection