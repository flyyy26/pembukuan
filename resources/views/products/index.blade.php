@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:package-variant-closed" class="text-indigo-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Produk Internal</h2>
                <p class="text-xs sm:text-sm text-gray-500">Daftar produk beserta HPP</p>
            </div>
        </div>

        <button type="button"
                onclick="openProductModal('create')"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium shadow-sm">
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
    <div class="modal-content relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto flex flex-col">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
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
        <form id="productForm" onsubmit="submitProductForm(event)" enctype="multipart/form-data" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="productId" name="product_id">

            <div class="p-5 sm:p-6 space-y-5">

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
                            <img id="productImagePreview" src="" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg border-2 border-gray-200 hidden">
                            <div id="productImagePlaceholder" class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                <iconify-icon icon="mdi:image-plus" class="text-gray-400 text-xl sm:text-2xl"></iconify-icon>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="productGambar" name="gambar" accept="image/*"
                                   class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-500 file:text-white hover:file:bg-indigo-600 file:cursor-pointer file:transition">
                            <div class="text-[10px] sm:text-xs text-gray-500 mt-2">
                                Format: JPG, PNG, WEBP. Maks 2 MB.
                                <span id="productImageHint" class="text-yellow-600 hidden">Kosongkan kalau tidak mau ganti gambar.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- SKU Internal --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU Internal <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:tag-outline" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text" id="productSku" name="sku_internal" placeholder="cth: ROMPI-NAVY-L" required
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                {{-- Nama Produk --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:package-variant" class="text-gray-400 text-base"></iconify-icon>
                        </div>
                        <input type="text" id="productNama" name="nama_produk" placeholder="cth: Rompi Parasut" required
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- HPP & Stok (Grid 2 kolom) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">HPP (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm font-medium">Rp</span>
                            </div>
                            <input type="number" id="productHpp" name="hpp" placeholder="cth: 45000" min="0" required
                                   class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div class="text-[10px] text-gray-500 mt-1">Harga pokok beli dari supplier.</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                        <input type="number" id="productStok" name="stok" min="0" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <div class="text-[10px] text-gray-500 mt-1">Jumlah stok fisik saat ini.</div>
                    </div>
                </div>

                {{-- Checkbox is_active --}}
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="productIsActive" name="is_active" value="1" checked
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="productIsActive" class="text-sm text-gray-700 select-none cursor-pointer">
                        Produk aktif (dihitung dalam Zakat Barang)
                    </label>
                </div>

            </div>

            {{-- Footer --}}
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

{{-- ============ ANIMASI & SCRIPT ============ --}}
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
// Gunakan let agar bisa di-reassign jika perlu (meski kita pakai reload)
let PRODUCT_DATA = @json($products->items());

// ==================== OPEN MODAL ====================
function openProductModal(mode, productId = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const errors = document.getElementById('productFormErrors');
    const imagePreview = document.getElementById('productImagePreview');
    const imagePlaceholder = document.getElementById('productImagePlaceholder');
    const imageHint = document.getElementById('productImageHint');
    const fileInput = document.getElementById('productGambar');
    const submitBtn = document.getElementById('productSubmitBtn'); // ✅ Ambil elemen tombol submit

    // Reset Form
    form.reset();
    errors.classList.add('hidden');
    fileInput.value = '';
    
    // ✅ Pastikan tombol submit selalu aktif saat modal dibuka
    submitBtn.disabled = false; 

    // Reset semua field ke nilai default
    document.getElementById('productSku').value = '';
    document.getElementById('productNama').value = '';
    document.getElementById('productStok').value = '0';
    document.getElementById('productHpp').value = '';
    document.getElementById('productIsActive').checked = true;

    if (mode === 'create') {
        document.getElementById('productModalTitle').textContent = 'Tambah Produk';
        document.getElementById('productModalIcon').setAttribute('icon', 'mdi:plus');
        document.getElementById('productId').value = '';
        document.getElementById('productSubmitBtnText').textContent = 'Simpan Produk';

        imagePreview.classList.add('hidden');
        imagePreview.src = '';
        imagePlaceholder.classList.remove('hidden');
        imageHint.classList.add('hidden');

        form.dataset.mode = 'create';
        form.dataset.productId = '';
    } else {
        // Mode Edit
        const product = PRODUCT_DATA.find(p => p.id === productId);
        if (!product) {
            alert('Data produk tidak ditemukan, silakan refresh halaman.');
            return;
        }

        document.getElementById('productModalTitle').textContent = 'Edit Produk';
        document.getElementById('productModalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('productId').value = product.id;
        document.getElementById('productSku').value = product.sku_internal || '';
        document.getElementById('productNama').value = product.nama_produk || '';
        document.getElementById('productStok').value = product.stok ?? 0;
        document.getElementById('productHpp').value = product.hpp || '';
        document.getElementById('productIsActive').checked = product.is_active !== false;
        document.getElementById('productSubmitBtnText').textContent = 'Simpan Perubahan';

        if (product.gambar) {
            imagePreview.src = `/storage/${product.gambar}`;
            imagePreview.classList.remove('hidden');
            imagePlaceholder.classList.add('hidden');
            imageHint.classList.remove('hidden');
        } else {
            imagePreview.classList.add('hidden');
            imagePreview.src = '';
            imagePlaceholder.classList.remove('hidden');
            imageHint.classList.add('hidden');
        }

        form.dataset.mode = 'edit';
        form.dataset.productId = product.id;
    }

    // Buka modal dengan animasi
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

// ==================== SUBMIT FORM ====================
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
    
    // Pastikan checkbox is_active mengirim nilai 1 atau 0
    if (!formData.has('is_active')) {
        formData.set('is_active', '0');
    } else {
        formData.set('is_active', '1');
    }

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
            
            // ✅ Kembalikan tombol ke keadaan semula jika error
            submitBtn.disabled = false;
            submitText.textContent = originalText;
            return;
        }

        // Jika Sukses
        submitText.textContent = 'Berhasil!';
        showProductNotification(data.message, 'success');
        
        // Tutup modal dan reload halaman agar data tabel & JSON sinkron
        closeProductModal();
        await refreshProductTable(); 

    } catch (err) {
        errors.classList.remove('hidden');
        errors.querySelector('ul').innerHTML = `<li>Gagal menyimpan: ${err.message}</li>`;
        
        // ✅ Kembalikan tombol ke keadaan semula jika error
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
}

// ==================== DELETE PRODUCT ====================
async function deleteProduct(productId, namaProduk) {
    if (!confirm(`Yakin hapus produk "${namaProduk}"?`)) return;
    
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

// ==================== REFRESH TABLE ====================
async function refreshProductTable() {
    // ✅ Menggunakan reload halaman untuk memastikan HTML dan JSON selalu sinkron.
    // Ini adalah cara paling aman untuk mencegah bug "data tidak ditemukan" saat Edit.
    window.location.reload(); 
}

// ==================== NOTIFICATION ====================
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

// ==================== ESC KEY ====================
document.addEventListener('keydown', function (e) { 
    if (e.key === 'Escape') closeProductModal(); 
});
</script>
@endsection