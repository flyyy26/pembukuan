@extends('layouts.app')

@section('title', 'Gaji Karyawan')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:cash-multiple" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Transaksi Gaji</h2>
                <p class="text-xs sm:text-sm text-gray-500">Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto mt-2 sm:mt-0">
            <button type="button" onclick="openKomisiModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:cog-outline" class="text-lg"></iconify-icon>
                Pengaturan Komisi
            </button>
            <button type="button" onclick="openGenerateModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:autorenew" class="text-lg"></iconify-icon>
                Generate Gaji
            </button>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Periode (Bulan)</label>
                <input type="month" name="periode" value="{{ $periode }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="dibayar" {{ $status === 'dibayar' ? 'selected' : '' }}>Dibayar</option>
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($storeId !== 'all' || $status !== 'all' || $periode !== now()->format('Y-m'))
                    <a href="{{ route('gaji.index') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
    
    {{-- SUMMARY (4 KOLOM) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-6">
        
        {{-- Total Gaji --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:wallet" class="text-indigo-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[9px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Total Gaji</div>
            <div class="text-xs sm:text-xl font-bold text-gray-800 break-words">
                <span class="text-[8px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_gaji'], 0, ',', '.') }}
            </div>
            <div class="text-[9px] sm:text-xs text-gray-400 mt-0.5 sm:mt-1 leading-tight">{{ $summary['jumlah_karyawan'] }} karyawan</div>
        </div>

        {{-- Sudah Dibayar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:check-circle" class="text-green-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[9px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Sudah Dibayar</div>
            <div class="text-xs sm:text-xl font-bold text-green-600 break-words">
                <span class="text-[8px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_dibayar'], 0, ',', '.') }}
            </div>
        </div>

        {{-- Belum Dibayar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:clock-alert" class="text-orange-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[9px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Belum Dibayar</div>
            <div class="text-xs sm:text-xl font-bold text-orange-600 break-words">
                <span class="text-[8px] sm:text-xs text-gray-400 font-normal">Rp</span>
                {{ number_format($summary['total_draft'], 0, ',', '.') }}
            </div>
        </div>

        {{-- ✅ VOLUME TRANSAKSI AGEN --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 sm:p-4 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:receipt-text-outline" class="text-blue-600 text-xs sm:text-lg"></iconify-icon>
                </div>
            </div>
            <div class="text-[9px] sm:text-xs text-gray-500 mb-0.5 sm:mb-1 leading-tight">Volume Transaksi Agen</div>
            <div class="text-xs sm:text-xl font-bold text-blue-700">
                {{ number_format($totalVolumeTransaksi) }} <span class="text-[10px] text-gray-500 font-normal">Trx</span>
            </div>
            @if($storeId !== 'all')
                <div class="text-[9px] sm:text-xs text-indigo-600 mt-0.5 sm:mt-1 leading-tight font-medium">
                    Komisi: Rp {{ number_format($komisiSettings[$storeId] ?? 0, 0, ',', '.') }}/trx
                </div>
            @else
                <div class="text-[9px] sm:text-xs text-gray-400 mt-0.5 sm:mt-1 leading-tight">Basis komisi per toko</div>
            @endif
        </div>
    </div>

    {{-- DATA GAJI --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Rincian Gaji</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $gajiList->count() }} data</div>
        </div>

        @if($gajiList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Belum ada data gaji untuk periode ini. <br/>Klik "Generate Gaji" untuk membuat draft.</span>
            </div>
        @else

            {{-- TAMPILAN TABEL (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Karyawan</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Toko</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Gaji Pokok</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Bonus</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Komisi</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Potongan</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Total</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Status</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-48 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($gajiList as $g)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-medium text-gray-800 whitespace-nowrap">{{ $g->karyawan->nama ?? '-' }}</td>
                                <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                    <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                    {{ $g->karyawan->store->nama_toko ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-right text-gray-700 whitespace-nowrap">Rp {{ number_format($g->gaji_pokok, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-emerald-600 whitespace-nowrap">Rp {{ number_format($g->bonus, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-indigo-600 whitespace-nowrap">Rp {{ number_format($g->komisi, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-red-600 whitespace-nowrap">Rp {{ number_format($g->potongan, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900 whitespace-nowrap">Rp {{ number_format($g->total_gaji, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($g->status === 'dibayar')
                                        <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:check-circle" class="text-sm"></iconify-icon> Dibayar
                                        </span>
                                        @if($g->tanggal_bayar)
                                            <div class="text-[10px] text-gray-400 mt-1">{{ $g->tanggal_bayar->format('d/m/Y') }}</div>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:clock-alert" class="text-sm"></iconify-icon> Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1 flex-wrap justify-center">
                                        @if($g->status === 'draft')
                                            <button type="button"
                                                    onclick="openEditGajiModal({{ $g->id }}, '{{ addslashes($g->karyawan->nama ?? '') }}', {{ $g->bonus }}, {{ $g->komisi }}, {{ $g->potongan }}, '{{ $g->metode_bayar }}')"
                                                    class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-blue-200">
                                                <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                            </button>
                                            <button type="button"
                                                    onclick="bayarGaji({{ $g->id }}, '{{ addslashes($g->karyawan->nama ?? '') }}', {{ $g->total_gaji }})"
                                                    class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-emerald-200">
                                                <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon> Bayar
                                            </button>
                                            <button type="button"
                                                    onclick="deleteGaji({{ $g->id }})"
                                                    class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition border sm:border-0 border-red-200">
                                                <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Lunas</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN CARD (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($gajiList as $g)
                    <div class="p-4 space-y-2 text-xs">
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold text-gray-800 text-sm">{{ $g->karyawan->nama ?? '-' }}</div>
                                <div class="flex items-center gap-1 text-[10px] text-gray-500 mt-0.5">
                                    <iconify-icon icon="mdi:store" class="text-xs"></iconify-icon>
                                    {{ $g->karyawan->store->nama_toko ?? '-' }}
                                </div>
                            </div>
                            <div class="text-right">
                                @if($g->status === 'dibayar')
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">
                                        <iconify-icon icon="mdi:check-circle" class="text-xs"></iconify-icon> Dibayar
                                    </span>
                                    @if($g->tanggal_bayar)
                                        <div class="text-[9px] text-gray-400 mt-0.5">{{ $g->tanggal_bayar->format('d/m/Y') }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded font-medium">
                                        <iconify-icon icon="mdi:clock-alert" class="text-xs"></iconify-icon> Draft
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-50 mt-2">
                            <div>
                                <span class="text-gray-400 block text-[10px]">Gaji Pokok</span>
                                <span class="text-gray-700">Rp {{ number_format($g->gaji_pokok, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-400 block text-[10px]">Total Diterima</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($g->total_gaji, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">Bonus</span>
                                <span class="text-emerald-600">+ Rp {{ number_format($g->bonus, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-400 block text-[10px]">Komisi</span>
                                <span class="text-indigo-600">+ Rp {{ number_format($g->komisi, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-span-2 text-right">
                                <span class="text-gray-400 block text-[10px]">Potongan</span>
                                <span class="text-red-600">- Rp {{ number_format($g->potongan, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if($g->status === 'draft')
                            <div class="flex gap-2 pt-2">
                                <button type="button"
                                        onclick="openEditGajiModal({{ $g->id }}, '{{ addslashes($g->karyawan->nama ?? '') }}', {{ $g->bonus }}, {{ $g->komisi }}, {{ $g->potongan }}, '{{ $g->metode_bayar }}')"
                                        class="flex-1 inline-flex justify-center items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                </button>
                                <button type="button"
                                        onclick="bayarGaji({{ $g->id }}, '{{ addslashes($g->karyawan->nama ?? '') }}', {{ $g->total_gaji }})"
                                        class="flex-1 inline-flex justify-center items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-1.5 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon> Bayar
                                </button>
                                <button type="button"
                                        onclick="deleteGaji({{ $g->id }})"
                                        class="flex-none inline-flex justify-center items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon>
                                </button>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

        @endif
    </div>
</div>

{{-- MODAL GENERATE --}}
<div id="generateModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeGenerateModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-indigo-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:autorenew" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Generate Gaji</h3>
            </div>
            <button type="button" onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="generateForm" onsubmit="submitGenerate(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorGenerate"></div>

                <div class="bg-indigo-50 rounded-lg p-3 text-xs text-indigo-800 flex items-start gap-2">
                    <iconify-icon icon="mdi:information" class="text-base mt-0.5 flex-shrink-0"></iconify-icon>
                    <div>
                        Draft gaji akan dibuat untuk <strong>semua karyawan aktif</strong> di periode ini.
                        Karyawan yang sudah punya gaji di periode ini akan dilewati.
                        <br>Potongan otomatis mengambil dari <strong>laporan minus yang belum dipotong</strong>.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Periode (Bulan) <span class="text-red-500">*</span></label>
                    <input type="month" name="periode" id="generatePeriode" value="{{ $periode }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                    <select name="store_id" id="generateStoreId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">Semua Toko</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitGenerate" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:autorenew" class="text-lg"></iconify-icon> Generate
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT GAJI --}}
<div id="gajiModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeGajiModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:pencil" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Edit Gaji <span id="gajiKaryawanNama" class="text-blue-600"></span></h3>
            </div>
            <button type="button" onclick="closeGajiModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="gajiForm" onsubmit="submitEditGaji(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="gajiId" value="">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorGaji"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bonus</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputBonus" name="bonus" placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                {{-- ✅ KOMISI DISABLE --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Komisi (Terkunci)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputKomisi" name="komisi" placeholder="0" disabled
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-3 py-2 text-sm font-bold bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
                    </div>
                </div>

                {{-- ✅ POTONGAN DISABLE --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Potongan (Terkunci)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputPotongan" name="potongan" placeholder="0" disabled
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-3 py-2 text-sm font-bold bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Metode Bayar</label>
                    <select id="inputMetode" name="metode_bayar" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="tunai">Tunai</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeGajiModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitGaji" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL PENGATURAN KOMISI --}}
<div id="komisiModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeKomisiModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cog-outline" class="text-gray-700 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Pengaturan Komisi Karyawan</h3>
            </div>
            <button type="button" onclick="closeKomisiModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="komisiForm" onsubmit="submitKomisiSettings(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            
            <div class="p-5">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm mb-4" id="modalErrorKomisi"></div>

                <div class="bg-blue-50 rounded-lg p-3 text-xs text-blue-800 flex items-start gap-2 mb-4">
                    <iconify-icon icon="mdi:information" class="text-base mt-0.5 flex-shrink-0"></iconify-icon>
                    <div>
                        Atur nominal komisi per transaksi untuk tiap toko. Komisi ini akan dikalikan dengan <strong>Volume Transaksi Agen</strong> toko tersebut pada bulan yang dipilih, lalu dibagi rata ke seluruh karyawan aktif di toko tersebut.
                    </div>
                </div>

                {{-- Tombol Isi Semua Sama --}}
                <div class="flex flex-col sm:flex-row items-center gap-2 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Isi Semua Sama:</label>
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputKomisiMassal" placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <button type="button" onclick="applyKomisiMassal()" class="w-full sm:w-auto bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition text-sm font-medium flex items-center justify-center gap-2">
                        <iconify-icon icon="mdi:arrow-down-bold-box" class="text-lg"></iconify-icon>
                        Terapkan
                    </button>
                </div>

                {{-- List Toko --}}
                <div class="space-y-2">
                    @foreach($stores as $s)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-2">
                            <div class="font-medium text-sm text-gray-800">{{ $s->nama_toko }}</div>
                            <div class="relative w-full sm:w-48">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm font-medium">Rp</span>
                                </div>
                                <input type="text" name="komisi[{{ $s->id }}]" 
                                    id="komisi-input-{{ $s->id }}"
                                    value="{{ $komisiSettings[$s->id] ?? 0 > 0 ? number_format($komisiSettings[$s->id], 0, ',', '.') : '' }}"
                                    placeholder="0" data-store-id="{{ $s->id }}"
                                    class="input-komisi-store w-full border border-gray-300 rounded-lg pl-10 pr-12 py-1.5 text-sm font-bold text-right focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400 text-[10px] font-medium">/Trx</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeKomisiModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitKomisi" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_GAJI = document.querySelector('meta[name="csrf-token"]').content;

    // Format input bonus & potongan
    document.addEventListener('DOMContentLoaded', function () {
        ['inputBonus', 'inputKomisi', 'inputPotongan'].forEach(id => {
            const inp = document.getElementById(id);
            if (inp) inp.addEventListener('input', function () {
                let v = this.value.replace(/[^0-9]/g, '');
                this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
            });
        });
    });


    // ===== GENERATE =====
    function openGenerateModal() {
        document.getElementById('generateForm').reset();
        document.getElementById('modalErrorGenerate').classList.add('hidden');
        document.getElementById('generatePeriode').value = "{{ $periode }}";
        document.getElementById('generateModal').classList.remove('hidden');
        document.getElementById('generateModal').classList.add('flex');
    }

    function closeGenerateModal() {
        document.getElementById('generateModal').classList.add('hidden');
        document.getElementById('generateModal').classList.remove('flex');
    }

    async function submitGenerate(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitGenerate');
        const errDiv = document.getElementById('modalErrorGenerate');
        btn.disabled = true;
        btn.innerHTML = 'Memproses...';
        errDiv.classList.add('hidden');

        try {
            const res = await fetch("{{ route('gaji.generate') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_GAJI,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            alert(data.message);
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:autorenew" class="text-lg"></iconify-icon> Generate';
        }
    }

    // ===== EDIT GAJI =====
    function openEditGajiModal(id, nama, bonus, komisi, potongan, metode) {
        document.getElementById('gajiForm').reset();
        document.getElementById('modalErrorGaji').classList.add('hidden');
        document.getElementById('gajiId').value = id;
        document.getElementById('gajiKaryawanNama').textContent = nama;
        document.getElementById('inputBonus').value = bonus > 0 ? new Intl.NumberFormat('id-ID').format(bonus) : '';
        document.getElementById('inputKomisi').value = komisi > 0 ? new Intl.NumberFormat('id-ID').format(komisi) : '';
        document.getElementById('inputPotongan').value = potongan > 0 ? new Intl.NumberFormat('id-ID').format(potongan) : '';
        document.getElementById('inputMetode').value = metode || 'tunai';
        document.getElementById('gajiModal').classList.remove('hidden');
        document.getElementById('gajiModal').classList.add('flex');
    }

    function closeGajiModal() {
        document.getElementById('gajiModal').classList.add('hidden');
        document.getElementById('gajiModal').classList.remove('flex');
    }

    async function submitEditGaji(e) {
        e.preventDefault();
        const id = document.getElementById('gajiId').value;
        const btn = document.getElementById('btnSubmitGaji');
        const errDiv = document.getElementById('modalErrorGaji');
        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN_GAJI);
        formData.append('_method', 'PUT');
        formData.append('bonus', document.getElementById('inputBonus').value.replace(/[^0-9]/g, ''));
        
        // ✅ Nilai komisi & potongan tetap dikirim meskipun input disabled
        formData.append('komisi', document.getElementById('inputKomisi').value.replace(/[^0-9]/g, ''));
        formData.append('potongan', document.getElementById('inputPotongan').value.replace(/[^0-9]/g, ''));
        
        formData.append('metode_bayar', document.getElementById('inputMetode').value);

        try {
            const res = await fetch(`/gaji/${id}/ajax`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_GAJI, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            closeGajiModal();
            window.location.reload();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan';
        }
    }

    // ===== BAYAR =====
    async function bayarGaji(id, nama, total) {
        if (!confirm(`Bayar gaji ${nama} sebesar Rp ${new Intl.NumberFormat('id-ID').format(total)}?`)) return;
        try {
            const res = await fetch(`/gaji/${id}/bayar`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_GAJI, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    // ===== DELETE =====
    async function deleteGaji(id) {
        if (!confirm('Hapus draft gaji ini?')) return;
        try {
            const res = await fetch(`/gaji/${id}/ajax`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_GAJI, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal.');
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeGenerateModal(); closeGajiModal(); }
    });

    // ===== PENGATURAN KOMISI =====
    function openKomisiModal() {
        document.getElementById('komisiForm').reset();
        document.getElementById('modalErrorKomisi').classList.add('hidden');
        document.getElementById('inputKomisiMassal').value = '';
        
        // Re-format input saat modal dibuka
        document.querySelectorAll('.input-komisi-store').forEach(inp => {
            let val = inp.value.replace(/[^0-9]/g, '');
            inp.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
        });

        document.getElementById('komisiModal').classList.remove('hidden');
        document.getElementById('komisiModal').classList.add('flex');
    }

    function closeKomisiModal() {
        document.getElementById('komisiModal').classList.add('hidden');
        document.getElementById('komisiModal').classList.remove('flex');
    }

    // Format input massal
    document.getElementById('inputKomisiMassal')?.addEventListener('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
    });

    // Format input per store
    document.querySelectorAll('.input-komisi-store').forEach(inp => {
        inp.addEventListener('input', function () {
            let v = this.value.replace(/[^0-9]/g, '');
            this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
        });
    });

    // Terapkan komisi massal
    function applyKomisiMassal() {
        const massalVal = document.getElementById('inputKomisiMassal').value;
        if (!massalVal) {
            alert('Isi nominal komisi terlebih dahulu.');
            return;
        }
        document.querySelectorAll('.input-komisi-store').forEach(inp => {
            inp.value = massalVal;
        });
    }

    // Submit pengaturan komisi
    async function submitKomisiSettings(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitKomisi');
        const errDiv = document.getElementById('modalErrorKomisi');
        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN_GAJI);

        document.querySelectorAll('.input-komisi-store').forEach(inp => {
            const storeId = inp.getAttribute('data-store-id');
            const rawVal = inp.value.replace(/[^0-9]/g, '');
            formData.append(`komisi[${storeId}]`, rawVal || '0');
        });

        try {
            const res = await fetch("{{ route('gaji.komisi-settings') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_GAJI, 'Accept': 'application/json' },
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
            btn.innerHTML = '<iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan Pengaturan';
        }
    }
    
    document.addEventListener('keydown', e => { 
        if (e.key === 'Escape') { 
            closeGenerateModal(); 
            closeGajiModal(); 
            closeKomisiModal(); 
        } 
    });
</script>
@endsection