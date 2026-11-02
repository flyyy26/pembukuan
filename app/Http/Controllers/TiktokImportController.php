<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\Store;
use App\Models\TiktokIncome;
use App\Models\TiktokOrderItem;
use App\Support\ImportedFileStorage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TiktokImportController extends Controller
{
    public function index(Request $request)
    {
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput;
        $to   = $toInput;

        // Jika hanya salah satu diisi, samakan
        if ($from && !$to)       $to = $from;
        elseif ($to && !$from)   $from = $to;

        $imports = Import::with('store')
            ->whereIn('tipe', ['tiktok_order', 'tiktok_income'])
            ->when($from, fn($q) => $q->whereDate('periode_start', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('periode_start', '<=', $to))
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->only(['from', 'to']));

        $stores = Store::where('marketplace', 'tiktok')
            ->where('is_active', true)
            ->orderBy('nama_toko')
            ->get();

        return view('tiktok.import', compact('imports', 'stores', 'fromInput', 'toInput'));
    }

    /**
     * Import 2 file TikTok sekaligus (Order + Income).
     */
    public function importBoth(Request $request)
    {
        $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'order_file'  => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,application/zip,text/csv|max:20480',
            'income_file' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,application/zip,text/csv|max:20480',
        ]);

        $store = Store::findOrFail($request->store_id);
        if ($store->marketplace !== 'tiktok') {
            return back()->with('error', 'Toko ini bukan TikTok Shop.');
        }

        // ==================================================
        // PROSES FILE 1: Order TikTok
        // ==================================================
        $orderFile = $request->file('order_file');
        $orderStoredPath = $orderFile->store('imports', 'local');
        $orderFullPath = Storage::disk('local')->path($orderStoredPath);
        $orderHash = hash_file('sha256', $orderFullPath);

        $existingOrder = Import::where('file_hash', $orderHash)
            ->where('tipe', 'tiktok_order')
            ->first();

        if ($existingOrder) {
            Storage::disk('local')->delete($orderStoredPath);
            $orderSkipped = true;
            $orderRows = 0;
        } else {
            try {
                $orderData = $this->parseTiktokOrderFile($orderFullPath);
            } catch (\Exception $e) {
                Storage::disk('local')->delete($orderStoredPath);
                return back()->with('error', 'Gagal parse Order TikTok: ' . $e->getMessage());
            }

            DB::beginTransaction();
            try {
                $orderImport = Import::create([
                    'store_id'      => $store->id,
                    'tipe'          => 'tiktok_order',
                    'file_name'     => $orderFile->getClientOriginalName(),
                    'stored_path'   => $orderStoredPath,
                    'file_hash'     => $orderHash,
                    'periode_start' => $orderData['periode_start'],
                    'periode_end'   => $orderData['periode_end'],
                    'total_rows'    => $orderData['total_rows'],
                    'status'        => 'pending',
                ]);

                foreach ($orderData['rows'] as $row) {
                    $row['import_id'] = $orderImport->id;
                    TiktokOrderItem::create($row);
                }

                $orderImport->update(['status' => 'imported']);
                DB::commit();
                $orderSkipped = false;
                $orderRows = $orderData['total_rows'];
            } catch (\Exception $e) {
                DB::rollBack();
                Storage::disk('local')->delete($orderStoredPath);
                return back()->with('error', 'Gagal simpan Order TikTok ke database: ' . $e->getMessage());
            }
        }

        // ==================================================
        // PROSES FILE 2: Income TikTok
        // ==================================================
        $incomeFile = $request->file('income_file');
        $incomeStoredPath = $incomeFile->store('imports', 'local');
        $incomeFullPath = Storage::disk('local')->path($incomeStoredPath);
        $incomeHash = hash_file('sha256', $incomeFullPath);

        $existingIncome = Import::where('file_hash', $incomeHash)
            ->where('tipe', 'tiktok_income')
            ->first();

        if ($existingIncome) {
            Storage::disk('local')->delete($incomeStoredPath);
            $incomeSkipped = true;
            $incomeRows = 0;
        } else {
            try {
                $incomeData = $this->parseTiktokIncomeFile($incomeFullPath);
            } catch (\Exception $e) {
                Storage::disk('local')->delete($incomeStoredPath);
                return back()->with('error', 'Gagal parse Income TikTok: ' . $e->getMessage());
            }

            DB::beginTransaction();
            try {
                $incomeImport = Import::create([
                    'store_id'      => $store->id,
                    'tipe'          => 'tiktok_income',
                    'file_name'     => $incomeFile->getClientOriginalName(),
                    'stored_path'   => $incomeStoredPath,
                    'file_hash'     => $incomeHash,
                    'periode_start' => $incomeData['periode_start'],
                    'periode_end'   => $incomeData['periode_end'],
                    'total_rows'    => $incomeData['total_rows'],
                    'status'        => 'pending',
                ]);

                foreach ($incomeData['rows'] as $row) {
                    $row['import_id'] = $incomeImport->id;
                    TiktokIncome::create($row);
                }

                $incomeImport->update(['status' => 'imported']);
                DB::commit();
                $incomeSkipped = false;
                $incomeRows = $incomeData['total_rows'];
            } catch (\Exception $e) {
                DB::rollBack();
                Storage::disk('local')->delete($incomeStoredPath);
                return back()->with('error', 'Gagal simpan Income TikTok ke database: ' . $e->getMessage());
            }
        }

        // ==================================================
        // AUTO RECONCILE
        // ==================================================
        \Artisan::call('tiktok:reconcile', ['store_id' => $store->id]);

        // ==================================================
        // FLASH MESSAGE
        // ==================================================
        $messages = [];

        if ($orderSkipped) {
            $messages[] = "Order TikTok: sudah pernah diimport (skip)";
        } else {
            $messages[] = "Order TikTok: {$orderRows} baris berhasil";
        }

        if ($incomeSkipped) {
            $messages[] = "Income TikTok: sudah pernah diimport (skip)";
        } else {
            $messages[] = "Income TikTok: {$incomeRows} baris berhasil";
        }

        return redirect()->route('tiktok.import')
            ->with('success', 'Import selesai. ' . implode('. ', $messages) . '. Rekonsiliasi otomatis dijalankan.');
    }

    public function destroy($id)
    {
        // Cari berdasarkan ID saja, tanpa filter tipe dulu
        $import = Import::find($id);

        // Jika data tidak ditemukan sama sekali (mungkin sudah dihapus atau ID salah)
        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Data import tidak ditemukan. Mungkin sudah dihapus sebelumnya.'
            ], 404);
        }

        // Cek apakah ini benar-benar import TikTok
        if (!in_array($import->tipe, ['tiktok_order', 'tiktok_income'])) {
            return response()->json([
                'success' => false,
                'message' => 'Data ini bukan bagian dari import TikTok.'
            ], 400);
        }

        $storeId = $import->store_id;
        $storedPath = $import->stored_path;
        $fileHash = $import->file_hash;

        DB::beginTransaction();
        try {
            // Hapus data turunan berdasarkan tipe import
            if ($import->tipe === 'tiktok_order') {
                TiktokOrderItem::where('import_id', $import->id)->delete();
            } elseif ($import->tipe === 'tiktok_income') {
                TiktokIncome::where('import_id', $import->id)->delete();
            }

            // Hapus record import utama
            $import->delete();

            // Rekonsiliasi ulang untuk memperbarui tabel Sales (agar data yang dihapus hilang dari laporan)
            if ($storeId) {
                \Artisan::call('tiktok:reconcile', ['store_id' => $storeId]);
            }

            DB::commit();

            try {
                ImportedFileStorage::deleteAfterImportRemoval($storedPath, $fileHash);
            } catch (\Throwable $e) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => 'Data import berhasil dihapus, tetapi file Excel gagal dihapus dari storage.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data import beserta transaksi terkait berhasil dihapus.'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse file Order TikTok.
     */
    private function parseTiktokOrderFile(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);

        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            if ($s->getTitle() === 'OrderSKUList') {
                $sheet = $s;
                break;
            }
        }

        if (!$sheet) {
            throw new \Exception("Sheet 'OrderSKUList' tidak ditemukan.");
        }

        $data = $sheet->toArray(null, true, true, false);
        if (count($data) < 2) {
            throw new \Exception("File terlalu pendek.");
        }

        $headers = array_map('trim', $data[0]);

        $rows = [];
        for ($i = 2; $i < count($data); $i++) {
            $row = $data[$i];
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') continue;
                $value = $row[$index] ?? null;
                $assoc[$header] = is_string($value) ? trim($value) : $value;
            }

            $rows[] = [
                'import_id'                    => null,
                'order_id'                     => $assoc['Order ID'] ?? null,
                'order_status'                 => $this->nullify($assoc['Order Status'] ?? null),
                'order_substatus'              => $this->nullify($assoc['Order Substatus'] ?? null),
                'sku_id'                       => $this->nullify($assoc['SKU ID'] ?? null),
                'seller_sku'                   => $this->nullify($assoc['Seller SKU'] ?? null),
                'product_name'                 => $this->nullify($assoc['Product Name'] ?? null),
                'variation'                    => $this->nullify($assoc['Variation'] ?? null),
                'quantity'                     => $this->toInt($assoc['Quantity'] ?? 0),
                'sku_unit_original_price'      => $this->toInt($assoc['SKU Unit Original Price'] ?? 0),
                'sku_subtotal_before_discount' => $this->toInt($assoc['SKU Subtotal Before Discount'] ?? 0),
                'sku_platform_discount'        => $this->toInt($assoc['SKU Platform Discount'] ?? 0),
                'sku_seller_discount'          => $this->toInt($assoc['SKU Seller Discount'] ?? 0),
                'sku_subtotal_after_discount'  => $this->toInt($assoc['SKU Subtotal After Discount'] ?? 0),
                'order_amount'                 => $this->toInt($assoc['Order Amount'] ?? 0),
                'created_time'                 => $this->toDateTime($assoc['Created Time'] ?? null),
                'paid_time'                    => $this->toDateTime($assoc['Paid Time'] ?? null),
                'shipped_time'                 => $this->toDateTime($assoc['Shipped Time'] ?? null),
                'delivered_time'               => $this->toDateTime($assoc['Delivered Time'] ?? null),
                'cancelled_time'               => $this->toDateTime($assoc['Cancelled Time'] ?? null),
                'cancel_reason'                => $this->nullify($assoc['Cancel Reason'] ?? null),
                'payment_method'               => $this->nullify($assoc['Payment Method'] ?? null),
                'order_channel'                => $this->nullify($assoc['Order Channel'] ?? null),
            ];
        }

        $tanggalList = collect($rows)
            ->pluck('created_time')
            ->filter()
            ->sort()
            ->values();

        return [
            'rows'          => $rows,
            'total_rows'    => count($rows),
            'periode_start' => $tanggalList->first() ? substr($tanggalList->first(), 0, 10) : null,
            'periode_end'   => $tanggalList->last()  ? substr($tanggalList->last(),  0, 10) : null,
        ];
    }

    /**
     * Parse file Income TikTok.
     */
    private function parseTiktokIncomeFile(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);

        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            if ($s->getTitle() === 'Detail pesanan') {
                $sheet = $s;
                break;
            }
        }

        if (!$sheet) {
            throw new \Exception("Sheet 'Detail pesanan' tidak ditemukan.");
        }

        $data = $sheet->toArray(null, true, true, false);
        if (count($data) < 2) {
            throw new \Exception("File terlalu pendek.");
        }

        $headers = array_map('trim', $data[0]);

        $rows = [];
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') continue;
                $value = $row[$index] ?? null;
                $assoc[$header] = is_string($value) ? trim($value) : $value;
            }

            // Parse detail produk terjual
            $detailParsed = $this->parseDetailProduk($assoc['Detail produk terjual'] ?? '');

            $rows[] = [
                'import_id'                        => null,
                'order_id'                         => $assoc['ID Pesanan/Penyesuaian'] ?? null,
                'jenis_transaksi'                  => $this->nullify($assoc['Jenis transaksi'] ?? null),
                'waktu_pemesanan'                  => $this->toDate($assoc['Waktu pemesanan'] ?? null),
                'waktu_pembayaran_pesanan'         => $this->toDate($assoc['Waktu pembayaran pesanan'] ?? null),
                'jumlah_penyelesaian_pembayaran'   => $this->toInt($assoc['Jumlah penyelesaian pembayaran'] ?? 0),
                'total_pendapatan' => $this->toInt($assoc['Total Pendapatan'] ?? 0),
                'subtotal_setelah_diskon_penjual'  => $this->toInt($assoc['Subtotal setelah diskon penjual'] ?? 0),
                'subtotal_sebelum_diskon'          => $this->toInt($assoc['Subtotal sebelum diskon'] ?? 0),
                'diskon_penjual'                   => $this->toInt($assoc['Diskon penjual'] ?? 0),
                'total_biaya'                      => $this->toInt($assoc['Total Biaya'] ?? 0),
                'biaya_komisi_platform'            => $this->toInt($assoc['Biaya komisi platform'] ?? 0),
                'komisi_afiliasi'                  => $this->toInt($assoc['Komisi Afiliasi'] ?? 0),
                'komisi_dinamis'                   => $this->toInt($assoc['Komisi dinamis'] ?? 0),
                'biaya_growth_xtra'                => $this->toInt($assoc['Biaya layanan Program Growth Xtra'] ?? 0),
                'biaya_pemrosesan_pesanan'         => $this->toInt($assoc['Biaya pemrosesan pesanan'] ?? 0),
                'pph_22'                           => $this->toInt($assoc['PPh Pasal 22 dipungut'] ?? 0),
                'ongkir'                           => $this->toInt($assoc['Ongkir'] ?? 0),
                'detail_produk_terjual'            => $assoc['Detail produk terjual'] ?? null,
                'detail_produk_terjual_parsed'     => $detailParsed,
            ];
        }

        $tanggalList = collect($rows)
            ->pluck('waktu_pemesanan')
            ->filter()
            ->sort()
            ->values();

        return [
            'rows'          => $rows,
            'total_rows'    => count($rows),
            'periode_start' => $tanggalList->first(),
            'periode_end'   => $tanggalList->last(),
        ];
    }

    /**
     * Parse "123 * 1; 456 * 2;" jadi array terstruktur.
     */
    private function parseDetailProduk(string $detail): array
    {
        if (empty($detail) || $detail === '/') return [];

        $items = [];
        $parts = array_filter(array_map('trim', explode(';', $detail)));
        foreach ($parts as $part) {
            if (preg_match('/^(\d+)\s*\*\s*(\d+)$/', $part, $m)) {
                $items[] = [
                    'sku_id' => $m[1],
                    'qty'    => (int) $m[2],
                ];
            }
        }
        return $items;
    }

    // ============================================
    // HELPERS
    // ============================================
    private function toInt($value): int
    {
        if ($value === null || $value === '' || $value === '-') return 0;
        if (is_numeric($value)) return (int) $value;
        return (int) preg_replace('/[^0-9\-]/', '', (string) $value);
    }

    private function toDate($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;
        try {
            if (preg_match('#^\d{4}/\d{2}/\d{2}$#', $value)) {
                return Carbon::createFromFormat('Y/m/d', $value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function toDateTime($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;
        try {
            if (preg_match('#^\d{2}/\d{2}/\d{4}#', $value)) {
                return Carbon::createFromFormat('d/m/Y H:i:s', $value)->format('Y-m-d H:i:s');
            }
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function nullify($value): ?string
    {
        if ($value === null || $value === '' || $value === '-') return null;
        return $value;
    }
}