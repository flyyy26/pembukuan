@extends('layouts.app')

@section('title', 'Transaksi Gaji Karyawan')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:cash-multiple" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Transaksi Gaji</h2>
                <p class="text-sm text-gray-500">Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <button type="button" onclick="openGenerateModal()" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:autorenew" class="text-lg"></iconify-icon>
            Generate Gaji
        </button>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Periode (Bulan)</label>
                <input type="month" name="periode" value="{{ $periode }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="dibayar" {{ $status === 'dibayar' ? 'selected' : '' }}>Dibayar</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($storeId !== 'all' || $status !== 'all' || $periode !== now()->format('Y-m'))
                    <a href="{{ route('gaji.index') }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:wallet" class="text-indigo-600 text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-1">Total Gaji</div>
            <div class="text-xl font-bold text-gray-800">Rp {{ number_format($summary['total_gaji'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $summary['jumlah_karyawan'] }} karyawan</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:check-circle" class="text-green-600 text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-1">Sudah Dibayar</div>
            <div class="text-xl font-bold text-green-600">Rp {{ number_format($summary['total_dibayar'], 0, ',', '.') }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:clock-alert" class="text-orange-600 text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-1">Belum Dibayar</div>
            <div class="text-xl font-bold text-orange-600">Rp {{ number_format($summary['total_draft'], 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- TABEL GAJI --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Rincian Gaji</h3>
            <div class="text-xs text-gray-500">{{ $gajiList->count() }} data</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Karyawan</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Toko</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Gaji Pokok</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Bonus</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Potongan</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($gajiList as $g)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $g->karyawan->nama ?? '-' }}</td>
                            <td class="py-3 px-4 text-xs text-gray-600">
                                <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                {{ $g->karyawan->store->nama_toko ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right text-gray-700">Rp {{ number_format($g->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-emerald-600">Rp {{ number_format($g->bonus, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-red-600">Rp {{ number_format($g->potongan, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-bold text-gray-900">Rp {{ number_format($g->total_gaji, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($g->status === 'dibayar')
                                    <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">
                                        <iconify-icon icon="mdi:check-circle" class="text-sm"></iconify-icon> Dibayar
                                    </span>
                                    @if($g->tanggal_bayar)
                                        <div class="text-[10px] text-gray-400 mt-1">{{ $g->tanggal_bayar->format('d/m/Y') }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium">
                                        <iconify-icon icon="mdi:clock-alert" class="text-sm"></iconify-icon> Draft
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1 flex-wrap justify-center">
                                    @if($g->status === 'draft')
                                        <button type="button"
                                                onclick="openEditGajiModal({{ $g->id }}, '{{ addslashes($g->karyawan->nama ?? '') }}', {{ $g->bonus }}, {{ $g->potongan }}, '{{ $g->metode_bayar }}')"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                        </button>
                                        <button type="button"
                                                onclick="bayarGaji({{ $g->id }}, '{{ addslashes($g->karyawan->nama ?? '') }}', {{ $g->total_gaji }})"
                                                class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon> Bayar
                                        </button>
                                        <button type="button"
                                                onclick="deleteGaji({{ $g->id }})"
                                                class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Lunas</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-500">
                                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                                Belum ada data gaji untuk periode ini. Klik "Generate Gaji" untuk membuat draft.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL GENERATE --}}
<div id="generateModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeGenerateModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-indigo-50">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:autorenew" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Generate Gaji</h3>
            </div>
            <button type="button" onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="generateForm" onsubmit="submitGenerate(event)">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorGenerate"></div>

                <div class="bg-indigo-50 rounded-lg p-3 text-xs text-indigo-800 flex items-start gap-2">
                    <iconify-icon icon="mdi:information" class="text-base mt-0.5"></iconify-icon>
                    <div>
                        Draft gaji akan dibuat untuk <strong>semua karyawan aktif</strong> di periode ini.
                        Karyawan yang sudah punya gaji di periode ini akan dilewati.
                        <br>Potongan otomatis mengambil dari <strong>laporan minus yang belum dipotong</strong>.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Periode (Bulan) <span class="text-red-500">*</span></label>
                    <input type="month" name="periode" id="generatePeriode" value="{{ $periode }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                    <select name="store_id" id="generateStoreId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">Semua Toko</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitGenerate" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:autorenew" class="text-lg"></iconify-icon> Generate
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT GAJI --}}
<div id="gajiModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeGajiModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:pencil" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Edit Gaji <span id="gajiKaryawanNama" class="text-blue-600"></span></h3>
            </div>
            <button type="button" onclick="closeGajiModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="gajiForm" onsubmit="submitEditGaji(event)">
            @csrf
            <input type="hidden" id="gajiId" value="">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorGaji"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bonus</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputBonus" name="bonus" placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Potongan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputPotongan" name="potongan" placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Metode Bayar</label>
                    <select id="inputMetode" name="metode_bayar" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="tunai">Tunai</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeGajiModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitGaji" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_GAJI = document.querySelector('meta[name="csrf-token"]').content;

    // Format input bonus & potongan
    document.addEventListener('DOMContentLoaded', function () {
        ['inputBonus', 'inputPotongan'].forEach(id => {
            const inp = document.getElementById(id);
            if (inp) inp.addEventListener('input', function () {
                let v = this.value.replace(/[^0-9]/g, '');
                this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
            });
        });
    });

    // ===== GENERATE =====
    function openGenerateModal() {
        document.getElementById('generateForm').reset();
        document.getElementById('modalErrorGenerate').classList.add('hidden');
        document.getElementById('generatePeriode').value = "{{ $periode }}";
        document.getElementById('generateModal').classList.remove('hidden');
        document.getElementById('generateModal').classList.add('flex');
    }

    function closeGenerateModal() {
        document.getElementById('generateModal').classList.add('hidden');
        document.getElementById('generateModal').classList.remove('flex');
    }

    async function submitGenerate(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitGenerate');
        const errDiv = document.getElementById('modalErrorGenerate');
        btn.disabled = true;
        btn.innerHTML = 'Memproses...';
        errDiv.classList.add('hidden');

        try {
            const res = await fetch("{{ route('gaji.generate') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_GAJI,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            alert(data.message);
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:autorenew" class="text-lg"></iconify-icon> Generate';
        }
    }

    // ===== EDIT GAJI =====
    function openEditGajiModal(id, nama, bonus, potongan, metode) {
        document.getElementById('gajiForm').reset();
        document.getElementById('modalErrorGaji').classList.add('hidden');
        document.getElementById('gajiId').value = id;
        document.getElementById('gajiKaryawanNama').textContent = nama;
        document.getElementById('inputBonus').value = bonus > 0 ? new Intl.NumberFormat('id-ID').format(bonus) : '';
        document.getElementById('inputPotongan').value = potongan > 0 ? new Intl.NumberFormat('id-ID').format(potongan) : '';
        document.getElementById('inputMetode').value = metode || 'tunai';
        document.getElementById('gajiModal').classList.remove('hidden');
        document.getElementById('gajiModal').classList.add('flex');
    }

    function closeGajiModal() {
        document.getElementById('gajiModal').classList.add('hidden');
        document.getElementById('gajiModal').classList.remove('flex');
    }

    async function submitEditGaji(e) {
        e.preventDefault();
        const id = document.getElementById('gajiId').value;
        const btn = document.getElementById('btnSubmitGaji');
        const errDiv = document.getElementById('modalErrorGaji');
        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN_GAJI);
        formData.append('_method', 'PUT');
        formData.append('bonus', document.getElementById('inputBonus').value.replace(/[^0-9]/g, ''));
        formData.append('potongan', document.getElementById('inputPotongan').value.replace(/[^0-9]/g, ''));
        formData.append('metode_bayar', document.getElementById('inputMetode').value);

        try {
            const res = await fetch(`/gaji/${id}/ajax`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_GAJI, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            closeGajiModal();
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan';
        }
    }

    // ===== BAYAR =====
    async function bayarGaji(id, nama, total) {
        if (!confirm(`Bayar gaji ${nama} sebesar Rp ${new Intl.NumberFormat('id-ID').format(total)}?`)) return;
        try {
            const res = await fetch(`/gaji/${id}/bayar`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_GAJI, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    // ===== DELETE =====
    async function deleteGaji(id) {
        if (!confirm('Hapus draft gaji ini?')) return;
        try {
            const res = await fetch(`/gaji/${id}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_GAJI, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeGenerateModal(); closeGajiModal(); }
    });
</script>
@endsection