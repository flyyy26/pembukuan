@extends('layouts.app')

@section('title', 'Toko')

@section('content')
<div class="w-full">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:store-outline" class="text-emerald-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Toko</h2>
                <p class="text-xs sm:text-sm text-gray-500">Daftar toko di marketplace</p>
            </div>
        </div>

        <button type="button"
                onclick="openModal('create')"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
            Tambah Toko
        </button>
    </div>

    {{-- ============ TABEL TOKO (Container untuk AJAX update) ============ --}}
    <div id="storesTableContainer">
        @include('stores.partials.table', ['stores' => $stores])
    </div>
</div>

{{-- ============ MODAL CONTAINER ============ --}}
<div id="storeModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="modal-backdrop absolute inset-0 bg-black/50" onclick="closeModal()"></div>

    {{-- Modal Content --}}
    <div class="modal-content relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto flex flex-col">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <iconify-icon id="modalIcon" icon="mdi:store-plus" class="text-emerald-600 text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 id="modalTitle" class="font-bold text-gray-800">Tambah Toko</h3>
                    <p id="modalSubtitle" class="text-xs text-gray-500">Daftarkan toko baru</p>
                </div>
            </div>
            <button type="button" onclick="closeModal()"
                    class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <iconify-icon icon="mdi:close" class="text-lg"></iconify-icon>
            </button>
        </div>

        {{-- Form --}}
        <form id="storeForm" onsubmit="submitStoreForm(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <input type="hidden" id="storeId" name="store_id">

            <div class="p-5 sm:p-6 space-y-5">

                {{-- Error Container --}}
                <div id="formErrors" class="hidden bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm space-y-0.5"></ul>
                </div>

                {{-- Marketplace --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Marketplace <span class="text-red-500">*</span>
                    </label>
                    @php
                        $marketplaces = [
                            'shopee'    => ['label' => 'Shopee',       'icon' => 'mdi:shopping'],
                            'tiktok'    => ['label' => 'TikTok Shop',  'icon' => 'mdi:music-note'],
                            'tokopedia' => ['label' => 'Tokopedia',    'icon' => 'mdi:storefront'],
                            'lazada'    => ['label' => 'Lazada',       'icon' => 'mdi:shopping-outline'],
                            'brilink'   => ['label' => 'BRILink',      'icon' => 'mdi:bank'],
                        ];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($marketplaces as $val => $mp)
                            <label class="cursor-pointer">
                                <input type="radio" name="marketplace" value="{{ $val }}" class="peer sr-only" required>
                                <div class="border-2 border-gray-200 rounded-lg p-3 text-center transition
                                            hover:border-gray-300 hover:bg-gray-50
                                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                    <iconify-icon icon="{{ $mp['icon'] }}" class="text-xl text-gray-400 m-auto text-center w-max mb-1 block"></iconify-icon>
                                    <div class="text-[10px] sm:text-xs font-medium text-gray-700">{{ $mp['label'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Nama Toko --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Toko <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="namaToko" name="nama_toko" required
                           placeholder="cth: HM Barokah Shopee"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>

                {{-- Seller ID --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Seller ID <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" id="sellerId" name="seller_id"
                           placeholder="cth: hmbarokah"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono
                                  focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2 flex-shrink-0 mt-auto">
                <button type="button" onclick="closeModal()"
                        class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-medium">
                    Batal
                </button>
                <button type="submit" id="submitBtn"
                        class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
                    <iconify-icon icon="mdi:content-save" class="text-base"></iconify-icon>
                    <span id="submitBtnText">Simpan Toko</span>
                </button>
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
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

// ==================== OPEN MODAL ====================
function openModal(mode, storeId = null, btn = null) {
    const modal = document.getElementById('storeModal');
    const form = document.getElementById('storeForm');
    const errors = document.getElementById('formErrors');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitBtnText');

    form.reset();
    errors.classList.add('hidden');

    submitBtn.disabled = false;
    submitText.textContent = mode === 'create' ? 'Simpan Toko' : 'Simpan Perubahan';
    document.querySelectorAll('input[name="marketplace"]').forEach(r => r.checked = false);

    if (mode === 'create') {
        document.getElementById('modalTitle').textContent = 'Tambah Toko';
        document.getElementById('modalSubtitle').textContent = 'Daftarkan toko baru';
        document.getElementById('modalIcon').setAttribute('icon', 'mdi:store-plus');
        document.getElementById('storeId').value = '';
        document.getElementById('submitBtnText').textContent = 'Simpan Toko';
        form.dataset.mode = 'create';
        form.dataset.storeId = '';
    } else {
        if (!btn) return; 

        const row = btn.closest('tr, div[data-id]');
        const data = {
            id: row.getAttribute('data-id'),
            nama_toko: row.getAttribute('data-nama'),
            marketplace: row.getAttribute('data-marketplace'),
            seller_id: row.getAttribute('data-seller')
        };

        document.getElementById('modalTitle').textContent = 'Edit Toko';
        document.getElementById('modalSubtitle').textContent = 'Ubah informasi toko';
        document.getElementById('modalIcon').setAttribute('icon', 'mdi:pencil');
        document.getElementById('storeId').value = data.id;
        document.getElementById('namaToko').value = data.nama_toko;
        document.getElementById('sellerId').value = data.seller_id || '';
        document.getElementById('submitBtnText').textContent = 'Simpan Perubahan';

        const radio = document.querySelector(`input[name="marketplace"][value="${data.marketplace}"]`);
        if (radio) radio.checked = true;

        form.dataset.mode = 'edit';
        form.dataset.storeId = data.id;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.classList.remove('modal-closing');

    document.body.style.overflow = 'hidden';
}

// ==================== CLOSE MODAL ====================
function closeModal() {
    const modal = document.getElementById('storeModal');
    if (!modal || modal.classList.contains('hidden')) return;

    modal.classList.add('modal-closing');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-closing');
        document.body.style.overflow = '';
    }, 200);
}

// ==================== SUBMIT FORM (AJAX) ====================
async function submitStoreForm(event) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitBtnText');
    const errors = document.getElementById('formErrors');
    const originalText = submitText.textContent;

    submitBtn.disabled = true;
    submitText.textContent = 'Menyimpan...';

    const formData = new FormData(form);
    const mode = form.dataset.mode;
    const storeId = form.dataset.storeId;

    let url, method;
    if (mode === 'create') {
        url = "{{ route('stores.store.ajax') }}";
        method = 'POST';
    } else {
        url = `/stores/${storeId}/ajax`;
        method = 'POST';
        formData.append('_method', 'PUT');
    }

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
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

        submitBtn.disabled = false;
        submitText.textContent = originalText;

        closeModal();
        showNotification(data.message, 'success');
        await refreshTable();

    } catch (err) {
        errors.classList.remove('hidden');
        errors.querySelector('ul').innerHTML = `<li>Gagal menyimpan: ${err.message}</li>`;
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    }
}

// ==================== DELETE STORE ====================
async function deleteStore(storeId, namaToko) {
    if (!confirm(`Yakin hapus toko "${namaToko}"?`)) return;

    try {
        const response = await fetch(`/stores/${storeId}/ajax`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            showNotification(data.message || 'Gagal menghapus.', 'error');
            return;
        }

        showNotification(data.message, 'success');

        const row = document.getElementById(`store-row-${storeId}`);
        if (row) {
            row.classList.add('removing');
            setTimeout(() => {
                refreshTable();
            }, 300);
        } else {
            refreshTable();
        }

    } catch (err) {
        showNotification('Gagal menghapus: ' + err.message, 'error');
    }
}

// ==================== REFRESH TABLE (AJAX) ====================
async function refreshTable() {
    try {
        const response = await fetch("{{ route('stores.table.html') }}", {
            headers: { 'Accept': 'text/html' },
        });
        const html = await response.text();
        document.getElementById('storesTableContainer').innerHTML = html;
    } catch (err) {
        console.error('Gagal refresh tabel:', err);
    }
}

// ==================== NOTIFICATION ====================
function showNotification(message, type = 'success') {
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
    if (e.key === 'Escape') closeModal();
});
</script>
@endsection