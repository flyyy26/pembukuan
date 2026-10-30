@extends('layouts.app')

@section('title', 'Pertumbuhan Saldo')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                <iconify-icon icon="mdi:chart-timeline-variant" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Laporan Pertumbuhan Saldo</h2>
                <p class="text-xs sm:text-sm text-gray-500">{{ $store?->nama_toko ?? 'Semua Toko' }}</p>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap gap-3 items-end">
            
            <div class="w-full lg:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Toko</label>
                {{-- ✅ Tambahkan onchange="this.form.submit()" --}}
                <select name="store" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="all" {{ $storeId === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->nama_toko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full lg:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Mode Laporan</label>
                {{-- ✅ Tambahkan onchange="this.form.submit()" --}}
                <select name="mode" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                    <option value="bulanan" {{ $mode === 'bulanan' ? 'selected' : '' }}>Per Bulan</option>
                    <option value="tahunan" {{ $mode === 'tahunan' ? 'selected' : '' }}>Per Tahun</option>
                </select>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
            </div>

            <div class="flex gap-2 w-full lg:w-auto mt-2 lg:mt-0">
                <button type="submit" class="flex-1 lg:flex-none bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center justify-center gap-2 shadow-sm">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($from || $to || $storeId !== 'all')
                    <a href="{{ route('brilink.pertumbuhan-saldo') }}" class="flex-1 lg:flex-none text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- CHART PERTUMBUHAN SALDO (PENAMBAHAN) --}}
    @if(count($data) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:chart-line" class="text-indigo-600 text-xl"></iconify-icon>
                <h3 class="font-bold text-gray-800 text-base sm:text-lg">Grafik Penambahan Saldo</h3>
            </div>
        </div>
        
        {{-- Container untuk Chart --}}
        <div class="relative w-full h-[300px] sm:h-[400px]">
            <canvas id="growthChart"></canvas>
        </div>
    </div>
    @endif

    {{-- ======================================================= --}}
    {{-- TABEL HARIAN (BARU) --}}
    {{-- ======================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:calendar-month" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">
                    Rincian Harian (Bulan {{ \Carbon\Carbon::parse($from)->locale('id')->translatedFormat('F Y') }})
                </h3>
            </div>
            
            <span class="text-[10px] sm:text-xs text-gray-500 bg-white px-2 py-1 rounded border w-max flex items-center gap-1">
                <iconify-icon icon="mdi:store" class="text-xs"></iconify-icon>
                {{ $store?->nama_toko ?? 'Semua Toko' }}
            </span>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] relative">
            <table class="w-full text-sm border-collapse min-w-max">
                <thead class="sticky top-0 z-20 bg-gray-100 shadow-sm">
                    <tr>
                        <th class="border border-gray-200 p-3 text-center text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100 w-32">Tanggal</th>
                        <th class="border border-gray-200 p-3 text-right text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100">Saldo Awal</th>
                        <th class="border border-gray-200 p-3 text-right text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100">Saldo Akhir</th>
                        <th class="border border-gray-200 p-3 text-right text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100">Penambahan</th>
                        <th class="border border-gray-200 p-3 text-center text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap bg-gray-100 w-16">Aksi</th> {{-- ✅ Kolom Baru --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dailyBalance as $index => $daily)
                        @php
                            $isEmpty = ($daily['saldo_awal'] == 0 && $daily['saldo_akhir'] == 0);
                        @endphp
                        <tr class="hover:bg-gray-50 transition {{ $isEmpty ? 'opacity-50' : '' }}">
                            <td class="p-2 sm:p-3 text-center text-gray-700 font-medium text-xs sm:text-sm whitespace-nowrap">
                                {{ $daily['tanggal_label'] }}
                            </td>
                            <td class="p-2 sm:p-3 text-right text-gray-700 text-xs sm:text-sm whitespace-nowrap">
                                {{ $daily['saldo_awal'] != 0 ? 'Rp ' . number_format($daily['saldo_awal'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="p-2 sm:p-3 text-right text-gray-800 font-semibold text-xs sm:text-sm whitespace-nowrap">
                                {{ $daily['saldo_akhir'] != 0 ? 'Rp ' . number_format($daily['saldo_akhir'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="p-2 sm:p-3 text-right font-bold text-xs sm:text-sm whitespace-nowrap {{ $daily['penambahan'] > 0 ? 'text-emerald-600' : ($daily['penambahan'] < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                @if($daily['penambahan'] > 0)
                                    + Rp {{ number_format($daily['penambahan'], 0, ',', '.') }}
                                @elseif($daily['penambahan'] < 0)
                                    - Rp {{ number_format(abs($daily['penambahan']), 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            {{-- ✅ TOMBOL MATA UNTUK DETAIL --}}
                            <td class="p-2 sm:p-3 text-center">
                                @if(!$isEmpty && count($daily['rincian_rekening']) > 0)
                                    <button type="button" 
                                            onclick='showDetailRekening(@json($daily["tanggal_label"]), @json($daily["rincian_rekening"]))'
                                            class="p-1.5 text-indigo-600 hover:bg-indigo-100 rounded transition border border-indigo-200 bg-white" 
                                            title="Lihat Rincian Rekening">
                                        <iconify-icon icon="mdi:eye-outline" class="text-sm"></iconify-icon>
                                    </button>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABEL DATA PERTUMBUHAN SALDO --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:table" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Rincian Pertumbuhan Saldo</h3>
            </div>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">
                {{ $store?->nama_toko ?? 'Semua Toko' }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse min-w-max">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            {{ $mode === 'tahunan' ? 'Tahun' : 'Bulan' }}
                        </th>
                        <th class="text-right p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Saldo Awal</th>
                        <th class="text-right p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Saldo Akhir</th>
                        <th class="text-right p-3 sm:p-4 text-[10px] sm:text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Penambahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $index => $row)
                        @php
                            // Konversi format label dari controller menjadi Bahasa Indonesia
                            $labelBulan = $row['label'];
                            if ($mode === 'bulanan') {
                                try {
                                    // Coba parsing "October 2026" ke Carbon, lalu format ke bahasa Indonesia
                                    $labelBulan = \Carbon\Carbon::parse($row['label'])->locale('id')->translatedFormat('F Y');
                                } catch (\Exception $e) {
                                    // Fallback jika parsing gagal
                                    $labelBulan = $row['label'];
                                }
                            } elseif ($mode === 'tahunan') {
                                // Jika tahunan, pastikan formatnya "Tahun 2026"
                                $labelBulan = 'Tahun ' . $row['periode'];
                            }
                        @endphp

                        {{-- BARIS UTAMA (BULAN) --}}
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 sm:p-4 font-medium text-gray-800 text-xs sm:text-sm whitespace-nowrap">
                                {{ $labelBulan }} {{-- ✅ Ditampilkan dalam Bahasa Indonesia --}}
                                <span class="text-[10px] text-gray-400 block font-normal">{{ $row['jumlah_report'] }} Report</span>
                            </td>
                            <td class="p-3 sm:p-4 text-right text-gray-700 text-xs sm:text-sm whitespace-nowrap">
                                Rp {{ number_format($row['saldo_awal'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 sm:p-4 text-right text-gray-800 font-semibold text-xs sm:text-sm whitespace-nowrap">
                                Rp {{ number_format($row['saldo_akhir'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 sm:p-4 text-right font-bold text-xs sm:text-sm whitespace-nowrap {{ $row['penambahan'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $row['penambahan'] >= 0 ? '+' : '' }} Rp {{ number_format($row['penambahan'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">
                                <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                                Belum ada data report untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                    <tfoot class="bg-indigo-50 border-t-2 border-indigo-200">
                        <tr>
                            <td class="p-3 sm:p-4 font-bold text-gray-800 text-xs sm:text-sm">TOTAL</td>
                            <td class="p-3 sm:p-4 text-right font-bold text-gray-800 text-xs sm:text-sm whitespace-nowrap">
                                Rp {{ number_format($summary['total_saldo_awal'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 sm:p-4 text-right font-bold text-gray-800 text-xs sm:text-sm whitespace-nowrap">
                                Rp {{ number_format($summary['total_saldo_akhir'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 sm:p-4 text-right font-bold text-xs sm:text-sm whitespace-nowrap {{ $summary['total_penambahan'] >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                                Rp {{ number_format($summary['total_penambahan'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<div id="modalRincianRekening" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeDetailRekening()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden">
        
        {{-- Header Modal --}}
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:wallet" class="text-indigo-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Rincian Rekening</h3>
                <span id="modalTanggalRekening" class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded font-medium"></span>
            </div>
            <button type="button" onclick="closeDetailRekening()" class="text-gray-400 hover:text-gray-600 transition">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="overflow-y-auto flex-1 p-0">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Nama Rekening</th>
                        <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                        <th class="p-3 text-xs font-semibold text-gray-500 uppercase text-right">Saldo Awal</th>
                        <th class="p-3 text-xs font-semibold text-gray-500 uppercase text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody id="modalRekeningBody" class="divide-y divide-gray-100">
                    {{-- Diisi via JS --}}
                </tbody>
            </table>
        </div>

        {{-- Footer Modal --}}
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end flex-shrink-0">
            <button type="button" onclick="closeDetailRekening()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition">Tutup</button>
        </div>
    </div>
</div>

{{-- Panggil Chart.js via CDN --}}
@if(count($data) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('growthChart');
    if (!canvas) return;

    // Data dari PHP di-reverse agar urutannya dari terlama ke terbaru (kiri ke kanan di chart)
    const chartData = @json(array_reverse($data));
    
    // Extract data untuk chart (HANYA PENAMBAHAN)
    const labels     = chartData.map(item => item.label_short);
    const penambahan = chartData.map(item => item.penambahan);

    const ctx = canvas.getContext('2d');

    // Gradient untuk Line Chart Penambahan
    const gradientPenambahan = ctx.createLinearGradient(0, 0, 0, 400);
    // Warna dasar hijau transparan
    gradientPenambahan.addColorStop(0, 'rgba(16, 185, 129, 0.4)'); 
    gradientPenambahan.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line', // ✅ Menggunakan Line Chart
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Penambahan Saldo',
                    data: penambahan,
                    borderColor: '#10b981', // Emerald-500 (Garis Hijau)
                    backgroundColor: gradientPenambahan, // Area di bawah garis
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true, // Mengisi area di bawah garis
                    tension: 0.3 // Garis melengkung
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false, // Sembunyikan legend karena hanya 1 dataset
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleFont: { size: 13, family: "'Geist', sans-serif" },
                    bodyFont: { size: 12, family: "'Geist', sans-serif" },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let val = context.parsed.y;
                            let prefix = val >= 0 ? '+' : '';
                            return 'Penambahan: ' + prefix + new Intl.NumberFormat('id-ID', { 
                                style: 'currency', 
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(val);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, family: "'Geist', sans-serif" },
                        color: '#6b7280'
                    }
                },
                y: {
                    beginAtZero: true, 
                    grid: {
                        color: '#f3f4f6',
                        drawBorder: false,
                    },
                    ticks: {
                        font: { size: 11, family: "'Geist', sans-serif" },
                        color: '#6b7280',
                        callback: function(value) {
                            if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + ' jt';
                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + ' rb';
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif

<script>
    function toggleRow(id) {
        const row = document.getElementById(id);
        const icon = document.getElementById(id.replace('daily-', 'icon-'));
        
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            row.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }
</script>

<script>
    function showDetailRekening(tanggal, rekeningData) {
        // Set Tanggal di Header
        document.getElementById('modalTanggalRekening').textContent = tanggal;

        const tbody = document.getElementById('modalRekeningBody');
        tbody.innerHTML = '';

        if (!rekeningData || rekeningData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">Tidak ada data rekening.</td></tr>';
        } else {
            rekeningData.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 transition';
                tr.innerHTML = `
                    <td class="p-3 text-gray-800 font-medium text-xs">${item.nama}</td>
                    <td class="p-3 text-xs"><span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded">${item.keterangan || '-'}</span></td>
                    <td class="p-3 text-right text-gray-700 text-xs">Rp ${new Intl.NumberFormat('id-ID').format(item.saldo_awal)}</td>
                    <td class="p-3 text-right font-semibold text-xs ${item.saldo_akhir < 0 ? 'text-red-600' : 'text-green-600'}">
                        Rp ${new Intl.NumberFormat('id-ID').format(item.saldo_akhir)}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Buka Modal
        const modal = document.getElementById('modalRincianRekening');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailRekening() {
        const modal = document.getElementById('modalRincianRekening');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Tutup modal jika tekan ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDetailRekening();
    });
</script>
@endsection