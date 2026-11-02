@php
    $logoPath = public_path('images/favicon.png');
    $tiktokPath = public_path('images/tiktok.png');
    $shopeePath = public_path('images/shopee.png');
    $whatsappPath = public_path('images/whatsapp.png');

    $logoBase64 = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;
    
    $tiktokBase64 = file_exists($tiktokPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($tiktokPath))
        : null;
    $shopeeBase64 = file_exists($shopeePath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($shopeePath))
        : null;
    $whatsappBase64 = file_exists($whatsappPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($whatsappPath))
        : null;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota - Barokah Sport</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 4mm; /* Margin kecil agar muat 2 kolom */
        }

        * {
            box-sizing: border-box;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            color: #000;
            background-color: #fff;
            position: relative;
            font-size: 10px;
        }

        /* WRAPPER UNTUK 2 NOTA */
        .wrapper-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .wrapper-table > tbody > tr > td {
            width: 50%;
            vertical-align: top;
        }
        .wrapper-table > tbody > tr > td:first-child {
            padding-right: 4mm;
            border-right: 1px dashed #999;
        }
        .wrapper-table > tbody > tr > td:last-child {
            padding-left: 4mm;
        }

        .nota-container {
            width: 100%;
            position: relative;
            /* min-height: 270mm; Memastikan tinggi sama */
        }

        /* CSS SPESIFIK DESAIN KASBON (DIPERKECIL AGAR MUAT 1/2 A4) */
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
            border-bottom: 2px solid #ecbc42;
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

        .logo-text h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            font-style: italic;
            color: #0b1a4a;
            letter-spacing: -1px;
        }

        .logo-text h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            font-style: italic;
            color: #0084d1;
            letter-spacing: 1px;
        }

        .logo-text p {
            margin: 2px 0 0 0;
            font-size: 8px;
            font-weight: bold;
            font-style: italic;
            color: #0b1a4a;
        }

        .header-right {
            border-left: 2px solid #000;
            padding-left: 10px;
            text-align: left;
        }

        .header-right span {
            font-size: 10px;
            font-weight: bold;
            vertical-align: middle;
            color: #0b1a4a;
        }

        .line-blue {
            height: 2px;
            background-color: #ecbc42;
            margin: 0;
        }

        .title-container {
            text-align: center;
            margin: 15px 0;
        }

        .nota-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 10px 0 8px;
        }

        .title-box {
            display: inline-block;
            background-color: #ecbc42;
            color: white;
            padding: 5px 20px;
            border-radius: 8px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .title-box h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        .title-box p {
            margin: 0;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .content {
            padding: 0 10px;
        }

        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .form-table td {
            padding: 4px 0;
            font-size: 12px;
            font-weight: 900;
            color: #000;
            vertical-align: middle;
        }

        .form-table td.label {
            width: 80px;
        }

        .form-table td.colon {
            width: 10px;
            text-align: center;
        }

        .input-box {
            border: 1px solid #000;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: bold;
            color: #000;
            display: block;
        }

        .textarea-box {
            border: 1px solid #000;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: normal;
            color: #000;
            height: 50px;
            display: block;
            resize: none;
        }

        .jumlah-wrapper {
            position: relative;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .jumlah-label {
            background-color: #000;
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 3px 15px;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            display: inline-block;
            position: relative;
            z-index: 2;
            margin-bottom: -2px;
            margin-left: 10px;
        }

        .jumlah-box {
            border: 1px solid #000;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 22px;
            font-weight: 900;
            color: #000;
            position: relative;
            z-index: 1;
        }

        .statement {
            font-size: 10px;
            color: #000;
            margin: 15px 0;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .signature-table td {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #000;
            vertical-align: bottom;
        }

        .signature-box {
            border: 1px solid #000;
            border-radius: 6px;
            width: 110px;
            height: 45px;
            margin: 5px auto;
        }

        .signature-name {
            margin-top: 2px;
        }

        /* FOOTER (Dibuat absolute di dalam container agar tidak menutupi nota copy) */
        .footer-wrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25px;
        }

        .footer-dark {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 35%;
            height: 100%;
            background-color: #000;
            z-index: 1;
        }

        /* STAMP ASLI/COPY */
        .stamp {
            position: absolute;
            top: 80px;
            right: 0;
            font-size: 30px;
            font-weight: bold;
            border: 2px solid;
            border-radius: 5px;
            padding: 0 5px;
            transform: rotate(-8deg);
            opacity: 0.8;
            z-index: 5;
        }

        .stamp.asli { color: #d00000; border-color: #d00000; }
        .stamp.copy { color: #999; border-color: #999; }
    </style>
</head>
<body>

@php
    $copies = ['ASLI', 'COPY'];
@endphp

<table class="wrapper-table">
    <tr>
        @foreach ($copies as $copy)
        <td>
            <div class="nota-container">
                
                <div class="stamp {{ strtolower($copy) }}">{{ $copy }}</div>

                {{-- HEADER --}}
                <table class="header-table">
                    <tr>
                        <td class="header-text" style="width: 75%; border:none;">

                            <h2>BAROKAH SPORT</h2>

                            <p>
                                Jl. Kebon Kalapa RT. 01, RW. 03, Kelurahan Mugarsari
                            </p>

                            <p>
                                Kecamatan Tamansari Tasikmalaya
                                Jawa Barat - Indonesia 46196
                            </p>

                            <p style="margin-top: 4px;">
                                <img src="{{ $tiktokBase64 }}" alt="" style="width:7pt; margin-top:7pt;"> barokah.sport &nbsp;
                                <img src="{{ $shopeeBase64 }}" alt="" style="width:7pt; margin-top:7pt;"> hmbarokah &nbsp;
                                <img src="{{ $whatsappBase64 }}" alt="" style="width:7pt; margin-top:7pt;"> 081381899600
                            </p>

                        </td>

                        <td style="width: 25%; text-align: right;">
                            {{-- Sesuaikan dengan lokasi logo --}}
                            @if ($logoBase64)
                                <img
                                    src="{{ $logoBase64 }}"
                                    class="logo"
                                    alt="Logo Barokah Sport"
                                    style="margin-right:17px;"
                                >
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="line-blue"></div>

                {{-- TITLE KASBON --}}
                <div class="nota-title">
                    {{ $judulNota }}
                </div>

                {{-- CONTENT --}}
                <div class="content">
                    <table class="form-table">
                        <tr>
                            <td class="label">{{ $labelNomor }}</td>
                            <td class="colon">:</td>
                            <td>
                                <div class="input-box">{{ $nomorNota }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal</td>
                            <td class="colon">:</td>
                            <td>
                                <div class="input-box">{{ $tanggalNota->format('d/m/Y') }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Nama</td>
                            <td class="colon">:</td>
                            <td>
                                <div class="input-box">{{ $namaNota }}</div>
                            </td>
                        </tr>
                        @if($tampilkanBagianNota)
                            <tr>
                                <td class="label">Bagian</td>
                                <td class="colon">:</td>
                                <td>
                                    <div class="input-box">Konveksi</div>
                                </td>
                            </tr>
                        @endif
                    </table>

                    {{-- JUMLAH BOX --}}
                    <div class="jumlah-wrapper">
                        <div class="jumlah-label">Jumlah</div>
                        <div class="jumlah-box">
                            Rp. {{ number_format($nominalNota, 0, ',', '.') }}
                        </div>
                    </div>

                    <table class="form-table">
                        <tr>
                            <td class="label" style="vertical-align: top; padding-top: 8px;">Keterangan</td>
                            <td class="colon" style="vertical-align: top; padding-top: 8px;">:</td>
                            <td>
                                <textarea class="textarea-box">{{ $keteranganNota ?? '-' }}</textarea>
                            </td>
                        </tr>
                    </table>

                    {{-- STATEMENT --}}
                    <div class="statement" style="text-align: center;">
                        {{ $pernyataanNota }}
                    </div>

                    {{-- SIGNATURES --}}
                    <table class="signature-table">
                        <tr>
                            <td width="50%">
                                Disetujui Oleh,
                                <div class="signature-box"></div>
                                <div class="signature-name">( Admin Gudang )</div>
                            </td>
                            <td width="50%">
                                Yang Menerima,
                                <div class="signature-box"></div>
                                <div class="signature-name">( {{ $namaPenerima }} )</div>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </td>
        @endforeach
    </tr>
</table>

</body>
</html>