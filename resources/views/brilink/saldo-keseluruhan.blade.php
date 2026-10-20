@extends('layouts.app')

@section('title', 'Data Saldo Keseluruhan')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                <iconify-icon icon="mdi:wallet" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Data Saldo Keseluruhan</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }}</p>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                {{-- TAMBAHKAN onchange="this.form.submit()" --}}
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Rekening / Dompet</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama rekening..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium w-full md:w-auto flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($search || $from || $to || $storeId !== 'all')
                    <a href="{{ route('brilink.saldo-keseluruhan') }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg w-full md:w-auto text-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL DATA SALDO PER TANGGAL --}}
    <div class="space-y-6">
        @if(empty($dataPerReport))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                Tidak ada data saldo untuk tanggal dan filter ini.
            </div>
        @else
            @foreach($dataPerReport as $data)
                @php
                    $label = $data['label'];
                    $accounts = $data['accounts'];
                    
                    // Karena $accounts sudah berbentuk Array (hasil agregasi), 
                    // kita gunakan array_sum atau collect() untuk menjumlahkan.
                    $totalAkhirPerTanggal = collect($accounts)->sum('saldo_akhir');
                @endphp
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    {{-- Header Tanggal --}}
                    <div class="px-5 py-3 bg-gray-800 flex justify-between items-center">
                        <span class="font-bold text-white">
                            <iconify-icon icon="mdi:calendar" class="mr-1 text-gray-400"></iconify-icon>
                            {{ $label }}
                        </span>
                        <span class="text-sm font-semibold text-emerald-400">
                            Total: Rp {{ number_format($totalAkhirPerTanggal, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left p-4 text-xs font-semibold text-gray-600 uppercase">Nama Rekening / Dompet</th>
                                    <th class="text-left p-4 text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                                    <th class="text-right p-4 text-xs font-semibold text-gray-600 uppercase">Saldo Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($accounts as $acc)
                                    <tr class="hover:bg-gray-50 transition">
                                        {{-- Panggil menggunakan array karena sudah di-map di Controller --}}
                                        <td class="p-4 font-bold text-gray-800">{{ $acc['nama_rekening'] }}</td>
                                        <td class="p-4">
                                            @if($acc['keterangan'])
                                                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ $acc['keterangan'] }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-right font-bold text-emerald-600">
                                            Rp {{ number_format($acc['saldo_akhir'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

</div>
@endsection