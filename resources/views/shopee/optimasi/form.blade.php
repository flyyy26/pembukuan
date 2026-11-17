@extends('layouts.app')

@section('title', $item->exists ? 'Edit Produk Optimasi' : 'Tambah Produk Optimasi')

@section('content')
@php
    $isEdit = $item->exists;
    $aspekList = \App\Models\ProductOptimization::ASPEK_AUDIT;
    $statusMeta = \App\Models\ProductOptimization::STATUS_META;
@endphp

<div class="w-full max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                <iconify-icon icon="mdi:rocket-launch-outline" class="text-amber-600 text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">
                    {{ $isEdit ? 'Edit Produk Optimasi' : 'Tambah Produk Optimasi' }}
                </h2>
                <p class="text-sm text-gray-500">{{ $store?->nama_toko ?? '-' }}</p>
            </div>
        </div>
        <a href="{{ route('shopee.optimasi.index', ['store' => $item->store_id ?? ($store->id ?? null)]) }}"
           class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            <iconify-icon icon="mdi:arrow-left" class="text-lg"></iconify-icon>
            Kembali
        </a>
    </div>

    {{-- Error --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <div class="font-semibold mb-1 text-sm">Ada kesalahan:</div>
            <ul class="text-xs list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('shopee.optimasi.update', $item) : route('shopee.optimasi.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- ===== INFO PRODUK ===== --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <iconify-icon icon="mdi:package-variant-closed" class="text-amber-600 text-lg"></iconify-icon>
                Informasi Produk
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko <span class="text-red-500">*</span></label>
                    <select name="store_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="">-- Pilih Toko --</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ (old('store_id', $item->store_id) == $s->id) ? 'selected' : '' }}>
                                {{ $s->nama_toko }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_produk" value="{{ old('nama_produk', $item->nama_produk) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">SKU Varian (opsional)</label>
                    <input type="text" name="marketplace_sku" value="{{ old('marketplace_sku', $item->marketplace_sku) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Terakhir Audit</label>
                    <input type="date" name="terakhir_audit"
                           value="{{ old('terakhir_audit', $item->terakhir_audit?->format('Y-m-d') ?? now()->toDateString()) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        {{-- ===== FINANSIAL ===== --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <iconify-icon icon="mdi:cash-multiple" class="text-amber-600 text-lg"></iconify-icon>
                Data Finansial
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @php
                    $finansial = [
                        'harga_produk'            => 'Harga Produk',
                        'biaya_layanan'           => 'Biaya Layanan',
                        'ongkir'                  => 'Ongkir',
                        'diskon_produk'           => 'Diskon Produk',
                        'total_diskon_pengiriman' => 'Total Diskon Pengiriman',
                        'voucher_diskon'          => 'Voucher Diskon',
                        'harga_final'             => 'Harga Final',
                    ];
                @endphp
                @foreach($finansial as $field => $label)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm font-medium text-gray-500 pointer-events-none">Rp</span>
                            <input type="text"
                                name="{{ $field }}"
                                inputmode="numeric"
                                data-rupiah
                                value="{{ old($field, $item->{$field} ?? 0) }}"
                                placeholder="0"
                                class="rupiah-input w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm text-right font-medium tabular-nums focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div class="text-xs text-gray-500 flex items-center gap-1.5">
                    <iconify-icon icon="mdi:information-outline" class="text-sm"></iconify-icon>
                    Harga Final terisi otomatis jika kosong. Klik nilai untuk edit manual.
                </div>
                <button type="button"
                        onclick="hitungHargaFinalOtomatis()"
                        class="inline-flex items-center gap-1.5 text-xs bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-lg hover:bg-amber-100 transition font-medium">
                    <iconify-icon icon="mdi:calculator" class="text-sm"></iconify-icon>
                    Hitung Otomatis
                </button>
            </div>
        </div>

        {{-- ===== AUDIT 9 ASPEK + SOLUSI + STATUS ===== --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <div class="flex items-start justify-between mb-1 gap-3 flex-wrap">
                <div>
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <iconify-icon icon="mdi:clipboard-check-outline" class="text-amber-600 text-lg"></iconify-icon>
                        Hasil Pemeriksaan & Solusi
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Isi status, catatan masalah, solusi, dan centang jika sudah dilaksanakan.
                    </p>
                </div>
                <div id="progressBadge" class="hidden bg-blue-50 border border-blue-200 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1.5">
                    <iconify-icon icon="mdi:progress-check" class="text-base"></iconify-icon>
                    <span id="progressText">0 / 0 selesai</span>
                </div>
            </div>

            <div class="space-y-3 mt-4">
                @foreach($aspekList as $key => $meta)
                    @php
                        $current = old("audit_{$key}", $item->{'audit_' . $key});
                        $curStatus = $current['status'] ?? '';
                        $curNote   = $current['note'] ?? '';
                        $curSolusi = old("solusi_{$key}", $item->{"solusi_{$key}"} ?? '');
                        $curSudah  = old("sudah_{$key}", $item->{"sudah_{$key}"} ?? false);
                        $isStok    = ($key === 'stok');
                    @endphp
                    <div class="border border-gray-100 rounded-lg p-3 aspek-card
                                {{ $curStatus === 'perhatian' ? 'bg-red-50/40 border-red-100' : ($curStatus === 'evaluasi' ? 'bg-yellow-50/40 border-yellow-100' : '') }}"
                         data-aspek="{{ $key }}">
                        <div class="flex items-center justify-between mb-3 gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-white border border-gray-200 rounded flex items-center justify-center shrink-0">
                                    <iconify-icon icon="{{ $meta['icon'] }}" class="text-gray-600 text-base"></iconify-icon>
                                </div>
                                <label class="text-sm font-semibold text-gray-800">{{ $meta['label'] }}</label>
                            </div>

                            <label class="inline-flex items-center gap-2 cursor-pointer select-none bg-white border border-gray-200 rounded-lg px-3 py-1.5 hover:border-green-300 transition">
                                <input type="checkbox"
                                       name="sudah_{{ $key }}"
                                       value="1"
                                       {{ $curSudah ? 'checked' : '' }}
                                       class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-2 focus:ring-green-500 sudah-checkbox"
                                       data-aspek="{{ $key }}">
                                <span class="text-xs font-medium text-gray-700">Sudah Dilaksanakan</span>
                            </label>
                        </div>

                        {{-- Row 1: Status + Catatan --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-2">
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1 uppercase tracking-wide">Status</label>
                                <select name="audit_{{ $key }}[status]"
                                        class="status-select w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white"
                                        data-aspek="{{ $key }}">
                                    <option value="">— Belum diaudit —</option>
                                    <option value="aman"      {{ $curStatus === 'aman' ? 'selected' : '' }}>Aman</option>
                                    <option value="evaluasi"  {{ $curStatus === 'evaluasi' ? 'selected' : '' }}>Perlu Evaluasi</option>
                                    <option value="perhatian" {{ $curStatus === 'perhatian' ? 'selected' : '' }}>Perlu Perhatian</option>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-medium text-gray-500 mb-1 uppercase tracking-wide">
                                    {{ $isStok ? 'Catatan / Detail Stok per Varian' : 'Catatan Masalah' }}
                                </label>
                                @if($isStok)
                                    {{-- ✅ TEXTAREA untuk Pilihan Ukuran & Stok --}}
                                    <textarea name="audit_{{ $key }}[note]"
                                              rows="4"
                                              placeholder="Contoh:&#10;Navy kuning ukuran L, XL, XXL, 3XL habis&#10;Biru Putih ukuran L habis&#10;Abu Hitam M habis"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none resize-y font-mono text-xs leading-relaxed">{{ $curNote }}</textarea>
                                @else
                                    <input type="text" name="audit_{{ $key }}[note]"
                                           value="{{ $curNote }}"
                                           placeholder="Catatan masalah (opsional)..."
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                @endif
                            </div>
                        </div>

                        {{-- Row 2: Solusi --}}
                        <div class="solusi-wrapper {{ in_array($curStatus, ['evaluasi','perhatian']) ? '' : 'hidden' }}">
                            <label class="block text-[10px] font-medium text-gray-500 mb-1 uppercase tracking-wide flex items-center gap-1">
                                <iconify-icon icon="mdi:lightbulb-on-outline" class="text-amber-500 text-sm"></iconify-icon>
                                Solusi / Tindakan Perbaikan
                            </label>
                            <textarea name="solusi_{{ $key }}"
                                      rows="2"
                                      placeholder="Tulis solusi konkret... contoh: Update stok L/XL/XXL via Shopee Seller Centre"
                                      class="w-full border border-amber-200 bg-amber-50/30 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none resize-y">{{ $curSolusi }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500 flex items-start gap-1.5">
                <iconify-icon icon="mdi:information-outline" class="text-sm mt-0.5 shrink-0"></iconify-icon>
                <span>Field <strong>Solusi</strong> otomatis muncul saat status <em>Perlu Evaluasi</em> atau <em>Perlu Perhatian</em>. Centang <strong>Sudah Dilaksanakan</strong> jika solusi sudah diterapkan di listing.</span>
            </div>
        </div>

        {{-- ===== ACTION ===== --}}
        <div class="flex justify-end gap-2">
            <a href="{{ route('shopee.optimasi.index', ['store' => $item->store_id ?? ($store->id ?? null)]) }}"
               class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-amber-600 text-white px-5 py-2 rounded-lg hover:bg-amber-700 transition text-sm font-medium">
                <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon>
                {{ $isEdit ? 'Update' : 'Simpan' }}
            </button>
        </div>
    </form>
</div>

<script>
// ============================================================
// FORMAT RUPIAH
// ============================================================
function formatRupiah(value) {
    let num = String(value).replace(/\D/g, '');
    if (!num) return '0';
    num = num.replace(/^0+/, '') || '0';
    return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function parseRupiah(value) {
    return parseInt(String(value).replace(/\D/g, '')) || 0;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[data-rupiah]').forEach(function (input) {
        input.value = formatRupiah(parseRupiah(input.value));

        input.addEventListener('input', function () {
            const cursorFromEnd = this.value.length - this.selectionStart;
            this.value = formatRupiah(this.value);
            const newPos = Math.max(0, this.value.length - cursorFromEnd);
            try { this.setSelectionRange(newPos, newPos); } catch (_) {}
        });

        input.addEventListener('focus', function () {
            if (this.value === '0') this.value = '';
        });

        input.addEventListener('blur', function () {
            if (this.value === '' || this.value === '0') this.value = '0';
        });
    });
});

function hitungHargaFinalOtomatis() {
    const harga      = parseRupiah(document.querySelector('[name="harga_produk"]').value);
    const diskon     = parseRupiah(document.querySelector('[name="diskon_produk"]').value);
    const voucher    = parseRupiah(document.querySelector('[name="voucher_diskon"]').value);
    const diskOngkir = parseRupiah(document.querySelector('[name="total_diskon_pengiriman"]').value);

    const final = Math.max(0, harga - diskon - voucher - diskOngkir);
    document.querySelector('[name="harga_final"]').value = formatRupiah(final);
}

// ============================================================
// TOGGLE SOLUSI + PROGRESS
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    function toggleSolusi(aspek, status) {
        const card = document.querySelector(`.aspek-card[data-aspek="${aspek}"]`);
        if (!card) return;
        const wrapper = card.querySelector('.solusi-wrapper');
        const needsFix = (status === 'evaluasi' || status === 'perhatian');

        wrapper.classList.toggle('hidden', !needsFix);

        card.classList.remove('bg-red-50/40', 'border-red-100', 'bg-yellow-50/40', 'border-yellow-100');
        if (status === 'perhatian') {
            card.classList.add('bg-red-50/40', 'border-red-100');
        } else if (status === 'evaluasi') {
            card.classList.add('bg-yellow-50/40', 'border-yellow-100');
        }
    }

    function updateProgress() {
        let total = 0, selesai = 0;
        document.querySelectorAll('.aspek-card').forEach(function (card) {
            const status = card.querySelector('.status-select').value;
            const cb = card.querySelector('.sudah-checkbox');
            if (status === 'evaluasi' || status === 'perhatian') {
                total++;
                if (cb && cb.checked) selesai++;
            }
        });

        const badge = document.getElementById('progressBadge');
        const text = document.getElementById('progressText');

        if (total === 0) {
            badge.classList.add('hidden');
        } else {
            badge.classList.remove('hidden');
            text.textContent = `${selesai} / ${total} selesai`;
            badge.classList.remove('bg-blue-50','border-blue-200','text-blue-700','bg-green-50','border-green-200','text-green-700','bg-amber-50','border-amber-200','text-amber-700');
            if (selesai === total) {
                badge.classList.add('bg-green-50','border-green-200','text-green-700');
            } else if (selesai === 0) {
                badge.classList.add('bg-amber-50','border-amber-200','text-amber-700');
            } else {
                badge.classList.add('bg-blue-50','border-blue-200','text-blue-700');
            }
        }
    }

    document.querySelectorAll('.status-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            toggleSolusi(this.dataset.aspek, this.value);
            updateProgress();
        });
        toggleSolusi(sel.dataset.aspek, sel.value);
    });

    document.querySelectorAll('.sudah-checkbox').forEach(function (cb) {
        cb.addEventListener('change', updateProgress);
    });

    updateProgress();
});
</script>
@endsection