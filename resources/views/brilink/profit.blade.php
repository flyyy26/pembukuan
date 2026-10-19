@extends('layouts.app')

@section('title', 'Laporan Profit BRILink')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                <iconify-icon icon="mdi:chart-line-variant" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Laporan Profit</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($from || $to)
                    <a href="{{ route('brilink.profit', ['store' => $storeId]) }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
        
        {{-- ================= PULSA ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-sm">PULSA</span>
                <iconify-icon icon="mdi:cellphone" class="text-gray-400 text-lg"></iconify-icon>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['pulsa']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['pulsa']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Penjualan</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['pulsa']['penjualan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-1">
                    <span class="font-bold text-gray-800">PROFIT</span>
                    <span class="font-bold text-lg {{ $dataProfit['pulsa']['profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($dataProfit['pulsa']['profit'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= PPOB ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-sm">PPOB</span>
                <iconify-icon icon="mdi:receipt-text" class="text-gray-400 text-lg"></iconify-icon>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['ppob']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['ppob']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Penjualan</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['ppob']['penjualan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-1">
                    <span class="font-bold text-gray-800">PROFIT</span>
                    <span class="font-bold text-lg {{ $dataProfit['ppob']['profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($dataProfit['ppob']['profit'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= BRILINK ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-sm">BRILINK</span>
                <iconify-icon icon="mdi:bank" class="text-gray-400 text-lg"></iconify-icon>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brilink']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brilink']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                
                {{-- Baris Saldo Terpakai --}}
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['brilink']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['brilink']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brilink']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>

                {{-- Baris Pendapatan Admin --}}
                <div class="flex justify-between pt-1">
                    <span class="font-bold text-gray-800">PROFIT</span>
                    <span class="font-bold text-lg text-emerald-600">
                        Rp {{ number_format($dataProfit['brilink']['pendapatan_admin'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ================= MBANKING ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-sm">MBANKING</span>
                <iconify-icon icon="mdi:cellphone-link" class="text-gray-400 text-lg"></iconify-icon>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['mbanking']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['mbanking']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                
                {{-- Baris Saldo Terpakai --}}
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Terpakai</span>
                    @if($dataProfit['mbanking']['saldo_terpakai'] < 0)
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format(abs($dataProfit['mbanking']['saldo_terpakai']), 0, ',', '.') }}</span>
                    @else
                        <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['mbanking']['saldo_terpakai'], 0, ',', '.') }}</span>
                    @endif
                </div>

                {{-- Baris Pendapatan Admin --}}
                <div class="flex justify-between pt-1">
                    <span class="font-bold text-gray-800">PENDAPATAN ADMIN</span>
                    <span class="font-bold text-lg text-emerald-600">
                        Rp {{ number_format($dataProfit['mbanking']['pendapatan_admin'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ================= KASIR PINTAR ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-sm">KASIR PINTAR</span>
                <iconify-icon icon="mdi:store" class="text-gray-400 text-lg"></iconify-icon>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['kasir_pintar']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Saldo Akhir</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['kasir_pintar']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Omset</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['kasir_pintar']['penjualan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-1">
                    <span class="font-bold text-gray-800">LABA</span>
                    <span class="font-bold text-lg text-emerald-600">Rp {{ number_format($dataProfit['kasir_pintar']['laba'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ================= BRANGKAS ================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                <span class="font-bold text-white text-sm">BRANGKAS</span>
                <iconify-icon icon="mdi:safe" class="text-gray-400 text-lg"></iconify-icon>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Brangkas Awal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brangkas']['saldo_awal'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Penambahan Brangkas</span>
                    <span class="font-semibold text-emerald-600">+ Rp {{ number_format($dataProfit['brangkas']['penambahan'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Jumlah Brangkas</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($dataProfit['brangkas']['jumlah_brangkas'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Pemakaian Brangkas</span>
                    <span class="font-semibold text-red-600">- Rp {{ number_format($dataProfit['brangkas']['pemakaian'], 0, ',', '.') }}</span>
                </div>

                {{-- Total Akhir --}}
                <div class="flex justify-between pt-1">
                    <span class="font-bold text-gray-800">BRANGKAS SAAT INI</span>
                    <span class="font-bold text-lg text-blue-600">Rp {{ number_format($dataProfit['brangkas']['saldo_akhir'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================================================= --}}
    {{-- CARD BARU: RINGKASAN PROFIT HARIAN --}}
    {{-- ======================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6 mt-6">
        <div class="bg-gray-800 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:chart-box-outline" class="text-white text-xl"></iconify-icon>
                <h3 class="font-bold text-white text-lg">Rangkuman Profit & Pengeluaran</h3>
            </div>
            <span class="text-xs text-gray-400 font-mono">Berdasarkan Filter Tanggal</span>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- KOLOM 1: PROFIT & LABA --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">Profit & Pendapatan</h4>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Profit Pulsa</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($profitPulsa, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Profit PPOB</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($profitPpob, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">BRILink Admin</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($adminBrilink, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">MBanking Admin</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($adminMbanking, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Laba Kasir Pintar</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($labaKasirPintar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="font-bold text-gray-800 text-sm">Total Profit Kotor</span>
                        <span class="font-bold text-emerald-600">Rp {{ number_format($totalProfitKotor, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- KOLOM 2: PRIVE --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">Pengeluaran</h4>
                    <div class="flex justify-between items-center text-sm pb-2">
                        <span class="text-gray-600">Prive</span>
                        <span class="font-semibold text-red-600">- Rp {{ number_format($totalPrive, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- KOLOM 3: PENDAPATAN HARIAN --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">Rekap Harian</h4>
                    <div class="flex justify-between items-center text-sm pb-2">
                        <span class="text-gray-600">Pendapatan Harian</span>
                        <span class="font-bold text-base text-blue-600">Rp {{ number_format($pendapatanHarian, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm pb-2">
                        <span class="text-gray-600">Pengeluaran Harian</span>
                        <span class="font-bold text-base text-red-600">Rp {{ number_format($pengeluaranHarian, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- KOLOM 4: PENAMBAHAN SALDO --}}
                <div class="space-y-3 flex flex-col justify-center">
                    <div class="p-5 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl text-white shadow-md text-center">
                        <div class="text-xs font-medium text-emerald-100 mb-1 uppercase tracking-wider">Penambahan Saldo</div>
                        <div class="text-2xl font-bold">Rp {{ number_format($penambahanSaldo, 0, ',', '.') }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- REKAPITULASI SALDO KESELURUHAN --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-md p-6 text-white flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                <iconify-icon icon="mdi:wallet" class="text-2xl text-white"></iconify-icon>
            </div>
            <div>
                <div class="text-blue-100 text-sm font-medium">Total Saldo Awal Keseluruhan</div>
                <div class="text-2xl font-bold">Rp {{ number_format($totalSaldoAwal, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="hidden md:block w-px h-12 bg-white/30"></div>
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                <iconify-icon icon="mdi:wallet-check" class="text-2xl text-white"></iconify-icon>
            </div>
            <div>
                <div class="text-blue-100 text-sm font-medium">Total Saldo Akhir Keseluruhan</div>
                <div class="text-2xl font-bold">Rp {{ number_format($totalSaldoAkhir, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

</div>
@endsection