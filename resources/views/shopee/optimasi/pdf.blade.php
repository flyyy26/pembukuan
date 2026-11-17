<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Optimasi Produk Shopee</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 9px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        h2 { font-size: 12px; margin: 12px 0 4px 0; }
        .header { border-bottom: 2px solid #f59e0b; padding-bottom: 6px; margin-bottom: 10px; }
        .meta { font-size: 9px; color: #6b7280; }
        .summary { display: table; width: 100%; margin-bottom: 10px; }
        .summary-item { display: table-cell; padding: 6px; text-align: center; border: 1px solid #e5e7eb; background: #f9fafb; }
        .summary-item .label { font-size: 8px; color: #6b7280; }
        .summary-item .value { font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-size: 8px; font-weight: bold; text-transform: uppercase; color: #4b5563; }
        td { font-size: 8px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .text-green { color: #16a34a; font-weight: bold; }
        .text-yellow { color: #ca8a04; font-weight: bold; }
        .text-red { color: #dc2626; font-weight: bold; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .footer { margin-top: 12px; font-size: 8px; color: #9ca3af; text-align: right; }

        /* Detail card */
        .detail-card { margin-bottom: 8px; border: 1px solid #e5e7eb; padding: 6px; border-radius: 3px; page-break-inside: avoid; }
        .detail-title { font-weight: bold; font-size: 9px; margin-bottom: 4px; }
        .detail-progress { float: right; font-weight: normal; color: #6b7280; font-size: 8px; }
        .aspek-row { font-size: 8px; margin-left: 8px; margin-bottom: 3px; padding-left: 6px; border-left: 2px solid #e5e7eb; }
        .aspek-label { font-weight: bold; color: #374151; }
        .aspek-masalah { color: #6b7280; margin-top: 1px; }
        .aspek-solusi { color: #92400e; margin-top: 1px; background: #fffbeb; padding: 2px 4px; border-radius: 2px; }
        .progress-bar { display: inline-block; width: 60px; height: 4px; background: #e5e7eb; border-radius: 2px; vertical-align: middle; overflow: hidden; margin-left: 4px; }
        .progress-bar-fill { height: 100%; background: #16a34a; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Optimasi Produk Shopee</h1>
        <div class="meta">
            Toko: <strong>{{ $store?->nama_toko ?? 'Semua Toko' }}</strong>
            @if($status) · Filter Status: <strong>{{ \App\Models\ProductOptimization::STATUS_META[$status]['label'] }}</strong> @endif
            @if($search) · Pencarian: <strong>"{{ $search }}"</strong> @endif
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-item">
            <div class="label">Total</div>
            <div class="value">{{ $summary['total'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Aman</div>
            <div class="value text-green">{{ $summary['aman'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Perlu Evaluasi</div>
            <div class="value text-yellow">{{ $summary['evaluasi'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Perlu Perhatian</div>
            <div class="value text-red">{{ $summary['perhatian'] }}</div>
        </div>
    </div>

    {{-- Tabel Utama --}}
    <table>
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th>Nama Produk</th>
                <th class="right">Harga</th>
                <th class="right">Harga Final</th>
                <th class="right">Margin</th>
                @foreach(\App\Models\ProductOptimization::ASPEK_AUDIT as $key => $meta)
                    <th class="center">{{ Str::limit($meta['label'], 12, '…') }}</th>
                @endforeach
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                @php $sm = \App\Models\ProductOptimization::STATUS_META[$item->status_global]; @endphp
                <tr>
                    <td class="center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->nama_produk }}</strong>
                        @if($item->marketplace_sku)<br><span style="font-family: monospace; font-size: 7px; color:#6b7280;">{{ $item->marketplace_sku }}</span>@endif
                    </td>
                    <td class="right">{{ number_format($item->harga_produk, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item->harga_final, 0, ',', '.') }}</td>
                    <td class="right">{{ $item->margin_persen }}%</td>
                    @foreach(\App\Models\ProductOptimization::ASPEK_AUDIT as $key => $meta)
                        @php
                            $a = $item->getAspek($key);
                            $aMeta = \App\Models\ProductOptimization::STATUS_META[$a['status']];
                            $cls = $a['status'] === 'perhatian' ? 'badge-red' : ($a['status'] === 'evaluasi' ? 'badge-yellow' : 'badge-green');
                            $sudah = $item->{"sudah_{$key}"};
                        @endphp
                        <td class="center">
                            <span class="badge {{ $cls }}">{{ Str::substr($aMeta['label'], 0, 1) }}</span>
                            @if(in_array($a['status'], ['evaluasi','perhatian']))
                                <span style="font-size: 7px; color: {{ $sudah ? '#16a34a' : '#9ca3af' }};">
                                    {{ $sudah ? '✓' : '○' }}
                                </span>
                            @endif
                        </td>
                    @endforeach
                    <td class="center">
                        <span class="badge {{ $item->status_global === 'perhatian' ? 'badge-red' : ($item->status_global === 'evaluasi' ? 'badge-yellow' : 'badge-green') }}">
                            {{ $sm['label'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="15" class="center" style="padding: 20px; color: #9ca3af;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Detail Catatan & Solusi --}}
    @if($items->count() > 0)
        <h2 style="margin-top: 15px; margin-bottom: 8px;">Detail Masalah, Solusi &amp; Status Pelaksanaan</h2>
        
        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="width: 15%; font-size: 8px; padding: 4px;">Produk</th>
                    <th style="width: 13%; font-size: 8px; padding: 4px;">Aspek</th>
                    <th style="width: 9%; font-size: 8px; padding: 4px; text-align: center;">Status</th>
                    <th style="width: 10%; font-size: 8px; padding: 4px; text-align: center;">Pelaksanaan</th>
                    <th style="width: 25%; font-size: 8px; padding: 4px;">Masalah / Catatan</th>
                    <th style="width: 28%; font-size: 8px; padding: 4px;">Solusi / Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php
                        $validAspeks = [];
                        foreach (\App\Models\ProductOptimization::ASPEK_AUDIT as $key => $m) {
                            $a = $item->getAspek($key);
                            $solusi = $item->{"solusi_{$key}"};
                            if (!empty($a['note']) || !empty($solusi)) {
                                $validAspeks[] = [
                                    'key' => $key,
                                    'meta' => $m,
                                    'aspek' => $a,
                                    'solusi' => $solusi,
                                    'sudah' => $item->{"sudah_{$key}"}
                                ];
                            }
                        }
                        
                        $progress = $item->progressPerbaikan();
                        $bgColor = $progress['persen'] === 100 ? '#dcfce7' : ($progress['persen'] > 0 ? '#eff6ff' : '#fef2f2');
                        $textColor = $progress['persen'] === 100 ? '#166534' : ($progress['persen'] > 0 ? '#1e40af' : '#991b1b');
                    @endphp

                    @if(count($validAspeks) > 0)
                        @foreach($validAspeks as $index => $data)
                            <tr style="page-break-inside: avoid;">
                                {{-- Kolom Produk --}}
                                <td style="border: 1px solid #e5e7eb; padding: 5px; vertical-align: top; {{ $index > 0 ? 'border-top: none;' : '' }}">
                                    @if($index === 0)
                                        <div style="font-weight: bold; font-size: 9px; color: #1f2937; margin-bottom: 3px;">
                                            {{ $item->nama_produk }}
                                        </div>
                                        @if($item->marketplace_sku)
                                            <div style="font-family: monospace; font-size: 7px; color: #6b7280; margin-bottom: 5px;">
                                                {{ $item->marketplace_sku }}
                                            </div>
                                        @endif
                                        <div style="display: inline-block; padding: 2px 4px; background: {{ $bgColor }}; color: {{ $textColor }}; border-radius: 3px; font-size: 7px; font-weight: bold;">
                                            Progress: {{ $progress['selesai'] }}/{{ $progress['total'] }} ({{ $progress['persen'] }}%)
                                        </div>
                                    @endif
                                </td>

                                {{-- Kolom Aspek --}}
                                <td style="border: 1px solid #e5e7eb; padding: 5px; vertical-align: top;">
                                    <strong style="font-size: 8px; color: #374151;">{{ $data['meta']['label'] }}</strong>
                                </td>
                                
                                {{-- Kolom Status (Evaluasi / Perhatian / Aman) --}}
                                <td style="border: 1px solid #e5e7eb; padding: 5px; vertical-align: top; text-align: center;">
                                    @php
                                        $isPerhatian = $data['aspek']['status'] === 'perhatian';
                                        $isEvaluasi = $data['aspek']['status'] === 'evaluasi';
                                        $badgeColor = $isPerhatian ? 'background:#fee2e2; color:#991b1b;' : ($isEvaluasi ? 'background:#fef9c3; color:#854d0e;' : 'background:#dcfce7; color:#166534;');
                                    @endphp
                                    <span class="badge" style="{{ $badgeColor }}">
                                        {{ \App\Models\ProductOptimization::STATUS_META[$data['aspek']['status']]['label'] }}
                                    </span>
                                </td>

                                {{-- ✅ KOLOM BARU: Status Pelaksanaan (Sudah / Belum) --}}
                                <td style="border: 1px solid #e5e7eb; padding: 5px; vertical-align: top; text-align: center;">
                                    @if(in_array($data['aspek']['status'], ['evaluasi','perhatian']))
                                        @if($data['sudah'])
                                            <span style="display: inline-block; padding: 2px 4px; background: #dcfce7; color: #166534; border-radius: 3px; font-size: 7px; font-weight: bold;">
                                                ✓ Sudah
                                            </span>
                                        @else
                                            <span style="display: inline-block; padding: 2px 4px; background: #f3f4f6; color: #4b5563; border-radius: 3px; font-size: 7px; font-weight: bold;">
                                                ✗ Belum
                                            </span>
                                        @endif
                                    @else
                                        <span style="color: #9ca3af; font-size: 8px;">-</span>
                                    @endif
                                </td>

                                {{-- Kolom Masalah --}}
                                <td style="border: 1px solid #e5e7eb; padding: 5px; vertical-align: top; color: #4b5563; font-size: 8px; line-height: 1.4;">
                                    {!! nl2br(e($data['aspek']['note'])) ?: '-' !!}
                                </td>

                                {{-- Kolom Solusi --}}
                                <td style="border: 1px solid #e5e7eb; padding: 5px; vertical-align: top; color: #92400e; background: #fffbeb; font-size: 8px; line-height: 1.4;">
                                    @if($data['solusi'])
                                        {!! nl2br(e($data['solusi'])) !!}
                                    @else
                                        <span style="color: #9ca3af; font-style: italic;">Belum ada solusi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Digenerate: {{ $generated_at }}
    </div>
</body>
</html>