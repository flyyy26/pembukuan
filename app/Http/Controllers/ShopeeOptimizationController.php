<?php

namespace App\Http\Controllers;

use App\Models\ProductOptimization;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ShopeeOptimizationController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->input('store');
        $status  = $request->input('status', '');
        $search  = trim($request->input('search', ''));

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        $query = ProductOptimization::query();
        if ($storeId) $query->where('store_id', $storeId);
        if ($status)  $query->where('status_global', $status);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'LIKE', "%{$search}%")
                  ->orWhere('marketplace_sku', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderByRaw("CASE status_global WHEN 'perhatian' THEN 1 WHEN 'evaluasi' THEN 2 ELSE 3 END")
            ->orderBy('skor_optimasi')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Ringkasan
        $base = ProductOptimization::query();
        if ($storeId) $base->where('store_id', $storeId);
        $all = $base->get();

        $summary = [
            'total'      => $all->count(),
            'aman'       => $all->where('status_global', 'aman')->count(),
            'evaluasi'   => $all->where('status_global', 'evaluasi')->count(),
            'perhatian'  => $all->where('status_global', 'perhatian')->count(),
            'avg_skor'   => $all->count() ? round($all->avg('skor_optimasi'), 1) : 0,
            'avg_margin' => $all->count() ? round($all->avg(fn($r) => $r->margin_persen), 1) : 0,
        ];

        // Masalah per aspek (berapa produk yang evaluasi / perhatian)
        $aspekProblem = [];
        foreach (array_keys(ProductOptimization::ASPEK_AUDIT) as $key) {
            $evaluasi = 0; $perhatian = 0;
            foreach ($all as $r) {
                $a = $r->getAspek($key);
                if ($a['status'] === 'evaluasi')  $evaluasi++;
                if ($a['status'] === 'perhatian') $perhatian++;
            }
            $aspekProblem[$key] = [
                'evaluasi'  => $evaluasi,
                'perhatian' => $perhatian,
                'total'     => $evaluasi + $perhatian,
            ];
        }

        return view('shopee.optimasi.index', compact(
            'stores', 'store', 'storeId',
            'items', 'summary', 'aspekProblem',
            'status', 'search'
        ));
    }

    public function create(Request $request)
    {
        $storeId = $request->input('store');
        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        $store = $storeId ? Store::find($storeId) : $stores->first();
        $item = new ProductOptimization(['store_id' => $store?->id]);

        return view('shopee.optimasi.form', compact('item', 'stores', 'store'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['store_id'] = $request->input('store_id');

        $item = new ProductOptimization();
        $this->fillItem($item, $validated);
        $item->save();

        return redirect()->route('shopee.optimasi.index', ['store' => $item->store_id])
            ->with('success', "Produk optimasi '{$item->nama_produk}' berhasil ditambahkan.");
    }

    public function edit(ProductOptimization $optimasi)
    {
        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        $store = $optimasi->store;
        $item = $optimasi;

        return view('shopee.optimasi.form', compact('item', 'stores', 'store'));
    }

    public function update(Request $request, ProductOptimization $optimasi)
    {
        $validated = $this->validateData($request);
        $validated['store_id'] = $request->input('store_id');

        $this->fillItem($optimasi, $validated);
        $optimasi->save();

        return redirect()->route('shopee.optimasi.index', ['store' => $optimasi->store_id])
            ->with('success', "Produk optimasi '{$optimasi->nama_produk}' berhasil diperbarui.");
    }

    public function destroy(ProductOptimization $optimasi)
    {
        $storeId = $optimasi->store_id;
        $nama = $optimasi->nama_produk;
        $optimasi->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Produk '{$nama}' berhasil dihapus."]);
        }

        return redirect()->route('shopee.optimasi.index', ['store' => $storeId])
            ->with('success', "Produk optimasi '{$nama}' berhasil dihapus.");
    }

    // ==================== EXPORT PDF ====================
    public function exportPdf(Request $request)
    {
        $storeId = $request->input('store');
        $status  = $request->input('status', '');
        $search  = trim($request->input('search', ''));

        $stores = Store::where('marketplace', 'shopee')->orderBy('nama_toko')->get();
        if (!$storeId && $stores->isNotEmpty()) {
            $storeId = $stores->first()->id;
        }
        $store = $storeId ? Store::find($storeId) : null;

        $query = ProductOptimization::with('store');
        if ($storeId) $query->where('store_id', $storeId);
        if ($status)  $query->where('status_global', $status);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'LIKE', "%{$search}%")
                  ->orWhere('marketplace_sku', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderByRaw("CASE status_global WHEN 'perhatian' THEN 1 WHEN 'evaluasi' THEN 2 ELSE 3 END")
            ->orderBy('skor_optimasi')
            ->get();

        $summary = [
            'total'     => $items->count(),
            'aman'      => $items->where('status_global', 'aman')->count(),
            'evaluasi'  => $items->where('status_global', 'evaluasi')->count(),
            'perhatian' => $items->where('status_global', 'perhatian')->count(),
            'avg_skor'  => $items->count() ? round($items->avg('skor_optimasi'), 1) : 0,
        ];

        $pdf = Pdf::loadView('shopee.optimasi.pdf', [
            'store'        => $store,
            'items'        => $items,
            'summary'      => $summary,
            'status'       => $status,
            'search'       => $search,
            'generated_at' => now()->translatedFormat('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        $fileName = 'optimasi-produk-' . ($store?->nama_toko ?? 'all') . '-' . now()->format('Ymd-His') . '.pdf';
        $fileName = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $fileName);

        return $pdf->download($fileName);
    }

    // ==================== EXPORT EXCEL (CSV) ====================
    public function exportExcel(Request $request)
    {
        $storeId = $request->input('store');
        $status  = $request->input('status', '');
        $search  = trim($request->input('search', ''));

        $query = ProductOptimization::with('store');
        if ($storeId) $query->where('store_id', $storeId);
        if ($status)  $query->where('status_global', $status);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'LIKE', "%{$search}%")
                  ->orWhere('marketplace_sku', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderByRaw("CASE status_global WHEN 'perhatian' THEN 1 WHEN 'evaluasi' THEN 2 ELSE 3 END")
            ->orderBy('skor_optimasi')
            ->get();

        $fileName = 'optimasi-produk-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($items) {
            $out = fopen('php://output', 'w');
            // BOM biar Excel baca UTF-8
            fwrite($out, "\xEF\xBB\xBF");

            $aspekKeys = array_keys(ProductOptimization::ASPEK_AUDIT);

            // Header
            $header = [
                'Toko', 'Nama Produk', 'SKU', 'Harga Produk', 'Biaya Layanan', 'Ongkir',
                'Diskon Produk', 'Total Diskon Pengiriman', 'Voucher Diskon', 'Harga Final',
                'Margin %', 'Skor Optimasi', 'Status Global', 'Terakhir Audit',
            ];
            foreach ($aspekKeys as $k) {
                $header[] = ProductOptimization::ASPEK_AUDIT[$k]['label'] . ' (Status)';
                $header[] = ProductOptimization::ASPEK_AUDIT[$k]['label'] . ' (Catatan)';
            }
            fputcsv($out, $header);

            // Body
            foreach ($items as $item) {
                $row = [
                    $item->store?->nama_toko ?? '-',
                    $item->nama_produk,
                    $item->marketplace_sku ?? '-',
                    $item->harga_produk,
                    $item->biaya_layanan,
                    $item->ongkir,
                    $item->diskon_produk,
                    $item->total_diskon_pengiriman,
                    $item->voucher_diskon,
                    $item->harga_final,
                    $item->margin_persen . '%',
                    $item->skor_optimasi,
                    ProductOptimization::STATUS_META[$item->status_global]['label'],
                    $item->terakhir_audit?->format('Y-m-d') ?? '-',
                ];
                foreach ($aspekKeys as $k) {
                    $a = $item->getAspek($k);
                    $row[] = ProductOptimization::STATUS_META[$a['status']]['label'];
                    $row[] = $a['note'];
                }
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==================== HELPERS ====================
    private function validateData(Request $request): array
    {
        // ✅ Bersihkan format ribuan (titik) sebelum validasi
        $numericFields = [
            'harga_produk', 'biaya_layanan', 'ongkir',
            'diskon_produk', 'total_diskon_pengiriman',
            'voucher_diskon', 'harga_final',
        ];
        $cleaned = [];
        foreach ($numericFields as $f) {
            $val = $request->input($f);
            $cleaned[$f] = ($val === null || $val === '') ? 0 : (int) preg_replace('/[^\d-]/', '', (string) $val);
        }
        $request->merge($cleaned);

        $aspekKeys = array_keys(ProductOptimization::ASPEK_AUDIT);

        $rules = [
            'store_id'                 => 'nullable|exists:stores,id',
            'nama_produk'              => 'required|string|max:255',
            'marketplace_sku'          => 'nullable|string|max:100',
            'harga_produk'             => 'nullable|integer|min:0',
            'biaya_layanan'            => 'nullable|integer|min:0',
            'ongkir'                   => 'nullable|integer|min:0',
            'diskon_produk'            => 'nullable|integer|min:0',
            'total_diskon_pengiriman'  => 'nullable|integer|min:0',
            'voucher_diskon'           => 'nullable|integer|min:0',
            'harga_final'              => 'nullable|integer|min:0',
            'terakhir_audit'           => 'nullable|date',
        ];
        foreach ($aspekKeys as $k) {
            $rules["audit_{$k}.status"]  = 'nullable|in:aman,evaluasi,perhatian';
            $rules["audit_{$k}.note"]    = 'nullable|string|max:5000';
            $rules["solusi_{$k}"]        = 'nullable|string|max:3000';
            $rules["sudah_{$k}"]         = 'nullable|boolean';
        }

        return $request->validate($rules);
    }

    private function fillItem(ProductOptimization $item, array $validated): void
    {
        $item->fill([
            'store_id'                 => $validated['store_id'] ?? null,
            'nama_produk'              => $validated['nama_produk'],
            'marketplace_sku'          => $validated['marketplace_sku'] ?? null,
            'harga_produk'             => $validated['harga_produk'] ?? 0,
            'biaya_layanan'            => $validated['biaya_layanan'] ?? 0,
            'ongkir'                   => $validated['ongkir'] ?? 0,
            'diskon_produk'            => $validated['diskon_produk'] ?? 0,
            'total_diskon_pengiriman'  => $validated['total_diskon_pengiriman'] ?? 0,
            'voucher_diskon'           => $validated['voucher_diskon'] ?? 0,
            'harga_final'              => $validated['harga_final'] ?? 0,
            'terakhir_audit'           => $validated['terakhir_audit'] ?? now()->toDateString(),
        ]);

        foreach (array_keys(ProductOptimization::ASPEK_AUDIT) as $k) {
            // Audit: status + note
            $data = $validated["audit_{$k}"] ?? null;
            $item->{"audit_{$k}"} = $data ?: null;

            // ✅ Field baru: solusi + status pelaksanaan
            $item->{"solusi_{$k}"} = $validated["solusi_{$k}"] ?? null;
            $item->{"sudah_{$k}"}  = !empty($validated["sudah_{$k}"]);
        }

        // Auto-hitung
        $item->skor_optimasi = $item->hitungSkor();
        $item->status_global = $item->hitungStatusGlobal();
    }
}