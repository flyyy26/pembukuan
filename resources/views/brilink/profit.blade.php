@extends('layouts.app')

@section('title', 'Laporan Profit BRILink')

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