<div class="text-xs">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-100 text-gray-600">
                <th class="p-2 border-b">Tgl / Order ID</th>
                <th class="p-2 border-b text-right" title="Harga setelah diskon × Qty (dari Order.all)">Harga Barang</th>
                <th class="p-2 border-b text-right" title="Biaya Administrasi + Biaya Komisi AMS">Adm</th>
                <th class="p-2 border-b text-right" title="Biaya Proses Pesanan + Biaya Gratis Ongkir XTRA">Proses</th>
                <th class="p-2 border-b text-right" title="Ongkos Kirim Dibayarkan − Gratis Ongkir dari Shopee">Ongkir</th>
                <th class="p-2 border-b text-right" title="PPh 22">Pajak</th>
                <th class="p-2 border-b text-right" title="Biaya Transaksi + Premi + FBS Fee">Lain</th>
                <th class="p-2 border-b text-right" title="Kampanye + Voucher Penjual + Cashback + Isi Saldo">Promosi</th>
                <th class="p-2 border-b text-right font-bold text-orange-600">Net Revenue</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($sales as $s)
            @php
                // Fix double-minus: kalau negatif, tampilkan +
                $fmt = function ($val) {
                    $v = (int) $val;
                    if ($v === 0) return '0';
                    return ($v >= 0 ? '-' : '+') . number_format(abs($v), 0, ',', '.');
                };
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="p-2">
                    <div class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($s->tanggal)->format('d/m/Y') }}</div>
                    <div class="text-[10px] text-gray-400 font-mono">{{ $s->order_id }}</div>
                </td>
                <td class="p-2 text-right text-gray-700">{{ number_format($s->subtotal, 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($s->biaya_komisi) }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($s->biaya_layanan) }}</td>
                <td class="p-2 text-right {{ $s->biaya_ongkir < 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $fmt($s->biaya_ongkir) }}
                </td>
                <td class="p-2 text-right text-red-500">{{ $fmt($s->biaya_pajak) }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($s->biaya_lainnya) }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($s->biaya_promosi ?? 0) }}</td>
                <td class="p-2 text-right font-bold text-orange-600">
                    {{ number_format($s->net_revenue, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="p-4 text-center text-gray-500">Tidak ada data transaksi.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50 font-bold text-gray-800 border-t-2 border-gray-200">
            <tr>
                <td class="p-2 text-right">TOTAL</td>
                <td class="p-2 text-right">{{ number_format($sales->sum('subtotal'), 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($sales->sum('biaya_komisi')) }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($sales->sum('biaya_layanan')) }}</td>
                <td class="p-2 text-right {{ $sales->sum('biaya_ongkir') < 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $fmt($sales->sum('biaya_ongkir')) }}
                </td>
                <td class="p-2 text-right text-red-500">{{ $fmt($sales->sum('biaya_pajak')) }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($sales->sum('biaya_lainnya')) }}</td>
                <td class="p-2 text-right text-red-500">{{ $fmt($sales->sum('biaya_promosi')) }}</td>
                <td class="p-2 text-right text-orange-600 text-sm">
                    {{ number_format($sales->sum('net_revenue'), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Legend sumber Excel --}}
    <div class="mt-3 pt-3 border-t border-gray-100 text-[10px] text-gray-500 space-y-1">
        <div><strong>Harga Barang</strong> ← <em>Harga Setelah Diskon × Jumlah</em> (Order.all)</div>
        <div><strong>Adm</strong> ← <em>Biaya Administrasi</em> + <em>Biaya Komisi AMS</em></div>
        <div><strong>Proses</strong> ← <em>Biaya Proses Pesanan</em> + <em>Biaya Gratis Ongkir XTRA</em></div>
        <div><strong>Ongkir</strong> ← <em>Ongkos Kirim Dibayarkan</em> − <em>Gratis Ongkir dari Shopee</em> (bisa + jika subsidi &gt; ongkir)</div>
        <div><strong>Pajak</strong> ← <em>PPh 22</em></div>
        <div><strong>Lain</strong> ← <em>Biaya Transaksi</em> + <em>Premi</em> + <em>FBS Fee</em></div>
        <div><strong>Promosi</strong> ← <em>Biaya Kampanye</em> + <em>Voucher Penjual</em> + <em>Cashback</em> + <em>Isi Saldo Otomatis</em></div>
        <div><strong>Net Revenue</strong> ← <em>Total Penghasilan</em> dari Income (bukan hitung ulang)</div>
    </div>
</div>