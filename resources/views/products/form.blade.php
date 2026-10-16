@extends('layouts.app')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="w-full max-w-3xl">

    {{-- ============ HEADER ============ --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('products.index') }}"
           class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition">
            <iconify-icon icon="mdi:arrow-left" class="text-gray-600 text-xl"></iconify-icon>
        </a>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                <iconify-icon icon="{{ $product->exists ? 'mdi:pencil' : 'mdi:plus' }}"
                              class="text-indigo-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $product->exists ? 'Ubah informasi produk' : 'Daftarkan produk baru beserta HPP' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ============ FORM CARD ============ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Error Validasi --}}
        @if($errors->any())
            <div class="bg-red-50 border-b border-red-200 px-6 py-4">
                <div class="flex items-start gap-2">
                    <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5"></iconify-icon>
                    <div>
                        <div class="text-sm font-medium text-red-800 mb-1">Ada kesalahan:</div>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-5">

                {{-- ============ GAMBAR ============ --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Gambar Produk
                        <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>

                    <div class="flex items-start gap-4">
                        {{-- Preview --}}
                        <div class="flex-shrink-0">
                            @if($product->exists && $product->gambar)
                                <img src="{{ $product->gambar_url }}"
                                     class="w-24 h-24 object-cover rounded-lg border-2 border-gray-200">
                            @else
                                <div class="w-24 h-24 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                    <iconify-icon icon="mdi:image-plus" class="text-gray-400 text-3xl"></iconify-icon>
                                </div>
                            @endif
                        </div>

                        {{-- Input --}}
                        <div class="flex-1">
                            <input type="file"
                                   name="gambar"
                                   accept="image/*"
                                   class="w-full text-xs text-gray-600
                                          file:mr-3 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-indigo-500 file:text-white
                                          hover:file:bg-indigo-600
                                          file:cursor-pointer file:transition">
                            <div class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <iconify-icon icon="mdi:information-outline" class="text-xs"></iconify-icon>
                                Format: JPG, PNG, WEBP. Maks 2 MB.
                                @if($product->exists && $product->gambar)
                                    <br><span class="text-yellow-600">Kosongkan kalau tidak mau ganti gambar.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ DIVIDER ============ --}}
                <div class="border-t border-gray-100"></div>

                {{-- ============ SKU ============ --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        SKU Internal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:tag-outline" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text"
                               name="sku_internal"
                               value="{{ old('sku_internal', $product->sku_internal) }}"
                               placeholder="cth: ROMPI-NAVY-L"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm font-mono
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               required>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Kode unik produk kamu sendiri.</div>
                </div>

                {{-- ============ NAMA PRODUK ============ --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:package-variant" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text"
                               name="nama_produk"
                               value="{{ old('nama_produk', $product->nama_produk) }}"
                               placeholder="cth: Rompi Parasut"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               required>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Nama dasar produk (tanpa varian).</div>
                </div>

                {{-- ============ WARNA & UKURAN ============ --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warna</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <iconify-icon icon="mdi:palette" class="text-gray-400 text-base"></iconify-icon>
                            </div>
                            <input type="text"
                                   name="warna"
                                   value="{{ old('warna', $product->warna) }}"
                                   placeholder="cth: Navy"
                                   class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <iconify-icon icon="mdi:ruler" class="text-gray-400 text-base"></iconify-icon>
                            </div>
                            <input type="text"
                                   name="ukuran"
                                   value="{{ old('ukuran', $product->ukuran) }}"
                                   placeholder="cth: L"
                                   class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                {{-- ============ DIVIDER ============ --}}
                <div class="border-t border-gray-100"></div>

                {{-- ============ HPP ============ --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        HPP (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm font-medium">Rp</span>
                        </div>
                        <input type="number"
                               name="hpp"
                               value="{{ old('hpp', $product->hpp) }}"
                               placeholder="cth: 45000"
                               min="0"
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               required>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Harga pokok pembelian dari supplier (per unit).</div>
                </div>

            </div>

            {{-- ============ FOOTER BUTTONS ============ --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <span class="text-red-500">*</span> Wajib diisi
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-medium">
                        <iconify-icon icon="mdi:close" class="text-base"></iconify-icon>
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:content-save" class="text-base"></iconify-icon>
                        {{ $product->exists ? 'Simpan Perubahan' : 'Simpan Produk' }}
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection