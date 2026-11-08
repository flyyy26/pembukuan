@extends('layouts.app')

@section('title', 'Hanca Selesai')

@section('content')
<div class="w-full min-w-0">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:check-all" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Hanca Selesai</h2>
                <p class="text-xs sm:text-sm text-gray-500">Daftar produk yang sudah lunas disetor</p>
            </div>
        </div>
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
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, produk..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $karyawanId !== 'all' || $bulanFilter !== now()->format('Y-m'))
                    <a href="{{ route('konveksi.hanca-selesai') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL UTAMA (POTONG KAIN & JAHIT TERHUBUNG) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-4 sm:px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm sm:text-base">Status Produksi (Potong Kain & Jahit)</h3>
            <span class="text-xs text-gray-500">
                Total: {{ $groupedByKategori->get('Potong Kain', collect())->sum(fn($h) => $h->details->count()) }} Data
            </span>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left min-w-[1000px]">
                <thead class="bg-white border-b border-gray-100">
                    <tr>
                        <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-center">Tgl Potong</th>
                        <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Tukang Potong</th>
                        <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Produk (Warna/Size)</th>
                        <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Qty Potong</th>
                        <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-center">Status Jahit</th>
                        <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Tukang Jahit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($groupedByKategori->get('Potong Kain', collect()) as $hancaPotong)
                        @foreach($hancaPotong->details as $detailPotong)
                            @php
                                $jahitDetail = $detailPotong->turunanJahit->first();
                                
                                // ✅ Validasi: hanya anggap "Selesai Jahit" jika Qty Jahit sudah lunas
                                $isJahitSelesai = $jahitDetail && $jahitDetail->sisa_hanca <= 0;
                                
                                $tukangJahit = $isJahitSelesai ? ($jahitDetail->hanca->karyawan->nama ?? '-') : null;
                                $tglJahit = $isJahitSelesai ? $jahitDetail->hanca->tanggal->format('d/m/Y') : null;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap text-center">{{ $hancaPotong->tanggal->format('d/m/Y') }}</td>
                                <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $hancaPotong->karyawan->nama ?? '-' }}</td>
                                <td class="py-2 px-3 text-xs text-gray-800 whitespace-nowrap">
                                    {{ $detailPotong->nama_produk }} 
                                    <span class="text-gray-500">({{ $detailPotong->warna ?? '-' }} / {{ $detailPotong->ukuran ?? '-' }})</span>
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-gray-800 whitespace-nowrap">{{ $detailPotong->jumlah_hanca }} pcs</td>
                                
                                {{-- STATUS JAHIT --}}
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    @if($isJahitSelesai)
                                        <span class="inline-flex items-center gap-1 text-[10px] bg-green-100 text-green-700 border border-green-200 px-2 py-1 rounded font-semibold">
                                            <iconify-icon icon="mdi:check-circle" class="text-xs"></iconify-icon> Selesai Jahit
                                        </span>
                                        <div class="text-[10px] text-gray-500 mt-1">{{ $tglJahit }}</div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] bg-orange-100 text-orange-700 border border-orange-200 px-2 py-1 rounded font-semibold">
                                            <iconify-icon icon="mdi:clock-outline" class="text-xs"></iconify-icon> Belum Dijahit
                                        </span>
                                    @endif
                                </td>

                                {{-- TUKANG JAHIT --}}
                                <td class="py-2 px-3 font-medium whitespace-nowrap {{ $tukangJahit ? 'text-indigo-600' : 'text-gray-400' }}">
                                    {{ $tukangJahit ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">
                                Belum ada data Potong Kain yang selesai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BAGIAN JAHIT YANG TIDAK ADA POTONG KAINNYA (JIKA ADA) --}}
    @php
        $jahitTanpaPotong = collect();
        if ($groupedByKategori->has('Jahit')) {
            foreach($groupedByKategori->get('Jahit') as $h) {
                foreach($h->details as $d) {
                    if (!$d->sumber_potong_id) {
                        $jahitTanpaPotong->push(['hanca' => $h, 'detail' => $d]);
                    }
                }
            }
        }
    @endphp

    @if($jahitTanpaPotong->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="px-4 sm:px-5 py-3 bg-gray-50 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-sm sm:text-base">Jahit Mandiri (Tanpa Potong Kain)</h3>
        </div>
        <table class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-white border-b border-gray-100">
                <tr>
                    <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Tanggal Jahit</th>
                    <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Tukang Jahit</th>
                    <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Produk</th>
                    <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($jahitTanpaPotong as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2 px-3 text-xs text-gray-700">{{ $item['hanca']->tanggal->format('d/m/Y') }}</td>
                        <td class="py-2 px-3 font-medium text-gray-800">{{ $item['hanca']->karyawan->nama ?? '-' }}</td>
                        <td class="py-2 px-3 text-xs text-gray-800">{{ $item['detail']->nama_produk }} ({{ $item['detail']->warna }} / {{ $item['detail']->ukuran }})</td>
                        <td class="py-2 px-3 text-right font-bold text-gray-800">{{ $item['detail']->jumlah_hanca }} pcs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- BAGIAN KATEGORI LAINNYA (SETIM, BUANG BENANG, DLL) --}}
    @php
        // ✅ FIX: Menggunakan filter() pada Collection, bukan except()
        $kategoriLain = $groupedByKategori->filter(function($value, $key) {
            return $key !== 'Potong Kain' && $key !== 'Jahit';
        });
    @endphp

    @if($kategoriLain->count() > 0)
        @foreach($kategoriLain as $kategori => $hancas)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div class="px-4 sm:px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 text-sm sm:text-base">Hanca Selesai - {{ $kategori }}</h3>
                    <span class="text-xs text-gray-500">{{ $hancas->count() }} Hanca</span>
                </div>
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="bg-white border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Tgl Selesai</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Karyawan</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Produk</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-center">Warna / Ukuran</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Qty Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($hancas as $hanca)
                            @foreach($hanca->details as $detail)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">{{ $hanca->updated_at->format('d/m/Y') }}</td>
                                    <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $hanca->karyawan->nama ?? '-' }}</td>
                                    <td class="py-2 px-3 text-xs text-gray-800 whitespace-nowrap">{{ $detail->nama_produk }}</td>
                                    <td class="py-2 px-3 text-center text-xs text-gray-600 whitespace-nowrap">{{ $detail->warna ?? '-' }} / {{ $detail->ukuran ?? '-' }}</td>
                                    <td class="py-2 px-3 text-right font-semibold text-gray-800 whitespace-nowrap">{{ $detail->jumlah_hanca }} pcs</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
@endsection