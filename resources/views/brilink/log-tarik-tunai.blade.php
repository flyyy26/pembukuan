@extends('layouts.app')

@section('title', 'Log Tarik Tunai BRILink')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-teal-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:cash-fast" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Log Tarik Tunai</h2>
                <p class="text-xs sm:text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }}</p>
            </div>
        </div>
        
        {{-- 🔥 TOMBOL SELALU MUNCUL --}}
        <button type="button" onclick="openTarikTunaiModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Tarik Tunai
        </button>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Nominal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari file atau nominal (cth: 500.000)..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-teal-600 focus:outline-none">
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-600 focus:outline-none">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-600 focus:outline-none">
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-teal-600 text-white px-5 py-2 rounded-lg hover:bg-teal-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $from || $to || $storeId !== 'all')
                    <a href="{{ route('brilink.log-tarik-tunai') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- LAYOUT UTAMA: KIRI TABEL, KANAN CARD VALIDASI --}}
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start">
        
        {{-- ========================================== --}}
        {{-- KIRI: TABEL LOG TRANSAKSI (3/4 layar) --}}
        {{-- ========================================== --}}
        <div class="xl:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Rincian Tarik Tunai</h3>
            </div>
            
            <div class="overflow-x-auto overflow-y-auto max-h-[75vh] border-t border-gray-200">
                <table class="w-full text-xs border-collapse min-w-max">
                    <thead class="sticky top-0 z-20 shadow-md bg-gray-100">
                        {{-- Header Tingkat 1 --}}
                        <tr class="bg-gray-100 border-b border-gray-200 text-center">
                            <th class="p-2 border-r border-gray-200 bg-gray-100 whitespace-nowrap" rowspan="2">Tanggal</th>
                            <th class="p-2 border-r border-gray-200 bg-teal-50 text-teal-800 whitespace-nowrap" colspan="4">Tunai EDC BRI</th>
                            
                            {{-- Kolom Saldo Masuk Berdiri Sendiri --}}
                            <th class="p-2 border-r border-gray-200 bg-purple-50 text-purple-800 align-middle w-28 sm:w-32 whitespace-nowrap" rowspan="2">Saldo Masuk & Pindah</th>
                            
                            <th class="p-2 border-r border-gray-200 bg-yellow-50 text-yellow-800 whitespace-nowrap" colspan="2">Tarik Tunai MBanking</th>
                            <th class="p-2 bg-gray-100 whitespace-nowrap" rowspan="2">Rek/Bank</th>
                            <th class="p-2 bg-gray-100 whitespace-nowrap" rowspan="2">Keterangan</th>
                            @if($storeId !== 'all')
                                <th class="p-2 border-r border-gray-200 bg-gray-100 whitespace-nowrap" rowspan="2">Aksi</th>
                            @endif
                        </tr>
                        
                        {{-- Header Tingkat 2 --}}
                        <tr class="bg-gray-50 border-b border-gray-200 text-right">
                            {{-- Sub-kolom EDC --}}
                            <th class="p-2 border-r border-gray-200 bg-gray-50 whitespace-nowrap">Nominal</th>
                            <th class="p-2 border-r border-gray-200 bg-gray-50 whitespace-nowrap">Admin</th>
                            <th class="p-2 border-r border-gray-200 bg-gray-50 whitespace-nowrap">KET. MESIN EDC</th>
                            <th class="p-2 border-r border-gray-200 bg-gray-50 whitespace-nowrap">KMC</th>
                            
                            {{-- Sub-kolom MBanking --}}
                            <th class="p-2 border-r border-gray-200 bg-yellow-100/50 whitespace-nowrap">Nominal</th>
                            <th class="p-2 border-r border-gray-200 bg-yellow-100/50 whitespace-nowrap">Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reports as $report)
                            @php
                                $edcItems = $report->tarikTunaiItems->where('tipe', 'edc_bri');
                                $mbItems = $report->tarikTunaiItems->where('tipe', 'mbanking');
                                $saldoPindahItems = $report->saldoPindahItems;
                                $maxRows = max($edcItems->count(), $mbItems->count(), $saldoPindahItems->count());
                            @endphp

                            @if($maxRows > 0)
                                @for($i = 0; $i < $maxRows; $i++)
                                    @php
                                        $edc = $edcItems->values()[$i] ?? null;
                                        $mb = $mbItems->values()[$i] ?? null;
                                        $saldo = $saldoPindahItems->values()[$i] ?? null;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        {{-- Tanggal --}}
                                        <td class="p-2 border-r border-gray-200 font-medium text-gray-700 whitespace-nowrap text-xs sm:text-sm">
                                            {{ $report->periode_label ?? '-' }}
                                        </td>
                                        
                                        {{-- EDC BRI --}}
                                        <td class="p-2 border-r border-gray-200 text-right text-gray-800 font-semibold whitespace-nowrap">
                                            {{ $edc ? number_format($edc->nominal, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="p-2 border-r border-gray-200 text-right text-teal-600 font-semibold whitespace-nowrap">
                                            {{ $edc ? number_format($edc->admin_toko, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="p-2 border-r border-gray-200 text-left text-gray-700 text-xs whitespace-nowrap">
                                            {{ $edc ? $edc->ket_mesin_edc : '-' }}
                                        </td>
                                        <td class="p-2 border-r border-gray-200 text-right text-blue-600 font-semibold whitespace-nowrap">
                                            {{ $edc ? number_format($edc->kmc, 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        {{-- Saldo Masuk & Pindah --}}          {{-- Saldo Masuk & Pindah --}}
                                        <td class="p-2 border-r border-gray-200 text-right text-purple-700 font-semibold bg-purple-50/30 w-28 sm:w-32 whitespace-nowrap">
                                            {{ $saldo ? number_format($saldo->nominal, 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- MBanking --}}
                                        <td class="p-2 border-r border-gray-200 text-right text-gray-800 font-semibold bg-yellow-50/30 whitespace-nowrap">
                                            {{ $mb ? number_format($mb->nominal, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="p-2 border-r border-gray-200 text-right text-teal-600 font-semibold bg-yellow-50/30 whitespace-nowrap">
                                            {{ $mb ? number_format($mb->admin_toko, 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Rekening Bank --}}
                                        <td class="p-2 border-r border-gray-200 text-left text-gray-600 whitespace-nowrap">
                                            {{ $mb ? $mb->keterangan_rek : '-' }}
                                        </td>

                                        {{-- Keterangan Umum --}}
                                        <td class="p-2 border-r border-gray-200 text-left text-gray-600 text-xs">
                                            {{ $mb ? $mb->keterangan : ($edc ? $edc->keterangan : '-') }}
                                        </td>

                                        {{-- Kolom Aksi --}}
                                        @if($storeId !== 'all')
                                            <td class="p-2 text-center border-l border-gray-200">
                                                <div class="inline-flex items-center justify-center">
                                                    <button type="button"
                                                            onclick="openEditTarikModal(
                                                                event, 
                                                                {{ $edc?->id ?? 'null' }}, 
                                                                {{ $mb?->id ?? 'null' }}, 
                                                                {{ $edc?->nominal ?? 0 }}, 
                                                                {{ $edc?->admin_toko ?? 0 }}, 
                                                                '{{ addslashes($edc?->ket_mesin_edc ?? '') }}', 
                                                                {{ $edc?->kmc ?? 0 }}, 
                                                                {{ $mb?->nominal ?? 0 }}, 
                                                                {{ $mb?->admin_toko ?? 0 }}, 
                                                                0, 
                                                                {{ $saldo?->nominal ?? 0 }}, {{-- ✅ Gunakan nominal dari baris ini saja --}}
                                                                '{{ addslashes($mb?->keterangan_rek ?? '') }}', 
                                                                '{{ addslashes($mb?->keterangan ?? ($edc?->keterangan ?? '')) }}', 
                                                                {{ $report->id }},
                                                                '{{ $report->periode_start->format('Y-m-d') }}'
                                                            )"
                                                            class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-blue-200 bg-white"
                                                            title="Edit">
                                                        <iconify-icon icon="mdi:pencil" class="text-xs sm:text-sm"></iconify-icon>
                                                    </button>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endfor
                            @else
                                <tr>
                                    <td class="p-2 border-r border-gray-200 text-xs sm:text-sm whitespace-nowrap">{{ $report->periode_label }}</td>
                                    <td colspan="{{ $storeId === 'all' ? '9' : '10' }}" class="p-2 text-center text-gray-400 italic">Tidak ada transaksi</td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="{{ $storeId === 'all' ? '9' : '10' }}" class="p-8 text-center text-gray-500">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- KANAN: STICKY CARD VALIDASI (1/4 layar) --}}
        {{-- ========================================== --}}
        <div class="xl:col-span-1 xl:sticky xl:top-10 space-y-4">
            <div class="flex items-center gap-2 mb-2 px-1">
                <iconify-icon icon="mdi:cash-check" class="text-teal-600 text-xl"></iconify-icon>
                <h3 class="font-bold text-gray-800">Validasi Mutasi Fisik</h3>
            </div>

            @php
                if ($storeId === 'all') {
                    $groupedForCard = $reports->groupBy(fn($r) => $r->periode_start->format('Y-m-d'));
                } else {
                    $groupedForCard = $reports->groupBy('id');
                }
            @endphp

            @forelse($groupedForCard as $key => $reportsInGroup)
                @php
                    $firstReport = $reportsInGroup->first();
                    $allItems = $reportsInGroup->flatMap->tarikTunaiItems;
                    $edcItems = $allItems->where('tipe', 'edc_bri');
                    
                    $totalEdc = $edcItems->sum('nominal');
                    $totalKmc = $edcItems->sum('kmc');
                    $totalSaldoMasuk = $reportsInGroup->flatMap->saldoPindahItems->sum('nominal');
                    $akumulasiEdc = $totalEdc + $totalKmc + $totalSaldoMasuk;

                    $validasiFisik = $reportsInGroup->sum('manual_validasi_mutasi_fisik');
                    $selisihFisik = $akumulasiEdc - $validasiFisik;
                    
                    $isSingleStore = ($storeId !== 'all');
                @endphp
                
                <div class="bg-white rounded-xl shadow-sm border border-teal-100 overflow-hidden">
                    <div class="bg-teal-50 px-4 py-3 border-b border-teal-100 flex justify-between items-center">
                        <span class="font-bold text-teal-900 text-sm sm:text-base">{{ $firstReport->periode_label ?? '-' }}</span>
                        @if($storeId === 'all')
                            <span class="text-[10px] text-teal-600 bg-white px-2 py-0.5 rounded border">Total Semua Toko</span>
                        @endif
                    </div>

                    <div class="p-4 space-y-4">
                        {{-- Rincian EDC --}}
                        <div class="space-y-1 pb-3 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center text-xs gap-1">
                                <span class="text-gray-500">Total Tarik Tunai EDC</span>
                                <span class="font-semibold text-gray-700 text-sm sm:text-base">Rp {{ number_format($totalEdc, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center text-xs gap-1">
                                <span class="text-gray-500">Komisi KMC</span>
                                <span class="font-semibold text-blue-600">+ Rp {{ number_format($totalKmc, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center text-xs gap-1">
                                <span class="text-gray-500">Saldo Masuk</span>
                                <span class="font-semibold text-purple-600">+ Rp {{ number_format($totalSaldoMasuk, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Akumulasi --}}
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-2 gap-1">
                            <span class="text-sm font-bold text-gray-800">Akumulasi EDC BRI</span>
                            <span id="akumulasi-{{ $firstReport->id }}" data-value="{{ $akumulasiEdc }}" class="font-bold text-base text-teal-700">
                                Rp {{ number_format($akumulasiEdc, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Input Validasi Fisik --}}
                        <div class="pt-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Validasi Mutasi Fisik</label>
                            @if($isSingleStore)
                                <form onsubmit="submitValidasiFisik(event, {{ $firstReport->id }})" class="flex items-center gap-2">
                                    @csrf
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-xs font-medium">Rp</span>
                                        </div>
                                        <input type="text" name="manual_validasi_mutasi_fisik" 
                                            id="input-validasi-{{ $firstReport->id }}"
                                            value="{{ $validasiFisik > 0 ? number_format($validasiFisik, 0, ',', '.') : '' }}" 
                                            placeholder="0"
                                            class="w-full border border-teal-300 rounded-lg text-right pl-8 pr-2 py-1.5 text-sm font-bold text-teal-900 focus:ring-2 focus:ring-teal-500 focus:outline-none transition"
                                            oninput="formatRupiahInput(this); hitungSelisihFisik({{ $firstReport->id }})">
                                    </div>
                                    <button type="submit" class="bg-teal-600 text-white p-2 rounded-lg hover:bg-teal-700 transition shadow-sm" title="Simpan">
                                        <iconify-icon icon="mdi:content-save" class="text-base block"></iconify-icon>
                                    </button>
                                </form>
                            @else
                                <div class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-right font-bold text-gray-700">
                                    Rp {{ number_format($validasiFisik, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-orange-500 mt-1">
                                    * Pilih toko spesifik untuk mengubah validasi.
                                </div>
                            @endif
                        </div>

                        {{-- Selisih Rekonsiliasi --}}
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center pt-2 border-t border-gray-100 gap-1">
                            <span class="text-xs font-bold text-gray-600">Selisih Fisik</span>
                            <span id="selisih-{{ $firstReport->id }}" class="font-bold text-sm {{ $selisihFisik == 0 ? 'text-gray-500' : ($selisihFisik > 0 ? 'text-orange-600' : 'text-red-600') }}">
                                Rp {{ number_format($selisihFisik, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center text-gray-500">
                    Tidak ada report untuk direkonsiliasi.
                </div>
            @endforelse
            @php
                $allTarik = $reports->flatMap->tarikTunaiItems;
                
                $totalMb = $allTarik->where('tipe', 'mbanking')->sum('nominal');
                $totalTrx = $allTarik->count();
                
                $totalSaldoMasuk = $reports->flatMap->saldoPindahItems->sum('nominal'); 
                
                $adminEdc = $allTarik->where('tipe', 'edc_bri')->sum('admin_toko');
                $adminMb = $allTarik->where('tipe', 'mbanking')->sum('admin_toko');
                $totalKmc = $allTarik->where('tipe', 'edc_bri')->sum('kmc');
                
                $labaBersih = $adminEdc + $adminMb + $totalKmc;
            @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-6">
                <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                    <span class="font-bold text-white text-sm">Ringkasan Total</span>
                    <iconify-icon icon="mdi:chart-pie" class="text-gray-400 text-lg"></iconify-icon>
                </div>

                <div class="p-4 space-y-3 text-sm">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-2 border-b border-gray-100 gap-1">
                        <span class="text-gray-600">Total Tarik Tunai MBanking</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($totalMb, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-2 border-b border-gray-100 gap-1">
                        <span class="text-gray-600">Jumlah Transaksi Tarik</span>
                        <span class="font-semibold text-gray-800">{{ number_format($totalTrx) }} <span class="text-xs font-normal text-gray-500">Trx</span></span>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-2 border-b border-gray-100 gap-1">
                        <span class="text-gray-600">Jumlah Saldo Masuk</span>
                        <span class="font-semibold text-purple-600">Rp {{ number_format($totalSaldoMasuk, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-2 border-b border-gray-100 gap-1">
                        <span class="text-gray-600">Jumlah Admin EDC BRI</span>
                        <span class="font-semibold text-emerald-600">Rp {{ number_format($adminEdc, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-2 border-b border-gray-100 gap-1">
                        <span class="text-gray-600">Jumlah Admin MBanking</span>
                        <span class="font-semibold text-emerald-600">Rp {{ number_format($adminMb, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-2 border-b border-gray-100 gap-1">
                        <span class="text-gray-600">Jumlah Komisi KMC</span>
                        <span class="font-semibold text-blue-600">Rp {{ number_format($totalKmc, 0, ',', '.') }}</span>
                    </div>

                    {{-- Total Laba Admin Bersih --}}
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pt-3 mt-1 border-t-2 border-gray-800 gap-1">
                        <span class="font-bold text-gray-800">Total Laba Admin Bersih</span>
                        <span class="font-bold text-lg text-teal-700">Rp {{ number_format($labaBersih, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH TARIK TUNAI MANUAL --}}
<div id="tarikTunaiModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeTarikTunaiModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-slide-up">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:plus-circle" class="text-teal-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Tambah Tarik Tunai Manual</h3>
            </div>
            <button type="button" onclick="closeTarikTunaiModal()" class="text-gray-400 hover:text-gray-600 transition">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="tarikTunaiForm" onsubmit="submitTarikTunaiForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" name="store_id" id="tarikStoreId" value="{{ $storeId }}">
            <input type="hidden" name="report_id" id="editReportId" value="">
            <input type="hidden" name="edc_id" id="editEdcId" value="">
            <input type="hidden" name="mb_id" id="editMbId" value="">
            <input type="hidden" id="tarikMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorTarik"></div>

                <div id="tokoFieldWrapper" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Toko <span class="text-red-500">*</span></label>
                    <select id="tarikStoreSelect" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                        <option value="">-- Pilih Toko --</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi</label>
                        <input type="date" name="tanggal" id="tarikTanggal" value="{{ date('Y-m-d') }}" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-3">
                    <h4 class="text-sm font-bold text-teal-800 mb-2">Tunai EDC BRI</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nominal</label>
                            <input type="text" name="edc_nominal" id="inputEdcNominal" placeholder="0"
                                class="input-format-rupiah w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Admin</label>
                            <input type="text" name="edc_admin" id="inputEdcAdmin" placeholder="0"
                                class="input-format-rupiah w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">KET. MESIN EDC</label>
                            <input type="text" name="ket_mesin_edc" id="inputKetMesinEdc" placeholder="cth: EDC AA"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-3">
                    <h4 class="text-sm font-bold text-teal-800 mb-2">KMC</h4>
                    <input type="text" name="edc_kmc" id="inputEdcKmc" placeholder="0"
                                class="input-format-rupiah w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>

                <div class="border-t border-gray-100 pt-3">
                    <h4 class="text-sm font-bold text-yellow-800 mb-2">Tarik Tunai MBanking</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nominal</label>
                            <input type="text" name="mb_nominal" id="inputMbNominal" placeholder="0"
                                class="input-format-rupiah w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Admin</label>
                            <input type="text" name="mb_admin" id="inputMbAdmin" placeholder="0"
                                class="input-format-rupiah w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Rekening Bank</label>
                            <input type="text" name="keterangan_rek" id="tarikKeteranganRek" placeholder="cth: kmrn / bca HM"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                            <input type="text" name="keterangan" id="inputKeteranganUmum" placeholder="cth: KIRANG INDOMARCO"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-500 focus:outline-none">
                        </div>
                    </div>
                    
                </div>
                
                <div class="border-t border-gray-100 pt-3">
                    <h4 class="text-sm font-bold text-purple-800 mb-2">Saldo Masuk & Pindah</h4>
                    <div class="mb-3">
                        <input type="text" name="saldo_pindah" id="inputSaldoPindah" placeholder="0"
                            class="input-format-rupiah w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeTarikTunaiModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitTarik" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.input-format-rupiah').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        });
    });

    const CURRENT_STORE_ID = "{{ $storeId }}";

    function openTarikTunaiModal() {
        document.getElementById('tarikTunaiForm').reset();
        document.getElementById('modalErrorTarik').classList.add('hidden');
        
        document.getElementById('tarikMode').value = 'create';
        document.getElementById('editEdcId').value = '';
        document.getElementById('editMbId').value = '';
        document.getElementById('editReportId').value = '';
        
        document.getElementById('tarikTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('tarikTanggal').disabled = false;

        const tokoWrapper = document.getElementById('tokoFieldWrapper');
        const tokoSelect = document.getElementById('tarikStoreSelect');
        const storeIdHidden = document.getElementById('tarikStoreId');

        if (CURRENT_STORE_ID === 'all') {
            tokoWrapper.classList.remove('hidden');
            tokoSelect.required = true;
            storeIdHidden.value = '';
        } else {
            tokoWrapper.classList.add('hidden');
            tokoSelect.required = false;
            storeIdHidden.value = CURRENT_STORE_ID;
        }

        document.getElementById('tarikTunaiModal').classList.remove('hidden');
        document.getElementById('tarikTunaiModal').classList.add('flex');
    }

    function openEditTarikModal(event, edcId, mbId, edcNom, edcAdm, edcKetMesin, edcKmc, mbNom, mbAdm, mbSaldo, saldoPindah, bank, ket, reportId, reportTanggal) {
        document.getElementById('tarikTunaiForm').reset();
        document.getElementById('modalErrorTarik').classList.add('hidden');
        
        document.getElementById('tarikMode').value = 'edit';
        document.getElementById('editEdcId').value = edcId || '';
        document.getElementById('editMbId').value = mbId || '';
        document.getElementById('editReportId').value = reportId;
        
        // ✅ Isi tanggal dari parameter backend
        document.getElementById('tarikTanggal').value = reportTanggal; 
        
        // Kunci input tanggal karena ini mode edit
        document.getElementById('tarikTanggal').disabled = true; 

        document.getElementById('tokoFieldWrapper').classList.add('hidden');
        document.getElementById('tarikStoreSelect').required = false;

        // Isi form lainnya
        if (edcNom > 0) document.getElementById('inputEdcNominal').value = new Intl.NumberFormat('id-ID').format(edcNom);
        if (edcAdm > 0) document.getElementById('inputEdcAdmin').value = new Intl.NumberFormat('id-ID').format(edcAdm);
        document.getElementById('inputKetMesinEdc').value = edcKetMesin || '';
        if (edcKmc > 0) document.getElementById('inputEdcKmc').value = new Intl.NumberFormat('id-ID').format(edcKmc);

        if (mbNom > 0) document.getElementById('inputMbNominal').value = new Intl.NumberFormat('id-ID').format(mbNom);
        if (mbAdm > 0) document.getElementById('inputMbAdmin').value = new Intl.NumberFormat('id-ID').format(mbAdm);

        if (saldoPindah > 0) document.getElementById('inputSaldoPindah').value = new Intl.NumberFormat('id-ID').format(saldoPindah);
        document.getElementById('tarikKeteranganRek').value = bank || '';
        document.getElementById('inputKeteranganUmum').value = ket || '';

        document.getElementById('tarikTunaiModal').classList.remove('hidden');
        document.getElementById('tarikTunaiModal').classList.add('flex');
    }

    function closeTarikTunaiModal() {
        document.getElementById('tarikTunaiModal').classList.add('hidden');
        document.getElementById('tarikTunaiModal').classList.remove('flex');
    }

    function hitungSelisihFisik(reportId) {
        const akumulasiEl = document.getElementById(`akumulasi-${reportId}`);
        if (!akumulasiEl) return;
        const akumulasiEdc = parseInt(akumulasiEl.getAttribute('data-value')) || 0;

        const inputEl = document.getElementById(`input-validasi-${reportId}`);
        const validasiStr = inputEl.value.replace(/[^0-9]/g, '');
        const validasiFisik = parseInt(validasiStr) || 0;

        const selisih = akumulasiEdc - validasiFisik;

        const formatter = new Intl.NumberFormat('id-ID');
        const selisihFormatted = formatter.format(selisih);

        const selisihEl = document.getElementById(`selisih-${reportId}`);
        if (selisihEl) {
            selisihEl.textContent = `Rp ${selisihFormatted}`;

            selisihEl.classList.remove('text-gray-500', 'text-orange-600', 'text-red-600');
            if (selisih === 0) {
                selisihEl.classList.add('text-gray-500');
            } else if (selisih > 0) {
                selisihEl.classList.add('text-orange-600');
            } else {
                selisihEl.classList.add('text-red-600');
            }
        }
    }

    async function submitTarikTunaiForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitTarik');
        const errorDiv = document.getElementById('modalErrorTarik');
        const mode = document.getElementById('tarikMode').value;

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errorDiv.classList.add('hidden');

        const formData = new FormData(form);

        // ✅ Bersihkan format rupiah (hilangkan titik) sebelum dikirim ke server
        // Jika kosong, set menjadi string kosong agar controller menganggapnya null/0
        const fieldsToClean = [
            'edc_nominal', 'edc_admin', 'edc_kmc', 
            'mb_nominal', 'mb_admin', 
            'saldo_pindah'
        ];

        fieldsToClean.forEach(field => {
            let val = formData.get(field);
            if (val) {
                let cleanVal = val.replace(/[^0-9]/g, '');
                formData.set(field, cleanVal);
            } else {
                formData.set(field, '');
            }
        });

        // Handle store ID jika "Semua Toko"
        if (mode === 'create' && CURRENT_STORE_ID === 'all') {
            const selectedStore = document.getElementById('tarikStoreSelect').value;
            if (!selectedStore) {
                errorDiv.textContent = 'Silakan pilih toko terlebih dahulu.';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan';
                return;
            }
            formData.set('store_id', selectedStore);
        }
        
        let url = "{{ route('brilink.log-tarik-tunai.store-ajax') }}";
        let method = 'POST';

        if (mode === 'edit') {
            url = "{{ route('brilink.log-tarik-tunai.update-ajax') }}";
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            // Jika response status bukan 200/201 (misal 422 atau 500)
            if (!response.ok) {
                throw new Error(data.message || 'Gagal menyimpan data.');
            }

            closeTarikTunaiModal();
            window.location.reload(); 

        } catch (error) {
            // ✅ Tampilkan pesan error dari backend
            errorDiv.textContent = error.message; 
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan';
        }
    }

    async function deleteTarikTunai(edcId, mbId) {
        if (!confirm('Yakin hapus transaksi tarik tunai ini?')) return;

        const formData = new FormData();
        if (edcId) formData.append('edc_id', edcId);
        if (mbId) formData.append('mb_id', mbId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const response = await fetch("{{ route('brilink.log-tarik-tunai.destroy-ajax') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menghapus.');

            window.location.reload();
        } catch (err) {
            alert('Gagal menghapus: ' + err.message);
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeTarikTunaiModal();
    });

    function formatRupiahInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    async function submitValidasiFisik(e, reportId) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const input = form.querySelector('input[name="manual_validasi_mutasi_fisik"]');
        
        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<iconify-icon icon="mdi:loading" class="text-xs block animate-spin"></iconify-icon>';
        
        const rawValue = input.value.replace(/\./g, '');
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('manual_validasi_mutasi_fisik', rawValue);

        try {
            const response = await fetch(`/brilink/${reportId}/validasi-fisik`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menyimpan.');

            window.location.reload();
        } catch (error) {
            alert('Error: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }
</script>
@endsection