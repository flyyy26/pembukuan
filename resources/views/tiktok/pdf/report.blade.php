<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan TikTok - {{ $store?->nama_toko ?? 'All' }}</title>
    <style>
        /* Copy seluruh CSS dari shopee/pdf/report.blade.php */
        /* Ganti aksen orange (#f97316, #ea580c) jadi pink (#ec4899, #db2777) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #1f2937; line-height: 1.45; padding: 20px 25px; }
        .header { border-bottom: 3px solid #ec4899; padding-bottom: 10px; margin-bottom: 16px; }
        .header-top { display: table; width: 100%; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; font-size: 8px; color: #6b7280; line-height: 1.6; }
        .header-title { font-size: 18px; font-weight: bold; color: #db2777; margin-bottom: 3px; }
        .header-subtitle { font-size: 11px; color: #6b7280; }
        .header-meta strong { color: #374151; }
        .section { margin-bottom: 14px; }
        .section.keep-together { page-break-inside: avoid; }
        .section.allow-break { page-break-inside: auto; }
        .section-title { font-size: 12px; font-weight: bold; color: #1f2937; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; }
        .summary-grid { display: table; width: 100%; table-layout: fixed; border-spacing: 5px 0; }
        .summary-card { display: table-cell; width: 25%; padding: 10px 8px; border: 1px solid #e5e7eb; border-radius: 5px; background-color: #fafafa; vertical-align: top; }
        .summary-card-label { font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .summary-card-value { font-size: 13px; font-weight: bold; line-height: 1.2; }
        .summary-card-value.blue { color: #2563eb; }
        .summary-card-value.orange { color: #ea580c; }
        .summary-card-value.green { color: #059669; }
        .summary-card-value.gray { color: #374151; }
        .biaya-grid { display: table; width: 100%; table-layout: fixed; border-spacing: 4px 0; }
        .biaya-item { display: table-cell; width: 16.66%; padding: 8px 5px; border: 1px solid #f3f4f6; border-radius: 4px; text-align: center; background-color: #f9fafb; }
        .biaya-label { font-size: 7px; color: #6b7280; text-transform: uppercase; margin-bottom: 3px; }
        .biaya-value { font-size: 10px; font-weight: bold; color: #1f2937; }
        table.data-table { width: 100%; border-collapse: collapse; font-size: 8.5px; page-break-inside: auto; }
        table.data-table thead { background-color: #f3f4f6; display: table-header-group; }
        table.data-table tfoot { display: table-footer-group; }
        table.data-table tr { page-break-inside: avoid; }
        table.data-table th { padding: 6px 5px; text-align: left; font-weight: bold; color: #374151; border-bottom: 1px solid #d1d5db; font-size: 7.5px; text-transform: uppercase; }
        table.data-table th.text-center { text-align: center; }
        table.data-table th.text-right { text-align: right; }
        table.data-table td { padding: 5px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        table.data-table tbody tr:nth-child(even) { background-color: #fafafa; }
        table.data-table tfoot td { background-color: #f3f4f6; font-weight: bold; padding: 6px 5px; border-top: 2px solid #d1d5db; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-green { color: #059669; font-weight: bold; }
        .text-red { color: #dc2626; font-weight: bold; }
        .text-orange { color: #ea580c; }
        .text-blue { color: #2563eb; font-weight: bold; }
        .text-gray { color: #6b7280; }
        .badge { display: inline-block; padding: 2px 4px; font-size: 6.5px; border-radius: 2px; font-weight: bold; text-transform: uppercase; }
        .badge-green { background-color: #d1fae5; color: #065f46; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .sku-tag { font-family: 'Courier New', monospace; font-size: 7.5px; background-color: #f3f4f6; padding: 2px 4px; border-radius: 2px; color: #374151; }
        .footer { position: fixed; bottom: 8px; left: 0; right: 0; padding: 0 25px; font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 5px; }
        .footer-content { display: table; width: 100%; }
        .footer-left { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
        .empty-state { text-align: center; padding: 30px; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>

{{-- FOOTER --}}
<div class="footer">
    <div class="footer-content">
        <div class="footer-left">Dicetak oleh <strong>BukuProfit</strong> pada {{ $generated_at }}</div>
        <div class="footer-right">Halaman <span class="pagenum"></span></div>
    </div>
</div>

{{-- HEADER --}}
<div class="header">
    <div class="header-top">
        <div class="header-left">
            <div class="header-title">Laporan Penjualan TikTok</div>
            <div class="header-subtitle">{{ $store?->nama_toko ?? 'Semua Toko' }}</div>
        </div>
        <div class="header-right">
            <div>
                <strong>Periode:</strong>
                @if($from && $to)
                    {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                @elseif($from)
                    Sejak {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
                @elseif($to)
                    Hingga {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                @else
                    Semua Periode
                @endif
            </div>
            <div><strong>Dicetak:</strong> {{ $generated_at }}</div>
            @if($search)
                <div><strong>Pencarian:</strong> "{{ $search }}"</div>
            @endif
        </div>
    </div>
</div>

{{-- SECTION 1 --}}
<div class="section keep-together">
    <div class="section-title">1. Ringkasan Keuangan</div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-card-label">Total Transaksi</div>
            <div class="summary-card-value gray">{{ number_format($summary['total_rows']) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Net Revenue</div>
            <div class="summary-card-value blue">Rp {{ number_format($summary['net_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Total HPP</div>
            <div class="summary-card-value orange">Rp {{ number_format($summary['total_hpp'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Profit</div>
            <div class="summary-card-value green">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="summary-grid" style="margin-top: 5px;">
        <div class="summary-card">
            <div class="summary-card-label">SKU Ter-mapping</div>
            <div class="summary-card-value gray">{{ $summary['sku_mapped'] }} / {{ $summary['sku_total'] }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Transaksi Matched</div>
            <div class="summary-card-value gray">{{ $summary['transaksi_matched'] }} / {{ $summary['total_transaksi'] }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Rata-rata Profit / SKU</div>
            <div class="summary-card-value blue">
                @if($summary['sku_mapped'] > 0)
                    Rp {{ number_format($summary['profit'] / $summary['sku_mapped'], 0, ',', '.') }}
                @else
                    -
                @endif
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Total Qty Terjual</div>
            <div class="summary-card-value gray">{{ number_format($perProduk->sum('total_qty')) }} unit</div>
        </div>
    </div>
</div>

{{-- SECTION 2 --}}
<div class="section keep-together">
    <div class="section-title">2. Breakdown Biaya Platform</div>
    <div class="biaya-grid">
        <div class="biaya-item"><div class="biaya-label">Komisi</div><div class="biaya-value">{{ number_format($summary['biaya_komisi'], 0, ',', '.') }}</div></div>
        <div class="biaya-item"><div class="biaya-label">Layanan</div><div class="biaya-value">{{ number_format($summary['biaya_layanan'], 0, ',', '.') }}</div></div>
        <div class="biaya-item"><div class="biaya-label">Ongkir</div><div class="biaya-value">{{ number_format($summary['biaya_ongkir'], 0, ',', '.') }}</div></div>
        <div class="biaya-item"><div class="biaya-label">Afiliasi</div><div class="biaya-value">{{ number_format($summary['biaya_afiliasi'], 0, ',', '.') }}</div></div>
        <div class="biaya-item"><div class="biaya-label">Pajak</div><div class="biaya-value">{{ number_format($summary['biaya_pajak'], 0, ',', '.') }}</div></div>
        <div class="biaya-item"><div class="biaya-label">Lainnya</div><div class="biaya-value">{{ number_format($summary['biaya_lainnya'], 0, ',', '.') }}</div></div>
    </div>
</div>

{{-- SECTION 3 --}}
<div class="section allow-break">
    <div class="section-title">3. Profit per SKU Varian ({{ $perProduk->count() }} SKU)</div>

    @if($perProduk->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 140px;">SKU Varian</th>
                <th>Produk Internal</th>
                <th style="width: 35px;" class="text-center">Qty</th>
                <th style="width: 75px;" class="text-right">Net Revenue</th>
                <th style="width: 65px;" class="text-right">HPP</th>
                <th style="width: 75px;" class="text-right">Profit</th>
                <th style="width: 50px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($perProduk as $idx => $p)
            <tr>
                <td class="text-center text-gray">{{ $idx + 1 }}</td>
                <td><span class="sku-tag">{{ $p['marketplace_sku'] }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($p['nama_produk'], 55, '…') }}</td>
                <td class="text-center">{{ number_format($p['total_qty']) }}</td>
                <td class="text-right">{{ number_format($p['net_revenue'], 0, ',', '.') }}</td>
                <td class="text-right text-orange">{{ number_format($p['hpp_total'], 0, ',', '.') }}</td>
                <td class="text-right {{ $p['profit'] > 0 ? 'text-green' : 'text-red' }}">{{ number_format($p['profit'], 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($p['mapped'])
                        <span class="badge badge-green">Mapped</span>
                    @else
                        <span class="badge badge-red">Belum</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-center">{{ number_format($perProduk->sum('total_qty')) }}</td>
                <td class="text-right text-blue">Rp {{ number_format($perProduk->sum('net_revenue'), 0, ',', '.') }}</td>
                <td class="text-right text-orange">Rp {{ number_format($perProduk->sum('hpp_total'), 0, ',', '.') }}</td>
                <td class="text-right text-green">Rp {{ number_format($perProduk->sum('profit'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
        <div class="empty-state">Belum ada data SKU untuk ditampilkan.</div>
    @endif
</div>

</body>
</html>