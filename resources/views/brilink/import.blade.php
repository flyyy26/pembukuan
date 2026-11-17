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
            <p class="text-sm text-gray-500">Upload hingga 10 file Excel Buku BRILink sekaligus</p>
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

    {{-- Form Import Multi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <form id="importForm" action="{{ route('brilink.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Pilih Toko (satu untuk semua file) --}}
            <div class="mb-4">
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

            {{-- Daftar File Dinamis --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Daftar File <span class="text-red-500">*</span>
                        <span id="fileCounter" class="text-xs text-gray-500 font-normal">(0 / 10)</span>
                    </label>
                    <button type="button" id="addFileBtn" onclick="addFileRow()"
                            class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-1.5 rounded-lg text-xs font-medium transition border border-emerald-200">
                        <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon>
                        Tambah File
                    </button>
                </div>

                <div id="fileRows" class="space-y-3">
                    {{-- Baris file dibuat via JS --}}
                </div>

                <p class="text-xs text-gray-500 mt-2">
                    Format: <strong>.xlsx</strong> atau <strong>.xls</strong> · Maksimal 20 MB per file · Maksimal 10 file.
                </p>
            </div>

            {{-- Tombol Submit --}}
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="submit" id="submitBtn"
                        class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <iconify-icon icon="mdi:cloud-upload" class="text-lg"></iconify-icon>
                    <span id="submitText">Import Semua File</span>
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
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Toko</label>
                    <select name="store_id"
                            class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none max-w-[180px]">
                        <option value="">Semua Toko</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ (string) $storeIdInput === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->nama_toko }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Dari</label>
                    <input type="date" name="from" 
                        value="{{ $fromInput ?? now()->startOfMonth()->format('Y-m-d') }}"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Sampai</label>
                    <input type="date" name="to" 
                        value="{{ $toInput ?? now()->format('Y-m-d') }}"
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
                            title="Reset ke bulan ini">
                            <iconify-icon icon="mdi:refresh" class="text-sm"></iconify-icon>
                            Bulan Ini
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

<script>
    const MAX_FILES = 10;
    let rowIndex = 0;

    function addFileRow() {
        const container = document.getElementById('fileRows');
        const currentCount = container.querySelectorAll('.file-row').length;

        if (currentCount >= MAX_FILES) {
            alert('Maksimal 10 file per sesi import.');
            return;
        }

        const idx = rowIndex++;
        const defaultDate = new Date().toISOString().slice(0, 10);

        const row = document.createElement('div');
        row.className = 'file-row grid grid-cols-1 md:grid-cols-12 gap-2 items-end p-3 bg-gray-50 rounded-lg border border-gray-200';
        row.dataset.rowIndex = idx;
        row.innerHTML = `
            <div class="md:col-span-1 flex items-center justify-center pb-1">
                <span class="file-number inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-600 text-white text-xs font-bold"></span>
            </div>
            <div class="md:col-span-6">
                <label class="block text-xs font-medium text-gray-600 mb-1">File Excel <span class="text-red-500">*</span></label>
                <input type="file" name="files[${idx}][file]" accept=".xlsx,.xls" required
                       class="w-full text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                              file:text-xs file:bg-emerald-600 file:text-white file:font-medium
                              hover:file:bg-emerald-700 file:cursor-pointer file:transition
                              border border-gray-300 rounded-lg bg-white">
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Laporan <span class="text-red-500">*</span></label>
                <input type="date" name="files[${idx}][tanggal]" value="${defaultDate}" required
                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
            </div>
            <div class="md:col-span-1 flex justify-center">
                <button type="button" onclick="removeFileRow(this)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                        title="Hapus baris">
                    <iconify-icon icon="mdi:delete" class="text-base"></iconify-icon>
                </button>
            </div>
        `;
        container.appendChild(row);
        refreshRowNumbers();
    }

    function removeFileRow(btn) {
        const row = btn.closest('.file-row');
        const container = document.getElementById('fileRows');
        // Minimal harus ada 1 baris
        if (container.querySelectorAll('.file-row').length <= 1) {
            alert('Minimal harus ada 1 file.');
            return;
        }
        row.remove();
        refreshRowNumbers();
    }

    function refreshRowNumbers() {
        const rows = document.querySelectorAll('#fileRows .file-row');
        rows.forEach((r, i) => {
            r.querySelector('.file-number').textContent = i + 1;
        });
        const counter = document.getElementById('fileCounter');
        counter.textContent = `(${rows.length} / ${MAX_FILES})`;

        const addBtn = document.getElementById('addFileBtn');
        addBtn.disabled = rows.length >= MAX_FILES;
        addBtn.classList.toggle('opacity-50', rows.length >= MAX_FILES);
        addBtn.classList.toggle('cursor-not-allowed', rows.length >= MAX_FILES);
    }

    // Submit handler: disable button + progress
    document.getElementById('importForm').addEventListener('submit', function (e) {
        const rows = document.querySelectorAll('#fileRows .file-row');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Minimal upload 1 file.');
            return;
        }
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('submitText');
        btn.disabled = true;
        text.textContent = `Memproses ${rows.length} file...`;
    });

    // Init: tambahkan 1 baris pertama
    document.addEventListener('DOMContentLoaded', function () {
        addFileRow();
    });
</script>
@endsection