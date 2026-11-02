@extends('layouts.app')

@section('title', 'Data Kasbon Konveksi')

@section('content')
<div class="w-full min-w-0">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:cash-remove" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Kasbon</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan kasbon (pinjaman) karyawan konveksi</p>
            </div>
        </div>

        <button type="button" onclick="openKasbonModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon> Tambah Kasbon
        </button>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan</label>
                <select name="karyawan_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-600 focus:outline-none">
                    <option value="all" {{ $karyawanId === 'all' ? 'selected' : '' }}>Semua Karyawan</option>
                    @foreach($karyawanList as $k)
                        <option value="{{ $k->id }}" {{ $karyawanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                <select name="bulan" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-600 focus:outline-none">
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari keterangan / nama..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-orange-600 focus:outline-none">
                </div>
            </div>

            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-orange-600 text-white px-5 py-2 rounded-lg hover:bg-orange-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $karyawanId !== 'all' || $bulanFilter !== now()->format('Y-m'))
                    <a href="{{ route('konveksi.kasbon') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 gap-2 sm:gap-4 mb-6 w-full">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:cash-remove" class="text-orange-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Total Kasbon</div>
            <div class="text-xs sm:text-base font-bold text-orange-600 break-words">
                <span class="text-[9px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_kasbon'], 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:format-list-bulleted" class="text-blue-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Jumlah Data</div>
            <div class="text-xs sm:text-base font-bold text-blue-600">{{ $summary['jumlah_data'] }}</div>
        </div>
    </div>

    {{-- TABEL DATA KASBON --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Kasbon</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $kasbonList->count() }} Data</div>
        </div>

        @if($kasbonList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada data kasbon.</span>
            </div>
        @else
            <div class="w-full overflow-x-auto block">
                <table class="w-full text-xs sm:text-sm text-left min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Keterangan</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Nominal</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Status</th> 
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($kasbonList as $k)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">{{ $k->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $k->karyawan->nama ?? '-' }}</td>
                                <td class="py-2 px-3 text-xs text-gray-600">{{ $k->keterangan ?? '-' }}</td>
                                <td class="py-2 px-3 text-right font-bold text-orange-600 whitespace-nowrap">
                                    Rp {{ number_format($k->status === 'lunas' ? $k->nominal_awal : $k->nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    @if($k->status === 'lunas')
                                        <span class="inline-flex items-center gap-1 text-[10px] bg-green-100 text-green-700 border border-green-200 px-2 py-1 rounded font-semibold">
                                            <iconify-icon icon="mdi:check-circle" class="text-xs"></iconify-icon>
                                            Lunas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] bg-orange-100 text-orange-700 border border-orange-200 px-2 py-1 rounded font-semibold">
                                            <iconify-icon icon="mdi:clock-outline" class="text-xs"></iconify-icon>
                                            Belum Lunas
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-center">
                                    @if($k->status !== 'lunas')
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            <button type="button" onclick="openBayarKasbonModal({{ $k->id }}, {{ $k->nominal }})" class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition border border-emerald-200" title="Bayar Kasbon">
                                                <iconify-icon icon="mdi:cash-plus" class="text-sm"></iconify-icon> Bayar
                                            </button>
                                            <button type="button" onclick="deleteKasbon({{ $k->id }})" class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition border border-red-200" title="Hapus">
                                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH KASBON --}}
<div id="kasbonModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeKasbonModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-orange-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:plus-circle" class="text-orange-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Tambah Kasbon</h3>
            </div>
            <button type="button" onclick="closeKasbonModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="kasbonForm" onsubmit="submitKasbon(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorKasbon"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan <span class="text-red-500">*</span></label>
                    <select name="konveksi_karyawan_id" id="kasbonKaryawanId" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($karyawanList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Field Nominal yang sudah ada --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" name="nominal" id="kasbonNominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                {{-- ✅ Field Sisa Kasbon Berjalan (Readonly) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sisa Kasbon Berjalan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="kasbonSisa" readonly placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold bg-gray-100 cursor-not-allowed focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="kasbonTanggal" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" id="kasbonKeterangan" placeholder="Opsional"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeKasbonModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitKasbon" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL BAYAR KASBON --}}
<div id="bayarKasbonModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBayarKasbonModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-emerald-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cash-check" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Bayar Kasbon</h3>
            </div>
            <button type="button" onclick="closeBayarKasbonModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="bayarKasbonForm" onsubmit="submitBayarKasbon(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="bayarKasbonId" value="">
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorBayarKasbon"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sisa Kasbon Saat Ini</label>
                    <input type="text" id="bayarKasbonSisa" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold bg-gray-100 cursor-not-allowed focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal Pembayaran <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" name="nominal_bayar" id="bayarKasbonNominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Bayar <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_bayar" id="bayarKasbonTanggal" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeBayarKasbonModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitBayarKasbon" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let currentTotalKasbon = 0;

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function parseAngka(str) {
        return parseInt(String(str).replace(/[^0-9]/g, '')) || 0;
    }

    function openKasbonModal() {
        document.getElementById('kasbonForm').reset();
        document.getElementById('modalErrorKasbon').classList.add('hidden');
        document.getElementById('kasbonTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('kasbonModal').classList.remove('hidden');
        document.getElementById('kasbonModal').classList.add('flex');
    }

    function closeKasbonModal() {
        document.getElementById('kasbonModal').classList.add('hidden');
        document.getElementById('kasbonModal').classList.remove('flex');
    }

    async function submitKasbon(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitKasbon');
        const errDiv = document.getElementById('modalErrorKasbon');
        const orig = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(e.target);
        formData.set('nominal', parseAngka(document.getElementById('kasbonNominal').value));

        try {
            const res = await fetch("{{ route('konveksi.kasbon.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            
            // ✅ Buka PDF otomatis setelah simpan
            if (data.kasbon_id) {
                window.open(`/konveksi/kasbon/${data.kasbon_id}/invoice-pdf`, '_blank');
            }
            
            setTimeout(() => window.location.reload(), 1000);
            
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }

    async function deleteKasbon(id) {
        if (!confirm('Yakin hapus kasbon ini?')) return;
        try {
            const res = await fetch(`/konveksi/kasbon/${id}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    document.getElementById('kasbonKaryawanId')?.addEventListener('change', async function () {
        const karyawanId = this.value;
        const sisaInput = document.getElementById('kasbonSisa');
        const nominalInput = document.getElementById('kasbonNominal');

        // Reset input nominal dan sisa jika karyawan di-reset
        if (!karyawanId) {
            currentTotalKasbon = 0;
            sisaInput.value = '';
            nominalInput.value = '';
            return;
        }

        sisaInput.value = 'Memuat...';

        try {
            // Panggil API untuk mendapatkan total kasbon karyawan tersebut
            const res = await fetch(`/konveksi/kasbon/sisa-ajax?karyawan_id=${karyawanId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            currentTotalKasbon = data.total;
            
            // Tampilkan sisa awal (sebelum ditambah inputan baru)
            sisaInput.value = formatRupiah(currentTotalKasbon);
            
        } catch (err) {
            sisaInput.value = '0';
            currentTotalKasbon = 0;
            console.error('Gagal mengambil data sisa kasbon', err);
        }
    });

    // 2. Event saat Nominal diketik (agar angka sisa langsung update)
    document.getElementById('kasbonNominal')?.addEventListener('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? formatRupiah(v) : '';
        
        // Hitung total (Sisa Kasbon sebelumnya + Nominal yang baru diinput)
        let nominalBaru = parseInt(v) || 0;
        let totalSisa = currentTotalKasbon + nominalBaru;
        
        document.getElementById('kasbonSisa').value = formatRupiah(totalSisa);
    });

        function openBayarKasbonModal(id, sisaNominal) {
        document.getElementById('bayarKasbonForm').reset();
        document.getElementById('modalErrorBayarKasbon').classList.add('hidden');
        document.getElementById('bayarKasbonId').value = id;
        document.getElementById('bayarKasbonSisa').value = 'Rp ' + formatRupiah(sisaNominal);
        document.getElementById('bayarKasbonTanggal').value = new Date().toISOString().slice(0, 10);
        
        document.getElementById('bayarKasbonModal').classList.remove('hidden');
        document.getElementById('bayarKasbonModal').classList.add('flex');
    }

    function closeBayarKasbonModal() {
        document.getElementById('bayarKasbonModal').classList.add('hidden');
        document.getElementById('bayarKasbonModal').classList.remove('flex');
    }

    async function submitBayarKasbon(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitBayarKasbon');
        const errDiv = document.getElementById('modalErrorBayarKasbon');
        const id = document.getElementById('bayarKasbonId').value;
        const orig = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(e.target);
        // Pastikan nominal dikirim sebagai angka murni
        formData.set('nominal_bayar', parseAngka(document.getElementById('bayarKasbonNominal').value));

        try {
            const res = await fetch(`/konveksi/kasbon/${id}/bayar-ajax`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            
            alert(data.message);
            window.location.reload();
            
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }

    // Format input nominal bayar kasbon
    document.getElementById('bayarKasbonNominal')?.addEventListener('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? formatRupiah(v) : '';
    });
</script>
@endsection