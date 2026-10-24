@extends('layouts.app')

@section('title', 'Laporan Hutang')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:file-document-outline" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Hutang</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan hutang kepada pihak lain</p>
            </div>
        </div>

        <button type="button" onclick="openHutangModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Hutang
        </button>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Nama / Keterangan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $status !== 'all' || $from || $to)
                    <a href="{{ route('hutang.index') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Total Hutang (Sesuai Filter)</div>
            <div class="text-sm sm:text-lg font-bold text-gray-800 break-words">Rp {{ number_format($summary['total_hutang'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Belum Lunas</div>
            <div class="text-sm sm:text-lg font-bold text-red-600 break-words">Rp {{ number_format($summary['total_belum_lunas'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Sudah Lunas</div>
            <div class="text-sm sm:text-lg font-bold text-emerald-600 break-words">Rp {{ number_format($summary['total_lunas'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="text-[10px] sm:text-xs text-gray-500 mb-1">Jumlah Data</div>
            <div class="text-sm sm:text-lg font-bold text-gray-800">{{ $summary['jumlah_data'] }} <span class="text-xs font-normal text-gray-500">Data</span></div>
        </div>
    </div>

    {{-- DATA HUTANG --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Hutang</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $summary['jumlah_data'] }} Data</div>
        </div>

        @if($hutangList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:file-document-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada catatan hutang.</span>
            </div>
        @else
            {{-- TAMPILAN TABEL (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nama Pemberi Hutang</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Keterangan</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nominal</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Status</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-40 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($hutangList as $h)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-xs text-gray-700 whitespace-nowrap">{{ $h->tanggal->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $h->nama_pemberi_hutang }}</td>
                                <td class="py-3 px-4 text-gray-700 text-xs">{{ $h->keterangan ?? '-' }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800 whitespace-nowrap">Rp {{ number_format($h->nominal, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($h->is_lunas)
                                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:check-circle" class="text-sm"></iconify-icon> Lunas
                                        </span>
                                        @if($h->tanggal_lunas)
                                            <div class="text-[10px] text-gray-400 mt-1">{{ $h->tanggal_lunas->format('d/m/Y') }}</div>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:clock-alert" class="text-sm"></iconify-icon> Belum Lunas
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        @if(!$h->is_lunas)
                                            <button type="button" onclick="bayarHutang({{ $h->id }}, '{{ addslashes($h->nama_pemberi_hutang) }}')"
                                                    class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-emerald-200">
                                                <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon> Lunas
                                            </button>
                                        @endif
                                        <button type="button" onclick="openEditHutangModal({{ $h->id }}, '{{ $h->tanggal->format('Y-m-d') }}', '{{ addslashes($h->nama_pemberi_hutang) }}', {{ $h->nominal }}, '{{ addslashes($h->keterangan ?? '') }}')"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-blue-200">
                                            <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                        </button>
                                        <button type="button" onclick="deleteHutang({{ $h->id }}, '{{ addslashes($h->nama_pemberi_hutang) }}')"
                                                class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-red-200">
                                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
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
                @foreach($hutangList as $h)
                    <div class="p-4 space-y-2 text-xs">
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold text-gray-800 text-sm">{{ $h->nama_pemberi_hutang }}</div>
                                <div class="flex items-center gap-1 text-[10px] text-gray-500 mt-0.5">
                                    <iconify-icon icon="mdi:calendar" class="text-xs"></iconify-icon>
                                    {{ $h->tanggal->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                @if($h->is_lunas)
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">
                                        <iconify-icon icon="mdi:check-circle" class="text-xs"></iconify-icon> Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">
                                        <iconify-icon icon="mdi:clock-alert" class="text-xs"></iconify-icon> Belum Lunas
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($h->keterangan)
                            <div class="bg-gray-50 p-2 rounded-lg mt-2 text-gray-700">
                                {{ $h->keterangan }}
                            </div>
                        @endif

                        <div class="flex justify-between items-center pt-1 mt-1 border-t border-gray-100">
                            <span class="font-bold text-gray-800">Nominal Hutang</span>
                            <span class="font-bold text-red-600 text-sm">Rp {{ number_format($h->nominal, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex gap-2 pt-2">
                            @if(!$h->is_lunas)
                                <button type="button" onclick="bayarHutang({{ $h->id }}, '{{ addslashes($h->nama_pemberi_hutang) }}')"
                                        class="flex-1 inline-flex justify-center items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon> Lunas
                                </button>
                            @endif
                            <button type="button" onclick="openEditHutangModal({{ $h->id }}, '{{ $h->tanggal->format('Y-m-d') }}', '{{ addslashes($h->nama_pemberi_hutang) }}', {{ $h->nominal }}, '{{ addslashes($h->keterangan ?? '') }}')"
                                    class="flex-1 inline-flex justify-center items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                            </button>
                            <button type="button" onclick="deleteHutang({{ $h->id }}, '{{ addslashes($h->nama_pemberi_hutang) }}')"
                                    class="flex-none inline-flex justify-center items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded text-xs font-medium transition">
                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- MODAL HUTANG --}}
<div id="hutangModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeHutangModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="hutangModalIcon" icon="mdi:plus-circle" class="text-red-600 text-lg"></iconify-icon>
                <h3 id="hutangModalTitle" class="font-bold text-gray-800">Tambah Hutang</h3>
            </div>
            <button type="button" onclick="closeHutangModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="hutangForm" onsubmit="submitHutangForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="hutangId" value="">
            <input type="hidden" id="hutangMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorHutang"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="hutangTanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pemberi Hutang <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_pemberi_hutang" id="hutangNama" required placeholder="cth: Setoran BRI"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="hutangNominal" name="nominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="hutangKeterangan" rows="2" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0 mt-auto">
                <button type="button" onclick="closeHutangModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitHutang" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitHutangText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_HUTANG = document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('hutangNominal')?.addEventListener('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
    });

    function openHutangModal() {
        document.getElementById('hutangForm').reset();
        document.getElementById('modalErrorHutang').classList.add('hidden');
        document.getElementById('hutangMode').value = 'create';
        document.getElementById('hutangId').value = '';
        document.getElementById('hutangModalTitle').textContent = 'Tambah Hutang';
        document.getElementById('hutangModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitHutangText').textContent = 'Simpan';
        document.getElementById('hutangTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('hutangModal').classList.remove('hidden');
        document.getElementById('hutangModal').classList.add('flex');
    }

    function openEditHutangModal(id, tanggal, nama, nominal, keterangan) {
        document.getElementById('hutangForm').reset();
        document.getElementById('modalErrorHutang').classList.add('hidden');
        document.getElementById('hutangMode').value = 'edit';
        document.getElementById('hutangId').value = id;
        document.getElementById('hutangModalTitle').textContent = 'Edit Hutang';
        document.getElementById('hutangModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitHutangText').textContent = 'Simpan Perubahan';
        document.getElementById('hutangTanggal').value = tanggal;
        document.getElementById('hutangNama').value = nama;
        document.getElementById('hutangNominal').value = new Intl.NumberFormat('id-ID').format(nominal);
        document.getElementById('hutangKeterangan').value = keterangan;
        document.getElementById('hutangModal').classList.remove('hidden');
        document.getElementById('hutangModal').classList.add('flex');
    }

    function closeHutangModal() {
        document.getElementById('hutangModal').classList.add('hidden');
        document.getElementById('hutangModal').classList.remove('flex');
    }

    async function submitHutangForm(e) {
        e.preventDefault();
        const mode = document.getElementById('hutangMode').value;
        const id = document.getElementById('hutangId').value;
        const btn = document.getElementById('btnSubmitHutang');
        const btnText = document.getElementById('btnSubmitHutangText');
        const errDiv = document.getElementById('modalErrorHutang');
        const orig = btnText.textContent;
        btn.disabled = true; btnText.textContent = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(e.target);
        formData.set('nominal', document.getElementById('hutangNominal').value.replace(/[^0-9]/g, ''));

        let url = "{{ route('hutang.store-ajax') }}";
        if (mode === 'edit') {
            url = `/hutang/${id}/ajax`;
            formData.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_HUTANG, 'Accept': 'application/json' }, body: formData });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            closeHutangModal();
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false; btnText.textContent = orig;
        }
    }

    async function bayarHutang(id, nama) {
        if (!confirm(`Tandai hutang kepada "${nama}" sudah LUNAS?`)) return;
        try {
            const res = await fetch(`/hutang/${id}/bayar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_HUTANG, 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) { alert('Gagal: ' + err.message); }
    }

    async function deleteHutang(id, nama) {
        if (!confirm(`Yakin hapus hutang kepada "${nama}"?`)) return;
        try {
            const res = await fetch(`/hutang/${id}/ajax`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_HUTANG, 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) { alert('Gagal: ' + err.message); }
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeHutangModal(); });
</script>
@endsection