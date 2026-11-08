@extends('layouts.app')

@section('title', 'Laporan Seluruh Profit')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                <iconify-icon icon="mdi:chart-box-multiple" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Seluruh Profit</h2>
                <p class="text-xs sm:text-sm text-gray-500">Konsolidasi Shopee · TikTok · Web · BRILink</p>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3 items-end flex-wrap">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                Filter
            </button>
            @if(request()->hasAny(['from', 'to']))
                <a href="{{ route('reports.all-profit.index') }}"
                   class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">
                    <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ================= KARTU TOTAL ================= --}}
    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-xs uppercase tracking-wider text-indigo-100 mb-1">Total Profit Bersih</div>
                <div class="text-3xl sm:text-4xl font-bold">
                    <span class="text-base text-indigo-200 font-normal">Rp</span>
                    {{ number_format($total['profit'], 0, ',', '.') }}
                </div>
            </div>
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                <iconify-icon icon="mdi:sigma" class="text-white text-3xl"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- ================= KARTU PER SUMBER ================= --}}
    @php
        $cards = [
            'shopee'  => ['label' => 'Shopee',  'icon' => 'mdi:shopping',  'color' => 'orange'],
            'tiktok'  => ['label' => 'TikTok',  'icon' => 'mdi:music-note','color' => 'gray'],
            'web'     => ['label' => 'Web',     'icon' => 'mdi:web',       'color' => 'blue'],
            'brilink' => ['label' => 'BRILink', 'icon' => 'mdi:bank',      'color' => 'emerald'],
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach($cards as $key => $cfg)
            @php $s = $summary[$key]; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-{{ $cfg['color'] }}-100 rounded-lg flex items-center justify-center">
                        <iconify-icon icon="{{ $cfg['icon'] }}" class="text-{{ $cfg['color'] }}-600 text-lg"></iconify-icon>
                    </div>
                    <div class="text-xs font-semibold text-gray-700">{{ $cfg['label'] }}</div>
                </div>
                <div class="text-[10px] text-gray-500 mb-0.5">Profit</div>
                <div class="text-base sm:text-lg font-bold {{ $s['profit'] >= 0 ? 'text-gray-900' : 'text-red-600' }} mb-2">
                    Rp {{ number_format($s['profit'], 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>

    {{-- ================= TABEL RINGKASAN PER SUMBER ================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <iconify-icon icon="mdi:table" class="text-gray-600 text-lg"></iconify-icon>
            <h3 class="font-semibold text-gray-800">Ringkasan per Sumber</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sumber</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaksi</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">HPP / Biaya</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Profit</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($cards as $key => $cfg)
                        @php $s = $summary[$key]; @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="{{ $cfg['icon'] }}" class="text-{{ $cfg['color'] }}-600 text-base"></iconify-icon>
                                    <span class="font-medium text-gray-800">{{ $cfg['label'] }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center text-gray-700">{{ number_format($s['count']) }}</td>
                            <td class="py-3 px-4 text-right text-gray-700">{{ number_format($s['revenue'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-orange-600">{{ number_format($s['hpp'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-bold {{ $s['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($s['profit'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    {{ $s['margin'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $s['margin'] }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-indigo-50 border-t-2 border-indigo-200">
                    <tr>
                        <td class="py-3 px-4 font-bold text-indigo-900">TOTAL</td>
                        <td class="py-3 px-4 text-center font-bold text-indigo-900">{{ number_format($total['count']) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-indigo-900">{{ number_format($total['revenue'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-indigo-900">{{ number_format($total['hpp'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-indigo-900">{{ number_format($total['profit'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center font-bold text-indigo-900">{{ $total['margin'] }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ================= TABEL PER BULAN ================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:calendar-month" class="text-gray-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Rekap Per Bulan</h3>
            </div>
            <span class="text-xs text-gray-500">{{ count($monthly) }} bulan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bulan</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-orange-600 uppercase tracking-wider">Shopee</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">TikTok</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-blue-600 uppercase tracking-wider">Web</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-emerald-600 uppercase tracking-wider">BRILink</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-indigo-600 uppercase tracking-wider">Total</th>
                        <th class="text-center py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($monthly as $m)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-800">{{ $m['label'] }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $m['month'] }}</div>
                            </td>
                            <td class="py-3 px-3 text-right text-gray-700">{{ number_format($m['shopee'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-gray-700">{{ number_format($m['tiktok'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-gray-700">{{ number_format($m['web'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-gray-700">{{ number_format($m['brilink'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-bold {{ $m['total'] >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                                {{ number_format($m['total'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    {{ $m['margin'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $m['margin'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-gray-500">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= TABEL PER HARI ================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:calendar-today" class="text-gray-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Rekap Per Hari</h3>
            </div>
            <span class="text-xs text-gray-500">{{ count($daily) }} hari (maks 92 hari terakhir)</span>
        </div>
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-orange-600 uppercase tracking-wider">Shopee</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">TikTok</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-blue-600 uppercase tracking-wider">Web</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-emerald-600 uppercase tracking-wider">BRILink</th>
                        <th class="text-right py-3 px-3 text-xs font-semibold text-indigo-600 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($daily as $d)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-2 px-4 text-gray-700">{{ $d['label'] }}</td>
                            <td class="py-2 px-3 text-right text-gray-700">{{ number_format($d['shopee'], 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right text-gray-700">{{ number_format($d['tiktok'], 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right text-gray-700">{{ number_format($d['web'], 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right text-gray-700">{{ number_format($d['brilink'], 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right font-bold {{ $d['total'] >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                                {{ number_format($d['total'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-gray-500">Belum ada data pada rentang ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection