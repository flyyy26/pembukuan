<?php

namespace App\Http\Controllers;

use App\Models\BrilinkAccount;
use App\Models\BrilinkPengeluaran;
use App\Models\BrilinkReport;
use App\Models\Product;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ZakatController extends Controller
{
    private const RATE = 0.025;

    public function index(Request $request)
    {
        $storeId = $request->input('store', 'all');

        $stores = Store::where('marketplace', 'brilink')
            ->orderBy('nama_toko')
            ->get();

        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // ============================================
        // 1) SALDO
        // ============================================
        $reportsQuery = BrilinkReport::query()
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->orderByDesc('periode_start')
            ->orderByDesc('id');

        $latestReports = $reportsQuery->get()
            ->groupBy('store_id')
            ->map(fn($items) => $items->first())
            ->values();

        $reportIds = $latestReports->pluck('id')->all();

        $kategoriUrutan = [
            'kas'       => 1,
            'pulsa'     => 2,
            'ppob'      => 3,
            'brilink'   => 4,
            'mbanking'  => 5,
            'brangkas'  => 6,
            'pos'       => 7,
        ];

        $breakdownSaldo = BrilinkAccount::whereIn('brilink_report_id', $reportIds)
            ->get()
            ->groupBy(fn($a) => mb_strtolower(trim($a->nama_rekening)))
            ->map(function ($items) use ($kategoriUrutan) {
                $first = $items->first();
                $saldo = (int) $items->sum('saldo_akhir');
                $keterangan = $items->pluck('keterangan')->filter()->first() ?? '-';

                return [
                    'nama_rekening'  => $first->nama_rekening,
                    'keterangan'     => $keterangan,
                    'saldo'          => $saldo,
                    'zakat'          => (int) round($saldo * self::RATE),
                    '_sort_kategori' => $kategoriUrutan[strtolower(trim($keterangan))] ?? 99,
                ];
            })
            ->sortBy([
                ['_sort_kategori', 'asc'],
                ['nama_rekening', 'asc'],
            ])
            ->values()
            ->map(function ($item) {
                unset($item['_sort_kategori']);
                return $item;
            })
            ->toArray();

        $totalSaldo = (int) collect($breakdownSaldo)->sum('saldo');
        $zakatSaldo = (int) round($totalSaldo * self::RATE);

        // ============================================
        // 2) PIUTANG
        // ============================================
        $piutangQuery = BrilinkPengeluaran::query()
            ->whereRaw('LOWER(kategori) LIKE ?', ['%piutang%'])
            ->when($storeId !== 'all', function ($q) use ($storeId) {
                $q->whereHas('report', fn($rq) => $rq->where('store_id', $storeId));
            });

        $piutangList = $piutangQuery->get()
            ->map(function ($p) {
                $sisa = max(0, (int) $p->nominal_kas - (int) $p->paid_amount);
                $nilaiUntukZakat = ($sisa > 0) ? $sisa : (int) $p->nominal_kas;

                return [
                    'id'         => $p->id,
                    'keterangan' => $p->keterangan ?? '-',
                    'kategori'   => $p->kategori ?? '-',
                    'nominal'    => (int) $p->nominal_kas,
                    'terbayar'   => (int) $p->paid_amount,
                    'sisa'       => $sisa,
                    'zakat'      => (int) round($nilaiUntukZakat * self::RATE),
                    'is_paid'    => (bool) $p->is_paid,
                ];
            })
            ->sortByDesc('nominal')
            ->values()
            ->toArray();

        $totalPiutang = (int) collect($piutangList)->sum(function ($p) {
            return $p['sisa'] > 0 ? $p['sisa'] : $p['nominal'];
        });
        $zakatPiutang = (int) round($totalPiutang * self::RATE);

        // ============================================
        // 3) BARANG ONLINE — dari Master Produk
        //    hanya produk aktif & stok > 0
        // ============================================
        $barangList = Product::query()
            ->where('is_active', true)
            ->orderBy('nama_produk')
            ->get()
            ->map(function (Product $p) {
                $harga = (int) $p->hpp;
                $qty   = (int) $p->stok;

                return [
                    'id'           => $p->id,
                    'sku_internal' => $p->sku_internal,
                    'nama_produk'  => $p->nama_produk,
                    'gambar_url'   => $p->gambar_url,
                    'qty'          => $qty,
                    'harga_satuan' => $harga,
                    'total'        => $qty * $harga,
                    'zakat'        => (int) round($qty * $harga * self::RATE),
                ];
            });

        $totalBarang = (int) $barangList->sum('total');
        $zakatBarang = (int) round($totalBarang * self::RATE);

        // ============================================
        // 4) TOTAL ZAKAT
        // ============================================
        $totalZakat = $zakatSaldo + $zakatPiutang + $zakatBarang;

        return view('zakat.index', [
            'stores'         => $stores,
            'store'          => $store,
            'storeId'        => (string) $storeId,
            'totalSaldo'     => $totalSaldo,
            'zakatSaldo'     => $zakatSaldo,
            'breakdownSaldo' => $breakdownSaldo,
            'piutangList'    => $piutangList,
            'totalPiutang'   => $totalPiutang,
            'zakatPiutang'   => $zakatPiutang,
            'barangList'     => $barangList,
            'totalBarang'    => $totalBarang,
            'zakatBarang'    => $zakatBarang,
            'totalZakat'     => $totalZakat,
        ]);
    }

    // ============================================
    // PRINT PDF
    // ============================================
    public function printPdf(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $store   = ($storeId !== 'all') ? Store::find($storeId) : null;

        // Saldo
        $reportsQuery = BrilinkReport::query()
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->orderByDesc('periode_start')
            ->orderByDesc('id');

        $latestReports = $reportsQuery->get()
            ->groupBy('store_id')
            ->map(fn($items) => $items->first())
            ->values();

        $reportIds = $latestReports->pluck('id')->all();

        $kategoriUrutan = [
            'kas' => 1, 'pulsa' => 2, 'ppob' => 3, 'brilink' => 4,
            'mbanking' => 5, 'brangkas' => 6, 'pos' => 7,
        ];

        $breakdownSaldo = BrilinkAccount::whereIn('brilink_report_id', $reportIds)
            ->get()
            ->groupBy(fn($a) => mb_strtolower(trim($a->nama_rekening)))
            ->map(function ($items) use ($kategoriUrutan) {
                $first = $items->first();
                $saldo = (int) $items->sum('saldo_akhir');
                $keterangan = $items->pluck('keterangan')->filter()->first() ?? '-';

                return [
                    'nama_rekening'  => $first->nama_rekening,
                    'saldo'          => $saldo,
                    'zakat'          => (int) round($saldo * self::RATE),
                    '_sort_kategori' => $kategoriUrutan[strtolower(trim($keterangan))] ?? 99,
                ];
            })
            ->sortBy([
                ['_sort_kategori', 'asc'],
                ['nama_rekening', 'asc'],
            ])
            ->values()
            ->map(function ($item) {
                unset($item['_sort_kategori']);
                return $item;
            })
            ->toArray();

        $totalSaldo = (int) collect($breakdownSaldo)->sum('saldo');
        $zakatSaldo = (int) round($totalSaldo * self::RATE);

        // Piutang
        $piutangQuery = BrilinkPengeluaran::query()
            ->whereRaw('LOWER(kategori) LIKE ?', ['%piutang%'])
            ->when($storeId !== 'all', function ($q) use ($storeId) {
                $q->whereHas('report', fn($rq) => $rq->where('store_id', $storeId));
            });

        $piutangList = $piutangQuery->get()
            ->map(function ($p) {
                $sisa = max(0, (int) $p->nominal_kas - (int) $p->paid_amount);
                $nilaiUntukZakat = ($sisa > 0) ? $sisa : (int) $p->nominal_kas;

                return [
                    'keterangan' => $p->keterangan ?? '-',
                    'nominal'    => (int) $p->nominal_kas,
                    'terbayar'   => (int) $p->paid_amount,
                    'sisa'       => $sisa,
                    'zakat'      => (int) round($nilaiUntukZakat * self::RATE),
                ];
            })
            ->sortByDesc('nominal')
            ->values()
            ->toArray();

        $totalPiutang = (int) collect($piutangList)->sum(function ($p) {
            return $p['sisa'] > 0 ? $p['sisa'] : $p['nominal'];
        });
        $zakatPiutang = (int) round($totalPiutang * self::RATE);

        // Barang — dari Master Produk
        $barangList = Product::query()
            ->where('is_active', true)
            ->orderBy('nama_produk')
            ->get()
            ->map(function (Product $p) {
                $harga = (int) $p->hpp;
                $qty   = (int) $p->stok;

                return [
                    'sku_internal' => $p->sku_internal,
                    'nama_produk'  => $p->nama_produk,
                    'qty'          => $qty,
                    'harga_satuan' => $harga,
                    'total'        => $qty * $harga,
                    'zakat'        => (int) round($qty * $harga * self::RATE),
                ];
            });

        $totalBarang = (int) $barangList->sum('total');
        $zakatBarang = (int) round($totalBarang * self::RATE);

        $totalZakat = $zakatSaldo + $zakatPiutang + $zakatBarang;

        $pdf = Pdf::loadView('zakat.invoice-pdf', [
            'store'          => $store,
            'storeId'        => $storeId,
            'totalSaldo'     => $totalSaldo,
            'zakatSaldo'     => $zakatSaldo,
            'breakdownSaldo' => $breakdownSaldo,
            'piutangList'    => $piutangList,
            'totalPiutang'   => $totalPiutang,
            'zakatPiutang'   => $zakatPiutang,
            'barangList'     => $barangList,
            'totalBarang'    => $totalBarang,
            'zakatBarang'    => $zakatBarang,
            'totalZakat'     => $totalZakat,
            'rate'           => self::RATE,
            'tanggalCetak'   => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        $fileName = 'Laporan-Zakat-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->stream($fileName);
    }
}