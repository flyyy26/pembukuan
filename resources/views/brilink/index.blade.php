@extends('layouts.app')

@section('title', 'Laporan BRILink')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:bank" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan BRILink</h2>
                <p class="text-xs sm:text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>

        @if($storeId)
        <a href="{{ route('brilink.import') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:cloud-upload" class="text-lg"></iconify-icon>
            Import BRILink
        </a>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:check-circle" class="text-green-600 text-lg mt-0.5 flex-shrink-0"></iconify-icon>
            <div class="text-sm">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5 flex-shrink-0"></iconify-icon>
            <div class="text-sm">{{ session('error') }}</div>
        </div>
    @endif

    @if($stores->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-4 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:alert" class="text-yellow-600 text-lg mt-0.5 flex-shrink-0"></iconify-icon>
            <div class="text-sm">
                <strong>Belum ada toko BRILink.</strong>
                Silakan <a href="{{ route('stores.index', ['modal' => 'create']) }}" class="underline font-semibold">tambah toko</a> dulu dengan marketplace BRILink.
            </div>
        </div>
    @else

        {{-- ============ FILTER ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end" id="filterForm">
                <div class="w-full md:flex-1 md:min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                    <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent">
                        <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent">
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent">
                </div>
                <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                    <button type="submit"
                            class="flex-1 md:flex-none inline-flex justify-center items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium">
                        <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                        Filter
                    </button>
                    @if($from || $to || $storeId !== 'all')
                        <a href="{{ route('brilink.index') }}"
                        class="flex-1 md:flex-none inline-flex justify-center items-center gap-2 text-gray-500 hover:text-gray-700 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg md:border-0 md:bg-transparent">
                            <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ============ SUMMARY CARDS ============ --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            
            {{-- 1. Total Omset Kasir PPOB --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:cash-multiple" class="text-blue-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Total Omset Kasir PPOB</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($summary['total_omset_ppob'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 2. Total Tarik Tunai (EDC BRI) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-teal-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:credit-card-outline" class="text-teal-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Tarik Tunai (EDC BRI)</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($summary['total_tarik_edc'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 3. Total Tarik Tunai (MBanking) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:cellphone" class="text-purple-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Tarik Tunai (MBanking)</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($summary['total_tarik_mbanking'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 4. Total Setoran BRILink --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:bank-transfer-in" class="text-indigo-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Total Setoran BRILink</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-indigo-600 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($summary['total_setoran'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 5. Total Pendapatan Admin Toko --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:hand-coin" class="text-emerald-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Pendapatan Admin Toko</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-emerald-600 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($summary['total_pendapatan_admin'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 6. Pindah Saldo Internal --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:swap-horizontal" class="text-orange-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Pindah Saldo Kas Internal</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-orange-600 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($summary['total_pindah_saldo'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 7. Top Up PPOB --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:wallet-plus" class="text-cyan-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Pengeluaran Top Up PPOB</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['topup_ppob'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 8. Top Up Pulsa --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-sky-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:cellphone-arrow-down" class="text-sky-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Pengeluaran Top Up Pulsa</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['topup_pulsa'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 9. Piutang Bu Haji --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:account-cash" class="text-amber-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Piutang Bu Haji</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['piutang_haji'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 10. Piutang Iyus --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:account-cash" class="text-amber-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Piutang Iyus</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['piutang_iyus'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 11. Piutang Lain-lain --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:account-cash" class="text-amber-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Piutang Lain-lain</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['piutang_lain'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 12. Prive --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:cash-minus" class="text-red-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Pengeluaran Prive</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-red-600 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['prive'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 13. Operasional Konveksi --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:store" class="text-gray-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Operasional Konveksi</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['operasional'], 0, ',', '.') }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="akar-icons:bank" class="text-blue-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Operational BRILink</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 break-words">
                    <span class="text-[10px] sm:text-xs text-gray-400 font-normal">Rp</span>
                    {{ number_format($breakdownPengeluaran['op_brilink'], 0, ',', '.') }}
                </div>
            </div>

            {{-- 14. Volume Transaksi Agen --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="mdi:receipt-text-outline" class="text-gray-600 text-base sm:text-lg"></iconify-icon>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Volume Transaksi Agen</div>
                <div class="text-sm sm:text-lg md:text-xl font-bold text-gray-800">
                    {{ number_format($summary['total_volume_transaksi']) }} <span class="text-[10px] sm:text-xs text-gray-500 font-normal">Trx</span>
                </div>
            </div>
        </div>

        {{-- ============ REKAPITULASI PENJUALAN & KAS ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="mdi:finance" class="text-emerald-600 text-xl"></iconify-icon>
                    <h3 class="font-bold text-gray-800 text-base sm:text-lg">Rekapitulasi Penjualan & Kas</h3>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                
                {{-- BLOK 1: ARUS Dana --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1">
                        <iconify-icon icon="mdi:swap-vertical" class="text-base"></iconify-icon> Arus Dana
                    </div>
                    <div class="space-y-3">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                            <span class="text-sm text-gray-600">Uang Masuk</span>
                            <span class="text-sm font-semibold text-emerald-600">Rp {{ number_format($summary['uang_masuk'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                            <span class="text-sm text-gray-600">Uang Keluar</span>
                            <span class="text-sm font-semibold text-red-600">Rp {{ number_format($summary['uang_keluar'], 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 mt-2 flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                            <span class="text-sm font-bold text-gray-800">Jumlah Penjualan (Net)</span>
                            <span class="text-base font-bold {{ $summary['jml_penjualan'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                                Rp {{ number_format($summary['jml_penjualan'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- BLOK 2: REKONSILIASI SISTEM --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1">
                        <iconify-icon icon="mdi:calculator-variant" class="text-base"></iconify-icon> Kalkulasi Sistem
                    </div>
                    <div class="space-y-3">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                            <span class="text-sm text-gray-600">Jumlah Kas Uang</span>
                            <span class="text-sm font-semibold text-gray-800">Rp {{ number_format($summary['jml_kas_uang'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                            <span class="text-sm text-gray-600">Selisih Kas Uang</span>
                            <span class="text-sm font-semibold {{ $summary['selisih_kas_uang'] == 0 ? 'text-gray-800' : 'text-orange-600' }}">
                                Rp {{ number_format($summary['selisih_kas_uang'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="text-[10px] text-gray-400 leading-tight mt-2">
                            * Kas Uang dikurangi Jumlah Penjualan (Net)
                        </div>
                    </div>
                </div>

                {{-- BLOK 3: VALIDASI FISIK (INPUT MANUAL) --}}
                <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-200 md:col-span-2 lg:col-span-1">
                    <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider mb-3 flex items-center gap-1">
                        <iconify-icon icon="mdi:cash-check" class="text-base"></iconify-icon> Validasi Uang Fisik
                    </div>
                    
                    @if($latestReport)
                        @if($storeId === 'all')
                            {{-- ✅ TAMPILAN JIKA SEMUA TOKO DIPILIH (HANYA TEKS) --}}
                            <div class="flex flex-col items-center justify-center py-6 text-center space-y-2">
                                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mb-1">
                                    <iconify-icon icon="mdi:store-alert-outline" class="text-indigo-500 text-2xl"></iconify-icon>
                                </div>
                                <div class="text-sm font-medium text-indigo-900">Pilih Toko Spesifik</div>
                                <div class="text-xs text-indigo-600/80 px-4">
                                    Validasi Uang Fisik hanya dapat dilakukan saat Anda memfilter <strong>satu toko</strong> tertentu.
                                </div>
                                <a href="{{ route('brilink.index', ['store' => $stores->first()?->id ?? '']) }}" class="mt-2 inline-flex items-center gap-1 text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                                    Pilih Toko Sekarang
                                </a>
                            </div>
                        @else
                            {{-- TAMPILAN FORM INPUT (SEPERTI SEBELUMNYA) --}}
                            <form action="{{ route('brilink.update-validasi', $latestReport->id) }}" method="POST" class="space-y-3">
                                @csrf
                                
                                {{-- Input Kas Akhir --}}
                                <div>
                                    <label class="block text-xs text-indigo-600 mb-1">Kas Akhir (Uang Fisik Aktual)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-sm text-gray-500 font-medium">Rp</span>
                                        </div>
                                        <input type="text" id="inputKasAkhir" name="manual_kas_akhir" value="{{ $summary['manual_kas_akhir'] ? number_format($summary['manual_kas_akhir'], 0, ',', '.') : '' }}" 
                                            class="w-full border border-indigo-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold text-indigo-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm input-format-rupiah" placeholder="0">
                                    </div>
                                </div>

                                {{-- Jumlah Penjualan (Net) --}}
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center px-1 py-1 border-b border-indigo-100 gap-1">
                                    <span class="text-xs text-gray-600">Jumlah Penjualan (Net)</span>
                                    <span id="viewJmlPenjualan" data-value="{{ $summary['jml_penjualan'] }}" class="text-sm font-semibold text-gray-800">
                                        Rp {{ number_format($summary['jml_penjualan'], 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Input Kas Awal --}}
                                <div>
                                    <label class="block text-xs text-indigo-600 mb-1">Kas Awal</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-sm text-gray-500 font-medium">Rp</span>
                                        </div>
                                        <input type="text" id="inputKasAwal" name="manual_kas_awal" value="{{ $summary['manual_kas_awal'] ? number_format($summary['manual_kas_awal'], 0, ',', '.') : '' }}" 
                                            class="w-full border border-indigo-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold text-indigo-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm input-format-rupiah" placeholder="0">
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm flex items-center justify-center gap-2 mt-2">
                                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon>
                                    Simpan Validasi
                                </button>
                            </form>

                            <div class="border-t border-indigo-200 mt-4 pt-3 flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                                <span class="text-sm font-bold text-indigo-900">Selisih Validasi</span>
                                <span id="viewSelisihValidasi" class="text-base font-bold {{ $summary['selisih_validasi'] == 0 ? 'text-indigo-900' : 'text-red-600' }}">
                                    Rp {{ number_format($summary['selisih_validasi'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="text-sm font-medium text-indigo-400 py-4 text-center">Belum ada report aktif</div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ============ TABEL SALDO PER REKENING ============ --}}
        @if(!empty($saldoPerRekening))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-4 py-4 sm:px-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-emerald-50 to-white">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="mdi:wallet" class="text-emerald-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800">Saldo Per Rekening / E-Wallet</h3>
                </div>
                @if($latestReport)
                <div class="text-[10px] text-gray-500 bg-white px-2 py-1 rounded border w-max">
                    Snapshot: {{ $latestReport->periode_label }}
                </div>
                @endif
            </div>
            
            {{-- TAMPILAN TABEL (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nama Rekening</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Saldo Awal</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Saldo Akhir</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($saldoPerRekening as $r)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-2.5 px-4 font-medium text-gray-800 whitespace-nowrap">{{ $r['nama'] }}</td>
                            <td class="py-2.5 px-4 whitespace-nowrap">
                                @if($r['keterangan'])
                                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $r['keterangan'] }}</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-right text-gray-600 whitespace-nowrap">
                                {{ number_format($r['saldo_awal'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-semibold text-gray-800 whitespace-nowrap">
                                {{ number_format($r['saldo'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-right text-xs whitespace-nowrap {{ $r['selisih'] > 0 ? 'text-green-600' : ($r['selisih'] < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                {{ $r['selisih'] > 0 ? '+' : '' }}{{ number_format($r['selisih'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-emerald-50 border-t-2 border-emerald-200">
                        <tr>
                            <td colspan="2" class="py-3 px-4 font-bold text-gray-800 whitespace-nowrap">TOTAL KESELURUHAN</td>
                            <td class="py-3 px-4 text-right font-bold text-gray-700 whitespace-nowrap">
                                {{ number_format(collect($saldoPerRekening)->sum('saldo_awal'), 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 text-base whitespace-nowrap">
                                {{ number_format(collect($saldoPerRekening)->sum('saldo'), 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold whitespace-nowrap {{ collect($saldoPerRekening)->sum('selisih') >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                {{ number_format(collect($saldoPerRekening)->sum('selisih'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- TAMPILAN CARD (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($saldoPerRekening as $r)
                    <div class="p-4 space-y-1 text-xs">
                        <div class="flex justify-between items-start gap-2">
                            <div class="font-bold text-gray-800 text-sm">{{ $r['nama'] }}</div>
                            @if($r['keterangan'])
                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-[10px]">{{ $r['keterangan'] }}</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-gray-500">Saldo Awal:</span>
                            <span class="text-gray-600">{{ number_format($r['saldo_awal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Saldo Akhir:</span>
                            <span class="font-semibold text-gray-800">{{ number_format($r['saldo'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-gray-50 mt-1">
                            <span class="text-gray-500">Selisih:</span>
                            <span class="font-semibold {{ $r['selisih'] > 0 ? 'text-green-600' : ($r['selisih'] < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                {{ $r['selisih'] > 0 ? '+' : '' }}{{ number_format($r['selisih'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endforeach
                <div class="p-4 bg-emerald-50 space-y-1 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-800">TOTAL AWAL</span>
                        <span class="font-bold text-gray-800">{{ number_format(collect($saldoPerRekening)->sum('saldo_awal'), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-800">TOTAL AKHIR</span>
                        <span class="font-bold text-emerald-700 text-sm">{{ number_format(collect($saldoPerRekening)->sum('saldo'), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-800">TOTAL SELISIH</span>
                        <span class="font-bold {{ collect($saldoPerRekening)->sum('selisih') >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format(collect($saldoPerRekening)->sum('selisih'), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ============ BAGIAN BREAKDOWN PENDAPATAN & PENGELUARAN ============ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            {{-- Breakdown Pendapatan Admin --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-4">
                    <iconify-icon icon="mdi:hand-coin-outline" class="text-emerald-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800">Pendapatan Admin</h3>
                </div>
                <div class="space-y-3">
                    @php
                        $pendapatanItems = [
                            ['label' => 'Admin Setoran BRILink',       'value' => $breakdownPendapatan['setoran_admin'], 'icon' => 'mdi:bank-transfer-in'],
                            ['label' => 'Admin Tarik Tunai EDC',       'value' => $breakdownPendapatan['tarik_edc'],     'icon' => 'mdi:credit-card-outline'],
                            ['label' => 'Admin Tarik Tunai MBanking',  'value' => $breakdownPendapatan['tarik_mb'],      'icon' => 'mdi:cellphone'],
                            ['label' => 'Komisi Merchant (KMC)',       'value' => $breakdownPendapatan['kmc'],           'icon' => 'mdi:percent-outline'],
                        ];
                        $pendapatanTotal = array_sum(array_column($pendapatanItems, 'value'));
                    @endphp
                    @foreach($pendapatanItems as $p)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-100 last:border-0 gap-1">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="{{ $p['icon'] }}" class="text-gray-400 text-sm flex-shrink-0"></iconify-icon>
                            <span class="text-sm text-gray-700">{{ $p['label'] }}</span>
                        </div>
                        <div class="text-sm font-semibold text-gray-800 text-right sm:text-left">
                            Rp {{ number_format($p['value'], 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-3 border-t-2 border-gray-200 gap-1">
                        <span class="text-sm font-bold text-gray-800">Total</span>
                        <span class="text-base font-bold text-emerald-600">
                            Rp {{ number_format($pendapatanTotal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Breakdown Pengeluaran --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-4">
                    <iconify-icon icon="mdi:cash-minus" class="text-red-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800">Rincian Pengeluaran</h3>
                </div>
                
                <div class="space-y-3">
                    @php                        
                        $pengeluaranItems = [
                            ['label' => 'Pindah Saldo Kas Internal', 'value' => $breakdownPengeluaran['pindah_saldo'], 'icon' => 'mdi:swap-horizontal', 'color' => 'text-orange-600'],
                            ['label' => 'Top Up PPOB',               'value' => $breakdownPengeluaran['topup_ppob'],   'icon' => 'mdi:wallet-plus',     'color' => 'text-cyan-600'],
                            ['label' => 'Top Up Pulsa',              'value' => $breakdownPengeluaran['topup_pulsa'],  'icon' => 'mdi:cellphone-arrow-down', 'color' => 'text-sky-600'],
                            ['label' => 'Piutang Bu Haji',           'value' => $breakdownPengeluaran['piutang_haji'], 'icon' => 'mdi:account-cash',    'color' => 'text-amber-600'],
                            ['label' => 'Piutang Iyus',              'value' => $breakdownPengeluaran['piutang_iyus'], 'icon' => 'mdi:account-cash',    'color' => 'text-amber-600'],
                            ['label' => 'Piutang Lain-lain',         'value' => $breakdownPengeluaran['piutang_lain'], 'icon' => 'mdi:account-cash',    'color' => 'text-amber-600'],
                            ['label' => 'Prive',                     'value' => $breakdownPengeluaran['prive'],        'icon' => 'mdi:cash-minus',      'color' => 'text-red-600'],
                            ['label' => 'Operasional Konveksi',      'value' => $breakdownPengeluaran['operasional'],  'icon' => 'mdi:store',           'color' => 'text-gray-700'],
                            ['label' => 'Operational BRILink',       'value' => $breakdownPengeluaran['op_brilink'],   'icon' => 'akar-icons:bank',        'color' => 'text-blue-600'],
                        ];
                    @endphp

                    @foreach($pengeluaranItems as $p)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-100 last:border-0 gap-1">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="{{ $p['icon'] }}" class="text-gray-400 text-sm flex-shrink-0"></iconify-icon>
                                <span class="text-sm text-gray-700">{{ $p['label'] }}</span>
                            </div>
                            <div class="text-sm font-semibold {{ $p['color'] }} text-right sm:text-left">
                                Rp {{ number_format($p['value'], 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ==========================================
        // 1. LOGIKA PERHITUNGAN REAL-TIME (BLOK 3)
        // ==========================================
        const inputKasAkhir = document.getElementById('inputKasAkhir');
        const inputKasAwal = document.getElementById('inputKasAwal');
        const viewJmlPenjualan = document.getElementById('viewJmlPenjualan');
        const viewSelisih = document.getElementById('viewSelisihValidasi');

        if (inputKasAkhir && inputKasAwal && viewJmlPenjualan && viewSelisih) {
            const hitungSelisih = () => {
                let valAkhir = inputKasAkhir.value.replace(/[^0-9]/g, '');
                let valAwal = inputKasAwal.value.replace(/[^0-9]/g, '');
                let valPenjualan = viewJmlPenjualan.getAttribute('data-value');

                let numAkhir = valAkhir ? parseInt(valAkhir) : 0;
                let numAwal = valAwal ? parseInt(valAwal) : 0;
                let numPenjualan = valPenjualan ? parseInt(valPenjualan) : 0;

                let selisih = (numAkhir - numPenjualan) - numAwal;

                let formatted = new Intl.NumberFormat('id-ID').format(selisih);
                
                viewSelisih.innerText = 'Rp ' + formatted;

                if (selisih === 0) {
                    viewSelisih.classList.remove('text-red-600');
                    viewSelisih.classList.add('text-indigo-900');
                } else {
                    viewSelisih.classList.remove('text-indigo-900');
                    viewSelisih.classList.add('text-red-600');
                }
            };

            inputKasAkhir.addEventListener('input', hitungSelisih);
            inputKasAwal.addEventListener('input', hitungSelisih);
        }

        // ==========================================
        // 2. FORMATTER RUPIAH & BERSIHKAN SEBELUM SUBMIT
        // ==========================================
        document.querySelectorAll('.input-format-rupiah').forEach(input => {
            let initialValue = input.value.replace(/[^0-9]/g, '');
            if (initialValue) {
                input.value = new Intl.NumberFormat('id-ID').format(initialValue);
            }
            input.addEventListener('input', function() {
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
            });
        });

        const formValidasi = document.querySelector('form[action*="validasi"]');
        if (formValidasi) {
            formValidasi.addEventListener('submit', function() {
                document.querySelectorAll('.input-format-rupiah').forEach(input => {
                    input.value = input.value.replace(/\./g, '');
                });
            });
        }
    });
</script>

{{-- Chart.js untuk trend bulanan --}}
@if(!empty($monthlyTrend ?? []))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('brilinkMonthlyChart');
    if (!ctx) return;
    const months = @json(array_reverse($monthlyTrend));
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months.map(m => m.label),
            datasets: [
                { label: 'Omset', data: months.map(m => m.omset), backgroundColor: 'rgba(59, 130, 246, 0.7)', borderRadius: 6 },
                { label: 'Pendapatan', data: months.map(m => m.pendapatan), backgroundColor: 'rgba(16, 185, 129, 0.7)', borderRadius: 6 },
                { label: 'Profit', data: months.map(m => m.profit), backgroundColor: 'rgba(20, 184, 166, 0.9)', borderRadius: 6 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 }, usePointStyle: true, padding: 16 } },
                tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': Rp ' + (ctx.parsed.y || 0).toLocaleString('id-ID') } },
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: v => v >= 1000000 ? 'Rp ' + (v/1000000).toFixed(1) + 'jt' : v >= 1000 ? 'Rp ' + (v/1000).toFixed(0) + 'rb' : 'Rp ' + v } },
            },
        },
    });
});
</script>
@endif

@endsection