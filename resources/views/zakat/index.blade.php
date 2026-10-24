@extends('layouts.app')

@section('title', 'Laporan Zakat')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:justify-end sm:flex-row gap-2 w-full sm:w-auto mb-4">
        <a href="{{ route('laporan-zakat.index') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:cash-check" class="text-lg"></iconify-icon> Laporan Bayar Zakat
        </a>
        <button type="button" onclick="openBayarZakatModal()"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:cash-register" class="text-lg"></iconify-icon> Bayar Zakat
        </button>
        <a href="{{ route('zakat.invoice-pdf', ['store' => $storeId]) }}" target="_blank"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:printer" class="text-lg"></iconify-icon> Cetak PDF
        </a>
    </div>

    {{-- FILTER TOKO --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end gap-3">
            
            {{-- Filter Toko --}}
            <div class="w-full">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko (Sumber Saldo & Piutang)</label>
                <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Dari Tanggal --}}
            <div class="w-full">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from ?? '' }}" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
            </div>

            {{-- Filter Sampai Tanggal --}}
            <div class="w-full">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to ?? '' }}" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex gap-2 w-full">
                <button type="submit" class="flex-1 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Terapkan Filter
                </button>
                @if($from || $to || $storeId !== 'all')
                    <a href="{{ route('zakat.index') }}" 
                    class="flex-1 bg-gray-100 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center justify-center gap-2">
                        <iconify-icon icon="mdi:close-circle" class="text-lg"></iconify-icon> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- CARD 1: ZAKAT SALDO --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-blue-50 to-white">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:wallet" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Zakat dari Saldo</h3>
            </div>
            <span class="text-[10px] sm:text-xs text-gray-500 bg-white px-2 py-1 rounded border w-max">Snapshot terakhir</span>
        </div>
        
        <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100">
            <div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Total Saldo Aset Likuid</div>
                <div class="text-base sm:text-xl font-bold text-gray-800">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</div>
                <div class="text-[9px] text-gray-400 mt-1">
                    Termasuk Saldo Online: Rp {{ number_format($totalSaldoOnline, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Rate Zakat</div>
                <div class="text-base sm:text-xl font-bold text-gray-800">2,5%</div>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Zakat Saldo</div>
                <div class="text-base sm:text-xl font-bold text-blue-700">Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</div>
            </div>
        </div>

        @if(!empty($breakdownSaldo))
            {{-- TABEL SALDO (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Rekening</th>
                            <th class="text-left py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Saldo Saat Ini</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Zakat (2,5%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($breakdownSaldo as $b)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 font-medium text-gray-800">{{ $b['nama_rekening'] }}</td>
                                <td class="py-2 px-4">
                                    @if($b['keterangan'] && $b['keterangan'] !== '-')
                                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $b['keterangan'] }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 text-right text-gray-700">Rp {{ number_format($b['saldo'], 0, ',', '.') }}</td>
                                <td class="py-2 px-4 text-right font-semibold text-blue-700">Rp {{ number_format($b['zakat'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        @foreach($saldoOnlineList as $so)
                            <tr class="hover:bg-gray-50 bg-blue-50/20">
                                <td class="py-2 px-4 font-medium text-gray-800">{{ $so->marketplace }}</td>
                                <td class="py-2 px-4">
                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px]">Marketplace</span>
                                </td>
                                <td class="py-2 px-4 text-right text-gray-700">Rp {{ number_format($so->saldo, 0, ',', '.') }}</td>
                                <td class="py-2 px-4 text-right font-semibold text-blue-700">Rp {{ number_format(round($so->saldo * 0.025), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-blue-50 border-t-2 border-blue-200">
                        <tr>
                            <td colspan="2" class="py-2 px-4 font-bold text-gray-800">TOTAL</td>
                            <td class="py-2 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td>
                            <td class="py-2 px-4 text-right font-bold text-blue-800">Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- LIST SALDO (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($breakdownSaldo as $b)
                    <div class="p-4 space-y-1 text-xs">
                        <div class="flex justify-between items-start gap-2">
                            <span class="font-bold text-gray-800 text-sm">{{ $b['nama_rekening'] }}</span>
                            @if($b['keterangan'] && $b['keterangan'] !== '-')
                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-[10px]">{{ $b['keterangan'] }}</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-gray-500">Saldo:</span>
                            <span class="font-medium text-gray-700">Rp {{ number_format($b['saldo'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Zakat (2,5%):</span>
                            <span class="font-bold text-blue-700">Rp {{ number_format($b['zakat'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
                @foreach($saldoOnlineList as $so)
                    <div class="p-4 space-y-1 text-xs bg-blue-50/20">
                        <div class="flex justify-between items-start gap-2">
                            <span class="font-bold text-gray-800 text-sm">{{ $so->marketplace }}</span>
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px]">Marketplace</span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-gray-500">Saldo:</span>
                            <span class="font-medium text-gray-700">Rp {{ number_format($so->saldo, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Zakat (2,5%):</span>
                            <span class="font-bold text-blue-700">Rp {{ number_format(round($so->saldo * 0.025), 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="p-4 bg-blue-50 flex justify-between items-center font-bold text-xs">
                    <span class="text-gray-800">TOTAL ZAKAT SALDO</span>
                    <span class="text-blue-800 text-sm">Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</span>
                </div>
            </div>
        @endif

        {{-- FORM INPUT SALDO ONLINE --}}
        <div class="p-4 sm:p-5 bg-gray-50/50 border-t border-gray-100">
            <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Tambah Saldo Online</h4>
            <form onsubmit="submitSaldoOnline(event)" class="flex flex-col sm:flex-row gap-2">
                <div class="w-full sm:w-1/3">
                    <input type="text" id="inputMarketplace" required placeholder="Marketplace (cth: Shopee)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="w-full sm:flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm font-medium">Rp</span>
                    </div>
                    <input type="text" id="inputSaldoOnline" required placeholder="0"
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <button type="submit" id="btnSubmitSaldoOnline" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon> Tambah
                </button>
            </form>
            
            {{-- List Tombol Hapus (Jika ada data) --}}
            @if($saldoOnlineList->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($saldoOnlineList as $so)
                        <div class="inline-flex items-center gap-1 bg-white border border-gray-200 px-2 py-1 rounded text-xs text-gray-600">
                            <span>{{ $so->marketplace }}: <strong>Rp {{ number_format($so->saldo, 0, ',', '.') }}</strong></span>
                            <button type="button" onclick="deleteSaldoOnline({{ $so->id }}, '{{ addslashes($so->marketplace) }}')" class="text-red-500 hover:text-red-700 ml-1">
                                <iconify-icon icon="mdi:close-circle" class="text-sm"></iconify-icon>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- CARD 2: ZAKAT PIUTANG --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-amber-50 to-white">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:account-cash" class="text-amber-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Zakat dari Piutang</h3>
            </div>
            <span class="text-[10px] sm:text-xs text-gray-500 bg-white px-2 py-1 rounded border w-max">Belum lunas</span>
        </div>
        
        <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100">
            <div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Total Sisa Piutang</div>
                <div class="text-base sm:text-xl font-bold text-gray-800">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Rate Zakat</div>
                <div class="text-base sm:text-xl font-bold text-gray-800">2,5%</div>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Zakat Piutang</div>
                <div class="text-base sm:text-xl font-bold text-amber-700">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</div>
            </div>
        </div>

        @if(!empty($piutangList))
            {{-- TABEL PIUTANG (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Keterangan</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Piutang</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Terbayar</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Sisa</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Zakat (2,5%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($piutangList as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 text-gray-800">{{ $p['keterangan'] }}</td>
                                <td class="py-2 px-4 text-right text-gray-700">Rp {{ number_format($p['nominal'], 0, ',', '.') }}</td>
                                <td class="py-2 px-4 text-right text-emerald-600">Rp {{ number_format($p['terbayar'], 0, ',', '.') }}</td>
                                <td class="py-2 px-4 text-right font-semibold text-gray-800">Rp {{ number_format($p['sisa'], 0, ',', '.') }}</td>
                                <td class="py-2 px-4 text-right font-semibold text-amber-700">Rp {{ number_format($p['zakat'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-amber-50 border-t-2 border-amber-200">
                        <tr>
                            <td colspan="3" class="py-2 px-4 font-bold text-gray-800">TOTAL</td>
                            <td class="py-2 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
                            <td class="py-2 px-4 text-right font-bold text-amber-800">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- LIST PIUTANG (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($piutangList as $p)
                    <div class="p-4 space-y-1 text-xs">
                        <div class="font-bold text-gray-800 text-sm mb-2">{{ $p['keterangan'] }}</div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Piutang:</span>
                            <span class="text-gray-700">Rp {{ number_format($p['nominal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Terbayar:</span>
                            <span class="text-emerald-600">Rp {{ number_format($p['terbayar'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Sisa Tagihan:</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($p['sisa'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-gray-50 mt-1">
                            <span class="text-gray-500">Zakat (2,5%):</span>
                            <span class="font-bold text-amber-700">Rp {{ number_format($p['zakat'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="p-4 bg-amber-50 flex justify-between items-center font-bold text-xs">
                    <span class="text-gray-800">TOTAL ZAKAT PIUTANG</span>
                    <span class="text-amber-800 text-sm">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</span>
                </div>
            </div>
        @else
            <div class="py-8 text-center text-gray-500 text-sm">
                <iconify-icon icon="mdi:check-circle-outline" class="text-3xl text-gray-300 block m-auto mb-1 w-max"></iconify-icon>
                Tidak ada piutang belum lunas.
            </div>
        @endif
    </div>

    {{-- CARD 3: ZAKAT BARANG ONLINE --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-purple-50 to-white">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:package-variant-closed" class="text-purple-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Zakat dari Barang Online</h3>
            </div>
            <button type="button" onclick="openBarangModal()"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-purple-600 text-white px-3 py-1.5 rounded-lg hover:bg-purple-700 transition text-xs font-medium shadow-sm">
                <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon>
                Tambah Barang
            </button>
        </div>

        @if($barangList->isEmpty())
            <div class="py-10 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <div class="mb-2 text-sm">Belum ada barang online yang dicatat.</div>
                <button type="button" onclick="openBarangModal()"
                   class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-700 text-sm font-medium">
                    <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon>
                    Tambah Barang Pertama
                </button>
            </div>
        @else
            {{-- TABEL BARANG (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Nama Barang</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-20">Qty</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">HPP</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Zakat (2,5%)</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($barangList as $b)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-800">
                                    {{ $b['nama_barang'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-semibold text-gray-800">{{ $b['qty'] }}</td>
                                <td class="py-3 px-4 text-right text-gray-700">Rp {{ number_format($b['harga_satuan'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($b['total'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-purple-700">
                                    Rp {{ number_format($b['zakat'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1 justify-center">
                                        <button type="button"
                                                onclick="openEditBarangModal({{ $b['id'] }}, '{{ addslashes($b['nama_barang']) }}', {{ $b['qty'] }}, {{ $b['harga_satuan'] }})"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                        </button>
                                        <button type="button" onclick="deleteBarang({{ $b['id'] }}, '{{ addslashes($b['nama_barang']) }}')"
                                                class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-purple-50 border-t-2 border-purple-200">
                        <tr>
                            <td colspan="3" class="py-3 px-4 font-bold text-gray-800 text-right">TOTAL NILAI BARANG</td>
                            <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalBarang, 0, ',', '.') }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="bg-purple-100">
                            <td colspan="4" class="py-3 px-4 font-bold text-purple-900 text-right">ZAKAT BARANG (2,5%)</td>
                            <td class="py-3 px-4 text-right font-bold text-purple-900 text-base">Rp {{ number_format($zakatBarang, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- LIST BARANG (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($barangList as $b)
                    <div class="p-4 space-y-1 text-xs">
                        <div class="font-bold text-gray-800 text-sm mb-2">{{ $b['nama_barang'] }}</div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Qty:</span>
                            <span class="font-semibold text-gray-800">{{ $b['qty'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">HPP:</span>
                            <span class="text-gray-700">Rp {{ number_format($b['harga_satuan'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Total:</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($b['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-gray-50 mt-1">
                            <span class="text-gray-500">Zakat (2,5%):</span>
                            <span class="font-bold text-purple-700">Rp {{ number_format($b['zakat'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="button" onclick="openEditBarangModal({{ $b['id'] }}, '{{ addslashes($b['nama_barang']) }}', {{ $b['qty'] }}, {{ $b['harga_satuan'] }})"
                                    class="flex-1 inline-flex justify-center items-center gap-1 text-blue-600 bg-blue-50 border border-blue-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                            </button>
                            <button type="button" onclick="deleteBarang({{ $b['id'] }}, '{{ addslashes($b['nama_barang']) }}')"
                                    class="flex-1 inline-flex justify-center items-center gap-1 text-red-600 bg-red-50 border border-red-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
                <div class="p-4 bg-purple-50 space-y-1 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-800">TOTAL NILAI BARANG</span>
                        <span class="font-bold text-gray-800 text-sm">Rp {{ number_format($totalBarang, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-purple-900">ZAKAT BARANG (2,5%)</span>
                        <span class="font-bold text-purple-900 text-base">Rp {{ number_format($zakatBarang, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- CARD PENGURANG: HUTANG & TABUNGAN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        {{-- HUTANG --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-red-50 to-white">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="mdi:file-document-outline" class="text-red-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Kewajiban: Hutang</h3>
                </div>
                <span class="text-xs font-bold text-red-600">- Rp {{ number_format($totalHutang, 0, ',', '.') }}</span>
            </div>
            <div class="overflow-x-auto max-h-64">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 border-b sticky top-0">
                        <tr>
                            <th class="text-left py-2 px-4 font-semibold text-gray-500 uppercase">Pemberi Hutang</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($hutangList as $h)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 text-gray-800">
                                    {{ $h->nama_pemberi_hutang }}
                                    <div class="text-[10px] text-gray-400">{{ $h->tanggal->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-2 px-4 text-right text-red-600 font-semibold">Rp {{ number_format($h->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-gray-500">Tidak ada hutang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABUNGAN --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-orange-50 to-white">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="mdi:piggy-bank" class="text-orange-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Kewajiban: Tabungan</h3>
                </div>
                <span class="text-xs font-bold text-orange-600">- Rp {{ number_format($totalTabungan, 0, ',', '.') }}</span>
            </div>
            <div class="overflow-x-auto max-h-64">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 border-b sticky top-0">
                        <tr>
                            <th class="text-left py-2 px-4 font-semibold text-gray-500 uppercase">Nama Akun</th>
                            <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tabunganList as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 text-gray-800 font-medium">{{ $t['nama'] }}</td>
                                <td class="py-2 px-4 text-right text-orange-600 font-semibold">Rp {{ number_format($t['saldo'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-gray-500">Tidak ada saldo tabungan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CARD 4: TOTAL ZAKAT --}}
    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl shadow-md overflow-hidden mb-6">
        <div class="flex flex-col md:flex-row">
            
            {{-- BAGIAN KIRI: TOTAL ZAKAT --}}
            <div class="p-6 md:w-1/3 flex flex-col justify-center items-center md:items-start border-b md:border-b-0 md:border-r border-white/20 text-white">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mb-3">
                    <iconify-icon icon="mdi:hand-heart" class="text-3xl text-white"></iconify-icon>
                </div>
                <div class="text-emerald-100 text-sm font-medium text-center md:text-left">Total Zakat (2,5%)</div>
                <div class="text-3xl sm:text-4xl font-bold mt-1">Rp {{ number_format($totalZakat, 0, ',', '.') }}</div>
                <div class="text-xs text-emerald-100 mt-2 text-center md:text-left">
                    *Dihitung dari Harta Bersih
                </div>
            </div>

            {{-- BAGIAN KANAN: RINCIAN PERHITUNGAN --}}
            <div class="p-4 sm:p-6 md:w-2/3 bg-white/5 text-white">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-8">
                    
                    {{-- Kolom Kiri: Nilai Aset & Kewajiban --}}
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-emerald-200 uppercase tracking-wider mb-2 border-b border-white/20 pb-1">Nilai Harta (Aset)</h4>
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-emerald-50">1. Saldo & Online</span>
                            <span class="font-semibold">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-emerald-50">2. Piutang</span>
                            <span class="font-semibold">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-emerald-50">3. Barang Online</span>
                            <span class="font-semibold">Rp {{ number_format($totalBarang, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-sm font-bold text-white">
                            <span>Total Harta</span>
                            <span>Rp {{ number_format($totalHarta, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center text-sm text-red-200 mt-1">
                            <span>(-) Hutang</span>
                            <span>Rp {{ number_format($totalHutang, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-red-200">
                            <span>(-) Tabungan</span>
                            <span>Rp {{ number_format($totalTabungan, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-sm font-bold text-yellow-300">
                            <span>Harta Bersih (Dasar Zakat)</span>
                            <span>Rp {{ number_format($hartaBersih, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Zakat yang Dihasilkan --}}
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-emerald-200 uppercase tracking-wider mb-2 border-b border-white/20 pb-1">Nilai Zakat (2,5%)</h4>
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-emerald-50">Zakat Saldo</span>
                            <span class="font-semibold text-white">Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-emerald-50">Zakat Piutang</span>
                            <span class="font-semibold text-white">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-emerald-50">Zakat Barang</span>
                            <span class="font-semibold text-white">Rp {{ number_format($zakatBarang, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-sm font-bold text-white">
                            <span>Subtotal Zakat Aset</span>
                            <span>Rp {{ number_format($zakatSaldo + $zakatPiutang + $zakatBarang, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center text-sm text-red-200 mt-1">
                            <span>(-) Zakat Hutang</span>
                            <span>Rp {{ number_format($zakatHutang, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-red-200">
                            <span>(-) Zakat Tabungan</span>
                            <span>Rp {{ number_format($zakatTabungan, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-sm font-bold text-yellow-300">
                            <span>Total Zakat Akhir</span>
                            <span>Rp {{ number_format($totalZakat, 0, ',', '.') }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL ZAKAT BARANG MANDIRI --}}
<div id="barangModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBarangModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="barangModalIcon" icon="mdi:plus-circle" class="text-purple-600 text-lg"></iconify-icon>
                <h3 id="barangModalTitle" class="font-bold text-gray-800">Tambah Barang Online</h3>
            </div>
            <button type="button" onclick="closeBarangModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="barangForm" onsubmit="submitBarangForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="barangId" value="">
            <input type="hidden" id="barangMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorBarang"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" id="barangNama" name="nama_barang" required placeholder="cth: Oneset"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                        <input type="number" id="barangQty" name="qty" required min="1" value="1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">HPP <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm font-medium">Rp</span>
                            </div>
                            <input type="text" id="barangHarga" name="harga_satuan" required placeholder="0"
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0 mt-auto">
                <button type="button" onclick="closeBarangModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitBarang" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitBarangText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL BAYAR ZAKAT --}}
<div id="bayarZakatModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBayarZakatModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-emerald-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cash-register" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Konfirmasi Bayar Zakat</h3>
            </div>
            <button type="button" onclick="closeBayarZakatModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="bayarZakatForm" onsubmit="submitBayarZakat(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf

            <input type="hidden" id="inputDetailBreakdown" name="detail_breakdown">
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorBayarZakat"></div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-center">
                    <div class="text-sm text-emerald-800 mb-1">Total Zakat yang Harus Dibayar</div>
                    <div class="text-3xl font-bold text-emerald-700">Rp {{ number_format($totalZakat, 0, ',', '.') }}</div>
                </div>

                <input type="hidden" name="total_zakat" value="{{ $totalZakat }}">
                <input type="hidden" name="total_saldo" value="{{ $totalSaldo }}">
                <input type="hidden" name="total_piutang" value="{{ $totalPiutang }}">
                <input type="hidden" name="total_barang" value="{{ $totalBarang }}">
                <input type="hidden" name="total_hutang" value="{{ $totalHutang }}">
                <input type="hidden" name="total_tabungan" value="{{ $totalTabungan }}">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" placeholder="cth: Dibayar via transfer BRI"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeBayarZakatModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitBayarZakat" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:check-circle" class="text-lg"></iconify-icon> Konfirmasi Bayar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_ZAKAT = document.querySelector('meta[name="csrf-token"]').content;

    // Format Harga
    document.getElementById('barangHarga')?.addEventListener('input', function () {
        const v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
    });

    function openBarangModal() {
        document.getElementById('barangForm').reset();
        document.getElementById('modalErrorBarang').classList.add('hidden');
        document.getElementById('barangMode').value = 'create';
        document.getElementById('barangId').value = '';
        document.getElementById('barangModalTitle').textContent = 'Tambah Barang Online';
        document.getElementById('barangModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitBarangText').textContent = 'Simpan';
        document.getElementById('barangQty').value = 1;
        document.getElementById('barangHarga').value = '';
        document.getElementById('barangModal').classList.remove('hidden');
        document.getElementById('barangModal').classList.add('flex');
    }

    function openEditBarangModal(id, nama, qty, harga) {
        document.getElementById('barangForm').reset();
        document.getElementById('modalErrorBarang').classList.add('hidden');
        document.getElementById('barangMode').value = 'edit';
        document.getElementById('barangId').value = id;
        document.getElementById('barangModalTitle').textContent = 'Edit Barang Online';
        document.getElementById('barangModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitBarangText').textContent = 'Simpan Perubahan';
        document.getElementById('barangNama').value = nama;
        document.getElementById('barangQty').value = qty;
        document.getElementById('barangHarga').value = new Intl.NumberFormat('id-ID').format(harga);
        document.getElementById('barangModal').classList.remove('hidden');
        document.getElementById('barangModal').classList.add('flex');
    }

    function closeBarangModal() {
        document.getElementById('barangModal').classList.add('hidden');
        document.getElementById('barangModal').classList.remove('flex');
    }

    async function submitBarangForm(e) {
        e.preventDefault();
        const mode = document.getElementById('barangMode').value;
        const id = document.getElementById('barangId').value;
        const btn = document.getElementById('btnSubmitBarang');
        const btnText = document.getElementById('btnSubmitBarangText');
        const errDiv = document.getElementById('modalErrorBarang');
        const orig = btnText.textContent;
        btn.disabled = true; btnText.textContent = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(e.target);
        formData.set('harga_satuan', document.getElementById('barangHarga').value.replace(/[^0-9]/g, ''));

        let url = "{{ route('zakat.barang.store-ajax') }}";
        if (mode === 'edit') {
            url = `/zakat/barang/${id}/ajax`;
            formData.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_ZAKAT, 'Accept': 'application/json' }, body: formData });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            closeBarangModal();
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false; btnText.textContent = orig;
        }
    }

    async function deleteBarang(id, nama) {
        if (!confirm(`Yakin hapus barang "${nama}"?`)) return;
        try {
            const res = await fetch(`/zakat/barang/${id}/ajax`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF_ZAKAT, 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok) { alert(data.message); return; }
            window.location.reload();
        } catch (err) { alert('Gagal: ' + err.message); }
    }
</script>

<script>
    // Format input Rupiah
    document.getElementById('inputSaldoOnline')?.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, '');
        this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
    });

    async function submitSaldoOnline(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitSaldoOnline');
        const marketplace = document.getElementById('inputMarketplace').value;
        const saldo = document.getElementById('inputSaldoOnline').value;

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('marketplace', marketplace);
        formData.append('saldo', saldo.replace(/[^0-9]/g, ''));

        try {
            const res = await fetch("{{ route('zakat.saldo-online.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon> Tambah';
        }
    }

    async function deleteSaldoOnline(id, marketplace) {
        if (!confirm(`Yakin hapus saldo online "${marketplace}"?`)) return;
        try {
            const res = await fetch(`/zakat/saldo-online/${id}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    function openBayarZakatModal() {
        document.getElementById('bayarZakatForm').reset();
        document.getElementById('modalErrorBayarZakat').classList.add('hidden');
        
        // Simpan SEMUA data yang tampil di halaman Index ke JSON
        const breakdownData = {
            saldo:         @json($breakdownSaldo),
            saldo_online:  @json($saldoOnlineList),      // ✅ TAMBAH
            piutang:       @json($piutangList),
            barang:        @json($barangList),
            tabungan:      @json($tabunganList),
            hutang:        @json($hutangList),

            zakat_saldo:        {{ $zakatSaldo }},
            zakat_saldo_online: {{ $zakatSaldoOnline }}, // ✅ TAMBAH
            zakat_piutang:      {{ $zakatPiutang }},
            zakat_barang:       {{ $zakatBarang }},
            zakat_hutang:       {{ $zakatHutang }},
            zakat_tabungan:     {{ $zakatTabungan }},

            total_saldo:    {{ $totalSaldo }},           // ✅ TAMBAH (nilai final sudah termasuk online)
            total_piutang:  {{ $totalPiutang }},         // ✅ TAMBAH
            total_barang:   {{ $totalBarang }},          // ✅ TAMBAH
            total_hutang:   {{ $totalHutang }},          // ✅ TAMBAH
            total_tabungan: {{ $totalTabungan }},        // ✅ TAMBAH

            total_harta:    {{ $totalHarta }},
            harta_bersih:   {{ $hartaBersih }},
            total_zakat:    {{ $totalZakat }},           // ✅ TAMBAH
        };

        document.getElementById('inputDetailBreakdown').value = JSON.stringify(breakdownData);

        document.getElementById('bayarZakatModal').classList.remove('hidden');
        document.getElementById('bayarZakatModal').classList.add('flex');
    }

    function closeBayarZakatModal() {
        document.getElementById('bayarZakatModal').classList.add('hidden');
        document.getElementById('bayarZakatModal').classList.remove('flex');
    }

    async function submitBayarZakat(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitBayarZakat');
        const errDiv = document.getElementById('modalErrorBayarZakat');
        btn.disabled = true; btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(e.target);

        try {
            const res = await fetch("{{ route('zakat.bayar') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            
            alert(data.message);
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false; btn.innerHTML = '<iconify-icon icon="mdi:check-circle" class="text-lg"></iconify-icon> Konfirmasi Bayar';
        }
    }
</script>
@endsection