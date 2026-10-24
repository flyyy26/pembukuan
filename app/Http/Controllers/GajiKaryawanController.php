<?php

namespace App\Http\Controllers;

use App\Models\GajiKaryawan;
use App\Models\Karyawan;
use App\Models\LaporanMinusKaryawan;
use App\Models\Store;
use App\Models\StoreKomisiSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajiKaryawanController extends Controller
{
    // ============================================================
    // 1. HALAMAN KARYAWAN
    // ============================================================
    public function indexKaryawan(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $search  = trim($request->input('search', ''));
        $status  = $request->input('status', 'all'); // all|active|inactive

        $stores = Store::orderBy('nama_toko')->get();

        $query = Karyawan::with('store')
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->when($status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false));

        if ($search !== '') {
            $query->where('nama', 'LIKE', "%{$search}%");
        }

        $karyawan = $query->orderBy('nama')->get();

        return view('gaji.karyawan', compact('karyawan', 'stores', 'storeId', 'search', 'status'));
    }

    public function storeKaryawanAjax(Request $request)
    {
        $validated = $request->validate([
            'store_id'      => 'nullable|exists:stores,id',
            'nama'          => 'required|string|max:100',
            'gaji_pokok'    => 'required',
            'tanggal_masuk' => 'nullable|date',
            'no_hp'         => 'nullable|string|max:30',
        ]);

        $validated['gaji_pokok'] = (int) preg_replace('/[^0-9]/', '', $validated['gaji_pokok']);
        $validated['is_active']  = true;

        $karyawan = Karyawan::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Karyawan '{$karyawan->nama}' berhasil ditambahkan.",
        ]);
    }

    public function updateKaryawanAjax(Request $request, Karyawan $karyawan)
    {
        $validated = $request->validate([
            'store_id'      => 'nullable|exists:stores,id',
            'nama'          => 'required|string|max:100',
            'gaji_pokok'    => 'required',
            'tanggal_masuk' => 'nullable|date',
            'no_hp'         => 'nullable|string|max:30',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['gaji_pokok'] = (int) preg_replace('/[^0-9]/', '', $validated['gaji_pokok']);

        $karyawan->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Karyawan '{$karyawan->nama}' berhasil diperbarui.",
        ]);
    }

    public function destroyKaryawanAjax(Karyawan $karyawan)
    {
        $nama = $karyawan->nama;
        $karyawan->delete();

        return response()->json([
            'success' => true,
            'message' => "Karyawan '{$nama}' berhasil dihapus.",
        ]);
    }

    // ============================================================
    // 2. HALAMAN TRANSAKSI GAJI
    // ============================================================
    public function indexGaji(Request $request)
    {
        $periode = $request->input('periode', now()->format('Y-m'));
        $storeId = $request->input('store', 'all');
        $status  = $request->input('status', 'all');

        $stores = Store::orderBy('nama_toko')->get();
        
        // ✅ Ambil setting komisi per toko
        $komisiSettings = StoreKomisiSetting::pluck('komisi_per_transaksi', 'store_id');

        $query = GajiKaryawan::with(['karyawan.store', 'minusItems'])
            ->where('periode', $periode)
            ->when($storeId !== 'all', fn($q) => $q->whereHas('karyawan', fn($sq) => $sq->where('store_id', $storeId)))
            ->when($status !== 'all', fn($q) => $q->where('status', $status));

        $gajiList = $query->get()->sortBy(fn($g) => $g->karyawan->nama ?? '');

        $summary = [
            'total_gaji'      => (int) $gajiList->sum('total_gaji'),
            'total_dibayar'   => (int) $gajiList->where('status', 'dibayar')->sum('total_gaji'),
            'total_draft'     => (int) $gajiList->where('status', 'draft')->sum('total_gaji'),
            'jumlah_karyawan' => $gajiList->count(),
        ];

        // ✅ AMBIL DATA VOLUME TRANSAKSI DARI BRILINK
        $volumeQuery = \App\Models\BrilinkReport::whereYear('periode_start', substr($periode, 0, 4))
            ->whereMonth('periode_start', substr($periode, 5, 2));

        if ($storeId !== 'all') {
            $volumeQuery->where('store_id', $storeId);
        }

        $volumeTransaksiPerToko = $volumeQuery->get()
            ->groupBy('store_id')
            ->map(function ($reports) {
                return (int) $reports->sum('volume_transaksi_agen');
            });

        $totalVolumeTransaksi = (int) $volumeTransaksiPerToko->sum();

        $karyawanAktif = Karyawan::where('is_active', true)
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->orderBy('nama')->get();

        return view('gaji.index', compact(
            'gajiList', 'stores', 'karyawanAktif',
            'periode', 'storeId', 'status', 'summary',
            'volumeTransaksiPerToko', 'totalVolumeTransaksi',
            'komisiSettings' // ✅ Kirim setting ke view
        ));
    }


    /** Generate draft gaji untuk semua karyawan aktif di periode tsb */
    public function generateGaji(Request $request)
    {
        $request->validate([
            'periode'  => 'required|date_format:Y-m',
            'store_id' => 'nullable',
        ]);

        $periode = $request->periode;
        $storeId = $request->store_id;

        // ✅ Ambil setting komisi per toko
        $komisiSettings = StoreKomisiSetting::pluck('komisi_per_transaksi', 'store_id');

        DB::beginTransaction();
        try {
            $karyawanList = Karyawan::where('is_active', true)
                ->when($storeId && $storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
                ->get()
                ->groupBy('store_id');

            $count = 0;
            foreach ($karyawanList as $sId => $karyawans) {
                $jumlahKaryawan = $karyawans->count();
                
                // ✅ Gunakan setting komisi dari DB. Jika belum ada, default 0.
                $komisiRate = $komisiSettings[$sId] ?? 0;

                $volumeTransaksi = \App\Models\BrilinkReport::where('store_id', $sId)
                    ->whereYear('periode_start', substr($periode, 0, 4))
                    ->whereMonth('periode_start', substr($periode, 5, 2))
                    ->sum('volume_transaksi_agen');
                    
                $totalKomisiToko = $volumeTransaksi * $komisiRate;
                $komisiPerKaryawan = $jumlahKaryawan > 0 ? (int) floor($totalKomisiToko / $jumlahKaryawan) : 0;

                foreach ($karyawans as $k) {
                    if (GajiKaryawan::where('karyawan_id', $k->id)->where('periode', $periode)->exists()) {
                        continue;
                    }

                    $minusItems = LaporanMinusKaryawan::where('karyawan_id', $k->id)
                        ->where('sudah_dipotong', false)
                        ->get();

                    $totalMinus = $minusItems->sum('nominal');

                    $gaji = GajiKaryawan::create([
                        'karyawan_id' => $k->id,
                        'periode'     => $periode,
                        'gaji_pokok'  => $k->gaji_pokok,
                        'bonus'       => 0,
                        'komisi'      => $komisiPerKaryawan,
                        'potongan'    => $totalMinus,
                        'total_gaji'  => ($k->gaji_pokok + $komisiPerKaryawan) - $totalMinus,
                        'status'      => 'draft',
                    ]);

                    if ($minusItems->count() > 0) {
                        LaporanMinusKaryawan::whereIn('id', $minusItems->pluck('id'))
                            ->update(['gaji_karyawan_id' => $gaji->id]);
                    }

                    $count++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil generate {$count} draft gaji untuk periode {$periode}.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function updateGajiAjax(Request $request, GajiKaryawan $gaji)
    {
        $validated = $request->validate([
            'bonus'        => 'nullable',
            'potongan'     => 'nullable',
            'komisi'       => 'nullable', // ✅ Tambah validasi komisi
            'metode_bayar' => 'nullable|in:tunai,transfer',
        ]);

        $bonus    = (int) preg_replace('/[^0-9]/', '', $validated['bonus'] ?? 0);
        $potongan = (int) preg_replace('/[^0-9]/', '', $validated['potongan'] ?? 0);
        $komisi   = (int) preg_replace('/[^0-9]/', '', $validated['komisi'] ?? $gaji->komisi); // ✅ Ambil komisi

        DB::beginTransaction();
        try {
            if ($potongan == 0) {
                LaporanMinusKaryawan::where('gaji_karyawan_id', $gaji->id)
                    ->update([
                        'sudah_dipotong'   => false,
                        'gaji_karyawan_id' => null,
                    ]);
            }

            $gaji->update([
                'bonus'        => $bonus,
                'potongan'     => $potongan,
                'komisi'       => $komisi, // ✅ Update komisi
                'metode_bayar' => $validated['metode_bayar'] ?? $gaji->metode_bayar,
                // ✅ Total = Pokok + Bonus + Komisi - Potongan
                'total_gaji'   => ($gaji->gaji_pokok + $bonus + $komisi) - $potongan,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function bayarGajiAjax(GajiKaryawan $gaji)
    {
        if ($gaji->status === 'dibayar') {
            return response()->json(['message' => 'Gaji ini sudah dibayar.'], 422);
        }

        DB::beginTransaction();
        try {
            $gaji->update([
                'status'        => 'dibayar',
                'tanggal_bayar' => now()->format('Y-m-d'),
            ]);

            // 🔥 HANYA POTONG MINUS YANG MEMILIKI ID GAJI INI
            LaporanMinusKaryawan::where('gaji_karyawan_id', $gaji->id)
                ->update([
                    'sudah_dipotong' => true,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil ditandai lunas.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function destroyGajiAjax(GajiKaryawan $gaji)
    {
        if ($gaji->status === 'dibayar') {
            return response()->json(['message' => 'Gaji yang sudah dibayar tidak bisa dihapus.'], 422);
        }

        $gaji->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gaji draft berhasil dihapus.',
        ]);
    }

    // ============================================================
    // 3. LAPORAN MINUS KARYAWAN
    // ============================================================
    public function indexMinus(Request $request)
    {
        $storeId = $request->input('store', 'all');
        $search  = trim($request->input('search', ''));
        $status  = $request->input('status', 'all'); // all|belum|sudah

        $stores = Store::orderBy('nama_toko')->get();

        $query = LaporanMinusKaryawan::with(['karyawan.store', 'gaji'])
            ->when($storeId !== 'all', fn($q) => $q->whereHas('karyawan', fn($sq) => $sq->where('store_id', $storeId)))
            ->when($status === 'belum', fn($q) => $q->where('sudah_dipotong', false))
            ->when($status === 'sudah', fn($q) => $q->where('sudah_dipotong', true));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                  ->orWhereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $minusList = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $karyawanAktif = Karyawan::where('is_active', true)->orderBy('nama')->get();

        $summary = [
            'total_belum' => (int) $minusList->where('sudah_dipotong', false)->sum('nominal'),
            'total_sudah' => (int) $minusList->where('sudah_dipotong', true)->sum('nominal'),
        ];

        return view('gaji.minus', compact(
            'minusList', 'stores', 'karyawanAktif',
            'storeId', 'search', 'status', 'summary'
        ));
    }

    public function storeMinusAjax(Request $request)
    {
        $validated = $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal'     => 'required|date',
            'nominal'     => 'required',
            'keterangan'  => 'required|string|max:500',
        ]);

        $validated['nominal'] = (int) preg_replace('/[^0-9]/', '', $validated['nominal']);
        $validated['sudah_dipotong'] = false;

        LaporanMinusKaryawan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan minus berhasil ditambahkan.',
        ]);
    }

    public function updateMinusAjax(Request $request, LaporanMinusKaryawan $minus)
    {
        if ($minus->sudah_dipotong) {
            return response()->json(['message' => 'Tidak bisa edit, sudah dipotong dari gaji.'], 422);
        }

        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'nominal'    => 'required',
            'keterangan' => 'required|string|max:500',
        ]);

        $validated['nominal'] = (int) preg_replace('/[^0-9]/', '', $validated['nominal']);

        $minus->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan minus berhasil diperbarui.',
        ]);
    }

    public function destroyMinusAjax(LaporanMinusKaryawan $minus)
    {
        if ($minus->sudah_dipotong) {
            return response()->json(['message' => 'Tidak bisa hapus, sudah dipotong dari gaji.'], 422);
        }

        $minus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan minus berhasil dihapus.',
        ]);
    }

    public function updateKomisiSettings(Request $request)
    {
        $request->validate([
            'komisi' => 'required|array',
            'komisi.*' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->komisi as $storeId => $nominal) {
                StoreKomisiSetting::updateOrCreate(
                    ['store_id' => $storeId],
                    ['komisi_per_transaksi' => (int) $nominal]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pengaturan komisi berhasil disimpan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }
}