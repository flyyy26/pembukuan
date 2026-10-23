<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti {{ ucfirst($trx->tipe) }} Tabungan</title>
    <style>
        @page { margin: 20px 24px; }
        * { box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 11px; color: #1c1c27; }
        table { width: 100%; border-collapse: collapse; }
        .header { border-bottom: 2px solid #047857; padding-bottom: 10px; margin-bottom: 14px; }
        .header td { vertical-align: top; }
        .brand { font-size: 16px; font-weight: bold; color: #047857; }
        .brand-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .title { font-size: 14px; font-weight: bold; text-align: right; color: #1e293b; }
        .title-sub { font-size: 9px; text-align: right; color: #64748b; margin-top: 2px; }
        .info { margin: 12px 0; }
        .info td { padding: 4px 0; font-size: 11px; vertical-align: top; }
        .info td:first-child { width: 35%; color: #64748b; }
        .info td:last-child { font-weight: bold; color: #1e293b; }
        .nominal { background: #ecfdf5; border: 1px solid #a7f3d0; padding: 12px; text-align: center; margin: 16px 0; }
        .nominal-label { font-size: 9px; color: #047857; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .nominal-value { font-size: 20px; font-weight: bold; color: #065f46; }
        .signature { margin-top: 32px; }
        .signature td { width: 50%; text-align: center; vertical-align: top; padding-top: 10px; font-size: 10px; color: #475569; }
        .signature-space { height: 50px; }
        .footer { margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 8px; color: #94a3b8; text-align: center; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-setoran { background: #d1fae5; color: #065f46; }
        .badge-penarikan { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<table class="header">
    <tr>
        <td>
            <div class="brand">BukuBarokah</div>
            <div class="brand-sub">SISTEM TABUNGAN</div>
        </td>
        <td>
            <div class="title">BUKTI {{ strtoupper($trx->tipe) }}</div>
            <div class="title-sub">No: {{ $noBukti }}</div>
        </td>
    </tr>
</table>

<table class="info">
    <tr>
        <td>Tanggal</td>
        <td>: {{ $trx->tanggal->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td>Nama Akun</td>
        <td>: {{ $account->nama }}</td>
    </tr>
    @if($account->keterangan)
    <tr>
        <td>Keterangan Akun</td>
        <td>: {{ $account->keterangan }}</td>
    </tr>
    @endif
    <tr>
        <td>Tipe</td>
        <td>:
            @if($trx->tipe === 'setoran')
                <span class="badge badge-setoran">SETORAN</span>
            @else
                <span class="badge badge-penarikan">PENARIKAN</span>
            @endif
        </td>
    </tr>
    @if($trx->keterangan)
    <tr>
        <td>Catatan</td>
        <td>: {{ $trx->keterangan }}</td>
    </tr>
    @endif
</table>

<div class="nominal">
    <div class="nominal-label">Nominal {{ ucfirst($trx->tipe) }}</div>
    <div class="nominal-value">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</div>
</div>

<table class="info">
    <tr>
        <td>Saldo Akun Saat Ini</td>
        <td>: Rp {{ number_format($saldoSetelah, 0, ',', '.') }}</td>
    </tr>
</table>

<table class="signature">
    <tr>
        <td>
            <div>Petugas</div>
            <div class="signature-space"></div>
            <div>(........................................)</div>
        </td>
        <td>
            <div>{{ $trx->tipe === 'setoran' ? 'Penyetor' : 'Penerima' }}</div>
            <div class="signature-space"></div>
            <div>(........................................)</div>
        </td>
    </tr>
</table>

<div class="footer">
    Dokumen dibuat otomatis oleh BukuBarokah · Dicetak pada {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>