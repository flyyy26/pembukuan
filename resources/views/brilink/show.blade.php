@extends('layouts.app')

@section('title', 'Detail Report BRILink')

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('brilink.index', ['store' => $report->store_id]) }}"
               class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition">
                <iconify-icon icon="mdi:arrow-left" class="text-gray-600 text-xl"></iconify-icon>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Detail Report BRILink</h2>
                <p class="text-sm text-gray-500">
                    {{ $report->file_name }} · {{ $report->periode_label }}
                </p>
            </div>
        </div>
    </div>

    {{-- Key Metrics --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="text-xs text-gray-500 mb-1">Omset PPOB</div>
            <div class="text-lg font-bold text-gray-800">Rp {{ number_format($report->total_omset_ppob, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="text-xs text-gray-500 mb-1">Setoran</div>
            <div class="text-lg font-bold text-indigo-600">Rp {{ number_format($report->total_setoran_brilink, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="text-xs text-gray-500 mb-1">Pendapatan Admin</div>
            <div class="text-lg font-bold text-emerald-600">Rp {{ number_format($report->total_pendapatan_admin, 0, ',', '.') }}</div>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-sm p-4 text-white">
            <div class="text-xs text-white/80 mb-1">Profit Bersih</div>
            <div class="text-lg font-bold">Rp {{ number_format($report->profit, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Rekening --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Rekening & Saldo</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left py-2 px-4 text-xs text-gray-500 uppercase">Nama</th>
                    <th class="text-left py-2 px-4 text-xs text-gray-500 uppercase">Kategori</th>
                    <th class="text-right py-2 px-4 text-xs text-gray-500 uppercase">Saldo Awal</th>
                    <th class="text-right py-2 px-4 text-xs text-gray-500 uppercase">Saldo Akhir</th>
                    <th class="text-right py-2 px-4 text-xs text-gray-500 uppercase">Selisih</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($report->accounts as $a)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 font-medium text-gray-800">{{ $a->nama_rekening }}</td>
                    <td class="py-2 px-4 text-xs">{{ $a->keterangan ?? '-' }}</td>
                    <td class="py-2 px-4 text-right">{{ number_format($a->saldo_awal, 0, ',', '.') }}</td>
                    <td class="py-2 px-4 text-right font-semibold">{{ number_format($a->saldo_akhir, 0, ',', '.') }}</td>
                    <td class="py-2 px-4 text-right text-xs {{ $a->selisih > 0 ? 'text-green-600' : ($a->selisih < 0 ? 'text-red-600' : 'text-gray-400') }}">
                        {{ $a->selisih > 0 ? '+' : '' }}{{ number_format($a->selisih, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PPOB --}}
    @if($report->ppobItems->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Omset PPOB per Kategori</h3>
        </div>
        <table class="w-full text-sm">
            <tbody class="divide-y">
                @foreach($report->ppobItems as $p)
                <tr>
                    <td class="py-2 px-4">{{ $p->kategori }}</td>
                    <td class="py-2 px-4 text-right font-semibold">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Pengeluaran --}}
    @if($report->pengeluaranItems->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-800">Pengeluaran</h3>
            <span class="text-xs text-gray-500">{{ $report->pengeluaranItems->count() }} transaksi</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left py-2 px-4 text-xs text-gray-500 uppercase">Keterangan</th>
                    <th class="text-left py-2 px-4 text-xs text-gray-500 uppercase">Kategori</th>
                    <th class="text-right py-2 px-4 text-xs text-gray-500 uppercase">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($report->pengeluaranItems as $p)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4">{{ $p->keterangan }}</td>
                    <td class="py-2 px-4 text-xs">
                        @if($p->kategori === 'Pindah Saldo Internal')
                            <span class="bg-orange-50 text-orange-700 px-2 py-0.5 rounded text-[10px]">{{ $p->kategori }}</span>
                        @else
                            {{ $p->kategori ?? '-' }}
                        @endif
                    </td>
                    <td class="py-2 px-4 text-right font-medium">Rp {{ number_format($p->nominal_kas, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection