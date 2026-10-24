@extends('layouts.app')

@section('title', 'Laporan Minus')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:alert-circle-outline" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Minus Karyawan</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catat potongan / minus yang akan dipotong dari gaji</p>
            </div>
        </div>

        <button type="button" onclick="openMinusModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Laporan Minus
        </button>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status Potong</label>
                <select name="status" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum Dipotong</option>
                    <option value="sudah" {{ $status === 'sudah' ? 'selected' : '' }}>Sudah Dipotong</option>
                </select>
            </div>

            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Karyawan / Keterangan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
                </div>
            </div>

            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $status !== 'all' || $storeId !== 'all')
                    <a href="{{ route('minus.index') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- SUMMARY (2 KOLOM) --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:clock-alert" class="text-orange-600 text-base sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Total Belum Dipotong</div>
            <div class="text-sm sm:text-xl font-bold text-orange-600 break-words">
                <span class="text-[9px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_belum'], 0, ',', '.') }}
            </div>
            <div class="text-[9px] sm:text-xs text-gray-400 mt-1 leading-tight hidden sm:block">Akan otomatis masuk potongan saat generate gaji</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:check-circle" class="text-green-600 text-base sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 mb-1 leading-tight">Total Sudah Dipotong</div>
            <div class="text-sm sm:text-xl font-bold text-green-600 break-words">
                <span class="text-[9px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_sudah'], 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- DATA MINUS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Minus</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $minusList->count() }} Data</div>
        </div>

        @if($minusList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada laporan minus.</span>
            </div>
        @else

            {{-- TAMPILAN TABEL (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Toko</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Keterangan</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nominal</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Status</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-40 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($minusList as $m)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-xs text-gray-700 whitespace-nowrap">{{ $m->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800 whitespace-nowrap">{{ $m->karyawan->nama ?? '-' }}</td>
                                <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                    <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                    {{ $m->karyawan->store->nama_toko ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-gray-700">{{ $m->keterangan ?? '-' }}</td>
                                <td class="py-3 px-4 text-right font-bold text-red-600 whitespace-nowrap">Rp {{ number_format($m->nominal, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($m->sudah_dipotong)
                                        <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:check-circle" class="text-sm"></iconify-icon> Sudah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:clock-alert" class="text-sm"></iconify-icon> Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if(!$m->sudah_dipotong)
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button"
                                                    onclick="openEditMinusModal({{ $m->id }}, {{ $m->karyawan_id }}, '{{ $m->tanggal?->format('Y-m-d') ?? '' }}', {{ $m->nominal }}, '{{ addslashes($m->keterangan ?? '') }}')"
                                                    class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-blue-200">
                                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                            </button>
                                            <button type="button"
                                                    onclick="deleteMinus({{ $m->id }})"
                                                    class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-red-200">
                                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Terkunci</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN CARD (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($minusList as $m)
                    <div class="p-4 space-y-2 text-xs">
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold text-gray-800 text-sm">{{ $m->karyawan->nama ?? '-' }}</div>
                                <div class="flex items-center gap-1 text-[10px] text-gray-500 mt-0.5">
                                    <iconify-icon icon="mdi:store" class="text-xs"></iconify-icon>
                                    {{ $m->karyawan->store->nama_toko ?? '-' }}
                                </div>
                            </div>
                            <div class="text-right">
                                @if($m->sudah_dipotong)
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">
                                        <iconify-icon icon="mdi:check-circle" class="text-xs"></iconify-icon> Sudah Dipotong
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">
                                        <iconify-icon icon="mdi:clock-alert" class="text-xs"></iconify-icon> Belum
                                    </span>
                                @endif
                                <div class="text-[10px] text-gray-500 mt-1">{{ $m->tanggal?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-2 rounded-lg mt-2">
                            <span class="text-gray-400 block text-[10px] mb-0.5">Keterangan:</span>
                            <span class="text-gray-700 leading-tight">{{ $m->keterangan ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between items-center pt-1 mt-1 border-t border-gray-100">
                            <span class="font-bold text-gray-800">Nominal Minus</span>
                            <span class="font-bold text-red-600 text-sm">Rp {{ number_format($m->nominal, 0, ',', '.') }}</span>
                        </div>

                        @if(!$m->sudah_dipotong)
                            <div class="flex gap-2 pt-2">
                                <button type="button"
                                        onclick="openEditMinusModal({{ $m->id }}, {{ $m->karyawan_id }}, '{{ $m->tanggal?->format('Y-m-d') ?? '' }}', {{ $m->nominal }}, '{{ addslashes($m->keterangan ?? '') }}')"
                                        class="flex-1 inline-flex justify-center items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                </button>
                                <button type="button"
                                        onclick="deleteMinus({{ $m->id }})"
                                        class="flex-1 inline-flex justify-center items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                </button>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

        @endif
    </div>
</div>

{{-- MODAL MINUS --}}
<div id="minusModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeMinusModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="minusModalIcon" icon="mdi:plus-circle" class="text-red-600 text-lg"></iconify-icon>
                <h3 id="minusModalTitle" class="font-bold text-gray-800">Tambah Laporan Minus</h3>
            </div>
            <button type="button" onclick="closeMinusModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="minusForm" onsubmit="submitMinusForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="minusId" value="">
            <input type="hidden" id="minusMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorMinus"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan <span class="text-red-500">*</span></label>
                    <select name="karyawan_id" id="minusKaryawanId" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($karyawanAktif as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }} {{ $k->store ? '(' . $k->store->nama_toko . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="minusTanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" name="nominal" id="minusNominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan <span class="text-red-500">*</span></label>
                    <textarea name="keterangan" id="minusKeterangan" required rows="3" placeholder="cth: Terlambat 2 hari, rusak barang, dll"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeMinusModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitMinus" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitMinusText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_MINUS = document.querySelector('meta[name="csrf-token"]').content;

    document.addEventListener('DOMContentLoaded', function () {
        const inp = document.getElementById('minusNominal');
        if (inp) inp.addEventListener('input', function () {
            let v = this.value.replace(/[^0-9]/g, '');
            this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
        });
    });

    function openMinusModal() {
        document.getElementById('minusForm').reset();
        document.getElementById('modalErrorMinus').classList.add('hidden');
        document.getElementById('minusMode').value = 'create';
        document.getElementById('minusId').value = '';
        document.getElementById('minusModalTitle').textContent = 'Tambah Laporan Minus';
        document.getElementById('minusModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitMinusText').textContent = 'Simpan';
        document.getElementById('minusTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('minusModal').classList.remove('hidden');
        document.getElementById('minusModal').classList.add('flex');
    }

    function openEditMinusModal(id, karyawanId, tanggal, nominal, keterangan) {
        document.getElementById('minusForm').reset();
        document.getElementById('modalErrorMinus').classList.add('hidden');
        document.getElementById('minusMode').value = 'edit';
        document.getElementById('minusId').value = id;
        document.getElementById('minusModalTitle').textContent = 'Edit Laporan Minus';
        document.getElementById('minusModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitMinusText').textContent = 'Simpan Perubahan';
        document.getElementById('minusKaryawanId').value = karyawanId;
        document.getElementById('minusTanggal').value = tanggal || new Date().toISOString().slice(0, 10);
        document.getElementById('minusNominal').value = nominal > 0 ? new Intl.NumberFormat('id-ID').format(nominal) : '';
        document.getElementById('minusKeterangan').value = keterangan || '';
        document.getElementById('minusModal').classList.remove('hidden');
        document.getElementById('minusModal').classList.add('flex');
    }

    function closeMinusModal() {
        document.getElementById('minusModal').classList.add('hidden');
        document.getElementById('minusModal').classList.remove('flex');
    }

    async function submitMinusForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitMinus');
        const btnText = document.getElementById('btnSubmitMinusText');
        const errDiv = document.getElementById('modalErrorMinus');
        const mode = document.getElementById('minusMode').value;
        const id = document.getElementById('minusId').value;

        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(form);
        formData.set('nominal', document.getElementById('minusNominal').value.replace(/[^0-9]/g, ''));

        let url = "{{ route('minus.store-ajax') }}";
        if (mode === 'edit') {
            url = `/minus-karyawan/${id}/ajax`;
            formData.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_MINUS, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Gagal.'));
            closeMinusModal();
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btnText.textContent = originalText;
        }
    }

    async function deleteMinus(id) {
        if (!confirm('Yakin hapus laporan minus ini?')) return;
        try {
            const res = await fetch(`/minus-karyawan/${id}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_MINUS, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMinusModal(); });
</script>
@endsection