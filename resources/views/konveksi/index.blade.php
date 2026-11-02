@extends('layouts.app')

@section('title', 'Laporan Konveksi')

@section('content')

<style>
    /* =========================================
    CUSTOM SELECT2 STYLE (Tailwind-ish)
    ========================================= */

    /* Container Utama */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db !important; /* border-gray-300 */
        border-radius: 0.5rem !important;     /* rounded-lg */
        padding: 2px 6px !important;
        min-height: 38px !important;          /* samakan dengan input */
        background-color: #fff !important;
        box-shadow: none !important;
        font-family: 'Geist', sans-serif !important;
    }

    /* Saat Focus */
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #10b981 !important; /* border-emerald-500 */
        outline: 0;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important; /* ring-emerald */
            
    }

    /* Pilihan (Tag/Badge) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #ecfdf5 !important; /* bg-emerald-50 */
        border: 1px solid #a7f3d0 !important; /* border-emerald-200 */
        color: #047857 !important;            /* text-emerald-700 */
        border-radius: 0.375rem !important;   /* rounded-md */
        padding: 2px 8px !important;
        font-size: 0.75rem !important;        /* text-xs */
        font-family: 'Geist', sans-serif !important;
        font-weight: 500 !important;
        margin: 2px 4px 2px 0 !important;
        display: inline-flex;
        align-items: center;
    }

    /* Tombol Silang (x) di dalam tag */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #047857 !important;
        margin-right: 4px !important;
        font-weight: bold !important;
        border: none !important;
        background: transparent !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ef4444 !important; /* hover merah */
        background: transparent !important;
    }

    /* Dropdown List */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #10b981 !important; /* bg-emerald-500 */
        color: white !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #ecfdf5 !important;
        color: #047857 !important;
        font-weight: 500;
    }

    /* Search Input di dalam Select2 */
    .select2-container--default .select2-search--inline .select2-search__field {
        font-size: 0.875rem !important; /* text-sm */
        margin-top: 4px !important;
        color: #374151 !important;
    }

    /* Dropdown Container */
    .select2-dropdown {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
        z-index: 9999;
    }

    /* Placeholder */
    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
        color: #9ca3af !important;
        font-size: 0.875rem;
    }

    .select2-container--default .select2-results__option {
        font-size: 0.75rem !important; /* text-xs (12px) */
        padding: 6px 10px !important;
        line-height: 1.4 !important;
    }

    /* 2. Teks di dalam Tag/Badge (Yang sudah dipilih) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        font-size: 0.75rem !important; /* text-xs */
        padding: 2px 6px !important;
        margin: 2px 4px 2px 0 !important;
    }

    /* 3. Teks Placeholder & Pencarian */
    .select2-container--default .select2-search--inline .select2-search__field {
        font-size: 0.75rem !important;
        height: 24px !important;
    }

    /* 4. Container (Memastikan tinggi tetap seimbang) */
    .select2-container--default .select2-selection--multiple {
        min-height: 36px !important;
        padding: 4px 6px !important;
    }
</style>
<div class="w-full min-w-0">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:clipboard-list" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Setor Hanca</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan hanca & pembayaran karyawan konveksi</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <button type="button" onclick="openKaryawanModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-xs sm:text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:account-plus" class="text-lg"></iconify-icon> Tambah Karyawan
            </button>
            <button type="button" onclick="openHancaModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition text-xs sm:text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon> Tambah Hanca
            </button>
            <button type="button" onclick="openBayarModal()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-emerald-600 text-white px-3 py-2 rounded-lg hover:bg-emerald-700 transition text-xs sm:text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:cash-plus" class="text-lg"></iconify-icon> Tambah Bayaran
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
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Kerja</label>
                <select name="kategori" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    <option value="all" {{ $kategoriFilter === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ $kategoriFilter == $kat ? 'selected' : '' }}>{{ $kat }}</option>
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, produk, model..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-purple-600 focus:outline-none">
                </div>
            </div>

            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $karyawanId !== 'all' || $kategoriFilter !== 'all' || $kategoriProdukFilter !== 'all' || $bulanFilter !== 'all')
                    <a href="{{ route('konveksi.index') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-4 mb-6 w-full">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:clipboard-list" class="text-purple-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Total Hanca</div>
            <div class="text-xs sm:text-base font-bold text-purple-600 break-words">
                <span class="text-[9px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_hanca'], 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:cash-check" class="text-green-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Total Dibayar</div>
            <div class="text-xs sm:text-base font-bold text-green-600 break-words">
                <span class="text-[9px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_dibayar'], 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Total Sisa</div>
            <div class="text-xs sm:text-base font-bold text-red-600 break-words">
                <span class="text-[9px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_sisa'], 0, ',', '.') }}
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

    {{-- TABEL DATA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Hanca</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $hancaList->count() }} Data</div>
        </div>

        @if($hancaList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada data hanca.</span>
            </div>
        @else
            <div class="w-full overflow-x-auto block">
                <table class="w-full text-xs sm:text-sm text-left min-w-[1000px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori Kerja</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nama Produk</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori Produk</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Warna</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Ukuran</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Qty</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Sisa Qty</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Harga</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Total</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Dibayar</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Sisa</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Durasi</th>
                            <th class="py-2 px-3 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($hancaList as $h)
                            @foreach($h->details as $detail)
                                @if($detail->sisa_hanca > 0)
                                    @php
                                        // ✅ HITUNG SISA QTY & NOMINAL SECARA REAL-TIME
                                        $dibayarNominal = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('nominal');
                                        $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                                        
                                        $sisaNominalDetail = max(0, $detail->total - $dibayarNominal);
                                        $sisaQtyDetail = max(0, $detail->jumlah_hanca - $dibayarQty);
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">
                                            {{ $h->tanggal?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">
                                            {{ $h->karyawan->nama ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">
                                            {{ $h->kategori }}
                                        </td>
                                        <td class="py-2 px-3 text-xs text-gray-800 whitespace-nowrap">
                                            {{ $detail->nama_produk }}
                                        </td>
                                        <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">
                                            {{ $detail->kategoriProduk->nama ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">
                                            {{ $detail->warna ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">
                                            {{ $detail->ukuran ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-semibold text-gray-800 whitespace-nowrap">
                                            {{ $detail->jumlah_hanca }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-semibold {{ $sisaQtyDetail > 0 ? 'text-orange-600' : 'text-green-600' }} whitespace-nowrap">
                                            {{ $sisaQtyDetail }}
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 whitespace-nowrap">
                                            Rp {{ number_format($detail->harga, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-bold text-gray-900 whitespace-nowrap">
                                            Rp {{ number_format($detail->total, 0, ',', '.') }}
                                        </td>

                                        {{-- Dibayar & Sisa Nominal --}}
                                        <td class="py-2 px-3 text-right text-green-600 whitespace-nowrap">
                                            Rp {{ number_format($dibayarNominal, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-bold {{ $sisaNominalDetail > 0 ? 'text-red-600' : 'text-green-600' }} whitespace-nowrap">
                                            Rp {{ number_format($sisaNominalDetail, 0, ',', '.') }}
                                        </td>

                                        {{-- Durasi --}}
                                        <td class="py-2 px-3 text-center whitespace-nowrap">
                                            @php
                                                $color = $h->durasi_color;
                                                $classes = [
                                                    'green'  => 'bg-green-100 text-green-700 border-green-200',
                                                    'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                    'red'    => 'bg-red-100 text-red-700 border-red-200',
                                                ];
                                                $class = $classes[$color] ?? $classes['green'];
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-semibold px-2 py-1 rounded border {{ $class }}">
                                                <iconify-icon icon="mdi:clock-outline" class="text-xs sm:text-sm"></iconify-icon>
                                                {{ $h->durasi }} hari
                                            </span>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="py-2 px-3 text-center">
                                            <div class="inline-flex items-center gap-1 justify-center">
                                                
                                                    <button type="button" 
                                                        onclick="openEditHancaModal(
                                                            {{ $h->id }},
                                                            {{ $h->konveksi_karyawan_id }},
                                                            '{{ addslashes($h->kategori) }}',
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
{{-- MODAL: TAMBAH KARYAWAN --}}
{{-- ============================================================ --}}
<div id="karyawanModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeKaryawanModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        
        {{-- Header --}}
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

            {{-- ✅ LIST KARYAWAN --}}
            <div class="border-t border-gray-100 flex-1 overflow-y-auto px-5 py-3">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Daftar Karyawan</h4>
                
                <div class="space-y-2">
                    @forelse($karyawanList as $k)
                        <div id="karyawan-item-{{ $k->id }}" class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 group hover:bg-white hover:border-purple-300 transition">
                            <div>
                                <div class="text-sm font-medium text-gray-800">{{ $k->nama }}</div>
                                @if($k->no_hp)
                                    <div class="text-[10px] text-gray-500 flex items-center gap-1">
                                        <iconify-icon icon="mdi:phone" class="text-xs"></iconify-icon>
                                        {{ $k->no_hp }}
                                    </div>
                                @endif
                            </div>
                            <button type="button" onclick="deleteKaryawan({{ $k->id }})" class="text-gray-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition" title="Hapus">
                                <iconify-icon icon="mdi:close-circle" class="text-lg"></iconify-icon>
                            </button>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 text-xs py-4">Belum ada karyawan.</div>
                    @endforelse
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeKaryawanModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitKaryawan" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: TAMBAH / EDIT HANCA --}}
{{-- ============================================================ --}}
<div id="hancaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeHancaModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-5xl overflow-hidden max-h-[92vh] flex flex-col">
        
        {{-- HEADER --}}
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-purple-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon id="hancaModalIcon" icon="mdi:plus-circle" class="text-purple-600 text-lg"></iconify-icon>
                <h3 id="hancaModalTitle" class="font-bold text-gray-800">Tambah Data Hanca</h3>
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

                {{-- DATA UTAMA --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Karyawan --}}
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

                    {{-- Kategori Kerja --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Kerja <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select name="kategori" id="hancaKategoriSelect" required onchange="handleKategoriChange()"
                                class="flex-1 min-w-0 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}" data-id="{{ $kategoriIdsByName[$kat] ?? '' }}">{{ $kat }}</option>
                                @endforeach
                                <option value="__new__" class="font-semibold text-purple-600">+ Tambah Kategori Baru...</option>
                            </select>
                            <button type="button" id="btnHapusKategoriKerja" onclick="hapusKategoriKerjaTerpilih()" disabled
                                class="px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 disabled:opacity-40 disabled:cursor-not-allowed"
                                title="Hapus kategori kerja terpilih">
                                <iconify-icon icon="mdi:delete-outline"></iconify-icon>
                            </button>
                        </div>
                        <div id="kategoriBaruWrapper" class="hidden mt-2">
                            <div class="flex gap-2">
                                <input type="text" id="hancaKategoriBaru" placeholder="Ketik kategori baru..."
                                    class="flex-1 border border-purple-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                <button type="button" onclick="simpanKategoriBaru()" id="btnSimpanKategori" class="bg-purple-600 text-white px-3 py-1.5 rounded-lg hover:bg-purple-700 text-xs font-medium flex items-center gap-1">
                                    <iconify-icon icon="mdi:plus" class="text-xs"></iconify-icon> Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="hancaTanggal" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                </div>

                {{-- SECTION PRODUK --}}
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

                    <div id="productRows" class="space-y-2">
                        {{-- Baris produk akan di-generate via JS --}}
                    </div>

                    {{-- Total Preview --}}
                    <div class="mt-3 bg-purple-50 border border-purple-200 rounded-lg p-3 flex justify-between items-center">
                        <span class="text-xs font-semibold text-purple-800">Total Hanca:</span>
                        <span id="hancaTotalPreview" class="text-lg font-bold text-purple-700">Rp 0</span>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeHancaModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitHanca" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitHancaText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: TAMBAH BAYARAN (MULTI HANCA) --}}
{{-- ============================================================ --}}
<div id="bayarModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBayarModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        
        {{-- HEADER --}}
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-emerald-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cash-plus" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Tambah Pembayaran</h3>
            </div>
            <button type="button" onclick="closeBayarModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="bayarForm" onsubmit="submitBayar(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorBayar"></div>

                {{-- Pilih Hanca (multi-select) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Pilih Hanca yang Disetor <span class="text-red-500">*</span>
                    </label>
                    <select name="hanca_ids[]" id="bayarHancaIds" multiple="multiple" style="width:100%;" required>
                        @foreach($hancaBelumLunas as $h)
                            @foreach($h->details as $detail)
                                @php
                                    // ✅ HITUNG SISA QTY SECARA REAL-TIME
                                    $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                                    $sisaQtyReal = $detail->jumlah_hanca - $dibayarQty;
                                @endphp
                                @if($sisaQtyReal > 0)
                                    <option value="{{ $h->id }}"
                                        data-hanca-id="{{ $h->id }}"
                                        data-detail-id="{{ $detail->id }}"
                                        data-karyawan-id="{{ $h->konveksi_karyawan_id }}"
                                        data-kategori="{{ $h->kategori }}"
                                        data-produk="{{ $detail->nama_produk }}"
                                        data-warna="{{ $detail->warna ?? '' }}"
                                        data-ukuran="{{ $detail->ukuran ?? '' }}"
                                        data-harga="{{ $detail->harga }}"
                                        data-sisa-qty="{{ $sisaQtyReal }}"
                                        data-total="{{ $detail->total }}">
                                        {{ $h->karyawan->nama ?? '-' }} — {{ $h->kategori }} —
                                        {{ $detail->nama_produk }}
                                        @if($detail->warna)[{{ $detail->warna }}]@endif
                                        (Sisa: {{ $sisaQtyReal }} pcs × Rp {{ number_format($detail->harga, 0, ',', '.') }})
                                    </option>
                                @endif
                            @endforeach
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Centang hanca yang akan disetor.</p>
                </div>

                {{-- TABEL SETOR PER HANCA --}}
                <div id="setorTableWrapper" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Rincian Setoran</label>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left px-2 py-2 font-semibold text-gray-600">Produk</th>
                                    <th class="text-center px-2 py-2 font-semibold text-gray-600">Sisa</th>
                                    <th class="text-center px-2 py-2 font-semibold text-gray-600 w-20">Setor</th>
                                    <th class="text-right px-2 py-2 font-semibold text-gray-600">Nominal</th>
                                </tr>
                            </thead>
                            <tbody id="setorTableBody" class="divide-y divide-gray-100">
                                {{-- diisi via JS --}}
                            </tbody>
                            <tfoot class="bg-emerald-50 border-t border-emerald-200">
                                <tr>
                                    <td colspan="2" class="px-2 py-2 text-xs font-semibold text-emerald-800">TOTAL</td>
                                    <td id="totalQtySetor" class="px-2 py-2 text-center font-bold text-emerald-700">0</td>
                                    <td id="totalNominalSetor" class="px-2 py-2 text-right font-bold text-emerald-700">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div id="kasbonSection" class="hidden bg-orange-50 border border-orange-200 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <iconify-icon icon="mdi:cash-remove" class="text-orange-600"></iconify-icon>
                        <span class="text-xs font-bold text-orange-800">Potong Kasbon Karyawan</span>
                    </div>
                    <div id="kasbonList" class="space-y-1">
                        <!-- Diisi via JS -->
                    </div>
                </div>

                <div class="mt-2 border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-gray-100">
                            <tr class="bg-orange-50 hidden" id="rowPotongKasbon">
                                <td class="px-2 py-2 text-xs font-semibold text-orange-800">Potong Kasbon</td>
                                <td id="potongKasbonNominal" class="px-2 py-2 text-right font-bold text-orange-700">- Rp 0</td>
                            </tr>
                            <tr class="bg-emerald-100 border-t-2 border-emerald-300">
                                <td class="px-2 py-2 text-xs font-bold text-emerald-900">SUBTOTAL DITERIMA</td>
                                <td id="subtotalDiterima" class="px-2 py-2 text-right font-black text-emerald-900 text-sm">Rp 0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Tanggal & Keterangan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

                {{-- Hidden tipe --}}
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

{{-- ============================================================ --}}
{{-- MODAL: DETAIL HANCA & RIWAYAT PEMBAYARAN --}}
{{-- ============================================================ --}}
<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeDetailModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-blue-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:eye" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Detail Hanca & Riwayat Pembayaran</h3>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 p-5 space-y-4" id="detailContent">
            <div class="text-center text-gray-500 py-8">
                <iconify-icon icon="mdi:loading" class="text-4xl animate-spin block m-auto mb-2"></iconify-icon>
                Memuat data...
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Tutup</button>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    let productRowCount = 0;
    let totalNominalHanca = 0;
    let totalKasbonDipotong = 0;

    // Data kategori produk dari server (untuk dropdown)
    const kategoriProdukData = @json($kategoriProdukList ?? []);
    const kategoriIdsByName = @json($kategoriIdsByName ?? []);
    const kategoriProdukIdsByName = @json($kategoriProdukIdsByName ?? []);
    let currentHancaData = null;

    // ============================================================
    // FORMAT ANGKA
    // ============================================================
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function parseAngka(str) {
        return parseInt(str.replace(/[^0-9]/g, '')) || 0;
    }

    // ============================================================
    // MODAL KARYAWAN
    // ============================================================
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

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        try {
            const res = await fetch("{{ route('konveksi.karyawan.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: new FormData(e.target),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            
            // ✅ Langsung refresh halaman
            window.location.reload();
            
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }

    async function deleteKaryawan(id) {
        if (!confirm('Yakin hapus karyawan ini? Semua data hanca dan pembayarannya juga akan terhapus.')) return;
        
        try {
            const res = await fetch(`/konveksi/karyawan/${id}/ajax`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': CSRF_TOKEN, 
                    'Accept': 'application/json' 
                },
            });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.message || 'Gagal menghapus.');
            
            // ✅ Langsung refresh halaman
            window.location.reload();
            
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }


    function handleKategoriChange() {
        const select = document.getElementById('hancaKategoriSelect');
        const wrapper = document.getElementById('kategoriBaruWrapper');
        const inputBaru = document.getElementById('hancaKategoriBaru');
        document.getElementById('btnHapusKategoriKerja').disabled = !select.value || select.value === '__new__';

        if (select.value === '__new__') {
            wrapper.classList.remove('hidden');
            inputBaru.focus();
        } else {
            wrapper.classList.add('hidden');
            inputBaru.value = '';
        }
    }

    async function hapusKategoriKerjaTerpilih() {
        const select = document.getElementById('hancaKategoriSelect');
        const option = select.selectedOptions[0];
        const categoryId = option?.dataset.id;
        if (!categoryId || !confirm(`Hapus kategori kerja "${select.value}"?`)) return;

        const button = document.getElementById('btnHapusKategoriKerja');
        button.disabled = true;
        try {
            const url = @json(route('konveksi.kategori.destroy-ajax', ['kategori' => '__ID__']));
            const response = await fetch(url.replace('__ID__', categoryId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menghapus kategori kerja.');

            delete kategoriIdsByName[select.value];
            option.remove();
            select.value = '';
            handleKategoriChange();
        } catch (error) {
            alert(error.message);
            button.disabled = false;
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
                [...categorySelect.options]
                    .filter(categoryOption => categoryOption.value === categoryName)
                    .forEach(categoryOption => categoryOption.remove());
                if (categorySelect.value === categoryName) categorySelect.value = '';
                handleKategoriProdukRowChange(categorySelect);
            });
            const categoryIndex = kategoriProdukData.indexOf(categoryName);
            if (categoryIndex !== -1) kategoriProdukData.splice(categoryIndex, 1);
            delete kategoriProdukIdsByName[categoryName];
        } catch (error) {
            alert(error.message);
            button.disabled = false;
        }
    }

    function addProductRow(data = null) {
        productRowCount++;
        const container = document.getElementById('productRows');
        const rowId = `product-row-${productRowCount}`;

        // Options Kategori Produk
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
                    {{-- Kategori Produk --}}
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
                                <input type="text" class="product-kategori-baru-input flex-1 min-w-0 border border-purple-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" placeholder="Ketik kategori baru...">
                                <button type="button" onclick="simpanKategoriProdukRowBaru(this)" class="product-kategori-baru-button bg-purple-600 text-white px-2 py-1 rounded hover:bg-purple-700 text-[10px] font-medium whitespace-nowrap">
                                    Tambah
                                </button>
                            </div>
                            <p class="product-kategori-baru-error hidden text-[10px] text-red-600 mt-1"></p>
                        </div>
                    </div>

                    {{-- Nama Produk --}}
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" class="product-nama w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.nama_produk || ''}" required placeholder="cth: Kaos Oblong">
                    </div>

                    {{-- Warna --}}
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Warna</label>
                        <input type="text" class="product-warna w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.warna || ''}" placeholder="Hitam">
                    </div>

                    {{-- Ukuran --}}
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Ukuran</label>
                        <input type="text" class="product-ukuran w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.ukuran || ''}" placeholder="L, XL">
                    </div>

                    {{-- Qty --}}
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Qty <span class="text-red-500">*</span></label>
                        <input type="number" class="product-qty w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data?.jumlah_hanca || ''}" min="1" required placeholder="0" oninput="hitungTotal()">
                    </div>

                    {{-- Harga --}}
                    <div class="col-span-1 sm:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Harga <span class="text-red-500">*</span></label>
                        <input type="text" class="product-harga w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500" value="${data ? formatRupiah(data.harga) : ''}" required placeholder="0" oninput="formatHargaInput(this); hitungTotal()">
                    </div>
                </div>

                {{-- Tombol Hapus Baris --}}
                <button type="button" onclick="removeProductRow('${rowId}')" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600 shadow-sm" title="Hapus produk">
                    &times;
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        hitungTotal();
    }

    // ============================================================
    // HAPUS BARIS PRODUK
    // ============================================================
    function removeProductRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            hitungTotal();
        }
    }

    // ============================================================
    // HANDLE KATEGORI PRODUK BARU (PER BARIS)
    // ============================================================
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
            input.value = '';
            error.textContent = '';
            error.classList.add('hidden');
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
        if (!kategori) {
            error.textContent = 'Kategori produk tidak boleh kosong.';
            error.classList.remove('hidden');
            input.focus();
            return;
        }

        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = '...';

        const formData = new FormData();
        formData.append('kategori_produk', kategori);

        try {
            const response = await fetch("{{ route('konveksi.kategori-produk.store-ajax') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Gagal menambahkan kategori produk.');
            }

            const savedCategory = data.kategori;
            if (!kategoriProdukData.includes(savedCategory)) {
                kategoriProdukData.push(savedCategory);
            }
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

    // ============================================================
    // FORMAT HARGA INPUT
    // ============================================================
    function formatHargaInput(input) {
        let v = input.value.replace(/[^0-9]/g, '');
        input.value = v ? formatRupiah(v) : '';
    }

    // ============================================================
    // HITUNG TOTAL SEMUA PRODUK
    // ============================================================
    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.product-row').forEach(row => {
            const qty = parseInt(row.querySelector('.product-qty')?.value) || 0;
            const harga = parseAngka(row.querySelector('.product-harga')?.value || '');
            total += qty * harga;
        });
        document.getElementById('hancaTotalPreview').textContent = 'Rp ' + formatRupiah(total);
    }

    // ============================================================
    // MODAL HANCA - OPEN CREATE
    // ============================================================
    function openHancaModal() {
        document.getElementById('hancaForm').reset();
        document.getElementById('modalErrorHanca').classList.add('hidden');
        document.getElementById('hancaMode').value = 'create';
        document.getElementById('hancaId').value = '';
        document.getElementById('hancaModalTitle').textContent = 'Tambah Data Hanca';
        document.getElementById('hancaModalIcon').setAttribute('icon', 'mdi:plus-circle');
        document.getElementById('btnSubmitHancaText').textContent = 'Simpan';
        document.getElementById('hancaTanggal').value = new Date().toISOString().slice(0, 10);

        // Reset kategori kerja
        document.getElementById('hancaKategoriSelect').value = '';
        handleKategoriChange();
        document.getElementById('kategoriBaruWrapper').classList.add('hidden');
        document.getElementById('hancaKategoriBaru').value = '';

        // Reset produk & tambah 1 baris default
        document.getElementById('productRows').innerHTML = '';
        productRowCount = 0;
        addProductRow();

        document.getElementById('hancaModal').classList.remove('hidden');
        document.getElementById('hancaModal').classList.add('flex');
    }

    // ============================================================
    // MODAL HANCA - OPEN EDIT (dengan data produk)
    // ============================================================
    function openEditHancaModal(id, karyawanId, kategori, tanggal, products) {
        document.getElementById('hancaForm').reset();
        document.getElementById('modalErrorHanca').classList.add('hidden');
        document.getElementById('hancaMode').value = 'edit';
        document.getElementById('hancaId').value = id;
        document.getElementById('hancaModalTitle').textContent = 'Edit Data Hanca';
        document.getElementById('hancaModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('btnSubmitHancaText').textContent = 'Simpan Perubahan';

        document.getElementById('hancaKaryawanId').value = karyawanId;
        document.getElementById('hancaTanggal').value = tanggal;

        // Set kategori kerja
        const selectKerja = document.getElementById('hancaKategoriSelect');
        let found = false;
        for (let opt of selectKerja.options) {
            if (opt.value === kategori && opt.value !== '__new__') {
                selectKerja.value = kategori;
                found = true;
                break;
            }
        }
        if (!found && kategori) {
            const newOpt = document.createElement('option');
            newOpt.value = kategori;
            newOpt.textContent = kategori;
            selectKerja.insertBefore(newOpt, selectKerja.querySelector('option[value="__new__"]'));
            selectKerja.value = kategori;
        }
        handleKategoriChange();

        document.getElementById('kategoriBaruWrapper').classList.add('hidden');
        document.getElementById('hancaKategoriBaru').value = '';

        // Render produk
        document.getElementById('productRows').innerHTML = '';
        productRowCount = 0;

        if (products && products.length > 0) {
            products.forEach(p => addProductRow(p));
        } else {
            addProductRow();
        }

        document.getElementById('hancaModal').classList.remove('hidden');
        document.getElementById('hancaModal').classList.add('flex');
    }

    // ============================================================
    // CLOSE MODAL
    // ============================================================
    function closeHancaModal() {
        document.getElementById('hancaModal').classList.add('hidden');
        document.getElementById('hancaModal').classList.remove('flex');
    }

    // ============================================================
    // SUBMIT HANCA (MULTI PRODUK)
    // ============================================================
    async function submitHanca(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitHanca');
        const btnText = document.getElementById('btnSubmitHancaText');
        const errDiv = document.getElementById('modalErrorHanca');
        const mode = document.getElementById('hancaMode').value;
        const id = document.getElementById('hancaId').value;
        const orig = btnText.textContent;

        // Tentukan kategori kerja final
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

        // Kumpulkan data produk
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

            if (!nama || qty <= 0 || harga <= 0) {
                valid = false;
                return;
            }

            products.push({
                id: row.querySelector('.product-id').value || null,
                kategori_produk: row.querySelector('.product-kategori-produk').value,
                nama_produk: nama,
                warna: row.querySelector('.product-warna').value.trim(),
                ukuran: row.querySelector('.product-ukuran').value.trim(),
                jumlah_hanca: qty,
                harga: harga,
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

        // Buat FormData
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
                headers: { 
                    'X-CSRF-TOKEN': CSRF_TOKEN, 
                    'Accept': 'application/json' 
                },
            });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.message || 'Gagal menghapus.');
            
            // ✅ Refresh halaman agar data terbaru
            window.location.reload();
            
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    function handleBayarSebagianChange() {
        const checked = document.getElementById('checkBayarSebagian').checked;
        const wrapper = document.getElementById('qtySetorWrapper');
        const qtyInput = document.getElementById('bayarQtySetor');
        const nominalInput = document.getElementById('bayarNominal');

        if (checked) {
            // Jika Parsial: Tampilkan Qty, Buka Nominal
            wrapper.classList.remove('hidden');
            nominalInput.readOnly = false;
            nominalInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
            
            // Reset isi
            qtyInput.value = '';
            nominalInput.value = '';
            qtyInput.focus();
            
            // Update hint
            const hint = document.getElementById('nominalHint');
            if (hint) hint.classList.add('hidden');
            
        } else {
            // Jika tidak Parsial (Lunas Semua): Sembunyikan Qty, Kunci Nominal
            wrapper.classList.add('hidden');
            nominalInput.readOnly = true;
            nominalInput.classList.add('bg-gray-100', 'cursor-not-allowed');
            
            qtyInput.value = '';
            autoLunasiSemua();
        }
        
        updateInfoPreview();
    }

    // ============================================================
    // AUTO LUNASI SEMUA (Jika tidak centang parsial)
    // ============================================================
    function autoLunasiSemua() {
        if (!currentHancaData) return;

        const totalSisaQty = currentHancaData.qty;
        const totalSisaNominal = currentHancaData.sisa;

        document.getElementById('bayarQtySetor').value = totalSisaQty;
        document.getElementById('bayarNominal').value = formatRupiah(totalSisaNominal);

        const hint = document.getElementById('nominalHint');
        if (hint) {
            hint.textContent = `Melunasi semua sisa: Rp ${formatRupiah(totalSisaNominal)}`;
            hint.classList.remove('hidden');
            hint.classList.add('text-emerald-600');
        }
    }

    function hitungNominalCicil() {
        if (!currentHancaData) return;

        const qty = parseInt(document.getElementById('bayarQtySetor').value) || 0;
        const sisaQty = currentHancaData.qty;
        const harga = currentHancaData.harga;

        // ✅ Jika qty melebihi sisa, paksa jadi sisa
        if (qty > sisaQty) {
            document.getElementById('bayarQtySetor').value = sisaQty;
            // Panggil ulang fungsi ini agar nominal dihitung ulang
            return hitungNominalCicil();
        }

        let nominal = qty * harga;

        document.getElementById('bayarNominal').value = nominal > 0 ? formatRupiah(nominal) : '';

        const hint = document.getElementById('nominalHint');
        if (hint) {
            hint.textContent = `Nominal: Rp ${formatRupiah(nominal)} (${qty} × Rp ${formatRupiah(harga)})`;
            hint.classList.remove('hidden');
            hint.classList.add('text-emerald-600');
        }

        updateInfoPreview();
    }


    async function deleteHanca(id) {
        if (!confirm('Yakin hapus data hanca ini? Semua riwayat pembayaran juga akan terhapus.')) return;
        try {
            const res = await fetch(`/konveksi/hanca/${id}/ajax`, {
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

    async function submitPengeluaran(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitPengeluaran');
        const errDiv = document.getElementById('modalErrorPengeluaran');
        const orig = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(e.target);
        formData.set('nominal', parseAngka(document.getElementById('pengeluaranNominal').value));

        try {
            const res = await fetch("{{ route('konveksi.pengeluaran.store-ajax') }}", {
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

    async function simpanKategoriBaru() {
        const input = document.getElementById('hancaKategoriBaru');
        const btn = document.getElementById('btnSimpanKategori');
        const select = document.getElementById('hancaKategoriSelect');
        const wrapper = document.getElementById('kategoriBaruWrapper');
        const errDiv = document.getElementById('modalErrorHanca');
        
        const kategori = input.value.trim();
        
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
                headers: { 
                    'X-CSRF-TOKEN': CSRF_TOKEN, 
                    'Accept': 'application/json' 
                },
                body: formData,
            });
            
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.message || 'Gagal menambah kategori.');
            
            // ✅ Tambahkan opsi baru ke select
            const newOption = document.createElement('option');
            newOption.value = data.kategori;
            newOption.dataset.id = data.id;
            newOption.textContent = data.kategori;
            kategoriProdukIdsByName[data.kategori] = data.id;
            kategoriIdsByName[data.kategori] = data.id;
            
            // Sisipkan sebelum opsi "+ Tambah Kategori Baru..."
            select.insertBefore(newOption, select.querySelector('option[value="__new__"]'));
            
            // Pilih kategori baru
            select.value = data.kategori;
            
            // Sembunyikan wrapper & reset input
            wrapper.classList.add('hidden');
            input.value = '';
            
            // Reset tombol
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah';
            
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah';
        }
    }

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

    // ============================================================
    // MODAL BAYAR
    // ============================================================
    function switchTab(tab) {
        const formHanca = document.getElementById('bayarForm');
        const formPengeluaran = document.getElementById('pengeluaranForm');
        const btnHanca = document.getElementById('tabBtnHanca');
        const btnPengeluaran = document.getElementById('tabBtnPengeluaran');

        if (tab === 'hanca') {
            formHanca.classList.remove('hidden');
            formHanca.classList.add('flex');
            formPengeluaran.classList.add('hidden');
            formPengeluaran.classList.remove('flex');

            btnHanca.className = 'flex-1 py-3 text-sm font-semibold text-emerald-600 border-b-2 border-emerald-600 bg-white transition';
            btnPengeluaran.className = 'flex-1 py-3 text-sm font-semibold text-gray-500 hover:text-gray-700 transition';
        } else {
            formHanca.classList.add('hidden');
            formHanca.classList.remove('flex');
            formPengeluaran.classList.remove('hidden');
            formPengeluaran.classList.add('flex');

            btnPengeluaran.className = 'flex-1 py-3 text-sm font-semibold text-emerald-600 border-b-2 border-emerald-600 bg-white transition';
            btnHanca.className = 'flex-1 py-3 text-sm font-semibold text-gray-500 hover:text-gray-700 transition';
        }
    }

    let setorState = {};

    // Update openBayarModal
    function openBayarModal() {
        document.getElementById('bayarForm').reset();
        document.getElementById('modalErrorBayar').classList.add('hidden');
        document.getElementById('bayarTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('setorTableWrapper').classList.add('hidden');
        document.getElementById('kasbonSection').classList.add('hidden');

        totalNominalHanca = 0;
        totalKasbonDipotong = 0;
        document.getElementById('rowPotongKasbon').classList.add('hidden');
        document.getElementById('subtotalDiterima').textContent = 'Rp 0';

        setorState = {};
        $('#bayarHancaIds').val(null).trigger('change');

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
            document.getElementById('kasbonSection').classList.add('hidden'); // Sembunyikan kasbon
            tbody.innerHTML = '';
            setorState = {};
            updateTotals();
            return;
        }

        // Bangun state baru, pertahankan qty yang sudah diisi user kalau masih ada
        const newState = {};
        selectedOpts.forEach(opt => {
            const hancaId = opt.dataset.hancaId;
            const detailId = opt.dataset.detailId;
            
            // ✅ GUNAKAN DETAIL ID SEBAGAI KEY, BUKAN HANCA ID
            const key = detailId; 

            // Kalau sudah ada state, pertahankan qty-nya
            const existing = setorState[key];
            const sisaQty = parseInt(opt.dataset.sisaQty) || 0;
            const harga = parseInt(opt.dataset.harga) || 0;
            const qty = existing && existing.qty <= sisaQty ? existing.qty : sisaQty;

            newState[key] = {
                hancaId,
                detailId,
                produk: opt.dataset.produk,
                warna: opt.dataset.warna,
                ukuran: opt.dataset.ukuran,
                karyawan: opt.dataset.karyawan,
                kategori: opt.dataset.kategori,
                sisaQty,
                harga,
                qty,
                nominal: qty * harga,
            };
        });
        setorState = newState;

        // Render baris
        tbody.innerHTML = '';
        Object.values(setorState).forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-2 py-2">
                    <div class="font-medium text-gray-800 text-xs">${item.produk}</div>
                    <div class="text-[10px] text-gray-500">${item.kategori}${item.warna ? ' · ' + item.warna : ''}${item.ukuran ? ' · ' + item.ukuran : ''}</div>
                </td>
                <td class="px-2 py-2 text-center text-xs text-orange-600 font-semibold">${item.sisaQty}</td>
                <td class="px-2 py-2 text-center">
                    <input type="number" min="1" max="${item.sisaQty}" value="${item.qty}"
                        oninput="onQtyChange('${item.detailId}', this)"
                        class="w-16 border border-gray-300 rounded px-1.5 py-1 text-xs text-center focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </td>
                <td class="px-2 py-2 text-right font-semibold text-gray-800 text-xs">
                    Rp ${formatRupiah(item.nominal)}
                </td>
            `;
            tbody.appendChild(tr);
        });

        wrapper.classList.remove('hidden');
        updateTotals();

        const karyawanId = selectedOpts[0].dataset.karyawanId; 
        loadKasbonKaryawan(karyawanId);
    }

    async function loadKasbonKaryawan(karyawanId) {
        const section = document.getElementById('kasbonSection');
        const list = document.getElementById('kasbonList');
        
        // Tampilkan sementara teks loading
        list.innerHTML = '<div class="text-xs text-orange-500">Memuat data kasbon...</div>';
        section.classList.remove('hidden'); // Tampilkan dulu untuk loading

        try {
            const res = await fetch(`/konveksi/kasbon/karyawan-ajax?karyawan_id=${karyawanId}`);
            const data = await res.json();

            // ✅ JIKA TIDAK ADA KASBON, SEMBUNYIKAN SECTION & KOSONGKAN LIST
            if (data.length === 0) {
                section.classList.add('hidden'); 
                list.innerHTML = '';
                return;
            }

            // Jika ada kasbon, pastikan section tampil dan render datanya
            section.classList.remove('hidden');
            list.innerHTML = '';
            
            data.forEach(kasbon => {
                const div = document.createElement('label');
                div.className = 'flex items-center gap-2 cursor-pointer p-1.5 hover:bg-orange-100 rounded transition';
                div.innerHTML = `
                    <input type="checkbox" name="kasbon_ids[]" value="${kasbon.id}" 
                        data-nominal="${kasbon.nominal}" 
                        onchange="hitungSubtotal()"
                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500">
                    ${kasbon.keterangan ? `
                    <div class="flex-1">
                        <div class="text-xs font-medium text-gray-800">Rp ${formatRupiah(kasbon.nominal)}</div>
                        <div class="text-[10px] text-gray-500">${kasbon.keterangan}</div>
                    </div>
                    ` : `
                    <div class="flex-1">
                        <div class="text-xs font-medium text-gray-800">Rp ${formatRupiah(kasbon.nominal)}</div>
                    </div>
                    `}
                `;
                list.appendChild(div);
            });

            hitungSubtotal();

        } catch (err) {
            // Jika error (misal jaringan), sembunyikan juga agar tidak mengganggu
            section.classList.add('hidden');
            list.innerHTML = '';
        }
    }

    function onQtyChange(detailId, input) {
        const item = setorState[detailId];
        if (!item) return;

        let qty = parseInt(input.value) || 0;
        if (qty < 1) qty = 1;
        if (qty > item.sisaQty) qty = item.sisaQty;

        input.value = qty;
        item.qty = qty;
        item.nominal = qty * item.harga;

        // Update nominal di baris ini
        const row = input.closest('tr');
        row.querySelector('td:last-child').textContent = 'Rp ' + formatRupiah(item.nominal);

        updateTotals();
    }


    function updateTotals() {
        let totalQty = 0;
        totalNominalHanca = 0; // Reset total
        
        Object.values(setorState).forEach(item => {
            totalQty += item.qty;
            totalNominalHanca += item.nominal;
        });

        const elQty = document.getElementById('totalQtySetor');
        const elNom = document.getElementById('totalNominalSetor');
        if (elQty) elQty.textContent = totalQty;
        if (elNom) elNom.textContent = 'Rp ' + formatRupiah(totalNominalHanca);

        // ✅ Panggil perhitungan subtotal setiap kali total berubah
        hitungSubtotal();
    }

    function hitungTotalKasbonDicentang() {
        const checkboxes = document.querySelectorAll('input[name="kasbon_ids[]"]:checked');
        let total = 0;
        checkboxes.forEach(cb => {
            // Ambil nominal dari atribut data-nominal di checkbox
            total += parseInt(cb.dataset.nominal) || 0;
        });
        return total;
    }

    function hitungSubtotal() {
        let totalKasbonDipotong = hitungTotalKasbonDicentang();
        
        // Jika kasbon lebih besar dari total setoran, potong hanya sebatas total setoran
        let potong = Math.min(totalKasbonDipotong, totalNominalHanca);
        
        let subtotal = totalNominalHanca - potong;

        const rowPotong = document.getElementById('rowPotongKasbon');
        const elPotong = document.getElementById('potongKasbonNominal');
        const elSubtotal = document.getElementById('subtotalDiterima');

        if (potong > 0) {
            rowPotong.classList.remove('hidden');
            elPotong.textContent = '- Rp ' + formatRupiah(potong);
        } else {
            rowPotong.classList.add('hidden');
        }

        elSubtotal.textContent = 'Rp ' + formatRupiah(subtotal);
    }


    document.addEventListener('DOMContentLoaded', function () {
        $('#bayarHancaIds').select2({
            placeholder: "-- Pilih Hanca (Bisa lebih dari 1) --",
            allowClear: true,
            dropdownParent: $('#bayarModal') // Agar dropdown muncul di dalam modal
        });
    });


    function loadHancaInfo() {
        const select = document.getElementById('bayarHancaIds');
        const selectedOptions = Array.from(select.selectedOptions);
        const infoDiv = document.getElementById('hancaInfo');

        if (selectedOptions.length === 0) {
            infoDiv.classList.add('hidden');
            currentHancaData = null;
            return;
        }

        let total = 0;
        let dibayar = 0;
        let sisa = 0;
        let totalQty = 0;
        let harga = 0;

        selectedOptions.forEach(opt => {
            total += parseInt(opt.dataset.total) || 0;
            dibayar += parseInt(opt.dataset.dibayar) || 0;
            sisa += parseInt(opt.dataset.sisa) || 0;
            // ✅ Pastikan dataset.qty dibaca dengan benar
            totalQty += parseInt(opt.dataset.qty) || 0; 
            harga = parseInt(opt.dataset.harga) || 0;
        });

        // ✅ Simpan data ke global variable
        currentHancaData = {
            total: total,
            dibayar: dibayar,
            sisa: sisa,
            qty: totalQty, // Ini yang akan membatasi input Qty
            harga: harga,
        };

        document.getElementById('infoTotal').textContent = 'Rp ' + formatRupiah(total);
        document.getElementById('infoDibayar').textContent = 'Rp ' + formatRupiah(dibayar);
        document.getElementById('infoSisa').textContent = 'Rp ' + formatRupiah(sisa);
        infoDiv.classList.remove('hidden');

        // ✅ Panggil fungsi ini untuk mengatur status input (readonly atau tidak)
        handleBayarSebagianChange();
    }

    function updateInfoPreview() {
        if (!currentHancaData) return;

        const nominal = parseAngka(document.getElementById('bayarNominal').value);

        const dibayarBaru = currentHancaData.dibayar + nominal;
        let sisaNominal = currentHancaData.sisa - nominal;
        if (sisaNominal < 0) sisaNominal = 0;

        document.getElementById('infoTotal').textContent = 'Rp ' + formatRupiah(currentHancaData.total);
        document.getElementById('infoDibayar').textContent = 'Rp ' + formatRupiah(dibayarBaru);

        const infoSisa = document.getElementById('infoSisa');
        infoSisa.textContent = 'Rp ' + formatRupiah(sisaNominal);
        infoSisa.className = 'font-bold text-lg ' + (sisaNominal > 0 ? 'text-red-600' : 'text-green-600');
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
            errDiv.textContent = 'Isi Qty Setor minimal 1 unit pada salah satu hanca.';
            errDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('tipe', 'bayaran');
        formData.append('tanggal', document.getElementById('bayarTanggal').value);
        formData.append('keterangan', document.getElementById('bayarKeterangan').value);

        items.forEach(item => {
            formData.append(`qty_setor[${item.detailId}]`, item.qty);
            formData.append(`nominal_setor[${item.detailId}]`, item.nominal);
        });

        // ✅ PASTIKAN CHECKBOX KASBON DIAMBIL
        const selectedKasbons = document.querySelectorAll('input[name="kasbon_ids[]"]:checked');
        selectedKasbons.forEach(cb => {
            formData.append('kasbon_ids[]', cb.value);
        });

        try {
            const res = await fetch("{{ route('konveksi.pembayaran.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            
            // ✅ Tangkap response text dulu untuk melihat error asli jika ada
            const textData = await res.text();
            let data;
            try {
                data = JSON.parse(textData);
            } catch (err) {
                console.error("Respon server bukan JSON:", textData);
                throw new Error("Terjadi kesalahan pada server. Cek console untuk detail.");
            }

            if (!res.ok) throw new Error(data.message || 'Gagal.');

            if (data.pembayaran_ids && data.pembayaran_ids.length > 0) {
                window.open(`/konveksi/pembayaran/${data.pembayaran_ids[0]}/invoice-pdf`, '_blank');
            }

            window.location.href = "{{ route('konveksi.data-bayaran') }}";
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }


    // ============================================================
    // MODAL DETAIL
    // ============================================================
    function openDetailModal(hancaId) {
        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('detailModal').classList.add('flex');
        document.getElementById('detailContent').innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <iconify-icon icon="mdi:loading" class="text-4xl animate-spin block m-auto mb-2"></iconify-icon>
                Memuat data...
            </div>`;

        // Kita akan fetch data via AJAX, tapi karena belum ada route detail, 
        // sementara kita render dari data yang ada di tabel (bisa dikembangkan nanti)
        // Untuk sekarang, kita reload halaman saja atau tampilkan pesan.
        // Idealnya buat route GET /konveksi/hanca/{id}/detail-ajax
        
        // Fallback: Tampilkan pesan
        setTimeout(() => {
            document.getElementById('detailContent').innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <p>Fitur detail sedang dalam pengembangan.</p>
                    <p class="text-xs mt-2">Silakan gunakan tombol Edit atau hapus jika perlu.</p>
                </div>`;
        }, 500);
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
        document.getElementById('detailModal').classList.remove('flex');
    }

    function handleKategoriProdukChange() {
        const select = document.getElementById('hancaKategoriProdukSelect');
        const wrapper = document.getElementById('kategoriProdukBaruWrapper');
        const inputBaru = document.getElementById('hancaKategoriProdukBaru');

        if (select.value === '__new__') {
            wrapper.classList.remove('hidden');
            inputBaru.focus();
        } else {
            wrapper.classList.add('hidden');
            inputBaru.value = '';
        }
    }

    async function simpanKategoriProdukBaru() {
        const input = document.getElementById('hancaKategoriProdukBaru');
        const btn = document.getElementById('btnSimpanKategoriProduk');
        const select = document.getElementById('hancaKategoriProdukSelect');
        const wrapper = document.getElementById('kategoriProdukBaruWrapper');
        const errDiv = document.getElementById('modalErrorHanca');
        
        const kategori = input.value.trim();
        
        if (!kategori) {
            errDiv.textContent = 'Kategori produk tidak boleh kosong.';
            errDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '...';
        errDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('kategori_produk', kategori);
        formData.append('_token', CSRF_TOKEN);

        try {
            const res = await fetch("{{ route('konveksi.kategori-produk.store-ajax') }}", {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': CSRF_TOKEN, 
                    'Accept': 'application/json' 
                },
                body: formData,
            });
            
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menambah kategori produk.');
            
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
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Tambah';
        }
    }

    // ============================================================
    // EVENT LISTENER
    // ============================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Format input harga
        const hancaHarga = document.getElementById('hancaHarga');
        if (hancaHarga) {
            hancaHarga.addEventListener('input', function () {
                let v = this.value.replace(/[^0-9]/g, '');
                this.value = v ? formatRupiah(v) : '';
                updateTotalPreview();
            });
        }

        const hancaJumlah = document.getElementById('hancaJumlah');
        if (hancaJumlah) {
            hancaJumlah.addEventListener('input', updateTotalPreview);
        }

        const bayarNominal = document.getElementById('bayarNominal');
        if (bayarNominal) {
            bayarNominal.addEventListener('input', function () {
                let v = this.value.replace(/[^0-9]/g, '');
                this.value = v ? formatRupiah(v) : '';
                updateInfoPreview();
            });
        }

        const pengeluaranNominal = document.getElementById('pengeluaranNominal');
        if (pengeluaranNominal) {
            pengeluaranNominal.addEventListener('input', function () {
                let v = this.value.replace(/[^0-9]/g, '');
                this.value = v ? formatRupiah(v) : '';
            });
        }
    });

    // ESC = Tutup semua modal
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeKaryawanModal();
            closeHancaModal();
            closeBayarModal();
            closeDetailModal();
        }
    });
</script>
@endsection