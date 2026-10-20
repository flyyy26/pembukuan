<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Piutang - BukuBarokah</title>

    <style>
        @page {
            margin: 28px 32px;
        }

        @font-face {
            font-family: 'Geist';
            font-style: normal;
            font-weight: normal;
            src: url('{{ url("fonts/Geist-Regular.ttf") }}') format('truetype');
        }

        @font-face {
            font-family: 'Geist';
            font-style: normal;
            font-weight: bold;
            src: url('{{ url("fonts/Geist-Bold.ttf") }}') format('truetype');
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Geist', sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #1c1c27;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* HEADER */
        .header-table {
            margin-bottom: 18px;
            border-bottom: 3px solid #030164;
        }

        .header-table td {
            padding: 0 0 14px;
            vertical-align: top;
        }

        .brand-name {
            font-size: 22px;
            font-weight: bold;
            color: #030164;
            margin-bottom: 3px;
        }

        .brand-subtitle {
            font-size: 9px;
            color: #64748b;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #030164;
            text-align: right;
        }

        .invoice-number {
            font-size: 10px;
            font-weight: bold;
            text-align: right;
            color: #334155;
            margin-top: 4px;
        }

        .invoice-date {
            font-size: 9px;
            color: #64748b;
            text-align: right;
            margin-top: 3px;
        }

        /* INFORMATION */
        .info-table {
            margin-bottom: 20px;
        }

        .info-table td {
            width: 33.33%;
            padding: 0 6px;
            vertical-align: top;
        }

        .info-table td:first-child {
            padding-left: 0;
        }

        .info-table td:last-child {
            padding-right: 0;
        }

        .info-card {
            border: 1px solid #dbe5e1;
            background-color: #fbfbfd;
            padding: 9px 10px;
        }

        .info-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 10px;
            font-weight: bold;
            color: #1e293b;
        }

        /* SECTION TITLE */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 8px;
        }

        /* TRANSACTION TABLE */
        .items-table {
            border: 1px solid #dce5e1;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table thead th {
            background-color: #030164;
            color: #ffffff;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 10px 7px;
            border: 1px solid #030164;
            text-align: left;
        }

        .items-table tbody td {
            padding: 9px 7px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .items-table tbody tr {
            page-break-inside: avoid;
        }

        .col-no {
            width: 5%;
            text-align: center !important;
        }

        .col-invoice {
            width: 19%;
        }

        .col-date {
            width: 13%;
        }

        .col-description {
            width: 28%;
        }

        .col-category {
            width: 15%;
        }

        .col-amount {
            width: 20%;
            text-align: right !important;
        }

        .invoice-code {
            font-size: 8px;
            color: #475569;
        }

        .category {
            display: inline-block;
            padding: 3px 6px;
            background-color: #f4f4fd;
            color: #030164;
            border: 1px solid #03016480;
            font-size: 8px;
            font-weight: bold;
        }

        .amount {
            font-weight: bold;
            white-space: nowrap;
            color: #1e293b;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* TOTAL */
        .total-table {
            width: 100%;
            margin-top: 15px;
        }

        .total-table td {
            vertical-align: middle;
        }

        .total-caption {
            font-size: 9px;
            color: #64748b;
            padding-right: 15px;
        }

        .total-box {
            width: 55%;
            margin-left: auto;
            border: 1px solid #030164;
            background-color: #f4f4fd;
        }

        .total-box td {
            padding: 12px;
        }

        .total-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #030164;
        }

        .total-value {
            font-size: 16px;
            font-weight: bold;
            color: #030164;
            text-align: right;
            white-space: nowrap;
        }

        /* SIGNATURE */
        .signature-table {
            margin-top: 42px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-title {
            font-size: 9px;
            color: #475569;
        }

        .signature-space {
            height: 58px;
        }

        .signature-name {
            font-size: 9px;
            font-weight: bold;
            color: #334155;
        }

        .signature-rule {
            width: 72%;
            margin: 0 auto;
            padding-top: 5px;
        }

        /* FOOTER */
        .footer-table {
            margin-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .footer-table td {
            padding-top: 8px;
            font-size: 8px;
            color: #94a3b8;
        }

        .footer-right {
            text-align: right;
        }

        .empty-state {
            padding: 20px;
            text-align: center;
            color: #64748b;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="brand-name">
                    {{ $store?->nama_toko ?? 'BukuBarokah' }}
                </div>
                <div class="brand-subtitle">
                    SISTEM PEMBUKUAN BRILink
                </div>
            </td>

            <td style="width: 45%;">
                <div class="invoice-title">INVOICE PIUTANG</div>

                <div class="invoice-number">
                    @if($mode === 'single')
                        {{ $items[0]['no_invoice'] ?? '-' }}
                    @else
                        GABUNGAN {{ count($items) }} TRANSAKSI
                    @endif
                </div>

                <div class="invoice-date">
                    Tanggal cetak: {{ $tanggalCetak }}
                </div>
            </td>
        </tr>
    </table>

    {{-- INFORMASI --}}
    <table class="info-table">
        <tr>
            <td>
                <div class="info-card">
                    <div class="info-label">Nama Toko</div>
                    <div class="info-value">
                        {{ $store?->nama_toko ?? 'Semua Toko' }}
                    </div>
                </div>
            </td>

            <td>
                <div class="info-card">
                    <div class="info-label">Jumlah Transaksi</div>
                    <div class="info-value">
                        {{ count($items) }} Piutang
                    </div>
                </div>
            </td>

            <td>
                <div class="info-card">
                    <div class="info-label">Status Dokumen</div>
                    <div class="info-value">TAGIHAN PIUTANG</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- DAFTAR PIUTANG --}}
    <div class="section-title">Rincian Transaksi Piutang</div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="col-no">No</th>

                @if($mode !== 'single')
                    <th class="col-invoice">No. Invoice</th>
                @endif

                <th class="col-date">Tanggal</th>
                <th class="col-description">Keterangan</th>
                <th class="col-category">Kategori</th>
                <th class="col-amount">Nominal (Rp)</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $i => $row)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>

                    @if($mode !== 'single')
                        <td class="invoice-code">
                            {{ $row['no_invoice'] ?? '-' }}
                        </td>
                    @endif

                    <td>{{ $row['tanggal'] ?? '-' }}</td>

                    <td>{{ $row['keterangan'] ?? '-' }}</td>

                    <td>
                        <span class="category">
                            {{ $row['kategori'] ?? '-' }}
                        </span>
                    </td>

                    <td class="col-amount amount">
                        {{ number_format($row['nominal'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $mode === 'single' ? 5 : 6 }}"
                        class="empty-state">
                        Tidak ada data piutang.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTAL --}}
    <table class="total-table">
        <tr>
            <td style="width: 45%;">
                <div class="total-caption">
                    Harap periksa kembali rincian transaksi sebelum
                    melakukan pembayaran atau penandatanganan.
                </div>
            </td>

            <td style="width: 55%;">
                <table class="total-box">
                    <tr>
                        <td>
                            <div class="total-label">Total Piutang</div>
                        </td>
                        <td class="total-value">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-title">Penerima / Pemilik Toko</div>
                <div class="signature-space"></div>
                <div class="signature-rule">
                    <div class="signature-name">
                        (........................................)
                    </div>
                </div>
            </td>

            <td>
                <div class="signature-title">Pihak yang Berhutang</div>
                <div class="signature-space"></div>
                <div class="signature-rule">
                    <div class="signature-name">
                        (........................................)
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <table class="footer-table">
        <tr>
            <td style="width: 60%;">
                Dokumen dibuat otomatis oleh BukuBarokah.
            </td>
            <td class="footer-right" style="width: 40%;">
                Dicetak pada {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>