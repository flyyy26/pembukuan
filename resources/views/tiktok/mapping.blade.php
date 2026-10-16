@extends('layouts.app')

@section('title', 'Mapping Produk TikTok')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:music-note" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Mapping Produk TikTok</h2>
                <p class="text-sm text-gray-500">{{ $store->nama_toko }}</p>
            </div>
        </div>

        <a href="{{ route('tiktok.import') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            <iconify-icon icon="mdi:arrow-left" class="text-lg"></iconify-icon>
            Kembali ke Import
        </a>
    </div>

    {{-- ============ STATISTIK ============ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:tag-multiple" class="text-gray-600 text-xl"></iconify-icon>
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-1">Total SKU Varian</div>
            <div class="text-2xl font-bold text-gray-800">{{ $totalAll }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:check-circle" class="text-green-600 text-xl"></iconify-icon>
                </div>
                <div class="text-xs text-gray-500">
                    {{ $totalAll > 0 ? round(($mappedAll / $totalAll) * 100, 1) : 0 }}%
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-1">Sudah Dimapping</div>
            <div class="text-2xl font-bold text-green-600">{{ $mappedAll }}</div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3">
                @php $pct = $totalAll > 0 ? ($mappedAll / $totalAll) * 100 : 0; @endphp
                <div class="bg-green-500 h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-xl"></iconify-icon>
                </div>
                <div class="text-xs text-gray-500">
                    {{ $totalAll > 0 ? round(($unmappedAll / $totalAll) * 100, 1) : 0 }}%
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-1">Belum Dimapping</div>
            <div class="text-2xl font-bold text-red-600">{{ $unmappedAll }}</div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3">
                @php $pct2 = $totalAll > 0 ? ($unmappedAll / $totalAll) * 100 : 0; @endphp
                <div class="bg-red-500 h-1.5 rounded-full transition-all" style="width: {{ $pct2 }}%"></div>
            </div>
        </div>
    </div>

    {{-- ============ SEARCH BAR ============ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('tiktok.mapping', $storeId) }}" class="flex gap-3 items-center">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                </div>
                <input type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari SKU TikTok, nama produk, atau variasi..."
                    class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm
                            focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition text-sm font-medium">
                <iconify-icon icon="mdi:magnify" class="text-lg"></iconify-icon>
                Cari
            </button>

            @if(!empty($search))
                <a href="{{ route('tiktok.mapping', $storeId) }}"
                class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                    <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon>
                    Reset
                </a>
            @endif
        </form>

        @if(!empty($search))
            <div class="mt-3 text-xs text-gray-500 flex items-center gap-1">
                <iconify-icon icon="mdi:information-outline" class="text-sm"></iconify-icon>
                Menampilkan hasil untuk: <strong class="text-gray-700">"{{ $search }}"</strong>
                — {{ $items->count() }} SKU ditemukan.
            </div>
        @endif
    </div>

    {{-- ============ TABEL MAPPING ============ --}}
    <div id="mappingTableContainer">
        @include('tiktok.partials.table', [
            'items' => $items,
            'products' => $products,
            'page' => $page,
            'perPage' => $perPage,
            'totalAll' => $totalAll,
            'storeId' => $storeId,
            'search' => $search,
        ])
    </div>
</div>

{{-- ============ MODAL PILIH PRODUK ============ --}}
<div id="productPickerModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-black/50" onclick="closeProductPicker()"></div>

    <div class="modal-content relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:link-variant" class="text-indigo-600 text-xl"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800">Pilih Produk Internal</h3>
                    <p id="pickerSkuLabel" class="text-xs text-gray-500 font-mono truncate"></p>
                </div>
            </div>
            <button type="button" onclick="closeProductPicker()"
                    class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition flex-shrink-0">
                <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon>
            </button>
        </div>

        <div class="px-6 py-3 border-b border-gray-100 flex-shrink-0 bg-gray-50">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                </div>
                <input type="text"
                       id="pickerSearch"
                       oninput="debounceSearch()"
                       placeholder="Cari SKU atau nama produk..."
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>

        <div id="pickerList" class="overflow-y-auto flex-1 p-3">
            <div class="text-center py-12 text-gray-400 text-sm">
                <iconify-icon icon="mdi:loading" class="text-3xl block mb-2 animate-spin"></iconify-icon>
                Memuat produk...
            </div>
        </div>

        <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between flex-shrink-0 bg-gray-50">
            <button type="button"
                    onclick="clearMapping()"
                    class="inline-flex items-center gap-2 text-red-600 hover:bg-red-50 px-3 py-2 rounded-lg text-xs font-medium transition">
                <iconify-icon icon="mdi:link-variant-off" class="text-base"></iconify-icon>
                Kosongkan Mapping
            </button>
            <button type="button"
                    onclick="closeProductPicker()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-medium">
                Batal
            </button>
        </div>
    </div>
</div>

<style>
    .modal-backdrop { opacity: 0; animation: fadeIn 0.2s ease-out forwards; }
    .modal-content { opacity: 0; transform: translateY(20px) scale(0.95); animation: slideUpScale 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUpScale { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .modal-closing .modal-backdrop { animation: fadeOut 0.2s ease-in forwards; }
    .modal-closing .modal-content { animation: slideDownScale 0.2s ease-in forwards; }
    @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
    @keyframes slideDownScale { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(10px) scale(0.98); } }
    @keyframes slide-in { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    .animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>

<script>
const MAPPING_CSRF = document.querySelector('meta[name="csrf-token"]').content;
const STORE_ID = {{ $storeId }};
let currentSku = '';
let currentPage = {{ $page }};
let currentSearch = @json($search ?? '');
let searchTimer = null;

function openProductPicker(sku) {
    currentSku = sku;
    document.getElementById('pickerSkuLabel').textContent = sku;
    document.getElementById('pickerSearch').value = '';
    const modal = document.getElementById('productPickerModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.classList.remove('modal-closing');
    document.body.style.overflow = 'hidden';
    loadProductsHtml('');
    setTimeout(() => document.getElementById('pickerSearch').focus(), 350);
}

function closeProductPicker() {
    const modal = document.getElementById('productPickerModal');
    if (!modal || modal.classList.contains('hidden')) return;
    modal.classList.add('modal-closing');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-closing');
        document.body.style.overflow = '';
    }, 200);
}

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        const q = document.getElementById('pickerSearch').value.trim();
        loadProductsHtml(q);
    }, 300);
}

async function loadProductsHtml(search = '') {
    const list = document.getElementById('pickerList');
    list.innerHTML = `<div class="text-center py-8 text-gray-400 text-sm"><iconify-icon icon="mdi:loading" class="text-3xl block mb-2 animate-spin"></iconify-icon>Memuat...</div>`;
    try {
        const url = `/tiktok/mapping/${STORE_ID}/products-html?search=${encodeURIComponent(search)}`;
        const response = await fetch(url, { headers: { 'Accept': 'text/html' } });
        const html = await response.text();
        list.innerHTML = html;
    } catch (err) {
        list.innerHTML = `<div class="text-center py-8 text-red-500 text-sm">Gagal memuat: ${err.message}</div>`;
    }
}

async function selectProductForMapping(productId) {
    try {
        const response = await fetch(`/tiktok/mapping/${STORE_ID}/ajax`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': MAPPING_CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                sku_varian: currentSku,
                product_id: productId,
                page: currentPage,
                search: currentSearch,
            }),
        });
        const data = await response.json();
        if (!response.ok) {
            showMappingNotification(data.message || 'Gagal menyimpan.', 'error');
            return;
        }
        showMappingNotification(data.message, 'success');
        closeProductPicker();
        await refreshMappingTable();
    } catch (err) {
        showMappingNotification('Gagal menyimpan: ' + err.message, 'error');
    }
}

async function clearMapping() {
    if (!confirm('Kosongkan mapping untuk SKU ini?')) return;
    await selectProductForMapping(null);
}

async function refreshMappingTable() {
    try {
        const url = `/tiktok/mapping/${STORE_ID}/table-html?page=${currentPage}&search=${encodeURIComponent(currentSearch)}`;
        const response = await fetch(url, { headers: { 'Accept': 'text/html' } });
        const html = await response.text();
        document.getElementById('mappingTableContainer').innerHTML = html;
    } catch (err) {
        console.error('Gagal refresh tabel:', err);
    }
}

function showMappingNotification(message, type = 'success') {
    const color = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
    const icon = type === 'success' ? 'mdi:check-circle' : 'mdi:alert-circle';
    const notif = document.createElement('div');
    notif.className = `fixed top-4 right-4 z-[100] ${color} border px-4 py-3 rounded-lg shadow-lg flex items-start gap-2 max-w-md animate-slide-in`;
    notif.innerHTML = `<iconify-icon icon="${icon}" class="text-lg mt-0.5"></iconify-icon><div class="text-sm">${message}</div>`;
    document.body.appendChild(notif);
    setTimeout(() => {
        notif.style.transition = 'opacity 0.3s, transform 0.3s';
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(20px)';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeProductPicker();
});
</script>
@endsection