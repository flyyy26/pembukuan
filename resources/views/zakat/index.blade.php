@extends('layouts.app')

@section('title', 'Laporan Zakat')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:hand-heart" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Zakat</h2>
                <p class="text-xs sm:text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }} · Rate 2,5%</p>
            </div>
        </div>
        <a href="{{ route('zakat.invoice-pdf', ['store' => $storeId]) }}" target="_blank"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:printer" class="text-lg"></iconify-icon> Cetak PDF
        </a>
    </div>

    {{-- FILTER TOKO --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko (Sumber Saldo & Piutang)</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto mt-2 sm:mt-0">
                <button type="submit" class="w-full bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
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
                <div class="p-4 bg-blue-50 flex justify-between items-center font-bold text-xs">
                    <span class="text-gray-800">TOTAL ZAKAT SALDO</span>
                    <span class="text-blue-800 text-sm">Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</span>
                </div>
            </div>
        @endif
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
            <a href="{{ route('products.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white border border-purple-600 text-purple-700 px-3 py-1.5 rounded-lg hover:bg-purple-50 transition text-xs font-medium">
                <iconify-icon icon="mdi:package-variant" class="text-sm"></iconify-icon>
                Kelola Produk →
            </a>
        </div>

        {{-- Info banner --}}
        <div class="px-4 sm:px-5 py-3 bg-purple-50 border-b border-purple-100 flex items-start gap-2">
            <iconify-icon icon="mdi:information-outline" class="text-purple-600 text-base mt-0.5 flex-shrink-0"></iconify-icon>
            <div class="text-[10px] sm:text-xs text-purple-800">
                Data diambil otomatis dari <strong>Master Produk</strong> (stok & HPP).
            </div>
        </div>

        @if($barangList->isEmpty())
            <div class="py-10 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <div class="mb-2 text-sm">Belum ada produk aktif dengan stok.</div>
                <a href="{{ route('products.index') }}"
                   class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-700 text-sm font-medium">
                    <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon>
                    Tambah produk di Master Produk
                </a>
            </div>
        @else
            {{-- TABEL BARANG (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-16">Gambar</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">SKU</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Nama Produk</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-20">Qty/Stok</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">HPP</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Zakat (2,5%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($barangList as $b)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    @if($b['gambar_url'])
                                        <img src="{{ $b['gambar_url'] }}" class="w-10 h-10 object-cover rounded-lg border border-gray-200">
                                    @else
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                            <iconify-icon icon="mdi:image-off-outline" class="text-base"></iconify-icon>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $b['sku_internal'] }}</span>
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-800">
                                    <div class="max-w-[250px] md:max-w-[350px] truncate" title="{{ $b['nama_produk'] }}">
                                        {{ $b['nama_produk'] }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($b['qty'] > 0)
                                        <span class="font-semibold text-gray-800">{{ $b['qty'] }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">0</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-gray-700">Rp {{ number_format($b['harga_satuan'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($b['total'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-purple-700">
                                    Rp {{ number_format($b['zakat'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-purple-50 border-t-2 border-purple-200">
                        <tr>
                            <td colspan="5" class="py-3 px-4 font-bold text-gray-800 text-right">TOTAL NILAI BARANG</td>
                            <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalBarang, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr class="bg-purple-100">
                            <td colspan="6" class="py-3 px-4 font-bold text-purple-900 text-right">ZAKAT BARANG (2,5%)</td>
                            <td class="py-3 px-4 text-right font-bold text-purple-900 text-base">Rp {{ number_format($zakatBarang, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- LIST BARANG (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($barangList as $b)
                    <div class="p-4 flex gap-3">
                        {{-- Gambar --}}
                        <div class="flex-shrink-0">
                            @if($b['gambar_url'])
                                <img src="{{ $b['gambar_url'] }}" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            @else
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                    <iconify-icon icon="mdi:image-off-outline" class="text-lg"></iconify-icon>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Detail --}}
                        <div class="flex-1 min-w-0 text-xs space-y-1">
                            <div class="font-bold text-gray-800 text-sm truncate" title="{{ $b['nama_produk'] }}">
                                {{ $b['nama_produk'] }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">{{ $b['sku_internal'] }}</span>
                                <span class="text-gray-400">|</span>
                                <span class="text-gray-600">Stok: <strong class="text-gray-800">{{ $b['qty'] }}</strong></span>
                            </div>
                            <div class="flex justify-between items-center pt-1 text-[11px]">
                                <span class="text-gray-500">HPP: Rp {{ number_format($b['harga_satuan'], 0, ',', '.') }}</span>
                                <span class="font-bold text-gray-800">Total: Rp {{ number_format($b['total'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-1 pt-1 border-t border-gray-50">
                                <span class="text-gray-500">Zakat (2,5%):</span>
                                <span class="font-bold text-purple-700">Rp {{ number_format($b['zakat'], 0, ',', '.') }}</span>
                            </div>
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

    {{-- CARD 4: TOTAL ZAKAT --}}
    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl shadow-md p-4 sm:p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-14 sm:h-14 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:hand-heart" class="text-xl sm:text-3xl text-white"></iconify-icon>
                </div>
                <div>
                    <div class="text-emerald-100 text-xs sm:text-sm font-medium">Total Zakat (Saldo + Piutang + Barang)</div>
                    <div class="text-2xl sm:text-3xl font-bold mt-1">Rp {{ number_format($totalZakat, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="text-left md:text-right text-xs sm:text-sm text-emerald-100 bg-white/10 md:bg-transparent p-3 md:p-0 rounded-lg">
                <div>Zakat Saldo: <strong>Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</strong></div>
                <div>Zakat Piutang: <strong>Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</strong></div>
                <div>Zakat Barang: <strong>Rp {{ number_format($zakatBarang, 0, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>

</div>
@endsection