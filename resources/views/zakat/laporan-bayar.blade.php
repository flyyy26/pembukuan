@extends('layouts.app')

@section('title', 'Laporan Bayar Zakat')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:cash-check" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Riwayat Bayar Zakat</h2>
                <p class="text-xs sm:text-sm text-gray-500">Arsip pembayaran zakat yang telah disimpan</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <a href="{{ route('zakat.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white border border-emerald-600 text-emerald-700 px-4 py-2 rounded-lg hover:bg-emerald-50 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:arrow-left" class="text-lg"></iconify-icon> Kembali ke Halaman Zakat
            </a>
            @if($pembayaranList->isNotEmpty())
                <button type="button" onclick="openHapusModal()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
                    <iconify-icon icon="mdi:delete" class="text-lg"></iconify-icon> Hapus Laporan
                </button>
            @endif
        </div>
    </div>

    {{-- FILTER TAHUN --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-medium text-gray-600 mb-1">Filter Tahun</label>
                <select name="tahun" onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $tahunFilter === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $tahunFilter == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto mt-2 sm:mt-0 flex gap-2">
                <button type="submit"
                        class="flex-1 sm:flex-none bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($tahunFilter !== 'all')
                    <a href="{{ route('laporan-zakat.index') }}"
                       class="flex-1 sm:flex-none bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center justify-center gap-2 border border-gray-200">
                        <iconify-icon icon="mdi:close-circle" class="text-lg"></iconify-icon> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- LOOPING DATA PEMBAYARAN --}}
    @if($pembayaranList->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-12 text-center text-gray-500">
            <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
            <span class="text-sm">Belum ada riwayat pembayaran zakat.</span>
        </div>
    @else
        <div class="space-y-6">
            @foreach($pembayaranList as $p)
                @php
                    $detail = is_array($p->detail_breakdown) ? $p->detail_breakdown : [];

                    // Ekstrak data dari JSON
                    $breakdownSaldo    = $detail['saldo']         ?? [];
                    $saldoOnlineList   = $detail['saldo_online']  ?? [];
                    $piutangList       = $detail['piutang']       ?? [];
                    $barangList        = $detail['barang']        ?? [];
                    $tabunganList      = $detail['tabungan']      ?? [];
                    $hutangList        = $detail['hutang']        ?? [];

                    // Nilai zakat per kategori
                    $zakatSaldo        = (int) ($detail['zakat_saldo']        ?? 0);
                    $zakatSaldoOnline  = (int) ($detail['zakat_saldo_online'] ?? 0);
                    $zakatPiutang      = (int) ($detail['zakat_piutang']      ?? 0);
                    $zakatBarang       = (int) ($detail['zakat_barang']       ?? 0);
                    $zakatHutang       = (int) ($detail['zakat_hutang']       ?? 0);
                    $zakatTabungan     = (int) ($detail['zakat_tabungan']     ?? 0);

                    // Total (prioritas JSON, fallback ke kolom model)
                    $totalSaldo    = (int) ($detail['total_saldo']    ?? $p->total_saldo    ?? 0);
                    $totalPiutang  = (int) ($detail['total_piutang']  ?? $p->total_piutang  ?? 0);
                    $totalBarang   = (int) ($detail['total_barang']   ?? $p->total_barang   ?? 0);
                    $totalHutang   = (int) ($detail['total_hutang']   ?? $p->total_hutang   ?? 0);
                    $totalTabungan = (int) ($detail['total_tabungan'] ?? $p->total_tabungan ?? 0);
                    $totalZakat    = (int) ($detail['total_zakat']    ?? $p->total_zakat    ?? 0);

                    $totalHarta    = (int) ($detail['total_harta']    ?? 0);
                    $hartaBersih   = (int) ($detail['harta_bersih']   ?? 0);

                    // Turunan
                    $totalSaldoOnline = (int) collect($saldoOnlineList)->sum('saldo');
                    $totalZakatAset   = $zakatSaldo + $zakatSaldoOnline + $zakatPiutang + $zakatBarang;
                    $totalZakatKewajiban = $zakatHutang + $zakatTabungan;

                    // Cek apakah data corrupt (semua kosong)
                    $isCorrupt = empty($breakdownSaldo) && empty($saldoOnlineList)
                              && empty($piutangList) && empty($barangList)
                              && empty($tabunganList) && empty($hutangList);
                @endphp

                {{-- CARD PEMBUNGKUS PER TANGGAL --}}
                <div class="bg-gray-50/60 border border-gray-200 rounded-xl p-3 sm:p-4">

                    {{-- Info Pembayaran + Tombol Hapus per-baris --}}
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 sm:p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-sm mb-3 shadow-sm">
                        <div>
                            <div class="font-bold text-emerald-800 text-sm sm:text-base">
                                Dibayar pada: {{ optional($p->tanggal)->translatedFormat('d F Y') ?? '-' }}
                                @if($p->keterangan)
                                    <span class="font-normal text-emerald-600 ml-2 text-xs">({{ $p->keterangan }})</span>
                                @endif
                            </div>
                            <div class="text-xs text-emerald-700 mt-1">
                                Total: <strong>Rp {{ number_format($totalZakat, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <button type="button"
                                onclick="hapusLaporan({{ $p->id }}, '{{ optional($p->tanggal)->translatedFormat('d F Y') ?? '-' }}')"
                                class="inline-flex items-center gap-1 bg-white border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus Laporan Ini
                        </button>
                    </div>

                    @if($isCorrupt)
                        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg text-sm">
                            <strong>Peringatan:</strong> Data pembayaran ini tidak memiliki rincian (JSON kosong).
                            Kemungkinan terjadi kesalahan sistem atau double submit. Disarankan untuk menghapus data ini.
                        </div>
                    @else

                        {{-- ============ CARD 1: ZAKAT SALDO ============ --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-3">
                            <button type="button" onclick="toggleCard('saldo-{{ $p->id }}')"
                                    class="w-full px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-blue-50 to-white hover:from-blue-100 transition text-left">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <iconify-icon icon="mdi:wallet" class="text-blue-600 text-lg"></iconify-icon>
                                    <h3 class="font-semibold text-gray-800 text-sm">Zakat dari Saldo</h3>
                                    <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded">
                                        Rp {{ number_format($zakatSaldo + $zakatSaldoOnline, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="label-saldo-{{ $p->id }}" class="text-xs text-blue-600 font-medium">Tampilkan</span>
                                    <iconify-icon id="chevron-saldo-{{ $p->id }}" icon="mdi:chevron-down"
                                                  class="text-blue-600 text-lg transition-transform duration-300"></iconify-icon>
                                </div>
                            </button>

                            <div id="content-saldo-{{ $p->id }}" class="hidden">
                                <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 bg-blue-50/30">
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Total Saldo Aset Likuid</div>
                                        <div class="text-base sm:text-lg font-bold text-gray-800">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</div>
                                        <div class="text-[9px] text-gray-400 mt-1">Termasuk Saldo Online: Rp {{ number_format($totalSaldoOnline, 0, ',', '.') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Rate Zakat</div>
                                        <div class="text-base sm:text-lg font-bold text-gray-800">2,5%</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Zakat Saldo</div>
                                        <div class="text-base sm:text-lg font-bold text-blue-700">Rp {{ number_format($zakatSaldo + $zakatSaldoOnline, 0, ',', '.') }}</div>
                                    </div>
                                </div>

                                @if(!empty($breakdownSaldo) || !empty($saldoOnlineList))
                                    <div class="hidden md:block overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead class="bg-gray-50 border-b">
                                                <tr>
                                                    <th class="text-left py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Rekening</th>
                                                    <th class="text-left py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori</th>
                                                    <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Saldo</th>
                                                    <th class="text-right py-2 px-4 font-semibold text-gray-500 uppercase whitespace-nowrap">Zakat (2,5%)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($breakdownSaldo as $b)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="py-2 px-4 font-medium text-gray-800">{{ $b['nama_rekening'] ?? '-' }}</td>
                                                        <td class="py-2 px-4">
                                                            @if(!empty($b['keterangan']) && $b['keterangan'] !== '-')
                                                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $b['keterangan'] }}</span>
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 px-4 text-right text-gray-700">Rp {{ number_format((int)($b['saldo'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="py-2 px-4 text-right font-semibold text-blue-700">Rp {{ number_format((int)($b['zakat'] ?? 0), 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                                @foreach($saldoOnlineList as $so)
                                                    @php
                                                        $namaMp  = is_array($so) ? ($so['marketplace'] ?? '-') : ($so->marketplace ?? '-');
                                                        $saldoMp = is_array($so) ? (int)($so['saldo'] ?? 0)   : (int)($so->saldo ?? 0);
                                                    @endphp
                                                    <tr class="hover:bg-gray-50 bg-blue-50/20">
                                                        <td class="py-2 px-4 font-medium text-gray-800">{{ $namaMp }}</td>
                                                        <td class="py-2 px-4">
                                                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px]">Marketplace</span>
                                                        </td>
                                                        <td class="py-2 px-4 text-right text-gray-700">Rp {{ number_format($saldoMp, 0, ',', '.') }}</td>
                                                        <td class="py-2 px-4 text-right font-semibold text-blue-700">Rp {{ number_format((int) round($saldoMp * 0.025), 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-blue-50 border-t-2 border-blue-200">
                                                <tr>
                                                    <td colspan="2" class="py-2 px-4 font-bold text-gray-800">TOTAL</td>
                                                    <td class="py-2 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td>
                                                    <td class="py-2 px-4 text-right font-bold text-blue-800">Rp {{ number_format($zakatSaldo + $zakatSaldoOnline, 0, ',', '.') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="md:hidden divide-y divide-gray-100">
                                        @foreach($breakdownSaldo as $b)
                                            <div class="p-3 space-y-1 text-xs">
                                                <div class="flex justify-between items-start gap-2">
                                                    <span class="font-bold text-gray-800 text-sm">{{ $b['nama_rekening'] ?? '-' }}</span>
                                                    @if(!empty($b['keterangan']) && $b['keterangan'] !== '-')
                                                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-[10px]">{{ $b['keterangan'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-gray-500">Saldo:</span>
                                                    <span class="font-medium text-gray-700">Rp {{ number_format((int)($b['saldo'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Zakat:</span>
                                                    <span class="font-bold text-blue-700">Rp {{ number_format((int)($b['zakat'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                        @foreach($saldoOnlineList as $so)
                                            @php
                                                $namaMp  = is_array($so) ? ($so['marketplace'] ?? '-') : ($so->marketplace ?? '-');
                                                $saldoMp = is_array($so) ? (int)($so['saldo'] ?? 0)   : (int)($so->saldo ?? 0);
                                            @endphp
                                            <div class="p-3 space-y-1 text-xs bg-blue-50/20">
                                                <div class="flex justify-between items-start gap-2">
                                                    <span class="font-bold text-gray-800 text-sm">{{ $namaMp }}</span>
                                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px]">Marketplace</span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="text-gray-500">Saldo:</span>
                                                    <span class="font-medium text-gray-700">Rp {{ number_format($saldoMp, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Zakat:</span>
                                                    <span class="font-bold text-blue-700">Rp {{ number_format((int) round($saldoMp * 0.025), 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="p-3 bg-blue-50 flex justify-between items-center font-bold text-xs">
                                            <span class="text-gray-800">TOTAL ZAKAT SALDO</span>
                                            <span class="text-blue-800 text-sm">Rp {{ number_format($zakatSaldo + $zakatSaldoOnline, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="py-6 text-center text-gray-400 text-xs">Tidak ada data saldo pada snapshot ini.</div>
                                @endif
                            </div>
                        </div>

                        {{-- ============ CARD 2: ZAKAT PIUTANG ============ --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-3">
                            <button type="button" onclick="toggleCard('piutang-{{ $p->id }}')"
                                    class="w-full px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-amber-50 to-white hover:from-amber-100 transition text-left">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <iconify-icon icon="mdi:account-cash" class="text-amber-600 text-lg"></iconify-icon>
                                    <h3 class="font-semibold text-gray-800 text-sm">Zakat dari Piutang</h3>
                                    <span class="text-xs bg-amber-100 text-amber-700 font-semibold px-2 py-0.5 rounded">
                                        Rp {{ number_format($zakatPiutang, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="label-piutang-{{ $p->id }}" class="text-xs text-amber-600 font-medium">Tampilkan</span>
                                    <iconify-icon id="chevron-piutang-{{ $p->id }}" icon="mdi:chevron-down"
                                                  class="text-amber-600 text-lg transition-transform duration-300"></iconify-icon>
                                </div>
                            </button>

                            <div id="content-piutang-{{ $p->id }}" class="hidden">
                                <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 bg-amber-50/30">
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Total Sisa Piutang</div>
                                        <div class="text-base sm:text-lg font-bold text-gray-800">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Rate Zakat</div>
                                        <div class="text-base sm:text-lg font-bold text-gray-800">2,5%</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Zakat Piutang</div>
                                        <div class="text-base sm:text-lg font-bold text-amber-700">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</div>
                                    </div>
                                </div>

                                @if(!empty($piutangList))
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
                                                @foreach($piutangList as $piutang)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="py-2 px-4 text-gray-800">{{ $piutang['keterangan'] ?? '-' }}</td>
                                                        <td class="py-2 px-4 text-right text-gray-700">Rp {{ number_format((int)($piutang['nominal'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="py-2 px-4 text-right text-emerald-600">Rp {{ number_format((int)($piutang['terbayar'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="py-2 px-4 text-right font-semibold text-gray-800">Rp {{ number_format((int)($piutang['sisa'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="py-2 px-4 text-right font-semibold text-amber-700">Rp {{ number_format((int)($piutang['zakat'] ?? 0), 0, ',', '.') }}</td>
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

                                    <div class="md:hidden divide-y divide-gray-100">
                                        @foreach($piutangList as $piutang)
                                            <div class="p-3 space-y-1 text-xs">
                                                <div class="font-bold text-gray-800 text-sm mb-2">{{ $piutang['keterangan'] ?? '-' }}</div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Piutang:</span>
                                                    <span class="text-gray-700">Rp {{ number_format((int)($piutang['nominal'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Terbayar:</span>
                                                    <span class="text-emerald-600">Rp {{ number_format((int)($piutang['terbayar'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Sisa Tagihan:</span>
                                                    <span class="font-bold text-gray-800">Rp {{ number_format((int)($piutang['sisa'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1 border-t border-gray-50 mt-1">
                                                    <span class="text-gray-500">Zakat (2,5%):</span>
                                                    <span class="font-bold text-amber-700">Rp {{ number_format((int)($piutang['zakat'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="p-3 bg-amber-50 flex justify-between items-center font-bold text-xs">
                                            <span class="text-gray-800">TOTAL ZAKAT PIUTANG</span>
                                            <span class="text-amber-800 text-sm">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="py-8 text-center text-gray-500 text-sm">
                                        <iconify-icon icon="mdi:check-circle-outline" class="text-3xl text-gray-300 block m-auto mb-1 w-max"></iconify-icon>
                                        Tidak ada piutang pada snapshot ini.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ============ CARD 3: ZAKAT BARANG ONLINE ============ --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-3">
                            <button type="button" onclick="toggleCard('barang-{{ $p->id }}')"
                                    class="w-full px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-purple-50 to-white hover:from-purple-100 transition text-left">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <iconify-icon icon="mdi:package-variant-closed" class="text-purple-600 text-lg"></iconify-icon>
                                    <h3 class="font-semibold text-gray-800 text-sm">Zakat dari Barang Online</h3>
                                    <span class="text-xs bg-purple-100 text-purple-700 font-semibold px-2 py-0.5 rounded">
                                        Rp {{ number_format($zakatBarang, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="label-barang-{{ $p->id }}" class="text-xs text-purple-600 font-medium">Tampilkan</span>
                                    <iconify-icon id="chevron-barang-{{ $p->id }}" icon="mdi:chevron-down"
                                                  class="text-purple-600 text-lg transition-transform duration-300"></iconify-icon>
                                </div>
                            </button>

                            <div id="content-barang-{{ $p->id }}" class="hidden">
                                @if(!empty($barangList))
                                    <div class="hidden md:block overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-50 border-b">
                                                <tr>
                                                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Nama Barang</th>
                                                    <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-20">Qty</th>
                                                    <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">HPP</th>
                                                    <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Total</th>
                                                    <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Zakat (2,5%)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($barangList as $b)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="py-3 px-4 font-medium text-gray-800">{{ $b['nama_barang'] ?? '-' }}</td>
                                                        <td class="py-3 px-4 text-center font-semibold text-gray-800">{{ $b['qty'] ?? 0 }}</td>
                                                        <td class="py-3 px-4 text-right text-gray-700">Rp {{ number_format((int)($b['harga_satuan'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format((int)($b['total'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="py-3 px-4 text-right font-semibold text-purple-700">Rp {{ number_format((int)($b['zakat'] ?? 0), 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-purple-50 border-t-2 border-purple-200">
                                                <tr>
                                                    <td colspan="3" class="py-3 px-4 font-bold text-gray-800 text-right">TOTAL NILAI BARANG</td>
                                                    <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($totalBarang, 0, ',', '.') }}</td>
                                                    <td></td>
                                                </tr>
                                                <tr class="bg-purple-100">
                                                    <td colspan="4" class="py-3 px-4 font-bold text-purple-900 text-right">ZAKAT BARANG (2,5%)</td>
                                                    <td class="py-3 px-4 text-right font-bold text-purple-900 text-base">Rp {{ number_format($zakatBarang, 0, ',', '.') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="md:hidden divide-y divide-gray-100">
                                        @foreach($barangList as $b)
                                            <div class="p-3 space-y-1 text-xs">
                                                <div class="font-bold text-gray-800 text-sm mb-2">{{ $b['nama_barang'] ?? '-' }}</div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Qty:</span>
                                                    <span class="font-semibold text-gray-800">{{ $b['qty'] ?? 0 }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">HPP:</span>
                                                    <span class="text-gray-700">Rp {{ number_format((int)($b['harga_satuan'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Total:</span>
                                                    <span class="font-bold text-gray-800">Rp {{ number_format((int)($b['total'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center pt-1 border-t border-gray-50 mt-1">
                                                    <span class="text-gray-500">Zakat (2,5%):</span>
                                                    <span class="font-bold text-purple-700">Rp {{ number_format((int)($b['zakat'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="p-3 bg-purple-50 space-y-1 text-xs">
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
                                @else
                                    <div class="py-8 text-center text-gray-500 text-sm">
                                        <iconify-icon icon="mdi:inbox-outline" class="text-3xl text-gray-300 block m-auto mb-1 w-max"></iconify-icon>
                                        Tidak ada barang online pada snapshot ini.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ============ CARD 4: KEWAJIBAN HUTANG ============ --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-3">
                            <button type="button" onclick="toggleCard('hutang-{{ $p->id }}')"
                                    class="w-full px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-red-50 to-white hover:from-red-100 transition text-left">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <iconify-icon icon="mdi:file-document-outline" class="text-red-600 text-lg"></iconify-icon>
                                    <h3 class="font-semibold text-gray-800 text-sm">Kewajiban: Hutang</h3>
                                    <span class="text-xs bg-red-100 text-red-700 font-semibold px-2 py-0.5 rounded">
                                        - Rp {{ number_format($totalHutang, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="label-hutang-{{ $p->id }}" class="text-xs text-red-600 font-medium">Tampilkan</span>
                                    <iconify-icon id="chevron-hutang-{{ $p->id }}" icon="mdi:chevron-down"
                                                  class="text-red-600 text-lg transition-transform duration-300"></iconify-icon>
                                </div>
                            </button>

                            <div id="content-hutang-{{ $p->id }}" class="hidden">
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
                                                @php
                                                    $namaHutang    = is_array($h) ? ($h['nama_pemberi_hutang'] ?? '-') : ($h->nama_pemberi_hutang ?? '-');
                                                    $nominalHutang = is_array($h) ? (int)($h['nominal'] ?? 0)         : (int)($h->nominal ?? 0);
                                                    $tanggalHutang = is_array($h) ? ($h['tanggal'] ?? null)           : ($h->tanggal ?? null);
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2 px-4 text-gray-800">
                                                        {{ $namaHutang }}
                                                        @if($tanggalHutang)
                                                            <div class="text-[10px] text-gray-400">
                                                                {{ \Carbon\Carbon::parse($tanggalHutang)->format('d/m/Y') }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-4 text-right text-red-600 font-semibold">Rp {{ number_format($nominalHutang, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="py-4 text-center text-gray-500">Tidak ada hutang.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- ============ CARD 5: KEWAJIBAN TABUNGAN ============ --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-3">
                            <button type="button" onclick="toggleCard('tabungan-{{ $p->id }}')"
                                    class="w-full px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-orange-50 to-white hover:from-orange-100 transition text-left">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <iconify-icon icon="mdi:piggy-bank" class="text-orange-600 text-lg"></iconify-icon>
                                    <h3 class="font-semibold text-gray-800 text-sm">Kewajiban: Tabungan</h3>
                                    <span class="text-xs bg-orange-100 text-orange-700 font-semibold px-2 py-0.5 rounded">
                                        - Rp {{ number_format($totalTabungan, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="label-tabungan-{{ $p->id }}" class="text-xs text-orange-600 font-medium">Tampilkan</span>
                                    <iconify-icon id="chevron-tabungan-{{ $p->id }}" icon="mdi:chevron-down"
                                                  class="text-orange-600 text-lg transition-transform duration-300"></iconify-icon>
                                </div>
                            </button>

                            <div id="content-tabungan-{{ $p->id }}" class="hidden">
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
                                                    <td class="py-2 px-4 text-gray-800 font-medium">{{ $t['nama'] ?? '-' }}</td>
                                                    <td class="py-2 px-4 text-right text-orange-600 font-semibold">Rp {{ number_format((int)($t['saldo'] ?? 0), 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="py-4 text-center text-gray-500">Tidak ada saldo tabungan.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- ============ CARD 6: TOTAL ZAKAT (SELALU TERBUKA) ============ --}}
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl shadow-md overflow-hidden">
                            <div class="flex flex-col md:flex-row">
                                <div class="p-5 md:w-1/3 flex flex-col justify-center items-center md:items-start border-b md:border-b-0 md:border-r border-white/20 text-white">
                                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                                        <iconify-icon icon="mdi:hand-heart" class="text-2xl text-white"></iconify-icon>
                                    </div>
                                    <div class="text-emerald-100 text-xs font-medium text-center md:text-left">Total Zakat (2,5%)</div>
                                    <div class="text-2xl sm:text-3xl font-bold mt-1">Rp {{ number_format($totalZakat, 0, ',', '.') }}</div>
                                    <div class="text-[10px] text-emerald-100 mt-1 text-center md:text-left">*Dihitung dari Harta Bersih</div>
                                </div>
                                <div class="p-4 md:w-2/3 bg-white/5 text-white">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                                        <div class="space-y-1.5">
                                            <h4 class="text-[10px] font-bold text-emerald-200 uppercase tracking-wider mb-1 border-b border-white/20 pb-1">Nilai Harta (Aset)</h4>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-emerald-50">1. Saldo & Online</span>
                                                <span class="font-semibold">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-emerald-50">2. Piutang</span>
                                                <span class="font-semibold">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-emerald-50">3. Barang Online</span>
                                                <span class="font-semibold">Rp {{ number_format($totalBarang, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-xs font-bold text-white">
                                                <span>Total Harta</span>
                                                <span>Rp {{ number_format($totalHarta, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs text-red-200 mt-1">
                                                <span>(-) Hutang</span>
                                                <span>Rp {{ number_format($totalHutang, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs text-red-200">
                                                <span>(-) Tabungan</span>
                                                <span>Rp {{ number_format($totalTabungan, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-xs font-bold text-yellow-300">
                                                <span>Harta Bersih</span>
                                                <span>Rp {{ number_format($hartaBersih, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-1.5">
                                            <h4 class="text-[10px] font-bold text-emerald-200 uppercase tracking-wider mb-1 border-b border-white/20 pb-1">Nilai Zakat (2,5%)</h4>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-emerald-50">Zakat Saldo</span>
                                                <span class="font-semibold">Rp {{ number_format($zakatSaldo + $zakatSaldoOnline, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-emerald-50">Zakat Piutang</span>
                                                <span class="font-semibold">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-emerald-50">Zakat Barang</span>
                                                <span class="font-semibold">Rp {{ number_format($zakatBarang, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-xs font-bold text-white">
                                                <span>Subtotal Zakat Aset</span>
                                                <span>Rp {{ number_format($totalZakatAset, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs text-red-200 mt-1">
                                                <span>(-) Zakat Hutang</span>
                                                <span>Rp {{ number_format($zakatHutang, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs text-red-200">
                                                <span>(-) Zakat Tabungan</span>
                                                <span>Rp {{ number_format($zakatTabungan, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-white/20 text-xs font-bold text-yellow-300">
                                                <span>Total Zakat Akhir</span>
                                                <span>Rp {{ number_format($totalZakat, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif {{-- end isCorrupt --}}
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
<div id="hapusModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeHapusModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-red-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:alert" class="text-red-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Hapus Laporan Zakat</h3>
            </div>
            <button type="button" onclick="closeHapusModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded text-sm">
                <strong>Peringatan:</strong> Data yang dihapus tidak bisa dikembalikan.
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Laporan yang Ingin Dihapus</label>
                <select id="hapusSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                    @foreach($pembayaranList as $p)
                        <option value="{{ $p->id }}">
                            {{ optional($p->tanggal)->translatedFormat('d F Y') }} — Rp {{ number_format((int) $p->total_zakat, 0, ',', '.') }}
                            @if($p->keterangan) ({{ $p->keterangan }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
            <button type="button" onclick="closeHapusModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
            <button type="button" onclick="hapusLaporanSelected()" id="btnHapusModal"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm font-medium flex items-center gap-2">
                <iconify-icon icon="mdi:delete" class="text-lg"></iconify-icon> Hapus Sekarang
            </button>
        </div>
    </div>
</div>

<script>
    const CSRF_ZAKAT_HAPUS = document.querySelector('meta[name="csrf-token"]').content;

    // ============ ACCORDION / DROPDOWN CARD ============
    function toggleCard(id) {
        const content = document.getElementById('content-' + id);
        const chevron = document.getElementById('chevron-' + id);
        const label   = document.getElementById('label-' + id);

        if (!content) return;

        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
            if (label)   label.textContent = 'Sembunyikan';
        } else {
            content.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            if (label)   label.textContent = 'Tampilkan';
        }
    }

    // ============ MODAL HAPUS ============
    function openHapusModal() {
        document.getElementById('hapusModal').classList.remove('hidden');
        document.getElementById('hapusModal').classList.add('flex');
    }
    function closeHapusModal() {
        document.getElementById('hapusModal').classList.add('hidden');
        document.getElementById('hapusModal').classList.remove('flex');
    }

    // ============ HAPUS VIA MODAL ============
    async function hapusLaporanSelected() {
        const id = document.getElementById('hapusSelect').value;
        if (!id) return;

        const btn = document.getElementById('btnHapusModal');
        btn.disabled = true;
        btn.innerHTML = 'Menghapus...';

        await hapusLaporanAjax(id);
    }

    // ============ HAPUS PER-BARIS ============
    function hapusLaporan(id, tanggal) {
        if (!confirm(`Yakin ingin menghapus laporan pembayaran tanggal ${tanggal}?\n\nData yang dihapus tidak bisa dikembalikan.`)) return;
        hapusLaporanAjax(id);
    }

    // ============ FUNGSI AJAX HAPUS ============
    async function hapusLaporanAjax(id) {
        try {
            const res = await fetch(`/laporan-bayar-zakat/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_ZAKAT_HAPUS,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal menghapus.');
            }

            alert(data.message);
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
            const btn = document.getElementById('btnHapusModal');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="mdi:delete" class="text-lg"></iconify-icon> Hapus Sekarang';
            }
        }
    }
</script>
@endsection