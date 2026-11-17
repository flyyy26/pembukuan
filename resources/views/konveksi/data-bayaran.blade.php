@extends('layouts.app')

@section('title', 'Data Bayaran Konveksi')

@section('content')
<div class="w-full min-w-0">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:cash-check" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Bayaran</h2>
                <p class="text-xs sm:text-sm text-gray-500">Proses pembayaran upah karyawan konveksi</p>
            </div>
        </div>

        {{-- ✅ TAMBAHKAN TOMBOL INI --}}
        <button type="button" onclick="openNotaKosongModal()"
           class="inline-flex items-center justify-center gap-2 bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:printer" class="text-lg"></iconify-icon>
            Cetak Nota Kosong
        </button>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan</label>
                <select name="karyawan_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $karyawanId === 'all' ? 'selected' : '' }}>Semua Karyawan</option>
                    @foreach($karyawanList as $k)
                        <option value="{{ $k->id }}" {{ $karyawanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan Setor</label>
                <select name="bulan" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama karyawan / produk..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $karyawanId !== 'all' || $bulanFilter !== now()->format('Y-m'))
                    <a href="{{ route('konveksi.data-bayaran') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- BAGIAN 1: BELUM DIBAYAR (MENUNGGU PEMBAYARAN) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-amber-50">
            <div>
                <h3 class="font-semibold text-amber-800 text-sm sm:text-base flex items-center gap-2">
                    <iconify-icon icon="mdi:clock-alert-outline"></iconify-icon> Menunggu Pembayaran
                </h3>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-[10px] text-amber-600 uppercase font-semibold">Total Terpilih</div>
                    <div id="headerTotalTerpilih" class="text-sm font-bold text-amber-800">Rp 0</div>
                </div>
                <button type="button" onclick="openBayarModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition text-sm font-medium shadow-sm">
                    <iconify-icon icon="mdi:cash-multiple" class="text-lg"></iconify-icon> Bayar Terpilih
                </button>
            </div>
        </div>

        @if($pembayaranBelumDibayar->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:check-all" class="text-5xl text-emerald-300 block m-auto mb-3 w-max"></iconify-icon>
                <span class="text-sm font-medium">Semua setoran sudah dibayar.</span>
                <p class="text-xs text-gray-400 mt-1">Data akan muncul di sini jika ada setoran baru dari halaman Setor Hanca.</p>
            </div>
        @else
            <div class="w-full overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left min-w-[900px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox" id="checkAllPembayaran" onchange="toggleAllPembayaran(this)" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 cursor-pointer">
                            </th>
                            <th class="py-3 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap">Tgl Setor</th>
                            <th class="py-3 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-3 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap">Produk</th>
                            <th class="py-3 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Qty</th>
                            <th class="py-3 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Harga</th>
                            <th class="py-3 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Total Upah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pembayaranBelumDibayar as $p)
                            @php
                                $harga = $p->hancaDetail->harga ?? 0;
                                $totalUpah = $p->qty_setor * $harga;
                            @endphp
                            <tr class="hover:bg-amber-50 transition cursor-pointer" onclick="toggleRow(this)">
                                <td class="py-2 px-3 text-center">
                                    <input type="checkbox" name="pembayaran_ids[]" value="{{ $p->id }}" 
                                        data-karyawan-id="{{ $p->hanca->konveksi_karyawan_id }}"
                                        data-nominal="{{ $totalUpah }}"
                                        data-karyawan="{{ $p->hanca->karyawan->nama ?? '-' }}" 
                                        data-produk="{{ $p->hancaDetail->nama_produk ?? '-' }}"
                                        data-qty="{{ $p->qty_setor }}"
                                        onchange="updateTotalBayar()" 
                                        class="pembayaran-checkbox w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 cursor-pointer">
                                </td>
                                <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">{{ $p->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $p->hanca->karyawan->nama ?? '-' }}</td>
                                <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $p->hancaDetail->nama_produk ?? '-' }} 
                                    <span class="text-gray-400">({{ $p->hancaDetail->warna ?? '-' }})</span>
                                </td>
                                <td class="py-2 px-3 text-right font-semibold text-gray-800 whitespace-nowrap">{{ $p->qty_setor }} pcs</td>
                                <td class="py-2 px-3 text-right text-gray-700 whitespace-nowrap">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                                <td class="py-2 px-3 text-right font-bold text-emerald-600 whitespace-nowrap">Rp {{ number_format($totalUpah, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- BAGIAN 2: RIWAYAT DIBAYAR --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base flex items-center gap-2">
                <iconify-icon icon="mdi:history" class="text-gray-500"></iconify-icon> Riwayat Pembayaran
            </h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $pembayaranRiwayat->count() }} Data</div>
        </div>

        @if($pembayaranRiwayat->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:file-document-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada riwayat pembayaran.</span>
            </div>
        @else
            <div class="w-full overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left min-w-[900px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap">Tgl Bayar</th>
                            <th class="py-2 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-2 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap">Produk</th>
                            <th class="py-2 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Qty</th>
                            <th class="py-2 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap text-right">Total Bayar</th>
                            <th class="py-2 px-3 font-semibold text-gray-500 uppercase whitespace-nowrap text-center">Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pembayaranRiwayat as $p)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">{{ $p->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $p->hanca->karyawan->nama ?? '-' }}</td>
                                <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">{{ $p->hancaDetail->nama_produk ?? '-' }}</td>
                                <td class="py-2 px-3 text-right font-semibold text-gray-800 whitespace-nowrap">{{ $p->qty_setor }}</td>
                                <td class="py-2 px-3 text-right font-bold text-emerald-600 whitespace-nowrap">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                <td class="py-2 px-3 text-center">
                                    <a href="{{ route('konveksi.pembayaran.invoice-pdf', $p) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium border border-blue-200">
                                        <iconify-icon icon="mdi:file-pdf-box" class="text-sm"></iconify-icon> Cetak
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- MODAL KONFIRMASI BAYAR --}}
<div id="bayarModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBayarModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        
        {{-- HEADER MODAL --}}
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-amber-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cash-check" class="text-amber-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Konfirmasi Pembayaran</h3>
            </div>
            <button type="button" onclick="closeBayarModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="bayarForm" onsubmit="submitBayar(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorBayar"></div>

                {{-- LIST ITEM YANG DIPILIH --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Setoran yang Dibayar</label>
                    <div class="border border-gray-200 rounded-lg overflow-hidden max-h-60 overflow-y-auto bg-gray-50">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-100 border-b border-gray-200 sticky top-0">
                                <tr>
                                    <th class="text-left px-3 py-2 font-semibold text-gray-600">Karyawan / Produk</th>
                                    <th class="text-center px-3 py-2 font-semibold text-gray-600">Qty</th>
                                    <th class="text-right px-3 py-2 font-semibold text-gray-600">Total Upah</th>
                                </tr>
                            </thead>
                            <tbody id="bayarTableBody" class="divide-y divide-gray-100 bg-white">
                                {{-- Diisi via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- KASBON --}}
                <div id="modalKasbonSection" class="hidden bg-orange-50 border border-orange-200 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <iconify-icon icon="mdi:cash-remove" class="text-orange-600"></iconify-icon>
                        <span class="text-xs font-bold text-orange-800">Potong Kasbon Karyawan</span>
                    </div>
                    <div id="modalKasbonList" class="space-y-1.5 max-h-40 overflow-y-auto">
                        {{-- Diisi via JS --}}
                    </div>
                </div>

                {{-- TOTAL --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-gray-100">
                            <tr class="bg-orange-50 hidden" id="rowPotongKasbonModal">
                                <td class="px-3 py-2 text-xs font-semibold text-orange-800">Potong Kasbon</td>
                                <td id="modalPotongKasbonNominal" class="px-3 py-2 text-right font-bold text-orange-700">- Rp 0</td>
                            </tr>
                            <tr class="bg-amber-100 border-t-2 border-amber-300">
                                <td class="px-3 py-3 text-xs font-bold text-amber-900 uppercase">Total Diterima</td>
                                <td id="totalNominalBayar" class="px-3 py-3 text-right font-black text-amber-900 text-base">Rp 0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_bayar" id="tanggalBayar" required value="{{ now()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            {{-- FOOTER MODAL (Sticky) --}}
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end items-center flex-shrink-0">
                <div class="flex gap-2">
                    <button type="button" onclick="closeBayarModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                    <button type="submit" id="btnSubmitBayar" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Bayar & Cetak
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL CETAK NOTA KOSONG --}}
<div id="notaKosongModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeNotaKosongModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:printer" class="text-gray-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Form Cetak Nota Kosong</h3>
            </div>
            <button type="button" onclick="closeNotaKosongModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <div class="p-5 overflow-y-auto flex-1 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Penerima</label>
                    <input type="text" id="notaNama" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-500 focus:outline-none" placeholder="Masukkan nama...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                    <input type="date" id="notaTanggal" value="{{ now()->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-500 focus:outline-none">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-medium text-gray-600">Daftar Barang</label>
                    <button type="button" onclick="tambahBarisNota()" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100 transition font-medium">
                        + Tambah Baris
                    </button>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-2 py-2 text-left">Nama Barang</th>
                                <th class="px-2 py-2 text-left w-24">Warna</th>
                                <th class="px-2 py-2 text-left w-20">Size</th>
                                <th class="px-2 py-2 text-center w-16">Qty</th>
                                <th class="px-2 py-2 text-right w-24">Harga</th>
                                <th class="px-2 py-2 text-right w-28">Jumlah</th>
                                <th class="px-2 py-2 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="notaTableBody" class="divide-y divide-gray-100">
                            {{-- Baris di-generate via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
            <button type="button" onclick="closeNotaKosongModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
            <button type="button" onclick="cetakNotaKosong()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 text-sm font-medium flex items-center gap-2">
                <iconify-icon icon="mdi:printer" class="text-lg"></iconify-icon> Cetak PDF
            </button>
        </div>
    </div>
</div>

<script>
    function openNotaKosongModal() {
        document.getElementById('notaKosongModal').classList.remove('hidden');
        document.getElementById('notaKosongModal').classList.add('flex');
        
        // Tambah 1 baris default jika masih kosong
        const tbody = document.getElementById('notaTableBody');
        if (tbody.children.length === 0) {
            tambahBarisNota();
        }
    }

    function closeNotaKosongModal() {
        document.getElementById('notaKosongModal').classList.add('hidden');
        document.getElementById('notaKosongModal').classList.remove('flex');
    }

    function tambahBarisNota() {
        const tbody = document.getElementById('notaTableBody');
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.innerHTML = `
            <td class="px-2 py-1"><input type="text" class="w-full border-none focus:ring-1 focus:ring-gray-300 rounded px-1 py-1" placeholder="Nama Barang"></td>
            <td class="px-2 py-1"><input type="text" class="w-full border-none focus:ring-1 focus:ring-gray-300 rounded px-1 py-1" placeholder="Warna"></td>
            <td class="px-2 py-1"><input type="text" class="w-full border-none focus:ring-1 focus:ring-gray-300 rounded px-1 py-1" placeholder="Size"></td>
            <td class="px-2 py-1"><input type="number" min="1" value="1" class="w-full text-center border-none focus:ring-1 focus:ring-gray-300 rounded px-1 py-1" oninput="hitungJumlahNota(this)"></td>
            <td class="px-2 py-1"><input type="number" min="0" value="0" class="w-full text-right border-none focus:ring-1 focus:ring-gray-300 rounded px-1 py-1" oninput="hitungJumlahNota(this)"></td>
            <td class="px-2 py-1"><input type="number" min="0" value="0" readonly class="w-full text-right bg-gray-50 border-none rounded px-1 py-1 text-gray-600"></td>
            <td class="px-2 py-1 text-center">
                <button type="button" onclick="hapusBarisNota(this)" class="text-red-400 hover:text-red-600">
                    <iconify-icon icon="mdi:trash-can-outline" class="text-sm"></iconify-icon>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function hapusBarisNota(btn) {
        const tr = btn.closest('tr');
        tr.remove();
    }

    function hitungJumlahNota(input) {
        const tr = input.closest('tr');
        const qty = parseFloat(tr.children[3].querySelector('input').value) || 0;
        const harga = parseFloat(tr.children[4].querySelector('input').value) || 0;
        const jumlahInput = tr.children[5].querySelector('input');
        jumlahInput.value = qty * harga;
    }

    function cetakNotaKosong() {
        const nama = document.getElementById('notaNama').value;
        const tanggal = document.getElementById('notaTanggal').value;
        
        const items = [];
        document.querySelectorAll('#notaTableBody tr').forEach(tr => {
            const inputs = tr.querySelectorAll('input');
            const item = {
                nama_produk: inputs[0].value,
                warna: inputs[1].value,
                ukuran: inputs[2].value,
                qty: parseInt(inputs[3].value) || 0,
                harga: parseFloat(inputs[4].value) || 0,
                jumlah: parseFloat(inputs[5].value) || 0
            };
            // Hanya masukkan yang ada nama produknya
            if (item.nama_produk.trim() !== '') {
                items.push(item);
            }
        });

        // Buat form virtual untuk submit data ke Controller
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = "{{ route('konveksi.pembayaran.print-kosong-pdf') }}";
        form.target = '_blank';

        const inputNama = document.createElement('input');
        inputNama.type = 'hidden';
        inputNama.name = 'nama';
        inputNama.value = nama;
        form.appendChild(inputNama);

        const inputTanggal = document.createElement('input');
        inputTanggal.type = 'hidden';
        inputTanggal.name = 'tanggal';
        inputTanggal.value = tanggal;
        form.appendChild(inputTanggal);

        const inputItems = document.createElement('input');
        inputItems.type = 'hidden';
        inputItems.name = 'items';
        inputItems.value = JSON.stringify(items);
        form.appendChild(inputItems);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        closeNotaKosongModal();
    }
</script>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let modalTotalUpah = 0;
    let modalKasbonData = [];

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }
    
    function parseAngka(str) {
        return parseInt(String(str).replace(/[^0-9]/g, '')) || 0;
    }

    function toggleRow(row) {
        const cb = row.querySelector('.pembayaran-checkbox');
        if (cb) {
            cb.checked = !cb.checked;
            updateTotalBayar();
        }
    }

    function toggleAllPembayaran(source) {
        const checkboxes = document.querySelectorAll('.pembayaran-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateTotalBayar();
    }

    function updateTotalBayar() {
        let total = 0;
        let count = 0;
        document.querySelectorAll('.pembayaran-checkbox:checked').forEach(cb => {
            total += parseInt(cb.dataset.nominal) || 0;
            count++;
            
            cb.closest('tr').classList.add('bg-amber-50');
            cb.closest('tr').classList.remove('hover:bg-amber-50');
        });

        document.querySelectorAll('.pembayaran-checkbox:not(:checked)').forEach(cb => {
            cb.closest('tr').classList.remove('bg-amber-50');
        });

        const headerTotal = document.getElementById('headerTotalTerpilih');
        if (headerTotal) {
            headerTotal.textContent = 'Rp ' + formatRupiah(total);
        }
    }

    function openBayarModal() {
        const selected = Array.from(document.querySelectorAll('.pembayaran-checkbox:checked'));
        
        if (selected.length === 0) {
            alert('Pilih minimal 1 setoran yang ingin dibayar.');
            return;
        }

        document.getElementById('modalErrorBayar').classList.add('hidden');
        const tbody = document.getElementById('bayarTableBody');
        tbody.innerHTML = '';
        
        modalTotalUpah = 0;
        modalKasbonData = [];
        
        const selectedKaryawanIds = new Set();

        selected.forEach(cb => {
            const nominal = parseInt(cb.dataset.nominal) || 0;
            modalTotalUpah += nominal;
            
            if (cb.dataset.karyawanId) selectedKaryawanIds.add(cb.dataset.karyawanId);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <div class="font-bold text-gray-800">${cb.dataset.karyawan}</div>
                    <div class="text-[10px] text-gray-500">${cb.dataset.produk}</div>
                </td>
                <td class="px-3 py-2 text-center text-gray-700">${cb.dataset.qty} pcs</td>
                <td class="px-3 py-2 text-right font-bold text-emerald-600">
                    Rp ${formatRupiah(nominal)}
                    <input type="hidden" name="pembayaran_ids[]" value="${cb.value}">
                </td>
            `;
            tbody.appendChild(tr);
        });

        // ✅ Render List Kasbon per Karyawan
        const kasbonListContainer = document.getElementById('modalKasbonList');
        kasbonListContainer.innerHTML = '';
        const kasbonAktif = @json($kasbonAktif ?? []);
        
        let hasKasbon = false;

        selectedKaryawanIds.forEach(id => {
            if (kasbonAktif[id] && kasbonAktif[id].length > 0) {
                hasKasbon = true;
                kasbonAktif[id].forEach((kasbon) => {
                    const uniqueId = `kasbon_${id}_${kasbon.id}`;
                    modalKasbonData.push({
                        id: kasbon.id,
                        nominal: kasbon.sisa,
                        checkboxId: uniqueId
                    });

                    const div = document.createElement('label');
                    div.className = 'flex items-start gap-2 cursor-pointer p-1.5 hover:bg-orange-100 rounded transition';
                    div.innerHTML = `
                        <input type="checkbox" id="${uniqueId}" name="kasbon_ids[]" value="${kasbon.id}" onchange="hitungTotalBayarModal()" class="mt-0.5 w-4 h-4 text-orange-600 rounded focus:ring-orange-500">
                        <div class="flex-1">
                            <div class="text-xs font-medium text-gray-800">Potong Kasbon</div>
                            <div class="text-[10px] text-gray-500">Sisa: Rp ${formatRupiah(kasbon.sisa)}</div>
                        </div>
                    `;
                    kasbonListContainer.appendChild(div);
                });
            }
        });

        if (hasKasbon) {
            document.getElementById('modalKasbonSection').classList.remove('hidden');
        } else {
            document.getElementById('modalKasbonSection').classList.add('hidden');
        }

        hitungTotalBayarModal();
        document.getElementById('bayarModal').classList.remove('hidden');
        document.getElementById('bayarModal').classList.add('flex');
    }

    function hitungTotalBayarModal() {
        let totalKasbon = 0;
        
        modalKasbonData.forEach(item => {
            const cb = document.getElementById(item.checkboxId);
            if (cb && cb.checked) {
                totalKasbon += item.nominal;
            }
        });

        const potong = Math.min(totalKasbon, modalTotalUpah);
        const totalDiterima = modalTotalUpah - potong;

        const rowPotong = document.getElementById('rowPotongKasbonModal');
        const elPotong = document.getElementById('modalPotongKasbonNominal');
        const elTotal = document.getElementById('totalNominalBayar');

        if (potong > 0) {
            rowPotong.classList.remove('hidden');
            elPotong.textContent = '- Rp ' + formatRupiah(potong);
        } else {
            rowPotong.classList.add('hidden');
        }

        elTotal.textContent = 'Rp ' + formatRupiah(totalDiterima);
    }

    function closeBayarModal() {
        document.getElementById('bayarModal').classList.add('hidden');
        document.getElementById('bayarModal').classList.remove('flex');
    }

    async function submitBayar(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitBayar');
        const errDiv = document.getElementById('modalErrorBayar');
        const orig = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Memproses...';
        errDiv.classList.add('hidden');

        const form = e.target;
        const formData = new FormData(form);
        
        const ids = formData.getAll('pembayaran_ids[]');

        try {
            const res = await fetch("{{ route('konveksi.data-bayaran.bayar-massal-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.message || 'Gagal.');

            if (ids.length > 0) {
                const queryString = ids.map(id => `ids[]=${id}`).join('&');
                const pdfUrl = `{{ route('konveksi.pembayaran.print-massal-pdf') }}?${queryString}`;
                window.open(pdfUrl, '_blank');
            }

            setTimeout(() => window.location.reload(), 1000);
            
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }
</script>
@endsection