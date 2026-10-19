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
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>

        <button type="button" onclick="openPengeluaranModal()" class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Pengeluaran
        </button>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            
            {{-- Pertahankan Tab Aktif saat filter --}}
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
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
                @if($from || $to || $search)
                    <a href="{{ route('brilink.pengeluaran', ['store' => $storeId]) }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg w-full md:w-auto text-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABS DINAMIS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="flex border-b border-gray-100 overflow-x-auto">
            {{-- Tab Semua --}}
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'semua']) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-medium whitespace-nowrap transition border-b-2
                      {{ strtolower($activeTab) === 'semua' ? 'text-gray-900 border-red-600 bg-gray-50/50' : 'text-gray-600 border-transparent hover:text-gray-800 hover:bg-gray-50' }}">
                <iconify-icon icon="mdi:view-grid-outline" class="text-lg"></iconify-icon>
                Semua Kategori
            </a>

            {{-- Tab Kategori Lainnya --}}
            @foreach($kategoriList as $kategori)
                @php 
                    $isActive = strtolower($activeTab) === strtolower($kategori); 
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['tab' => strtolower($kategori)]) }}"
                   class="flex items-center gap-2 px-5 py-3 text-sm font-medium whitespace-nowrap transition border-b-2
                          {{ $isActive
                              ? 'text-gray-900 border-red-600 bg-gray-50/50'
                              : 'text-gray-600 border-transparent hover:text-gray-800 hover:bg-gray-50' }}">
                    <iconify-icon icon="mdi:folder-outline" class="text-lg"></iconify-icon>
                    {{ $kategori }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- TABEL DATA HORIZONTAL --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-red-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Rincian Pengeluaran: {{ ucwords($activeTab) }}</h3>
            </div>
            <div class="text-xs text-gray-500">{{ $groupedByReport->flatten()->count() }} Transaksi</div>
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
                            
                            {{-- BARIS TOTAL REKAP --}}
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
                                <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase">Toko</th>
                                <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase">Keterangan</th>
                                <th class="border border-gray-200 p-3 text-right text-xs font-semibold text-gray-600 uppercase">Nominal</th>
                                <th class="border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 uppercase">Kategori Akuntansi</th>
                                <th class="border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 uppercase w-32">Aksi</th>
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
                                        <td class="border border-gray-200 p-3 text-center">
                                            <div class="inline-flex items-center gap-1">
                                                <button type="button"
                                                        onclick="openEditPengeluaranModal({{ $item->id }}, '{{ addslashes($item->keterangan ?? '') }}', '{{ addslashes($item->kategori ?? '') }}', {{ $item->nominal_kas }})"
                                                        class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition">
                                                    <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                                    Edit
                                                </button>
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
                            
                            {{-- BARIS TOTAL REKAP --}}
                            <tr class="bg-red-50 border-t-2 border-red-300">
                                <td colspan="{{ $storeId === 'all' ? '3' : '2' }}" class="border border-gray-200 p-3 font-bold text-gray-800 text-right">
                                    TOTAL PENGELUARAN
                                </td>
                                <td class="border border-gray-200 p-3 text-right text-red-800 font-bold">
                                    Rp {{ number_format($groupedByReport->flatten()->sum('nominal_kas'), 0, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            @endif
        </div>
    </div>

</div>

{{-- MODAL TAMBAH PENGELUARAN MANUAL --}}
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
            <input type="hidden" name="store_id" value="{{ $storeId }}">
            <input type="hidden" id="pengeluaranId" name="pengeluaran_id" value="">
            <input type="hidden" id="pengeluaranMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorPengeluaran"></div>

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

<script>
    const CSRF_TOKEN_PENGELUARAN = document.querySelector('meta[name="csrf-token"]').content;

    function todayLocal() {
        const d = new Date();
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    // ===================================================
    // FORMAT NOMINAL (dipakai di modal)
    // ===================================================
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

        // Set mode create
        document.getElementById('pengeluaranMode').value = 'create';
        document.getElementById('pengeluaranId').value = '';
        document.getElementById('pengeluaranModalTitle').textContent = 'Tambah Pengeluaran';
        document.getElementById('pengeluaranModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitPengeluaranText').textContent = 'Simpan';

        // Tampilkan tanggal input, sembunyikan readonly
        document.getElementById('tanggalFieldWrapper').classList.remove('hidden');
        document.getElementById('tanggalReadonlyWrapper').classList.add('hidden');
        document.getElementById('pengeluaranTanggal').value = todayLocal();

        document.getElementById('inputNominalPengeluaran').value = '';

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

        // Set mode edit
        document.getElementById('pengeluaranMode').value = 'edit';
        document.getElementById('pengeluaranId').value = id;
        document.getElementById('pengeluaranModalTitle').textContent = 'Edit Pengeluaran';
        document.getElementById('pengeluaranModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitPengeluaranText').textContent = 'Simpan Perubahan';

        // Sembunyikan tanggal input, tampilkan readonly
        document.getElementById('tanggalFieldWrapper').classList.add('hidden');
        document.getElementById('tanggalReadonlyWrapper').classList.remove('hidden');

        // Ambil tanggal dari baris tabel (kolom pertama)
        const row = document.getElementById('pengeluaran-row-' + id);
        const tanggalText = row ? row.querySelector('td').innerText.trim() : '-';
        document.getElementById('pengeluaranTanggalReadonly').textContent = tanggalText;

        // Isi field
        document.getElementById('pengeluaranKeterangan').value = keterangan || '';
        document.getElementById('pengeluaranKategori').value = kategori || '';

        // Format nominal dengan titik
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

        // Bersihkan titik dari nominal
        const nominalInput = document.getElementById('inputNominalPengeluaran');
        const nominalRaw = nominalInput.value.replace(/[^0-9]/g, '');

        const formData = new FormData(form);
        formData.set('nominal', nominalRaw); // override dengan angka bersih

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
    // ESC = tutup modal
    // ===================================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePengeluaranModal();
    });
</script>
@endsection