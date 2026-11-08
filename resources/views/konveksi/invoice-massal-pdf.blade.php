<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pembayaran Massal - Barokah Sport</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 4mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* PAGE BREAK ANTAR KARYAWAN */
        .page-break {
            page-break-after: always;
        }
        .page-break:last-child {
            page-break-after: auto;
        }

        /* PEMBAGIAN KERTAS MENJADI 2 NOTA */
        .wrapper-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .wrapper-table td {
            width: 50%;
            vertical-align: top;
        }
        .wrapper-table td:first-child {
            padding: 0 4mm 0 0;
            border-right: 1px dashed #999;
        }
        .wrapper-table td:last-child {
            padding: 0 0 0 4mm;
        }

        /* CONTAINER NOTA */
        .nota-container {
            width: 100%;
            position: relative;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .header-text h2 {
            font-size: 19px;
            margin: 0 0 3px 0;
            font-weight: bold;
            border-bottom: 2px solid #333;
            padding-bottom: 3px;
        }
        .header-text p {
            font-size: 9px;
            margin: 2px 0;
            line-height: 1.3;
        }
        .logo {
            max-width: 85px;
            max-height: 65px;
        }

        /* JUDUL NOTA */
        .nota-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 10px 0 8px;
        }

        /* INFORMASI PEMBAYARAN */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border: 1px solid #333;
        }
        .info-table td {
            border: none;
            padding: 3px 4px;
            font-size: 10px;
        }
        .info-table td:first-child {
            width: 105px;
        }

        /* TABEL BARANG */
        .barang-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 5px;
        }
        .barang-table th,
        .barang-table td {
            border: 1px solid #333;
            padding: 4px 3px;
            font-size: 9px;
        }
        .barang-table th {
            background: #ddd;
            text-align: center;
            font-weight: bold;
        }
        .barang-table td {
            height: 16px;
            vertical-align: middle;
        }
        .barang-table td.center { text-align: center; }
        .barang-table td.right { text-align: right; }

        /* FOOTER PEMOTONGAN DAN TOTAL */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .footer-table td {
            border: 1px solid #333;
            padding: 4px;
            font-size: 9px;
        }
        .footer-table .label {
            font-weight: bold;
            background: #ddd;
        }
        .footer-table .right { text-align: right; }

        /* TANDA TANGAN */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .signature-table td {
            font-size: 9px;
            text-align: center;
            vertical-align: bottom;
            padding: 0 2px;
        }
        .signature-space { height: 38px; }
        .signature-name { white-space: nowrap; }

        /* CAP ASLI DAN COPY */
        .stamp {
            position: absolute;
            top: 105px;
            right: 15px;
            font-size: 25px;
            font-weight: bold;
            border: 2px solid;
            border-radius: 5px;
            padding: 0 7px;
            transform: rotate(-8deg);
            opacity: 0.8;
            z-index: 5;
        }
        .stamp.asli { color: #d00000; border-color: #d00000; }
        .stamp.copy { color: #999; border-color: #999; }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

@php
    // ✅ SOLUSI HOSTING: Gunakan URL asset() agar mengambil dari folder public_html/buku/
    $logoUrl = asset('images/favicon.png');
    $tiktokUrl = asset('images/tiktok.png');
    $shopeeUrl = asset('images/shopee.png');
    $whatsappUrl = asset('images/whatsapp.png');

    $copies = ['ASLI', 'COPY'];
@endphp

@foreach ($groupedPembayarans as $key => $items)
    <div class="page-break">
        @php
            // Ambil data karyawan & tanggal dari item pertama
            $firstItem = $items->first();
            $karyawan = $firstItem->hanca->karyawan;
            $tanggalBayar = $firstItem->tanggal;
            
            // ✅ Panggil variabel yang sudah dihitung di Controller
            $totalNominal = $items->total_kotor;
            $totalKasbon = $items->total_kasbon;
        @endphp

        <table class="wrapper-table">
            <tr>
                @foreach ($copies as $copy)
                <td>
                    <div class="nota-container">
                        {{-- CAP NOTA --}}
                        <div class="stamp {{ strtolower($copy) }}">
                            {{ $copy }}
                        </div>

                        {{-- HEADER --}}
                        <table class="header-table">
                            <tr>
                                <td class="header-text" style="width: 75%; border:none;">
                                    <h2>BAROKAH SPORT</h2>
                                    <p>Jl. Kebon Kalapa RT. 01, RW. 03, Kelurahan Mugarsari</p>
                                    <p>Kecamatan Tamansari Tasikmalaya Jawa Barat - Indonesia 46196</p>
                                    <p style="margin-top: 4px;">
                                        {{-- ✅ Gunakan URL langsung --}}
                                        <img src="{{ $tiktokUrl }}" alt="" style="width:7pt; margin-top:7pt;"> barokah.sport &nbsp;
                                        <img src="{{ $shopeeUrl }}" alt="" style="width:7pt; margin-top:7pt;"> hmbarokah &nbsp;
                                        <img src="{{ $whatsappUrl }}" alt="" style="width:7pt; margin-top:7pt;"> 081381899600
                                    </p>
                                </td>
                                <td style="width: 25%; text-align: right;">
                                    {{-- ✅ Gunakan URL langsung --}}
                                    <img src="{{ $logoUrl }}" class="logo" alt="Logo Barokah Sport" style="margin-right:17px;">
                                </td>
                            </tr>
                        </table>

                        {{-- JUDUL --}}
                        <div class="nota-title">
                            NOTA PEMBAYARAN
                        </div>

                        {{-- INFORMASI --}}
                        <table class="info-table">
                            <tr>
                                <td style="border-right:none; padding:7px; padding-bottom:0;">Tanggal dibayar</td>
                                <td style="padding:7px; padding-bottom:0;">
                                    : {{ $tanggalBayar->format('d/m/Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border-right:none; padding:7px; padding-top:0;">Nama</td>
                                <td style="padding:7px; padding-top:0;">
                                    : {{ $karyawan->nama ?? '-' }}
                                </td>
                            </tr>
                        </table>

                        {{-- TABEL BARANG (LOOPING DARI GRUP) --}}
                        <table class="barang-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Nama Barang</th>
                                    <th style="width: 15%;">Warna</th>
                                    <th style="width: 10%;">Size</th>
                                    <th style="width: 10%;">Qty</th>
                                    <th style="width: 15%;">Harga</th>
                                    <th style="width: 20%;">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $p)
                                    <tr>
                                        <td style="padding:7px;">{{ $p->hancaDetail->nama_produk ?? '-' }}</td>
                                        <td class="center" style="padding:7px;">{{ $p->hancaDetail->warna ?? '-' }}</td>
                                        <td class="center" style="padding:7px;">{{ $p->hancaDetail->ukuran ?? '-' }}</td>
                                        <td class="center" style="padding:7px;">{{ $p->qty_setor }}</td>
                                        <td class="right" style="padding:7px;">{{ number_format($p->hancaDetail->harga ?? 0, 0, ',', '.') }}</td>
                                        <td class="right" style="padding:7px;">{{ number_format($p->nominal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach

                                {{-- BARIS KOSONG MINIMAL 5 BARIS --}}
                                @for ($i = 0; $i < max(0, 5 - $items->count()); $i++)
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>

                        {{-- TOTAL DAN PEMOTONGAN --}}
                        <table class="footer-table">
                            <tr>
                                <td style="width: 60%; padding:7px;">Bayaran Hanca</td>
                                <td class="right" style="width: 40%; padding:7px;">
                                    {{ number_format($totalNominal, 0, ',', '.') }}
                                </td>
                            </tr>
                            
                            @if($totalKasbon > 0)
                            <tr>
                                <td style="padding:7px;">Potong Kasbon</td>
                                <td class="right" style="padding:7px; color: red;">
                                    - {{ number_format($totalKasbon, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <td class="label" style="padding:7px;">Total Diterima</td>
                                <td class="right" style="padding:7px;">
                                    <strong>{{ number_format($totalNominal - $totalKasbon, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </table>

                        {{-- TANDA TANGAN --}}
                        <table class="signature-table">
                            <tr>
                                <td width="50%">
                                    Disetujui Oleh,
                                    <div class="signature-space"></div>
                                    <div class="signature-name">( Admin Gudang )</div>
                                </td>
                                <td width="50%">
                                    Yang Menerima,
                                    <div class="signature-space"></div>
                                    <div class="signature-name">( {{ $karyawan->nama ?? 'Penerima' }} )</div>
                                </td>
                            </tr>
                        </table>

                    </div>
                </td>
                @endforeach
            </tr>
        </table>
    </div>
@endforeach

</body>
</html>