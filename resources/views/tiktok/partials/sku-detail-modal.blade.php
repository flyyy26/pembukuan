<div class="text-xs">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-100 text-gray-600">
                <th class="p-2 border-b">Tgl / Order ID</th>
                <th class="p-2 border-b text-right">Harga Barang</th>
                <th class="p-2 border-b text-right" title="Biaya komisi platform">Komisi</th>
                <th class="p-2 border-b text-right" title="Biaya pemrosesan pesanan + Biaya layanan Growth Xtra">Layanan</th>
                <th class="p-2 border-b text-right" title="Ongkir">Ongkir</th>
                <th class="p-2 border-b text-right" title="Komisi Afiliasi">Afiliasi</th>
                <th class="p-2 border-b text-right" title="PPh Pasal 22 dipungut">Pajak</th>
                <th class="p-2 border-b text-right" title="Komisi dinamis + Biaya lainnya">Lainnya</th>
                <th class="p-2 border-b text-right font-bold text-blue-600">Net Revenue</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($sales as $s)
            <tr class="hover:bg-gray-50">
                <td class="p-2">
                    <div class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($s->tanggal)->format('d/m/Y') }}</div>
                    <div class="text-[10px] text-gray-400 font-mono">{{ $s->order_id }}</div>
                </td>
                <td class="p-2 text-right text-gray-700">{{ number_format($s->subtotal, 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($s->biaya_komisi, 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($s->biaya_layanan, 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($s->biaya_ongkir, 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($s->biaya_afiliasi, 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($s->biaya_pajak, 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($s->biaya_lainnya, 0, ',', '.') }}</td>
                <td class="p-2 text-right font-bold text-blue-600">
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
                <td class="p-2 text-right text-red-500">-{{ number_format($sales->sum('biaya_komisi'), 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($sales->sum('biaya_layanan'), 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($sales->sum('biaya_ongkir'), 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($sales->sum('biaya_afiliasi'), 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($sales->sum('biaya_pajak'), 0, ',', '.') }}</td>
                <td class="p-2 text-right text-red-500">-{{ number_format($sales->sum('biaya_lainnya'), 0, ',', '.') }}</td>
                <td class="p-2 text-right text-blue-600 text-sm">{{ number_format($sales->sum('net_revenue'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Legend sumber Excel --}}
    <div class="mt-3 pt-3 border-t border-gray-100 text-[10px] text-gray-500 space-y-1">
        <div class="flex items-center gap-1">
            <span class="inline-block w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
            <span><strong>Komisi</strong> ← <em>Biaya komisi platform</em></span>
        </div>
        <div class="flex items-center gap-1">
            <span class="inline-block w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
            <span><strong>Layanan</strong> ← <em>Biaya pemrosesan pesanan</em> + <em>Biaya layanan Growth Xtra</em></span>
        </div>
        <div class="flex items-center gap-1">
            <span class="inline-block w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
            <span><strong>Ongkir</strong> ← <em>Ongkir</em></span>
        </div>
        <div class="flex items-center gap-1">
            <span class="inline-block w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
            <span><strong>Afiliasi</strong> ← <em>Komisi Afiliasi</em></span>
        </div>
        <div class="flex items-center gap-1">
            <span class="inline-block w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
            <span><strong>Pajak</strong> ← <em>PPh Pasal 22 dipungut</em></span>
        </div>
        <div class="flex items-center gap-1">
            <span class="inline-block w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
            <span><strong>Lainnya</strong> ← <em>Komisi dinamis + Biaya Lainnya</em></span>
        </div>
    </div>
</div>