@extends('layouts.app')

@section('title', 'Data Setor Hanca')

@section('content')

<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding: 2px 6px !important;
        min-height: 38px !important;
        background-color: #fff !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
        color: #047857 !important;
        border-radius: 0.375rem !important;
        padding: 2px 8px !important;
        font-size: 0.75rem !important;
        margin: 2px 4px 2px 0 !important;
    }
    .select2-dropdown {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }
    .select2-container--default .select2-results__option {
        font-size: 0.75rem !important;
        padding: 6px 10px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #10b981 !important;
        color: white !important;
    }
</style>

<div class="w-full min-w-0">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:clipboard-list" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Setor Hanca</h2>
                <p class="text-xs sm:text-sm text-gray-500">Proses setoran barang (Qty) karyawan konveksi</p>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan</label>
                <select name="karyawan_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $karyawanId === 'all' ? 'selected' : '' }}>Semua Karyawan</option>
                    @foreach($karyawanList as $k)
                        <option value="{{ $k->id }}" {{ $karyawanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Kerja</label>
                <select name="kategori" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $kategoriFilter === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ $kategoriFilter == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Produk</label>
                <select name="kategori_produk" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $kategoriProdukFilter === 'all' ? 'selected' : '' }}>Semua Kategori Produk</option>
                    @foreach($kategoriProdukList as $katProd)
                        <option value="{{ $katProd }}" {{ $kategoriProdukFilter == $katProd ? 'selected' : '' }}>{{ $katProd }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                <select name="bulan" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $bulanFilter === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                    @foreach($bulanList as $bln)
                        <option value="{{ $bln['value'] }}" {{ $bulanFilter == $bln['value'] ? 'selected' : '' }}>{{ $bln['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, produk..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $karyawanId !== 'all' || $kategoriFilter !== 'all' || $kategoriProdukFilter !== 'all' || $bulanFilter !== 'all')
                    <a href="{{ route('konveksi.setor-hanca') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Hanca Belum Selesai</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $hancaList->count() }} Data</div>
        </div>

        @if($hancaList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Tidak ada hanca yang perlu disetor.</span>
            </div>
        @else
            <div class="w-full overflow-x-auto block">
                <table class="w-full text-xs sm:text-sm text-left min-w-[900px]" style="text-transform:uppercase;">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori Kerja</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tukang Potong</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nama Produk</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Warna / Ukuran</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Qty Hanca</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Sisa Qty</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Waktu</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($hancaList as $h)
                            @foreach($h->details as $detail)
                                @if($detail->sisa_hanca > 0)
                                    @php
                                        $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                                        $sisaQtyDetail = max(0, $detail->jumlah_hanca - $dibayarQty);
                                        $tukangPotong = $detail->sumberPotong->hanca->karyawan->nama ?? '-';

                                        // ✅ Hitung Waktu
                                        $tanggalMulai = $h->tanggal ? \Carbon\Carbon::parse($h->tanggal)->startOfDay() : null;
                                        $tanggalSelesai = $sisaQtyDetail <= 0
                                            ? \Carbon\Carbon::parse($detail->updated_at)->startOfDay()
                                            : now()->startOfDay();
                                        $durasi = $tanggalMulai ? (int) $tanggalMulai->diffInDays($tanggalSelesai) : 0;

                                        if ($durasi <= 7) {
                                            $waktuClass = 'bg-green-100 text-green-700 border-green-200';
                                        } elseif ($durasi <= 14) {
                                            $waktuClass = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                        } else {
                                            $waktuClass = 'bg-red-100 text-red-700 border-red-200';
                                        }
                                    @endphp

                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">{{ $h->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] text-uppercase font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                {{ $h->kategori }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-xs text-gray-600 font-medium whitespace-nowrap">{{ $tukangPotong }}</td>
                                        <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $h->karyawan->nama ?? '-' }}</td>
                                        <td class="py-2 px-3 text-xs text-gray-800 whitespace-nowrap">{{ $detail->nama_produk }}</td>
                                        <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">{{ $detail->warna ?? '-' }} / {{ $detail->ukuran ?? '-' }}</td>
                                        <td class="py-2 px-3 text-right font-semibold text-gray-800 whitespace-nowrap">{{ $detail->jumlah_hanca }}</td>
                                        <td class="py-2 px-3 text-right font-semibold text-orange-600 whitespace-nowrap">{{ $sisaQtyDetail }}</td>
                                        <td class="py-2 px-3 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-semibold px-2 py-1 rounded border {{ $waktuClass }}">
                                                <iconify-icon icon="mdi:clock-outline" class="text-xs sm:text-sm"></iconify-icon>
                                                {{ $durasi }} hari
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <button type="button" 
                                                onclick="openSetorModalDariDetail({{ $detail->id }})"
                                                class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition border border-emerald-200"
                                                title="Setor Hanca Ini">
                                                <iconify-icon icon="mdi:package-down" class="text-sm"></iconify-icon> Setor
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: SETOR HANCA (HANYA QTY) --}}
{{-- ============================================================ --}}
<div id="setorModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeSetorModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-emerald-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:package-down" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Setor Barang Hanca</h3>
            </div>
            <button type="button" onclick="closeSetorModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>
        <form id="setorForm" onsubmit="submitSetor(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorSetor"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Pilih Hanca yang Disetor <span class="text-red-500">*</span>
                    </label>
                    <select name="hanca_ids[]" id="setorHancaIds" multiple="multiple" style="width:100%;" required>
                        @foreach($hancaList as $h)
                            @foreach($h->details as $detail)
                                @php
                                    $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                                    $sisaQtyReal = $detail->jumlah_hanca - $dibayarQty;
                                @endphp
                                @if($sisaQtyReal > 0)
                                    <option value="{{ $h->id }}"
                                        data-hanca-id="{{ $h->id }}"
                                        data-detail-id="{{ $detail->id }}"
                                        data-karyawan-id="{{ $h->konveksi_karyawan_id }}"
                                        data-karyawan="{{ $h->karyawan->nama ?? '-' }}"
                                        data-kategori="{{ $h->kategori }}"
                                        data-produk="{{ $detail->nama_produk }}"
                                        data-warna="{{ $detail->warna ?? '' }}"
                                        data-ukuran="{{ $detail->ukuran ?? '' }}"
                                        data-sisa-qty="{{ $sisaQtyReal }}">
                                        {{ $h->karyawan->nama ?? '-' }} — {{ $detail->nama_produk }} @if($detail->warna)[{{ $detail->warna }}]@endif (Sisa: {{ $sisaQtyReal }} pcs)
                                    </option>
                                @endif
                            @endforeach
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Centang hanca yang akan disetor barangnya.</p>
                </div>

                <div id="setorTableWrapper" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Rincian Setoran</label>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left px-2 py-2 font-semibold text-gray-600">Produk</th>
                                    <th class="text-center px-2 py-2 font-semibold text-gray-600">Sisa</th>
                                    <th class="text-center px-2 py-2 font-semibold text-gray-600 w-24">Setor Qty</th>
                                </tr>
                            </thead>
                            <tbody id="setorTableBody" class="divide-y divide-gray-100"></tbody>
                            <tfoot class="bg-emerald-50 border-t border-emerald-200">
                                <tr>
                                    <td colspan="2" class="px-2 py-2 text-xs font-semibold text-emerald-800 text-right">TOTAL SETOR</td>
                                    <td id="totalQtySetor" class="px-2 py-2 text-center font-bold text-emerald-700">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="setorTanggal" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" id="setorKeterangan" placeholder="Opsional"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <input type="hidden" name="tipe" value="bayaran">
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeSetorModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitSetor" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan Setoran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let setorState = {};

    function formatRupiah(angka) { return new Intl.NumberFormat('id-ID').format(angka); }
    function parseAngka(str) { return parseInt(String(str).replace(/[^0-9]/g, '')) || 0; }

    // ===== MODAL SETOR =====
    $(document).ready(function() {
        $('#setorHancaIds').select2({
            placeholder: "-- Pilih Hanca (Bisa lebih dari 1) --",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#setorModal'),
        });
        $('#setorHancaIds').on('select2:select select2:unselect', function () {
            renderSetorTable();
        });
    });

    function openSetorModalDariDetail(detailId) {
        document.getElementById('setorForm').reset();
        document.getElementById('modalErrorSetor').classList.add('hidden');
        document.getElementById('setorTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('setorTableWrapper').classList.add('hidden');
        setorState = {};

        const select = $('#setorHancaIds');
        select.val(null).trigger('change');

        const matchingOption = document.querySelector(`#setorHancaIds option[data-detail-id="${detailId}"]`);
        if (matchingOption) {
            select.val([matchingOption.value]);
            renderSetorTable();
            select.trigger('change.select2');
        }

        document.getElementById('setorModal').classList.remove('hidden');
        document.getElementById('setorModal').classList.add('flex');
    }

    function closeSetorModal() {
        document.getElementById('setorModal').classList.add('hidden');
        document.getElementById('setorModal').classList.remove('flex');
    }

    function renderSetorTable() {
        const selectedOpts = Array.from(document.getElementById('setorHancaIds').selectedOptions);
        const wrapper = document.getElementById('setorTableWrapper');
        const tbody = document.getElementById('setorTableBody');

        if (selectedOpts.length === 0) {
            wrapper.classList.add('hidden');
            tbody.innerHTML = ''; setorState = {}; updateTotals(); return;
        }

        const newState = {};
        selectedOpts.forEach(opt => {
            const detailId = opt.dataset.detailId;
            const existing = setorState[detailId];
            const sisaQty = parseInt(opt.dataset.sisaQty) || 0;
            const qty = existing && existing.qty <= sisaQty ? existing.qty : sisaQty;
            
            newState[detailId] = {
                detailId, 
                produk: opt.dataset.produk,
                warna: opt.dataset.warna, 
                ukuran: opt.dataset.ukuran,
                sisaQty, 
                qty
            };
        });
        setorState = newState;

        tbody.innerHTML = '';
        Object.values(setorState).forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-2 py-2">
                    <div class="font-medium text-gray-800 text-xs">${item.produk}</div>
                    <div class="text-[10px] text-gray-500">${item.warna ? item.warna : ''}${item.ukuran ? ' · ' + item.ukuran : ''}</div>
                </td>
                <td class="px-2 py-2 text-center text-xs text-orange-600 font-semibold">${item.sisaQty}</td>
                <td class="px-2 py-2 text-center">
                    <input type="number" min="1" max="${item.sisaQty}" value="${item.qty}"
                        oninput="onQtyChange('${item.detailId}', this)"
                        class="w-16 border border-gray-300 rounded px-1.5 py-1 text-xs text-center focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </td>
            `;
            tbody.appendChild(tr);
        });

        wrapper.classList.remove('hidden');
        updateTotals();
    }

    function onQtyChange(detailId, input) {
        const item = setorState[detailId];
        if (!item) return;
        let qty = parseInt(input.value) || 0;
        if (qty < 1) qty = 1;
        if (qty > item.sisaQty) qty = item.sisaQty;
        input.value = qty;
        item.qty = qty;
        updateTotals();
    }

    function updateTotals() {
        let totalQty = 0;
        Object.values(setorState).forEach(item => {
            totalQty += item.qty;
        });
        const elQty = document.getElementById('totalQtySetor');
        if (elQty) elQty.textContent = totalQty;
    }

    async function submitSetor(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitSetor');
        const errDiv = document.getElementById('modalErrorSetor');
        const orig = btn.innerHTML;
        errDiv.classList.add('hidden');

        if (Object.keys(setorState).length === 0) {
            errDiv.textContent = 'Pilih minimal 1 hanca untuk disetor.';
            errDiv.classList.remove('hidden');
            return;
        }
        const items = Object.values(setorState).filter(i => i.qty > 0);
        if (items.length === 0) {
            errDiv.textContent = 'Isi Qty Setor minimal 1 unit.';
            errDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true; btn.innerHTML = 'Menyimpan...';

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('tipe', 'bayaran');
        formData.append('tanggal', document.getElementById('setorTanggal').value);
        formData.append('keterangan', document.getElementById('setorKeterangan').value);
        items.forEach(item => {
            formData.append(`qty_setor[${item.detailId}]`, item.qty);
        });
        // Tidak ada nominal_setor yang dikirim, karena ini hanya setor barang

        try {
            const res = await fetch("{{ route('konveksi.pembayaran.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            const textData = await res.text();
            let data;
            try { data = JSON.parse(textData); }
            catch (err) { console.error("Respon server bukan JSON:", textData); throw new Error("Terjadi kesalahan pada server."); }
            if (!res.ok) throw new Error(data.message || 'Gagal.');

            // ✅ Jangan buka PDF, langsung reload saja
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false; btn.innerHTML = orig;
        }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeSetorModal(); }
    });
</script>
@endsection