@extends('layouts.app')

@section('title', 'Laporan Live TikTok')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:video-outline" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Live TikTok</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan performa host & produk saat live</p>
            </div>
        </div>
        
        <button type="button" onclick="openLiveModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Sesi Live
        </button>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Host / Keterangan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama host..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-gray-900 text-white px-5 py-2 rounded-lg hover:bg-gray-800 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($from || $to || $search || $storeId !== 'all')
                    <a href="{{ route('tiktok.live.index') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- DATA LIST --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Riwayat Live</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $liveSessions->count() }} Sesi</div>
        </div>

        @if($liveSessions->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:video-off-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada data live untuk filter ini.</span>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($liveSessions as $live)
                    <div class="p-4 sm:p-5 hover:bg-gray-50 transition">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                            
                            {{-- KIRI: INFO UTAMA --}}
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="font-bold text-gray-800 text-base">{{ $live->karyawan->nama ?? 'Host Tidak Diketahui' }}</div>
                                    <span class="text-[10px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded border">{{ $live->store->nama_toko ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span class="flex items-center gap-1"><iconify-icon icon="mdi:calendar"></iconify-icon> {{ $live->tanggal->format('d M Y') }}</span>
                                    <span class="flex items-center gap-1"><iconify-icon icon="mdi:clock-outline"></iconify-icon> {{ $live->waktu_mulai }} - {{ $live->waktu_selesai }}</span>
                                </div>
                                @if($live->keterangan)
                                    <div class="text-xs text-gray-500 mt-1 italic">"{{ $live->keterangan }}"</div>
                                @endif
                            </div>

                            {{-- TENGAH: PRODUK --}}
                            <div class="flex-1 w-full sm:w-auto">
                                @if($live->products->isNotEmpty())
                                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-2 text-xs">
                                        <div class="font-semibold text-gray-600 mb-1 border-b pb-1">Produk Terjual:</div>
                                        <ul class="space-y-0.5 max-h-24 overflow-y-auto pr-1">
                                            @foreach($live->products as $prod)
                                                <li class="flex justify-between gap-2 text-gray-700">
                                                    <span class="truncate">{{ $prod->nama_produk }} @if($prod->ukuran) ({{ $prod->ukuran }}) @endif</span>
                                                    <span class="font-bold whitespace-nowrap">{{ $prod->qty }}x</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400 italic">Tidak ada rincian produk</div>
                                @endif
                            </div>

                            {{-- KANAN: TOTAL TRANSAKSI & AKSI --}}
                            <div class="flex flex-col items-end gap-2 w-full sm:w-auto">
                                <div class="text-right">
                                    <div class="text-[10px] text-gray-500 mb-0.5">Total Transaksi</div>
                                    <div class="font-bold text-gray-800 text-lg">
                                        {{ number_format($live->total_transaksi) }} <span class="text-xs font-normal text-gray-500">Trx</span>
                                    </div>
                                </div>
                                <div class="flex gap-1 mt-1">
                                    <button type="button" 
                                            onclick='openEditLiveModal(@json($live))'
                                            class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-100 px-2 py-1 rounded text-xs font-medium transition">
                                        <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                    </button>
                                    <button type="button" onclick="deleteLive({{ $live->id }})" 
                                            class="inline-flex items-center gap-1 text-red-600 hover:bg-red-100 px-2 py-1 rounded text-xs font-medium transition">
                                        <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- MODAL LIVE --}}
<div id="liveModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeLiveModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
        
        {{-- Header Modal --}}
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="liveModalIcon" icon="mdi:plus-circle" class="text-gray-800 text-lg"></iconify-icon>
                <h3 id="liveModalTitle" class="font-bold text-gray-800">Tambah Sesi Live</h3>
            </div>
            <button type="button" onclick="closeLiveModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        {{-- Form --}}
        <form id="liveForm" onsubmit="submitLiveForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="liveMode" value="create">
            <input type="hidden" id="liveId" value="">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorLive"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Store --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Toko TikTok</label>
                        <select name="store_id" id="liveStoreId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                            <option value="">-- Tanpa Toko --</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_toko }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Host --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Host (Karyawan) <span class="text-red-500">*</span></label>
                        <select name="karyawan_id" id="liveKaryawanId" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                            <option value="">-- Pilih Host --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="liveTanggal" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_mulai" id="liveWaktuMulai" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_selesai" id="liveWaktuSelesai" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Total Transaksi (Jumlah) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium"><iconify-icon icon="mdi:receipt-text-outline"></iconify-icon></span>
                        </div>
                        <input type="number" name="total_transaksi" id="liveTotalTransaksi" required placeholder="cth: 50" min="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-gray-800 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" id="liveKeterangan" placeholder="cth: Live malam minggu" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:outline-none">
                </div>

                {{-- DYNAMIC PRODUCTS --}}
                <div class="border-t border-gray-100 pt-3">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700">Rincian Produk Terjual</label>
                        <button type="button" onclick="addProductRow()" class="inline-flex items-center gap-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded transition">
                            <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah Baris
                        </button>
                    </div>
                    <div id="productContainer" class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <!-- Baris produk akan di-generate oleh JS di sini -->
                    </div>
                    <div id="emptyProductMsg" class="text-xs text-gray-400 italic text-center py-2">Belum ada produk ditambahkan.</div>
                </div>

            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeLiveModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitLive" class="bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitLiveText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_LIVE = document.querySelector('meta[name="csrf-token"]').content;

    // Dynamic Product Form
    let productIndex = 0;
    function addProductRow(data = null) {
        document.getElementById('emptyProductMsg').classList.add('hidden');
        const container = document.getElementById('productContainer');
        const id = `prod-${productIndex}`;
        
        const html = `
            <div id="${id}" class="flex items-start gap-2 bg-gray-50 p-2 rounded border border-gray-200">
                <div class="flex-1 space-y-1">
                    <input type="text" name="produk[${productIndex}][nama_produk]" value="${data ? data.nama_produk : ''}" placeholder="Nama Produk" required class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-gray-800 focus:outline-none">
                    <input type="text" name="produk[${productIndex}][ukuran]" value="${data && data.ukuran ? data.ukuran : ''}" placeholder="Ukuran (cth: XL)" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-gray-800 focus:outline-none">
                </div>
                <div class="w-20">
                    <input type="number" name="produk[${productIndex}][qty]" value="${data ? data.qty : ''}" placeholder="Qty" required min="1" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-gray-800 focus:outline-none">
                </div>
                <button type="button" onclick="removeProductRow('${id}')" class="text-red-500 hover:text-red-700 p-1 mt-0.5">
                    <iconify-icon icon="mdi:close-circle" class="text-lg"></iconify-icon>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        productIndex++;
    }

    function removeProductRow(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
        if (document.getElementById('productContainer').children.length === 0) {
            document.getElementById('emptyProductMsg').classList.remove('hidden');
        }
    }

    // Modal Logic
    function openLiveModal() {
        document.getElementById('liveForm').reset();
        document.getElementById('modalErrorLive').classList.add('hidden');
        document.getElementById('liveMode').value = 'create';
        document.getElementById('liveId').value = '';
        document.getElementById('liveModalTitle').textContent = 'Tambah Sesi Live';
        document.getElementById('btnSubmitLiveText').textContent = 'Simpan';
        
        document.getElementById('liveTanggal').value = new Date().toISOString().slice(0, 10);
        
        // Reset produk
        document.getElementById('productContainer').innerHTML = '';
        productIndex = 0;
        document.getElementById('emptyProductMsg').classList.remove('hidden');

        filterHostByStore();

        document.getElementById('liveModal').classList.remove('hidden');
        document.getElementById('liveModal').classList.add('flex');
    }

    function openEditLiveModal(data) {
        document.getElementById('liveForm').reset();
        document.getElementById('modalErrorLive').classList.add('hidden');
        document.getElementById('liveMode').value = 'edit';
        document.getElementById('liveId').value = data.id;
        document.getElementById('liveModalTitle').textContent = 'Edit Sesi Live';
        document.getElementById('btnSubmitLiveText').textContent = 'Simpan Perubahan';

        document.getElementById('liveStoreId').value = data.store_id || '';
        filterHostByStore();
        document.getElementById('liveKaryawanId').value = data.karyawan_id || '';
        document.getElementById('liveTanggal').value = data.tanggal.split('T')[0]; // handle ISO string
        document.getElementById('liveWaktuMulai').value = data.waktu_mulai;
        document.getElementById('liveWaktuSelesai').value = data.waktu_selesai;
        document.getElementById('liveTotalTransaksi').value = new Intl.NumberFormat('id-ID').format(data.total_transaksi);
        document.getElementById('liveKeterangan').value = data.keterangan || '';

        // Populate Products
        document.getElementById('productContainer').innerHTML = '';
        productIndex = 0;
        if (data.products && data.products.length > 0) {
            document.getElementById('emptyProductMsg').classList.add('hidden');
            data.products.forEach(p => addProductRow(p));
        } else {
            document.getElementById('emptyProductMsg').classList.remove('hidden');
        }

        document.getElementById('liveModal').classList.remove('hidden');
        document.getElementById('liveModal').classList.add('flex');
    }

    function closeLiveModal() {
        document.getElementById('liveModal').classList.add('hidden');
        document.getElementById('liveModal').classList.remove('flex');
    }

    // Submit Form
    async function submitLiveForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitLive');
        const btnText = document.getElementById('btnSubmitLiveText');
        const errDiv = document.getElementById('modalErrorLive');
        const mode = document.getElementById('liveMode').value;
        const id = document.getElementById('liveId').value;

        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(form);

        let url = "{{ route('tiktok.live.store-ajax') }}";
        if (mode === 'edit') {
            url = `/tiktok/live/${id}/ajax`;
            formData.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_LIVE, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            
            if (!res.ok) {
                // Handle validation errors if any
                throw new Error(data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Gagal.'));
            }
            
            closeLiveModal();
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btnText.textContent = originalText;
        }
    }

    async function deleteLive(id) {
        if (!confirm('Yakin hapus sesi live ini? Semua rincian produk di dalamnya akan terhapus.')) return;
        try {
            const res = await fetch(`/tiktok/live/${id}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_LIVE, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLiveModal(); });
</script>

<script>
        // ===== FILTER HOST BERDASARKAN TOKO =====
    const ALL_KARYAWANS = @json($karyawans->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama, 'store_id' => $k->store_id]));
    
    function filterHostByStore() {
        const storeId = document.getElementById('liveStoreId').value;
        const hostSelect = document.getElementById('liveKaryawanId');
        const currentValue = hostSelect.value;
        
        hostSelect.innerHTML = '<option value="">-- Pilih Host --</option>';
        
        const filtered = storeId 
            ? ALL_KARYAWANS.filter(k => k.store_id == storeId)
            : ALL_KARYAWANS;

        if (filtered.length === 0 && storeId) {
            hostSelect.innerHTML = '<option value="">-- Tidak ada karyawan di toko ini --</option>';
            return;
        }

        filtered.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            opt.textContent = k.nama;
            hostSelect.appendChild(opt);
        });

        // Kembalikan value jika sebelumnya sudah dipilih (saat edit)
        if (currentValue) hostSelect.value = currentValue;
    }

    // Panggil fungsi saat dropdown Toko di modal berubah
    document.getElementById('liveStoreId')?.addEventListener('change', filterHostByStore);
</script>
@endsection