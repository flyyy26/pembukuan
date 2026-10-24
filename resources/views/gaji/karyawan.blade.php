@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:account-group" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Karyawan</h2>
                <p class="text-xs sm:text-sm text-gray-500">Kelola data master karyawan</p>
            </div>
        </div>

        <button type="button" onclick="openKaryawanModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Karyawan
        </button>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Nama</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama karyawan..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                </div>
            </div>

            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $status !== 'all' || $storeId !== 'all')
                    <a href="{{ route('karyawan.index') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- DATA KARYAWAN --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Karyawan</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $karyawan->count() }} Karyawan</div>
        </div>

        @if($karyawan->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:account-off-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                Belum ada data karyawan untuk filter ini.
            </div>
        @else

            {{-- TAMPILAN TABEL (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nama</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Toko</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Gaji Pokok</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Status</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tgl Masuk</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">No HP</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-40 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($karyawan as $k)
                            <tr id="row-karyawan-{{ $k->id }}" class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $k->nama }}</td>
                                <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                    <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                    {{ $k->store->nama_toko ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-right text-gray-800 font-semibold whitespace-nowrap">
                                    Rp {{ number_format($k->gaji_pokok, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($k->is_active)
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">Aktif</span>
                                    @else
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded font-medium">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $k->tanggal_masuk?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">{{ $k->no_hp ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button"
                                                onclick="openEditKaryawanModal({{ $k->id }}, '{{ addslashes($k->nama) }}', {{ $k->store_id ?? 'null' }}, {{ $k->gaji_pokok }}, '{{ $k->tanggal_masuk?->format('Y-m-d') ?? '' }}', '{{ addslashes($k->no_hp ?? '') }}', {{ $k->is_active ? 1 : 0 }})"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                        </button>
                                        <button type="button"
                                                onclick="deleteKaryawan({{ $k->id }}, '{{ addslashes($k->nama) }}')"
                                                class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN CARD (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($karyawan as $k)
                    <div id="row-karyawan-{{ $k->id }}" class="p-4 space-y-2 text-xs">
                        <div class="flex justify-between items-start">
                            <div class="font-bold text-gray-800 text-sm">{{ $k->nama }}</div>
                            @if($k->is_active)
                                <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">Aktif</span>
                            @else
                                <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium whitespace-nowrap">Nonaktif</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 text-[10px] text-gray-500">
                            <iconify-icon icon="mdi:store" class="text-xs"></iconify-icon>
                            {{ $k->store->nama_toko ?? '-' }}
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 pt-1 mt-1 border-t border-gray-50">
                            <div>
                                <span class="text-gray-400 block text-[10px]">Gaji Pokok</span>
                                <span class="font-semibold text-gray-800">Rp {{ number_format($k->gaji_pokok, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-400 block text-[10px]">Tgl Masuk</span>
                                <span class="text-gray-700">{{ $k->tanggal_masuk?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                            <div class="col-span-2 text-right mt-1">
                                <span class="text-gray-400 block text-[10px]">No HP</span>
                                <span class="text-gray-700">{{ $k->no_hp ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="button"
                                    onclick="openEditKaryawanModal({{ $k->id }}, '{{ addslashes($k->nama) }}', {{ $k->store_id ?? 'null' }}, {{ $k->gaji_pokok }}, '{{ $k->tanggal_masuk?->format('Y-m-d') ?? '' }}', '{{ addslashes($k->no_hp ?? '') }}', {{ $k->is_active ? 1 : 0 }})"
                                    class="flex-1 inline-flex justify-center items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                            </button>
                            <button type="button"
                                    onclick="deleteKaryawan({{ $k->id }}, '{{ addslashes($k->nama) }}')"
                                    class="flex-1 inline-flex justify-center items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

        @endif
    </div>
</div>

{{-- MODAL KARYAWAN --}}
<div id="karyawanModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeKaryawanModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="karyawanModalIcon" icon="mdi:account-plus" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 id="karyawanModalTitle" class="font-bold text-gray-800">Tambah Karyawan</h3>
            </div>
            <button type="button" onclick="closeKaryawanModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="karyawanForm" onsubmit="submitKaryawanForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="karyawanId" value="">
            <input type="hidden" id="karyawanMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorKaryawan"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="karyawanNama" required placeholder="cth: Ahmad"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                    <select name="store_id" id="karyawanStoreId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Tanpa Toko --</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gaji Pokok <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" name="gaji_pokok" id="karyawanGaji" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" id="karyawanTanggalMasuk"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. HP</label>
                    <input type="text" name="no_hp" id="karyawanNoHp" placeholder="cth: 08123..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div id="statusAktifWrapper" class="hidden">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="karyawanIsActive" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Karyawan Aktif</span>
                    </label>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeKaryawanModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitKaryawan" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitKaryawanText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_KARYAWAN = document.querySelector('meta[name="csrf-token"]').content;

    document.addEventListener('DOMContentLoaded', function () {
        const inp = document.getElementById('karyawanGaji');
        if (inp) inp.addEventListener('input', function () {
            let v = this.value.replace(/[^0-9]/g, '');
            this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
        });
    });

    function openKaryawanModal() {
        const form = document.getElementById('karyawanForm');
        form.reset();
        document.getElementById('modalErrorKaryawan').classList.add('hidden');
        document.getElementById('karyawanMode').value = 'create';
        document.getElementById('karyawanId').value = '';
        document.getElementById('karyawanModalTitle').textContent = 'Tambah Karyawan';
        document.getElementById('karyawanModalIcon').setAttribute('icon', 'mdi:account-plus');
        document.getElementById('btnSubmitKaryawanText').textContent = 'Simpan';
        document.getElementById('statusAktifWrapper').classList.add('hidden');

        document.getElementById('karyawanModal').classList.remove('hidden');
        document.getElementById('karyawanModal').classList.add('flex');
    }

    function openEditKaryawanModal(id, nama, storeId, gaji, tanggalMasuk, noHp, isActive) {
        const form = document.getElementById('karyawanForm');
        form.reset();
        document.getElementById('modalErrorKaryawan').classList.add('hidden');
        document.getElementById('karyawanMode').value = 'edit';
        document.getElementById('karyawanId').value = id;
        document.getElementById('karyawanModalTitle').textContent = 'Edit Karyawan';
        document.getElementById('karyawanModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitKaryawanText').textContent = 'Simpan Perubahan';
        document.getElementById('statusAktifWrapper').classList.remove('hidden');

        document.getElementById('karyawanNama').value = nama;
        document.getElementById('karyawanStoreId').value = storeId || '';
        document.getElementById('karyawanGaji').value = gaji > 0 ? new Intl.NumberFormat('id-ID').format(gaji) : '';
        document.getElementById('karyawanTanggalMasuk').value = tanggalMasuk || '';
        document.getElementById('karyawanNoHp').value = noHp || '';
        document.getElementById('karyawanIsActive').checked = isActive == 1;

        document.getElementById('karyawanModal').classList.remove('hidden');
        document.getElementById('karyawanModal').classList.add('flex');
    }

    function closeKaryawanModal() {
        document.getElementById('karyawanModal').classList.add('hidden');
        document.getElementById('karyawanModal').classList.remove('flex');
    }

    async function submitKaryawanForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitKaryawan');
        const btnText = document.getElementById('btnSubmitKaryawanText');
        const errDiv = document.getElementById('modalErrorKaryawan');
        const mode = document.getElementById('karyawanMode').value;
        const id = document.getElementById('karyawanId').value;

        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(form);
        formData.set('gaji_pokok', document.getElementById('karyawanGaji').value.replace(/[^0-9]/g, ''));

        if (mode === 'edit') {
            const isActiveChecked = document.getElementById('karyawanIsActive').checked ? 1 : 0;
            formData.set('is_active', isActiveChecked);
        }

        let url = "{{ route('karyawan.store-ajax') }}";
        if (mode === 'edit') {
            url = `/karyawan/${id}/ajax`;
            formData.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_KARYAWAN,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Gagal.'));
            closeKaryawanModal();
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btnText.textContent = originalText;
        }
    }

    async function deleteKaryawan(id, nama) {
        if (!confirm(`Yakin hapus karyawan "${nama}"?`)) return;
        try {
            const res = await fetch(`/karyawan/${id}/ajax`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_KARYAWAN,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeKaryawanModal(); });
</script>
@endsection