@extends('layouts.app')

@section('title', 'Laporan Pengeluaran BRILink')

@section('content')
@php
    $activeTab = request()->input('tab', 'semua');
@endphp
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:cash-minus" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Laporan Pengeluaran</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }}</p>
            </div>
        </div>

        {{-- 🔥 TOMBOL TAMBAH SELALU MUNCUL --}}
        <button type="button" onclick="openPengeluaranModal()" class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Pengeluaran
        </button>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Catatan / Kategori</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari keterangan pengeluaran..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium w-full md:w-auto flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($from || $to || $search || $storeId !== 'all')
                    <a href="{{ route('brilink.pengeluaran', ['tab' => $activeTab]) }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg w-full md:w-auto text-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABS DINAMIS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="flex border-b border-gray-100 overflow-x-auto">
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'semua']) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-medium whitespace-nowrap transition border-b-2
                      {{ strtolower($activeTab) === 'semua' ? 'text-gray-900 border-red-600 bg-gray-50/50' : 'text-gray-600 border-transparent hover:text-gray-800 hover:bg-gray-50' }}">
                <iconify-icon icon="mdi:view-grid-outline" class="text-lg"></iconify-icon>
                Semua Kategori
            </a>

            @foreach($kategoriList as $kategori)
                @php $isActive = strtolower($activeTab) === strtolower($kategori); @endphp
                <a href="{{ request()->fullUrlWithQuery(['tab' => strtolower($kategori)]) }}"
                   class="flex items-center gap-2 px-5 py-3 text-sm font-medium whitespace-nowrap transition border-b-2
                          {{ $isActive ? 'text-gray-900 border-red-600 bg-gray-50/50' : 'text-gray-600 border-transparent hover:text-gray-800 hover:bg-gray-50' }}">
                    <iconify-icon icon="mdi:folder-outline" class="text-lg"></iconify-icon>
                    {{ $kategori }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- TABEL DATA HORIZONTAL --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @php
            $isPiutangTab = str_contains(strtolower(trim($activeTab)), 'piutang');
        @endphp

        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-red-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Rincian Pengeluaran: {{ ucwords($activeTab) }}</h3>
            </div>

            @if($isPiutangTab)
                <div class="flex items-center gap-2">
                    <button type="button"
                            onclick="openBulkBayarModal()"
                            class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:cash-multiple" class="text-lg"></iconify-icon>
                        Bayar Bulk
                    </button>
                    <a href="{{ route('brilink.pengeluaran.invoice-bulk-pdf', [
                            'store'    => $storeId,
                            'from'     => $from,
                            'to'       => $to,
                            'search'   => $search,
                            'kategori' => $activeTab,
                        ]) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:printer" class="text-lg"></iconify-icon>
                        Print Invoice
                    </a>
                </div>
            @else
                <div class="text-xs text-gray-500">{{ $groupedByReport->flatten()->count() }} Transaksi</div>
            @endif
        </div>

        <div class="overflow-x-auto p-5">
            @if($groupedByReport->isEmpty())
                <div class="py-12 text-center text-gray-500">
                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    Belum ada data pengeluaran untuk kategori dan filter ini.
                </div>
            @else
                {{-- JIKA TAB "SEMUA" (TAMPILAN MENYAMPING) --}}
                @if(strtolower($activeTab) === 'semua')
                    <table class="w-full text-sm border-collapse min-w-max">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Tanggal Report</th>
                                @if($storeId === 'all')
                                    <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Toko</th>
                                @endif
                                @foreach($kategoriList as $kategori)
                                    <th class="border border-gray-200 p-3 text-right text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                                        {{ $kategori }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedByReport as $reportId => $itemsInReport)
                                @php
                                    $report = $itemsInReport->first()->report;
                                    $rows = [];
                                    foreach ($itemsInReport as $item) {
                                        $foundEmptySlot = false;
                                        foreach ($rows as $index => $row) {
                                            if (!isset($row[$item->kategori])) {
                                                $rows[$index][$item->kategori] = $item;
                                                $foundEmptySlot = true;
                                                break;
                                            }
                                        }
                                        if (!$foundEmptySlot) {
                                            $rows[] = [ $item->kategori => $item ];
                                        }
                                    }
                                @endphp

                                @foreach($rows as $row)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="border border-gray-200 p-3 text-gray-700 font-medium">
                                            {{ $report->periode_label ?? '-' }}
                                        </td>
                                        
                                        @if($storeId === 'all')
                                            <td class="border border-gray-200 p-3 text-xs text-gray-600">
                                                <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                                {{ $report->store->nama_toko ?? '-' }}
                                            </td>
                                        @endif

                                        @foreach($kategoriList as $kategori)
                                            @php $transaksi = $row[$kategori] ?? null; @endphp
                                            <td class="border border-gray-200 p-3 text-right align-top">
                                                @if($transaksi)
                                                    <div class="flex flex-col items-end">
                                                        <div class="text-sm font-semibold text-red-600">
                                                            Rp {{ number_format($transaksi->nominal_kas, 0, ',', '.') }}
                                                        </div>
                                                        <div class="text-[10px] text-gray-500 mt-1 max-w-[150px] truncate" title="{{ $transaksi->keterangan }}">
                                                            {{ $transaksi->keterangan }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                            
                            <tr class="bg-red-50 border-t-2 border-red-300">
                                <td colspan="{{ $storeId === 'all' ? '2' : '1' }}" class="border border-gray-200 p-3 font-bold text-gray-800 text-left">
                                    TOTAL
                                </td>
                                @foreach($kategoriList as $kategori)
                                    @php
                                        $totalPerKategori = $groupedByReport->flatten()->filter(function($item) use ($kategori) {
                                            return strtolower(trim($item->kategori)) === strtolower(trim($kategori));
                                        })->sum('nominal_kas');
                                    @endphp
                                    <td class="border border-gray-200 p-3 text-right text-red-800 font-bold">
                                        {{ $totalPerKategori > 0 ? 'Rp ' . number_format($totalPerKategori, 0, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-red-600 text-white">
                                <td colspan="{{ count($kategoriList) + ($storeId === 'all' ? 2 : 1) }}" class="p-4 text-right">
                                    <span class="text-sm font-bold uppercase tracking-wider mr-4">Total Keseluruhan Pengeluaran</span>
                                    <span class="text-xl font-bold">
                                        Rp {{ number_format($groupedByReport->flatten()->sum('nominal_kas'), 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                {{-- JIKA TAB SPESIFIK (TAMPILAN VERTIKAL / KE BAWAH) --}}
                @else
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Tanggal Report</th>
                                
                                @if($storeId === 'all')
                                    <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase">Toko</th>
                                @endif

                                <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase">Keterangan</th>
                                <th class="border border-gray-200 p-3 text-right text-xs font-semibold text-gray-600 uppercase">Nominal</th>
                                <th class="border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 uppercase">Kategori Akuntansi</th>
                                
                                @if($isPiutangTab)
                                    <th class="border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                @endif
                                
                                <th class="border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 uppercase w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedByReport as $reportId => $itemsInReport)
                                @php $report = $itemsInReport->first()->report; @endphp
                                
                                @foreach($itemsInReport as $item)
                                    <tr id="pengeluaran-row-{{ $item->id }}" class="hover:bg-gray-50 transition">
                                        <td class="border border-gray-200 p-3 text-gray-700 font-medium">
                                            {{ $report->periode_label ?? '-' }}
                                        </td>

                                        @if($storeId === 'all')
                                            <td class="border border-gray-200 p-3 text-xs text-gray-600">
                                                <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                                {{ $item->report->store->nama_toko ?? '-' }}
                                            </td>
                                        @endif

                                        <td class="border border-gray-200 p-3 text-gray-800">
                                            {{ $item->keterangan ?? 'Tanpa catatan' }}
                                        </td>
                                        <td class="border border-gray-200 p-3 text-right font-bold text-red-600">
                                            Rp {{ number_format($item->nominal_kas, 0, ',', '.') }}
                                        </td>
                                        <td class="border border-gray-200 p-3 text-center">
                                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                                {{ $item->kategori ?: 'Tanpa Kategori' }}
                                            </span>
                                        </td>

                                        {{-- KOLOM STATUS (KHUSUS PIUTANG) --}}
                                        @if($isPiutangTab)
                                            <td class="border border-gray-200 p-3 text-center">
                                                @if($item->is_paid)
                                                    <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">
                                                        <iconify-icon icon="mdi:check-circle" class="text-sm"></iconify-icon> Lunas
                                                    </span>
                                                @elseif($item->paid_amount > 0)
                                                    <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium" 
                                                        title="Terbayar: Rp {{ number_format($item->paid_amount, 0, ',', '.') }} dari Rp {{ number_format($item->nominal_kas, 0, ',', '.') }}">
                                                        <iconify-icon icon="mdi:progress-clock" class="text-sm"></iconify-icon> 
                                                        Terbayar (Rp {{ number_format($item->paid_amount, 0, ',', '.') }})
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-medium">
                                                        <iconify-icon icon="mdi:clock-alert" class="text-sm"></iconify-icon> Belum Bayar
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        {{-- KOLOM AKSI --}}
                                        <td class="border border-gray-200 p-3 text-center">
                                            <div class="inline-flex items-center gap-1 flex-wrap justify-center">
                                                
                                                {{-- TOMBOL BAYAR (KHUSUS PIUTANG & BELUM LUNAS) --}}
                                                @if($isPiutangTab && !$item->is_paid)
                                                    <button type="button"
                                                            onclick="bayarPiutangSingle({{ $item->id }}, {{ $item->nominal_kas - $item->paid_amount }})"
                                                            class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition"
                                                            title="Tandai Lunas">
                                                        <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon>
                                                        Bayar
                                                    </button>
                                                @endif

                                                {{-- EDIT --}}
                                                <button type="button"
                                                        onclick="openEditPengeluaranModal({{ $item->id }}, '{{ addslashes($item->keterangan ?? '') }}', '{{ addslashes($item->kategori ?? '') }}', {{ $item->nominal_kas }})"
                                                        class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition">
                                                    <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                                    Edit
                                                </button>

                                                {{-- HAPUS --}}
                                                <button type="button"
                                                        onclick="deletePengeluaran({{ $item->id }}, '{{ addslashes($item->keterangan ?? '') }}')"
                                                        class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                                                    <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            
                            @php
                                $totalNominal = $groupedByReport->flatten()->sum('nominal_kas');
                                $totalTerbayar = $groupedByReport->flatten()->sum('paid_amount');
                                $sisaPiutang = max(0, $totalNominal - $totalTerbayar);
                            @endphp

                            <tr class="bg-red-50 border-t-2 border-red-300">
                                <td colspan="{{ $storeId === 'all' ? '3' : '2' }}" class="border border-gray-200 p-3 font-bold text-gray-800 text-right align-top">
                                    <div>TOTAL PENGELUARAN</div>
                                    @if($isPiutangTab)
                                        <div class="text-xs font-normal text-gray-500 mt-1">
                                            Total Terbayar: Rp {{ number_format($totalTerbayar, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="border border-gray-200 p-3 text-right align-top">
                                    <div class="font-bold text-red-800">
                                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                                    </div>
                                    @if($isPiutangTab)
                                        <div class="mt-1 text-xs font-bold {{ $sisaPiutang > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                            Sisa: Rp {{ number_format($sisaPiutang, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td></td>

                                {{-- Kolom STATUS (jika piutang) --}}
                                @if($isPiutangTab)
                                    <td class="border border-gray-200 p-3 text-center align-top">
                                        @if($sisaPiutang == 0 && $totalNominal > 0)
                                            <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">
                                                <iconify-icon icon="mdi:check-all" class="text-sm"></iconify-icon> Semua Lunas
                                            </span>
                                        @elseif($sisaPiutang > 0)
                                            <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium">
                                                <iconify-icon icon="mdi:alert-circle" class="text-sm"></iconify-icon> Belum Lunas
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Kolom Aksi (kosong, hanya untuk align) --}}
                                <td class="border border-gray-200 p-3"></td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            @endif
        </div>
    </div>

</div>

{{-- MODAL TAMBAH / EDIT PENGELUARAN --}}
<div id="pengeluaranModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closePengeluaranModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-slide-up">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon id="pengeluaranModalIcon" icon="mdi:plus-circle" class="text-red-600 text-lg"></iconify-icon>
                <h3 id="pengeluaranModalTitle" class="font-bold text-gray-800">Tambah Pengeluaran</h3>
            </div>
            <button type="button" onclick="closePengeluaranModal()" class="text-gray-400 hover:text-gray-600 transition">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="pengeluaranForm" onsubmit="submitPengeluaranForm(event)">
            @csrf
            {{-- Value store_id di form akan diupdate via JS --}}
            <input type="hidden" name="store_id" id="pengeluaranStoreId" value="{{ $storeId }}">
            <input type="hidden" id="pengeluaranId" name="pengeluaran_id" value="">
            <input type="hidden" id="pengeluaranMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorPengeluaran"></div>

                {{-- 🔥 DROPDOWN TOKO (Hanya Tampil jika Semua Toko) --}}
                <div id="tokoFieldWrapper" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Toko <span class="text-red-500">*</span></label>
                    <select id="pengeluaranStoreSelect" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none bg-white">
                        <option value="">-- Pilih Toko --</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal: hanya tampil saat CREATE --}}
                <div id="tanggalFieldWrapper">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi</label>
                    <input type="date" name="tanggal" id="pengeluaranTanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                {{-- Info tanggal saat EDIT (readonly) --}}
                <div id="tanggalReadonlyWrapper" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi</label>
                    <div class="w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
                        <iconify-icon icon="mdi:lock" class="text-gray-400 text-sm mr-1"></iconify-icon>
                        <span id="pengeluaranTanggalReadonly">-</span>
                        <span class="text-[10px] text-gray-400 ml-2">(tidak dapat diubah)</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Akuntansi</label>
                    <select name="kategori" id="pengeluaranKategori" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan / Catatan</label>
                    <input type="text" name="keterangan" id="pengeluaranKeterangan" required placeholder="cth: Beli Bensin"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputNominalPengeluaran" name="nominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closePengeluaranModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitPengeluaran" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitPengeluaranText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL BAYAR BULK PIUTANG --}}
<div id="bulkBayarModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBulkBayarModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-slide-up">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-emerald-50">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cash-multiple" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Bayar Bulk Piutang</h3>
            </div>
            <button type="button" onclick="closeBulkBayarModal()" class="text-gray-400 hover:text-gray-600 transition">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="bulkBayarForm" onsubmit="submitBulkBayar(event)">
            @csrf
            <input type="hidden" name="store" value="{{ $storeId }}">
            <input type="hidden" name="kategori" value="{{ $activeTab }}">
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <input type="hidden" name="search" value="{{ $search }}">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="bulkBayarError"></div>

                <div class="bg-emerald-50 rounded-lg p-3 text-xs text-emerald-800 flex items-start gap-2">
                    <iconify-icon icon="mdi:information" class="text-base mt-0.5"></iconify-icon>
                    <div>
                        Piutang akan dilunasi otomatis <strong>dari yang paling lama</strong> (FIFO). 
                        Jika nominal tidak pas, piutang terakhir akan dibayar sebagian.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Total Nominal Pembayaran</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputBulkBayar" name="nominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeBulkBayarModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitBulkBayar" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:cash-check" class="text-lg"></iconify-icon> Bayar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_PENGELUARAN = document.querySelector('meta[name="csrf-token"]').content;

    // Cek status filter saat ini dari PHP
    const CURRENT_STORE_ID = "{{ $storeId }}";

    function todayLocal() {
        const d = new Date();
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function formatNominalInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const inputNominal = document.getElementById('inputNominalPengeluaran');
        if (inputNominal) {
            inputNominal.addEventListener('input', function () {
                formatNominalInput(this);
            });
        }
        
        const inputBulk = document.getElementById('inputBulkBayar');
        if (inputBulk) {
            inputBulk.addEventListener('input', function () {
                formatNominalInput(this);
            });
        }
    });

    // ===================================================
    // OPEN MODAL — CREATE
    // ===================================================
    function openPengeluaranModal() {
        const modal = document.getElementById('pengeluaranModal');
        const form = document.getElementById('pengeluaranForm');
        const errors = document.getElementById('modalErrorPengeluaran');

        form.reset();
        errors.classList.add('hidden');

        document.getElementById('pengeluaranMode').value = 'create';
        document.getElementById('pengeluaranId').value = '';
        document.getElementById('pengeluaranModalTitle').textContent = 'Tambah Pengeluaran';
        document.getElementById('pengeluaranModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitPengeluaranText').textContent = 'Simpan';

        document.getElementById('tanggalFieldWrapper').classList.remove('hidden');
        document.getElementById('tanggalReadonlyWrapper').classList.add('hidden');
        document.getElementById('pengeluaranTanggal').value = todayLocal();

        document.getElementById('inputNominalPengeluaran').value = '';

        // 🔥 LOGIKA TAMPILKAN/SEMBUNYIKAN DROPDOWN TOKO
        const tokoWrapper = document.getElementById('tokoFieldWrapper');
        const tokoSelect = document.getElementById('pengeluaranStoreSelect');
        const storeIdHidden = document.getElementById('pengeluaranStoreId');

        if (CURRENT_STORE_ID === 'all') {
            // Jika Semua Toko, tampilkan dropdown dan kosongkan value hidden
            tokoWrapper.classList.remove('hidden');
            tokoSelect.required = true;
            storeIdHidden.value = '';
        } else {
            // Jika Toko Spesifik, sembunyikan dropdown dan isi hidden
            tokoWrapper.classList.add('hidden');
            tokoSelect.required = false;
            storeIdHidden.value = CURRENT_STORE_ID;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // ===================================================
    // OPEN MODAL — EDIT
    // ===================================================
    function openEditPengeluaranModal(id, keterangan, kategori, nominal) {
        const modal = document.getElementById('pengeluaranModal');
        const form = document.getElementById('pengeluaranForm');
        const errors = document.getElementById('modalErrorPengeluaran');

        form.reset();
        errors.classList.add('hidden');

        document.getElementById('pengeluaranMode').value = 'edit';
        document.getElementById('pengeluaranId').value = id;
        document.getElementById('pengeluaranModalTitle').textContent = 'Edit Pengeluaran';
        document.getElementById('pengeluaranModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitPengeluaranText').textContent = 'Simpan Perubahan';

        // Sembunyikan dropdown toko saat edit (karena toko tidak diubah)
        document.getElementById('tokoFieldWrapper').classList.add('hidden');
        document.getElementById('pengeluaranStoreSelect').required = false;

        document.getElementById('tanggalFieldWrapper').classList.add('hidden');
        document.getElementById('tanggalReadonlyWrapper').classList.remove('hidden');

        const row = document.getElementById('pengeluaran-row-' + id);
        const tanggalText = row ? row.querySelector('td').innerText.trim() : '-';
        document.getElementById('pengeluaranTanggalReadonly').textContent = tanggalText;

        document.getElementById('pengeluaranKeterangan').value = keterangan || '';
        document.getElementById('pengeluaranKategori').value = kategori || '';

        if (nominal > 0) {
            document.getElementById('inputNominalPengeluaran').value =
                new Intl.NumberFormat('id-ID').format(nominal);
        } else {
            document.getElementById('inputNominalPengeluaran').value = '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // ===================================================
    // CLOSE MODAL
    // ===================================================
    function closePengeluaranModal() {
        const modal = document.getElementById('pengeluaranModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // ===================================================
    // SUBMIT FORM (CREATE atau EDIT)
    // ===================================================
    async function submitPengeluaranForm(e) {
        e.preventDefault();

        const form = e.target;
        const btn = document.getElementById('btnSubmitPengeluaran');
        const btnText = document.getElementById('btnSubmitPengeluaranText');
        const errorDiv = document.getElementById('modalErrorPengeluaran');
        const mode = document.getElementById('pengeluaranMode').value;
        const id = document.getElementById('pengeluaranId').value;

        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        errorDiv.classList.add('hidden');

        const nominalInput = document.getElementById('inputNominalPengeluaran');
        const nominalRaw = nominalInput.value.replace(/[^0-9]/g, '');

        const formData = new FormData(form);
        formData.set('nominal', nominalRaw);

        // Jika create dan sedang di mode 'all', pastikan store_id terambil dari dropdown modal
        if (mode === 'create' && CURRENT_STORE_ID === 'all') {
            const selectedStore = document.getElementById('pengeluaranStoreSelect').value;
            if (!selectedStore) {
                errorDiv.textContent = 'Silakan pilih toko terlebih dahulu.';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btnText.textContent = originalText;
                return;
            }
            formData.set('store_id', selectedStore);
        }

        let url, method;
        if (mode === 'create') {
            url = "{{ route('brilink.pengeluaran.store-ajax') }}";
            method = 'POST';
        } else {
            url = `/brilink/pengeluaran/${id}/ajax`;
            method = 'POST';
            formData.append('_method', 'PUT');
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PENGELUARAN,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Gagal menyimpan data.');
            }

            closePengeluaranModal();
            window.location.reload();

        } catch (error) {
            errorDiv.textContent = error.message;
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btnText.textContent = originalText;
        }
    }

    // ===================================================
    // DELETE PENGELUARAN
    // ===================================================
    async function deletePengeluaran(id, keterangan) {
        if (!confirm(`Yakin hapus pengeluaran "${keterangan}"?`)) return;

        try {
            const response = await fetch(`/brilink/pengeluaran/${id}/ajax`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PENGELUARAN,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (!response.ok) {
                alert(data.message || 'Gagal menghapus.');
                return;
            }

            window.location.reload();

        } catch (err) {
            alert('Gagal menghapus: ' + err.message);
        }
    }

    // ===================================================
    // BAYAR PIUTANG SINGLE
    // ===================================================
    async function bayarPiutangSingle(id, sisaNominal) {
        const nominalFormatted = new Intl.NumberFormat('id-ID').format(sisaNominal);
        if (!confirm(`Tandai piutang ini LUNAS?\nSisa tagihan: Rp ${nominalFormatted}`)) return;

        try {
            const response = await fetch(`/brilink/piutang/${id}/bayar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PENGELUARAN,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal.');

            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    // ===================================================
    // BAYAR PIUTANG BULK (FIFO)
    // ===================================================
    function openBulkBayarModal() {
        document.getElementById('bulkBayarForm').reset();
        document.getElementById('bulkBayarError').classList.add('hidden');
        document.getElementById('bulkBayarModal').classList.remove('hidden');
        document.getElementById('bulkBayarModal').classList.add('flex');
    }

    function closeBulkBayarModal() {
        document.getElementById('bulkBayarModal').classList.add('hidden');
        document.getElementById('bulkBayarModal').classList.remove('flex');
    }

    async function submitBulkBayar(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitBulkBayar');
        const errorDiv = document.getElementById('bulkBayarError');
        const input = document.getElementById('inputBulkBayar');

        const nominalRaw = input.value.replace(/[^0-9]/g, '');
        if (!nominalRaw || parseInt(nominalRaw) <= 0) {
            errorDiv.textContent = 'Nominal tidak valid.';
            errorDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Memproses...';
        errorDiv.classList.add('hidden');

        const formData = new FormData(form);
        formData.set('nominal', nominalRaw);

        try {
            const response = await fetch("{{ route('brilink.piutang.bayar-bulk') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PENGELUARAN,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal.');

            alert(data.message);
            window.location.reload();
        } catch (err) {
            errorDiv.textContent = err.message;
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:cash-check" class="text-lg"></iconify-icon> Bayar';
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePengeluaranModal();
            closeBulkBayarModal();
        }
    });
</script>
@endsection