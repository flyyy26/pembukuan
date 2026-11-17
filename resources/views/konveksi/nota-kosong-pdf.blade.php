@php
    // ✅ SOLUSI HOSTING: Gunakan URL asset()
    $logoUrl = asset('images/favicon.png');
    $tiktokUrl = asset('images/tiktok.png');
    $shopeeUrl = asset('images/shopee.png');
    $whatsappUrl = asset('images/whatsapp.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pesanan - Barokah Sport</title>

    <style>
        @page { size: A4 portrait; margin: 4mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #000; margin: 0; padding: 0; }
        
        .wrapper-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
        .wrapper-table td { width: 50%; vertical-align: top; }
        .wrapper-table td:first-child { padding: 0 4mm 0 0; border-right: 1px dashed #999; }
        .wrapper-table td:last-child { padding: 0 0 0 4mm; }

        .nota-container { width: 100%; position: relative; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .header-text h2 { font-size: 19px; margin: 0 0 3px 0; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 3px; }
        .header-text p { font-size: 9px; margin: 2px 0; line-height: 1.3; }
        .logo { max-width: 85px; max-height: 65px; }
        
        .nota-title { text-align: center; font-weight: bold; font-size: 13px; margin: 10px 0 8px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; border: 1px solid #333; }
        .info-table td { border: none; padding: 3px 4px; font-size: 10px; }
        .info-table td:first-child { width: 105px; }

        .barang-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 5px; }
        .barang-table th, .barang-table td { border: 1px solid #333; padding: 4px 3px; font-size: 9px; }
        .barang-table th { background: #ddd; text-align: center; font-weight: bold; }
        .barang-table td { height: 16px; vertical-align: middle; }
        .barang-table td.center { text-align: center; }
        .barang-table td.right { text-align: right; }

        .footer-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .footer-table td { border: 1px solid #333; padding: 4px; font-size: 9px; }
        .footer-table .label { font-weight: bold; background: #ddd; }
        .footer-table .right { text-align: right; }

        .signature-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .signature-table td { font-size: 9px; text-align: center; vertical-align: bottom; padding: 0 2px; }
        .signature-space { height: 38px; }
        .signature-name { white-space: nowrap; }

        .stamp { position: absolute; top: 105px; right: 15px; font-size: 25px; font-weight: bold; border: 2px solid; border-radius: 5px; padding: 0 7px; transform: rotate(-8deg); opacity: 0.8; z-index: 5; }
        .stamp.asli { color: #d00000; border-color: #d00000; }
        .stamp.copy { color: #999; border-color: #999; }

        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>

@php $copies = ['ASLI', 'COPY']; @endphp

<table class="wrapper-table">
    <tr>
        @foreach ($copies as $copy)
        <td>
            <div class="nota-container">
                <div class="stamp {{ strtolower($copy) }}">{{ $copy }}</div>

                <table class="header-table">
                    <tr>
                        <td class="header-text" style="width: 75%; border:none;">
                            <h2>BAROKAH SPORT</h2>
                            <p>Jl. Kebon Kalapa RT. 01, RW. 03, Kelurahan Mugarsari</p>
                            <p>Kecamatan Tamansari Tasikmalaya Jawa Barat - Indonesia 46196</p>
                            <p style="margin-top: 4px;">
                                <img src="{{ $tiktokUrl }}" alt="" style="width:7pt; margin-top:7pt;"> barokah.sport &nbsp;
                                <img src="{{ $shopeeUrl }}" alt="" style="width:7pt; margin-top:7pt;"> hmbarokah &nbsp;
                                <img src="{{ $whatsappUrl }}" alt="" style="width:7pt; margin-top:7pt;"> 081381899600
                            </p>
                        </td>
                        <td style="width: 25%; text-align: right;">
                            <img src="{{ $logoUrl }}" class="logo" alt="Logo Barokah Sport" style="margin-right:17px;">
                        </td>
                    </tr>
                </table>

                <div class="nota-title">NOTA PESANAN</div>

                <table class="info-table">
                    <tr>
                        <td style="border-right:none; padding:7px; padding-bottom:0;">Tanggal</td>
                        <td style="padding:7px; padding-bottom:0;">: {{ $tanggal }}</td>
                    </tr>
                    <tr>
                        <td style="border-right:none; padding:7px; padding-top:0;">Nama</td>
                        <td style="padding:7px; padding-top:0;">: {{ $nama }}</td>
                    </tr>
                </table>

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
                        {{-- ✅ PERBAIKAN: Gunakan @forelse dengan benar --}}
                        @forelse($items as $item)
                            <tr>
                                <td style="padding:7px;">{{ $item['nama_produk'] ?? '-' }}</td>
                                <td class="center" style="padding:7px;">{{ $item['warna'] ?? '-' }}</td>
                                <td class="center" style="padding:7px;">{{ $item['ukuran'] ?? '-' }}</td>
                                <td class="center" style="padding:7px;">{{ $item['qty'] }}</td>
                                <td class="right" style="padding:7px;">{{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td class="right" style="padding:7px;">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            {{-- Jika tidak ada item, tampilkan 5 baris kosong --}}
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                    <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td>
                                </tr>
                            @endfor
                        @endforelse

                        {{-- ✅ Sisa baris kosong jika item kurang dari 5 --}}
                        @if(count($items) > 0 && count($items) < 5)
                            @for ($i = 0; $i < (5 - count($items)); $i++)
                                <tr>
                                    <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>

                <table class="footer-table">
                    <tr>
                        <td class="label" style="padding:7px; width: 60%;">TOTAL PESANAN</td>
                        <td class="right" style="padding:7px; width: 40%;">
                            <strong>{{ number_format($total_pesanan, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </table>

                <table class="signature-table">
                    <tr>
                        <td width="50%">
                            Disetujui Oleh,
                            <div class="signature-space"></div>
                            <div class="signature-name">( Barokah Sport )</div>
                        </td>
                        <td width="50%">
                            Yang Menerima,
                            <div class="signature-space"></div>
                            <div class="signature-name">( {{ $nama }} )</div>
                        </td>
                    </tr>
                </table>

            </div>
        </td>
        @endforeach
    </tr>
</table>

</body>
</html>