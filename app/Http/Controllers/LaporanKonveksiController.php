<?php

namespace App\Http\Controllers;

use App\Models\Hanca;
use App\Models\HancaPembayaran;
use App\Models\KonveksiKaryawan;
use App\Models\KonveksiKategori;
use App\Models\KonveksiKategoriProduk;
use App\Models\KonveksiPengeluaran;
use App\Models\KonveksiPengeluaranUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\HancaDetail; 
use App\Models\KonveksiKasbon; 
use App\Models\KonveksiPembayaran;

class LaporanKonveksiController extends Controller
{
    // ============================================
    // HALAMAN UTAMA
    // ============================================
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $kategoriFilter = $request->input('kategori', 'all');
        $kategoriProdukFilter = $request->input('kategori_produk', 'all');
        $bulanFilter = $request->input('bulan', 'all');

        $query = Hanca::with([
            'karyawan', 
            'details.kategoriProduk',
            'pembayarans'
        ])
        // ✅ TAMBAHKAN FILTER INI: Hanya tampilkan hanca yang memiliki detail belum lunas
        ->whereHas('details', function($q) {
            $q->where('sisa_hanca', '>', 0);
        })
        ->when($karyawanId !== 'all', fn($q) => $q->where('konveksi_karyawan_id', $karyawanId))
        ->when($kategoriFilter !== 'all', fn($q) => $q->where('kategori', $kategoriFilter))
        ->when($kategoriProdukFilter !== 'all', function($q) use ($kategoriProdukFilter) {
            $q->whereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', $kategoriProdukFilter));
        })
        ->when($bulanFilter !== 'all', function($q) use ($bulanFilter) {
            $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
              ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
        });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kategori', 'LIKE', "%{$search}%")
                ->orWhereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"))
                ->orWhereHas('details', fn($sq) => $sq->where('nama_produk', 'LIKE', "%{$search}%"))
                ->orWhereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $hancaList = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $summary = [
            'total_hanca'      => (int) $hancaList->sum('total'),
            'total_dibayar'    => (int) $hancaList->sum(fn($h) => $h->total_dibayar),
            'total_sisa'       => (int) $hancaList->sum(fn($h) => $h->sisa),
            'jumlah_data'      => $hancaList->count(),
        ];

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();
        $kategoriList = KonveksiKategori::orderBy('nama')->pluck('nama');
        $kategoriProdukList = KonveksiKategoriProduk::orderBy('nama')->pluck('nama');
        $kategoriIdsByName = KonveksiKategori::pluck('id', 'nama');
        $kategoriProdukIdsByName = KonveksiKategoriProduk::pluck('id', 'nama');

        $bulanList = Hanca::selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function($item) {
                return [
                    'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->translatedFormat('F Y')
                ];
            });

        $hancaBelumLunas = $hancaList->filter(function($h) {
            return $h->details->contains(function($detail) use ($h) {
                $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                return ($detail->jumlah_hanca - $dibayarQty) > 0;
            });
        })->values();

        return view('konveksi.index', compact(
            'hancaList', 'summary', 'karyawanList', 'search', 'karyawanId',
            'kategoriList', 'kategoriProdukList', 'kategoriIdsByName', 'kategoriProdukIdsByName', 'hancaBelumLunas',
            'kategoriFilter', 'kategoriProdukFilter', 'bulanFilter', 'bulanList'
        ));
    }

    // ============================================
    // CRUD KARYAWAN KONVEKSI
    // ============================================
    public function storeKaryawanAjax(Request $request)
    {
        $validated = $request->validate([
            'nama'  => 'required|string|max:100',
            'no_hp' => 'nullable|string|max:30',
        ]);

        $karyawan = KonveksiKaryawan::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Karyawan '{$karyawan->nama}' berhasil ditambahkan.",
            'karyawan' => $karyawan,
        ]);
    }

    public function destroyKaryawanAjax(KonveksiKaryawan $karyawan)
    {
        $nama = $karyawan->nama;
        $karyawan->delete();

        return response()->json([
            'success' => true,
            'message' => "Karyawan '{$nama}' berhasil dihapus."
        ]);
    }

    // ============================================
    // CRUD HANCA
    // ============================================
    public function storeHancaAjax(Request $request)
    {
        $validated = $request->validate([
            'konveksi_karyawan_id' => 'required|exists:konveksi_karyawans,id',
            'kategori'             => 'required|string|max:100',
            'tanggal'              => 'required|date',
            'products'             => 'required|json',
        ]);

        $products = json_decode($validated['products'], true);
        if (!is_array($products) || count($products) === 0) {
            return response()->json(['message' => 'Minimal 1 produk harus diisi.'], 422);
        }

        // ✅ Validasi: jika kategori Jahit dan ada sumber_potong_id, pastikan belum dipakai
        $sumberIds = collect($products)->pluck('sumber_potong_id')->filter()->unique()->toArray();
        if (!empty($sumberIds)) {
            $used = HancaDetail::whereIn('sumber_potong_id', $sumberIds)
                ->whereNotNull('sumber_potong_id')
                ->pluck('sumber_potong_id')
                ->toArray();
            if (!empty($used)) {
                return response()->json(['message' => 'Beberapa produk dari Potong Kain sudah diambil sebelumnya.'], 422);
            }
        }

        KonveksiKategori::firstOrCreate(['nama' => trim($validated['kategori'])]);

        DB::beginTransaction();
        try {
            $hanca = Hanca::create([
                'konveksi_karyawan_id' => $validated['konveksi_karyawan_id'],
                'kategori'             => $validated['kategori'],
                'tanggal'              => $validated['tanggal'],
            ]);

            foreach ($products as $prod) {
                $kategoriProdukId = null;
                if (!empty($prod['kategori_produk']) && $prod['kategori_produk'] !== '__new__') {
                    $kp = KonveksiKategoriProduk::firstOrCreate(['nama' => trim($prod['kategori_produk'])]);
                    $kategoriProdukId = $kp->id;
                }

                $harga = (int) preg_replace('/[^0-9]/', '', $prod['harga']);
                $total = $harga * $prod['jumlah_hanca'];

                $hanca->details()->create([
                    'sumber_potong_id'   => $prod['sumber_potong_id'] ?? null,
                    'kategori_produk_id' => $kategoriProdukId,
                    'nama_produk'        => $prod['nama_produk'],
                    'warna'              => $prod['warna'] ?? null,
                    'ukuran'             => $prod['ukuran'] ?? null,
                    'jumlah_hanca'       => $prod['jumlah_hanca'],
                    'sisa_hanca'         => $prod['jumlah_hanca'],
                    'harga'              => $harga,
                    'total'              => $total,
                    'sisa_nominal'       => $total,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data hanca berhasil ditambahkan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function updateHancaAjax(Request $request, Hanca $hanca)
    {
        $validated = $request->validate([
            'konveksi_karyawan_id' => 'required|exists:konveksi_karyawans,id',
            'kategori'             => 'required|string|max:100',
            'tanggal'              => 'required|date',
            'products'             => 'required|json',
        ]);

        $products = json_decode($validated['products'], true);
        if (!is_array($products) || count($products) === 0) {
            return response()->json(['message' => 'Minimal 1 produk harus diisi.'], 422);
        }

        KonveksiKategori::firstOrCreate(['nama' => trim($validated['kategori'])]);

        DB::beginTransaction();
        try {
            $hanca->update([
                'konveksi_karyawan_id' => $validated['konveksi_karyawan_id'],
                'kategori'             => $validated['kategori'],
                'tanggal'              => $validated['tanggal'],
            ]);

            $existingIds = [];

            foreach ($products as $prod) {
                $kategoriProdukId = null;
                if (!empty($prod['kategori_produk']) && $prod['kategori_produk'] !== '__new__') {
                    $kp = KonveksiKategoriProduk::firstOrCreate(['nama' => trim($prod['kategori_produk'])]);
                    $kategoriProdukId = $kp->id;
                }

                $harga = (int) preg_replace('/[^0-9]/', '', $prod['harga']);
                $total = $harga * $prod['jumlah_hanca'];

                if (!empty($prod['id'])) {
                    $detail = HancaDetail::find($prod['id']);
                    $detail->update([
                        'sumber_potong_id'   => $prod['sumber_potong_id'] ?? $detail->sumber_potong_id,
                        'kategori_produk_id' => $kategoriProdukId,
                        'nama_produk'        => $prod['nama_produk'],
                        'warna'              => $prod['warna'] ?? null,
                        'ukuran'             => $prod['ukuran'] ?? null,
                        'jumlah_hanca'       => $prod['jumlah_hanca'],
                        'harga'              => $harga,
                        'total'              => $total,
                    ]);
                    $existingIds[] = $detail->id;
                } else {
                    $new = $hanca->details()->create([
                        'sumber_potong_id'   => $prod['sumber_potong_id'] ?? null,
                        'kategori_produk_id' => $kategoriProdukId,
                        'nama_produk'        => $prod['nama_produk'],
                        'warna'              => $prod['warna'] ?? null,
                        'ukuran'             => $prod['ukuran'] ?? null,
                        'jumlah_hanca'       => $prod['jumlah_hanca'],
                        'sisa_hanca'         => $prod['jumlah_hanca'],
                        'harga'              => $harga,
                        'total'              => $total,
                        'sisa_nominal'       => $total,
                    ]);
                    $existingIds[] = $new->id;
                }
            }

            $hanca->details()->whereNotIn('id', $existingIds)->delete();
            $hanca->recalculateSisa();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data hanca berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function destroyHancaAjax(Hanca $hanca)
    {
        $hanca->delete();
        return response()->json(['success' => true, 'message' => 'Data hanca berhasil dihapus.']);
    }

    // ============================================
    // CRUD PEMBAYARAN
    // ============================================
    public function storePembayaranAjax(Request $request)
    {
        // ✅ Ubah validasi: nominal_setor jadi nullable
        $validated = $request->validate([
            'qty_setor'       => 'required|array|min:1',
            'qty_setor.*'     => 'integer|min:1',
            'nominal_setor'   => 'nullable|array',
            'nominal_setor.*' => 'integer|min:0',
            'kasbon_ids'      => 'nullable|array',
            'kasbon_ids.*'    => 'exists:konveksi_kasbons,id',
            'tanggal'         => 'required|date',
            'keterangan'      => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $pembayaranIds = [];
            
            // Ambil ID Kasbon pertama
            $kasbonId = !empty($validated['kasbon_ids']) ? $validated['kasbon_ids'][0] : null;

            foreach ($validated['qty_setor'] as $detailId => $qty) {
                if ($qty <= 0) continue;

                $detail = HancaDetail::find($detailId);
                if (!$detail) continue;

                $hanca = $detail->hanca;
                
                // ✅ LOGIKA BARU: 
                // Karena halaman setor hanya mengirim Qty, kita set nominal = 0.
                // Nominal akan dihitung saat diproses di halaman Data Bayaran.
                $nominal = 0;
                
                // (Opsional) Jika ada halaman lain yang mengirim nominal_setor, bisa ditangkap di sini
                if (isset($validated['nominal_setor'][$detailId])) {
                    $nominal = (int) $validated['nominal_setor'][$detailId];
                }

                // Simpan Pembayaran
                $pembayaran = HancaPembayaran::create([
                    'hanca_id'        => $hanca->id,
                    'hanca_detail_id' => $detail->id, 
                    'kasbon_id'       => $kasbonId, 
                    'tipe'            => 'bayaran',
                    'nominal'         => $nominal, // 0 untuk setoran tanpa nominal
                    'kasbon_dipotong' => 0,
                    'qty_setor'       => $qty,
                    'tanggal'         => $validated['tanggal'],
                    'keterangan'      => $validated['keterangan'],
                ]);

                // Update Sisa Qty & Sisa Nominal di Detail
                $detail->sisa_hanca -= $qty;
                // Jika nominal 0, sisa_nominal tidak berkurang (tetap menunggu pembayaran)
                if ($nominal > 0) {
                    $detail->sisa_nominal = max(0, $detail->sisa_nominal - $nominal);
                }
                $detail->save();

                $pembayaranIds[] = $pembayaran->id;
            }

            // Sinkronisasi ulang sisa di level Hanca
            $uniqueHancaIds = HancaPembayaran::whereIn('id', $pembayaranIds)->pluck('hanca_id')->unique();
            foreach ($uniqueHancaIds as $hid) {
                $h = Hanca::find($hid);
                if ($h) $h->recalculateSisa();
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Setoran berhasil disimpan.',
                'pembayaran_ids' => $pembayaranIds,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    public function bayarMassalAjax(Request $request)
    {
        $request->validate([
            'pembayaran_ids'   => 'required|array',
            'pembayaran_ids.*' => 'exists:hanca_pembayarans,id',
            'tanggal_bayar'    => 'required|date',
            'kasbon_ids'       => 'nullable|array',
            'kasbon_ids.*'     => 'exists:konveksi_kasbons,id',
        ]);

        DB::beginTransaction();
        try {
            $pembayaranIds = $request->pembayaran_ids;
            $kasbonIds     = $request->input('kasbon_ids', []);
            $tanggalBayar  = $request->tanggal_bayar;

            // 1. Ambil data pembayaran yang dipilih
            $pembayarans = HancaPembayaran::with('hancaDetail')->whereIn('id', $pembayaranIds)->get();
            
            // 2. Hitung total upah kotor (sebelum dipotong)
            $totalUpahKotor = 0;
            foreach ($pembayarans as $p) {
                $harga = $p->hancaDetail->harga ?? 0;
                $totalUpahKotor += ($p->qty_setor * $harga);
            }

            // 3. Proses Potong Kasbon
            $totalPotongKasbon = 0;
            $sisaUpah = $totalUpahKotor;

            // Ambil ID kasbon pertama (jika ada yang dipilih)
            $kasbonIdUtama = null;
            if (!empty($kasbonIds)) {
                $kasbons = KonveksiKasbon::whereIn('id', $kasbonIds)->orderBy('id', 'asc')->get();
                
                foreach ($kasbons as $kasbon) {
                    if ($sisaUpah <= 0) break;

                    $sisaKasbon = $kasbon->nominal; // Sesuai model Anda
                    if ($sisaKasbon <= 0) continue;

                    $potong = min($sisaUpah, $sisaKasbon);

                    if ($potong > 0) {
                        $sisaKasbonBaru = $sisaKasbon - $potong;
                        
                        $kasbon->update([
                            'nominal' => $sisaKasbonBaru,
                            'status'  => $sisaKasbonBaru <= 0 ? 'lunas' : 'belum_lunas',
                        ]);

                        $sisaUpah -= $potong;
                        $totalPotongKasbon += $potong;
                        
                        if ($kasbonIdUtama === null) {
                            $kasbonIdUtama = $kasbon->id;
                        }
                    }
                }
            }

            // 4. Update HancaPembayaran 
            // ✅ PENTING: Simpan nominal KOTOR (sebelum dipotong) ke kolom 'nominal'
            $isFirst = true;
            foreach ($pembayarans as $p) {
                $harga = $p->hancaDetail->harga ?? 0;
                $nominalKotor = $p->qty_setor * $harga;
                
                $p->update([
                    'nominal'         => $nominalKotor, // Simpan nilai asli
                    'kasbon_id'       => $isFirst ? $kasbonIdUtama : null,
                    'kasbon_dipotong' => $isFirst ? $totalPotongKasbon : 0, // Potongan hanya di baris pertama
                    'tanggal'         => $tanggalBayar,
                ]);
                
                $isFirst = false;
            }

            DB::commit();

            return response()->json([
                'success'             => true,
                'message'             => 'Pembayaran berhasil diproses.',
                'total_upah'          => $totalUpahKotor,
                'total_potong_kasbon' => $totalPotongKasbon,
                'total_diterima'      => $sisaUpah,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printMassalPdf(Request $request)
    {
        $ids = $request->query('ids', []);
        
        if (empty($ids) || !is_array($ids)) {
            abort(400, 'Tidak ada data pembayaran yang dipilih.');
        }

        // Ambil semua pembayaran beserta relasinya
        $pembayarans = HancaPembayaran::with(['hanca.karyawan', 'hancaDetail'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        if ($pembayarans->isEmpty()) {
            abort(404, 'Data pembayaran tidak ditemukan.');
        }

        // ✅ Kelompokkan berdasarkan karyawan_id dan tanggal_bayar
        $grouped = $pembayarans->groupBy(function($item) {
            return $item->hanca->konveksi_karyawan_id . '|' . $item->tanggal->format('Y-m-d');
        });

        // ✅ Hitung total untuk setiap grup
        foreach ($grouped as $key => $items) {
            $totalKasbon = $items->sum('kasbon_dipotong');
            $totalKotor = $items->sum('nominal'); // 'nominal' sekarang berisi nilai kotor
            
            // Simpan hasil hitungan ke dalam koleksi agar bisa dibaca di view
            $items->total_kasbon = $totalKasbon;
            $items->total_kotor = $totalKotor;
            $items->total_bersih = $totalKotor - $totalKasbon;
        }

        $data = [
            'groupedPembayarans'  => $grouped,
            'tanggal_cetak'       => now()->translatedFormat('d F Y H:i'),
        ];

        $pdf = Pdf::loadView('konveksi.invoice-massal-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->stream('Nota-Pembayaran-Massal-' . now()->format('YmdHis') . '.pdf');
    }


    public function destroyPembayaranAjax(HancaPembayaran $pembayaran)
    {
        DB::beginTransaction();
        try {
            $hanca = $pembayaran->hanca;
            $pembayaran->delete();
            if ($hanca) $hanca->recalculateSisa();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    public function potongKainIndex(Request $request)
    {
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $kategoriProdukFilter = $request->input('kategori_produk', 'all');
        $bulanFilter = $request->input('bulan', 'all');

        $query = Hanca::with(['karyawan', 'details.kategoriProduk', 'pembayarans'])
            ->where('kategori', 'Potong Kain')
            ->whereHas('details', function($q) {
                $q->where('sisa_hanca', '>', 0);
            })
            ->when($karyawanId !== 'all', fn($q) => $q->where('konveksi_karyawan_id', $karyawanId))
            ->when($kategoriProdukFilter !== 'all', function($q) use ($kategoriProdukFilter) {
                $q->whereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', $kategoriProdukFilter));
            })
            ->when($bulanFilter !== 'all', function($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"))
                ->orWhereHas('details', fn($sq) => $sq->where('nama_produk', 'LIKE', "%{$search}%"))
                ->orWhereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $hancaList = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $summary = [
            'total_hanca'   => (int) $hancaList->sum('total'),
            'total_dibayar' => (int) $hancaList->sum(fn($h) => $h->total_dibayar),
            'total_sisa'    => (int) $hancaList->sum(fn($h) => $h->sisa),
            'jumlah_data'   => $hancaList->count(),
        ];

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();
        $kategoriProdukList = KonveksiKategoriProduk::orderBy('nama')->pluck('nama');
        $kategoriProdukIdsByName = KonveksiKategoriProduk::pluck('id', 'nama');

        $bulanList = Hanca::where('kategori', 'Potong Kain')
            ->selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(fn($item) => [
                'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->translatedFormat('F Y'),
            ]);

        $hancaBelumLunas = $hancaList->filter(function($h) {
            return $h->details->contains(function($detail) use ($h) {
                $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                return ($detail->jumlah_hanca - $dibayarQty) > 0;
            });
        })->values();

        return view('konveksi.potong-kain', compact(
            'hancaList', 'summary', 'karyawanList', 'search', 'karyawanId',
            'kategoriProdukList', 'kategoriProdukIdsByName',
            'kategoriProdukFilter', 'bulanFilter', 'bulanList',
            'hancaBelumLunas'
        ));
    }

    // ============================================
    // DATA AMBIL HANCA
    // ============================================
    public function ambilHancaIndex(Request $request)
    {
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $kategoriProdukFilter = $request->input('kategori_produk', 'all');
        $bulanFilter = $request->input('bulan', 'all');

        $query = Hanca::with(['karyawan', 'details.kategoriProduk', 'pembayarans'])
            ->where('kategori', '!=', 'Potong Kain')
            ->whereHas('details', function($q) {
                $q->where('sisa_hanca', '>', 0);
            })
            ->when($karyawanId !== 'all', fn($q) => $q->where('konveksi_karyawan_id', $karyawanId))
            ->when($kategoriProdukFilter !== 'all', function($q) use ($kategoriProdukFilter) {
                $q->whereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', $kategoriProdukFilter));
            })
            ->when($bulanFilter !== 'all', function($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"))
                ->orWhereHas('details', fn($sq) => $sq->where('nama_produk', 'LIKE', "%{$search}%"))
                ->orWhereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $hancaList = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $summary = [
            'total_hanca'   => (int) $hancaList->sum('total'),
            'total_dibayar' => (int) $hancaList->sum(fn($h) => $h->total_dibayar),
            'total_sisa'    => (int) $hancaList->sum(fn($h) => $h->sisa),
            'jumlah_data'   => $hancaList->count(),
        ];

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();
        $kategoriProdukList = KonveksiKategoriProduk::orderBy('nama')->pluck('nama');
        $kategoriProdukIdsByName = KonveksiKategoriProduk::pluck('id', 'nama');
        $kategoriList = KonveksiKategori::where('nama', '!=', 'Potong Kain')
            ->orderBy('nama')
            ->pluck('nama')
            ->toArray();
        if (!in_array('Jahit', $kategoriList)) {
            array_unshift($kategoriList, 'Jahit');
        }
        $kategoriIdsByName = KonveksiKategori::pluck('id', 'nama');

        $usedPotongIds = HancaDetail::whereNotNull('sumber_potong_id')->pluck('sumber_potong_id')->toArray();

        // Potong Kain yang punya detail selesai
        $potongKainSelesai = Hanca::with(['karyawan', 'details.kategoriProduk', 'pembayarans'])
            ->where('kategori', 'Potong Kain')
            ->whereHas('details', function($q) use ($usedPotongIds) {
                $q->where('sisa_hanca', '<=', 0)
                ->whereNotIn('id', $usedPotongIds); // ✅ Hanya yang belum diambil
            })
            ->orderByDesc('tanggal')
            ->get();

        $bulanList = Hanca::where('kategori', '!=', 'Potong Kain')
            ->selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(fn($item) => [
                'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->translatedFormat('F Y'),
            ]);

        return view('konveksi.ambil-hanca', compact(
            'hancaList', 'summary', 'karyawanList', 'search', 'karyawanId',
            'kategoriProdukList', 'kategoriProdukIdsByName', 'potongKainSelesai',
            'usedPotongIds', 'kategoriProdukFilter', 'bulanFilter', 'bulanList',
            'kategoriList', 'kategoriIdsByName'
        ));
    }

    // ============================================
    // DATA SETOR HANCA
    // ============================================
    public function setorHancaIndex(Request $request)
    {
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $kategoriFilter = $request->input('kategori', 'all');
        $kategoriProdukFilter = $request->input('kategori_produk', 'all');
        $bulanFilter = $request->input('bulan', 'all');

        $query = Hanca::with(['karyawan', 'details.kategoriProduk', 'pembayarans'])
            ->where('kategori', '!=', 'Potong Kain')   // ✅ EXCLUDE Potong Kain
            ->whereHas('details', fn($q) => $q->where('sisa_hanca', '>', 0))
            ->when($karyawanId !== 'all', fn($q) => $q->where('konveksi_karyawan_id', $karyawanId))
            ->when($kategoriFilter !== 'all', fn($q) => $q->where('kategori', $kategoriFilter))
            ->when($kategoriProdukFilter !== 'all', function($q) use ($kategoriProdukFilter) {
                $q->whereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', $kategoriProdukFilter));
            })
            ->when($bulanFilter !== 'all', function($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kategori', 'LIKE', "%{$search}%")
                ->orWhereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"))
                ->orWhereHas('details', fn($sq) => $sq->where('nama_produk', 'LIKE', "%{$search}%"))
                ->orWhereHas('details.kategoriProduk', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $hancaList = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $summary = [
            'total_hanca'   => (int) $hancaList->sum('total'),
            'total_dibayar' => (int) $hancaList->sum(fn($h) => $h->total_dibayar),
            'total_sisa'    => (int) $hancaList->sum(fn($h) => $h->sisa),
            'jumlah_data'   => $hancaList->count(),
        ];

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();
        $kategoriList = KonveksiKategori::where('nama', '!=', 'Potong Kain')->orderBy('nama')->pluck('nama'); // ✅ exclude di dropdown
        $kategoriProdukList = KonveksiKategoriProduk::orderBy('nama')->pluck('nama');
        $kategoriProdukIdsByName = KonveksiKategoriProduk::pluck('id', 'nama');
        $kategoriIdsByName = KonveksiKategori::pluck('id', 'nama');

        $bulanList = Hanca::where('kategori', '!=', 'Potong Kain')   // ✅ exclude dari list bulan
            ->selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(fn($item) => [
                'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->translatedFormat('F Y'),
            ]);

        $hancaBelumLunas = $hancaList->filter(function($h) {
            return $h->details->contains(function($detail) use ($h) {
                $dibayarQty = $h->pembayarans->where('hanca_detail_id', $detail->id)->sum('qty_setor');
                return ($detail->jumlah_hanca - $dibayarQty) > 0;
            });
        })->values();

        return view('konveksi.setor-hanca', compact(
            'hancaList', 'summary', 'karyawanList', 'search', 'karyawanId',
            'kategoriList', 'kategoriProdukList', 'kategoriIdsByName', 'kategoriProdukIdsByName',
            'hancaBelumLunas', 'kategoriFilter', 'kategoriProdukFilter', 'bulanFilter', 'bulanList'
        ));
    }

    // ============================================
    // AJAX: Detail Potong Kain Selesai
    // ============================================
    public function getPotongKainDetailsAjax(Hanca $hanca)
    {
        $hanca->load(['karyawan', 'details.kategoriProduk', 'pembayarans']);

        if ($hanca->kategori !== 'Potong Kain') {
            return response()->json(['message' => 'Bukan data Potong Kain.'], 422);
        }

        $usedIds = HancaDetail::whereNotNull('sumber_potong_id')->pluck('sumber_potong_id')->toArray();

        $details = $hanca->details
            ->filter(function($d) use ($hanca) {
                $dibayarQty = $hanca->pembayarans->where('hanca_detail_id', $d->id)->sum('qty_setor');
                return ($d->jumlah_hanca - $dibayarQty) <= 0;
            })
            ->filter(fn($d) => !in_array($d->id, $usedIds))
            ->map(fn($d) => [
                'id'                 => $d->id,
                'nama_produk'        => $d->nama_produk,
                'warna'              => $d->warna,
                'ukuran'             => $d->ukuran,
                'jumlah_hanca'       => $d->jumlah_hanca,
                'kategori_produk'    => $d->kategoriProduk->nama ?? '',
                'kategori_produk_id' => $d->kategori_produk_id,
            ])
            ->values();

        return response()->json([
            'hanca_id'  => $hanca->id,
            'karyawan'  => $hanca->karyawan->nama ?? '-',
            'tanggal'   => $hanca->tanggal?->format('d/m/Y'),
            'details'   => $details,
        ]);
    }

    // ============================================
    // PENGELUARAN (Tab DP / Lain-lain)
    // ============================================
    public function storePengeluaranAjax(Request $request)
    {
        $validated = $request->validate([
            'konveksi_karyawan_id' => 'required|exists:konveksi_karyawans,id',
            'kategori'             => 'required|in:dp,lain-lain',
            'nominal'              => 'required',
            'keterangan'           => 'nullable|string|max:255',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', $validated['nominal']);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        KonveksiPengeluaran::create([
            'konveksi_karyawan_id' => $validated['konveksi_karyawan_id'],
            'kategori'             => $validated['kategori'],
            'nominal'              => $nominal,
            'keterangan'           => $validated['keterangan'],
        ]);

        return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil disimpan.']);
    }

    public function pengeluaranIndex(Request $request)
    {
        $search = trim($request->input('search', ''));
        $bulanFilter = $request->input('bulan', now()->format('Y-m')); // ✅ Default bulan ini

        $query = KonveksiPengeluaranUmum::when($bulanFilter !== 'all', function($q) use ($bulanFilter) {
            $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
              ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
        });

        // ✅ Logika Pencarian
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        $pengeluaranList = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        // ✅ Ambil daftar bulan unik untuk dropdown
        $bulanList = KonveksiPengeluaranUmum::selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function($item) {
                return [
                    'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->translatedFormat('F Y')
                ];
            });
        
        // Pastikan bulan yang dipilih ada di list (jika belum ada datanya)
        if ($bulanFilter !== 'all' && !$bulanList->contains('value', $bulanFilter)) {
            $bulanList->prepend([
                'value' => $bulanFilter,
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $bulanFilter)->translatedFormat('F Y'),
            ]);
        }

        return view('konveksi.data-pengeluaran', compact('pengeluaranList', 'bulanFilter', 'bulanList', 'search'));
    }

    public function storePengeluaranUmumAjax(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:100',
            'nominal'    => 'required',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', $validated['nominal']);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        $pengeluaran = KonveksiPengeluaranUmum::create([
            'nama'       => trim($validated['nama']),
            'nominal'    => $nominal,
            'tanggal'    => $validated['tanggal'],
            'keterangan' => $validated['keterangan'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil disimpan.',
            'pengeluaran_id' => $pengeluaran->id,
        ]);
    }

    public function destroyPengeluaranUmumAjax(KonveksiPengeluaranUmum $pengeluaran)
    {
        $pengeluaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dihapus.',
        ]);
    }

    public function printPengeluaranPdf(KonveksiPengeluaranUmum $pengeluaran)
    {
        $data = [
            'judulNota' => 'NOTA PENGELUARAN',
            'labelNomor' => 'No. Pengeluaran',
            'nomorNota' => 'PNG-' . $pengeluaran->tanggal->format('Ymd') . '-' . str_pad($pengeluaran->id, 4, '0', STR_PAD_LEFT),
            'tanggalNota' => $pengeluaran->tanggal,
            'namaNota' => $pengeluaran->nama,
            'nominalNota' => $pengeluaran->nominal,
            'keteranganNota' => $pengeluaran->keterangan,
            'tampilkanBagianNota' => false,
            'pernyataanNota' => 'Demikian pengeluaran ini dibuat untuk dipergunakan sebagaimana mestinya.',
            'namaPenerima' => $pengeluaran->nama,
        ];

        $pdf = Pdf::loadView('konveksi.invoice-kasbon-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->stream('Nota-Pengeluaran-' . $pengeluaran->id . '.pdf');
    }

    // ============================================
    // MASTER KATEGORI KERJA
    // ============================================
    public function storeKategoriAjax(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:100',
        ]);

        $nama = trim($validated['kategori']);

        $exists = KonveksiKategori::whereRaw('LOWER(nama) = ?', [strtolower($nama)])->exists();
        if ($exists) {
            return response()->json(['message' => 'Kategori sudah ada.'], 422);
        }

        $kategori = KonveksiKategori::create(['nama' => $nama]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'id' => $kategori->id,
            'kategori' => $kategori->nama
        ]);
    }

    public function destroyKategoriAjax(KonveksiKategori $kategori)
    {
        if (Hanca::where('kategori', $kategori->nama)->exists()) {
            return response()->json([
                'message' => 'Kategori kerja sudah digunakan pada data hanca dan tidak dapat dihapus.',
            ], 422);
        }

        $kategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori kerja berhasil dihapus.',
        ]);
    }

    // ============================================
    // MASTER KATEGORI PRODUK
    // ============================================
    public function storeKategoriProdukAjax(Request $request)
    {
        $validated = $request->validate([
            'kategori_produk' => 'required|string|max:100',
        ]);

        $nama = trim($validated['kategori_produk']);

        $exists = KonveksiKategoriProduk::whereRaw('LOWER(nama) = ?', [strtolower($nama)])->exists();
        if ($exists) {
            return response()->json(['message' => 'Kategori produk sudah ada.'], 422);
        }

        $kategori = KonveksiKategoriProduk::create(['nama' => $nama]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori produk berhasil ditambahkan.',
            'id' => $kategori->id,
            'kategori' => $kategori->nama
        ]);
    }

    public function destroyKategoriProdukAjax(KonveksiKategoriProduk $kategoriProduk)
    {
        if (HancaDetail::where('kategori_produk_id', $kategoriProduk->id)->exists()) {
            return response()->json([
                'message' => 'Kategori produk sudah digunakan pada data hanca dan tidak dapat dihapus.',
            ], 422);
        }

        $kategoriProduk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori produk berhasil dihapus.',
        ]);
    }

    // ============================================
    // PRINT PDF INVOICE
    // ============================================
    public function printInvoicePdf(HancaPembayaran $pembayaran)
    {
        $pembayaran->load('hanca.karyawan');
        $hanca = $pembayaran->hanca;

        // Cari transaksi di detik yang sama
        $waktuTransaksi = $pembayaran->created_at;
        $allPembayarans = HancaPembayaran::with(['hancaDetail', 'kasbon'])
            ->where('created_at', $waktuTransaksi)
            ->where('tipe', 'bayaran')
            ->get();

        $details = collect();
        $totalNominal = 0;
        $kasbonId = null;

        foreach ($allPembayarans as $p) {
            $totalNominal += $p->nominal;
            if ($p->kasbon_id) {
                $kasbonId = $p->kasbon_id;
            }

            $detail = $p->hancaDetail; 
            if ($detail) {
                $details->push([
                    'nama_produk' => $detail->nama_produk,
                    'warna'       => $detail->warna,
                    'ukuran'      => $detail->ukuran,
                    'qty'         => $p->qty_setor,
                    'harga'       => $detail->harga,
                    'jumlah'      => $p->nominal,
                ]);
            }
        }

        // Hitung potongan kasbon khusus transaksi ini
        $totalKasbonDipotong = 0;
        if ($kasbonId) {
            $kasbon = \App\Models\KonveksiKasbon::find($kasbonId);
            if ($kasbon) {
                // Kita perlu tahu berapa yang dipotong hari ini. 
                // Kita asumsikan potongannya adalah minimal antara nominal setoran dan sisa kasbon.
                // Tapi karena data asli tidak menyimpan history potongan, kita pakai logika selisih.
                // Untuk amannya, kita gunakan data dari request sebelumnya (bisa disimpan di session/DB).
                // Namun, karena kita tidak menyimpan riwayat potongan di database, kita asumsikan:
                $totalKasbonDipotong = min($totalNominal, $kasbon->nominal); 
            }
        }

        $data = [
            'pembayaran'       => $pembayaran,
            'hanca'            => $hanca,
            'details'          => $details, 
            'total_nominal'    => $totalNominal, 
            'total_kasbon'     => $totalKasbonDipotong, 
            'karyawan'         => $hanca->karyawan,
            'no_bukti'         => 'INV-KNV-' . $pembayaran->tanggal->format('Ymd') . '-' . str_pad($pembayaran->id, 4, '0', STR_PAD_LEFT),
            'tanggal_cetak'    => now()->translatedFormat('d F Y H:i'),
            'sisa_setelah'     => max(0, $hanca->sisa),
        ];

        $pdf = Pdf::loadView('konveksi.invoice-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->stream('Bukti-Pembayaran-' . $pembayaran->id . '.pdf');
    }

    public function destroyDetailAjax(HancaDetail $detail)
    {
        $hanca = $detail->hanca;
        
        // Hapus detail
        $detail->delete();

        // Jika Hanca sudah tidak punya detail sama sekali, hapus Hancanya juga (opsional)
        if ($hanca && $hanca->details()->count() === 0) {
            $hanca->delete();
        } else if ($hanca) {
            // Recalculate sisa hanca jika masih ada detail
            $hanca->recalculateSisa();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Produk hanca berhasil dihapus.'
        ]);
    }

    public function kasbonIndex(Request $request)
    {
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $bulanFilter = $request->input('bulan', now()->format('Y-m'));

        $query = KonveksiKasbon::with('karyawan')
            // Hapus whereDoesntHave agar semua data muncul (termasuk yang lunas)
            ->when($karyawanId !== 'all', fn($q) => $q->where('konveksi_karyawan_id', $karyawanId))
            // ✅ Filter Bulan
            ->when($bulanFilter !== 'all', function($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                  ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                ->orWhereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"));
            });
        }

        $kasbonList = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $summary = [
            // Hanya hitung yang belum lunas untuk total kasbon berjalan
            'total_kasbon' => (int) $kasbonList->where('status', 'belum_lunas')->sum('nominal'),
            'jumlah_data'  => $kasbonList->count(),
        ];

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();

        // ✅ Ambil daftar bulan unik dari data kasbon
        $bulanList = KonveksiKasbon::selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function($item) {
                return [
                    'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->translatedFormat('F Y')
                ];
            });
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanFilter) && !$bulanList->contains('value', $bulanFilter)) {
            $bulanList->prepend([
                'value' => $bulanFilter,
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $bulanFilter)->translatedFormat('F Y'),
            ]);
        }

        return view('konveksi.kasbon', compact(
            'kasbonList', 'summary', 'karyawanList', 'search', 'karyawanId', 
            'bulanFilter', 'bulanList' // ✅ Kirim ke view
        ));
    }

    public function storeKasbonAjax(Request $request)
    {
        $validated = $request->validate([
            'konveksi_karyawan_id' => 'required|exists:konveksi_karyawans,id',
            'nominal'              => 'required',
            'tanggal'              => 'required|date',
            'keterangan'           => 'nullable|string|max:255',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', $validated['nominal']);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        $kasbon = KonveksiKasbon::create([
            'konveksi_karyawan_id' => $validated['konveksi_karyawan_id'],
            'nominal'              => $nominal,
            'nominal_awal'         => $nominal,
            'tanggal'              => $validated['tanggal'],
            'keterangan'           => $validated['keterangan'],
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Kasbon berhasil disimpan.',
            'kasbon_id' => $kasbon->id // ✅ Kirim ID untuk cetak PDF
        ]);
    }

    public function destroyKasbonAjax(KonveksiKasbon $kasbon)
    {
        $kasbon->delete();
        return response()->json(['success' => true, 'message' => 'Kasbon berhasil dihapus.']);
    }

    public function printKasbonPdf(KonveksiKasbon $kasbon)
    {
        $kasbon->load('karyawan');
        
        // Hitung total kasbon karyawan ini sampai saat ini (opsional)
        $totalKasbon = KonveksiKasbon::where('konveksi_karyawan_id', $kasbon->konveksi_karyawan_id)
            ->where('id', '<=', $kasbon->id)
            ->sum('nominal');

        $data = [
            'judulNota' => 'NOTA KASBON',
            'labelNomor' => 'No. Kasbon',
            'nomorNota' => 'KSN-' . $kasbon->tanggal->format('Ymd') . '-' . str_pad($kasbon->id, 4, '0', STR_PAD_LEFT),
            'tanggalNota' => $kasbon->tanggal,
            'namaNota' => $kasbon->karyawan->nama ?? '-',
            'nominalNota' => $kasbon->nominal_awal ?? $kasbon->nominal,
            'keteranganNota' => $kasbon->keterangan,
            'tampilkanBagianNota' => true,
            'total_kasbon' => $totalKasbon,
            'pernyataanNota' => 'Demikian kasbon ini dibuat untuk dipergunakan sebagaimana mestinya.',
            'namaPenerima' => $kasbon->karyawan->nama ?? 'Penerima',
        ];

        $pdf = Pdf::loadView('konveksi.invoice-kasbon-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->stream('Nota-Kasbon-' . $kasbon->id . '.pdf');
    }

    public function getSisaKasbonAjax(Request $request)
    {
        $karyawanId = $request->query('karyawan_id');
        if (!$karyawanId) return response()->json(['total' => 0]);

        $totalKasbon = KonveksiKasbon::where('konveksi_karyawan_id', $karyawanId)
            ->where('status', 'belum_lunas') // ✅ Filter status
            ->sum('nominal');

        return response()->json(['total' => (int) $totalKasbon]);
    }

    public function getKasbonByKaryawanAjax(Request $request)
    {
        $karyawanId = $request->query('karyawan_id');
        $kasbons = KonveksiKasbon::where('konveksi_karyawan_id', $karyawanId)
            ->where('status', 'belum_lunas') // ✅ Filter status
            ->get();
        
        return response()->json($kasbons);
    }

    public function bayarKasbonAjax(Request $request, KonveksiKasbon $kasbon)
    {
        $validated = $request->validate([
            'nominal_bayar' => 'required',
            'tanggal_bayar' => 'required|date',
        ]);

        $nominalBayar = (int) preg_replace('/[^0-9]/', '', $validated['nominal_bayar']);
        if ($nominalBayar <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        DB::beginTransaction();
        try {
            if ($nominalBayar >= $kasbon->nominal) {
                // Jika bayar lebih besar/sama dengan sisa kasbon -> Lunas
                $kasbon->update([
                    'status' => 'lunas',
                    'nominal' => 0, // Opsional: set 0 jika lunas
                ]);
                $pesan = 'Kasbon berhasil dilunasi.';
            } else {
                // Jika bayar sebagian -> Kurangi nominal
                $kasbon->update([
                    'nominal' => $kasbon->nominal - $nominalBayar,
                ]);
                $pesan = 'Kasbon berhasil dibayar sebagian.';
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $pesan]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function dataBayaran(Request $request)
    {
        $karyawanId  = $request->input('karyawan_id', 'all');
        $bulanFilter = $request->input('bulan', now()->format('Y-m'));
        $search      = trim($request->input('search', ''));

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();

        // 1. QUERY PEMBAYARAN (BELUM DIBAYAR)
        // Mengambil dari HancaPembayaran yang memiliki nominal 0 (belum ditentukan upahnya)
        $queryBelum = HancaPembayaran::with(['hanca.karyawan', 'hancaDetail'])
            ->where('tipe', 'bayaran')
            ->where('nominal', 0) // ✅ Belum dibayar (nominal masih 0)
            ->when($karyawanId !== 'all', function ($q) use ($karyawanId) {
                $q->whereHas('hanca', fn($sq) => $sq->where('konveksi_karyawan_id', $karyawanId));
            })
            ->when($bulanFilter !== 'all', function ($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                  ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('hanca.karyawan', fn($sq) => $sq->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('hancaDetail', fn($sq) => $sq->where('nama_produk', 'like', "%{$search}%"));
            })
            ->orderBy('tanggal', 'asc')
            ->get();

        // 2. QUERY PEMBAYARAN (RIWAYAT)
        // Mengambil dari HancaPembayaran yang nominalnya > 0 (sudah dibayar)
        $queryRiwayat = HancaPembayaran::with(['hanca.karyawan', 'hancaDetail'])
            ->where('tipe', 'bayaran')
            ->where('nominal', '>', 0) // ✅ Sudah dibayar
            ->when($karyawanId !== 'all', function ($q) use ($karyawanId) {
                $q->whereHas('hanca', fn($sq) => $sq->where('konveksi_karyawan_id', $karyawanId));
            })
            ->when($bulanFilter !== 'all', function ($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                  ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('hanca.karyawan', fn($sq) => $sq->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('hancaDetail', fn($sq) => $sq->where('nama_produk', 'like', "%{$search}%"));
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $pembayaranBelumDibayar = $queryBelum;
        $pembayaranRiwayat      = $queryRiwayat;

        // 3. AMBIL DATA KASBON AKTIF
        $kasbonAktif = [];
        
        // Ambil kasbon yang statusnya belum lunas (atau nominal > 0)
        $kasbons = KonveksiKasbon::where('status', 'belum_lunas')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($kasbons as $kasbon) {
            // Karena di tabel kasbon Anda menggunakan 'nominal' sebagai sisa, kita ambil itu
            $sisa = $kasbon->nominal; 
            if ($sisa > 0) {
                $kasbonAktif[$kasbon->konveksi_karyawan_id][] = [
                    'id'         => $kasbon->id,
                    'sisa'       => $sisa,
                    'keterangan' => $kasbon->keterangan ?? 'Kasbon #' . $kasbon->id,
                ];
            }
        }

        // 4. LIST BULAN UNTUK FILTER
        $bulanList = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $bulanList->push([
                'value' => $date->format('Y-m'),
                'label' => $date->translatedFormat('F Y'),
            ]);
        }

        return view('konveksi.data-bayaran', compact(
            'karyawanList',
            'karyawanId',
            'bulanList',
            'bulanFilter',
            'search',
            'pembayaranBelumDibayar',
            'pembayaranRiwayat',
            'kasbonAktif'
        ));
    }

    public function hancaSelesai(Request $request)
    {
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $bulanFilter = $request->input('bulan', now()->format('Y-m')); 

        // ✅ Ambil hanca yang sudah selesai (sisa_hanca <= 0)
        $query = Hanca::with(['karyawan', 'details.kategoriProduk', 'pembayarans', 'details.turunanJahit.hanca.karyawan'])
            ->whereHas('details', function($q) {
                // Pastikan SEMUA detail sudah selesai (tidak ada yang > 0)
                $q->where('sisa_hanca', '<=', 0);
            })
            ->when($karyawanId !== 'all', fn($q) => $q->where('konveksi_karyawan_id', $karyawanId))
            ->when($bulanFilter !== 'all', function ($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kategori', 'LIKE', "%{$search}%")
                ->orWhereHas('karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"))
                ->orWhereHas('details', fn($sq) => $sq->where('nama_produk', 'LIKE', "%{$search}%"));
            });
        }

        $hancaList = $query->orderByDesc('updated_at')->get();

        // ✅ Kelompokkan berdasarkan Kategori Kerja (Potong Kain / Jahit / dll)
        $groupedByKategori = $hancaList->groupBy('kategori');

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();
        
        // Ambil list bulan dari data hanca
        $bulanList = Hanca::selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->translatedFormat('F Y'),
                ];
            });
            
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanFilter) && !$bulanList->contains('value', $bulanFilter)) {
            $bulanList->prepend([
                'value' => $bulanFilter,
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $bulanFilter)->translatedFormat('F Y'),
            ]);
        }

        return view('konveksi.hanca-selesai', compact(
            'groupedByKategori', 'karyawanList', 'search', 'karyawanId', 'bulanFilter', 'bulanList'
        ));
    }
}