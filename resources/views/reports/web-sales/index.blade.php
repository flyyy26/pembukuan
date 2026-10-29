@extends('layouts.app')

@section('title', 'Laporan Penjualan Web')

@section('content')
<div class="space-y-6">

    {{-- ====== HEADER ====== --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <iconify-icon icon="mdi:web" class="text-2xl text-blue-600"></iconify-icon>
                Laporan Penjualan Web
            </h1>
            <p class="text-sm text-gray-500 mt-1">Barokah Sport · pesanan delivered</p>
        </div>
        <form method="POST" action="{{ route('reports.web-sales.sync') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit"
                    onclick="return confirm('Sinkronisasi sekarang dari DB web?')"
                    class="w-full sm:w-auto justify-center inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                           text-blue-700 bg-blue-50 hover:bg-blue-100
                           border border-blue-200 rounded-lg transition">
                <iconify-icon icon="mdi:sync" class="text-base"></iconify-icon>
                Sync Sekarang
            </button>
        </form>
    </div>

    {{-- ====== ALERT ====== --}}
    @if (session('success'))
        <div class="flex items-start gap-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            <iconify-icon icon="mdi:check-circle" class="text-lg text-green-600 flex-shrink-0 mt-0.5"></iconify-icon>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-start gap-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            <iconify-icon icon="mdi:alert-circle" class="text-lg text-red-600 flex-shrink-0 mt-0.5"></iconify-icon>
            <div class="flex-1">{{ session('error') }}</div>
        </div>
    @endif

    {{-- ====== INFO SYNC ====== --}}
    @if ($lastSync)
        <p class="text-xs text-gray-500 flex flex-wrap items-center gap-1.5">
            <iconify-icon icon="mdi:clock-outline" class="text-sm"></iconify-icon>
            Sync terakhir:
            <strong class="text-gray-700">{{ $lastSync->finished_at?->format('d/m/Y H:i') }}</strong>
            <span class="hidden sm:inline">({{ $lastSync->records_created }} baru, {{ $lastSync->records_updated }} diperbarui)</span>
        </p>
    @else
        <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
            <iconify-icon icon="mdi:alert" class="text-base flex-shrink-0 mt-0.5"></iconify-icon>
            <span>Belum pernah sinkronisasi. Klik tombol <strong>"Sync Sekarang"</strong>.</span>
        </div>
    @endif

    {{-- ====== FILTER ====== --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('reports.web-sales.index') }}"
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status Bayar</label>
                <select name="payment_status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="all" @selected($payment === 'all' || !$payment)>Semua</option>
                    <option value="paid" @selected($payment === 'paid')>Paid</option>
                    <option value="pending" @selected($payment === 'pending')>Pending</option>
                    <option value="expired" @selected($payment === 'expired')>Expired</option>
                </select>
            </div>
            <div class="sm:col-span-2 md:col-span-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Order / Pelanggan</label>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="No order, nama, email..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="sm:col-span-2 md:col-span-2 mt-2 md:mt-0">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium
                               text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                    <iconify-icon icon="mdi:filter" class="text-base"></iconify-icon>
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- ====== RINGKASAN (2 KOLOM DI MOBILE, 4 KOLOM DI DESKTOP) ====== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Total Pesanan --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-sm p-3 sm:p-5 text-white">
            <div class="flex items-center justify-between mb-1 sm:mb-2">
                <h6 class="text-[10px] sm:text-xs font-medium text-blue-100 uppercase tracking-wide">Total Pesanan</h6>
                <iconify-icon icon="mdi:cart-outline" class="text-lg sm:text-2xl text-blue-200 hidden sm:block"></iconify-icon>
            </div>
            <h3 class="text-base sm:text-2xl font-bold">{{ number_format($summary->total_orders ?? 0, 0, ',', '.') }}</h3>
        </div>

        {{-- Total Pendapatan --}}
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-sm p-3 sm:p-5 text-white">
            <div class="flex items-center justify-between mb-1 sm:mb-2">
                <h6 class="text-[10px] sm:text-xs font-medium text-emerald-100 uppercase tracking-wide">Total Pendapatan</h6>
                <iconify-icon icon="mdi:cash-multiple" class="text-lg sm:text-2xl text-emerald-200 hidden sm:block"></iconify-icon>
            </div>
            <h3 class="text-base sm:text-2xl font-bold"><span class="text-[10px] sm:text-sm font-normal">Rp</span> {{ number_format($summary->total_revenue ?? 0, 0, ',', '.') }}</h3>
        </div>

        {{-- Rata-rata Order --}}
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-sm p-3 sm:p-5 text-white">
            <div class="flex items-center justify-between mb-1 sm:mb-2">
                <h6 class="text-[10px] sm:text-xs font-medium text-amber-100 uppercase tracking-wide">Rata-rata Order</h6>
                <iconify-icon icon="mdi:chart-line" class="text-lg sm:text-2xl text-amber-200 hidden sm:block"></iconify-icon>
            </div>
            <h3 class="text-base sm:text-2xl font-bold"><span class="text-[10px] sm:text-sm font-normal">Rp</span> {{ number_format($summary->avg_order_value ?? 0, 0, ',', '.') }}</h3>
        </div>

        {{-- Total Ongkir --}}
        <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-sm p-3 sm:p-5 text-white">
            <div class="flex items-center justify-between mb-1 sm:mb-2">
                <h6 class="text-[10px] sm:text-xs font-medium text-cyan-100 uppercase tracking-wide">Total Ongkir</h6>
                <iconify-icon icon="mdi:truck-outline" class="text-lg sm:text-2xl text-cyan-200 hidden sm:block"></iconify-icon>
            </div>
            <h3 class="text-base sm:text-2xl font-bold"><span class="text-[10px] sm:text-sm font-normal">Rp</span> {{ number_format($summary->total_shipping ?? 0, 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- ====== TABEL PESANAN ====== --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:format-list-bulleted" class="text-lg text-blue-600 w-max m-auto"></iconify-icon>
                <strong class="text-sm text-gray-800">Daftar Pesanan</strong>
            </div>
            <span class="text-xs text-gray-500">{{ $orders->total() }} pesanan</span>
        </div>

        {{-- TAMPILAN TABEL (DESKTOP/TABLET) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">No. Order</th>
                        <th class="px-4 py-3 text-left font-medium">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-medium">Pembayaran</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-center font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $order->order_number }}</code>
                            </td>
                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                {{ $order->web_created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-800">{{ $order->customer_name ?? '-' }}</div>
                                @if ($order->customer_email)
                                    <div class="text-xs text-gray-400">{{ $order->customer_email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = match(strtolower($order->payment_status ?? '')) {
                                        'paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'expired' => 'bg-red-100 text-red-700 border-red-200',
                                        default   => 'bg-gray-100 text-gray-600 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border rounded-full {{ $badge }}">
                                    {{ $order->payment_status ?? '-' }}
                                </span>
                                @if ($order->payment_method)
                                    <div class="text-xs text-gray-400 mt-1">{{ $order->payment_method }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('reports.web-sales.show', $order->web_order_id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium
                                            text-blue-700 bg-blue-50 hover:bg-blue-100
                                            border border-blue-200 rounded-lg transition">
                                        <iconify-icon icon="mdi:eye-outline" class="text-sm"></iconify-icon>
                                        Detail
                                    </a>
                                    <button type="button" 
                                            onclick="deleteWebOrder({{ $order->web_order_id }})"
                                            class="inline-flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition">
                                        <iconify-icon icon="mdi:delete-outline" class="text-sm"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">
                                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 mb-2 w-max block mx-auto"></iconify-icon>
                                Tidak ada pesanan <strong>delivered</strong> pada rentang ini.<br>
                                <span class="text-xs">Klik "Sync Sekarang" jika data belum ada di DB lokal.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TAMPILAN CARD (MOBILE) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($orders as $order)
                <div class="p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $order->order_number }}</code>
                            <div class="text-[10px] text-gray-500 mt-1">{{ $order->web_created_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="text-right">
                            @php
                                $badge = match(strtolower($order->payment_status ?? '')) {
                                    'paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'expired' => 'bg-red-100 text-red-700 border-red-200',
                                    default   => 'bg-gray-100 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium border rounded-full {{ $badge }}">
                                {{ $order->payment_status ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-2 rounded-lg mt-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">Pelanggan:</span>
                            <span class="text-gray-800 font-medium text-right truncate pl-2">{{ $order->customer_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs mt-1">
                            <span class="text-gray-500">Total Belanja:</span>
                            <span class="text-gray-900 font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <a href="{{ route('reports.web-sales.show', $order->web_order_id) }}"
                           class="flex-1 inline-flex justify-center items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2 py-1.5 rounded text-xs font-medium transition">
                            <iconify-icon icon="mdi:eye-outline" class="text-sm"></iconify-icon> Detail
                        </a>
                        <button type="button" 
                                onclick="deleteWebOrder({{ $order->web_order_id }})"
                                class="flex-none inline-flex justify-center items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded text-xs font-medium transition">
                            <iconify-icon icon="mdi:delete-outline" class="text-sm"></iconify-icon>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block mx-auto mb-2"></iconify-icon>
                    <span class="text-sm">Tidak ada pesanan pada rentang ini.</span>
                </div>
            @endforelse
        </div>

        @if ($orders->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    const CSRF_TOKEN_WEB = document.querySelector('meta[name="csrf-token"]').content;

    async function deleteWebOrder(id) {
        if (!confirm('Yakin ingin menghapus data pesanan ini dari database lokal? (Data asli di web tidak akan terhapus)')) {
            return;
        }

        // Gunakan route() agar URL-nya otomatis benar
        // URL placeholder {id} akan diganti dengan variabel id
        const url = "{{ route('reports.web-sales.destroy', ':id') }}".replace(':id', id);

        try {
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN_WEB,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.message || 'Gagal menghapus.');
            
            // Reload halaman untuk memperbarui tabel
            window.location.reload();
        } catch (err) {
            alert('Gagal: ' + err.message);
        }
    }
</script>
@endsection