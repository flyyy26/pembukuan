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

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <form action="{{ route('brilink.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Toko BRILink</label>
                <select name="store_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">-- Pilih Toko --</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel BRILink</label>
                <input type="file" name="file" accept=".xlsx,.xls" required
                       class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-xs file:bg-emerald-600 file:text-white">
            </div>
            <button class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700">
                Import File
            </button>
        </form>
    </div>

    {{-- Riwayat import --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Riwayat Import BRILink</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">File</th>
                    <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Toko</th>
                    <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Periode</th>
                    <th class="text-center py-3 px-4 text-xs text-gray-500 uppercase">Baris</th>
                    <th class="text-center py-3 px-4 text-xs text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($imports as $imp)
                <tr>
                    <td class="py-3 px-4 text-xs">{{ $imp->file_name }}</td>
                    <td class="py-3 px-4">{{ $imp->store?->nama_toko ?? '-' }}</td>
                    <td class="py-3 px-4 text-xs">
                        @if($imp->periode_start)
                            {{ $imp->periode_start->format('d/m/Y') }} – {{ $imp->periode_end?->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">{{ $imp->total_rows }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block px-2 py-1 rounded-full text-xs
                            {{ $imp->status === 'imported' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-700' }}">
                            {{ $imp->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-12 text-center text-gray-400">Belum ada riwayat import</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($imports->hasPages())
            <div class="px-5 py-4 border-t">{{ $imports->links() }}</div>
        @endif
    </div>
</div>
@endsection