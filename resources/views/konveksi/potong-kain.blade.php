@extends('layouts.app')

@section('title', 'Data Potong Kain')

@section('content')

<style>
    /* Sembunyikan tombol silang (x) di sebelah kanan chip Select2 */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        display: none !important;
    }
    .select2-search__field::-webkit-search-cancel-button {
        -webkit-appearance: none;
        appearance: none;
    }
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
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #10b981 !important;
        color: white !important;
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
</style>

<div class="w-full min-w-0">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:content-cut" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Potong Kain</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan setor barang potong kain</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <button type="button" onclick="openKaryawanModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-xs sm:text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:account-plus" class="text-lg"></iconify-icon> Tambah Karyawan
            </button>
            <button type="button" onclick="openHancaModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition text-xs sm:text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon> Tambah Potong Kain
            </button>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan</label>
                <select name="karyawan_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    <option value="all" {{ $karyawanId === 'all' ? 'selected' : '' }}>Semua Karyawan</option>
                    @foreach($karyawanList as $k)
                        <option value="{{ $k->id }}" {{ $karyawanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Produk</label>
                <select name="kategori_produk" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    <option value="all" {{ $kategoriProdukFilter === 'all' ? 'selected' : '' }}>Semua Kategori Produk</option>
                    @foreach($kategoriProdukList as $katProd)
                        <option value="{{ $katProd }}" {{ $kategoriProdukFilter == $katProd ? 'selected' : '' }}>{{ $katProd }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                <select name="bulan" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-600 focus:outline-none">
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
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-purple-600 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $karyawanId !== 'all' || $kategoriProdukFilter !== 'all' || $bulanFilter !== 'all')
                    <a href="{{ route('konveksi.potong-kain') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Potong Kain</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $hancaList->count() }} Data</div>
        </div>

        @if($hancaList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada data potong kain.</span>
            </div>
        @else
            <div class="w-full overflow-x-auto block">
                <table class="w-full text-xs sm:text-sm text-left min-w-[900px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nama Produk</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori Produk</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Warna</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Ukuran</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Qty</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Sisa Qty</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Waktu</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($hancaList as $h)
                            @foreach($h->details as $detail)
                                @php
                                    $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                                    $sisaQtyDetail = max(0, $detail->jumlah_hanca - $dibayarQty);

                                    // Hitung Waktu
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
                                    <td class="py-2 px-3 text-right font-semibold {{ $sisaQtyDetail > 0 ? 'text-orange-600' : 'text-green-600' }} whitespace-nowrap">{{ $sisaQtyDetail }}</td>
                                    
                                    {{-- Kolom Waktu --}}
                                    <td class="py-2 px-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-semibold px-2 py-1 rounded border {{ $waktuClass }}">
                                            <iconify-icon icon="mdi:clock-outline" class="text-xs sm:text-sm"></iconify-icon>
                                            {{ $durasi }} hari
                                        </span>
                                    </td>

                                    <td class="py-2 px-3 text-center">
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            {{-- TOMBOL SETOR (Hanya jika sisa qty > 0) --}}
                                            @if($sisaQtyDetail > 0)
                                                <button type="button" 
                                                    onclick="openBayarModalDariDetail({{ $detail->id }})"
                                                    class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition border border-emerald-200"
                                                    title="Setor Potong Kain">Setor
                                                </button>
                                            @endif
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

{{-- MODAL: TAMBAH KARYAWAN --}}
<div id="karyawanModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeKaryawanModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-purple-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:account-plus" class="text-purple-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Tambah Karyawan Konveksi</h3>
            </div>
            <button type="button" onclick="closeKaryawanModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>
        <form id="karyawanForm" onsubmit="submitKaryawan(event)" class="flex-1 flex flex-col overflow-hidden">
            @csrf
            <div class="p-5 space-y-4 flex-shrink-0">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorKaryawan"></div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="karyawanNama" required placeholder="cth: Entis"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. HP (Opsional)</label>
                    <input type="text" name="no_hp" id="karyawanNoHp" placeholder="cth: 08123..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>
            </div>
            <div class="border-t border-gray-100 flex-1 overflow-y-auto px-5 py-3">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Daftar Karyawan</h4>
                <div class="space-y-2">
                    @forelse($karyawanList as $k)
                        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <div>
                                <div class="text-sm font-medium text-gray-800">{{ $k->nama }}</div>
                                @if($k->no_hp)
                                    <div class="text-[10px] text-gray-500">{{ $k->no_hp }}</div>
                                @endif
                            </div>
                            <button type="button" onclick="deleteKaryawan({{ $k->id }})" class="text-gray-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition">
                                <iconify-icon icon="mdi:close-circle" class="text-lg"></iconify-icon>
                            </button>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 text-xs py-4">Belum ada karyawan.</div>
                    @endforelse
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeKaryawanModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitKaryawan" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: TAMBAH / EDIT HANCA (POTONG KAIN) --}}
<div id="hancaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeHancaModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-5xl overflow-hidden max-h-[92vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-purple-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="hancaModalIcon" icon="mdi:plus-circle" class="text-purple-600 text-lg"></iconify-icon>
                <h3 id="hancaModalTitle" class="font-bold text-gray-800">Tambah Potong Kain</h3>
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

                <div class="bg-purple-50 border border-purple-200 rounded-lg px-3 py-2 text-xs text-purple-700 flex items-center gap-2">
                    <iconify-icon icon="mdi:information" class="text-base"></iconify-icon>
                    Kategori kerja otomatis: <strong>Potong Kain</strong>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan <span class="text-red-500">*</span></label>
                        <select name="konveksi_karyawan_id" id="hancaKaryawanId" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawanList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="hancaTanggal" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3 mt-2">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold text-sm text-gray-700">
                            <iconify-icon icon="mdi:package-variant-closed" class="text-purple-600 mr-1"></iconify-icon>
                            Daftar Produk
                        </h4>
                        <button type="button" onclick="addProductRow()" class="text-xs bg-purple-100 text-purple-700 px-3 py-1.5 rounded-lg hover:bg-purple-200 transition font-medium flex items-center gap-1">
                            <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah Produk
                        </button>
                    </div>
                    <div id="productRows" class="space-y-2"></div>
                    <div class="mt-3 bg-purple-50 border border-purple-200 rounded-lg p-3 flex justify-between items-center">
                        <span class="text-xs font-semibold text-purple-800">Total Hanca:</span>
                        <span id="hancaTotalPreview" class="text-lg font-bold text-purple-700">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeHancaModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitHanca" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitHancaText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: SETOR POTONG KAIN (HANYA QTY) --}}
<div id="bayarModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBayarModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-emerald-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:package-variant" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Setor Barang Potong Kain</h3>
            </div>
            <button type="button" onclick="closeBayarModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>
        <form id="bayarForm" onsubmit="submitBayar(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorBayar"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Pilih Hanca yang Disetor <span class="text-red-500">*</span>
                    </label>
                    <select name="hanca_ids[]" id="bayarHancaIds" multiple="multiple" style="width:100%;" required>
                        @foreach($hancaBelumLunas as $h)
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
                        <input type="date" name="tanggal" id="bayarTanggal" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" id="bayarKeterangan" placeholder="Opsional"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <input type="hidden" name="tipe" value="bayaran">
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeBayarModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitBayar" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan Setoran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let productRowCount = 0;
    let setorState = {};

    const kategoriProdukData = @json($kategoriProdukList ?? []);
    const kategoriProdukIdsByName = @json($kategoriProdukIdsByName ?? []);

    function formatRupiah(angka) { return new Intl.NumberFormat('id-ID').format(angka); }
    function parseAngka(str) { return parseInt(String(str).replace(/[^0-9]/g, '')) || 0; }

    // ===== KARYAWAN =====
    function openKaryawanModal() {
        document.getElementById('karyawanForm').reset();
        document.getElementById('modalErrorKaryawan').classList.add('hidden');
        document.getElementById('karyawanModal').classList.remove('hidden');
        document.getElementById('karyawanModal').classList.add('flex');
    }
    function closeKaryawanModal() {
        document.getElementById('karyawanModal').classList.add('hidden');
        document.getElementById('karyawanModal').classList.remove('flex');
    }
    async function submitKaryawan(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitKaryawan');
        const errDiv = document.getElementById('modalErrorKaryawan');
        const orig = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');
        try {
            const res = await fetch("{{ route('konveksi.karyawan.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: new FormData(e.target),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false; btn.innerHTML = orig;
        }
    }
    async function deleteKaryawan(id) {
        if (!confirm('Yakin hapus karyawan ini?')) return;
        try {
            const res = await fetch(`/konveksi/karyawan/${id}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menghapus.');
            window.location.reload();
        } catch (err) { alert('Gagal: ' + err.message); }
    }

    // ===== PRODUK ROW =====
    function addProductRow(data = null) {
        productRowCount++;
        const container = document.getElementById('productRows');
        const rowId = `product-row-${productRowCount}`;

        let kategoriOptions = '<option value="">-- Pilih Kategori --</option>';
        kategoriProdukData.forEach(k => {
            const selected = (data && data.kategori_produk === k) ? 'selected' : '';
            const categoryId = kategoriProdukIdsByName[k] || '';
            kategoriOptions += `<option value="${k}" data-id="${categoryId}" ${selected}>${k}</option>`;
        });
        kategoriOptions += '<option value="__new__" class="font-semibold text-purple-600">+ Tambah Kategori Baru</option>';

        const html = `
            <div id="${rowId}" class="product-row bg-gray-50 p-3 rounded-lg border border-gray-200 relative">
                <input type="hidden" class="product-id" value="${data?.id || ''}">
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end">
                    <div class="col-span-2 sm:col-span-1 product-category-field">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Kategori Produk</label>
                        <div class="flex gap-1">
                            <select class="product-kategori-produk min-w-0 flex-1 border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" onchange="handleKategoriProdukRowChange(this)">
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
                                <input type="text" class="product-kategori-baru-input flex-1 min-w-0 border border-purple-300 rounded px-2 py-1 text-xs" placeholder="Ketik kategori baru...">
                                <button type="button" onclick="simpanKategoriProdukRowBaru(this)" class="product-kategori-baru-button bg-purple-600 text-white px-2 py-1 rounded hover:bg-purple-700 text-[10px] font-medium whitespace-nowrap">Tambah</button>
                            </div>
                            <p class="product-kategori-baru-error hidden text-[10px] text-red-600 mt-1"></p>
                        </div>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" class="product-nama w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.nama_produk || ''}" required placeholder="cth: Kaos Oblong">
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Warna</label>
                        <input type="text" class="product-warna w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.warna || ''}" placeholder="Hitam">
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Ukuran</label>
                        <input type="text" class="product-ukuran w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.ukuran || ''}" placeholder="L, XL">
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Qty <span class="text-red-500">*</span></label>
                        <input type="number" class="product-qty w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.jumlah_hanca || ''}" min="1" required placeholder="0" oninput="hitungTotal()">
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Harga / Pcs <span class="text-red-500">*</span></label>
                        <input type="text" class="product-harga w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data ? formatRupiah(data.harga) : ''}" required placeholder="0" oninput="formatHargaInput(this);">
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
        if (row) { row.remove(); hitungTotal(); }
    }

    function handleKategoriProdukRowChange(select) {
        const wrapper = select.closest('.product-category-field').querySelector('.product-kategori-baru-wrapper');
        const input = wrapper.querySelector('.product-kategori-baru-input');
        const error = wrapper.querySelector('.product-kategori-baru-error');
        const deleteButton = select.closest('.product-category-field').querySelector('.product-kategori-delete');
        deleteButton.disabled = !select.value || select.value === '__new__';
        if (select.value === '__new__') {
            wrapper.classList.remove('hidden'); input.focus();
        } else {
            wrapper.classList.add('hidden'); input.value = ''; error.textContent = ''; error.classList.add('hidden');
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
            button.disabled = false; button.textContent = originalText;
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
            alert(error.message); button.disabled = false;
        }
    }

    function hitungTotal() {
        let totalQty = 0;
        document.querySelectorAll('.product-row').forEach(row => {
            const qty = parseInt(row.querySelector('.product-qty')?.value) || 0;
            totalQty += qty;
        });
        document.getElementById('hancaTotalPreview').textContent = totalQty + ' pcs';
    }

    // ===== MODAL HANCA =====
    function openHancaModal() {
        document.getElementById('hancaForm').reset();
        document.getElementById('modalErrorHanca').classList.add('hidden');
        document.getElementById('hancaMode').value = 'create';
        document.getElementById('hancaId').value = '';
        document.getElementById('hancaModalTitle').textContent = 'Tambah Potong Kain';
        document.getElementById('hancaModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitHancaText').textContent = 'Simpan';
        document.getElementById('hancaTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('productRows').innerHTML = '';
        productRowCount = 0;
        addProductRow();
        document.getElementById('hancaModal').classList.remove('hidden');
        document.getElementById('hancaModal').classList.add('flex');
    }

    function openEditHancaModal(id, karyawanId, tanggal, products) {
        document.getElementById('hancaForm').reset();
        document.getElementById('modalErrorHanca').classList.add('hidden');
        document.getElementById('hancaMode').value = 'edit';
        document.getElementById('hancaId').value = id;
        document.getElementById('hancaModalTitle').textContent = 'Edit Potong Kain';
        document.getElementById('hancaModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitHancaText').textContent = 'Simpan Perubahan';
        document.getElementById('hancaKaryawanId').value = karyawanId;
        document.getElementById('hancaTanggal').value = tanggal;
        document.getElementById('productRows').innerHTML = '';
        productRowCount = 0;
        if (products && products.length > 0) products.forEach(p => addProductRow(p));
        else addProductRow();
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

        const kategoriFinal = 'Potong Kain';

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
                kategori_produk: row.querySelector('.product-kategori-produk').value,
                nama_produk: nama,
                warna: row.querySelector('.product-warna').value.trim(),
                ukuran: row.querySelector('.product-ukuran').value.trim(),
                jumlah_hanca: qty,
                harga: harga, // ✅ Harga diisi dari input
            });
        });

        if (!valid) {
            errDiv.textContent = 'Semua produk harus diisi lengkap (Nama, Qty, Harga).';
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

    // ===== MODAL SETOR =====
    $(document).ready(function() {
        $('#bayarHancaIds').select2({
            placeholder: "-- Pilih Hanca (Bisa lebih dari 1) --",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#bayarModal'),
        });
        $('#bayarHancaIds').on('select2:select select2:unselect', function () {
            renderSetorTable();
        });
    });

    function openBayarModalDariDetail(detailId) {
        document.getElementById('bayarForm').reset();
        document.getElementById('modalErrorBayar').classList.add('hidden');
        document.getElementById('bayarTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('setorTableWrapper').classList.add('hidden');
        setorState = {};

        const select = $('#bayarHancaIds');
        select.val(null).trigger('change');

        const matchingOption = document.querySelector(`#bayarHancaIds option[data-detail-id="${detailId}"]`);
        if (matchingOption) {
            select.val([matchingOption.value]);
            renderSetorTable();
            select.trigger('change.select2');
        }

        document.getElementById('bayarModal').classList.remove('hidden');
        document.getElementById('bayarModal').classList.add('flex');
    }

    function closeBayarModal() {
        document.getElementById('bayarModal').classList.add('hidden');
        document.getElementById('bayarModal').classList.remove('flex');
    }

    function renderSetorTable() {
        const selectedOpts = Array.from(document.getElementById('bayarHancaIds').selectedOptions);
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

    async function submitBayar(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitBayar');
        const errDiv = document.getElementById('modalErrorBayar');
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
        formData.append('tanggal', document.getElementById('bayarTanggal').value);
        formData.append('keterangan', document.getElementById('bayarKeterangan').value);
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
        if (e.key === 'Escape') { closeKaryawanModal(); closeHancaModal(); closeBayarModal(); }
    });
</script>
@endsection