@extends('layouts.app')

@section('title', 'Import TikTok')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
            <iconify-icon icon="mdi:music-note" class="text-white text-xl"></iconify-icon>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Import TikTok Shop</h2>
            <p class="text-sm text-gray-500">Upload 2 file Excel dari TikTok Seller Center</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4 flex items-start gap-2">
            <iconify-icon icon="mdi:check-circle" class="text-green-600 text-lg mt-0.5 flex-shrink-0"></iconify-icon>
            <div class="whitespace-pre-line text-sm leading-relaxed">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4 flex items-start gap-2">
            <iconify-icon icon="mdi:alert" class="text-yellow-600 text-lg mt-0.5 flex-shrink-0"></iconify-icon>
            <div class="whitespace-pre-line text-sm leading-relaxed">{{ session('warning') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4 flex items-start gap-2">
            <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5 flex-shrink-0"></iconify-icon>
            <div class="whitespace-pre-line text-sm leading-relaxed">{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-2">
                <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5 flex-shrink-0"></iconify-icon>
                <ul class="list-disc list-inside text-sm leading-relaxed">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($stores->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-4 rounded-lg mb-6 flex items-start gap-2">
            <iconify-icon icon="mdi:alert" class="text-yellow-600 text-lg mt-0.5"></iconify-icon>
            <div>
                <strong>Belum ada toko TikTok.</strong>
                Silakan <a href="{{ route('stores.index', ['modal' => 'create']) }}" class="underline font-semibold">tambah toko</a> dulu dengan marketplace TikTok.
            </div>
        </div>
    @else

        {{-- ============ FORM IMPORT ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">

            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="mdi:file-excel" class="text-green-600 text-lg"></iconify-icon>
                    <h3 class="font-semibold text-gray-800">Import Laporan TikTok</h3>
                </div>
                <p class="text-xs text-gray-500 mt-1">Upload <strong>2 file Excel</strong> dari TikTok Seller Center</p>
            </div>

            <form action="{{ route('tiktok.import.both') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="p-6">

                    {{-- Pilih Toko --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Toko</label>
                        <select name="store_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                            <option value="">-- Pilih Toko TikTok --</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_toko }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2 File Input --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- File 1: Order --}}
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-5 bg-gray-50/30 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-gray-800 text-white rounded-lg flex items-center justify-center text-sm font-bold">1</div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">File Semua Pesanan TikTok</div>
                                    <div class="text-xs text-gray-500">OrderSKUList · detail pesanan</div>
                                </div>
                            </div>

                            <input type="file" name="order_file" accept=".xlsx,.xls" required
                                   class="w-full text-xs text-gray-600
                                          file:mr-3 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-gray-800 file:text-white
                                          hover:file:bg-gray-900
                                          file:cursor-pointer file:transition">

                            <div class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <iconify-icon icon="mdi:information-outline" class="text-xs"></iconify-icon>
                                Dari TikTok Seller → Order → Export
                            </div>
                        </div>

                        {{-- File 2: Income --}}
                        <div class="border-2 border-dashed border-pink-300 rounded-xl p-5 bg-pink-50/30 hover:bg-pink-50 transition">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-pink-500 text-white rounded-lg flex items-center justify-center text-sm font-bold">2</div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">File Income TikTok</div>
                                    <div class="text-xs text-gray-500">Detail pesanan · pendapatan</div>
                                </div>
                            </div>

                            <input type="file" name="income_file" accept=".xlsx,.xls" required
                                   class="w-full text-xs text-gray-600
                                          file:mr-3 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-pink-500 file:text-white
                                          hover:file:bg-pink-600
                                          file:cursor-pointer file:transition">

                            <div class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <iconify-icon icon="mdi:information-outline" class="text-xs"></iconify-icon>
                                Dari TikTok Seller → Finance → Income
                            </div>
                        </div>
                    </div>

                    {{-- Info Box --}}
                    <div class="mt-5 bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-start gap-2">
                        <iconify-icon icon="mdi:lightbulb-on" class="text-blue-600 text-base mt-0.5"></iconify-icon>
                        <div class="text-xs text-blue-800">
                            <strong>Tips:</strong> Kalau file sudah pernah diimport (duplikat), sistem akan skip dan tetap proses file lainnya.
                            Rekonsiliasi otomatis dijalankan setelahnya.
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-gray-800 text-white px-6 py-2.5 rounded-lg hover:bg-gray-900 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:cloud-upload" class="text-lg"></iconify-icon>
                        Import Kedua File
                    </button>
                </div>
            </form>
        </div>

    @endif

    {{-- ============ RIWAYAT IMPORT ============ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:history" class="text-gray-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Riwayat Import TikTok</h3>
                <span class="text-xs text-gray-500">({{ $imports->total() }} file)</span>
            </div>

            {{-- Filter Tanggal (opsional, tanpa default) --}}
            <form method="GET" action="{{ route('tiktok.import') }}" class="flex flex-wrap items-center gap-2">
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Dari</label>
                    <input type="date" name="from" value="{{ $fromInput }}"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-gray-800 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Sampai</label>
                    <input type="date" name="to" value="{{ $toInput }}"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-gray-800 focus:outline-none">
                </div>
                <div class="flex gap-1 pt-3.5">
                    <button type="submit"
                        class="inline-flex items-center gap-1 bg-gray-800 text-white px-3 py-1.5 rounded-lg hover:bg-gray-900 transition text-xs font-medium">
                        <iconify-icon icon="mdi:filter" class="text-sm"></iconify-icon>
                        Filter
                    </button>
                    @if($fromInput || $toInput)
                        <a href="{{ route('tiktok.import') }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-white border border-gray-300 text-gray-500 hover:text-gray-700 rounded-lg transition">
                            <iconify-icon icon="mdi:close" class="text-sm"></iconify-icon>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
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
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Aksi</th>
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
                                    'tiktok_order'  => ['bg-gray-100 text-gray-800', 'mdi:file-document-outline'],
                                    'tiktok_income' => ['bg-pink-50 text-pink-700', 'mdi:cash-multiple'],
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
                        <button type="button" onclick="deleteImport({{ $imp->id }}, '{{ addslashes($imp->file_name) }}')"
                            class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                        </button>
                    </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-500">
                            <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                            Belum ada riwayat import TikTok.
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

<script>
    const CSRF_TOKEN_TIKTOK = document.querySelector('meta[name="csrf-token"]').content;

    async function deleteImport(id, fileName) {
        if (!confirm(`Yakin ingin menghapus data import "${fileName}"?\n\nSEMUA data transaksi (Order & Income) dari file ini akan ikut terhapus permanen, dan laporan penjualan akan dihitung ulang.`)) {
            return;
        }

        try {
            const response = await fetch(`/tiktok/import/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_TIKTOK,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                alert(data.message || 'Gagal menghapus data.');
                return;
            }

            // ✅ REFRESH HALAMAN SETELAH SUKSES MENGHAPUS
            alert(data.message);
            window.location.reload();

        } catch (err) {
            alert('Gagal menghapus: ' + err.message);
        }
    }
</script>
@endsection