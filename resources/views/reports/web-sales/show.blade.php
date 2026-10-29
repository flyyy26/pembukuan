@extends('layouts.app')

@section('title', 'Detail Pesanan ' . $order->order_number)

@section('content')
<div class="space-y-6">

    {{-- ====== HEADER ====== --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.web-sales.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium
                      text-gray-700 bg-white hover:bg-gray-50
                      border border-gray-300 rounded-lg transition">
                <iconify-icon icon="mdi:arrow-left" class="text-base"></iconify-icon>
                Kembali
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detail Pesanan</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    <code class="bg-gray-100 px-2 py-0.5 rounded">{{ $order->order_number }}</code>
                </p>
            </div>
        </div>
    </div>

    {{-- ====== GRID INFO + PELANGGAN ====== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- Info Pesanan --}}
        <div class="lg:col-span-5 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 flex items-center gap-2">
                <iconify-icon icon="mdi:receipt-text-outline" class="text-lg text-blue-600"></iconify-icon>
                <strong class="text-sm text-gray-800">Info Pesanan</strong>
            </div>
            <div class="p-5 space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <span class="text-gray-500">No. Order</span>
                    <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $order->order_number }}</code>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-gray-500">Tanggal</span>
                    <span class="text-gray-800">{{ $order->web_created_at?->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-gray-500">Status</span>
                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium
                                 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full">
                        {{ $order->status }}
                    </span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-gray-500">Pembayaran</span>
                    <span class="text-gray-800">
                        {{ $order->payment_status ?? '-' }}
                        @if ($order->payment_method)
                            <span class="text-gray-400">({{ $order->payment_method }})</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-800">Rp {{ number_format(($order->total_amount - $order->shipping_cost + $order->discount_amount), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-gray-500">Ongkir</span>
                    <span class="text-gray-800">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-gray-500">Diskon</span>
                    <span class="text-red-600">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-gray-200 pt-2 mt-2 flex justify-between gap-3">
                    <span class="text-gray-700 font-semibold">Total</span>
                    <span class="text-lg font-bold text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Data Pelanggan & Bukti Pembayaran --}}
        <div class="lg:col-span-7 flex flex-col gap-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden h-full">
                <div class="px-5 py-3 border-b border-gray-200 flex items-center gap-2">
                    <iconify-icon icon="mdi:account-outline" class="text-lg text-blue-600"></iconify-icon>
                    <strong class="text-sm text-gray-800">Data Pelanggan</strong>
                </div>
                <div class="p-5 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500">Nama</span>
                        <span class="text-gray-800 font-medium">{{ $order->customer_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500">Email</span>
                        <span class="text-gray-800">{{ $order->customer_email ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500">Telepon</span>
                        <span class="text-gray-800">{{ $order->customer_phone ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- ✅ BUKTI PEMBAYARAN --}}
            @if($order->payment_proof)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm h-full">
                <div class="px-5 py-3 border-b border-gray-200 flex items-center gap-2">
                    <iconify-icon icon="mdi:image-outline" class="text-lg text-blue-600"></iconify-icon>
                    <strong class="text-sm text-gray-800">Bukti Pembayaran</strong>
                </div>
                <div class="p-5 flex justify-center bg-gray-50">
                    {{-- Sesuaikan URL gambar dengan konfigurasi storage/server web Anda --}}
                    <a href="https://barokahsport.com/storage/{{ $order->payment_proof }}" target="_blank">
                        <img src="https://barokahsport.com/storage/{{ $order->payment_proof }}" 
                             alt="Bukti Pembayaran" 
                             class="max-h-64 rounded-lg border border-gray-300 shadow-sm hover:opacity-90 transition cursor-pointer">
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ====== ITEM PESANAN ====== --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:package-variant-closed" class="text-lg text-blue-600"></iconify-icon>
                <strong class="text-sm text-gray-800">Item Pesanan</strong>
            </div>
            <span class="text-xs text-gray-500">{{ $order->items->count() }} item</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Produk</th>
                        <th class="px-4 py-3 text-left font-medium">SKU</th>
                        <th class="px-4 py-3 text-left font-medium">Kategori</th>
                        <th class="px-4 py-3 text-right font-medium">Harga</th>
                        <th class="px-4 py-3 text-center font-medium">Qty</th>
                        <th class="px-4 py-3 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($order->items as $item)
                        <tr class="hover:bg-gray-50 transition">
                            {{-- ✅ Padding dan perataan teks diperbaiki --}}
                            <td class="px-4 py-3 text-gray-800">{{ $item->product_name }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $item->sku ?? '-' }}</code>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->category_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <iconify-icon icon="mdi:inbox-outline" class="text-3xl text-gray-300 mb-2 block mx-auto"></iconify-icon>
                                Tidak ada item
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <th colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total</th>
                        <th class="px-4 py-3 text-right text-base font-bold text-gray-900 whitespace-nowrap">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection