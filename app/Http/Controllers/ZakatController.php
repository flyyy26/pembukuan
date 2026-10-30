<?php

namespace App\Http\Controllers;

use App\Models\BrilinkAccount;
use App\Models\BrilinkPengeluaran;
use App\Models\BrilinkReport;
use App\Models\Store;
use App\Models\Product;
use App\Models\ZakatSaldoOnline;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Hutang;
use App\Models\TabunganAccount;
use App\Models\TabunganTransaction;
use App\Models\ZakatPembayaran;

class ZakatController extends Controller
{
    private const RATE = 0.025;

    public function index(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $from = $request->input('from');
        $to = $request->input('to');

        $stores = Store::where('marketplace', 'brilink')
            ->orderBy('nama_toko')
            ->get();

        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // ============================================
        // 1) SALDO
        // ============================================
        $reportsQuery = BrilinkReport::query()
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('periode_start')
            ->orderByDesc('id');

        // Ambil report terbaru HANYA dari rentang tanggal yang difilter
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

        $saldoOnlineList = ZakatSaldoOnline::orderBy('id')->get();
        $totalSaldoOnline = (int) $saldoOnlineList->sum('saldo');
        $zakatSaldoOnline = (int) round($totalSaldoOnline * self::RATE);

        // Gabungkan dengan total saldo sebelumnya
        $totalSaldo += $totalSaldoOnline;
        $zakatSaldo += $zakatSaldoOnline;

        // ============================================
        // 2) PIUTANG
        // ============================================
        $piutangQuery = BrilinkPengeluaran::query()
            ->whereRaw('LOWER(kategori) LIKE ?', ['%piutang%'])
            ->when($storeId !== 'all', function ($q) use ($storeId) {
                $q->whereHas('report', fn($rq) => $rq->where('store_id', $storeId));
            })
            ->when($from, fn($q) => $q->whereHas('report', fn($rq) => $rq->whereDate('periode_start', '>=', $from)))
            ->when($to, fn($q) => $q->whereHas('report', fn($rq) => $rq->whereDate('periode_start', '<=', $to)));

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
        // 3) BARANG ONLINE
        // ============================================
        $barangList = Product::query()
            ->where('stok', '>', 0)
            ->orderBy('nama_produk')
            ->get()
            ->map(function ($p) {
                $total = (int) $p->stok * (int) $p->hpp;
                return [
                    'id'           => $p->id,
                    'nama_barang'  => $p->nama_produk,
                    'sku_prefix'   => $p->sku_prefix,
                    'qty'          => (int) $p->stok,
                    'harga_satuan' => (int) $p->hpp,
                    'total'        => $total,
                    'zakat'        => (int) round($total * self::RATE),
                ];
            });

        $totalBarang = (int) $barangList->sum('total');
        $zakatBarang = (int) round($totalBarang * self::RATE);

        // ============================================
        // 4) HUTANG (Pengurang)
        // ============================================
        $hutangList = Hutang::query()
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->orderByDesc('tanggal')
            ->get();
        $totalHutang = (int) $hutangList->sum('nominal');

        // ============================================
        // 5) TABUNGAN (Pengurang)
        // ============================================
        $tabunganList = TabunganAccount::orderBy('nama')->get()->map(function ($acc) use ($from, $to) {
            // Filter transaksi berdasarkan tanggal (asumsi kolom created_at)
            $transactions = $acc->transactions()
                ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));

            $setoran = (clone $transactions)->where('tipe', 'setoran')->sum('nominal');
            $penarikan = (clone $transactions)->where('tipe', 'penarikan')->sum('nominal');
            
            return [
                'id' => $acc->id,
                'nama' => $acc->nama,
                'saldo' => max(0, $setoran - $penarikan),
            ];
        })->filter(fn($a) => $a['saldo'] > 0);

        $totalTabungan = (int) $tabunganList->sum('saldo');

        // ============================================
        // 6) TOTAL ZAKAT
        // ============================================
        $totalZakatAset = $zakatSaldo + $zakatPiutang + $zakatBarang;
        $zakatHutang = (int) round($totalHutang * self::RATE);
        $zakatTabungan = (int) round($totalTabungan * self::RATE);
        $totalZakatKewajiban = $zakatHutang + $zakatTabungan;
        $totalZakat = $totalZakatAset - $totalZakatKewajiban;
        if ($totalZakat < 0) $totalZakat = 0;

        $totalHarta = $totalSaldo + $totalPiutang + $totalBarang;
        $totalKewajiban = $totalHutang + $totalTabungan;
        $hartaBersih = max(0, $totalHarta - $totalKewajiban);

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
            'saldoOnlineList'=> $saldoOnlineList,
            'totalSaldoOnline'=> $totalSaldoOnline,
            'zakatSaldoOnline'=> $zakatSaldoOnline,
            'hutangList'     => $hutangList,
            'totalHutang'    => $totalHutang,
            'totalTabungan'  => $totalTabungan,
            'tabunganList'   => $tabunganList,
            
            'totalHarta'     => $totalHarta,
            'hartaBersih'    => $hartaBersih,
            'zakatHutang'    => $zakatHutang,
            'zakatTabungan'  => $zakatTabungan,
            
            // ✅ Kirim balik nilai from & to ke view
            'from'           => $from,
            'to'             => $to,
        ]);
    }

    // ============================================
    // PRINT PDF
    // ============================================
    public function printPdf(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $store   = ($storeId !== 'all') ? Store::find($storeId) : null;

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

        $saldoOnlineList = ZakatSaldoOnline::orderBy('id')->get();
        $totalSaldoOnline = (int) $saldoOnlineList->sum('saldo');
        $zakatSaldoOnline = (int) round($totalSaldoOnline * self::RATE);

        $totalSaldo += $totalSaldoOnline;
        $zakatSaldo += $zakatSaldoOnline;

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

        // ============================================
        // 3) BARANG
        // ============================================
        $barangList = Product::query()
            ->where('stok', '>', 0)
            ->orderBy('nama_produk')
            ->get()
            ->map(function ($p) {
                $total = (int) $p->stok * (int) $p->hpp;
                return [
                    'nama_barang'  => $p->nama_produk,
                    'sku_prefix'   => $p->sku_prefix,
                    'qty'          => (int) $p->stok,
                    'harga_satuan' => (int) $p->hpp,
                    'total'        => $total,
                    'zakat'        => (int) round($total * self::RATE),
                ];
            });

        $totalBarang = (int) $barangList->sum('total');
        $zakatBarang = (int) round($totalBarang * self::RATE);

        // ============================================
        // 4) HUTANG & TABUNGAN (PENGURANG)
        // ============================================
        $hutangList = Hutang::orderByDesc('tanggal')->get();
        $totalHutang = (int) $hutangList->sum('nominal');
        $zakatHutang = (int) round($totalHutang * self::RATE);

        $tabunganList = TabunganAccount::orderBy('nama')->get()->map(function ($acc) {
            $setoran = $acc->transactions()->where('tipe', 'setoran')->sum('nominal');
            $penarikan = $acc->transactions()->where('tipe', 'penarikan')->sum('nominal');
            return [
                'nama' => $acc->nama,
                'saldo' => max(0, $setoran - $penarikan),
            ];
        })->filter(fn($a) => $a['saldo'] > 0);

        $totalTabungan = (int) $tabunganList->sum('saldo');
        $zakatTabungan = (int) round($totalTabungan * self::RATE);

        // ============================================
        // 5) TOTAL ZAKAT AKHIR
        // ============================================
        $totalZakatAset = $zakatSaldo + $zakatPiutang + $zakatBarang;
        $totalZakatKewajiban = $zakatHutang + $zakatTabungan;
        $totalZakat = $totalZakatAset - $totalZakatKewajiban;
        if ($totalZakat < 0) $totalZakat = 0;

        $pdf = Pdf::loadView('zakat.invoice-pdf', [
            'store'          => $store,
            'storeId'        => $storeId,
            'totalSaldo'     => $totalSaldo,
            'zakatSaldo'     => $zakatSaldo,
            'breakdownSaldo' => $breakdownSaldo,
            'piutangList'    => $piutangList,
            'totalPiutang'   => $totalPiutang,
            'zakatPiutang'   => $zakatPiutang,
            'saldoOnlineList'=> $saldoOnlineList,
            'barangList'     => $barangList,
            'totalBarang'    => $totalBarang,
            'zakatBarang'    => $zakatBarang,
            
            // ✅ Data Pengurang
            'hutangList'     => $hutangList,
            'totalHutang'    => $totalHutang,
            'zakatHutang'    => $zakatHutang,
            'tabunganList'   => $tabunganList,
            'totalTabungan'  => $totalTabungan,
            'zakatTabungan'  => $zakatTabungan,
            'totalZakat'     => $totalZakat,

            'rate'           => self::RATE,
            'tanggalCetak'   => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        $fileName = 'Laporan-Zakat-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->stream($fileName);
    }

    public function storeSaldoOnlineAjax(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|string|max:50',
            'saldo'       => 'required',
        ]);

        $saldo = (int) preg_replace('/[^0-9]/', '', (string) $request->saldo);
        
        ZakatSaldoOnline::create([
            'marketplace' => $validated['marketplace'],
            'saldo'       => $saldo,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Saldo online '{$validated['marketplace']}' berhasil ditambahkan.",
        ]);
    }

    public function destroySaldoOnlineAjax(ZakatSaldoOnline $saldoOnline)
    {
        $nama = $saldoOnline->marketplace;
        $saldoOnline->delete();

        return response()->json([
            'success' => true,
            'message' => "Saldo online '{$nama}' berhasil dihapus.",
        ]);
    }

    public function bayarZakat(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'total_zakat' => 'required|integer',
            'total_saldo' => 'required|integer',
            'total_piutang' => 'required|integer',
            'total_barang' => 'required|integer',
            'total_hutang' => 'required|integer',
            'total_tabungan' => 'required|integer',
            'keterangan' => 'nullable|string|max:255',
            'detail_breakdown' => 'nullable|string',
        ]);

        ZakatPembayaran::create([
            'tanggal' => $request->tanggal,
            'total_zakat' => $request->total_zakat,
            'total_saldo' => $request->total_saldo,
            'total_piutang' => $request->total_piutang,
            'total_barang' => $request->total_barang,
            'total_hutang' => $request->total_hutang,
            'total_tabungan' => $request->total_tabungan,
            'keterangan' => $request->keterangan,
            'detail_breakdown' => json_decode($request->detail_breakdown, true),
        ]);

        return response()->json(['success' => true, 'message' => 'Pembayaran zakat berhasil disimpan.']);
    }
}