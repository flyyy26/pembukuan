@extends('layouts.app')

@section('title', 'Log Pulsa & PPOB')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:cellphone-link" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Log Pulsa & PPOB</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" onclick="openManualModal()" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
                Tambah Transaksi Manual
            </button>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Kategori / Keterangan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari data..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium w-full md:w-auto flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $from || $to)
                    <a href="{{ route('brilink.log-ppob', ['store' => $storeId]) }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg w-full md:w-auto text-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL DATA HORIZONTAL & REKAP --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Rincian Transaksi Kasir PPOB</h3>
            </div>
            <div class="text-xs text-gray-500">{{ $ppobData->count() }} Data</div>
        </div>

        <div class="overflow-x-auto p-5">
            @if($ppobData->isEmpty())
                <div class="py-12 text-center text-gray-500">
                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    Belum ada data PPOB untuk toko dan filter ini.
                </div>
            @else
                @php
                    // Kelompokkan data berdasarkan report (tanggal import)
                    $groupedReports = $ppobData->groupBy('brilink_report_id');
                    
                    // Kumpulkan semua kategori unik agar urutan kolom konsisten
                    $allCategories = $ppobData->pluck('kategori')->unique()->values();
                @endphp

                <table class="w-full text-sm border-collapse min-w-max">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Tanggal Report</th>
                            @foreach($allCategories as $kategori)
                                <th class="border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                                    {{ $kategori }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Kelompokkan data HANYA berdasarkan Tanggal (Y-m-d)
                            // Sehingga data dari toko yang berbeda di tanggal yang sama akan menyatu
                            $groupedByDate = $ppobData->groupBy(function($item) {
                                return $item->report->periode_start->format('Y-m-d');
                            });
                        @endphp

                        @foreach($groupedByDate as $date => $itemsInDate)
                            @php
                                $firstReport = $itemsInDate->first()->report;
                                
                                // Buat keranjang baris
                                $rows = [];
                                foreach ($itemsInDate as $item) {
                                    $foundEmptySlot = false;
                                    // Cari baris yang belum ada kategori ini
                                    foreach ($rows as $index => $row) {
                                        if (!isset($row[$item->kategori])) {
                                            // Jika sudah ada transaksi dengan kategori sama di baris ini (dari toko lain),
                                            // kita jumlahkan nominalnya
                                            $rows[$index][$item->kategori] = [
                                                'nominal' => ($row[$item->kategori]['nominal'] ?? 0) + $item->nominal,
                                                'id' => $item->id, // Simpan ID terakhir untuk aksi edit (jika perlu)
                                            ];
                                            $foundEmptySlot = true;
                                            break;
                                        }
                                    }
                                    
                                    // Jika semua baris yang ada sudah terisi kategori ini, buat baris baru
                                    if (!$foundEmptySlot) {
                                        $rows[] = [ 
                                            $item->kategori => [
                                                'nominal' => $item->nominal,
                                                'id' => $item->id
                                            ]
                                        ];
                                    }
                                }
                            @endphp

                            @foreach($rows as $row)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="border border-gray-200 p-3 text-gray-700 font-medium">
                                        {{ $firstReport->periode_label ?? '-' }}
                                    </td>
                                    
                                    @foreach($allCategories as $kategori)
                                        @php $transaksi = $row[$kategori] ?? null; @endphp
                                        <td class="border border-gray-200 p-3 text-right text-blue-600 font-semibold group/cell">
                                            @if($transaksi)
                                                <div class="flex items-center justify-end gap-1">
                                                    <span>Rp {{ number_format($transaksi['nominal'], 0, ',', '.') }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                        
                        {{-- BARIS TOTAL REKAP OMSET --}}
                        <tr class="bg-blue-50 border-t-2 border-blue-300">
                            <td class="border border-gray-200 p-3 font-bold text-gray-800 text-left">
                                TOTAL REKAP
                            </td>
                            @foreach($allCategories as $kategori)
                                @php
                                    $totalPerKategori = $ppobData->filter(fn($item) => strtolower(trim($item->kategori)) === strtolower(trim($kategori)))->sum('nominal');
                                @endphp
                                <td class="border border-gray-200 p-3 text-center text-blue-800 font-bold">
                                    {{ $totalPerKategori > 0 ? 'Rp ' . number_format($totalPerKategori, 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-blue-600 text-white">
                            <td colspan="{{ count($allCategories) + 1 }}" class="p-4 text-right">
                                <span class="text-sm font-bold uppercase tracking-wider mr-4">Total Omset Keseluruhan</span>
                                <span class="text-xl font-bold">Rp {{ number_format($totalOmset, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

</div>

<div id="manualModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeManualModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-slide-up">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon id="manualModalIcon" icon="mdi:plus-circle" class="text-blue-600 text-lg"></iconify-icon>
                <h3 id="manualModalTitle" class="font-bold text-gray-800">Tambah Transaksi Manual</h3>
            </div>
            <button type="button" onclick="closeManualModal()" class="text-gray-400 hover:text-gray-600 transition">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="manualForm" onsubmit="submitManualForm(event)">
            @csrf
            <input type="hidden" name="store_id" value="{{ $storeId }}">
            <input type="hidden" id="manualPpobId" value="">
            <input type="hidden" id="manualMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalError"></div>

                {{-- Tanggal: hanya tampil saat CREATE --}}
                <div id="tanggalManualWrapper">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi</label>
                    <input type="date" name="tanggal" id="manualTanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                {{-- Tanggal readonly saat EDIT --}}
                <div id="tanggalManualReadonlyWrapper" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi</label>
                    <div class="w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
                        <iconify-icon icon="mdi:lock" class="text-gray-400 text-sm mr-1"></iconify-icon>
                        <span id="manualTanggalReadonly">-</span>
                        <span class="text-[10px] text-gray-400 ml-2">(tidak dapat diubah)</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                    <select name="kategori" id="manualKategori" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="Pulsa & Deposit">Pulsa & Deposit</option>
                        <option value="Barang Kasir Pintar">Barang Kasir Pintar</option>
                        <option value="Fastpay">Fastpay</option>
                        <option value="Payfazz">Payfazz</option>
                        <option value="Buku Warung">Buku Warung</option>
                        <option value="Penambahan Kas">Penambahan Kas</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputNominalManual" name="nominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeManualModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitManual" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitManualText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_PPOB = document.querySelector('meta[name="csrf-token"]').content;

    function todayLocal() {
        const d = new Date();
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    // ===================================================
    // FORMAT NOMINAL
    // ===================================================
    document.addEventListener('DOMContentLoaded', function () {
        const inputNominal = document.getElementById('inputNominalManual');
        if (inputNominal) {
            inputNominal.addEventListener('input', function () {
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
            });
        }
    });

    // ===================================================
    // OPEN MODAL — CREATE
    // ===================================================
    function openManualModal() {
        const modal = document.getElementById('manualModal');
        document.getElementById('manualForm').reset();
        document.getElementById('modalError').classList.add('hidden');

        document.getElementById('manualMode').value = 'create';
        document.getElementById('manualPpobId').value = '';
        document.getElementById('manualModalTitle').textContent = 'Tambah Transaksi Manual';
        document.getElementById('manualModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitManualText').textContent = 'Simpan';

        document.getElementById('tanggalManualWrapper').classList.remove('hidden');
        document.getElementById('tanggalManualReadonlyWrapper').classList.add('hidden');
        document.getElementById('manualTanggal').value = todayLocal();

        document.getElementById('inputNominalManual').value = '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // ===================================================
    // OPEN MODAL — EDIT
    // ===================================================
    function openEditPpobModal(event, id, kategori, nominal) {
        const modal = document.getElementById('manualModal');
        document.getElementById('manualForm').reset();
        document.getElementById('modalError').classList.add('hidden');

        document.getElementById('manualMode').value = 'edit';
        document.getElementById('manualPpobId').value = id;
        document.getElementById('manualModalTitle').textContent = 'Edit Transaksi PPOB';
        document.getElementById('manualModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitManualText').textContent = 'Simpan Perubahan';

        document.getElementById('tanggalManualWrapper').classList.add('hidden');
        document.getElementById('tanggalManualReadonlyWrapper').classList.remove('hidden');

        // Ambil label tanggal dari baris tabel
        const btn = event.currentTarget;
        const td = btn.closest('td');
        const tr = td ? td.closest('tr') : null;
        const tanggalText = tr ? tr.querySelector('td').innerText.trim() : '-';
        document.getElementById('manualTanggalReadonly').textContent = tanggalText;

        document.getElementById('manualKategori').value = kategori || '';

        if (nominal > 0) {
            document.getElementById('inputNominalManual').value =
                new Intl.NumberFormat('id-ID').format(nominal);
        } else {
            document.getElementById('inputNominalManual').value = '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // ===================================================
    // CLOSE
    // ===================================================
    function closeManualModal() {
        document.getElementById('manualModal').classList.add('hidden');
        document.getElementById('manualModal').classList.remove('flex');
    }

    // ===================================================
    // SUBMIT (CREATE / EDIT)
    // ===================================================
    async function submitManualForm(e) {
        e.preventDefault();

        const form = e.target;
        const btn = document.getElementById('btnSubmitManual');
        const btnText = document.getElementById('btnSubmitManualText');
        const errorDiv = document.getElementById('modalError');
        const mode = document.getElementById('manualMode').value;
        const id = document.getElementById('manualPpobId').value;

        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        errorDiv.classList.add('hidden');

        const nominalInput = document.getElementById('inputNominalManual');
        const nominalRaw = nominalInput.value.replace(/[^0-9]/g, '');

        const formData = new FormData(form);
        formData.set('nominal', nominalRaw);

        let url, method;
        if (mode === 'create') {
            url = "{{ route('brilink.log-ppob.store-ajax') }}";
            method = 'POST';
        } else {
            url = `/brilink/log-ppob/${id}/ajax`;
            method = 'POST';
            formData.append('_method', 'PUT');
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PPOB,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Gagal menyimpan.');
            }

            closeManualModal();
            window.location.reload();
        } catch (error) {
            errorDiv.textContent = error.message;
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btnText.textContent = originalText;
        }
    }

    // ===================================================
    // DELETE PPOB
    // ===================================================
    async function deletePpob(id, kategori, nominal) {
        const nominalFormatted = new Intl.NumberFormat('id-ID').format(nominal);
        if (!confirm(`Yakin hapus transaksi "${kategori}" dengan nominal Rp ${nominalFormatted}?`)) return;

        try {
            const response = await fetch(`/brilink/log-ppob/${id}/ajax`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PPOB,
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
        if (e.key === 'Escape') closeManualModal();
    });
</script>
@endsection