@extends('layouts.app')

@section('title', 'Laporan TikTok')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:music-note" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Laporan TikTok</h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
            </div>
        </div>

        @if($storeId)
        <div class="flex gap-2">
            {{-- Export PDF --}}
            <a href="{{ route('tiktok.export.pdf', array_filter([
                'store'  => $storeId,
                'from'   => $from,
                'to'     => $to,
                'search' => $search,
            ])) }}"
            target="_blank"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:file-pdf-box" class="text-lg"></iconify-icon>
                Export PDF
            </a>

            {{-- Perbandingan --}}
            <a href="{{ route('tiktok.compare', ['store' => $storeId]) }}"
            class="inline-flex items-center gap-2 bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:compare-horizontal" class="text-lg"></iconify-icon>
                Perbandingan
            </a>

            {{-- Mapping --}}
            <a href="{{ route('tiktok.mapping', $storeId) }}"
            class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:link-variant" class="text-lg"></iconify-icon>
                Mapping Produk
            </a>
        </div>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:check-circle" class="text-green-600 text-lg mt-0.5"></iconify-icon>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($stores->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-4 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:alert" class="text-yellow-600 text-lg mt-0.5"></iconify-icon>
            <div>
                <strong>Belum ada toko TikTok.</strong>
                Silakan <a href="{{ route('stores.index', ['modal' => 'create']) }}" class="underline font-semibold">tambah toko</a> dulu.
            </div>
        </div>
    @else

        {{-- ============ FILTER ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" class="flex gap-3 items-end flex-wrap">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                    <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                </div>
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cari SKU / Produk</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                        </div>
                        <input type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Cari SKU TikTok / nama produk / SKU internal..."
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                    </div>
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition text-sm font-medium">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                    Filter
                </button>
                @if($from || $to || !empty($search))
                    <a href="{{ route('tiktok.index', ['store' => $storeId]) }}"
                    class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">
                        <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon>
                        Reset
                    </a>
                @endif
            </form>

            @if(!empty($search))
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                    <iconify-icon icon="mdi:information-outline" class="text-sm"></iconify-icon>
                    Menampilkan hasil untuk: <strong class="text-gray-700">"{{ $search }}"</strong>
                    <span class="text-gray-400">·</span>
                    <span>{{ $summary['sku_total'] }} SKU ditemukan</span>
                </div>
            @endif
        </div>

        {{-- ============ TABS ============ --}}
        @php
            $tabs = [
                ['id' => 'ringkasan', 'label' => 'Ringkasan', 'icon' => 'mdi:view-dashboard-outline'],
                ['id' => 'bulanan',   'label' => 'Bulanan',   'icon' => 'mdi:calendar-month-outline'],
                ['id' => 'top',       'label' => 'Top Produk','icon' => 'mdi:trophy-outline'],
                ['id' => 'sku',       'label' => 'Per SKU',   'icon' => 'mdi:format-list-bulleted'],
            ];
            $hasSearch = !empty($search);
            $requestedTab = request()->input('tab');
            if ($requestedTab) {
                $activeTab = $requestedTab;
            } elseif ($hasSearch) {
                $activeTab = 'sku';
            } else {
                $activeTab = 'ringkasan';
            }
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="flex border-b border-gray-100 overflow-x-auto">
                @foreach($tabs as $tab)
                    @php $isActive = $activeTab === $tab['id']; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['tab' => $tab['id'], 'page' => 1]) }}"
                       class="flex items-center gap-2 px-5 py-3 text-sm font-medium whitespace-nowrap transition border-b-2
                              {{ $isActive
                                  ? 'text-gray-900 border-gray-800 bg-gray-50/50'
                                  : 'text-gray-600 border-transparent hover:text-gray-800 hover:bg-gray-50' }}">
                        <iconify-icon icon="{{ $tab['icon'] }}" class="text-lg"></iconify-icon>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ============ TAB: RINGKASAN ============ --}}
        @if($activeTab === 'ringkasan')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:receipt-text-outline" class="text-gray-600 text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-1">Total Transaksi</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($summary['total_rows']) }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:cash-multiple" class="text-blue-600 text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-1">Net Revenue</div>
                    <div class="text-2xl font-bold text-blue-600">
                        <span class="text-sm text-gray-400 font-normal">Rp</span>
                        {{ number_format($summary['net_revenue'], 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:package-variant-closed" class="text-orange-600 text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-1">Total HPP</div>
                    <div class="text-2xl font-bold text-orange-600">
                        <span class="text-sm text-gray-400 font-normal">Rp</span>
                        {{ number_format($summary['total_hpp'], 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-sm p-5 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:chart-line" class="text-white text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-white/80 mb-1">Profit</div>
                    <div class="text-2xl font-bold">
                        <span class="text-sm text-white/70 font-normal">Rp</span>
                        {{ number_format($summary['profit'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <iconify-icon icon="mdi:calculator-variant" class="text-gray-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800">Breakdown Biaya Platform</h3>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    @php
                        $biaya = [
                            ['label' => 'Komisi',   'value' => $summary['biaya_komisi'],   'icon' => 'mdi:percent'],
                            ['label' => 'Layanan',  'value' => $summary['biaya_layanan'],  'icon' => 'mdi:cog'],
                            ['label' => 'Ongkir',   'value' => $summary['biaya_ongkir'],   'icon' => 'mdi:truck-delivery'],
                            ['label' => 'Afiliasi', 'value' => $summary['biaya_afiliasi'], 'icon' => 'mdi:account-group'],
                            ['label' => 'Pajak',    'value' => $summary['biaya_pajak'],    'icon' => 'mdi:file-document'],
                            ['label' => 'Lainnya',  'value' => $summary['biaya_lainnya'],  'icon' => 'mdi:dots-horizontal'],
                        ];
                    @endphp
                    @foreach($biaya as $b)
                    <div class="border border-gray-100 rounded-lg p-3">
                        <div class="flex items-center gap-1.5 mb-1">
                            <iconify-icon icon="{{ $b['icon'] }}" class="text-gray-400 text-sm"></iconify-icon>
                            <div class="text-xs text-gray-500">{{ $b['label'] }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-800">
                            {{ number_format($b['value'], 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="mdi:link-variant" class="text-purple-600 text-lg"></iconify-icon>
                            <div class="text-sm font-medium text-gray-700">SKU Dimapping</div>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $summary['sku_total'] > 0 ? round(($summary['sku_mapped'] / $summary['sku_total']) * 100, 1) : 0 }}%
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-3xl font-bold text-purple-600">{{ $summary['sku_mapped'] }}</span>
                        <span class="text-lg text-gray-400">/</span>
                        <span class="text-lg text-gray-600">{{ $summary['sku_total'] }}</span>
                        <span class="text-xs text-gray-500">SKU</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @php $pct = $summary['sku_total'] > 0 ? ($summary['sku_mapped'] / $summary['sku_total']) * 100 : 0; @endphp
                        <div class="bg-purple-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="mdi:check-decagram" class="text-green-600 text-lg"></iconify-icon>
                            <div class="text-sm font-medium text-gray-700">Transaksi Matched</div>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $summary['total_transaksi'] > 0 ? round(($summary['transaksi_matched'] / $summary['total_transaksi']) * 100, 1) : 0 }}%
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-3xl font-bold text-green-600">{{ $summary['transaksi_matched'] }}</span>
                        <span class="text-lg text-gray-400">/</span>
                        <span class="text-lg text-gray-600">{{ $summary['total_transaksi'] }}</span>
                        <span class="text-xs text-gray-500">transaksi</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @php $pct2 = $summary['total_transaksi'] > 0 ? ($summary['transaksi_matched'] / $summary['total_transaksi']) * 100 : 0; @endphp
                        <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $pct2 }}%"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============ TAB: BULANAN ============ --}}
        @if($activeTab === 'bulanan')
            @if(!empty($monthlyData['months']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:chart-timeline-variant" class="text-gray-600 text-lg"></iconify-icon>
                        <h3 class="font-semibold text-gray-800">Rekap Per Bulan</h3>
                    </div>
                    @if($monthlyData['best_month'])
                        <div class="inline-flex items-center gap-2 bg-yellow-50 border border-yellow-200 px-3 py-1.5 rounded-lg">
                            <iconify-icon icon="mdi:trophy" class="text-yellow-600 text-base"></iconify-icon>
                            <span class="text-xs text-yellow-800">
                                Bulan terbaik: <strong>{{ $monthlyData['best_month']['label'] }}</strong>
                                (Rp {{ number_format($monthlyData['best_month']['profit'], 0, ',', '.') }})
                            </span>
                        </div>
                    @endif
                </div>

                <div class="mb-6" style="height: 280px;">
                    <canvas id="monthlyChart"></canvas>
                </div>

                <div class="overflow-x-auto border border-gray-100 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bulan</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaksi</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Net Revenue</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">HPP</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Profit</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Growth</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($monthlyData['months'] as $m)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-800">{{ $m['label'] }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $m['year_month'] }}</div>
                                </td>
                                <td class="py-3 px-4 text-center">{{ number_format($m['transaksi']) }}</td>
                                <td class="py-3 px-4 text-center">{{ number_format($m['qty']) }}</td>
                                <td class="py-3 px-4 text-right">{{ number_format($m['net_revenue'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-orange-600">{{ number_format($m['hpp_total'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold {{ $m['profit'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($m['profit'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($m['growth'] === null)
                                        <span class="text-xs text-gray-400">—</span>
                                    @elseif($m['growth'] > 0)
                                        <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                                            <iconify-icon icon="mdi:trending-up" class="text-sm"></iconify-icon>
                                            +{{ $m['growth'] }}%
                                        </span>
                                    @elseif($m['growth'] < 0)
                                        <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 px-2 py-1 rounded-full text-xs font-medium">
                                            <iconify-icon icon="mdi:trending-down" class="text-sm"></iconify-icon>
                                            {{ $m['growth'] }}%
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">0%</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <iconify-icon icon="mdi:chart-line" class="text-4xl text-gray-300 block mb-2"></iconify-icon>
                    <div class="text-gray-500">Belum ada data bulanan.</div>
                </div>
            @endif
        @endif

        {{-- ============ TAB: TOP PRODUK ============ --}}
        @if($activeTab === 'top')
            @if(!empty($topProducts['insight']) && $topProducts['insight']['total_sku'] > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="text-xs text-gray-500 mb-2">SKU Ter-mapping</div>
                        <div class="text-xl font-bold text-gray-800">{{ $topProducts['insight']['total_sku'] }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="text-xs text-gray-500 mb-2">SKU Profit</div>
                        <div class="text-xl font-bold text-green-600">{{ $topProducts['insight']['sku_profit'] }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="text-xs text-gray-500 mb-2">SKU Rugi</div>
                        <div class="text-xl font-bold text-red-600">{{ $topProducts['insight']['sku_rugi'] }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="text-xs text-gray-500 mb-2">Rata-rata Profit</div>
                        <div class="text-xl font-bold text-blue-600">Rp {{ number_format($topProducts['insight']['avg_profit'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    @foreach([
                        ['title' => 'Top 10 Terlaris', 'key' => 'top_qty', 'color' => 'blue', 'icon' => 'mdi:fire', 'value_key' => 'total_qty', 'suffix' => 'unit'],
                        ['title' => 'Top 10 Paling Profit', 'key' => 'top_profit', 'color' => 'green', 'icon' => 'mdi:trophy', 'value_key' => 'profit', 'suffix' => 'profit'],
                        ['title' => '10 Profit Terendah', 'key' => 'bottom_profit', 'color' => 'red', 'icon' => 'mdi:alert-octagon', 'value_key' => 'profit', 'suffix' => 'profit'],
                    ] as $section)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2 bg-gradient-to-r from-{{ $section['color'] }}-50 to-white">
                            <div class="w-8 h-8 bg-{{ $section['color'] }}-100 rounded-lg flex items-center justify-center">
                                <iconify-icon icon="{{ $section['icon'] }}" class="text-{{ $section['color'] }}-600 text-lg"></iconify-icon>
                            </div>
                            <h4 class="font-semibold text-gray-800 text-sm">{{ $section['title'] }}</h4>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($topProducts[$section['key']] as $idx => $p)
                            <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 bg-{{ $idx < 3 ? $section['color'] : 'gray' }}-100 text-{{ $idx < 3 ? $section['color'] : 'gray' }}-700">
                                    {{ $idx + 1 }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-mono text-[11px] text-gray-700 truncate">{{ $p['marketplace_sku'] }}</div>
                                    <div class="text-[10px] text-gray-500 truncate">{{ \Illuminate\Support\Str::limit($p['nama_produk'], 30, '…') }}</div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold text-{{ $section['color'] }}-600">
                                        {{ number_format($p[$section['value_key']], 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">{{ $section['suffix'] }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data</div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <iconify-icon icon="mdi:trophy" class="text-4xl text-gray-300 block mb-2"></iconify-icon>
                    <div class="text-gray-500">Belum ada SKU yang dimapping. Mapping produk dulu untuk melihat Top 10.</div>
                    <a href="{{ route('tiktok.mapping', $storeId) }}"
                       class="inline-flex items-center gap-2 mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        <iconify-icon icon="mdi:link-variant" class="text-lg"></iconify-icon>
                        Mapping Produk
                    </a>
                </div>
            @endif
        @endif

        {{-- ============ TAB: PER SKU ============ --}}
        @if($activeTab === 'sku')
            <div id="skuTableContainer">
                @include('tiktok.partials.sku-table', [
                    'perProduk' => $perProduk,
                    'totalProduk' => $totalProduk,
                    'page' => $page,
                    'perPage' => $perPage,
                    'storeId' => $storeId,
                    'from' => $from,
                    'to' => $to,
                    'search' => $search,
                ])
            </div>
        @endif

    @endif
</div>

{{-- Chart.js --}}
@if(!empty($monthlyData['months']) && $activeTab === 'bulanan')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    const months = @json(array_reverse($monthlyData['months']));
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months.map(m => m.label),
            datasets: [
                { label: 'Net Revenue', data: months.map(m => m.net_revenue), backgroundColor: 'rgba(59, 130, 246, 0.7)', borderRadius: 6 },
                { label: 'Profit', data: months.map(m => m.profit), backgroundColor: 'rgba(16, 185, 129, 0.7)', borderRadius: 6 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12, family: 'Geist' }, usePointStyle: true, padding: 16 } },
                tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': Rp ' + (ctx.parsed.y || 0).toLocaleString('id-ID') } },
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: v => v >= 1000000 ? 'Rp ' + (v/1000000).toFixed(1) + 'jt' : v >= 1000 ? 'Rp ' + (v/1000).toFixed(0) + 'rb' : 'Rp ' + v } },
            },
        },
    });
});
</script>
@endif

<script>
async function loadSkuPage(page) {
    const container = document.getElementById('skuTableContainer');
    if (!container) return;
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';
    try {
        const params = new URLSearchParams({
            page: page,
            store: '{{ $storeId ?? "" }}',
            from: '{{ $from ?? "" }}',
            to: '{{ $to ?? "" }}',
            search: '{{ $search ?? "" }}',
        });
        const response = await fetch(`{{ route('tiktok.sku.table') }}?${params}`, { headers: { 'Accept': 'text/html' } });
        const html = await response.text();
        container.innerHTML = html;
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
    }
}
</script>
@endsection