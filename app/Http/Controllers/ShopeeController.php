<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ShopeeAdSpend;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ShopeeController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));
        $liveType = strtolower((string) $request->input('live_type', 'all'));
        if (!in_array($liveType, ['all', 'live', 'video'], true)) {
            $liveType = 'all';
        }
        $kategoriReturn = $request->input('kategori_return', 'all');
        if (!in_array($kategoriReturn, ['all', 'return', 'cancel', 'shipping_failed'], true)) {
            $kategoriReturn = 'all';
        }
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 25;

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();

        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }

        $store = $storeId ? Store::find($storeId) : null;
        $excludedOrderIds = $this->excludedProfitOrderIds($storeId);

        // ============ QUERY SALES ============
        $query = Sale::with('product')->where('marketplace', 'shopee');
        if ($storeId) $query->where('store_id', $storeId);
        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);
        if ($excludedOrderIds !== []) $query->whereNotIn('order_id', $excludedOrderIds);

        // Filter search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                ->orWhere('order_id', 'LIKE', "%{$search}%")
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('nama_produk', 'LIKE', "%{$search}%")
                        ->orWhere('sku_prefix', 'LIKE', "%{$search}%");
                });
            });
        }

        $rows = $query->get();

        // ============ RINGKASAN ============
        $summary = [
            'total_rows'      => $rows->count(),
            'total_transaksi' => $rows->pluck('order_id')->unique()->count(),  // ✅ order unik
            'total_qty'       => (int) $rows->sum('qty'),                      // ✅ total qty
            'net_revenue'     => $rows->sum('net_revenue'),
            'total_hpp'       => $rows->sum(fn($r) => $r->hpp_total ?? 0),
            'profit'          => $rows->sum(fn($r) => $r->profit ?? 0),
            'biaya_komisi'    => $rows->sum('biaya_komisi'),
            'biaya_layanan'   => $rows->sum('biaya_layanan'),
            'biaya_ongkir'    => $rows->sum('biaya_ongkir'),
            'biaya_afiliasi'  => $rows->sum('biaya_afiliasi'),
            'biaya_promosi'   => $rows->sum('biaya_promosi'),
            'biaya_pajak'     => $rows->sum('biaya_pajak'),
            'biaya_lainnya'   => $rows->sum('biaya_lainnya'),
        ];
        $summary['biaya_iklan'] = $storeId
            ? (int) (ShopeeAdSpend::where('store_id', $storeId)
                ->where('period_key', $this->adSpendPeriodKey($from, $to))
                ->value('amount') ?? 0)
            : 0;
        $summary['profit_after_ads'] = $summary['net_revenue'] - $summary['total_hpp'] - $summary['biaya_iklan'];

        $summary['transaksi_matched'] = $rows->where('status', 'matched')->count();

        $skuUnik = $rows->pluck('marketplace_sku')->filter()->unique();
        $skuDimapping = $rows->where('status', 'matched')
            ->pluck('marketplace_sku')->filter()->unique();
        $summary['sku_mapped'] = $skuDimapping->count();
        $summary['sku_total']  = $skuUnik->count();

        // ============ PER PRODUK ============
        $allPerProduk = $rows->groupBy(function ($row) {
            $sku = preg_replace('/\s+/', ' ', trim((string) $row->marketplace_sku));
            return $sku !== '' ? $sku : '__NO_SKU__';
        })->map(function ($group, $skuKey) {
            $first = $group->first();
            return [
                'marketplace_sku' => $skuKey === '__NO_SKU__' ? '(tanpa SKU)' : $skuKey,
                'product_id'      => $first->product_id,
                'nama_produk'     => $first->product?->nama_produk ?? '-',
                'internal_sku'    => $first->product?->sku_internal ?? '-',
                'total_qty'       => $group->sum('qty'),
                'net_revenue'     => $group->sum('net_revenue'),
                'hpp_total'       => $group->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'          => $group->sum(fn($r) => $r->profit ?? 0),
                'mapped'          => $first->product_id !== null,
            ];
        })->sortByDesc('profit')->values();

        $totalProduk = $allPerProduk->count();
        $page = min($page, max(1, (int) ceil($totalProduk / $perPage)));
        $perProduk   = $allPerProduk->slice(($page - 1) * $perPage, $perPage)->values();

        // ✅ Fix: ambil qty REAL per SKU sama persis dengan popup detail
        $perProduk = $perProduk->map(function ($item) use ($storeId, $from, $to, $excludedOrderIds) {
            if ($item['marketplace_sku'] === '(tanpa SKU)' || str_ends_with($item['marketplace_sku'], ' (Isi Grosir)')) {
                return $item;
            }

            $qtyQuery = Sale::where('marketplace', 'shopee')
                ->where('marketplace_sku', $item['marketplace_sku'])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to));
            if ($excludedOrderIds !== []) $qtyQuery->whereNotIn('order_id', $excludedOrderIds);
            $item['total_qty'] = (int) $qtyQuery->sum('qty');

            return $item;
        });

        // ============ PER PESANAN (BARU) ============
        // Mengelompokkan data berdasarkan nomor pesanan (order_id)
        $perPesanan = $rows->groupBy('order_id')->map(function ($group) {
            $first = $group->first();
            
            // Kumpulkan detail produk yang dibeli di pesanan ini
            $detailProduk = $group->map(function ($item) {
                return [
                    'sku'         => $item->marketplace_sku,
                    'nama_produk' => $item->product?->nama_produk ?? '-',
                    'qty'         => $item->qty,
                    'net_revenue' => $item->net_revenue,
                ];
            })->values();

            return [
                'order_id'      => $first->order_id,
                'tanggal'       => $first->tanggal,
                'total_net'     => $group->sum('net_revenue'),
                'total_hpp'     => $group->sum(fn($r) => $r->hpp_total ?? 0),
                'total_profit'  => $group->sum(fn($r) => $r->profit ?? 0),
                'detail_produk' => $detailProduk, // Array berisi produk-produk di pesanan ini
            ];
        })->sortBy(function($pesanan) {
            // Urutkan berdasarkan tanggal, lalu berdasarkan order_id
            // agar jika ada tanggal yang sama, urutannya konsisten
            return $pesanan['tanggal'] . '|' . $pesanan['order_id'];
        }) 
        ->values(); 
        $totalPesanan = $perPesanan->count();
        $pesananPage = min(
            max(1, (int) $request->input('pesanan_page', 1)),
            max(1, (int) ceil($totalPesanan / $perPage))
        );
        $perPesananPage = $perPesanan->slice(($pesananPage - 1) * $perPage, $perPage)->values();

        // ============ PENGEMBALIAN / PEMBATALAN ============
        
        // 1. Ambil dari Import File Return (ShopeeReturn)
        $queryReturn = \App\Models\ShopeeReturn::query()
            ->with('import:id,file_name')
            ->whereHas('import', fn($q) => $q->where('store_id', $storeId));

        if ($from) $queryReturn->whereDate('waktu_pengajuan', '>=', $from);
        if ($to)   $queryReturn->whereDate('waktu_pengajuan', '<=', $to);

        if ($search !== '') {
            $queryReturn->where(function ($q) use ($search) {
                $q->where('sku_varian', 'LIKE', "%{$search}%")
                  ->orWhere('nama_produk', 'LIKE', "%{$search}%")
                  ->orWhere('no_pesanan', 'LIKE', "%{$search}%");
            });
        }

        $rawReturn = $queryReturn->get()->map(function ($item) {
            $fileName = strtolower($item->import?->file_name ?? '');
            $isFailedDeliveryReport = str_contains($fileName, 'failed_delivery')
                || str_contains($fileName, 'failed delivery')
                || $item->tipe_laporan === 'shipping_failed';
            $tipeLaporan = $isFailedDeliveryReport
                ? 'shipping_failed'
                : ((str_contains($fileName, 'cancel') || str_contains($fileName, 'batal'))
                    ? 'cancel'
                    : ($item->tipe_laporan === 'cancel' ? 'cancel' : 'return'));

            $rawData = $item->raw_data ?? [];
            $alasan = trim((string) (
                $item->alasan_pengajuan
                ?: ($rawData['Alasan Pengembalian']
                    ?? $rawData['Alasan Pembatalan']
                    ?? $rawData['Alasan Pengajuan']
                    ?? ''))
            );
            $statusPengirimanGagal = $rawData['Status pengiriman gagal'] ?? null;
            $isShippingFailed = $isFailedDeliveryReport
                || str_contains(strtolower($alasan), 'pengiriman gagal')
                || str_contains(strtolower($alasan), 'gagal kirim')
                || ($statusPengirimanGagal !== null && trim((string) $statusPengirimanGagal) !== '');

            $item->setAttribute('report_type', $tipeLaporan);
            $item->setAttribute('reason_label', $alasan ?: ($isShippingFailed ? 'Gagal Kirim' : 'Alasan tidak tersedia'));
            $item->setAttribute('is_shipping_failed', $isShippingFailed);
            $item->setAttribute('is_failed_delivery_report', $isFailedDeliveryReport);

            return $item;
        });

        $failedDeliveryOrderIds = $rawReturn
            ->where('is_failed_delivery_report', true)
            ->pluck('no_pesanan')
            ->flip();
        $failedReasonsByOrder = $rawReturn
            ->filter(fn($item) => $item->report_type === 'cancel' && $item->is_shipping_failed)
            ->groupBy('no_pesanan')
            ->map(fn($items) => $items->first()->reason_label);

        $rawReturn = $rawReturn
            ->map(function ($item) use ($failedReasonsByOrder) {
                if ($item->is_failed_delivery_report
                    && $item->reason_label === 'Gagal Kirim'
                    && isset($failedReasonsByOrder[$item->no_pesanan])) {
                    $item->setAttribute('reason_label', $failedReasonsByOrder[$item->no_pesanan]);
                }

                return $item;
            })
            ->reject(fn($item) => $item->report_type === 'cancel' && isset($failedDeliveryOrderIds[$item->no_pesanan]))
            ->values();

        // 2. Grouping per pesanan untuk tampilan Tab
        $perBatal = $rawReturn->groupBy(fn($item) => $item->report_type . '|' . $item->no_pesanan)->map(function ($group) {
            $first = $group->first();

            $tanggalPengajuan = $first->waktu_pengajuan?->format('Y-m-d');
            if (!$tanggalPengajuan) {
                $rawData = $first->raw_data ?? [];
                $tanggalMentah = $rawData['Waktu Pengembalian Diajukan']
                    ?? $rawData['Waktu Pengajuan']
                    ?? $rawData['Waktu Pembatalan']
                    ?? $rawData['Tanggal Pembatalan']
                    ?? $rawData['Tanggal Pengajuan']
                    ?? null;

                if ($tanggalMentah) {
                    $tanggalPengajuan = Carbon::parse($tanggalMentah)->format('Y-m-d');
                }
            }

            $detailProduk = $group->map(function ($item) {
                $rawData = $item->raw_data ?? [];
                $danaKembali = $item->pengembalian_dana ?: $item->nominal_pengembalian;
                if (!$danaKembali) {
                    $rawRefund = $rawData['Total Pengembalian Dana']
                        ?? $rawData['Jumlah Pengembalian Dana ke Pembeli']
                        ?? $rawData['Jumlah Pengembalian Dana']
                        ?? $rawData['Dana Kembali']
                        ?? 0;
                    $danaKembali = (int) preg_replace('/[^0-9\-]/', '', (string) $rawRefund);
                }

                $statusKirim = $item->status_pengiriman_ke_pembeli
                    ?: ($rawData['Solusi Pengembalian Barang/Dana'] ?? $rawData['Status Pengiriman ke Pembeli'] ?? null);
                $statusKembali = $item->status_pengembalian_ke_penjual
                    ?: ($rawData['Status Pengembalian Barang'] ?? $rawData['Status Pengembalian ke Penjual'] ?? null);
                if (array_key_exists('Status pengiriman gagal', $rawData)) {
                    $statusKirim = $rawData['Solusi Pengembalian Barang/Dana']
                        ?? $rawData['Status Pengiriman ke Pembeli']
                        ?? null;
                    $statusKembali = $statusKembali ?: $rawData['Status pengiriman gagal'];
                }

                return [
                    'sku'         => $item->sku_varian,
                    'nama_produk' => $item->nama_produk,
                    'qty'         => $item->jumlah,
                    'status'      => $item->status_pengajuan ?: ($rawData['Status Pembatalan/ Pengembalian'] ?? null),
                    'alasan'      => $item->reason_label,
                    'status_kirim' => $statusKirim,
                    'status_kembali' => $statusKembali,
                    'dana_kembali' => $danaKembali,
                ];
            })->values();

            return [
                'order_id'      => $first->no_pesanan,
                'tipe_laporan' => $first->report_type,
                'gagal_kirim'  => $group->contains(fn($item) => $item->is_shipping_failed),
                'tanggal'       => $tanggalPengajuan,
                'created_at'    => $first->created_at,
                'status_utama'  => $detailProduk->first()['status'] ?: 'Return / Batal',
                'total_nilai'   => $detailProduk->sum('dana_kembali'),
                'detail_produk' => $detailProduk,
            ];
        })->sortByDesc('tanggal')->values();

            $perBatalFiltered = match ($kategoriReturn) {
                'return' => $perBatal->where('tipe_laporan', 'return')->values(),
                'cancel' => $perBatal->filter(fn($item) => $item['tipe_laporan'] === 'cancel' && !$item['gagal_kirim'])->values(),
                'shipping_failed' => $perBatal->filter(fn($item) => $item['gagal_kirim'])->values(),
                default => $perBatal,
            };
            $totalBatal = $perBatalFiltered->count();
            $batalPage = min(
                max(1, (int) $request->input('batal_page', 1)),
                max(1, (int) ceil($totalBatal / $perPage))
            );
            $perBatalPage = $perBatalFiltered->slice(($batalPage - 1) * $perPage, $perPage)->values();

        // 3. Ringkasan Pembatalan
        $summaryBatal = [
            'total_batal' => $perBatal->where('tipe_laporan', 'cancel')->count(),
            'total_retur' => $perBatal->where('tipe_laporan', 'return')->count(),
                'total_gagal_kirim' => $perBatal->where('gagal_kirim', true)->count(),
            'total_item'  => $rawReturn->sum('jumlah'),
        ];

        // ============ REKAP BULANAN ============
        $monthlyData = $this->buildMonthlySummary($storeId, $search);

        // ============ TOP PRODUCTS ============
        $topProducts = $this->buildTopProducts($allPerProduk, $storeId, $from, $to, $excludedOrderIds);

        // ============ DATA CHAT ============
        // Menggunakan query yang lebih eksplisit
        $queryChat = \App\Models\ShopeeChat::query();
        
        // 1. Filter berdasarkan toko (relasi import)
        $queryChat->whereHas('import', function($q) use ($storeId) {
            $q->where('store_id', $storeId);
        });

        // 2. Filter tanggal
        if ($from) {
            $queryChat->where('tanggal', '>=', $from);
        }
        if ($to) {
            $queryChat->where('tanggal', '<=', $to);
        }

        // 3. Ambil data
        $rawChat = $queryChat->orderByDesc('tanggal')->get();

        // Ringkasan Chat
        $summaryChat = [
            'total_pengunjung'   => $rawChat->sum('pengunjung'),
            'total_chat'         => $rawChat->sum('jumlah_chat'),
            'chat_dibalas'       => $rawChat->sum('chat_dibalas'),
            'chat_belum_dibalas' => $rawChat->sum('chat_belum_dibalas'),
            'total_pembeli'      => $rawChat->sum('total_pembeli'),
            'total_pesanan'      => $rawChat->sum('total_pesanan'),
            'total_penjualan'    => $rawChat->sum('penjualan_idr'),
            // Rata-rata persentase (hitung dari total, bukan rata-rata persentase baris)
            'csat_persen'        => $rawChat->avg('csat_persen') ?? 0,
            'konversi_chat'      => $rawChat->avg('konversi_chat_dibalas') ?? 0,
        ];

        // ============ DATA LIVE & VIDEO ============
        $queryLive = \App\Models\ShopeeLiveVideo::with('importData')
            ->whereIn('metrics->record_type', ['livestream', 'video'])
            ->whereHas('importData', function ($q) use ($storeId, $from, $to) {
                $q->where('store_id', $storeId);
            });

        if ($liveType !== 'all') {
            $queryLive->where('tipe', ucfirst($liveType));
        }

        if ($from) {
            $queryLive->whereDate('tanggal', '>=', $from);
        }
        if ($to) {
            $queryLive->whereDate('tanggal', '<=', $to);
        }

        $rawLive = $queryLive->orderByDesc('tanggal')->orderByDesc('id')->get();
        $liveRows = $rawLive->where('tipe', 'Live')->values();
        $videoRows = $rawLive->where('tipe', 'Video')->values();

        return view('shopee.index', compact(
              'summary', 'perProduk', 'perPesanan', 'perPesananPage', 'totalPesanan', 'pesananPage', 'rawLive', 'liveRows', 'videoRows',
              'liveType',
              'perBatal', 'perBatalFiltered', 'perBatalPage', 'totalBatal', 'batalPage', 'summaryBatal',
              'summaryChat', 'rawChat',
            'from', 'to', 'search', 'stores', 'store', 'storeId',
             'kategoriReturn',
            'page', 'perPage', 'totalProduk', 'monthlyData', 'topProducts'
        ));
    }

    public function saveAdSpend(Request $request)
    {
        $amountInput = $request->input('biaya_iklan');
        if (is_string($amountInput) && preg_match('/^\d+(?:\.\d{3})*$/D', $amountInput) === 1) {
            $request->merge(['biaya_iklan' => str_replace('.', '', $amountInput)]);
        }

        $validated = $request->validate([
            'store' => ['required', 'integer', 'exists:stores,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'biaya_iklan' => ['required', 'integer', 'min:0'],
            'tab' => ['required', 'in:ringkasan,pesanan'],
        ]);

        $store = Store::where('marketplace', 'shopee')->findOrFail($validated['store']);
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;
        $periodKey = $this->adSpendPeriodKey($from, $to);

        ShopeeAdSpend::updateOrCreate(
            ['store_id' => $store->id, 'period_key' => $periodKey],
            [
                'period_from' => $from,
                'period_to' => $to,
                'amount' => $validated['biaya_iklan'],
            ]
        );

        return redirect()->route('shopee.index', [
            'store' => $store->id,
            'from' => $from,
            'to' => $to,
            'tab' => $validated['tab'],
        ])->with('success', 'Biaya iklan berhasil disimpan.');
    }

    /**
     * Top 10 produk berdasarkan Qty, Profit, dan Bottom Profit.
     */
    private function buildTopProducts($allPerProduk, $storeId = null, $from = null, $to = null, array $excludedOrderIds = []): array
    {
        // ✅ Hitung ulang total_qty per SKU pakai count() (sama seperti tabel utama)
        $allPerProduk = $allPerProduk->map(function ($item) use ($storeId, $from, $to, $excludedOrderIds) {
            if ($item['marketplace_sku'] === '(tanpa SKU)') {
                return $item;
            }

            $qtyQuery = Sale::where('marketplace', 'shopee')
                ->where('marketplace_sku', $item['marketplace_sku'])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to));
            if ($excludedOrderIds !== []) $qtyQuery->whereNotIn('order_id', $excludedOrderIds);
            $item['total_qty'] = $qtyQuery->count();

            return $item;
        });

        // Filter hanya SKU yang mapped (punya HPP & profit valid)
        $mapped = $allPerProduk->where('mapped', true);

        $topByQty = $mapped->sortByDesc('total_qty')->take(10)->values();
        $topByProfit = $mapped->sortByDesc('profit')->take(10)->values();
        $bottomByProfit = $mapped->sortBy('profit')->take(10)->values();

        $totalSku = $mapped->count();
        $skuProfit = $mapped->where('profit', '>', 0)->count();
        $skuRugi   = $mapped->where('profit', '<', 0)->count();
        $avgProfit = $totalSku > 0 ? $mapped->avg('profit') : 0;

        return [
            'top_qty'       => $topByQty,
            'top_profit'    => $topByProfit,
            'bottom_profit' => $bottomByProfit,
            'insight' => [
                'total_sku'  => $totalSku,
                'sku_profit' => $skuProfit,
                'sku_rugi'   => $skuRugi,
                'avg_profit' => $avgProfit,
            ],
        ];
    }

    /**
     * Rekap per bulan (12 bulan terakhir).
     */
    private function buildMonthlySummary($storeId, $search = ''): array
    {
        $query = Sale::where('marketplace', 'shopee');
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('nama_produk', 'LIKE', "%{$search}%")
                        ->orWhere('sku_prefix', 'LIKE', "%{$search}%");
                });
            });
        }

        $rows = $query->get();

        $grouped = $rows->groupBy(function ($row) {
            return \Carbon\Carbon::parse($row->tanggal)->format('Y-m');
        })->sortKeysDesc()->take(12);

        $result = [];
        $prevProfit = null;

        foreach ($grouped->sortKeys() as $yearMonth => $items) {
            $carbon = \Carbon\Carbon::createFromFormat('Y-m', $yearMonth);

            $netRevenue = $items->sum('net_revenue');
            $hppTotal   = $items->sum(fn($r) => $r->hpp_total ?? 0);
            $profit     = $items->sum(fn($r) => $r->profit ?? 0);
            $qty        = $items->sum('qty');
            $transaksi  = $items->pluck('order_id')->unique()->count();

            $growth = null;
            if ($prevProfit !== null && $prevProfit != 0) {
                $growth = round((($profit - $prevProfit) / abs($prevProfit)) * 100, 1);
            }
            $prevProfit = $profit;

            $result[] = [
                'year_month'   => $yearMonth,
                'label'        => $carbon->translatedFormat('M Y'),
                'label_short'  => $carbon->translatedFormat('M'),
                'transaksi'    => $transaksi,
                'qty'          => $qty,
                'net_revenue'  => $netRevenue,
                'hpp_total'    => $hppTotal,
                'profit'       => $profit,
                'growth'       => $growth,
            ];
        }

        $result = array_reverse($result);

        $bestMonth = collect($result)->sortByDesc('profit')->first();

        return [
            'months'      => $result,
            'best_month'  => $bestMonth,
            'total_profit' => collect($result)->sum('profit'),
            'total_net'    => collect($result)->sum('net_revenue'),
        ];
    }

    public function reconcile($storeId)
    {
        \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);

        return redirect()->route('shopee.index', ['store' => $storeId])
            ->with('success', 'Rekonsiliasi selesai.');
    }

     public function compare(Request $request)
    {
        $storeId = $request->input('store');

        // Default: bulan ini vs bulan lalu
        $today = Carbon::today();
        $defaultBFrom = $today->copy()->startOfMonth()->format('Y-m-d');
        $defaultBTo   = $today->copy()->endOfMonth()->format('Y-m-d');
        $defaultAFrom = $today->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $defaultATo   = $today->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $aFrom = $request->input('a_from', $defaultAFrom);
        $aTo   = $request->input('a_to', $defaultATo);
        $bFrom = $request->input('b_from', $defaultBFrom);
        $bTo   = $request->input('b_to', $defaultBTo);

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        // ============ AMBIL DATA PERIODE A ============
        $queryA = Sale::with('product')->where('marketplace', 'shopee');
        if ($storeId) $queryA->where('store_id', $storeId);
        $queryA->whereDate('tanggal', '>=', $aFrom)->whereDate('tanggal', '<=', $aTo);
        $rowsA = $queryA->get();

        // ============ AMBIL DATA PERIODE B ============
        $queryB = Sale::with('product')->where('marketplace', 'shopee');
        if ($storeId) $queryB->where('store_id', $storeId);
        $queryB->whereDate('tanggal', '>=', $bFrom)->whereDate('tanggal', '<=', $bTo);
        $rowsB = $queryB->get();

        // ============ SUMMARY ============
        $summaryA = $this->buildSummary($rowsA);
        $summaryB = $this->buildSummary($rowsB);

        // ============ GROWTH ============
        $growth = [
            'net_revenue' => $this->calcGrowth($summaryA['net_revenue'], $summaryB['net_revenue']),
            'total_hpp'   => $this->calcGrowth($summaryA['total_hpp'], $summaryB['total_hpp']),
            'profit'      => $this->calcGrowth($summaryA['profit'], $summaryB['profit']),
            'transaksi'   => $this->calcGrowth($summaryA['total_rows'], $summaryB['total_rows']),
        ];

        // ============ PER SKU ============
        $skuA = $this->groupBySku($rowsA);
        $skuB = $this->groupBySku($rowsB);

        // Gabungkan semua SKU unik
        $allSkus = collect(array_unique(array_merge(
            array_keys($skuA),
            array_keys($skuB)
        )));

        $comparison = $allSkus->map(function ($sku) use ($skuA, $skuB) {
            $a = $skuA[$sku] ?? null;
            $b = $skuB[$sku] ?? null;

            $profitA = $a['profit'] ?? 0;
            $profitB = $b['profit'] ?? 0;
            $diffProfit = $profitB - $profitA;

            return [
                'sku'             => $sku,
                'nama_produk'     => $b['nama_produk'] ?? $a['nama_produk'] ?? '-',
                'qty_a'           => $a['qty'] ?? 0,
                'qty_b'           => $b['qty'] ?? 0,
                'net_revenue_a'   => $a['net_revenue'] ?? 0,
                'net_revenue_b'   => $b['net_revenue'] ?? 0,
                'profit_a'        => $profitA,
                'profit_b'        => $profitB,
                'diff_profit'     => $diffProfit,
                'growth_profit'   => $this->calcGrowth($profitA, $profitB),
                'status'          => $this->getSkuStatus($a, $b),
            ];
        })->sortByDesc(function ($item) {
            return abs($item['diff_profit']);
        })->values();

        // ============ SKU BARU & HILANG ============
        $skuBaru = $comparison->where('status', 'baru')->sortByDesc('profit_b')->take(5)->values();
        $skuHilang = $comparison->where('status', 'hilang')->sortByDesc('profit_a')->take(5)->values();

        return view('shopee.compare', compact(
            'stores', 'store', 'storeId',
            'aFrom', 'aTo', 'bFrom', 'bTo',
            'summaryA', 'summaryB', 'growth',
            'comparison', 'skuBaru', 'skuHilang'
        ));
    }

    /**
     * Build summary dari collection sales.
     */
    private function buildSummary($rows): array
    {
        return [
            'total_rows'  => $rows->count(),
            'net_revenue' => $rows->sum('net_revenue'),
            'total_hpp'   => $rows->sum(fn($r) => $r->hpp_total ?? 0),
            'profit'      => $rows->sum(fn($r) => $r->profit ?? 0),
        ];
    }

    /**
     * Hitung growth % dari nilai A ke B.
     */
    private function calcGrowth($valueA, $valueB): ?float
    {
        if ($valueA == 0 && $valueB == 0) return 0;
        if ($valueA == 0) return null; // tidak bisa hitung growth dari 0
        return round((($valueB - $valueA) / abs($valueA)) * 100, 1);
    }

    /**
     * Group sales by SKU.
     */
    private function groupBySku($rows): array
    {
        $grouped = [];
        foreach ($rows->groupBy('marketplace_sku') as $sku => $items) {
            $first = $items->first();
            $grouped[$sku] = [
                'sku'          => $sku,
                'nama_produk'  => $first->product?->nama_produk ?? '-',
                'qty'          => $items->sum('qty'),
                'net_revenue'  => $items->sum('net_revenue'),
                'hpp_total'    => $items->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'       => $items->sum(fn($r) => $r->profit ?? 0),
            ];
        }
        return $grouped;
    }

    /**
     * Tentukan status SKU: baru / hilang / tetap.
     */
    private function getSkuStatus($a, $b): string
    {
        if ($a === null && $b !== null) return 'baru';
        if ($a !== null && $b === null) return 'hilang';
        return 'tetap';
    }

    /**
     * AJAX: Return HTML tabel SKU (untuk pagination tanpa reload).
     */
    public function skuTableHtml(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));
        $page    = (int) $request->input('page', 1);
        $perPage = 25;
        $excludedOrderIds = $this->excludedProfitOrderIds($storeId);

        $query = Sale::with('product')->where('marketplace', 'shopee');
        if ($storeId) $query->where('store_id', $storeId);
        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);
        if ($excludedOrderIds !== []) $query->whereNotIn('order_id', $excludedOrderIds);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                ->orWhere('order_id', 'LIKE', "%{$search}%")
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('nama_produk', 'LIKE', "%{$search}%")
                        ->orWhere('sku_prefix', 'LIKE', "%{$search}%");
                });
            });
        }

        $rows = $query->get();

        $allPerProduk = $rows->groupBy(function ($row) {
            $sku = trim((string) $row->marketplace_sku);
            return $sku !== '' ? $sku : '__NO_SKU__';
        })->map(function ($group, $skuKey) {
            $first = $group->first();
            return [
                'marketplace_sku' => $skuKey === '__NO_SKU__' ? '(tanpa SKU)' : $skuKey,
                'product_id'      => $first->product_id,
                'nama_produk'     => $first->product?->nama_produk ?? '-',
                'internal_sku'    => $first->product?->sku_internal ?? '-',
                'total_qty'       => $group->sum('qty'),
                'net_revenue'     => $group->sum('net_revenue'),
                'hpp_total'       => $group->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'          => $group->sum(fn($r) => $r->profit ?? 0),
                'mapped'          => $first->product_id !== null,
            ];
        })->sortByDesc('profit')->values();

        $totalProduk = $allPerProduk->count();
        $perProduk   = $allPerProduk->slice(($page - 1) * $perPage, $perPage)->values();

        // ✅ Qty = jumlah baris Sale (sama seperti popup)
        $perProduk = $perProduk->map(function ($item) use ($storeId, $from, $to, $excludedOrderIds) {
            if ($item['marketplace_sku'] === '(tanpa SKU)' || str_ends_with($item['marketplace_sku'], ' (Isi Grosir)')) {
                return $item;
            }

            $qtyQuery = Sale::where('marketplace', 'shopee')
                ->where('marketplace_sku', $item['marketplace_sku'])
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to));
            if ($excludedOrderIds !== []) $qtyQuery->whereNotIn('order_id', $excludedOrderIds);
            $item['total_qty'] = (int) $qtyQuery->sum('qty');

            return $item;
        });

        return view('shopee.partials.sku-table', compact(
            'perProduk', 'totalProduk', 'page', 'perPage', 'storeId', 'from', 'to', 'search'
        ))->render();
    }

     public function exportPdf(Request $request)
    {
        $storeId = $request->input('store');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $search  = trim($request->input('search', ''));

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;
        $excludedOrderIds = $this->excludedProfitOrderIds($storeId);

        // ============ QUERY SALES ============
        $query = Sale::with('product')->where('marketplace', 'shopee');
        if ($storeId) $query->where('store_id', $storeId);
        if ($from) $query->whereDate('tanggal', '>=', $from);
        if ($to)   $query->whereDate('tanggal', '<=', $to);
        if ($excludedOrderIds !== []) $query->whereNotIn('order_id', $excludedOrderIds);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('marketplace_sku', 'LIKE', "%{$search}%")
                ->orWhere('order_id', 'LIKE', "%{$search}%")
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('nama_produk', 'LIKE', "%{$search}%")
                        ->orWhere('sku_prefix', 'LIKE', "%{$search}%");
                });
            });
        }

        $rows = $query->get();

        // ============ SUMMARY ============
        $summary = [
            'total_rows'    => $rows->count(),
            'net_revenue'   => $rows->sum('net_revenue'),
            'total_hpp'     => $rows->sum(fn($r) => $r->hpp_total ?? 0),
            'profit'        => $rows->sum(fn($r) => $r->profit ?? 0),
            'biaya_komisi'  => $rows->sum('biaya_komisi'),
            'biaya_layanan' => $rows->sum('biaya_layanan'),
            'biaya_ongkir'  => $rows->sum('biaya_ongkir'),
            'biaya_afiliasi'=> $rows->sum('biaya_afiliasi'),
            'biaya_promosi' => $rows->sum('biaya_promosi'),
            'biaya_pajak'   => $rows->sum('biaya_pajak'),
            'biaya_lainnya' => $rows->sum('biaya_lainnya'),
        ];

        $summary['transaksi_matched'] = $rows->where('status', 'matched')->count();
        $summary['total_transaksi']   = $rows->count();

        $skuUnik = $rows->pluck('marketplace_sku')->filter()->unique();
        $skuDimapping = $rows->where('status', 'matched')->pluck('marketplace_sku')->filter()->unique();
        $summary['sku_mapped'] = $skuDimapping->count();
        $summary['sku_total']  = $skuUnik->count();

        // ============ PER PRODUK (SEMUA, TANPA PAGINATION) ============
        $perProduk = $rows->groupBy('marketplace_sku')->map(function ($group) {
            $first = $group->first();
            return [
                'marketplace_sku' => $first->marketplace_sku,
                'product_id'      => $first->product_id,
                'nama_produk'     => $first->product?->nama_produk ?? '-',
                'internal_sku'    => $first->product?->sku_internal ?? '-',
                'total_qty'       => $group->sum('qty'),
                'net_revenue'     => $group->sum('net_revenue'),
                'hpp_total'       => $group->sum(fn($r) => $r->hpp_total ?? 0),
                'profit'          => $group->sum(fn($r) => $r->profit ?? 0),
                'mapped'          => $first->product_id !== null,
            ];
        })->sortByDesc('profit')->values();

        // ============ DATA UNTUK PDF ============
        $data = [
            'store'         => $store,
            'from'          => $from,
            'to'            => $to,
            'search'        => $search,
            'summary'       => $summary,
            'perProduk'     => $perProduk,
            'generated_at'  => now()->translatedFormat('d F Y, H:i'),
        ];

        // ============ GENERATE PDF ============
        $pdf = Pdf::loadView('shopee.pdf.report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'sans-serif',
            ]);

        $fileName = 'laporan-shopee-' . ($store?->nama_toko ?? 'all') . '-' . now()->format('Ymd-His') . '.pdf';
        $fileName = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $fileName);

        return $pdf->download($fileName);
    }

    public function skuDetailHtml(Request $request)
    {
        $storeId = $request->input('store');
        $sku     = $request->input('sku');
        $from    = $request->input('from');
        $to      = $request->input('to');
        $excludedOrderIds = $this->excludedProfitOrderIds($storeId);

        $query = Sale::where('marketplace', 'shopee')
                     ->where('marketplace_sku', $sku);

        if ($storeId) $query->where('store_id', $storeId);
        if ($from)    $query->whereDate('tanggal', '>=', $from);
        if ($to)      $query->whereDate('tanggal', '<=', $to);
        if ($excludedOrderIds !== []) $query->whereNotIn('order_id', $excludedOrderIds);

        $sales = $query->orderBy('tanggal', 'desc')->get();

        return view('shopee.partials.sku-detail-modal', compact('sales', 'sku'))->render();
    }

    private function excludedProfitOrderIds($storeId): array
    {
        $orderItemIds = DB::table('shopee_order_items')
            ->join('imports as order_imports', 'order_imports.id', '=', 'shopee_order_items.import_id')
            ->when($storeId, fn($query) => $query->where('order_imports.store_id', $storeId))
            ->where(function ($query) {
                $query->where('shopee_order_items.returned_quantity', '>', 0)
                    ->orWhere('shopee_order_items.status_pembatalan', 'LIKE', '%Disetujui%')
                    ->orWhere('shopee_order_items.status_pembatalan', 'LIKE', '%Dibatalkan%')
                    ->orWhere('shopee_order_items.status_pesanan', 'LIKE', '%Dibatalkan%');
            })
            ->pluck('shopee_order_items.no_pesanan');

        $returnReportIds = DB::table('shopee_returns')
            ->join('imports as return_imports', 'return_imports.id', '=', 'shopee_returns.import_id')
            ->when($storeId, fn($query) => $query->where('return_imports.store_id', $storeId))
            ->where(function ($query) {
                $query->whereRaw('LOWER(return_imports.file_name) LIKE ?', ['%cancel%'])
                    ->orWhereRaw('LOWER(return_imports.file_name) LIKE ?', ['%batal%'])
                    ->orWhereRaw('LOWER(return_imports.file_name) LIKE ?', ['%failed_delivery%'])
                    ->orWhereRaw('LOWER(return_imports.file_name) LIKE ?', ['%failed delivery%'])
                    ->orWhereIn('shopee_returns.tipe_laporan', ['cancel', 'shipping_failed'])
                    ->orWhere(function ($statusQuery) {
                        $statusQuery->where('shopee_returns.tipe_laporan', 'return')
                            ->whereRaw("LOWER(COALESCE(shopee_returns.status_pengajuan, '')) NOT LIKE ?", ['%ditolak%'])
                            ->whereRaw("LOWER(COALESCE(shopee_returns.status_pengajuan, '')) NOT LIKE ?", ['%dibatalkan%']);
                    });
            })
            ->pluck('shopee_returns.no_pesanan');

        return $orderItemIds
            ->merge($returnReportIds)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function adSpendPeriodKey(?string $from, ?string $to): string
    {
        return ($from ?: 'any') . '|' . ($to ?: 'any');
    }
}