<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Zakat</title>
    <style>
        @page { margin: 24px 28px; }
        * { box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 10px; color: #1c1c27; }
        table { width: 100%; border-collapse: collapse; }
        .header { border-bottom: 3px solid #047857; padding-bottom: 10px; margin-bottom: 14px; }
        .header td { vertical-align: top; }
        .brand { font-size: 18px; font-weight: bold; color: #047857; }
        .brand-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .title { font-size: 16px; font-weight: bold; text-align: right; color: #1e293b; }
        .title-sub { font-size: 9px; text-align: right; color: #64748b; margin-top: 2px; }
        .section-title { font-size: 11px; font-weight: bold; color: #1e293b; margin: 14px 0 6px; }
        .info td { padding: 3px 0; font-size: 10px; }
        .info td:first-child { width: 30%; color: #64748b; }
        .info td:last-child { font-weight: bold; }
        .items th { background: #047857; color: #fff; padding: 8px 6px; font-size: 9px; text-align: left; }
        .items td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        .items .text-right { text-align: right; }
        .items .text-center { text-align: center; }
        .total-row td { background: #ecfdf5; font-weight: bold; }
        .grand { background: #047857; color: #fff; padding: 12px; text-align: right; margin-top: 16px; }
        .grand-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        .grand-value { font-size: 18px; font-weight: bold; }
        .footer { margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 8px; color: #94a3b8; text-align: center; }
        .signature { margin-top: 30px; }
        .signature td { width: 50%; text-align: center; vertical-align: top; font-size: 10px; color: #475569; }
        .signature-space { height: 50px; }
    </style>
</head>
<body>

{{-- ============ HEADER ============ --}}
<table class="header">
    <tr>
        <td>
            <div class="brand">BukuBarokah</div>
            <div class="brand-sub">LAPORAN ZAKAT</div>
        </td>
        <td>
            <div class="title">LAPORAN ZAKAT</div>
            <div class="title-sub">Dicetak: {{ $tanggalCetak }}</div>
        </td>
    </tr>
</table>

<table class="info">
    <tr>
        <td>Toko (Sumber Saldo & Piutang)</td>
        <td>: {{ $store?->nama_toko ?? 'Semua Toko' }}</td>
    </tr>
    <tr>
        <td>Rate Zakat</td>
        <td>: 2,5%</td>
    </tr>
</table>

{{-- ============ BAGIAN A: ZAKAT SALDO ============ --}}
<div class="section-title">A. Zakat dari Saldo</div>

@if(!empty($breakdownSaldo))
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 50%;">Rekening</th>
                <th style="width: 22%;" class="text-right">Saldo Saat Ini</th>
                <th style="width: 23%;" class="text-right">Zakat (2,5%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($breakdownSaldo as $i => $b)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $b['nama_rekening'] }}</td>
                    <td class="text-right">Rp {{ number_format($b['saldo'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($b['zakat'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @foreach($saldoOnlineList as $so)
                <tr>
                    <td class="text-center">{{ count($breakdownSaldo) + $loop->iteration }}</td>
                    <td>{{ $so->marketplace }} (Online)</td>
                    <td class="text-right">Rp {{ number_format($so->saldo, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format(round($so->saldo * 0.025), 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@else
    <table class="info">
        <tr>
            <td>Total Saldo Aset Likuid</td>
            <td>: Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Zakat Saldo (2,5%)</td>
            <td>: Rp {{ number_format($zakatSaldo, 0, ',', '.') }}</td>
        </tr>
    </table>
@endif

{{-- ============ BAGIAN B: ZAKAT PIUTANG ============ --}}
<div class="section-title">B. Zakat dari Piutang</div>

@if(!empty($piutangList))
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 38%;">Keterangan</th>
                <th style="width: 14%;" class="text-right">Piutang</th>
                <th style="width: 14%;" class="text-right">Terbayar</th>
                <th style="width: 14%;" class="text-right">Sisa</th>
                <th style="width: 15%;" class="text-right">Zakat (2,5%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($piutangList as $i => $p)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $p['keterangan'] }}</td>
                    <td class="text-right">Rp {{ number_format($p['nominal'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p['terbayar'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p['sisa'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p['zakat'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($zakatPiutang, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@else
    <div style="padding: 10px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1;">
        Tidak ada piutang belum lunas.
    </div>
@endif

{{-- ============ BAGIAN C: ZAKAT BARANG (MASTER PRODUK) ============ --}}
<div class="section-title">C. Zakat dari Barang Online</div>

@if($barangList->isEmpty())
    <div style="padding: 10px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1;">
        Belum ada data barang online.
    </div>
@else
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Nama Kategori</th>
                <th style="width: 10%;" class="text-left">Prefix</th>
                <th style="width: 10%;" class="text-center">Stok</th>
                <th style="width: 20%;" class="text-right">HPP</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($barangList as $b)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $b['nama_barang'] }}</td>
                    <td>{{ $b['sku_prefix'] ?? '-' }}</td>
                    <td class="text-center">{{ number_format($b['qty'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($b['harga_satuan'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($b['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">Total Nilai Barang</td>
                <td class="text-right">Rp {{ number_format($totalBarang, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="5" class="text-right">Zakat Barang (2,5%)</td>
                <td class="text-right">Rp {{ number_format($zakatBarang, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endif

{{-- ============ BAGIAN D: PENGURANG (HUTANG & TABUNGAN) ============ --}}
<div class="section-title">D. Pengurang: Hutang & Tabungan</div>

<table class="items">
    <thead>
        <tr>
            <th style="width: 50%;">Keterangan</th>
            <th style="width: 25%;" class="text-right">Nominal</th>
            <th style="width: 25%;" class="text-right">Zakat (2,5%)</th>
        </tr>
    </thead>
    <tbody>
        {{-- HUTANG --}}
        @forelse($hutangList as $h)
            <tr>
                <td>Hutang: {{ $h->nama_pemberi_hutang }} ({{ $h->tanggal->format('d/m/Y') }})</td>
                <td class="text-right">Rp {{ number_format($h->nominal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format(round($h->nominal * 0.025), 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">Tidak ada hutang.</td>
            </tr>
        @endforelse

        {{-- TABUNGAN --}}
        @forelse($tabunganList as $t)
            <tr>
                <td>Tabungan: {{ $t['nama'] }}</td>
                <td class="text-right">Rp {{ number_format($t['saldo'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format(round($t['saldo'] * 0.025), 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">Tidak ada tabungan.</td>
            </tr>
        @endforelse
        
        <tr class="total-row" style="background-color: #fef2f2; color: #991b1b;">
            <td class="text-right">Total Pengurang</td>
            <td class="text-right">Rp {{ number_format($totalHutang + $totalTabungan, 0, ',', '.') }}</td>
            <td class="text-right">- Rp {{ number_format($zakatHutang + $zakatTabungan, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

{{-- ============ GRAND TOTAL ============ --}}
<div style="margin-top: 16px;">
    
    <table class="items" style="margin-bottom: 8px; border: none;">
        <tbody>
            <tr>
                <td style="width: 70%; text-align: right; font-size: 11px; border: none; padding-bottom: 4px;">Subtotal Zakat Aset (Saldo + Piutang + Barang)</td>
                <td style="width: 30%; text-align: right; font-size: 11px; border: none; font-weight: bold; padding-bottom: 4px;">Rp {{ number_format($zakatSaldo + $zakatPiutang + $zakatBarang, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="text-align: right; font-size: 11px; border: none; color: #b91c1c; padding-bottom: 8px;">(-) Zakat Pengurang (Hutang & Tabungan)</td>
                <td style="text-align: right; font-size: 11px; border: none; font-weight: bold; color: #b91c1c; padding-bottom: 8px;">- Rp {{ number_format($zakatHutang + $zakatTabungan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="grand" style="margin-top: 0;">
        <div class="grand-label">Total Zakat Akhir (2,5%)</div>
        <div class="grand-value">Rp {{ number_format($totalZakat, 0, ',', '.') }}</div>
    </div>
</div>

{{-- ============ TANDA TANGAN ============ --}}
<table class="signature">
    <tr>
        <td>
            <div>Petugas / Amil</div>
            <div class="signature-space"></div>
            <div>(........................................)</div>
        </td>
        <td>
            <div>Pemilik / Muzakki</div>
            <div class="signature-space"></div>
            <div>(........................................)</div>
        </td>
    </tr>
</table>

<div class="footer">
    Dokumen dibuat otomatis oleh BukuBarokah
</div>

</body>
</html>