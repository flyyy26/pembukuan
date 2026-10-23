<?php

namespace App\Http\Controllers;

use App\Models\TabunganAccount;
use App\Models\TabunganTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TabunganController extends Controller
{
    // ============================================================
    // HALAMAN UTAMA
    // ============================================================
    public function index(Request $request)
    {
        $accountId = $request->input('account');
        $search    = trim($request->input('search', ''));
        $tipe      = $request->input('tipe', 'semua');

        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Default: bulan ini
        $from = $fromInput ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $toInput   ?? now()->endOfMonth()->format('Y-m-d');

        if ($from && !$to)       $to = $from;
        elseif ($to && !$from)   $from = $to;

        $accounts = TabunganAccount::orderByDesc('is_active')
            ->orderBy('nama')
            ->get();

        // ============================================================
        // STATISTIK PER AKUN (saldo real-time)
        // ============================================================
        $accountsWithSaldo = TabunganAccount::query()
            ->when($accountId && $accountId !== 'all', fn($q) => $q->where('id', $accountId))
            ->when($search !== '', fn($q) => $q->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            }))
            ->orderByDesc('is_active')
            ->orderBy('nama')
            ->get()
            ->map(function ($acc) {
                $setoran   = $acc->transactions()->where('tipe', 'setoran')->sum('nominal');
                $penarikan = $acc->transactions()->where('tipe', 'penarikan')->sum('nominal');
                return [
                    'id'          => $acc->id,
                    'nama'        => $acc->nama,
                    'keterangan'  => $acc->keterangan,
                    'is_active'   => $acc->is_active,
                    'total_setoran'   => (int) $setoran,
                    'total_penarikan' => (int) $penarikan,
                    'saldo'       => (int) ($setoran - $penarikan),
                ];
            });

        // ============================================================
        // RIWAYAT TRANSAKSI (sesuai filter)
        // ============================================================
        $transactionsQuery = TabunganTransaction::with('account')
            ->when($accountId && $accountId !== 'all', fn($q) => $q->where('tabungan_account_id', $accountId))
            ->when($tipe !== 'semua', fn($q) => $q->where('tipe', $tipe))
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('keterangan', 'LIKE', "%{$search}%")
                       ->orWhereHas('account', fn($aq) => $aq->where('nama', 'LIKE', "%{$search}%"));
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $transactions = $transactionsQuery->get();

        // ============================================================
        // SUMMARY CARDS
        // ============================================================
        $totalSaldoAll = TabunganAccount::all()->sum(fn($a) => $a->saldo);

        $bulanIni = now();
        $setoranBulanIni   = TabunganTransaction::where('tipe', 'setoran')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->sum('nominal');
        $penarikanBulanIni = TabunganTransaction::where('tipe', 'penarikan')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->sum('nominal');

        $summary = [
            'total_saldo'         => (int) $totalSaldoAll,
            'setoran_bulan_ini'   => (int) $setoranBulanIni,
            'penarikan_bulan_ini' => (int) $penarikanBulanIni,
            'jumlah_akun_aktif'   => TabunganAccount::aktif()->count(),
        ];

        return view('tabungan.index', [
            'accounts'         => $accounts,
            'accountsWithSaldo'=> $accountsWithSaldo,
            'transactions'     => $transactions,
            'summary'          => $summary,
            'accountId'        => $accountId ?: 'all',
            'search'           => $search,
            'tipe'             => $tipe,
            'from'             => $from,
            'to'               => $to,
            'fromInput'        => $fromInput,
            'toInput'          => $toInput,
        ]);
    }

    // ============================================================
    // CRUD AKUN
    // ============================================================
    public function storeAccountAjax(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $account = TabunganAccount::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Akun '{$account->nama}' berhasil ditambahkan.",
            'account' => $account,
        ]);
    }

    public function updateAccountAjax(Request $request, TabunganAccount $account)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $account->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Akun '{$account->nama}' berhasil diperbarui.",
        ]);
    }

    public function destroyAccountAjax(TabunganAccount $account)
    {
        // Hitung dulu untuk info konfirmasi
        $jumlahTransaksi = $account->transactions()->count();
        $nama = $account->nama;

        DB::beginTransaction();
        try {
            // FK sudah cascadeOnDelete → transaksi otomatis terhapus
            $account->delete();

            DB::commit();

            $msg = "Akun '{$nama}' berhasil dihapus";
            if ($jumlahTransaksi > 0) {
                $msg .= " beserta {$jumlahTransaksi} transaksinya";
            }
            $msg .= '.';

            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal hapus: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // CRUD TRANSAKSI
    // ============================================================
    public function storeTransactionAjax(Request $request)
    {
        $validated = $request->validate([
            'tabungan_account_id' => 'required|exists:tabungan_accounts,id',
            'tipe'                => 'required|in:setoran,penarikan',
            'nominal'             => 'required',
            'tanggal'             => 'required|date_format:Y-m-d',
            'keterangan'          => 'nullable|string|max:255',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        // Validasi saldo cukup untuk penarikan
        if ($validated['tipe'] === 'penarikan') {
            $account = TabunganAccount::find($validated['tabungan_account_id']);
            if ($nominal > $account->saldo) {
                return response()->json([
                    'message' => 'Saldo tidak cukup. Saldo saat ini: Rp ' . number_format($account->saldo, 0, ',', '.'),
                ], 422);
            }
        }

        $validated['nominal'] = $nominal;

        DB::beginTransaction();
        try {
            TabunganTransaction::create($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    public function updateTransactionAjax(Request $request, TabunganTransaction $transaction)
    {
        $validated = $request->validate([
            'nominal'    => 'required',
            'tanggal'    => 'required|date_format:Y-m-d',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        // Validasi saldo jika penarikan
        if ($transaction->tipe === 'penarikan') {
            $account = $transaction->account;
            // Saldo selain transaksi ini
            $setoran   = $account->transactions()->where('tipe', 'setoran')->where('id', '!=', $transaction->id)->sum('nominal');
            $penarikan = $account->transactions()->where('tipe', 'penarikan')->where('id', '!=', $transaction->id)->sum('nominal');
            $saldoTanpaIni = $setoran - $penarikan;

            if ($nominal > $saldoTanpaIni) {
                return response()->json([
                    'message' => 'Saldo tidak cukup. Saldo tersedia (tanpa transaksi ini): Rp ' . number_format($saldoTanpaIni, 0, ',', '.'),
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $transaction->update([
                'nominal'    => $nominal,
                'tanggal'    => $validated['tanggal'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    public function destroyTransactionAjax(TabunganTransaction $transaction)
    {
        DB::beginTransaction();
        try {
            // Validasi: jika hapus setoran, saldo harus tetap >= 0
            if ($transaction->tipe === 'setoran') {
                $account = $transaction->account;
                $saldoSetelah = $account->saldo - $transaction->nominal;
                if ($saldoSetelah < 0) {
                    return response()->json([
                        'message' => 'Tidak bisa hapus setoran ini — saldo akan menjadi negatif. Hapus penarikan terkait dulu.',
                    ], 422);
                }
            }

            $transaction->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // PRINT PDF
    // ============================================================
    public function printInvoice(TabunganTransaction $transaction)
    {
        $transaction->load('account');

        $pdf = Pdf::loadView('tabungan.invoice-pdf', [
            'trx'      => $transaction,
            'account'  => $transaction->account,
            'noBukti'  => $this->generateNoBukti($transaction),
            'saldoSetelah' => $transaction->account->saldo,
        ])->setPaper('a5', 'portrait');

        $fileName = 'Bukti-' . strtoupper($transaction->tipe) . '-' . $transaction->id . '.pdf';

        return $pdf->stream($fileName);
    }

    private function generateNoBukti(TabunganTransaction $trx): string
    {
        $prefix = $trx->tipe === 'setoran' ? 'SET' : 'TAR';
        return $prefix . '-TBG-' . $trx->tanggal->format('Ymd') . '-' . str_pad((string) $trx->id, 4, '0', STR_PAD_LEFT);
    }
}