@extends('layouts.app')

@section('title', 'Log Setoran BRILink')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:bank-transfer-in" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Log Setoran BRILink</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>
        
        <button type="button" onclick="openSetoranModal()" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Setoran Manual
        </button>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari File / Periode</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari data..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium w-full md:w-auto flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $from || $to)
                    <a href="{{ route('brilink.log-setoran', ['store' => $storeId]) }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg w-full md:w-auto text-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL DATA HORIZONTAL --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Rincian Setoran Tunai</h3>
            </div>
            <div class="text-xs text-gray-500">{{ $reports->count() }} Report</div>
        </div>

        <div class="overflow-x-auto p-5">
            @if($reports->isEmpty())
                <div class="py-12 text-center text-gray-500">
                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    Belum ada data setoran untuk toko dan filter ini.
                </div>
            @else
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
    
                    {{-- ========================================== --}}
                    {{-- KIRI: TABEL LOG TRANSAKSI (2/3 layar) --}}
                    {{-- ========================================== --}}
                    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                            <h3 class="font-semibold text-gray-800">Rincian Transaksi Setoran</h3>
                            <span class="text-xs text-gray-500">{{ $reports->flatMap->setoranItems->count() }} Baris</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 border-b border-gray-200">
                                        <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Tanggal</th>
                                        <th class="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Nominal EDC BRI</th>
                                        <th class="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Administrasi Toko</th>
                                        {{-- Sembunyikan kolom Aksi jika Semua Toko --}}
                                        @if($storeId !== 'all')
                                            <th class="p-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($reports as $report)
                                        @forelse($report->setoranItems as $item)
                                            <tr class="hover:bg-gray-50 transition border-b border-gray-200">
                                                <td class="p-3 text-gray-700 font-medium whitespace-nowrap">
                                                    {{ $report->periode_label ?? '-' }}
                                                </td>
                                                <td class="p-3 text-right text-gray-800 font-semibold">
                                                    Rp {{ number_format($item->nominal_edc ?: $item->jumlah_setoran_riil, 0, ',', '.') }}
                                                </td>
                                                <td class="p-3 text-right text-emerald-600 font-semibold">
                                                    Rp {{ number_format($item->administrasi_toko, 0, ',', '.') }}
                                                </td>
                                                
                                                {{-- Sembunyikan tombol jika Semua Toko --}}
                                                @if($storeId !== 'all')
                                                    <td class="p-3 text-center">
                                                        <div class="inline-flex items-center gap-1">
                                                            <button type="button"
                                                                    onclick="openEditSetoranModal(event, {{ $item->id }}, {{ $item->nominal_edc ?: $item->jumlah_setoran_riil }}, {{ $item->administrasi_toko }})"
                                                                    class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition"
                                                                    title="Edit">
                                                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                                            </button>
                                                            <button type="button"
                                                                    onclick="deleteSetoran({{ $item->id }}, {{ $item->nominal_edc ?: $item->jumlah_setoran_riil }})"
                                                                    class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition"
                                                                    title="Hapus">
                                                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                                            </button>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="p-3 text-gray-700 font-medium">{{ $report->periode_label ?? '-' }}</td>
                                                <td colspan="{{ $storeId === 'all' ? '2' : '3' }}" class="p-3 text-center text-gray-400 italic">Tidak ada transaksi</td>
                                            </tr>
                                        @endforelse
                                    @empty
                                        <tr>
                                            <td colspan="{{ $storeId === 'all' ? '3' : '4' }}" class="p-8 text-center text-gray-500">Belum ada data transaksi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td class="p-3 font-bold text-gray-800">TOTAL KESELURUHAN</td>
                                        <td class="p-3 text-right font-bold text-gray-800">
                                            Rp {{ number_format($reports->flatMap->setoranItems->sum('nominal_edc') ?: $totalSetoranKeseluruhan, 0, ',', '.') }}
                                        </td>
                                        <td class="p-3 text-right font-bold text-emerald-700">
                                            Rp {{ number_format($reports->flatMap->setoranItems->sum('administrasi_toko'), 0, ',', '.') }}
                                        </td>
                                        {{-- Sembunyikan kolom aksi kosong jika Semua Toko --}}
                                        @if($storeId !== 'all')
                                            <td></td>
                                        @endif
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- KANAN: STICKY CARD REKONSILIASI (1/3 layar) --}}
                    {{-- ========================================== --}}
                    <div class="xl:col-span-1 sticky top-6 space-y-4">
                        <div class="flex items-center gap-2 mb-2 px-1">
                            <iconify-icon icon="mdi:shield-check" class="text-indigo-600 text-xl"></iconify-icon>
                            <h3 class="font-bold text-gray-800">Rekonsiliasi Mutasi</h3>
                        </div>

                        @php
                            // Group data untuk Card Kanan
                            if ($storeId === 'all') {
                                // Jika semua toko, group per tanggal
                                $groupedForCard = $reports->groupBy(fn($r) => $r->periode_start->format('Y-m-d'));
                            } else {
                                // Jika 1 toko, group per ID report
                                $groupedForCard = $reports->groupBy('id');
                            }
                        @endphp

                        @forelse($groupedForCard as $key => $reportsInGroup)
                            @php
                                $firstReport = $reportsInGroup->first();
                                $setoranRiil = $reportsInGroup->flatMap->setoranItems->sum('jumlah_setoran_riil');
                                
                                // Validasi mutasi: diambil dari report pertama atau dijumlahkan (jika semua toko)
                                $validasiMutasi = $reportsInGroup->sum('manual_validasi_mutasi_rek');
                                $selisih = $setoranRiil - $validasiMutasi;
                                
                                // Untuk form submit validasi (jika all, kita tidak bisa update ke 1 report saja, 
                                // jadi kita tampilkan input readonly atau nonaktifkan form)
                                $isSingleStore = ($storeId !== 'all');
                            @endphp
                            
                            <div class="bg-white rounded-xl shadow-sm border border-indigo-100 overflow-hidden">
                                {{-- Header Card --}}
                                <div class="bg-indigo-50 px-4 py-3 border-b border-indigo-100 flex justify-between items-center">
                                    <span class="font-bold text-indigo-900">{{ $firstReport->periode_label ?? '-' }}</span>
                                    @if($storeId === 'all')
                                        <span class="text-[10px] text-indigo-500 bg-white px-2 py-0.5 rounded border">Total Semua Toko</span>
                                    @endif
                                </div>

                                <div class="p-4 space-y-4">
                                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                        <span class="text-xs text-gray-500">Jumlah Setoran Riil</span>
                                        <span class="font-bold text-gray-800">Rp {{ number_format($setoranRiil, 0, ',', '.') }}</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Validasi Mutasi Rekening</label>
                                        @if($isSingleStore)
                                            {{-- Form Input (Hanya untuk 1 toko) --}}
                                            <form onsubmit="submitValidasiMutasi(event, {{ $firstReport->id }})" class="flex items-center gap-2">
                                                @csrf
                                                <div class="relative flex-1">
                                                    <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 text-xs font-medium">Rp</span>
                                                    </div>
                                                    <input type="text" name="manual_validasi_mutasi_rek" 
                                                        value="{{ $validasiMutasi > 0 ? number_format($validasiMutasi, 0, ',', '.') : '' }}" 
                                                        placeholder="0"
                                                        class="w-full border border-indigo-300 rounded-lg text-right pl-8 pr-2 py-1.5 text-sm font-bold text-indigo-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
                                                        oninput="formatRupiahInput(this)">
                                                </div>
                                                <button type="submit" class="bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 transition shadow-sm" title="Simpan">
                                                    <iconify-icon icon="mdi:content-save" class="text-base block"></iconify-icon>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Readonly (Jika Semua Toko) --}}
                                            <div class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-right font-bold text-gray-700">
                                                Rp {{ number_format($validasiMutasi, 0, ',', '.') }}
                                            </div>
                                            <div class="text-[10px] text-orange-500 mt-1">
                                                * Pilih toko spesifik untuk mengubah validasi.
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                        <span class="text-xs font-bold text-gray-600">Selisih Rekonsiliasi</span>
                                        <span class="font-bold text-sm {{ $selisih == 0 ? 'text-gray-500' : ($selisih > 0 ? 'text-orange-600' : 'text-red-600') }}">
                                            Rp {{ number_format($selisih, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center text-gray-500">
                                Tidak ada report untuk direkonsiliasi.
                            </div>
                        @endforelse
                    </div>
                    
                </div>
            @endif
        </div>
    </div>

</div>
<div id="setoranModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeSetoranModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-slide-up">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon id="setoranModalIcon" icon="mdi:plus-circle" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 id="setoranModalTitle" class="font-bold text-gray-800">Tambah Setoran Manual</h3>
            </div>
            <button type="button" onclick="closeSetoranModal()" class="text-gray-400 hover:text-gray-600 transition">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="setoranForm" onsubmit="submitSetoranForm(event)">
            @csrf
            <input type="hidden" name="store_id" value="{{ $storeId }}">
            <input type="hidden" id="setoranId" value="">
            <input type="hidden" id="setoranMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorSetoran"></div>

                {{-- Tanggal (Create Only) --}}
                <div id="tanggalSetoranWrapper">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi</label>
                    <input type="date" name="tanggal" id="setoranTanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                {{-- Tanggal (Edit Only - Readonly) --}}
                <div id="tanggalSetoranReadonlyWrapper" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi</label>
                    <div class="w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
                        <iconify-icon icon="mdi:lock" class="text-gray-400 text-sm mr-1"></iconify-icon>
                        <span id="setoranTanggalReadonly">-</span>
                        <span class="text-[10px] text-gray-400 ml-2">(tidak dapat diubah)</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal EDC BRI</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputNominalEdc" name="nominal_edc" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Administrasi Toko</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputAdminToko" name="admin_toko" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeSetoranModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitSetoran" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitSetoranText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Helper Format Rupiah ---
    function formatRupiahInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const inputEdc = document.getElementById('inputNominalEdc');
        const inputAdmin = document.getElementById('inputAdminToko');
        if (inputEdc) inputEdc.addEventListener('input', function() { formatRupiahInput(this); });
        if (inputAdmin) inputAdmin.addEventListener('input', function() { formatRupiahInput(this); });
    });

    // --- Buka Modal CREATE ---
    function openSetoranModal() {
        document.getElementById('setoranForm').reset();
        document.getElementById('modalErrorSetoran').classList.add('hidden');
        
        document.getElementById('setoranMode').value = 'create';
        document.getElementById('setoranId').value = '';
        document.getElementById('setoranModalTitle').textContent = 'Tambah Setoran Manual';
        document.getElementById('setoranModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitSetoranText').textContent = 'Simpan';

        document.getElementById('tanggalSetoranWrapper').classList.remove('hidden');
        document.getElementById('tanggalSetoranReadonlyWrapper').classList.add('hidden');
        document.getElementById('setoranTanggal').value = new Date().toISOString().slice(0, 10);

        document.getElementById('inputNominalEdc').value = '';
        document.getElementById('inputAdminToko').value = '';

        document.getElementById('setoranModal').classList.remove('hidden');
        document.getElementById('setoranModal').classList.add('flex');
    }

    // --- Buka Modal EDIT ---
    function openEditSetoranModal(event, id, nominal, admin) {
        document.getElementById('setoranForm').reset();
        document.getElementById('modalErrorSetoran').classList.add('hidden');
        
        document.getElementById('setoranMode').value = 'edit';
        document.getElementById('setoranId').value = id;
        document.getElementById('setoranModalTitle').textContent = 'Edit Setoran Manual';
        document.getElementById('setoranModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitSetoranText').textContent = 'Simpan Perubahan';

        document.getElementById('tanggalSetoranWrapper').classList.add('hidden');
        document.getElementById('tanggalSetoranReadonlyWrapper').classList.remove('hidden');
        
        // Ambil label tanggal dari baris tabel tempat tombol edit berada
        const btn = event.currentTarget;
        const td = btn.closest('td');
        const tr = td ? td.closest('tr') : null;
        // Kolom pertama (indeks 0) adalah Tanggal
        const tanggalText = tr ? tr.querySelectorAll('td')[0].innerText.trim() : '-';
        document.getElementById('setoranTanggalReadonly').textContent = tanggalText;

        if (nominal > 0) document.getElementById('inputNominalEdc').value = new Intl.NumberFormat('id-ID').format(nominal);
        if (admin > 0) document.getElementById('inputAdminToko').value = new Intl.NumberFormat('id-ID').format(admin);

        document.getElementById('setoranModal').classList.remove('hidden');
        document.getElementById('setoranModal').classList.add('flex');
    }

    // --- Tutup Modal ---
    function closeSetoranModal() {
        document.getElementById('setoranModal').classList.add('hidden');
        document.getElementById('setoranModal').classList.remove('flex');
    }

    // --- Submit AJAX (Create/Edit) ---
    async function submitSetoranForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitSetoran');
        const btnText = document.getElementById('btnSubmitSetoranText');
        const errorDiv = document.getElementById('modalErrorSetoran');
        
        const mode = document.getElementById('setoranMode').value;
        const id = document.getElementById('setoranId').value;

        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        errorDiv.classList.add('hidden');

        // Bersihkan titik
        const nominalEdcRaw = document.getElementById('inputNominalEdc').value.replace(/[^0-9]/g, '');
        const adminTokoRaw = document.getElementById('inputAdminToko').value.replace(/[^0-9]/g, '');

        const formData = new FormData(form);
        formData.set('nominal_edc', nominalEdcRaw);
        formData.set('admin_toko', adminTokoRaw);

        let url, method;
        if (mode === 'create') {
            url = "{{ route('brilink.log-setoran.store-ajax') }}";
            method = 'POST';
        } else {
            url = `/brilink/log-setoran/${id}/ajax`;
            method = 'POST';
            formData.append('_method', 'PUT');
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

            if (!response.ok) {
                throw new Error(data.message || 'Gagal menyimpan data.');
            }

            closeSetoranModal();
            window.location.reload(); 

        } catch (error) {
            errorDiv.textContent = error.message;
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btnText.textContent = originalText;
        }
    }

    // --- Hapus Setoran ---
    async function deleteSetoran(id, nominal) {
        if (!confirm(`Yakin hapus setoran dengan nominal Rp ${new Intl.NumberFormat('id-ID').format(nominal)}?`)) return;

        try {
            const response = await fetch(`/brilink/log-setoran/${id}/ajax`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
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

    // --- Submit Form Validasi Mutasi (Bawaan Sebelumnya) ---
    async function submitValidasiMutasi(e, reportId) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const input = form.querySelector('input[name="manual_validasi_mutasi_rek"]');
        
        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<iconify-icon icon="mdi:loading" class="text-xs block animate-spin"></iconify-icon>';
        
        const rawValue = input.value.replace(/\./g, '');
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('manual_validasi_mutasi_rek', rawValue);

        try {
            const response = await fetch(`/brilink/${reportId}/validasi-mutasi`, {
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

    // ESC untuk tutup modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSetoranModal();
    });
</script>
@endsection