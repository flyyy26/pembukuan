@extends('layouts.app')

@section('title', 'Laporan Shopee')

@section('content')
<div class="w-full">

    {{-- ============ MODAL DETAIL SKU SHOPEE ============ --}}
    <div id="shopeeSkuDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" onclick="window.closeShopeeSkuDetail()"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-orange-50">
                <h3 class="font-bold text-gray-800">Rincian Biaya & Net Revenue: <span id="shopeeDetailSkuLabel" class="text-orange-600 font-mono"></span></h3>
                <button onclick="window.closeShopeeSkuDetail()" class="text-gray-400 hover:text-gray-700">
                    <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
                </button>
            </div>
            <div id="shopeeSkuDetailContent" class="p-4 overflow-y-auto flex-1">
                {{-- Konten di-load via AJAX --}}
            </div>
        </div>
    </div>

    <script>
        window.showShopeeSkuDetail = function (sku) {
            const modal = document.getElementById('shopeeSkuDetailModal');
            const content = document.getElementById('shopeeSkuDetailContent');
            const label = document.getElementById('shopeeDetailSkuLabel');
            if (!modal || !content || !label) {
                throw new Error('Elemen popup detail SKU tidak ditemukan.');
            }

            label.textContent = sku;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            content.innerHTML = '<div class="text-center py-8 text-gray-500"><iconify-icon icon="mdi:loading" class="animate-spin text-2xl"></iconify-icon><br>Memuat data...</div>';

            const params = new URLSearchParams({
                store: '{{ $storeId ?? "" }}',
                sku: sku,
                from: '{{ $from ?? "" }}',
                to: '{{ $to ?? "" }}'
            });

            fetch(`{{ route('shopee.sku.detail') }}?${params}`)
                .then(res => res.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-red-500 text-center py-4">Gagal memuat data: ${err.message}</div>`;
                });
        };

        window.closeShopeeSkuDetail = function () {
            const modal = document.getElementById('shopeeSkuDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };
    </script>

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        {{-- Title --}}
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center shrink-0">
                    <iconify-icon icon="mdi:shopping" class="text-orange-600 text-xl"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 truncate">Laporan Shopee</h2>
                    <p class="text-sm text-gray-500 truncate">{{ $store?->nama_toko ?? 'Belum pilih toko' }}</p>
                </div>
            </div>
        </div>
    
        @if($storeId)
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('shopee.export.pdf', array_filter([
                'store'  => $storeId,
                'from'   => $from,
                'to'     => $to,
                'search' => $search,
            ])) }}"
            target="_blank"
            class="inline-flex items-center justify-center gap-2 bg-red-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:file-pdf-box" class="text-lg shrink-0"></iconify-icon>
                <span>Export PDF</span>
            </a>
            <a href="{{ route('shopee.compare', ['store' => $storeId]) }}"
            class="inline-flex items-center justify-center gap-2 bg-purple-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:compare-horizontal" class="text-lg shrink-0"></iconify-icon>
                <span>Perbandingan</span>
            </a>
            <a href="{{ route('products.index') }}"
            class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-sm"
            title="Atur kategori & prefix SKU untuk mapping otomatis">
                <iconify-icon icon="mdi:tag-multiple" class="text-lg shrink-0"></iconify-icon>
                <span>Kategori & Prefix</span>
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
                <strong>Belum ada toko Shopee.</strong>
                Silakan <a href="{{ route('stores.index', ['modal' => 'create']) }}" class="underline font-semibold">tambah toko</a> dulu.
            </div>
        </div>
    @else

        {{-- ============ FILTER ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" class="flex gap-3 items-end flex-wrap">
                <input type="hidden" name="tab" value="{{ request()->input('tab', 'ringkasan') }}">
                @if(request()->input('tab') === 'pembatalan')
                    <input type="hidden" name="kategori_return" value="{{ $kategoriReturn }}">
                @endif
                @if(request()->input('tab') === 'live')
                    <input type="hidden" name="live_type" value="{{ $liveType }}">
                @endif
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                    <select name="store" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
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
                            placeholder="Cari nomor pesanan / SKU / nama produk..."
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon>
                    Filter
                </button>
                @if($from || $to || !empty($search) || $kategoriReturn !== 'all')
                    <a href="{{ route('shopee.index', ['store' => $storeId]) }}"
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
                ['id' => 'ringkasan', 'label' => 'Profit Per Produk', 'icon' => 'mdi:view-dashboard-outline'],
                ['id' => 'pesanan',   'label' => 'Profit Per Pesanan', 'icon' => 'mdi:receipt-text-outline'],
                ['id' => 'pembatalan','label' => 'Batal / Return',    'icon' => 'mdi:package-variant-remove'],
                ['id' => 'chat',      'label' => 'Statistik Chat',    'icon' => 'mdi:message-text-outline'],
                ['id' => 'live',      'label' => 'Live & Video',      'icon' => 'mdi:video-outline'],
                ['id' => 'top',       'label' => 'Top Produk','icon' => 'mdi:trophy-outline'],
            ];

            $requestedTab = request()->input('tab');
            $activeTab = in_array($requestedTab, ['ringkasan', 'pesanan', 'pembatalan', 'chat', 'live', 'top'])
                ? $requestedTab
                : 'ringkasan';
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="flex border-b border-gray-100 overflow-x-auto">
                @foreach($tabs as $tab)
                    @php $isActive = $activeTab === $tab['id']; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['tab' => $tab['id'], 'page' => 1]) }}"
                       class="flex items-center gap-2 px-5 py-3 text-sm font-medium whitespace-nowrap transition border-b-2
                              {{ $isActive
                                  ? 'text-blue-600 border-blue-600 bg-blue-50/50'
                                  : 'text-gray-600 border-transparent hover:text-gray-800 hover:bg-gray-50' }}">
                        <iconify-icon icon="{{ $tab['icon'] }}" class="text-lg"></iconify-icon>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ============ TAB: RINGKASAN ============ --}}
        @if($activeTab === 'ringkasan')
            {{-- RINGKASAN KEUANGAN --}}
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:receipt-text-outline" class="text-gray-600 text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-1">Produk Terjual</div>
                    <div class="text-md md:text-xl lg:text-2xl font-bold text-gray-800">{{ number_format($summary['total_qty']) }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:cash-multiple" class="text-blue-600 text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-1">Net Revenue</div>
                    <div class="text-md md:text-xl lg:text-2xl font-bold text-blue-600">
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
                    <div class="text-md md:text-xl lg:text-2xl font-bold text-orange-600">
                        <span class="text-sm text-gray-400 font-normal">Rp</span>
                        {{ number_format($summary['total_hpp'], 0, ',', '.') }}
                    </div>
                </div>

                @include('shopee.partials.ad-spend-input', ['inputId' => 'biaya-iklan-produk'])

                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-sm p-5 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:chart-line" class="text-white text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-white/80 mb-1">Profit</div>
                    <div class="text-md md:text-xl lg:text-2xl font-bold">
                        <span class="text-sm text-white/70 font-normal">Rp</span>
                        {{ number_format($summary['profit_after_ads'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- BREAKDOWN BIAYA --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <iconify-icon icon="mdi:calculator-variant" class="text-gray-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800">Breakdown Biaya Platform</h3>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3">
                    @php
                        $biaya = [
                            ['label' => 'Komisi',   'value' => $summary['biaya_komisi'],   'icon' => 'mdi:percent'],
                            ['label' => 'Layanan',  'value' => $summary['biaya_layanan'],  'icon' => 'mdi:cog'],
                            ['label' => 'Ongkir',   'value' => $summary['biaya_ongkir'],   'icon' => 'mdi:truck-delivery'],
                            ['label' => 'Afiliasi', 'value' => $summary['biaya_afiliasi'], 'icon' => 'mdi:account-group'],
                            ['label' => 'Promosi',  'value' => $summary['biaya_promosi'],  'icon' => 'mdi:tag-multiple'],  // ← tambah
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

            <div id="skuTableContainer" class="mt-6">
                @include('shopee.partials.sku-table', [
                    'perProduk'   => $perProduk,
                    'totalProduk' => $totalProduk,
                    'page'        => $page,
                    'perPage'     => $perPage,
                    'storeId'     => $storeId,
                    'from'        => $from,
                    'to'          => $to,
                ])
            </div>
        @endif

        {{-- ============ TAB: PER PESANAN ============ --}}
        @if($activeTab === 'pesanan')
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:receipt-text-outline" class="text-gray-600 text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-1">Total Pesanan</div>
                    <div class="text-md md:text-xl lg:text-2xl font-bold text-gray-800">{{ number_format($perPesanan->count()) }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:cash-multiple" class="text-blue-600 text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-1">Net Revenue</div>
                    <div class="text-md md:text-xl lg:text-2xl font-bold text-blue-600">
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
                    <div class="text-md md:text-xl lg:text-2xl font-bold text-orange-600">
                        <span class="text-sm text-gray-400 font-normal">Rp</span>
                        {{ number_format($summary['total_hpp'], 0, ',', '.') }}
                    </div>
                </div>

                @include('shopee.partials.ad-spend-input', ['inputId' => 'biaya-iklan-pesanan'])

                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-sm p-5 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <iconify-icon icon="mdi:chart-line" class="text-white text-xl"></iconify-icon>
                        </div>
                    </div>
                    <div class="text-xs text-white/80 mb-1">Profit</div>
                    <div class="text-md md:text-xl lg:text-2xl font-bold">
                        <span class="text-sm text-white/70 font-normal">Rp</span>
                        {{ number_format($summary['profit_after_ads'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:receipt-text-outline" class="text-gray-600 text-lg"></iconify-icon>
                        <h3 class="font-semibold text-gray-800">Daftar Pesanan</h3>
                    </div>
                    <div class="text-xs text-gray-500">
                        Total: {{ $totalPesanan }} Pesanan
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">No.</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">No. Pesanan / Tanggal</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Produk yang Dibeli</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Net Revenue</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">HPP</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Profit Pesanan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($perPesananPage as $index => $pesanan)
                            <tr class="hover:bg-gray-50 transition align-top">
                                <td class="py-4 px-4 text-center text-gray-500">
                                    {{ (($pesananPage - 1) * $perPage) + $index + 1 }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-mono font-medium text-gray-800">{{ $pesanan['order_id'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($pesanan['tanggal'])->format('d M Y') }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <ul class="space-y-1.5">
                                        @foreach($pesanan['detail_produk'] as $produk)
                                            <li class="text-xs">
                                                <div class="font-medium text-gray-800">{{ $produk['nama_produk'] }}</div>
                                                <div class="text-gray-500 flex items-center gap-2 mt-1">
                                                    {{-- TOMBOL SKU YANG BISA DIKLIK --}}
                                                    <button type="button"
                                                            data-sku="{{ $produk['sku'] }}"
                                                            onclick="window.showShopeeSkuDetail(this.dataset.sku)"
                                                            class="font-mono text-[11px] bg-gray-100 hover:bg-orange-100 hover:text-orange-700 px-1.5 py-0.5 rounded text-gray-700 transition flex items-center gap-1">
                                                        {{ $produk['sku'] }}
                                                        <iconify-icon icon="mdi:eye-outline" class="text-[10px]"></iconify-icon>
                                                    </button>

                                                    <span>• {{ $produk['qty'] }}x</span>
                                                    <span class="text-gray-400">(Net: {{ number_format($produk['net_revenue'], 0, ',', '.') }})</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="py-4 px-4 text-right text-gray-700 whitespace-nowrap">
                                    {{ number_format($pesanan['total_net'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-right text-orange-600 whitespace-nowrap">
                                    {{ number_format($pesanan['total_hpp'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-right font-bold whitespace-nowrap {{ $pesanan['total_profit'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($pesanan['total_profit'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-500">
                                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                                    <div class="text-sm">Belum ada data pesanan di periode ini.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('shopee.partials.pagination', [
                    'currentPage' => $pesananPage,
                    'totalItems' => $totalPesanan,
                    'perPage' => $perPage,
                    'pageParameter' => 'pesanan_page',
                    'itemLabel' => 'pesanan',
                ])
            </div>
        @endif

        {{-- ============ TAB: PENGEMBALIAN / PEMBATALAN ============ --}}
        @if($activeTab === 'pembatalan')
            
            {{-- Ringkasan Batal --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <iconify-icon icon="mdi:cart-off" class="text-red-600 text-xl"></iconify-icon>
                        <div class="text-xs text-gray-500">Pembatalan</div>
                    </div>
                    <div class="text-2xl font-bold text-red-600">
                        {{ number_format($summaryBatal['total_batal']) }} <span class="text-sm font-normal text-gray-500">Pesanan</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <iconify-icon icon="mdi:package-variant-closed-remove" class="text-purple-600 text-xl"></iconify-icon>
                        <div class="text-xs text-gray-500">Pengembalian / Retur</div>
                    </div>
                    <div class="text-2xl font-bold text-purple-600">
                        {{ number_format($summaryBatal['total_retur']) }} <span class="text-sm font-normal text-gray-500">Pesanan</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-amber-100 p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <iconify-icon icon="mdi:truck-alert-outline" class="text-amber-600 text-xl"></iconify-icon>
                        <div class="text-xs text-gray-500">Gagal Kirim</div>
                    </div>
                    <div class="text-2xl font-bold text-amber-600">
                        {{ number_format($summaryBatal['total_gagal_kirim']) }} <span class="text-sm font-normal text-gray-500">Pesanan</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <iconify-icon icon="mdi:package-variant" class="text-gray-600 text-xl"></iconify-icon>
                        <div class="text-xs text-gray-500">Total Item</div>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">
                        {{ number_format($summaryBatal['total_item']) }} <span class="text-sm font-normal text-gray-500">Pcs</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Batal --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:package-variant-remove" class="text-gray-600 text-lg"></iconify-icon>
                        <h3 class="font-semibold text-gray-800">Daftar Pembatalan & Pengembalian</h3>
                    </div>
                    <form method="GET" action="{{ route('shopee.index') }}" class="flex items-end gap-2">
                        <input type="hidden" name="store" value="{{ $storeId }}">
                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="to" value="{{ $to }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="tab" value="pembatalan">
                        <div>
                            <label for="kategori_return" class="block text-xs font-medium text-gray-600 mb-1">Tampilkan</label>
                            <select id="kategori_return" name="kategori_return"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all" {{ $kategoriReturn === 'all' ? 'selected' : '' }}>Semua</option>
                                <option value="return" {{ $kategoriReturn === 'return' ? 'selected' : '' }}>Pengembalian / Retur</option>
                                <option value="cancel" {{ $kategoriReturn === 'cancel' ? 'selected' : '' }}>Pembatalan</option>
                                <option value="shipping_failed" {{ $kategoriReturn === 'shipping_failed' ? 'selected' : '' }}>Gagal Kirim</option>
                            </select>
                        </div>
                        <button type="submit"
                                class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                            Terapkan
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">No. Pesanan / Tanggal</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Alasan</th> {{-- <-- KOLOM BARU --}}
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Produk yang Dibatalkan</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Alur Barang</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Dana Kembali</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($perBatalPage as $batal)
                            <tr class="hover:bg-gray-50 transition align-top">
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-mono font-medium text-gray-800">{{ $batal['order_id'] }}</div>
                                    <div class="text-xs text-blue-600 font-semibold mt-1 flex items-center gap-1">
                                        <iconify-icon icon="mdi:calendar-check" class="text-[12px]"></iconify-icon>
                                        Tanggal Pengajuan: {{ $batal['tanggal'] ? \Carbon\Carbon::parse($batal['tanggal'])->format('d M Y') : '-' }}
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 px-2 py-1 rounded text-[11px] font-medium border border-red-100">
                                        <iconify-icon icon="mdi:alert-circle-outline" class="text-sm"></iconify-icon>
                                        {{ $batal['status_utama'] }}
                                    </span>
                                </td>
                                
                                {{-- KOLOM ALASAN BARU --}}
                                <td class="py-4 px-4 text-xs text-gray-600 max-w-[200px] break-words">
                                    {{ $batal['detail_produk']->first()['alasan'] ?? '-' }}
                                </td>

                                <td class="py-4 px-4">
                                    <ul class="space-y-1.5">
                                        @foreach($batal['detail_produk'] as $produk)
                                            <li class="text-xs">
                                                <div class="font-medium text-gray-800">{{ $produk['nama_produk'] }}</div>
                                                <div class="text-gray-500 flex items-center gap-2 mt-1">
                                                    <span class="font-mono text-[11px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">
                                                        {{ $produk['sku'] }}
                                                    </span>
                                                    <span>• {{ $produk['qty'] }}x</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="py-4 px-4 text-xs text-gray-600 space-y-1">
                                    <div>
                                        <span class="font-semibold">Ke Pembeli:</span> 
                                        {{ $batal['detail_produk']->first()['status_kirim'] ?? '-' }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">Ke Penjual:</span> 
                                        {{ $batal['detail_produk']->first()['status_kembali'] ?? '-' }}
                                    </div>
                                </td>

                                {{-- Kolom Dana Kembali --}}
                                <td class="py-4 px-4 text-right font-bold text-red-600 whitespace-nowrap">
                                    {{ number_format($batal['detail_produk']->first()['dana_kembali'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-500">
                                    <iconify-icon icon="mdi:check-decagram-outline" class="text-4xl text-green-400 block m-auto mb-2 w-max"></iconify-icon>
                                    <div class="text-sm">Tidak ada data untuk filter ini pada periode yang dipilih.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('shopee.partials.pagination', [
                    'currentPage' => $batalPage,
                    'totalItems' => $totalBatal,
                    'perPage' => $perPage,
                    'pageParameter' => 'batal_page',
                    'itemLabel' => 'pesanan',
                ])
            </div>
        @endif

        {{-- ============ TAB: CHAT ============ --}}
        @if($activeTab === 'chat')
            {{-- Ringkasan Chat --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">Total Pengunjung</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($summaryChat['total_pengunjung']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5">
                    <div class="text-xs text-blue-600 mb-1">Total Chat Masuk</div>
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($summaryChat['total_chat']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-green-100 p-5">
                    <div class="text-xs text-green-600 mb-1">Chat Dibalas</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($summaryChat['chat_dibalas']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
                    <div class="text-xs text-red-600 mb-1">Belum Dibalas</div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($summaryChat['chat_belum_dibalas']) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">Rata-rata CSAT</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ number_format($summaryChat['csat_persen'], 1) }}%</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">Konversi Chat ke Order</div>
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($summaryChat['konversi_chat'], 1) }}%</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">Total Pesanan dari Chat</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($summaryChat['total_pesanan']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">Penjualan dari Chat</div>
                    <div class="text-2xl font-bold text-green-600">Rp {{ number_format($summaryChat['total_penjualan'], 0, ',', '.') }}</div>
                </div>
            </div>
        @endif

        {{-- ============ TAB: LIVE & VIDEO ============ --}}
        @if($activeTab === 'live')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:video-outline" class="text-gray-600 text-lg"></iconify-icon>
                        <h3 class="font-semibold text-gray-800">Daftar Livestream & Video</h3>
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="live">
                        <input type="hidden" name="store" value="{{ $storeId }}">
                        <input type="hidden" name="from" value="{{ $from }}">
                        <input type="hidden" name="to" value="{{ $to }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <label for="liveTypeFilter" class="text-xs text-gray-500">Jenis laporan</label>
                        <select id="liveTypeFilter" name="live_type" onchange="this.form.submit()"
                                class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all" {{ $liveType === 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="live" {{ $liveType === 'live' ? 'selected' : '' }}>Live</option>
                            <option value="video" {{ $liveType === 'video' ? 'selected' : '' }}>Video</option>
                        </select>
                    </form>
                </div>

                @if($rawLive->isEmpty())
                    <div class="py-12 px-5 text-center text-gray-500">
                        <iconify-icon icon="mdi:video-off-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                        <div class="text-sm">Belum ada daftar konten untuk toko, jenis, dan tanggal yang dipilih.</div>
                        <div class="text-xs mt-1">Import ekspor Daftar Livestream dan Daftar Video dari Seller Center untuk menampilkan datanya.</div>
                    </div>
                @else
                    <div class="p-4 sm:p-5 space-y-6">
                        @if($liveType !== 'video' && $liveRows->isNotEmpty())
                            <section>
                                <h4 class="font-semibold text-gray-800 mb-3">Daftar Livestream</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-xs text-gray-600">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold whitespace-nowrap">Tanggal Mulai</th>
                                        <th class="px-3 py-3 text-left font-semibold min-w-[220px]">Nama Livestream</th>
                                        <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Penonton Aktif</th>
                                        <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Penonton</th>
                                        <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Pesanan Siap Dikirim</th>
                                        <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Produk Terjual</th>
                                        <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Penjualan Dibuat</th>
                                        <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Penjualan Siap Dikirim</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($liveRows as $live)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3 whitespace-nowrap text-gray-600">{{ \Carbon\Carbon::parse($live->metrics['published_at'])->format('d M Y H:i') }}</td>
                                            <td class="px-3 py-3 text-gray-800">{{ $live->judul }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ number_format($live->metrics['active_viewers'] ?? 0) }}</td>
                                            <td class="px-3 py-3 text-right font-semibold text-gray-800">{{ number_format($live->metrics['views'] ?? 0) }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ number_format($live->metrics['orders_ready_to_ship'] ?? 0) }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ number_format($live->metrics['products_sold_ready_to_ship'] ?? 0) }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700 whitespace-nowrap">Rp {{ number_format($live->metrics['sales_created'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-3 text-right font-semibold text-green-700 whitespace-nowrap">Rp {{ number_format($live->metrics['sales_ready_to_ship'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="bg-gray-50/70">
                                            <td colspan="8" class="px-3 py-2">
                                                <details>
                                                    <summary class="cursor-pointer text-xs text-blue-700">Metrik lainnya dari Excel</summary>
                                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 py-3 text-xs">
                                                        <div><span class="text-gray-500">Durasi live</span><div class="font-semibold">{{ $live->metrics['duration'] ?? '-' }}</div></div>
                                                        <div><span class="text-gray-500">Komentar</span><div class="font-semibold">{{ number_format($live->metrics['comments'] ?? 0) }}</div></div>
                                                        <div><span class="text-gray-500">Tambah ke keranjang</span><div class="font-semibold">{{ number_format($live->metrics['add_to_cart'] ?? 0) }}</div></div>
                                                        <div><span class="text-gray-500">Rata-rata durasi ditonton</span><div class="font-semibold">{{ $live->metrics['average_watch_duration'] ?? '-' }}</div></div>
                                                        <div><span class="text-gray-500">Pesanan dibuat</span><div class="font-semibold">{{ number_format($live->metrics['orders_created'] ?? 0) }}</div></div>
                                                        <div><span class="text-gray-500">Produk terjual (pesanan dibuat)</span><div class="font-semibold">{{ number_format($live->metrics['products_sold_created'] ?? 0) }}</div></div>
                                                    </div>
                                                </details>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                            </section>
                        @endif

                        @if($liveType !== 'live' && $videoRows->isNotEmpty())
                            <section>
                                <h4 class="font-semibold text-gray-800 mb-3">Daftar Video</h4>
                                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 mb-4">
                                    File ekspor video tidak memuat data penjualan. Penjualan tiap video ditandai “Tidak tersedia” dan tidak dianggap Rp0.
                                </p>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50 text-xs text-gray-600">
                                            <tr>
                                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap">Tanggal Posting</th>
                                                <th class="px-3 py-3 text-left font-semibold min-w-[280px]">Judul Video</th>
                                                <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Ditonton</th>
                                                <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Suka</th>
                                                <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Komentar</th>
                                                <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Rata-rata Ditonton</th>
                                                <th class="px-3 py-3 text-right font-semibold whitespace-nowrap">Penjualan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($videoRows as $video)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-3 whitespace-nowrap text-gray-600">{{ \Carbon\Carbon::parse($video->metrics['published_at'])->format('d M Y H:i') }}</td>
                                                    <td class="px-3 py-3 text-gray-800 whitespace-pre-line">{{ $video->judul }}</td>
                                                    <td class="px-3 py-3 text-right font-semibold text-gray-800">{{ number_format($video->metrics['views'] ?? 0) }}</td>
                                                    <td class="px-3 py-3 text-right text-gray-700">{{ number_format($video->metrics['likes'] ?? 0) }}</td>
                                                    <td class="px-3 py-3 text-right text-gray-700">{{ number_format($video->metrics['comments'] ?? 0) }}</td>
                                                    <td class="px-3 py-3 text-right text-gray-700">{{ $video->metrics['average_watch_duration'] ?? '-' }}</td>
                                                    <td class="px-3 py-3 text-right text-gray-400 whitespace-nowrap">Tidak tersedia</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- ============ TAB: TOP PRODUK ============ --}}
        @if($activeTab === 'top')
            @if(!empty($topProducts['insight']) && $topProducts['insight']['total_sku'] > 0)

                {{-- Ringkasan Insight --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <iconify-icon icon="mdi:package-variant-closed" class="text-gray-500 text-lg"></iconify-icon>
                            <div class="text-xs text-gray-500">SKU Ter-mapping</div>
                        </div>
                        <div class="text-xl font-bold text-gray-800">{{ $topProducts['insight']['total_sku'] }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <iconify-icon icon="mdi:trending-up" class="text-green-600 text-lg"></iconify-icon>
                            <div class="text-xs text-gray-500">SKU Profit</div>
                        </div>
                        <div class="text-xl font-bold text-green-600">{{ $topProducts['insight']['sku_profit'] }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <iconify-icon icon="mdi:trending-down" class="text-red-600 text-lg"></iconify-icon>
                            <div class="text-xs text-gray-500">SKU Rugi</div>
                        </div>
                        <div class="text-xl font-bold text-red-600">{{ $topProducts['insight']['sku_rugi'] }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <iconify-icon icon="mdi:calculator" class="text-blue-600 text-lg"></iconify-icon>
                            <div class="text-xs text-gray-500">Rata-rata Profit</div>
                        </div>
                        <div class="text-xl font-bold text-blue-600">
                            Rp {{ number_format($topProducts['insight']['avg_profit'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {{-- Top 10 by Qty --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2 bg-gradient-to-r from-blue-50 to-white">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <iconify-icon icon="mdi:fire" class="text-blue-600 text-lg"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">Top 10 Terlaris</h4>
                                <p class="text-[10px] text-gray-500">Berdasarkan qty terjual</p>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse($topProducts['top_qty'] as $idx => $p)
                            <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                                    {{ $idx === 0 ? 'bg-yellow-100 text-yellow-700' : ($idx === 1 ? 'bg-gray-200 text-gray-700' : ($idx === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500')) }}">
                                    {{ $idx + 1 }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-mono text-[11px] text-gray-700 truncate" title="{{ $p['marketplace_sku'] }}">
                                        {{ $p['marketplace_sku'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 truncate" title="{{ $p['nama_produk'] }}">
                                        {{ \Illuminate\Support\Str::limit($p['nama_produk'], 30, '…') }}
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold text-blue-600">{{ number_format($p['total_qty']) }}</div>
                                    <div class="text-[10px] text-gray-400">unit</div>
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Top 10 by Profit --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2 bg-gradient-to-r from-green-50 to-white">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <iconify-icon icon="mdi:trophy" class="text-green-600 text-lg"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">Top 10 Paling Profit</h4>
                                <p class="text-[10px] text-gray-500">Berdasarkan total profit</p>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse($topProducts['top_profit'] as $idx => $p)
                            <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                                    {{ $idx === 0 ? 'bg-yellow-100 text-yellow-700' : ($idx === 1 ? 'bg-gray-200 text-gray-700' : ($idx === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500')) }}">
                                    {{ $idx + 1 }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-mono text-[11px] text-gray-700 truncate" title="{{ $p['marketplace_sku'] }}">
                                        {{ $p['marketplace_sku'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 truncate" title="{{ $p['nama_produk'] }}">
                                        {{ \Illuminate\Support\Str::limit($p['nama_produk'], 30, '…') }}
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold text-green-600">
                                        {{ number_format($p['profit'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">profit</div>
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Bottom 10 by Profit --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2 bg-gradient-to-r from-red-50 to-white">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <iconify-icon icon="mdi:alert-octagon" class="text-red-600 text-lg"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">10 Profit Terendah</h4>
                                <p class="text-[10px] text-gray-500">Untuk evaluasi</p>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse($topProducts['bottom_profit'] as $idx => $p)
                            <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-red-50 text-red-600 flex-shrink-0">
                                    {{ $idx + 1 }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-mono text-[11px] text-gray-700 truncate" title="{{ $p['marketplace_sku'] }}">
                                        {{ $p['marketplace_sku'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 truncate" title="{{ $p['nama_produk'] }}">
                                        {{ \Illuminate\Support\Str::limit($p['nama_produk'], 30, '…') }}
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold {{ $p['profit'] > 0 ? 'text-orange-600' : 'text-red-600' }}">
                                        {{ number_format($p['profit'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        @if($p['profit'] < 0)
                                            <span class="text-red-500 font-medium">RUGI</span>
                                        @else
                                            profit
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <iconify-icon icon="mdi:trophy" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    <div class="text-gray-500">Belum ada SKU yang dimapping. Cocokan produk dulu untuk melihat Top 10.</div>
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        <iconify-icon icon="mdi:tag-multiple" class="text-lg"></iconify-icon>
                        Atur Kategori & Prefix
                    </a>
                </div>
            @endif
        @endif

    @endif
</div>

<script>
    document.addEventListener('input', function (event) {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || !input.matches('[data-rupiah-input]')) return;

        const digits = input.value.replace(/\D/g, '');
        input.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
</script>

{{-- ============ CHART.JS UNTUK GRAFIK BULANAN ============ --}}
@if(!empty($monthlyData['months']) && $activeTab === 'bulanan')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('monthlyChart');
        if (!ctx) return;

        const months = @json(array_reverse($monthlyData['months']));

        const labels = months.map(m => m.label);
        const netRevenue = months.map(m => m.net_revenue);
        const profit = months.map(m => m.profit);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Net Revenue',
                        data: netRevenue,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Profit',
                        data: profit,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 12, family: 'Geist' },
                            usePointStyle: true,
                            padding: 16,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        padding: 12,
                        titleFont: { size: 13, family: 'Geist' },
                        bodyFont: { size: 12, family: 'Geist' },
                        callbacks: {
                            label: function (context) {
                                let value = context.parsed.y || 0;
                                return context.dataset.label + ': Rp ' + value.toLocaleString('id-ID');
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, family: 'Geist' } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            font: { size: 11, family: 'Geist' },
                            callback: function (value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                return 'Rp ' + value;
                            },
                        },
                    },
                },
            },
        });
    });
</script>


@endif

@endsection