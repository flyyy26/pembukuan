@extends('layouts.app')

@section('title', 'Laporan Piutang')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:account-cash" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Piutang</h2>
                <p class="text-xs sm:text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }} · Terhubung dengan BRILink</p>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap gap-3 items-end">
            
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Piutang</label>
                <select name="kategori" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                    <option value="all" {{ $kategoriFilter === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ $kategoriFilter == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-600 focus:outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="sudah" {{ $status === 'sudah' ? 'selected' : '' }}>Sudah Lunas</option>
                </select>
            </div>

            <div class="w-full sm:col-span-2 md:col-span-1 md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Keterangan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari keterangan piutang..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                </div>
            </div>

            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                {{-- value={{ $from }} otomatis kosong jika tidak ada request --}}
                <input type="date" name="from" value="{{ $from }}" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-transparent">
            </div>

            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-transparent">
            </div>

            <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                <button type="submit" class="flex-1 md:flex-none bg-amber-600 text-white px-5 py-2 rounded-lg hover:bg-amber-700 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                {{-- Tombol reset muncul jika ada filter aktif --}}
                @if($search || $status !== 'all' || $from || $to || $storeId !== 'all' || $kategoriFilter !== 'all')
                    <a href="{{ route('piutang.index') }}" class="flex-1 md:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="text-xs text-gray-500 mb-1">Total Piutang Awal</div>
            <div class="text-lg sm:text-xl font-bold text-gray-800">Rp {{ number_format($summary['total_nominal'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="text-xs text-gray-500 mb-1">Total Terbayar</div>
            <div class="text-lg sm:text-xl font-bold text-emerald-600">Rp {{ number_format($summary['total_terbayar'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="text-xs text-gray-500 mb-1">Sisa Piutang</div>
            <div class="text-lg sm:text-xl font-bold text-amber-600">Rp {{ number_format($summary['sisa_piutang'], 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- DATA PIUTANG --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Piutang</h3>
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max order-3 sm:order-1">{{ $summary['jumlah_data'] }} Data</div>
                
                {{-- TOMBOL BAYAR BULK (Hanya muncul jika kategori dipilih) --}}
                @if($kategoriFilter !== 'all')
                    <button type="button" onclick="openBulkBayarModal()"
                            class="w-full sm:w-auto order-1 sm:order-2 inline-flex items-center justify-center gap-2 bg-emerald-600 text-white px-4 py-1.5 rounded-lg hover:bg-emerald-700 transition text-xs font-medium shadow-sm">
                        <iconify-icon icon="mdi:cash-multiple" class="text-sm"></iconify-icon>
                        Bayar Bulk
                    </button>
                @endif

                {{-- TOMBOL PRINT INVOICE --}}
                <a href="{{ route('piutang.invoice-pdf', [
                        'store' => $storeId, 
                        'from' => $from, 
                        'to' => $to, 
                        'search' => $search, 
                        'status' => $status,
                        'kategori' => $kategoriFilter
                    ]) }}" 
                   target="_blank"
                   class="w-full sm:w-auto order-2 sm:order-3 inline-flex items-center justify-center gap-2 bg-amber-600 text-white px-4 py-1.5 rounded-lg hover:bg-amber-700 transition text-xs font-medium shadow-sm">
                    <iconify-icon icon="mdi:printer" class="text-sm"></iconify-icon>
                    Print Invoice
                </a>
            </div>
        </div>

        @if($piutangList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:check-circle-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                <span class="text-sm">Tidak ada data piutang untuk filter ini.</span>
            </div>
        @else
            {{-- TAMPILAN TABEL (DESKTOP) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Keterangan</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kategori</th>
                            @if($storeId === 'all')
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Toko</th>
                            @endif
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Nominal Awal</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Terbayar</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Sisa</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Status</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-32 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($piutangList as $p)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-xs text-gray-700 whitespace-nowrap">{{ $p['tanggal_label'] }}</td>
                                <td class="py-3 px-4 text-gray-800">{{ $p['keterangan'] }}</td>
                                <td class="py-3 px-4 text-xs whitespace-nowrap">
                                    <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-1 rounded text-[10px]">
                                        {{ $p['kategori'] }}
                                    </span>
                                </td>
                                @if($storeId === 'all')
                                    <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                        <iconify-icon icon="mdi:store" class="mr-1"></iconify-icon>
                                        {{ $p['store_name'] }}
                                    </td>
                                @endif
                                <td class="py-3 px-4 text-right text-gray-700 whitespace-nowrap">Rp {{ number_format($p['nominal'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-emerald-600 whitespace-nowrap">Rp {{ number_format($p['terbayar'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-amber-600 whitespace-nowrap">Rp {{ number_format($p['sisa'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($p['is_paid'])
                                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:check-circle" class="text-sm"></iconify-icon> Lunas
                                        </span>
                                    @elseif($p['terbayar'] > 0)
                                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:progress-clock" class="text-sm"></iconify-icon> Sebagian
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:clock-alert" class="text-sm"></iconify-icon> Belum Bayar
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if(!$p['is_paid'])
                                        <button type="button"
                                                onclick="bayarPiutang({{ $p['id'] }}, {{ $p['sisa'] }})"
                                                class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition border border-emerald-200">
                                            <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon>
                                            Bayar
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN CARD (MOBILE) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($piutangList as $p)
                    <div class="p-4 space-y-2 text-xs">
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold text-gray-800 text-sm mb-1">{{ $p['keterangan'] }}</div>
                                <div class="mb-1">
                                    <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded text-[10px]">
                                        {{ $p['kategori'] }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 text-[10px] text-gray-500">
                                    <iconify-icon icon="mdi:calendar" class="text-xs"></iconify-icon>
                                    {{ $p['tanggal_label'] }}
                                </div>
                                @if($storeId === 'all')
                                    <div class="flex items-center gap-1 text-[10px] text-gray-500 mt-0.5">
                                        <iconify-icon icon="mdi:store" class="text-xs"></iconify-icon>
                                        {{ $p['store_name'] }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                @if($p['is_paid'])
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">
                                        <iconify-icon icon="mdi:check-circle" class="text-xs"></iconify-icon> Lunas
                                    </span>
                                @elseif($p['terbayar'] > 0)
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">
                                        <iconify-icon icon="mdi:progress-clock" class="text-xs"></iconify-icon> Sebagian
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">
                                        <iconify-icon icon="mdi:clock-alert" class="text-xs"></iconify-icon> Belum
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-50 mt-2">
                            <div>
                                <span class="text-gray-400 block text-[10px]">Nominal Awal</span>
                                <span class="text-gray-700">Rp {{ number_format($p['nominal'], 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-400 block text-[10px]">Terbayar</span>
                                <span class="text-emerald-600">Rp {{ number_format($p['terbayar'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-1 mt-1 border-t border-gray-100">
                            <span class="font-bold text-gray-800">Sisa Piutang</span>
                            <span class="font-bold text-amber-600 text-sm">Rp {{ number_format($p['sisa'], 0, ',', '.') }}</span>
                        </div>

                        @if(!$p['is_paid'])
                            <div class="pt-2">
                                <button type="button"
                                        onclick="bayarPiutang({{ $p['id'] }}, {{ $p['sisa'] }})"
                                        class="w-full inline-flex justify-center items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-2 rounded text-xs font-medium transition">
                                    <iconify-icon icon="mdi:cash-check" class="text-sm"></iconify-icon> Tandai Lunas
                                </button>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- MODAL BAYAR BULK PIUTANG --}}
<div id="bulkBayarModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeBulkBayarModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-slide-up">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-emerald-50">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cash-multiple" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Bayar Bulk Piutang</h3>
            </div>
            <button type="button" onclick="closeBulkBayarModal()" class="text-gray-400 hover:text-gray-600 transition">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="bulkBayarForm" onsubmit="submitBulkBayar(event)">
            @csrf
            <input type="hidden" name="store" value="{{ $storeId }}">
            <input type="hidden" name="kategori" value="{{ $kategoriFilter }}">
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="bulkBayarError"></div>

                <div class="bg-emerald-50 rounded-lg p-3 text-xs text-emerald-800 flex items-start gap-2">
                    <iconify-icon icon="mdi:information" class="text-base mt-0.5 flex-shrink-0"></iconify-icon>
                    <div>
                        Piutang akan dilunasi otomatis <strong>dari yang paling lama</strong> (FIFO) untuk kategori <strong>{{ $kategoriFilter }}</strong>. 
                        Jika nominal tidak pas, piutang terakhir akan dibayar sebagian.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Total Nominal Pembayaran</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="inputBulkBayar" name="nominal" required placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeBulkBayarModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitBulkBayar" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:cash-check" class="text-lg"></iconify-icon> Bayar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN_PIUTANG = document.querySelector('meta[name="csrf-token"]').content;

    async function bayarPiutang(id, sisaNominal) {
        const nominalFormatted = new Intl.NumberFormat('id-ID').format(sisaNominal);
        if (!confirm(`Tandai piutang ini LUNAS?\nSisa tagihan: Rp ${nominalFormatted}`)) return;

        try {
            const response = await fetch(`/piutang/${id}/bayar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PIUTANG,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal.');

            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }

    function openBulkBayarModal() {
        document.getElementById('bulkBayarForm').reset();
        document.getElementById('bulkBayarError').classList.add('hidden');
        document.getElementById('bulkBayarModal').classList.remove('hidden');
        document.getElementById('bulkBayarModal').classList.add('flex');
    }

    function closeBulkBayarModal() {
        document.getElementById('bulkBayarModal').classList.add('hidden');
        document.getElementById('bulkBayarModal').classList.remove('flex');
    }

    document.getElementById('inputBulkBayar')?.addEventListener('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
    });

    async function submitBulkBayar(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitBulkBayar');
        const errorDiv = document.getElementById('bulkBayarError');
        const input = document.getElementById('inputBulkBayar');

        const nominalRaw = input.value.replace(/[^0-9]/g, '');
        if (!nominalRaw || parseInt(nominalRaw) <= 0) {
            errorDiv.textContent = 'Nominal tidak valid.';
            errorDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Memproses...';
        errorDiv.classList.add('hidden');

        const formData = new FormData(form);
        formData.set('nominal', nominalRaw);

        try {
            const response = await fetch("{{ route('piutang.bayar-bulk') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_PIUTANG,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal.');

            alert(data.message);
            window.location.reload();
        } catch (err) {
            errorDiv.textContent = err.message;
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="mdi:cash-check" class="text-lg"></iconify-icon> Bayar';
        }
    }
</script>
@endsection