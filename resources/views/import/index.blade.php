@extends('layouts.app')

@section('title', 'Import Shopee')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
            <iconify-icon icon="mdi:shopping" class="text-orange-600 text-xl"></iconify-icon>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Import Shopee</h2>
            <p class="text-sm text-gray-500">Upload laporan dari Shopee Seller Center untuk diolah</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:check-circle" class="text-green-600 text-lg mt-0.5"></iconify-icon>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5"></iconify-icon>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-start gap-2">
                <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5"></iconify-icon>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @php
        $shopeeStores = $stores->where('marketplace', 'shopee');
    @endphp

    @if($shopeeStores->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-4 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:alert" class="text-yellow-600 text-lg mt-0.5"></iconify-icon>
            <div>
                <strong>Belum ada toko Shopee.</strong>
                Silakan <a href="{{ route('stores.index', ['modal' => 'create']) }}" class="underline font-semibold">tambah toko</a> dulu sebelum import.
            </div>
        </div>
    @else

        {{-- ============ FORM IMPORT ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">

            {{-- Header Card — ORANGE gradient --}}
            <div class="px-6 py-4 border-b border-orange-100 bg-gradient-to-r from-orange-50 to-white">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="mdi:file-excel" class="text-green-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800">Import Laporan Shopee</h3>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Upload <strong>2 file</strong> dari Shopee Seller Center sekaligus
                </p>
            </div>

            {{-- Form Body --}}
            <form action="{{ route('import.shopee.both') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="p-6">

                    {{-- Pilih Toko --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Toko</label>
                        <select name="store_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="">-- Pilih Toko Shopee --</option>
                            @foreach($shopeeStores as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_toko }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2 File Input --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- File 1: Order.all — ORANGE --}}
                        <div class="border-2 border-dashed border-orange-300 rounded-xl p-5 bg-orange-50/30 hover:bg-orange-50 transition">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-orange-500 text-white rounded-lg flex items-center justify-center text-sm font-bold">1</div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">File Order.all</div>
                                    <div class="text-xs text-gray-500">Detail varian & qty</div>
                                </div>
                            </div>

                            <input type="file" name="order_file" accept=".xlsx,.xls" required
                                   class="w-full text-xs text-gray-600
                                          file:mr-3 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-orange-500 file:text-white
                                          hover:file:bg-orange-600
                                          file:cursor-pointer file:transition">

                            <div class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <iconify-icon icon="mdi:information-outline" class="text-xs"></iconify-icon>
                                Laporan Penjualan → <strong>Order.all</strong>
                            </div>
                        </div>

                        {{-- File 2: Income — ORANGE lebih gelap --}}
                        <div class="border-2 border-dashed border-amber-300 rounded-xl p-5 bg-amber-50/30 hover:bg-amber-50 transition">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-amber-500 text-white rounded-lg flex items-center justify-center text-sm font-bold">2</div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">File Income</div>
                                    <div class="text-xs text-gray-500">Uang cair & biaya</div>
                                </div>
                            </div>

                            <input type="file" name="income_file" accept=".xlsx,.xls" required
                                   class="w-full text-xs text-gray-600
                                          file:mr-3 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-amber-500 file:text-white
                                          hover:file:bg-amber-600
                                          file:cursor-pointer file:transition">

                            <div class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <iconify-icon icon="mdi:information-outline" class="text-xs"></iconify-icon>
                                Laporan → <strong>Penghasilan</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Info Box — ORANGE theme --}}
                    <div class="mt-5 bg-orange-50 border border-orange-100 rounded-lg p-3 flex items-start gap-2">
                        <iconify-icon icon="mdi:lightbulb-on" class="text-orange-600 text-base mt-0.5"></iconify-icon>
                        <div class="text-xs text-orange-800">
                            <strong>Tips:</strong> Kalau file Order.all sudah pernah diimport (duplikat), sistem akan skip dan tetap proses Income.
                            Rekonsiliasi otomatis dijalankan setelahnya.
                        </div>
                    </div>
                </div>

                {{-- Footer / Button — ORANGE --}}
                <div class="px-6 py-4 bg-orange-50/50 border-t border-orange-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-orange-500 text-white px-6 py-2.5 rounded-lg hover:bg-orange-600 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:cloud-upload" class="text-lg"></iconify-icon>
                        Import Kedua File
                    </button>
                </div>
            </form>
        </div>

    @endif

    {{-- ============ RIWAYAT IMPORT ============ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:history" class="text-gray-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Riwayat Import Shopee</h3>
            </div>
            <div class="text-xs text-gray-500">
                {{ $imports->total() }} file
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">File</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Toko</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Baris</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($imports as $imp)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="mdi:file-excel" class="text-green-600 text-base"></iconify-icon>
                                <span class="text-xs text-gray-700">{{ $imp->file_name }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-gray-700">{{ $imp->store?->nama_toko ?? '-' }}</td>
                        <td class="py-3 px-4">
                            @php
                                $badge = match($imp->tipe) {
                                    'shopee_order'  => ['bg-orange-50 text-orange-700', 'mdi:file-document-outline'],
                                    'shopee_income' => ['bg-amber-50 text-amber-700', 'mdi:cash-multiple'],
                                    default         => ['bg-gray-50 text-gray-700', 'mdi:file'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 {{ $badge[0] }} px-2 py-1 rounded-full text-xs font-medium">
                                <iconify-icon icon="{{ $badge[1] }}" class="text-xs"></iconify-icon>
                                {{ $imp->tipe }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600">
                            @if($imp->periode_start)
                                {{ $imp->periode_start->format('d/m/Y') }} – {{ $imp->periode_end?->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center text-gray-700 font-medium">{{ $imp->total_rows }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($imp->status === 'imported')
                                <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                                    <iconify-icon icon="mdi:check-circle" class="text-xs"></iconify-icon>
                                    Berhasil
                                </span>
                            @elseif($imp->status === 'failed')
                                <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 px-2 py-1 rounded-full text-xs font-medium">
                                    <iconify-icon icon="mdi:alert-circle" class="text-xs"></iconify-icon>
                                    Gagal
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-700 px-2 py-1 rounded-full text-xs font-medium">
                                    {{ $imp->status }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-500">
                            <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                            Belum ada riwayat import.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($imports->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                Menampilkan
                <strong>{{ $imports->firstItem() ?? 0 }}</strong>–<strong>{{ $imports->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $imports->total() }}</strong> import
            </div>
            <div>
                {{ $imports->links() }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection