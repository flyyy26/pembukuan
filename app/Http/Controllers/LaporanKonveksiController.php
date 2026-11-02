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

            // Hapus detail yang dihapus user
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
        $validated = $request->validate([
            'qty_setor'       => 'required|array|min:1',
            'qty_setor.*'     => 'integer|min:0',
            'nominal_setor'   => 'required|array|min:1',
            'nominal_setor.*' => 'integer|min:0',
            'kasbon_ids'      => 'nullable|array',
            'kasbon_ids.*'    => 'exists:konveksi_kasbons,id',
            'tanggal'         => 'required|date',
            'keterangan'      => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $pembayaranIds = [];
            
            // Hitung total nominal yang disetor hari ini
            $totalSetoran = array_sum($validated['nominal_setor']);
            
            // Ambil ID Kasbon pertama
            $kasbonId = !empty($validated['kasbon_ids']) ? $validated['kasbon_ids'][0] : null;
            $kasbon = $kasbonId ? KonveksiKasbon::find($kasbonId) : null;

            // LOGIKA PEMOTONGAN KASBON
            $nominalDipotong = 0;
            $sisaSetoran = $totalSetoran;

            if ($kasbon && $sisaSetoran > 0) {
                if ($kasbon->nominal <= $sisaSetoran) {
                    $nominalDipotong = $kasbon->nominal;
                    $kasbon->status = 'lunas';
                    $kasbon->nominal = 0; 
                    $kasbon->save();
                    $sisaSetoran -= $nominalDipotong;
                } else {
                    $nominalDipotong = $sisaSetoran;
                    $kasbon->nominal -= $sisaSetoran;
                    $kasbon->save();
                    $sisaSetoran = 0;
                }
            }

            // Simpan setoran hanca (seperti biasa)
            $sisaKasbonDipotong = $nominalDipotong;
            foreach ($validated['qty_setor'] as $detailId => $qty) {
                if ($qty <= 0) continue;

                $detail = HancaDetail::find($detailId);
                if (!$detail) continue;

                $hanca = $detail->hanca;
                $nominal = (int) ($validated['nominal_setor'][$detailId] ?? 0);
                if ($nominal <= 0) continue;

                $kasbonDipotongDetail = min($sisaKasbonDipotong, $nominal);
                $sisaKasbonDipotong -= $kasbonDipotongDetail;

                $pembayaran = HancaPembayaran::create([
                    'hanca_id'        => $hanca->id,
                    'hanca_detail_id' => $detail->id, 
                    'kasbon_id'       => $kasbonId, 
                    'tipe'            => 'bayaran',
                    'nominal'         => $nominal,
                    'kasbon_dipotong' => $kasbonDipotongDetail,
                    'qty_setor'       => $qty,
                    'tanggal'         => $validated['tanggal'],
                    'keterangan'      => $validated['keterangan'],
                ]);

                $detail->sisa_hanca -= $qty;
                $detail->sisa_nominal -= $nominal;
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

    public function pengeluaranIndex()
    {
        $pengeluaranList = KonveksiPengeluaranUmum::orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('konveksi.data-pengeluaran', compact('pengeluaranList'));
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
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
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
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
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
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
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
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $bulanFilter = $request->input('bulan', now()->format('Y-m'));

        $query = HancaPembayaran::with(['hanca.karyawan', 'hancaDetail'])
            ->where('qty_setor', '>', 0)
            ->whereNotNull('hanca_detail_id')
            ->when($karyawanId !== 'all', function ($q) use ($karyawanId) {
                $q->whereHas('hanca', fn($sq) => $sq->where('konveksi_karyawan_id', $karyawanId));
            })
            ->when($bulanFilter !== 'all', function ($q) use ($bulanFilter) {
                $q->whereYear('tanggal', substr($bulanFilter, 0, 4))
                    ->whereMonth('tanggal', substr($bulanFilter, 5, 2));
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                    ->orWhereHas('hanca', fn($sq) => $sq->where('kategori', 'LIKE', "%{$search}%"))
                    ->orWhereHas('hanca.karyawan', fn($sq) => $sq->where('nama', 'LIKE', "%{$search}%"))
                    ->orWhereHas('hancaDetail', fn($sq) => $sq->where('nama_produk', 'LIKE', "%{$search}%"));
            });
        }

        $pembayarans = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();
        
        $bulanList = HancaPembayaran::where('qty_setor', '>', 0)
            ->whereNotNull('hanca_detail_id')
            ->selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month')
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

        return view('konveksi.data-bayaran', compact(
            'pembayarans', 'karyawanList', 'search', 'karyawanId', 'bulanFilter', 'bulanList'
        ));
    }

    public function hancaSelesai(Request $request)
    {
        $search = trim($request->input('search', ''));
        $karyawanId = $request->input('karyawan_id', 'all');
        $bulanFilter = $request->input('bulan', now()->format('Y-m'));

        $query = Hanca::with(['karyawan', 'details.kategoriProduk', 'pembayarans'])
            ->whereHas('details', fn($q) => $q->where('sisa_hanca', '<=', 0))
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
        $detailsSelesai = collect();

        foreach ($hancaList as $hanca) {
            foreach ($hanca->details as $detail) {
                if ($detail->sisa_hanca <= 0) {
                    $detail->hanca_induk = $hanca;
                    $detailsSelesai->push($detail);
                }
            }
        }

        $groupedHancas = $detailsSelesai->groupBy('nama_produk');
        $karyawanList = KonveksiKaryawan::orderBy('nama')->get();
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
            'groupedHancas', 'karyawanList', 'search', 'karyawanId', 'bulanFilter', 'bulanList'
        ));
    }
}