@extends('layouts.app')

@section('title', 'Data Bayaran')

@section('content')
<div class="w-full min-w-0">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:cash-check" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Bayaran</h2>
                <p class="text-xs sm:text-sm text-gray-500">Riwayat setiap pembayaran setoran hanca karyawan</p>
            </div>
        </div>
    </div>

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
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan Bayar</label>
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari produk / karyawan..."
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Riwayat Pembayaran</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $pembayarans->count() }} Data</div>
        </div>

        @if($pembayarans->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:cash-check" class="text-5xl text-gray-300 block m-auto mb-3 w-max"></iconify-icon>
                <p class="text-sm">Belum ada data pembayaran.</p>
                <p class="text-xs text-gray-400 mt-1">Setiap pembayaran hanca akan muncul di sini, termasuk pembayaran sebagian.</p>
            </div>
        @else
            <div class="w-full overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left min-w-[1100px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">No. Bayar</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal Bayar</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">Produk</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-center">Warna / Ukuran</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Qty Setor</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Harga / Pcs</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Total Upah</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Kasbon</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right">Total Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pembayarans as $pembayaran)
                            @php
                                $detail = $pembayaran->hancaDetail;
                                $kasbonDipotong = (int) $pembayaran->kasbon_dipotong;
                                $totalDiterima = max(0, (int) $pembayaran->nominal - $kasbonDipotong);
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2 px-3 text-xs text-gray-500 whitespace-nowrap">#{{ $pembayaran->id }}</td>
                                <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">
                                    {{ $pembayaran->tanggal?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">
                                    {{ $pembayaran->hanca->karyawan->nama ?? '-' }}
                                </td>
                                <td class="py-2 px-3 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $pembayaran->hanca->kategori ?? '-' }}
                                </td>
                                <td class="py-2 px-3 text-xs text-gray-800 whitespace-nowrap">
                                    {{ $detail->nama_produk ?? '-' }}
                                </td>
                                <td class="py-2 px-3 text-center text-xs text-gray-600 whitespace-nowrap">
                                    {{ $detail->warna ?? '-' }} / {{ $detail->ukuran ?? '-' }}
                                </td>
                                <td class="py-2 px-3 text-right font-semibold text-gray-800 whitespace-nowrap">
                                    {{ number_format((int) $pembayaran->qty_setor, 0, ',', '.') }} pcs
                                </td>
                                <td class="py-2 px-3 text-right text-gray-700 whitespace-nowrap">
                                    Rp {{ number_format((int) ($detail->harga ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-gray-800 whitespace-nowrap">
                                    Rp {{ number_format((int) $pembayaran->nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-orange-600 whitespace-nowrap">
                                    {{ $kasbonDipotong > 0 ? '- Rp ' . number_format($kasbonDipotong, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-emerald-600 whitespace-nowrap">
                                    Rp {{ number_format($totalDiterima, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
