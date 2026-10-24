<?php

namespace App\Http\Controllers;

use App\Models\BrilinkPengeluaran;
use App\Models\Store;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PiutangController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $kategoriFilter = $request->input('kategori', 'all');

        // ✅ Tidak ada default tanggal, biarkan kosong
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput;
        $to   = $toInput;

        if ($from && !$to) {
            $to = $from;
        } elseif ($to && !$from) {
            $from = $to;
        }

        $stores = Store::where('marketplace', 'brilink')->orderBy('nama_toko')->get();
        
        if (!$storeId) {
            $storeId = 'all';
        }
        $store = ($storeId !== 'all') ? Store::find($storeId) : null;

        // 1. Ambil Daftar Kategori Piutang
        $kategoriList = BrilinkPengeluaran::whereRaw('LOWER(kategori) LIKE ?', ['%piutang%'])
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori')
            ->sort()
            ->values();

        // 2. Query Data Piutang
        $query = BrilinkPengeluaran::with('report.store')
            ->whereRaw('LOWER(kategori) LIKE ?', ['%piutang%'])
            ->when($storeId !== 'all', function ($q) use ($storeId) {
                $q->whereHas('report', fn($rq) => $rq->where('store_id', $storeId));
            })
            ->when($from, function ($q) use ($from) {
                $q->whereHas('report', fn($rq) => $rq->whereDate('periode_start', '>=', $from));
            })
            ->when($to, function ($q) use ($to) {
                $q->whereHas('report', fn($rq) => $rq->whereDate('periode_start', '<=', $to));
            });

        if ($kategoriFilter !== 'all') {
            $query->where('kategori', $kategoriFilter);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        if ($status === 'belum') {
            $query->where('is_paid', false);
        } elseif ($status === 'sudah') {
            $query->where('is_paid', true);
        }

        $piutangList = $query->orderByDesc('id')->get()->map(function ($p) {
            $sisa = max(0, (int) $p->nominal_kas - (int) $p->paid_amount);
            
            return [
                'id'           => $p->id,
                'keterangan'   => $p->keterangan ?? '-',
                'kategori'     => $p->kategori ?? '-',
                'nominal'      => (int) $p->nominal_kas,
                'terbayar'     => (int) $p->paid_amount,
                'sisa'         => $sisa,
                'is_paid'      => (bool) $p->is_paid,
                'tanggal'      => $p->report?->periode_start?->format('Y-m-d') ?? '-',
                'tanggal_label'=> $p->report?->periode_start?->translatedFormat('d M Y') ?? '-',
                'store_name'   => $p->report?->store?->nama_toko ?? '-',
                'paid_at'      => $p->paid_at,
            ];
        });

        $summary = [
            'total_nominal'   => (int) $piutangList->sum('nominal'),
            'total_terbayar'  => (int) $piutangList->sum('terbayar'),
            'sisa_piutang'    => (int) $piutangList->sum('sisa'),
            'jumlah_data'     => $piutangList->count(),
        ];

        return view('piutang.index', [
            'stores'         => $stores,
            'store'          => $store,
            'storeId'        => (string) $storeId,
            'piutangList'    => $piutangList,
            'summary'        => $summary,
            'kategoriList'   => $kategoriList,
            'kategoriFilter' => $kategoriFilter,
            'search'         => $search,
            'status'         => $status,
            'from'           => $fromInput,
            'to'             => $toInput,
        ]);
    }

    // ===================================================
    // BAYAR PIUTANG (SINGLE) — Sinkron dengan BRILink
    // ===================================================
    public function bayarPiutang(BrilinkPengeluaran $pengeluaran)
    {
        $kategori = strtolower($pengeluaran->kategori ?? '');
        if (!str_contains($kategori, 'piutang')) {
            return response()->json(['message' => 'Transaksi ini bukan piutang.'], 422);
        }

        $pengeluaran->update([
            'is_paid'     => true,
            'paid_at'     => now()->format('Y-m-d'),
            'paid_amount' => $pengeluaran->nominal_kas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Piutang berhasil ditandai lunas.',
        ]);
    }

    public function printInvoiceBulk()
    {
        $storeId = $request->input('store', 'all');
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $kategoriFilter = $request->input('kategori', 'all');

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // ✅ Tidak ada default tanggal
        $from = $fromInput;
        $to   = $toInput;

        if ($from && !$to) $to = $from;
        elseif ($to && !$from) $from = $to;
        
        // Ambil SEMUA data piutang tanpa filter
        $piutangData = BrilinkPengeluaran::with('report.store')
            ->whereRaw('LOWER(kategori) LIKE ?', ['%piutang%'])
            ->orderByDesc('id')
            ->get();

        if ($piutangData->isEmpty()) {
            abort(404, 'Tidak ada data piutang untuk dicetak.');
        }

        $items = $piutangData->map(function ($p) {
            $tgl = $p->report?->periode_start ? $p->report->periode_start->format('Ymd') : now()->format('Ymd');
            $noInvoice = 'INV-PTG-' . $tgl . '-' . str_pad((string) $p->id, 4, '0', STR_PAD_LEFT);

            if ($p->is_paid) {
                $statusText = 'Lunas';
            } elseif ($p->paid_amount > 0) {
                $statusText = 'Sebagian';
            } else {
                $statusText = 'Belum Lunas';
            }

            return [
                'no_invoice' => $noInvoice,
                'tanggal'    => $p->report?->periode_start?->format('d/m/Y') ?? '-',
                'keterangan' => $p->keterangan ?? '-',
                'kategori'   => $p->kategori ?? '-',
                'nominal'    => (int) $p->nominal_kas,
                'status'     => $statusText,
            ];
        });

        $totalPiutang = $items->sum('nominal');
        $totalSisaPiutang = $piutangData->sum(function($p) {
            return max(0, $p->nominal_kas - $p->paid_amount);
        });

        $pdf = Pdf::loadView('piutang.invoice-pdf', [
            'items'            => $items,
            'store'            => null, // Semua Toko
            'totalPiutang'     => $totalPiutang,
            'totalSisaPiutang' => $totalSisaPiutang,
            'mode'             => 'bulk',
            'tanggalCetak'     => now()->format('d/m/Y H:i'),
            'filterFrom'       => 'all', // Tidak difilter
            'filterTo'         => 'all',
        ])->setPaper('A4', 'portrait');

        $fileName = 'Invoice-Piutang-Semua-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->stream($fileName);
    }

    // ===================================================
    // BAYAR PIUTANG BULK (FIFO) — Sinkron dengan BRILink
    // ===================================================
    public function bayarPiutangBulk(Request $request)
    {
        $request->validate([
            'store'    => 'required',
            'kategori' => 'required|string',
            'nominal'  => 'required',
            'from'     => 'nullable|date',
            'to'       => 'nullable|date',
            'search'   => 'nullable|string',
            'status'   => 'nullable|string',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        $storeId = $request->store;
        $kategoriFilter = trim($request->kategori);
        $from = $request->from;
        $to = $request->to;
        $search = trim((string) $request->search);
        $status = $request->status;

        // Ambil piutang yang BELUM lunas dengan kategori sama, urut dari yang terlama (FIFO)
        $query = BrilinkPengeluaran::with('report')
            ->where('kategori', $kategoriFilter)
            ->where('is_paid', false)
            ->whereHas('report', function ($q) use ($storeId, $from, $to) {
                $q->when($storeId !== 'all', fn($sq) => $sq->where('store_id', $storeId))
                ->when($from, fn($sq) => $sq->whereDate('periode_start', '>=', $from))
                ->when($to, fn($sq) => $sq->whereDate('periode_start', '<=', $to));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        // Urut berdasarkan tanggal report (paling awal dulu = FIFO)
        $piutangs = $query->get()->sortBy(function ($p) {
            return $p->report->periode_start?->format('Y-m-d') . '|' . $p->id;
        })->values();

        if ($piutangs->isEmpty()) {
            return response()->json(['message' => 'Tidak ada piutang yang belum lunas untuk kategori ini.'], 422);
        }

        $remaining = $nominal;
        $terbayar = 0;
        $lunasCount = 0;
        $partialCount = 0;

        DB::beginTransaction();
        try {
            foreach ($piutangs as $p) {
                if ($remaining <= 0) break;

                $sisaUtang = $p->nominal_kas - $p->paid_amount;
                if ($sisaUtang <= 0) continue;

                $bayar = min($remaining, $sisaUtang);
                $newPaid = $p->paid_amount + $bayar;

                $p->update([
                    'paid_amount' => $newPaid,
                    'is_paid'     => $newPaid >= $p->nominal_kas,
                    'paid_at'     => $newPaid >= $p->nominal_kas ? now()->format('Y-m-d') : $p->paid_at,
                ]);

                $remaining -= $bayar;
                $terbayar += $bayar;

                if ($newPaid >= $p->nominal_kas) {
                    $lunasCount++;
                } else {
                    $partialCount++;
                }
            }

            DB::commit();

            $sisaKembali = $remaining; 
            $msg = "Pembayaran Rp " . number_format($terbayar, 0, ',', '.') .
                " berhasil. {$lunasCount} piutang lunas";
            if ($partialCount > 0) $msg .= ", {$partialCount} piutang dibayar sebagian";
            if ($sisaKembali > 0) $msg .= ". Kelebihan bayar: Rp " . number_format($sisaKembali, 0, ',', '.');

            return response()->json([
                'success' => true,
                'message' => $msg,
                'lunas_count' => $lunasCount,
                'partial_count' => $partialCount,
                'sisa_kembali' => $sisaKembali,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal bayar: ' . $e->getMessage()], 500);
        }
    }
}