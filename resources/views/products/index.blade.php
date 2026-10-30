@extends('layouts.app')

@section('title', 'Kategori & Prefix')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:tag-multiple" class="text-indigo-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Kategori & Prefix</h2>
                <p class="text-xs sm:text-sm text-gray-500">Setiap kategori mewakili semua SKU varian dengan prefix yang sama</p>
            </div>
        </div>

        <button type="button"
                onclick="openProductModal('create')"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Kategori
        </button>
    </div>

    {{-- ============ INFO BOX ============ --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 flex items-start gap-3">
        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <iconify-icon icon="mdi:information-outline" class="text-blue-600 text-lg"></iconify-icon>
        </div>
        <div class="text-sm text-blue-900">
            <div class="font-semibold mb-1">Cara Kerja Prefix</div>
            <div class="text-xs leading-relaxed">
                Semua SKU dari laporan <strong>Shopee</strong> & <strong>TikTok</strong> yang diawali prefix akan otomatis
                memakai HPP kategori ini. Contoh: prefix <code class="bg-white px-1.5 py-0.5 rounded border border-blue-200 font-mono text-[11px]">TR</code>
                akan menangkap <code class="bg-white px-1 py-0.5 rounded font-mono text-[10px]">TR-NAVY-L</code>,
                <code class="bg-white px-1 py-0.5 rounded font-mono text-[10px]">TR-BLACK-M</code>, dst.
            </div>
        </div>
    </div>

    {{-- ============ TABEL KATEGORI ============ --}}
    <div id="productsTableContainer">
        @include('products.partials.table', ['products' => $products])
    </div>
</div>

{{-- ============ MODAL CONTAINER ============ --}}
<div id="productModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-black/50" onclick="closeProductModal()"></div>

    <div class="modal-content relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto flex flex-col">

        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <iconify-icon id="productModalIcon" icon="mdi:plus" class="text-indigo-600 text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 id="productModalTitle" class="font-bold text-gray-800">Tambah Kategori</h3>
                    <p id="productModalSubtitle" class="text-xs text-gray-500">Daftarkan kategori baru beserta HPP</p>
                </div>
            </div>
            <button type="button" onclick="closeProductModal()"
                    class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon>
            </button>
        </div>

        <form id="productForm" onsubmit="submitProductForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="productId" name="product_id">

            <div class="p-5 sm:p-6 space-y-5">

                <div id="productFormErrors" class="hidden bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm space-y-0.5"></ul>
                </div>

                {{-- Nama Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:shape-outline" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text" id="productNama" name="nama_produk" placeholder="cth: Trening Parasut" required
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                {{-- Prefix SKU --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prefix SKU <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:tag-outline" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text" id="productSku" name="sku_prefix" placeholder="cth: TR" required
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               style="text-transform: uppercase;">
                    </div>
                    <div class="text-[10px] text-gray-500 mt-1">
                        Awalan SKU di laporan marketplace. <strong>Harus unik</strong> antar kategori.
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- HPP & Stok --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">HPP (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm font-medium">Rp</span>
                            </div>
                            <input type="number" id="productHpp" name="hpp" placeholder="cth: 50000" min="0" required
                                   class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div class="text-[10px] text-gray-500 mt-1">Harga pokok beli dari supplier.</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                        <input type="number" id="productStok" name="stok" min="0" required value="0"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <div class="text-[10px] text-gray-500 mt-1">Jumlah stok fisik saat ini.</div>
                    </div>
                </div>

            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center flex-shrink-0 mt-auto">
                <div class="text-xs text-gray-500 hidden sm:block">
                    <span class="text-red-500">*</span> Wajib diisi
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button type="button" onclick="closeProductModal()"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit" id="productSubmitBtn"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:content-save" class="text-base"></iconify-icon>
                        <span id="productSubmitBtnText">Simpan</span>
                    </button>
                </div>
            </div>
        </form>
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
const PRODUCT_CSRF = document.querySelector('meta[name="csrf-token"]').content;
let PRODUCT_DATA = @json($products->items());

function openProductModal(mode, productId = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const errors = document.getElementById('productFormErrors');
    const submitBtn = document.getElementById('productSubmitBtn');

    form.reset();
    errors.classList.add('hidden');
    submitBtn.disabled = false;

    document.getElementById('productSku').value = '';
    document.getElementById('productNama').value = '';
    document.getElementById('productHpp').value = '';
    document.getElementById('productStok').value = '0';

    if (mode === 'create') {
        document.getElementById('productModalTitle').textContent = 'Tambah Kategori';
        document.getElementById('productModalIcon').setAttribute('icon', 'mdi:plus');
        document.getElementById('productId').value = '';
        document.getElementById('productSubmitBtnText').textContent = 'Simpan Kategori';
        form.dataset.mode = 'create';
        form.dataset.productId = '';
    } else {
        const product = PRODUCT_DATA.find(p => p.id === productId);
        if (!product) {
            alert('Data kategori tidak ditemukan, silakan refresh halaman.');
            return;
        }

        document.getElementById('productModalTitle').textContent = 'Edit Kategori';
        document.getElementById('productModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('productId').value = product.id;
        document.getElementById('productSku').value = product.sku_prefix || '';
        document.getElementById('productNama').value = product.nama_produk || '';
        document.getElementById('productHpp').value = product.hpp || '';
        document.getElementById('productStok').value = product.stok ?? 0;
        document.getElementById('productSubmitBtnText').textContent = 'Simpan Perubahan';

        form.dataset.mode = 'edit';
        form.dataset.productId = product.id;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.classList.remove('modal-closing');
    document.body.style.overflow = 'hidden';
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    if (!modal || modal.classList.contains('hidden')) return;

    modal.classList.add('modal-closing');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-closing');
        document.body.style.overflow = '';
    }, 200);
}

async function submitProductForm(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('productSubmitBtn');
    const submitText = document.getElementById('productSubmitBtnText');
    const errors = document.getElementById('productFormErrors');
    const originalText = submitText.textContent;

    submitBtn.disabled = true;
    submitText.textContent = 'Menyimpan...';
    errors.classList.add('hidden');

    const formData = new FormData(form);

    const mode = form.dataset.mode;
    const productId = form.dataset.productId;

    let url;
    if (mode === 'create') {
        url = "{{ route('products.store.ajax') }}";
    } else {
        url = `/products/${productId}/ajax`;
        formData.append('_method', 'PUT');
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': PRODUCT_CSRF, 
                'Accept': 'application/json' 
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            errors.classList.remove('hidden');
            const ul = errors.querySelector('ul');
            ul.innerHTML = '';
            if (data.errors) {
                Object.values(data.errors).flat().forEach(msg => { ul.innerHTML += `<li>${msg}</li>`; });
            } else if (data.message) {
                ul.innerHTML = `<li>${data.message}</li>`;
            } else {
                ul.innerHTML = '<li>Terjadi kesalahan saat menyimpan.</li>';
            }
            
            submitBtn.disabled = false;
            submitText.textContent = originalText;
            return;
        }

        submitText.textContent = 'Berhasil!';
        showProductNotification(data.message, 'success');
        
        closeProductModal();
        await refreshProductTable(); 

    } catch (err) {
        errors.classList.remove('hidden');
        errors.querySelector('ul').innerHTML = `<li>Gagal menyimpan: ${err.message}</li>`;
        
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
}

async function deleteProduct(productId, namaProduk) {
    if (!confirm(`Yakin hapus kategori "${namaProduk}"?\n\nSemua SKU marketplace yang match prefix ini akan jadi "orphan" (tidak terhitung HPP & profit).`)) return;
    
    try {
        const response = await fetch(`/products/${productId}/ajax`, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': PRODUCT_CSRF, 
                'Accept': 'application/json' 
            },
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            showProductNotification(data.message || 'Gagal menghapus.', 'error');
            return;
        }
        
        showProductNotification(data.message, 'success');
        await refreshProductTable();
        
    } catch (err) {
        showProductNotification('Gagal menghapus: ' + err.message, 'error');
    }
}

async function refreshProductTable() {
    window.location.reload(); 
}

function showProductNotification(message, type = 'success') {
    const color = type === 'success' 
        ? 'bg-green-50 border-green-200 text-green-800' 
        : 'bg-red-50 border-red-200 text-red-800';
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
    if (e.key === 'Escape') closeProductModal(); 
});
</script>
@endsection