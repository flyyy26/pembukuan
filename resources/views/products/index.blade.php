@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:package-variant-closed" class="text-indigo-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Produk Internal</h2>
                <p class="text-sm text-gray-500">Daftar produk beserta HPP</p>
            </div>
        </div>

        <button type="button"
                onclick="openProductModal('create')"
                class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Produk
        </button>
    </div>

    {{-- ============ TABEL PRODUK (Container untuk AJAX update) ============ --}}
    <div id="productsTableContainer">
        @include('products.partials.table', ['products' => $products])
    </div>
</div>

{{-- ============ MODAL CONTAINER ============ --}}
<div id="productModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="modal-backdrop absolute inset-0 bg-black/50" onclick="closeProductModal()"></div>

    {{-- Modal Content --}}
    <div class="modal-content relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <iconify-icon id="productModalIcon" icon="mdi:plus" class="text-indigo-600 text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 id="productModalTitle" class="font-bold text-gray-800">Tambah Produk</h3>
                    <p id="productModalSubtitle" class="text-xs text-gray-500">Daftarkan produk baru beserta HPP</p>
                </div>
            </div>
            <button type="button" onclick="closeProductModal()"
                    class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon>
            </button>
        </div>

        {{-- Form --}}
        <form id="productForm" onsubmit="submitProductForm(event)" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="productId" name="product_id">

            <div class="p-6 space-y-5">

                {{-- Error Container --}}
                <div id="productFormErrors" class="hidden bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm space-y-0.5"></ul>
                </div>

                {{-- Gambar --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Gambar Produk <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <img id="productImagePreview"
                                 src=""
                                 class="w-20 h-20 object-cover rounded-lg border-2 border-gray-200 hidden">
                            <div id="productImagePlaceholder"
                                 class="w-20 h-20 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                <iconify-icon icon="mdi:image-plus" class="text-gray-400 text-2xl"></iconify-icon>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file"
                                   id="productGambar"
                                   name="gambar"
                                   accept="image/*"
                                   class="w-full text-xs text-gray-600
                                          file:mr-3 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-indigo-500 file:text-white
                                          hover:file:bg-indigo-600
                                          file:cursor-pointer file:transition">
                            <div class="text-xs text-gray-500 mt-2">
                                Format: JPG, PNG, WEBP. Maks 2 MB.
                                <span id="productImageHint" class="text-yellow-600 hidden">
                                    Kosongkan kalau tidak mau ganti gambar.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- SKU Internal --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        SKU Internal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:tag-outline" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text"
                               id="productSku"
                               name="sku_internal"
                               placeholder="cth: ROMPI-NAVY-L"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm font-mono
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               required>
                    </div>
                </div>

                {{-- Nama Produk --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:package-variant" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text"
                               id="productNama"
                               name="nama_produk"
                               placeholder="cth: Rompi Parasut"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               required>
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- HPP --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        HPP (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm font-medium">Rp</span>
                        </div>
                        <input type="number"
                               id="productHpp"
                               name="hpp"
                               placeholder="cth: 45000"
                               min="0"
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               required>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Harga pokok pembelian dari supplier (per unit).</div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <span class="text-red-500">*</span> Wajib diisi
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeProductModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-medium">
                        <iconify-icon icon="mdi:close" class="text-base"></iconify-icon>
                        Batal
                    </button>
                    <button type="submit" id="productSubmitBtn"
                            class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
                        <iconify-icon icon="mdi:content-save" class="text-base"></iconify-icon>
                        <span id="productSubmitBtnText">Simpan Produk</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============ ANIMASI & SCRIPT ============ --}}
<style>
    .modal-backdrop {
        opacity: 0;
        animation: fadeIn 0.2s ease-out forwards;
    }
    .modal-content {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
        animation: slideUpScale 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUpScale {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal-closing .modal-backdrop { animation: fadeOut 0.2s ease-in forwards; }
    .modal-closing .modal-content {
        animation: slideDownScale 0.2s ease-in forwards;
    }
    @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
    @keyframes slideDownScale {
        from { opacity: 1; transform: translateY(0) scale(1); }
        to   { opacity: 0; transform: translateY(10px) scale(0.98); }
    }
    @keyframes slide-in {
        from { opacity: 0; transform: translateX(20px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .animate-slide-in { animation: slide-in 0.3s ease-out; }

    tr.removing {
        transition: opacity 0.3s, transform 0.3s;
        opacity: 0;
        transform: translateX(-20px);
    }
</style>

<script>
const PRODUCT_CSRF = document.querySelector('meta[name="csrf-token"]').content;
const PRODUCT_DATA = @json($products->items());

// ==================== OPEN MODAL ====================
function openProductModal(mode, productId = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const errors = document.getElementById('productFormErrors');
    const imagePreview = document.getElementById('productImagePreview');
    const imagePlaceholder = document.getElementById('productImagePlaceholder');
    const imageHint = document.getElementById('productImageHint');
    const fileInput = document.getElementById('productGambar');

    form.reset();
    errors.classList.add('hidden');
    fileInput.value = '';

    if (mode === 'create') {
        document.getElementById('productModalTitle').textContent = 'Tambah Produk';
        document.getElementById('productModalSubtitle').textContent = 'Daftarkan produk baru beserta HPP';
        document.getElementById('productModalIcon').setAttribute('icon', 'mdi:plus');
        document.getElementById('productId').value = '';
        document.getElementById('productSubmitBtnText').textContent = 'Simpan Produk';

        imagePreview.classList.add('hidden');
        imagePlaceholder.classList.remove('hidden');
        imageHint.classList.add('hidden');

        form.dataset.mode = 'create';
        form.dataset.productId = '';
    } else {
        const product = PRODUCT_DATA.find(p => p.id === productId);
        if (!product) return;

        document.getElementById('productModalTitle').textContent = 'Edit Produk';
        document.getElementById('productModalSubtitle').textContent = 'Ubah informasi produk';
        document.getElementById('productModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('productId').value = product.id;
        document.getElementById('productSku').value = product.sku_internal || '';
        document.getElementById('productNama').value = product.nama_produk || '';
        document.getElementById('productHpp').value = product.hpp || '';
        document.getElementById('productSubmitBtnText').textContent = 'Simpan Perubahan';

        // Preview gambar
        if (product.gambar) {
            imagePreview.src = `/storage/${product.gambar}`;
            imagePreview.classList.remove('hidden');
            imagePlaceholder.classList.add('hidden');
            imageHint.classList.remove('hidden');
        } else {
            imagePreview.classList.add('hidden');
            imagePlaceholder.classList.remove('hidden');
            imageHint.classList.add('hidden');
        }

        form.dataset.mode = 'edit';
        form.dataset.productId = product.id;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.classList.remove('modal-closing');

    document.body.style.overflow = 'hidden';
}

// ==================== CLOSE MODAL ====================
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

// ==================== PREVIEW GAMBAR ====================
document.getElementById('productGambar').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (ev) {
        const preview = document.getElementById('productImagePreview');
        const placeholder = document.getElementById('productImagePlaceholder');
        preview.src = ev.target.result;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});

// ==================== SUBMIT FORM (AJAX) ====================
async function submitProductForm(event) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = document.getElementById('productSubmitBtn');
    const submitText = document.getElementById('productSubmitBtnText');
    const errors = document.getElementById('productFormErrors');
    const originalText = submitText.textContent;

    submitBtn.disabled = true;
    submitText.textContent = 'Menyimpan...';

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
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            errors.classList.remove('hidden');
            const ul = errors.querySelector('ul');
            ul.innerHTML = '';

            if (data.errors) {
                Object.values(data.errors).flat().forEach(msg => {
                    ul.innerHTML += `<li>${msg}</li>`;
                });
            } else if (data.message) {
                ul.innerHTML = `<li>${data.message}</li>`;
            } else {
                ul.innerHTML = '<li>Terjadi kesalahan.</li>';
            }

            submitBtn.disabled = false;
            submitText.textContent = originalText;
            return;
        }

        closeProductModal();
        showProductNotification(data.message, 'success');
        await refreshProductTable();

    } catch (err) {
        errors.classList.remove('hidden');
        errors.querySelector('ul').innerHTML = `<li>Gagal menyimpan: ${err.message}</li>`;
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
}

// ==================== DELETE PRODUK ====================
async function deleteProduct(productId, namaProduk) {
    if (!confirm(`Yakin hapus produk "${namaProduk}"?`)) return;

    try {
        const response = await fetch(`/products/${productId}/ajax`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': PRODUCT_CSRF,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            showProductNotification(data.message || 'Gagal menghapus.', 'error');
            return;
        }

        showProductNotification(data.message, 'success');

        const row = document.getElementById(`product-row-${productId}`);
        if (row) {
            row.classList.add('removing');
            setTimeout(() => refreshProductTable(), 300);
        } else {
            refreshProductTable();
        }

    } catch (err) {
        showProductNotification('Gagal menghapus: ' + err.message, 'error');
    }
}

// ==================== REFRESH TABLE ====================
async function refreshProductTable() {
    try {
        const response = await fetch("{{ route('products.table.html') }}", {
            headers: { 'Accept': 'text/html' },
        });
        const html = await response.text();
        document.getElementById('productsTableContainer').innerHTML = html;
    } catch (err) {
        console.error('Gagal refresh tabel:', err);
    }
}

// ==================== NOTIFICATION ====================
function showProductNotification(message, type = 'success') {
    const color = type === 'success'
        ? 'bg-green-50 border-green-200 text-green-800'
        : 'bg-red-50 border-red-200 text-red-800';
    const icon = type === 'success' ? 'mdi:check-circle' : 'mdi:alert-circle';

    const notif = document.createElement('div');
    notif.className = `fixed top-4 right-4 z-[100] ${color} border px-4 py-3 rounded-lg shadow-lg flex items-start gap-2 max-w-md animate-slide-in`;
    notif.innerHTML = `
        <iconify-icon icon="${icon}" class="text-lg mt-0.5"></iconify-icon>
        <div class="text-sm">${message}</div>
    `;

    document.body.appendChild(notif);

    setTimeout(() => {
        notif.style.transition = 'opacity 0.3s, transform 0.3s';
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(20px)';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

// ==================== ESC KEY ====================
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeProductModal();
});
</script>
@endsection