@extends('layouts.app')

@section('title', $store->exists ? 'Edit Toko' : 'Tambah Toko')

@section('content')
<div class="w-full max-w-3xl">

    {{-- ============ HEADER ============ --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('stores.index') }}"
           class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition">
            <iconify-icon icon="mdi:arrow-left" class="text-gray-600 text-xl"></iconify-icon>
        </a>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                <iconify-icon icon="{{ $store->exists ? 'mdi:pencil' : 'mdi:store-plus' }}"
                              class="text-emerald-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $store->exists ? 'Edit Toko' : 'Tambah Toko' }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $store->exists ? 'Ubah informasi toko' : 'Daftarkan toko baru di marketplace' }}
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

        <form action="{{ $store->exists ? route('stores.update', $store) : route('stores.store') }}"
              method="POST">
            @csrf
            @if($store->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-5">

                {{-- ============ MARKETPLACE ============ --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Marketplace <span class="text-red-500">*</span>
                    </label>

                    @php
                        $marketplaces = [
                            'shopee'    => ['label' => 'Shopee',       'icon' => 'mdi:shopping',          'color' => 'orange'],
                            'tiktok'    => ['label' => 'TikTok Shop',  'icon' => 'mdi:music-note',        'color' => 'gray'],
                            'tokopedia' => ['label' => 'Tokopedia',    'icon' => 'mdi:storefront',        'color' => 'green'],
                            'lazada'    => ['label' => 'Lazada',       'icon' => 'mdi:shopping-outline',  'color' => 'red'],
                        ];
                        $currentMarket = old('marketplace', $store->marketplace);
                    @endphp

                    {{-- Radio Card Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($marketplaces as $val => $mp)
                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="marketplace"
                                       value="{{ $val }}"
                                       class="peer sr-only"
                                       {{ $currentMarket === $val ? 'checked' : '' }}
                                       required>
                                <div class="border-2 border-gray-200 rounded-xl p-4 text-center transition
                                            hover:border-gray-300 hover:bg-gray-50
                                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50
                                            peer-checked:shadow-sm">
                                    <iconify-icon icon="{{ $mp['icon'] }}"
                                                  class="text-2xl text-gray-400 mb-2 block
                                                         peer-checked:text-emerald-600"></iconify-icon>
                                    <div class="text-xs font-medium text-gray-700">{{ $mp['label'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- ============ DIVIDER ============ --}}
                <div class="border-t border-gray-100"></div>

                {{-- ============ NAMA TOKO ============ --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Toko <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:store-outline" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text"
                               name="nama_toko"
                               value="{{ old('nama_toko', $store->nama_toko) }}"
                               placeholder="cth: HM Barokah Shopee"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               required>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Nama toko seperti yang muncul di marketplace.</div>
                </div>

                {{-- ============ SELLER ID ============ --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Seller ID <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:account-circle-outline" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text"
                               name="seller_id"
                               value="{{ old('seller_id', $store->seller_id) }}"
                               placeholder="cth: hmbarokah"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm font-mono
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Username / ID seller di marketplace (untuk referensi).
                    </div>
                </div>

            </div>

            {{-- ============ FOOTER BUTTONS ============ --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <span class="text-red-500">*</span> Wajib diisi
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('stores.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-medium">
                        <iconify-icon icon="mdi:close" class="text-base"></iconify-icon>
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:content-save" class="text-base"></iconify-icon>
                        {{ $store->exists ? 'Simpan Perubahan' : 'Simpan Toko' }}
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection