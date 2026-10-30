@extends('layouts.app')

@section('title', 'Laporan Profit')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                <iconify-icon icon="mdi:chart-line-variant" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Profit</h2>
                <p class="text-xs sm:text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($from || $to || $storeId !== 'all')
                    <a href="{{ route('brilink.profit') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ======================================================= --}}
    {{-- TABEL PROFIT HARIAN --}}
    {{-- ======================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6 mt-6">
        <div class="bg-gray-800 px-4 sm:px-6 py-4 border-b border-gray-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-white text-xl"></iconify-icon>
                <h3 class="font-bold text-white text-sm sm:text-lg">Laporan Profit Harian</h3>
            </div>
            <span class="text-[10px] sm:text-xs text-gray-400 font-mono">
                Bulan: {{ \Carbon\Carbon::parse($from)->translatedFormat('F Y') }}
            </span>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[600px] p-0 sm:p-0 relative">
            @if(empty($dailyProfit))
                <div class="py-12 text-center text-gray-500">
                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    Belum ada data profit untuk filter ini.
                </div>
            @else
                <table class="w-full text-sm border-collapse min-w-max">
                    <thead class="sticky top-0 z-20 shadow-sm bg-gray-100">
                        {{-- Header Tingkat 1 --}}
                        <tr class="bg-gray-100">
                            <th class="border border-gray-200 p-3 text-center text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100" rowspan="2">Tanggal</th>
                            <th class="border border-gray-200 p-2 text-center text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100" colspan="7">Profit & Pendapatan</th>
                            <th class="border border-gray-200 p-3 text-center text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100" rowspan="2">Prive</th>
                            <th class="border border-gray-200 p-3 text-center text-[10px] sm:text-xs font-semibold text-blue-600 uppercase whitespace-nowrap bg-gray-100" rowspan="2">Profit Bersih</th>
                        </tr>
                        {{-- Header Tingkat 2 --}}
                        <tr class="bg-gray-50">
                            <th class="border border-gray-200 p-2 text-right text-[9px] sm:text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap bg-gray-50">Profit Pulsa</th>
                            <th class="border border-gray-200 p-2 text-right text-[9px] sm:text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap bg-gray-50">Profit PPOB</th>
                            <th class="border border-gray-200 p-2 text-right text-[9px] sm:text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap bg-gray-50">BRILink Admin</th>
                            <th class="border border-gray-200 p-2 text-right text-[9px] sm:text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap bg-gray-50">MBanking Admin</th>
                            <th class="border border-gray-200 p-2 text-right text-[9px] sm:text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap bg-gray-50">Barang Titip Jual</th>
                            <th class="border border-gray-200 p-2 text-right text-[9px] sm:text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap bg-gray-50">Komisi Merchant (KMC)</th>
                            <th class="border border-gray-200 p-2 text-right text-[9px] sm:text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap bg-gray-50">Laba Kasir Pintar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dailyProfit as $row)
                            @php
                                $isEmpty = ($row['pendapatan'] == 0 && $row['prive'] == 0);
                            @endphp
                            <tr class="hover:bg-gray-50 transition {{ $isEmpty ? 'opacity-50' : '' }}">
                                <td class="p-2 sm:p-3 text-center text-gray-700 font-medium text-xs sm:text-sm whitespace-nowrap">
                                    {{ $row['tanggal_label'] }}
                                </td>
                                
                                {{-- 7 Kolom Profit & Pendapatan --}}
                                <td class="p-2 sm:p-3 text-right {{ $row['profit_pulsa'] > 0 ? 'text-gray-800' : 'text-gray-400' }} text-[10px] sm:text-xs whitespace-nowrap">
                                    {{ $row['profit_pulsa'] != 0 ? number_format($row['profit_pulsa'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 sm:p-3 text-right {{ $row['profit_ppob'] > 0 ? 'text-gray-800' : 'text-gray-400' }} text-[10px] sm:text-xs whitespace-nowrap">
                                    {{ $row['profit_ppob'] != 0 ? number_format($row['profit_ppob'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 sm:p-3 text-right {{ $row['admin_brilink'] > 0 ? 'text-gray-800' : 'text-gray-400' }} text-[10px] sm:text-xs whitespace-nowrap">
                                    {{ $row['admin_brilink'] != 0 ? number_format($row['admin_brilink'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 sm:p-3 text-right {{ $row['admin_mbanking'] > 0 ? 'text-gray-800' : 'text-gray-400' }} text-[10px] sm:text-xs whitespace-nowrap">
                                    {{ $row['admin_mbanking'] != 0 ? number_format($row['admin_mbanking'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 sm:p-3 text-right {{ $row['titip_jual'] > 0 ? 'text-gray-800' : 'text-gray-400' }} text-[10px] sm:text-xs whitespace-nowrap">
                                    {{ $row['titip_jual'] != 0 ? number_format($row['titip_jual'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 sm:p-3 text-right {{ $row['kmc'] > 0 ? 'text-gray-800' : 'text-gray-400' }} text-[10px] sm:text-xs whitespace-nowrap">
                                    {{ $row['kmc'] != 0 ? number_format($row['kmc'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 sm:p-3 text-right {{ $row['laba_kasir'] > 0 ? 'text-gray-800' : 'text-gray-400' }} text-[10px] sm:text-xs whitespace-nowrap">
                                    {{ $row['laba_kasir'] != 0 ? number_format($row['laba_kasir'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- Prive --}}
                                <td class="p-2 sm:p-3 text-right {{ $row['prive'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }} text-xs sm:text-sm whitespace-nowrap">
                                    {{ $row['prive'] > 0 ? '- ' . number_format($row['prive'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- Profit Bersih --}}
                                <td class="p-2 sm:p-3 text-right text-xs sm:text-sm whitespace-nowrap 
                                    {{ $row['profit_bersih'] > 0 
                                        ? 'text-emerald-600 font-bold' 
                                        : ($row['profit_bersih'] < 0 
                                            ? 'text-red-600 font-bold' 
                                            : 'text-gray-400') }}">
                                    
                                    @if($row['profit_bersih'] != 0)
                                        {{ $row['profit_bersih'] < 0 ? '- ' : '' }}
                                        {{ number_format(abs($row['profit_bersih']), 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    
                    {{-- Footer Total --}}
                    <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                        <tr>
                            <td class="p-2 sm:p-3 font-bold text-gray-800 text-xs sm:text-sm whitespace-nowrap text-center">TOTAL</td>
                            {{-- Total 7 Kolom Pendapatan --}}
                            @php
                                $sumPulsa = collect($dailyProfit)->sum('profit_pulsa');
                                $sumPpob = collect($dailyProfit)->sum('profit_ppob');
                                $sumBrilink = collect($dailyProfit)->sum('admin_brilink');
                                $sumMb = collect($dailyProfit)->sum('admin_mbanking');
                                $sumTitip = collect($dailyProfit)->sum('titip_jual');
                                $sumKmc = collect($dailyProfit)->sum('kmc');
                                $sumKasir = collect($dailyProfit)->sum('laba_kasir');
                            @endphp
                            <td class="p-2 sm:p-3 text-right font-bold text-gray-800 text-[10px] sm:text-xs whitespace-nowrap">{{ number_format($sumPulsa, 0, ',', '.') }}</td>
                            <td class="p-2 sm:p-3 text-right font-bold text-gray-800 text-[10px] sm:text-xs whitespace-nowrap">{{ number_format($sumPpob, 0, ',', '.') }}</td>
                            <td class="p-2 sm:p-3 text-right font-bold text-gray-800 text-[10px] sm:text-xs whitespace-nowrap">{{ number_format($sumBrilink, 0, ',', '.') }}</td>
                            <td class="p-2 sm:p-3 text-right font-bold text-gray-800 text-[10px] sm:text-xs whitespace-nowrap">{{ number_format($sumMb, 0, ',', '.') }}</td>
                            <td class="p-2 sm:p-3 text-right font-bold text-gray-800 text-[10px] sm:text-xs whitespace-nowrap">{{ number_format($sumTitip, 0, ',', '.') }}</td>
                            <td class="p-2 sm:p-3 text-right font-bold text-gray-800 text-[10px] sm:text-xs whitespace-nowrap">{{ number_format($sumKmc, 0, ',', '.') }}</td>
                            <td class="p-2 sm:p-3 text-right font-bold text-gray-800 text-[10px] sm:text-xs whitespace-nowrap">{{ number_format($sumKasir, 0, ',', '.') }}</td>
                            
                            {{-- Total Prive --}}
                            <td class="p-2 sm:p-3 text-right font-bold text-red-700 text-xs sm:text-sm whitespace-nowrap">
                                {{ $totalPriveHarian > 0 ? '- ' . number_format($totalPriveHarian, 0, ',', '.') : '-' }}
                            </td>
                            
                            {{-- Total Profit Bersih --}}
                            <td class="p-2 sm:p-3 text-right font-bold text-blue-700 text-sm sm:text-base whitespace-nowrap">
                                {{ number_format($totalProfitBersihHarian, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

    {{-- GRID CARD PROFIT: 2 KOLOM DI HP, 3 KOLOM DI DESKTOP --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 mb-6">
        
        {{-- ================= PULSA ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-xs sm:text-sm">PULSA</span>
                <iconify-icon icon="mdi:cellphone" class="text-gray-400 text-base sm:text-lg hidden sm:block"></iconify-icon>
            </div>
            <div class="p-3 sm:p-4 space-y-2 sm:space-y-3 text-[10px] sm:text-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Topup Saldo</span>
                    <span class="font-semibold text-blue-600">+ Rp {{ number_format($dataProfit['pulsa']['topup'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['pulsa']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['pulsa']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Penjualan</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['penjualan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between pt-1 gap-0.5">
                    <span class="font-bold text-gray-800">PROFIT</span>
                    <span class="font-bold text-sm sm:text-lg {{ $dataProfit['pulsa']['profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($dataProfit['pulsa']['profit'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= PPOB ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-xs sm:text-sm">PPOB</span>
                <iconify-icon icon="mdi:receipt-text" class="text-gray-400 text-base sm:text-lg hidden sm:block"></iconify-icon>
            </div>
            <div class="p-3 sm:p-4 space-y-2 sm:space-y-3 text-[10px] sm:text-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Topup Saldo</span>
                    <span class="font-semibold text-blue-600">+ Rp {{ number_format($dataProfit['ppob']['topup'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['ppob']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['ppob']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Penjualan</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['penjualan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between pt-1 gap-0.5">
                    <span class="font-bold text-gray-800">PROFIT</span>
                    <span class="font-bold text-sm sm:text-lg {{ $dataProfit['ppob']['profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($dataProfit['ppob']['profit'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= BRILINK ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-xs sm:text-sm">BRILINK</span>
                <iconify-icon icon="mdi:bank" class="text-gray-400 text-base sm:text-lg hidden sm:block"></iconify-icon>
            </div>
            <div class="p-3 sm:p-4 space-y-2 sm:space-y-3 text-[10px] sm:text-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brilink']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brilink']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['brilink']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['brilink']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brilink']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between pt-1 gap-0.5">
                    <span class="font-bold text-gray-800">PROFIT</span>
                    <span class="font-bold text-sm sm:text-lg text-emerald-600">Rp {{ number_format($dataProfit['brilink']['pendapatan_admin'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= MBANKING ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-xs sm:text-sm">MBANKING</span>
                <iconify-icon icon="mdi:cellphone-link" class="text-gray-400 text-base sm:text-lg hidden sm:block"></iconify-icon>
            </div>
            <div class="p-3 sm:p-4 space-y-2 sm:space-y-3 text-[10px] sm:text-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['mbanking']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['mbanking']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['mbanking']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['mbanking']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['mbanking']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between pt-1 gap-0.5">
                    <span class="font-bold text-gray-800">PENDAPATAN</span>
                    <span class="font-bold text-sm sm:text-lg text-emerald-600">Rp {{ number_format($dataProfit['mbanking']['pendapatan_admin'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= KASIR PINTAR ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-xs sm:text-sm">KASIR PINTAR</span>
                <iconify-icon icon="mdi:store" class="text-gray-400 text-base sm:text-lg hidden sm:block"></iconify-icon>
            </div>
            <div class="p-3 sm:p-4 space-y-2 sm:space-y-3 text-[10px] sm:text-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['kasir_pintar']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['kasir_pintar']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Omset</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['kasir_pintar']['penjualan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between pt-1 gap-0.5">
                    <span class="font-bold text-gray-800">LABA</span>
                    <span class="font-bold text-sm sm:text-lg text-emerald-600">Rp {{ number_format($dataProfit['kasir_pintar']['laba'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= BRANGKAS ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-xs sm:text-sm">BRANGKAS</span>
                <iconify-icon icon="mdi:safe" class="text-gray-400 text-base sm:text-lg hidden sm:block"></iconify-icon>
            </div>
            <div class="p-3 sm:p-4 space-y-2 sm:space-y-3 text-[10px] sm:text-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Brangkas Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brangkas']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Penambahan</span>
                    <span class="font-semibold text-emerald-600">+ Rp {{ number_format($dataProfit['brangkas']['penambahan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Jumlah</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brangkas']['jumlah_brangkas'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-gray-100 pb-1 sm:pb-2 gap-0.5">
                    <span class="text-gray-500">Pemakaian</span>
                    <span class="font-semibold text-red-600">- Rp {{ number_format($dataProfit['brangkas']['pemakaian'], 0, ',', '.') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between pt-1 gap-0.5">
                    <span class="font-bold text-gray-800">SAAT INI</span>
                    <span class="font-bold text-sm sm:text-lg text-blue-600">Rp {{ number_format($dataProfit['brangkas']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================================================= --}}
    {{-- CARD BARU: RINGKASAN PROFIT HARIAN --}}
    {{-- ======================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6 mt-6">
        <div class="bg-gray-800 px-4 sm:px-6 py-4 border-b border-gray-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:chart-box-outline" class="text-white text-xl"></iconify-icon>
                <h3 class="font-bold text-white text-sm sm:text-lg">Rangkuman Profit & Pengeluaran</h3>
            </div>
            <span class="text-[10px] sm:text-xs text-gray-400 font-mono">Berdasarkan Filter Tanggal</span>
        </div>

        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- KOLOM 1: PROFIT & LABA --}}
                <div class="space-y-2 sm:space-y-3">
                    <h4 class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 sm:mb-3 border-b pb-1">Profit & Pendapatan</h4>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm gap-0.5">
                        <span class="text-gray-600">Profit Pulsa</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($profitPulsa, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm gap-0.5">
                        <span class="text-gray-600">Profit PPOB</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($profitPpob, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm gap-0.5">
                        <span class="text-gray-600">BRILink Admin</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($adminBrilink, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm gap-0.5">
                        <span class="text-gray-600">MBanking Admin</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($adminMbanking, 0, ',', '.') }}</span>
                    </div>
                    
                    {{-- ✅ BARU: Barang Titip Jual --}}
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm gap-0.5">
                        <span class="text-gray-600">Barang Titip Jual</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($penjualanBarangTitipJual, 0, ',', '.') }}</span>
                    </div>

                    {{-- ✅ BARU: Komisi Merchant (KMC) --}}
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm gap-0.5">
                        <span class="text-gray-600">Komisi Merchant (KMC)</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($totalKmc, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm border-b border-gray-100 pb-2 gap-0.5">
                        <span class="text-gray-600">Laba Kasir Pintar</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($labaKasirPintar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center pt-1 gap-0.5">
                        <span class="font-bold text-gray-800 text-xs sm:text-sm">Total Profit Kotor</span>
                        <span class="font-bold text-emerald-600 text-sm sm:text-base">Rp {{ number_format($totalProfitKotor, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- KOLOM 2: PRIVE --}}
                <div class="space-y-2 sm:space-y-3">
                    <h4 class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 sm:mb-3 border-b pb-1">Pengeluaran</h4>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm pb-2 gap-0.5">
                        <span class="text-gray-600">Prive</span>
                        <span class="font-semibold text-red-600">- Rp {{ number_format($totalPrive, 0, ',', '.') }}</span>
                    </div>
                    @if(!empty($breakdownPrive))
                        <div class="pl-3 border-l-2 border-red-200 space-y-1.5 max-h-40 overflow-y-auto pr-1">
                            @foreach($breakdownPrive as $bp)
                                <div class="flex justify-between items-start gap-2 text-[10px] sm:text-xs">
                                    <span class="text-gray-500 truncate" title="{{ $bp['keterangan'] }}">
                                        • {{ $bp['keterangan'] }}
                                        @if($bp['jumlah'] > 1)
                                            <span class="text-gray-400">({{ $bp['jumlah'] }}x)</span>
                                        @endif
                                    </span>
                                    <span class="font-medium text-gray-700 whitespace-nowrap">
                                        Rp {{ number_format($bp['total'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-[10px] sm:text-xs text-gray-400 italic pl-3">Belum ada transaksi prive.</div>
                    @endif
                </div>

                {{-- KOLOM 3: PENDAPATAN HARIAN --}}
                <div class="space-y-2 sm:space-y-3">
                    <h4 class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 sm:mb-3 border-b pb-1">Rekap Harian</h4>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm pb-2 gap-0.5">
                        <span class="text-gray-600">Pendapatan Harian</span>
                        <span class="font-bold text-sm sm:text-base text-blue-600">Rp {{ number_format($pendapatanHarian, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs sm:text-sm pb-2 gap-0.5">
                        <span class="text-gray-600">Pengeluaran Harian</span>
                        <span class="font-bold text-sm sm:text-base text-red-600">Rp {{ number_format($pengeluaranHarian, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- KOLOM 4: PROFIT BERSIH --}}
                <div class="space-y-3 flex flex-col justify-center">
                    <div class="p-4 sm:p-5 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl text-white shadow-md text-center">
                        <div class="text-[10px] sm:text-xs font-medium text-emerald-100 mb-1 uppercase tracking-wider">Profit Bersih</div>
                        <div class="text-xl sm:text-2xl font-bold">Rp {{ number_format($penambahanSaldo, 0, ',', '.') }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- REKAPITULASI SALDO KESELURUHAN --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-md p-4 sm:p-6 text-white flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:wallet" class="text-xl sm:text-2xl text-white"></iconify-icon>
            </div>
            <div>
                <div class="text-blue-100 text-xs sm:text-sm font-medium">Total Saldo Awal Keseluruhan</div>
                <div class="text-lg sm:text-2xl font-bold">Rp {{ number_format($totalSaldoAwal, 0, ',', '.') }}</div>
            </div>
        </div>
        
        {{-- Garis Pembatas (Horizontal di HP, Vertikal di Desktop) --}}
        <div class="block md:hidden w-full h-px bg-white/30 my-1"></div>
        <div class="hidden md:block w-px h-12 bg-white/30"></div>
        
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:wallet" class="text-xl sm:text-2xl text-white"></iconify-icon>
            </div>
            <div>
                <div class="text-blue-100 text-xs sm:text-sm font-medium">Total Saldo Akhir Keseluruhan</div>
                <div class="text-lg sm:text-2xl font-bold">Rp {{ number_format($totalSaldoAkhir, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

</div>
@endsection