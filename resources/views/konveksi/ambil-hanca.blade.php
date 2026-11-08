@extends('layouts.app')

@section('title', 'Data Ambil Hanca')

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

    /* Memperbaiki ukuran font placeholder Select2 agar sesuai dengan input lainnya */
    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
        font-size: 0.875rem !important; /* Ukuran text-sm (14px) */
        color: #9ca3af !important;      /* Warna text-gray-400 */
        font-family: inherit;
    }

    /* Memastikan input pencarian di dalam Select2 juga menggunakan ukuran font yang sama */
    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        font-size: 0.875rem !important;
        font-family: inherit;
        margin-top: 0;
    }

    /* Memastikan tag/pilihan yang sudah dipilih tidak terlalu besar */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        font-size: 0.75rem !important; /* Ukuran text-xs (12px) */
        padding: 2px 8px !important;
        margin-top: 4px !important;
    }
</style>

<div class="w-full min-w-0">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:hand-back-right" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Ambil Hanca</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan pengambilan hanca jahit dari hasil potong kain</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <button type="button" onclick="openHancaModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-indigo-600 text-white px-3 py-2 rounded-lg hover:bg-indigo-700 transition text-xs sm:text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon> Ambil Hanca
            </button>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan</label>
                <select name="karyawan_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="all" {{ $karyawanId === 'all' ? 'selected' : '' }}>Semua Karyawan</option>
                    @foreach($karyawanList as $k)
                        <option value="{{ $k->id }}" {{ $karyawanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Produk</label>
                <select name="kategori_produk" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="all" {{ $kategoriProdukFilter === 'all' ? 'selected' : '' }}>Semua Kategori Produk</option>
                    @foreach($kategoriProdukList as $katProd)
                        <option value="{{ $katProd }}" {{ $kategoriProdukFilter == $katProd ? 'selected' : '' }}>{{ $katProd }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                <select name="bulan" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
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
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $karyawanId !== 'all' || $kategoriProdukFilter !== 'all' || $bulanFilter !== 'all')
                    <a href="{{ route('konveksi.ambil-hanca') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Ambil Hanca</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $hancaList->count() }} Data</div>
        </div>

        @if($hancaList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada data ambil hanca.</span>
            </div>
        @else
            <div class="w-full overflow-x-auto block">
                <table class="w-full text-xs sm:text-sm text-left min-w-[1000px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nama Produk</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori Produk</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Warna</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Ukuran</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Qty</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Waktu</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($hancaList as $h)
                            @foreach($h->details as $detail)
                                @php
                                    $dibayarNominal = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('nominal');
                                    $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                                    $sisaNominalDetail = max(0, $detail->total - $dibayarNominal);
                                    $sisaQtyDetail = max(0, $detail->jumlah_hanca - $dibayarQty);

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

                                {{-- ✅ TAMBAHKAN KONDISI INI: Hanya tampilkan jika sisa qty > 0 --}}
                                @if($sisaQtyDetail > 0)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">{{ $h->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $h->karyawan->nama ?? '-' }}</td>
                                    <td class="py-2 px-3 text-xs text-gray-800 whitespace-nowrap">{{ $detail->nama_produk }}</td>
                                    <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">{{ $detail->kategoriProduk->nama ?? '-' }}</td>
                                    <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">{{ $detail->warna ?? '-' }}</td>
                                    <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">{{ $detail->ukuran ?? '-' }}</td>
                                    <td class="py-2 px-3 text-right font-semibold text-gray-800 whitespace-nowrap">{{ $detail->jumlah_hanca }}</td>
                                    <td class="py-2 px-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-semibold px-2 py-1 rounded border {{ $waktuClass }}">
                                            <iconify-icon icon="mdi:clock-outline" class="text-xs sm:text-sm"></iconify-icon>
                                            {{ $durasi }} hari
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            <button type="button" 
                                                onclick="openEditHancaModal(
                                                    {{ $h->id }},
                                                    {{ $h->konveksi_karyawan_id }},
                                                    '{{ $h->tanggal?->format('Y-m-d') }}',
                                                    @js($h->details->map(fn($d) => [
                                                        'id' => $d->id,
                                                        'kategori_produk' => $d->kategoriProduk->nama ?? '',
                                                        'nama_produk' => $d->nama_produk,
                                                        'warna' => $d->warna,
                                                        'ukuran' => $d->ukuran,
                                                        'jumlah_hanca' => $d->jumlah_hanca,
                                                        'harga' => $d->harga,
                                                    ]))
                                                )" 
                                                class="inline-flex items-center gap-1 text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded text-xs font-medium transition border border-indigo-200"
                                                title="Edit Hanca">
                                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon>
                                            </button>
                                            <button type="button" onclick="deleteHancaDetail({{ $detail->id }})" class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition border border-red-200" title="Hapus Produk Ini">
                                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                            </button>
                                        </div>
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
{{-- MODAL: TAMBAH / EDIT HANCA (AMBIL HANCA) --}}
{{-- ============================================================ --}}
<div id="hancaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeHancaModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-5xl overflow-hidden max-h-[92vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-indigo-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="hancaModalIcon" icon="mdi:plus-circle" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 id="hancaModalTitle" class="font-bold text-gray-800">Ambil Hanca Jahit</h3>
            </div>
            <button type="button" onclick="closeHancaModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>
        <form id="hancaForm" onsubmit="submitHanca(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="hancaId" value="">
            <input type="hidden" id="hancaMode" value="create">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorHanca"></div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan <span class="text-red-500">*</span></label>
                        <select name="konveksi_karyawan_id" id="hancaKaryawanId" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawanList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- ✅ KATEGORI KERJA --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Kerja <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select name="kategori" id="hancaKategoriSelect" required onchange="handleKategoriChange()"
                                class="flex-1 min-w-0 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                {{-- ✅ Hardcode Jahit sebagai default --}}
                                <option value="Jahit" selected>JAHIT</option>
                                
                                {{-- Looping kategori lain (kecuali Jahit & Potong Kain) --}}
                                @foreach($kategoriList as $kat)
                                    @if($kat !== 'Jahit' && $kat !== 'Potong Kain')
                                        <option value="{{ $kat }}" data-id="{{ $kategoriIdsByName[$kat] ?? '' }}">{{ $kat }}</option>
                                    @endif
                                @endforeach
                                
                                <option value="__new__" class="font-semibold text-indigo-600">+ Tambah Kategori Baru...</option>
                            </select>
                            <button type="button" onclick="hapusKategoriKerjaTerpilih()" id="btnHapusKategori" disabled
                                class="px-2 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 disabled:opacity-40 disabled:cursor-not-allowed" title="Hapus kategori terpilih">
                                <iconify-icon icon="mdi:delete-outline"></iconify-icon>
                            </button>
                        </div>
                        <div id="kategoriBaruWrapper" class="hidden mt-2">
                            <div class="flex gap-2">
                                <input type="text" id="hancaKategoriBaru" placeholder="Ketik kategori baru..."
                                    class="flex-1 border border-indigo-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <button type="button" onclick="simpanKategoriBaru()" id="btnSimpanKategori" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 text-xs font-medium flex items-center gap-1">
                                    <iconify-icon icon="mdi:plus" class="text-xs"></iconify-icon> Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="hancaTanggal" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                {{-- SECTION: PILIH POTONG KAIN --}}
                <div id="potongKainSection" class="border-t border-gray-200 pt-3 mt-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Pilih Hanca Potong Kain (Selesai) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <select id="ambilPotongKainSelect" multiple="multiple" style="width:100%;"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @foreach($potongKainSelesai as $pk)
                                @php
                                    $detailsSelesai = $pk->details->filter(function($d) use ($pk, $usedPotongIds) {
                                        $dibayarQty = $pk->pembayarans->where('hanca_detail_id', $d->id)->sum('qty_setor');
                                        $selesai = ($d->jumlah_hanca - $dibayarQty) <= 0;
                                        $belumDipakai = !in_array($d->id, $usedPotongIds);
                                        return $selesai && $belumDipakai;
                                    });
                                @endphp
                                @foreach($detailsSelesai as $detail)
                                    <option value="{{ $pk->id }}" data-detail-id="{{ $detail->id }}"
                                        data-produk="{{ $detail->nama_produk }}"
                                        data-warna="{{ $detail->warna }}"
                                        data-ukuran="{{ $detail->ukuran }}"
                                        data-qty="{{ $detail->jumlah_hanca }}"
                                        data-kategori="{{ $detail->kategoriProduk->nama ?? '' }}">
                                        {{ $pk->tanggal?->format('d/m/Y') }} — {{ $pk->karyawan->nama ?? '-' }} — 
                                        {{ $detail->nama_produk }} 
                                        @if($detail->warna) [{{ $detail->warna }}] @endif
                                        @if($detail->ukuran) [{{ $detail->ukuran }}] @endif
                                        ({{ $detail->jumlah_hanca }} pcs)
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <button type="button" onclick="resetAmbilFromPotongKain()" 
                            class="px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 text-xs">
                            <iconify-icon icon="mdi:refresh"></iconify-icon>
                        </button>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3 mt-2">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold text-sm text-gray-700">
                            <iconify-icon icon="mdi:package-variant-closed" class="text-indigo-600 mr-1"></iconify-icon>
                            Daftar Produk
                        </h4>
                        <button type="button" onclick="addProductRow()" class="text-xs bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-200 transition font-medium flex items-center gap-1">
                            <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah Produk
                        </button>
                    </div>
                    <div id="productRows" class="space-y-2"></div>
                    <div class="mt-3 bg-indigo-50 border border-indigo-200 rounded-lg p-3 flex justify-between items-center">
                        <span class="text-xs font-semibold text-indigo-800">Total Hanca:</span>
                        <span id="hancaTotalPreview" class="text-lg font-bold text-indigo-700">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeHancaModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitHanca" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitHancaText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let productRowCount = 0;

    function formatRupiah(angka) { return new Intl.NumberFormat('id-ID').format(angka); }
    function parseAngka(str) { return parseInt(String(str).replace(/[^0-9]/g, '')) || 0; }

    let kategoriProdukData = @json($kategoriProdukList ?? []);
    let kategoriProdukIdsByName = @json($kategoriProdukIdsByName ?? []);
    let kategoriKerjaData = @json($kategoriList ?? []);
    let kategoriKerjaIdsByName = @json($kategoriIdsByName ?? []);

    function handleKategoriChange() {
        const select = document.getElementById('hancaKategoriSelect');
        const wrapper = document.getElementById('kategoriBaruWrapper');
        const inputBaru = document.getElementById('hancaKategoriBaru');
        const btnHapus = document.getElementById('btnHapusKategori');
        const potongKainSection = document.getElementById('potongKainSection');

        // ✅ Nonaktifkan tombol hapus jika Jahit, kosong, atau mode tambah baru
        btnHapus.disabled = !select.value || select.value === '__new__' || select.value === 'Jahit';

        if (select.value === '__new__') {
            wrapper.classList.remove('hidden');
            inputBaru.focus();
        } else {
            wrapper.classList.add('hidden');
            inputBaru.value = '';
        }

        // Sembunyikan/Tampilkan Potong Kain
        if (select.value === 'Jahit') {
            potongKainSection.classList.remove('hidden');
        } else {
            potongKainSection.classList.add('hidden');
            document.getElementById('ambilPotongKainSelect').value = '';
            
            const rows = document.querySelectorAll('.product-row');
            if (rows.length > 0) {
                rows.forEach(row => {
                    if (row.querySelector('.product-nama').readOnly) {
                        row.remove();
                    }
                });
                if (document.querySelectorAll('.product-row').length === 0) {
                    addProductRow();
                }
            }
        }
    }

    // ===== LOAD POTONG KAIN DETAILS =====
    async function loadPotongKainDetails() {
        const select = document.getElementById('ambilPotongKainSelect');
        const hancaId = select.value;
        const detailId = select.options[select.selectedIndex]?.dataset.detailId; // ✅ Ambil detail ID
        
        if (!hancaId) return;

        try {
            const res = await fetch(`/konveksi/potong-kain/${hancaId}/details-selesai-ajax`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal mengambil data.');

            document.getElementById('productRows').innerHTML = '';
            productRowCount = 0;

            if (!data.details || data.details.length === 0) {
                alert('Hanca ini tidak punya produk selesai yang belum diambil.');
                addProductRow();
                return;
            }

            // ✅ Filter hanya detail yang dipilih (jika ada detailId)
            let detailsToRender = data.details;
            if (detailId) {
                detailsToRender = data.details.filter(d => d.id == detailId);
            }

            // Jika tidak ada yang cocok, fallback ke semua (atau beri alert)
            if (detailsToRender.length === 0) {
                alert('Detail produk yang dipilih tidak ditemukan atau sudah diambil.');
                addProductRow();
                return;
            }

            detailsToRender.forEach(d => {
                addProductRow({
                    sumber_potong_id: d.id,
                    kategori_produk: d.kategori_produk,
                    nama_produk: d.nama_produk,
                    warna: d.warna,
                    ukuran: d.ukuran,
                    jumlah_hanca: d.jumlah_hanca,
                });
            });

            const firstHarga = document.querySelector('.product-row .product-harga');
            if (firstHarga) firstHarga.focus();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    async function simpanKategoriBaru() {
        const input = document.getElementById('hancaKategoriBaru');
        const btn = document.getElementById('btnSimpanKategori');
        const select = document.getElementById('hancaKategoriSelect');
        const wrapper = document.getElementById('kategoriBaruWrapper');
        const errDiv = document.getElementById('modalErrorHanca');
        
        const kategori = input.value.trim();
        if (kategori.toLowerCase() === 'potong kain') {
            alert('Kategori "Potong Kain" tidak dapat ditambahkan di sini karena sudah memiliki halaman khusus.');
            return;
        }
        if (!kategori) {
            errDiv.textContent = 'Kategori baru tidak boleh kosong.';
            errDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '...';
        errDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('kategori', kategori);
        formData.append('_token', CSRF_TOKEN);

        try {
            const res = await fetch("{{ route('konveksi.kategori.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menambah kategori.');
            
            const newOption = document.createElement('option');
            newOption.value = data.kategori;
            newOption.dataset.id = data.id;
            newOption.textContent = data.kategori;
            
            select.insertBefore(newOption, select.querySelector('option[value="__new__"]'));
            select.value = data.kategori;
            
            wrapper.classList.add('hidden');
            input.value = '';
            
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah';
            
            handleKategoriChange();
            
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah';
        }
    }

    $(document).ready(function() {
        // Inisialisasi Select2 pada dropdown Potong Kain
        $('#ambilPotongKainSelect').select2({
            placeholder: "-- Pilih Produk Potong Kain yang Sudah Selesai --",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#hancaModal') // agar dropdown muncul di atas modal
        });

        // Event saat user memilih produk potong kain
        $('#ambilPotongKainSelect').on('select2:select', function (e) {
            const option = e.params.data.element;
            const detailId = $(option).data('detail-id');
        
            // Cegah produk duplikat
            let isDuplicate = false;
        
            document.querySelectorAll('.product-sumber-potong-id').forEach(input => {
                if (input.value == detailId) {
                    isDuplicate = true;
                }
            });
        
            if (isDuplicate) {
                alert('Produk ini sudah ada di daftar.');
                $(this).val(
                    ($(this).val() || []).filter(v => v !== option.value)
                ).trigger('change.select2');
        
                return;
            }
        
            // Tambahkan produk ke Daftar Produk
            addProductRow({
                sumber_potong_id: detailId,
                kategori_produk: $(option).data('kategori'),
                nama_produk: $(option).data('produk'),
                warna: $(option).data('warna'),
                ukuran: $(option).data('ukuran'),
                jumlah_hanca: $(option).data('qty'),
            });
        });
        
        $('#ambilPotongKainSelect').on('select2:unselect', function (e) {
            const option = e.params.data.element;
            const detailId = $(option).data('detail-id');
        
            // Cari dan hapus produk terkait dari Daftar Produk
            document.querySelectorAll('.product-row').forEach(row => {
                const sumberId = row.querySelector('.product-sumber-potong-id')?.value;
        
                if (sumberId == detailId) {
                    row.remove();
                }
            });
        
            hitungTotal();
        });
    });

    async function hapusKategoriKerjaTerpilih() {
        const select = document.getElementById('hancaKategoriSelect');
        const option = select.selectedOptions[0];
        const categoryId = option?.dataset.id;
        const categoryName = select.value;

        // ✅ Cegah hapus jika Jahit
        if (categoryName === 'Jahit') {
            alert('Kategori "Jahit" adalah kategori utama dan tidak dapat dihapus.');
            return;
        }

        if (!categoryId || !confirm(`Hapus kategori kerja "${categoryName}"?`)) return;

        const button = document.getElementById('btnHapusKategori');
        button.disabled = true;
        try {
            const url = @json(route('konveksi.kategori.destroy-ajax', ['kategori' => '__ID__']));
            const response = await fetch(url.replace('__ID__', categoryId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menghapus kategori kerja.');

            // Hapus dari dropdown & data global
            option.remove();
            delete kategoriKerjaIdsByName[categoryName];
            select.value = 'Jahit'; // Kembalikan ke default
            handleKategoriChange(); // Refresh tampilan
            
        } catch (error) {
            alert(error.message);
            button.disabled = false;
        }
    }


    function resetAmbilFromPotongKain() {
        $('#ambilPotongKainSelect').val(null).trigger('change.select2');
        document.getElementById('productRows').innerHTML = '';
        productRowCount = 0;
        addProductRow(); // Baris kosong baru
        hitungTotal();
    }

    // ===== PRODUK ROW =====
    function addProductRow(data = null) {
        const container = document.getElementById('productRows');

        // ✅ Jika menambahkan data dari Potong Kain, hapus baris kosong pertama (jika ada)
        if (data && data.sumber_potong_id) {
            const existingRows = container.querySelectorAll('.product-row');
            if (existingRows.length === 1) {
                const firstRowNama = existingRows[0].querySelector('.product-nama');
                // Cek apakah baris tersebut benar-benar kosong
                if (firstRowNama && firstRowNama.value.trim() === '') {
                    existingRows[0].remove();
                }
            }
        }

        productRowCount++;
        const rowId = `product-row-${productRowCount}`;

        let kategoriOptions = '<option value="">-- Pilih Kategori --</option>';
        kategoriProdukData.forEach(k => {
            const selected = (data && data.kategori_produk === k) ? 'selected' : '';
            const categoryId = kategoriProdukIdsByName[k] || '';
            kategoriOptions += `<option value="${k}" data-id="${categoryId}" ${selected}>${k}</option>`;
        });
        kategoriOptions += '<option value="__new__" class="font-semibold text-indigo-600">+ Tambah Kategori Baru</option>';

        const isFromPotong = data && data.sumber_potong_id;
        
        const html = `
            <div id="${rowId}" class="product-row bg-gray-50 p-3 rounded-lg border border-gray-200 relative">
                <input type="hidden" class="product-id" value="${data?.id || ''}">
                <input type="hidden" class="product-sumber-potong-id" value="${data?.sumber_potong_id || ''}">
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end">
                    <div class="col-span-2 sm:col-span-1 product-category-field">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Kategori Produk</label>
                        <div class="flex gap-1">
                            {{-- ✅ Tambahkan ${isFromPotong ? 'disabled' : ''} di sini --}}
                            <select class="product-kategori-produk min-w-0 flex-1 border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500 ${isFromPotong ? 'bg-gray-100 cursor-not-allowed' : ''}" onchange="handleKategoriProdukRowChange(this)" ${isFromPotong ? 'disabled' : ''}>
                                ${kategoriOptions}
                            </select>
                            <button type="button" onclick="hapusKategoriProdukTerpilih(this)" disabled
                                class="product-kategori-delete px-2 rounded border border-red-200 text-red-600 hover:bg-red-50 disabled:opacity-40 disabled:cursor-not-allowed"
                                title="Hapus kategori produk terpilih">
                                <iconify-icon icon="mdi:delete-outline"></iconify-icon>
                            </button>
                        </div>
                        <div class="product-kategori-baru-wrapper hidden mt-1">
                            <div class="flex gap-1">
                                <input type="text" class="product-kategori-baru-input flex-1 min-w-0 border border-indigo-300 rounded px-2 py-1 text-xs" placeholder="Ketik kategori baru...">
                                <button type="button" onclick="simpanKategoriProdukRowBaru(this)" class="product-kategori-baru-button bg-indigo-600 text-white px-2 py-1 rounded hover:bg-indigo-700 text-[10px] font-medium whitespace-nowrap">Tambah</button>
                            </div>
                            <p class="product-kategori-baru-error hidden text-[10px] text-red-600 mt-1"></p>
                        </div>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" class="product-nama w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500" value="${data?.nama_produk || ''}" required placeholder="cth: Kaos Oblong" ${isFromPotong ? 'readonly' : ''}>
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Warna</label>
                        <input type="text" class="product-warna w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500" value="${data?.warna || ''}" placeholder="Hitam" ${isFromPotong ? 'readonly' : ''}>
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Ukuran</label>
                        <input type="text" class="product-ukuran w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500" value="${data?.ukuran || ''}" placeholder="L, XL" ${isFromPotong ? 'readonly' : ''}>
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Qty <span class="text-red-500">*</span></label>
                        <input type="number" class="product-qty w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500" value="${data?.jumlah_hanca || ''}" min="1" required placeholder="0" ${isFromPotong ? 'readonly' : ''} oninput="hitungTotal()">
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Harga <span class="text-red-500">*</span></label>
                        <input type="text" class="product-harga w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500" value="${data?.harga ? formatRupiah(data.harga) : ''}" required placeholder="0" oninput="formatHargaInput(this); hitungTotal()">
                    </div>
                </div>
                <button type="button" onclick="removeProductRow('${rowId}')" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600 shadow-sm" title="Hapus produk">&times;</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        hitungTotal();
    }

    function removeProductRow(rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;
    
        // Ambil ID sumber potong kain
        const sumberPotongId = row.querySelector('.product-sumber-potong-id')?.value;
    
        // Hapus pilihan dari Select2 jika produk berasal dari Potong Kain
        if (sumberPotongId) {
            const select = $('#ambilPotongKainSelect');
    
            const selectedValues = select.val() || [];
    
            // Cari option berdasarkan detail ID, bukan ID hanca
            select.find('option').each(function () {
                if ($(this).data('detail-id') == sumberPotongId) {
                    const optionValue = this.value;
    
                    const updatedValues = selectedValues.filter(
                        value => value !== optionValue
                    );
    
                    select.val(updatedValues).trigger('change.select2');
                }
            });
        }
    
        // Hapus baris produk
        row.remove();
    
        hitungTotal();
    }

    function handleKategoriProdukRowChange(select) {
        const wrapper = select.closest('.product-category-field').querySelector('.product-kategori-baru-wrapper');
        const input = wrapper.querySelector('.product-kategori-baru-input');
        const error = wrapper.querySelector('.product-kategori-baru-error');
        const deleteButton = select.closest('.product-category-field').querySelector('.product-kategori-delete');
        deleteButton.disabled = !select.value || select.value === '__new__';
        if (select.value === '__new__') {
            wrapper.classList.remove('hidden');
            input.focus();
        } else {
            wrapper.classList.add('hidden');
            input.value = ''; error.textContent = ''; error.classList.add('hidden');
        }
    }

    async function simpanKategoriProdukRowBaru(button) {
        const wrapper = button.closest('.product-kategori-baru-wrapper');
        const field = button.closest('.product-category-field');
        const input = wrapper.querySelector('.product-kategori-baru-input');
        const select = field.querySelector('.product-kategori-produk');
        const error = wrapper.querySelector('.product-kategori-baru-error');
        const kategori = input.value.trim();
        error.classList.add('hidden');
        if (!kategori) { error.textContent = 'Kategori produk tidak boleh kosong.'; error.classList.remove('hidden'); input.focus(); return; }
        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = '...';
        const formData = new FormData();
        formData.append('kategori_produk', kategori);
        try {
            const response = await fetch("{{ route('konveksi.kategori-produk.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menambahkan kategori produk.');
            const savedCategory = data.kategori;
            if (!kategoriProdukData.includes(savedCategory)) kategoriProdukData.push(savedCategory);
            kategoriProdukIdsByName[savedCategory] = data.id;
            document.querySelectorAll('.product-kategori-produk').forEach(categorySelect => {
                if (![...categorySelect.options].some(option => option.value === savedCategory)) {
                    const option = document.createElement('option');
                    option.value = savedCategory;
                    option.dataset.id = data.id;
                    option.textContent = savedCategory;
                    categorySelect.insertBefore(option, categorySelect.querySelector('option[value="__new__"]'));
                }
            });
            select.value = savedCategory;
            handleKategoriProdukRowChange(select);
            wrapper.classList.add('hidden');
            input.value = '';
        } catch (requestError) {
            error.textContent = requestError.message;
            error.classList.remove('hidden');
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }

    async function hapusKategoriProdukTerpilih(button) {
        const field = button.closest('.product-category-field');
        const select = field.querySelector('.product-kategori-produk');
        const option = select.selectedOptions[0];
        const categoryName = select.value;
        const categoryId = option?.dataset.id;
        if (!categoryId || !confirm(`Hapus kategori produk "${categoryName}"?`)) return;
        button.disabled = true;
        try {
            const url = @json(route('konveksi.kategori-produk.destroy-ajax', ['kategoriProduk' => '__ID__']));
            const response = await fetch(url.replace('__ID__', categoryId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menghapus kategori produk.');
            document.querySelectorAll('.product-kategori-produk').forEach(categorySelect => {
                [...categorySelect.options].filter(o => o.value === categoryName).forEach(o => o.remove());
                if (categorySelect.value === categoryName) categorySelect.value = '';
                handleKategoriProdukRowChange(categorySelect);
            });
            const idx = kategoriProdukData.indexOf(categoryName);
            if (idx !== -1) kategoriProdukData.splice(idx, 1);
            delete kategoriProdukIdsByName[categoryName];
        } catch (error) {
            alert(error.message);
            button.disabled = false;
        }
    }

    function formatHargaInput(input) {
        let v = input.value.replace(/[^0-9]/g, '');
        input.value = v ? formatRupiah(v) : '';
    }

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.product-row').forEach(row => {
            const qty = parseInt(row.querySelector('.product-qty')?.value) || 0;
            const harga = parseAngka(row.querySelector('.product-harga')?.value || '');
            total += qty * harga;
        });
        document.getElementById('hancaTotalPreview').textContent = 'Rp ' + formatRupiah(total);
    }

    // ===== MODAL HANCA =====
    function openHancaModal() {
        document.getElementById('hancaForm').reset();
        document.getElementById('modalErrorHanca').classList.add('hidden');
        document.getElementById('hancaMode').value = 'create';
        document.getElementById('hancaId').value = '';
        document.getElementById('hancaModalTitle').textContent = 'Ambil Hanca';
        document.getElementById('hancaModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitHancaText').textContent = 'Simpan';
        document.getElementById('hancaTanggal').value = new Date().toISOString().slice(0, 10);
        
        // Set default kategori ke 'Jahit'
        const selectKategori = document.getElementById('hancaKategoriSelect');
        selectKategori.value = 'Jahit'; // ✅ Selalu default Jahit
        handleKategoriChange();

        $('#ambilPotongKainSelect').val(null).trigger('change.select2');

        document.getElementById('ambilPotongKainSelect').value = '';
        document.getElementById('productRows').innerHTML = '';
        productRowCount = 0;
        addProductRow();
        
        document.getElementById('hancaModal').classList.remove('hidden');
        document.getElementById('hancaModal').classList.add('flex');
    }

    function openEditHancaModal(id, karyawanId, kategori, tanggal, products) {
        document.getElementById('hancaForm').reset();
        document.getElementById('modalErrorHanca').classList.add('hidden');
        document.getElementById('hancaMode').value = 'edit';
        document.getElementById('hancaId').value = id;
        document.getElementById('hancaModalTitle').textContent = 'Edit Hanca';
        document.getElementById('hancaModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitHancaText').textContent = 'Simpan Perubahan';
        
        document.getElementById('hancaKaryawanId').value = karyawanId;
        document.getElementById('hancaTanggal').value = tanggal;
        
        // Set Kategori
        const selectKategori = document.getElementById('hancaKategoriSelect');
        // Cari opsi yang cocok
        let found = false;
        for (let opt of selectKategori.options) {
            if (opt.value === kategori) {
                selectKategori.value = kategori;
                found = true;
                break;
            }
        }
        // Jika tidak ketemu (misal data lama), tambahkan opsi baru
        if (!found && kategori) {
            const newOpt = document.createElement('option');
            newOpt.value = kategori;
            newOpt.textContent = kategori;
            selectKategori.insertBefore(newOpt, selectKategori.querySelector('option[value="__new__"]'));
            selectKategori.value = kategori;
        }
        
        handleKategoriChange();

        document.getElementById('ambilPotongKainSelect').value = '';
        document.getElementById('productRows').innerHTML = '';
        productRowCount = 0;
        
        // Render produk
        if (products && products.length > 0) {
            products.forEach(p => addProductRow(p));
        } else {
            addProductRow();
        }
        
        document.getElementById('hancaModal').classList.remove('hidden');
        document.getElementById('hancaModal').classList.add('flex');
    }

    function closeHancaModal() {
        document.getElementById('hancaModal').classList.add('hidden');
        document.getElementById('hancaModal').classList.remove('flex');
    }

    async function submitHanca(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitHanca');
        const btnText = document.getElementById('btnSubmitHancaText');
        const errDiv = document.getElementById('modalErrorHanca');
        const mode = document.getElementById('hancaMode').value;
        const id = document.getElementById('hancaId').value;
        const orig = btnText.textContent;

        const selectKerja = document.getElementById('hancaKategoriSelect');
        let kategoriFinal = selectKerja.value;
        if (kategoriFinal === '__new__') {
            kategoriFinal = document.getElementById('hancaKategoriBaru').value.trim();
        }
        if (!kategoriFinal) {
            errDiv.textContent = 'Kategori kerja wajib dipilih.';
            errDiv.classList.remove('hidden');
            return;
        }

        const products = [];
        const rows = document.querySelectorAll('.product-row');
        if (rows.length === 0) {
            errDiv.textContent = 'Minimal 1 produk harus diisi.';
            errDiv.classList.remove('hidden');
            return;
        }

        let valid = true;
        rows.forEach(row => {
            const nama = row.querySelector('.product-nama').value.trim();
            const qty = parseInt(row.querySelector('.product-qty').value) || 0;
            const harga = parseAngka(row.querySelector('.product-harga').value);
            if (!nama || qty <= 0 || harga <= 0) { valid = false; return; }
            
            products.push({
                id: row.querySelector('.product-id').value || null,
                sumber_potong_id: row.querySelector('.product-sumber-potong-id')?.value || null,
                kategori_produk: row.querySelector('.product-kategori-produk').value,
                nama_produk: nama,
                warna: row.querySelector('.product-warna').value.trim(),
                ukuran: row.querySelector('.product-ukuran').value.trim(),
                jumlah_hanca: qty,
                harga: harga,
            });
        });

        if (!valid) {
            errDiv.textContent = 'Semua produk harus diisi lengkap (Harga wajib > 0).';
            errDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('konveksi_karyawan_id', document.getElementById('hancaKaryawanId').value);
        formData.append('kategori', kategoriFinal);
        formData.append('tanggal', document.getElementById('hancaTanggal').value);
        formData.append('products', JSON.stringify(products));

        let url = "{{ route('konveksi.hanca.store-ajax') }}";
        if (mode === 'edit') {
            url = `/konveksi/hanca/${id}/ajax`;
            formData.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, {
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
            btnText.textContent = orig;
        }
    }

    async function deleteHancaDetail(detailId) {
        if (!confirm('Yakin hapus produk ini dari hanca?')) return;
        try {
            const res = await fetch(`/konveksi/hanca-detail/${detailId}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menghapus.');
            window.location.reload();
        } catch (err) { alert('Gagal: ' + err.message); }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeHancaModal(); }
    });
</script>
@endsection