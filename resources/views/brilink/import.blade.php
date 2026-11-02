@extends('layouts.app')
@section('title', 'Import BRILink')

@section('content')
<div class="w-full">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center">
            <iconify-icon icon="mdi:bank" class="text-white text-xl"></iconify-icon>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Import BRILink</h2>
            <p class="text-sm text-gray-500">Upload file Excel Buku BRILink (6 sheet)</p>
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

    {{-- Form Import --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <form action="{{ route('brilink.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                {{-- Pilih Toko --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Toko BRILink <span class="text-red-500">*</span>
                    </label>
                    <select name="store_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Toko --</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ old('store_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_toko }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Laporan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Laporan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal" required
                           value="{{ old('tanggal', date('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    @error('tanggal')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Upload File --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    File Excel BRILink <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file" accept=".xlsx,.xls" required
                       class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-xs file:bg-emerald-600 file:text-white file:font-medium
                              hover:file:bg-emerald-700 file:cursor-pointer file:transition
                              border border-gray-300 rounded-lg">
                @error('file')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Format: <strong>.xlsx</strong> atau <strong>.xls</strong> · Maksimal 20 MB
                </p>
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center gap-2 shadow-sm">
                    <iconify-icon icon="mdi:cloud-upload" class="text-lg"></iconify-icon>
                    Import File
                </button>
            </div>
        </form>
    </div>

    {{-- Riwayat Import --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:history" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800">Riwayat Import BRILink</h3>
                <span class="text-xs text-gray-500">({{ $imports->total() }} file)</span>
            </div>

            {{-- Filter Tanggal Riwayat --}}
            <form method="GET" action="{{ route('brilink.import') }}" class="flex flex-wrap items-center gap-2">
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Dari</label>
                    <input type="date" name="from" value="{{ $fromInput ?? now()->format('Y-m-d') }}"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Sampai</label>
                    <input type="date" name="to" value="{{ $toInput ?? now()->format('Y-m-d') }}"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex gap-1 pt-3.5">
                    <button type="submit"
                        class="inline-flex items-center gap-1 bg-emerald-600 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-700 transition text-xs font-medium">
                        <iconify-icon icon="mdi:filter" class="text-sm"></iconify-icon>
                        Filter
                    </button>
                    @if(($fromInput ?? null) || ($toInput ?? null))
                        <a href="{{ route('brilink.import') }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-white border border-gray-300 text-gray-500 hover:text-gray-700 rounded-lg transition"
                            title="Reset ke hari ini">
                            <iconify-icon icon="mdi:refresh" class="text-sm"></iconify-icon>
                            Hari Ini
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">File</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Toko</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Baris</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-24">Aksi</th> {{-- BARU --}}
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($imports as $imp)
                    <tr class="hover:bg-gray-50 transition" id="import-row-{{ $imp->id }}">
                        <td class="py-3 px-4 text-xs text-gray-700">{{ $imp->file_name }}</td>
                        <td class="py-3 px-4 text-gray-700">{{ $imp->store?->nama_toko ?? '-' }}</td>
                        <td class="py-3 px-4 text-xs">
                            @if($imp->periode_start)
                                {{ $imp->periode_start->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center text-gray-700">{{ $imp->total_rows }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-block px-2 py-1 rounded-full text-xs
                                {{ $imp->status === 'imported' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-700' }}">
                                {{ $imp->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <form action="{{ route('brilink.import.destroy', $imp->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus data import {{ addslashes($imp->file_name) }}?\\n\\nSEMUA data transaksi dari file ini akan ikut terhapus permanen.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400">
                            <iconify-icon icon="mdi:inbox-outline" class="text-4xl block mb-2 w-max mx-auto"></iconify-icon>
                            <div class="text-sm">Belum ada riwayat import pada rentang tanggal ini.</div>
                            <div class="text-xs mt-1 text-gray-400">
                                Coba ubah filter tanggal atau klik "Hari Ini".
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($imports->hasPages())
            <div class="px-5 py-4 border-t">{{ $imports->links() }}</div>
        @endif
    </div>
</div>

@endsection