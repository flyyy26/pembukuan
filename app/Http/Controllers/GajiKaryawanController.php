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

        // ✅ Hanya toko yang punya karyawan aktif
        $storeIdsWithKaryawan = Karyawan::where('is_active', true)
            ->whereNotNull('store_id')
            ->distinct()
            ->pluck('store_id')
            ->toArray();

        $stores = Store::whereIn('id', $storeIdsWithKaryawan)
            ->orderBy('marketplace')
            ->orderBy('nama_toko')
            ->get();

        $komisiSettings = StoreKomisiSetting::all()->keyBy('store_id');
        $komisiHostLiveGlobal = StoreKomisiSetting::getKomisiHostLiveGlobal();

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

        // Volume BRILink
        $volumeQuery = \App\Models\BrilinkReport::whereYear('periode_start', substr($periode, 0, 4))
            ->whereMonth('periode_start', substr($periode, 5, 2));
        if ($storeId !== 'all') $volumeQuery->where('store_id', $storeId);

        $volumeTransaksiPerToko = $volumeQuery->get()
            ->groupBy('store_id')
            ->map(fn($reports) => (int) $reports->sum('volume_transaksi_agen'));

        $totalVolumeTransaksi = (int) $volumeTransaksiPerToko->sum();

        // Transaksi Live
        $totalTransaksiLive = 0;
        $targetStores = $stores->filter(function($s) use ($storeId) {
            if ($storeId !== 'all' && $s->id != $storeId) return false;
            return $s->marketplace !== 'brilink';
        });

        if ($targetStores->isNotEmpty()) {
            $liveQuery = \App\Models\TiktokLiveSession::whereYear('tanggal', substr($periode, 0, 4))
                ->whereMonth('tanggal', substr($periode, 5, 2))
                ->whereIn('store_id', $targetStores->pluck('id'));
            $totalTransaksiLive = (int) $liveQuery->sum('total_transaksi');
        }

        $totalKomisiLive = $totalTransaksiLive * $komisiHostLiveGlobal;

        $karyawanAktif = Karyawan::where('is_active', true)
            ->when($storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
            ->orderBy('nama')->get();

        $totalGmvWeb = 0;
        $webSales = \App\Models\WebOrderSnapshot::whereYear('web_created_at', substr($periode, 0, 4))
            ->whereMonth('web_created_at', substr($periode, 5, 2))
            ->where('status', 'delivered')
            ->sum('total_amount');
        
        // Jika filter toko bukan 'all', kita asumsikan GMV ini milik toko tersebut
        if ($storeId !== 'all') {
            $totalGmvWeb = (int) $webSales;
        } else {
            // Jika 'all', tampilkan total GMV dari semua web sales di bulan itu
            $totalGmvWeb = (int) $webSales; 
        }

        return view('gaji.index', compact(
            'gajiList', 'stores', 'karyawanAktif',
            'periode', 'storeId', 'status', 'summary',
            'volumeTransaksiPerToko', 'totalVolumeTransaksi',
            'komisiSettings',
            'totalTransaksiLive',
            'totalKomisiLive',
            'komisiHostLiveGlobal',
            'totalGmvWeb'
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

        $komisiSettings = StoreKomisiSetting::all()->keyBy('store_id');
        $komisiHostLiveRate = StoreKomisiSetting::getKomisiHostLiveGlobal();

        // Ambil total GMV Web untuk bulan ini (untuk toko non-brilink)
        $totalGmvWeb = (int) \App\Models\WebOrderSnapshot::whereYear('web_created_at', substr($periode, 0, 4))
            ->whereMonth('web_created_at', substr($periode, 5, 2))
            ->where('status', 'delivered')
            ->sum('total_amount');

        DB::beginTransaction();
        try {
            $karyawanList = Karyawan::where('is_active', true)
                ->when($storeId && $storeId !== 'all', fn($q) => $q->where('store_id', $storeId))
                ->get()
                ->groupBy('store_id');

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($karyawanList as $sId => $karyawans) {
                $jumlahKaryawan = $karyawans->count();
                $store = Store::find($sId);
                $isBrilink = $store && $store->marketplace === 'brilink';
                $setting = $komisiSettings[$sId] ?? null;

                // ==========================================
                // 1. HITUNG KOMISI BRILINK (JIKA TOKO BRILINK)
                // ==========================================
                $komisiBrilinkPerKaryawan = 0;
                if ($isBrilink) {
                    $tipeKomisi = $setting->tipe_komisi ?? 'rp';
                    $komisiRate = $setting ? (int) $setting->komisi_per_transaksi : 0;
                    
                    // ✅ Perbaikan: Memastikan format Y-m terbaca dengan benar
                    $tahun = substr($periode, 0, 4);
                    $bulan = substr($periode, 5, 2);

                    $reports = \App\Models\BrilinkReport::where('store_id', $sId)
                        ->whereYear('periode_start', $tahun)
                        ->whereMonth('periode_start', $bulan)
                        ->get();
                        
                    $volumeTransaksi = (int) $reports->sum('volume_transaksi_agen');
                    $profitKotor     = (int) $reports->sum('total_pendapatan_admin');

                    if ($tipeKomisi === 'persen') {
                        $totalKomisiToko = (int) round($profitKotor * $komisiRate / 100);
                    } else {
                        $totalKomisiToko = $volumeTransaksi * $komisiRate;
                    }
                    
                    $komisiBrilinkPerKaryawan = $jumlahKaryawan > 0 ? (int) floor($totalKomisiToko / $jumlahKaryawan) : 0;
                }

                // ==========================================
                // 2. HITUNG KOMISI WEB (JIKA TOKO NON-BRILINK)
                // ==========================================
                $komisiWebPerKaryawan = 0;
                if (!$isBrilink) {
                    $tipeWeb = $setting->tipe_komisi_web ?? 'rp';
                    $rateWeb = $setting ? (int) $setting->komisi_web_per_trx : 0;

                    if ($rateWeb > 0 && $totalGmvWeb > 0) {
                        // ✅ LANGKAH 1: Hitung Total Komisi untuk SATU TOKO
                        if ($tipeWeb === 'persen') {
                            $totalKomisiWebToko = (int) round($totalGmvWeb * $rateWeb / 100);
                        } else {
                            $totalKomisiWebToko = $totalGmvWeb * $rateWeb;
                        }

                        // ✅ LANGKAH 2: Bagi rata total komisi tersebut ke jumlah karyawan
                        $komisiWebPerKaryawan = $jumlahKaryawan > 0 ? (int) floor($totalKomisiWebToko / $jumlahKaryawan) : 0;
                    }
                }

                // ==========================================
                // 3. LOOPING MASING-MASING KARYAWAN
                // ==========================================
                foreach ($karyawans as $k) {
                    $existing = GajiKaryawan::where('karyawan_id', $k->id)
                        ->where('periode', $periode)
                        ->first();

                    if ($existing && $existing->status === 'dibayar') {
                        $skippedCount++;
                        continue;
                    }

                    // --- HITUNG KOMISI HOST LIVE ---
                    $komisiHostLive = 0;
                    $totalTransaksiLive = \App\Models\TiktokLiveSession::where('karyawan_id', $k->id)
                        ->whereYear('tanggal', substr($periode, 0, 4))
                        ->whereMonth('tanggal', substr($periode, 5, 2))
                        ->sum('total_transaksi');
                    
                    $isHostLive = $totalTransaksiLive > 0;
                    
                    if (!$isBrilink) {
                        $komisiHostLive = $totalTransaksiLive * $komisiHostLiveRate;
                    }

                    // ✅ ATURAN: Jika punya data Live, komisi Web-nya 0
                    $komisiWebKaryawanIni = $isHostLive ? 0 : $komisiWebPerKaryawan;

                    // TOTAL KOMPONEN KOMISI
                    $totalKomisiBaru = $komisiBrilinkPerKaryawan + $komisiWebKaryawanIni + $komisiHostLive;

                    // --- HITUNG POTONGAN MINUS ---
                    $minusQuery = LaporanMinusKaryawan::where('karyawan_id', $k->id);
                    if ($existing) {
                        $minusQuery->where(function ($q) use ($existing) {
                            $q->where('sudah_dipotong', false)->orWhere('gaji_karyawan_id', $existing->id);
                        });
                    } else {
                        $minusQuery->where('sudah_dipotong', false);
                    }
                    $minusItems = $minusQuery->get();
                    $totalMinus = (int) $minusItems->sum('nominal');

                    // --- SIMPAN KE DATABASE ---
                    if ($existing) {
                        $existing->update([
                            'gaji_pokok' => $k->gaji_pokok,
                            'komisi'     => $totalKomisiBaru,
                            'potongan'   => $totalMinus,
                            'total_gaji' => ($k->gaji_pokok + $existing->bonus + $totalKomisiBaru) - $totalMinus,
                        ]);

                        if ($minusItems->count() > 0) {
                            LaporanMinusKaryawan::whereIn('id', $minusItems->pluck('id'))
                                ->update(['gaji_karyawan_id' => $existing->id]);
                        }
                        LaporanMinusKaryawan::where('gaji_karyawan_id', $existing->id)
                            ->whereNotIn('id', $minusItems->pluck('id'))
                            ->update(['gaji_karyawan_id' => null, 'sudah_dipotong' => false]);

                        $updatedCount++;
                    } else {
                        $gaji = GajiKaryawan::create([
                            'karyawan_id' => $k->id,
                            'periode'     => $periode,
                            'gaji_pokok'  => $k->gaji_pokok,
                            'bonus'       => 0,
                            'komisi'      => $totalKomisiBaru,
                            'potongan'    => $totalMinus,
                            'total_gaji'  => ($k->gaji_pokok + $totalKomisiBaru) - $totalMinus,
                            'status'      => 'draft',
                        ]);

                        if ($minusItems->count() > 0) {
                            LaporanMinusKaryawan::whereIn('id', $minusItems->pluck('id'))
                                ->update(['gaji_karyawan_id' => $gaji->id]);
                        }
                        $createdCount++;
                    }
                }
            }

            DB::commit();

            $msgParts = [];
            if ($createdCount > 0) $msgParts[] = "{$createdCount} draft baru dibuat";
            if ($updatedCount > 0) $msgParts[] = "{$updatedCount} draft diperbarui";
            if ($skippedCount > 0) $msgParts[] = "{$skippedCount} dilewati (sudah dibayar)";

            $message = "Generate gaji periode {$periode} selesai. " . implode(', ', $msgParts) . '.';

            return response()->json(['success' => true, 'message' => $message]);
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
            'komisi'                  => 'nullable|array',
            'komisi.*'                => 'nullable|integer|min:0',
            'tipe_komisi'             => 'nullable|array',
            'tipe_komisi.*'           => 'nullable|in:rp,persen',
            
            // ✅ Pastikan validasi untuk komisi web ada
            'komisi_web'              => 'nullable|array', 
            'komisi_web.*'            => 'nullable|integer|min:0',
            'tipe_komisi_web'         => 'nullable|array', 
            'tipe_komisi_web.*'       => 'nullable|in:rp,persen',
            
            'komisi_host_live_global' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Ambil semua ID toko dari request komisi (untuk handle toko brilink maupun non-brilink)
            $storeIds = array_unique(array_merge(
                array_keys($request->komisi ?? []),
                array_keys($request->komisi_web ?? [])
            ));

            foreach ($storeIds as $storeId) {
                $tipe = $request->tipe_komisi[$storeId] ?? 'rp';
                if (!in_array($tipe, ['rp', 'persen'], true)) $tipe = 'rp';
                $nominal = isset($request->komisi[$storeId]) ? (int) $request->komisi[$storeId] : 0;

                $komisiWeb = isset($request->komisi_web[$storeId]) ? (int) $request->komisi_web[$storeId] : 0;
                $tipeWeb = $request->tipe_komisi_web[$storeId] ?? 'rp';
                if (!in_array($tipeWeb, ['rp', 'persen'], true)) $tipeWeb = 'rp';

                StoreKomisiSetting::updateOrCreate(
                    ['store_id' => $storeId],
                    [
                        'komisi_per_transaksi'    => $nominal,
                        'tipe_komisi'             => $tipe,
                        'komisi_web_per_trx'      => $komisiWeb, // ✅ Simpan GMV
                        'tipe_komisi_web'         => $tipeWeb,   // ✅ Simpan tipe GMV
                        'komisi_host_live_global' => (int) $request->komisi_host_live_global,
                    ]
                );
            }

            StoreKomisiSetting::setKomisiHostLiveGlobal((int) $request->komisi_host_live_global);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pengaturan komisi berhasil disimpan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }
}