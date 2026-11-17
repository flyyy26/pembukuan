<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ShopeeIncomeRow;
use App\Models\ShopeeOrderItem;
use App\Models\ShopeeReturn;
use App\Models\Store;
use App\Support\ImportedFileStorage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index()
    {
        $imports = Import::with('store')
            ->whereIn('tipe', ['shopee_order', 'shopee_income', 'shopee_return', 'shopee_chat', 'shopee_live_video']) 
            ->orderByDesc('id')
            ->paginate(15);

        $stores = Store::where('marketplace', 'shopee')
            ->where('is_active', true)
            ->orderBy('nama_toko')
            ->get();

        return view('import.index', compact('imports', 'stores'));
    }

    // ============================================
    //  IMPORT 1: Order.all Shopee (detail varian)
    // ============================================
    public function importShopeeOrder(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'file'     => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $store = Store::findOrFail($request->store_id);
        if ($store->marketplace !== 'shopee') {
            return back()->with('error', 'Toko ini bukan Shopee.');
        }

        $file = $request->file('file');
        $storedPath = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($storedPath);

        $fileHash = hash_file('sha256', $fullPath);

        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'shopee_order')
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', "File ini sudah pernah diimport (Import #{$existing->id}).");
        }

        try {
            $data = $this->parseShopeeOrderFile($fullPath);
        } catch (\Exception $e) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal parse file: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $import = Import::create([
                'store_id'      => $store->id,
                'tipe'          => 'shopee_order',
                'file_name'     => $file->getClientOriginalName(),
                'stored_path'   => $storedPath,
                'file_hash'     => $fileHash,
                'periode_start' => $data['periode_start'],
                'periode_end'   => $data['periode_end'],
                'total_rows'    => $data['total_rows'],
                'status'        => 'pending',
            ]);

            foreach ($data['rows'] as $row) {
                $row['import_id'] = $import->id;
                ShopeeOrderItem::create($row);
            }

            $import->update(['status' => 'imported']);
            DB::commit();

            \Artisan::call('shopee:reconcile', ['store_id' => $store->id]);

            return redirect()->route('import.index')
                ->with('success', "Berhasil import Order.all: {$data['total_rows']} baris dari {$file->getClientOriginalName()}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal simpan ke database: ' . $e->getMessage());
        }
    }

    private function parseShopeeOrderFile(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);

        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            if ($s->getTitle() === 'orders') {
                $sheet = $s;
                break;
            }
        }

        if (!$sheet) {
            throw new \Exception("Sheet 'orders' tidak ditemukan di file.");
        }

        $data = $sheet->toArray(null, true, true, false);
        if (count($data) < 2) {
            throw new \Exception("File terlalu pendek.");
        }

        // Baris 0 = header
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

            $rows[] = [
                'no_pesanan'                   => $assoc['No. Pesanan'] ?? null,
                'status_pesanan'               => $this->nullify($assoc['Status Pesanan'] ?? null),
                'status_pembatalan'            => $this->nullify($assoc['Status Pembatalan/ Pengembalian'] ?? null),
                'no_resi'                      => $this->nullify($assoc['No. Resi'] ?? null),
                'opsi_pengiriman'              => $this->nullify($assoc['Opsi Pengiriman'] ?? null),
                'waktu_pesanan_dibuat'         => $this->toDateTime($assoc['Waktu Pesanan Dibuat'] ?? null),
                'waktu_pembayaran'             => $this->toDateTime($assoc['Waktu Pembayaran Dilakukan'] ?? null),
                'waktu_pesanan_selesai'        => $this->toDateTime($assoc['Waktu Pesanan Selesai'] ?? null),
                'tipe_pesanan'                 => $this->nullify($assoc['Tipe Pesanan'] ?? null),
                'metode_pembayaran'            => $this->nullify($assoc['Metode Pembayaran'] ?? null),
                'sku_induk'                    => $this->nullify($assoc['SKU Induk'] ?? null),
                'nama_produk'                  => $this->nullify($assoc['Nama Produk'] ?? null),
                'sku_varian'                   => $this->nullify($assoc['Nomor Referensi SKU'] ?? null),
                'nama_variasi'                 => $this->nullify($assoc['Nama Variasi'] ?? null),
                'harga_awal'                   => $this->toInt($assoc['Harga Awal'] ?? 0),
                'harga_setelah_diskon'         => $this->toInt($assoc['Harga Setelah Diskon'] ?? 0),
                'jumlah'                       => $this->toInt($assoc['Jumlah'] ?? 0),
                'returned_quantity'            => $this->toInt($assoc['Returned quantity'] ?? 0),
                'subtotal_pesanan'             => $this->toInt($assoc['Subtotal Pesanan'] ?? 0),
                'total_diskon'                 => $this->toInt($assoc['Total Diskon'] ?? 0),
                'diskon_dari_penjual'          => $this->toInt($assoc['Diskon Dari Penjual'] ?? 0),
                'diskon_dari_shopee'           => $this->toInt($assoc['Diskon Dari Shopee'] ?? 0),
                'ongkos_kirim_dibayar_pembeli' => $this->toInt($assoc['Ongkos Kirim Dibayar oleh Pembeli'] ?? 0),
                'total_pembayaran'             => $this->toInt($assoc['Total Pembayaran'] ?? 0),
            ];
        }

        $tanggalList = collect($rows)
            ->pluck('waktu_pesanan_dibuat')
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

    public function importShopeeBoth(Request $request)
    {
        $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'order_file'  => 'required|file|mimes:xlsx,xls|max:10240',
            'income_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $store = Store::findOrFail($request->store_id);
        if ($store->marketplace !== 'shopee') {
            return back()->with('error', 'Toko ini bukan Shopee.');
        }

        // ==================================================
        // PROSES FILE 1: Order.all
        // ==================================================
        $orderFile = $request->file('order_file');
        $orderStoredPath = $orderFile->store('imports', 'local');
        $orderFullPath = Storage::disk('local')->path($orderStoredPath);
        $orderHash = hash_file('sha256', $orderFullPath);

        $existingOrder = Import::where('file_hash', $orderHash)
            ->where('tipe', 'shopee_order')
            ->first();

        if ($existingOrder) {
            Storage::disk('local')->delete($orderStoredPath);
            // Skip file ini, lanjut ke income
            $orderSkipped = true;
            $orderRows = 0;
        } else {
            try {
                $orderData = $this->parseShopeeOrderFile($orderFullPath);
            } catch (\Exception $e) {
                Storage::disk('local')->delete($orderStoredPath);
                return back()->with('error', 'Gagal parse Order.all: ' . $e->getMessage());
            }

            DB::beginTransaction();
            try {
                $orderImport = Import::create([
                    'store_id'      => $store->id,
                    'tipe'          => 'shopee_order',
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
                    ShopeeOrderItem::create($row);
                }

                $orderImport->update(['status' => 'imported']);
                DB::commit();
                $orderSkipped = false;
                $orderRows = $orderData['total_rows'];
            } catch (\Exception $e) {
                DB::rollBack();
                Storage::disk('local')->delete($orderStoredPath);
                return back()->with('error', 'Gagal simpan Order.all ke database: ' . $e->getMessage());
            }
        }

        // ==================================================
        // PROSES FILE 2: Income
        // ==================================================
        $incomeFile = $request->file('income_file');
        $incomeStoredPath = $incomeFile->store('imports', 'local');
        $incomeFullPath = Storage::disk('local')->path($incomeStoredPath);
        $incomeHash = hash_file('sha256', $incomeFullPath);

        $existingIncome = Import::where('file_hash', $incomeHash)
            ->where('tipe', 'shopee_income')
            ->first();

        if ($existingIncome) {
            Storage::disk('local')->delete($incomeStoredPath);
            $incomeSkipped = true;
            $incomeRows = 0;
        } else {
            try {
                $incomeData = $this->parseShopeeFile($incomeFullPath);
            } catch (\Exception $e) {
                Storage::disk('local')->delete($incomeStoredPath);
                return back()->with('error', 'Gagal parse Income: ' . $e->getMessage());
            }

            DB::beginTransaction();
            try {
                $incomeImport = Import::create([
                    'store_id'      => $store->id,
                    'tipe'          => 'shopee_income',
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
                    ShopeeIncomeRow::create($row);
                }

                $incomeImport->update(['status' => 'imported']);
                DB::commit();
                $incomeSkipped = false;
                $incomeRows = $incomeData['total_rows'];
            } catch (\Exception $e) {
                DB::rollBack();
                Storage::disk('local')->delete($incomeStoredPath);
                return back()->with('error', 'Gagal simpan Income ke database: ' . $e->getMessage());
            }
        }

        // ==================================================
        // AUTO RECONCILE
        // ==================================================
        \Artisan::call('shopee:reconcile', ['store_id' => $store->id]);

        // ==================================================
        // FLASH MESSAGE
        // ==================================================
        $messages = [];

        if ($orderSkipped) {
            $messages[] = "Order.all: sudah pernah diimport (skip)";
        } else {
            $messages[] = "Order.all: {$orderRows} baris berhasil";
        }

        if ($incomeSkipped) {
            $messages[] = "Income: sudah pernah diimport (skip)";
        } else {
            $messages[] = "Income: {$incomeRows} baris berhasil";
        }

        return redirect()->route('import.index')
            ->with('success', 'Import selesai. ' . implode('. ', $messages) . '. Rekonsiliasi otomatis dijalankan.');
    }

    // ============================================
    //  IMPORT 2: Income Shopee (settlement)
    // ============================================
    public function importShopee(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'file'     => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $store = Store::findOrFail($request->store_id);
        if ($store->marketplace !== 'shopee') {
            return back()->with('error', 'Toko ini bukan Shopee.');
        }

        $file = $request->file('file');
        $storedPath = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($storedPath);

        $fileHash = hash_file('sha256', $fullPath);

        $existing = Import::where('file_hash', $fileHash)
            ->where('tipe', 'shopee_income')
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', "File ini sudah pernah diimport (Import #{$existing->id}).");
        }

        try {
            $data = $this->parseShopeeFile($fullPath);
        } catch (\Exception $e) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal parse file: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $import = Import::create([
                'store_id'      => $store->id,
                'tipe'          => 'shopee_income',
                'file_name'     => $file->getClientOriginalName(),
                'stored_path'   => $storedPath,
                'file_hash'     => $fileHash,
                'periode_start' => $data['periode_start'],
                'periode_end'   => $data['periode_end'],
                'total_rows'    => $data['total_rows'],
                'status'        => 'pending',
            ]);

            foreach ($data['rows'] as $row) {
                $row['import_id'] = $import->id;
                ShopeeIncomeRow::create($row);
            }

            $import->update(['status' => 'imported']);
            DB::commit();

            \Artisan::call('shopee:reconcile', ['store_id' => $store->id]);

            return redirect()->route('import.index')
                ->with('success', "Berhasil import Income: {$data['total_rows']} baris dari {$file->getClientOriginalName()}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal simpan ke database: ' . $e->getMessage());
        }
    }

    private function parseShopeeFile(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);

        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            if ($s->getTitle() === 'Penghasilan') {
                $sheet = $s;
                break;
            }
        }

        if (!$sheet) {
            throw new \Exception("Sheet 'Penghasilan' tidak ditemukan di file.");
        }

        $data = $sheet->toArray(null, true, true, false);
        if (count($data) < 3) {
            throw new \Exception("File terlalu pendek.");
        }

        $headers = array_map('trim', $data[2]);

        $rows = [];
        for ($i = 3; $i < count($data); $i++) {
            $row = $data[$i];
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') continue;
                $value = $row[$index] ?? null;
                $assoc[$header] = is_string($value) ? trim($value) : $value;
            }

            $tipe = strtolower($assoc['Lihat berdasarkan'] ?? '');
            if (!in_array($tipe, ['order', 'sku'])) continue;

            $rows[] = [
                'tipe_baris'              => $tipe,
                'no_pesanan'              => $assoc['No. Pesanan'] ?? null,
                'no_pengajuan'            => $this->nullify($assoc['No. Pengajuan'] ?? null),
                'id_produk'               => $this->nullify($assoc['ID Produk'] ?? null),
                'nama_produk'             => $this->nullify($assoc['Nama Produk'] ?? null),
                'waktu_pesanan_dibuat'    => $this->toDate($assoc['Waktu Pesanan Dibuat'] ?? null),
                'tanggal_dana_dilepaskan' => $this->toDate($assoc['Tanggal Dana Dilepaskan'] ?? null),
                'tipe_pesanan'            => $this->nullify($assoc['Tipe Pesanan'] ?? null),
                'total_penghasilan'       => $this->toInt($assoc['Total Penghasilan'] ?? 0),
                'harga_produk'            => $this->toInt($assoc['Harga Produk'] ?? 0),

                // ===== BIAYA PLATFORM =====
                'biaya_administrasi'            => $this->toInt($assoc['Biaya Administrasi'] ?? 0),
                'biaya_proses_pesanan'          => $this->toInt($assoc['Biaya Proses Pesanan'] ?? 0),
                'biaya_gratis_ongkir_xtra'      => $this->toInt($assoc['Biaya Gratis Ongkir XTRA - Ukuran Biasa (Kategori H)'] ?? 0),
                'biaya_transaksi'               => $this->toInt($assoc['Biaya Transaksi'] ?? 0),
                'biaya_komisi_ams'              => $this->toInt($assoc['Biaya Komisi AMS'] ?? 0),
                'pph_22'                        => $this->toInt($assoc['PPh 22'] ?? 0),

                // ===== BIAYA PROMOSI (sebelumnya tidak dipetakan) =====
                'biaya_kampanye'                => $this->toInt($assoc['Biaya Kampanye'] ?? 0),
                'biaya_isi_saldo_otomatis'      => $this->toInt($assoc['Biaya Isi Saldo Otomatis (dari Penghasilan)'] ?? 0),
                'premi'                         => $this->toInt($assoc['Premi'] ?? 0),
                'fbs_fee'                       => $this->toInt($assoc['FBS Fee'] ?? 0),

                // ===== VOUCHER & CASHBACK PENJUAL =====
                'voucher_disponsor_penjual'                => $this->toInt($assoc['Voucher disponsor oleh Penjual'] ?? 0),
                'cashback_koin_disponsori_penjual'         => $this->toInt($assoc['Cashback Koin disponsori Penjual'] ?? 0),
                'diskon_produk_dari_shopee'                => $this->toInt($assoc['Diskon Produk dari Shopee'] ?? 0),
                'voucher_cofund_disponsor_penjual'         => $this->toInt($assoc['Voucher co-fund disponsor oleh Penjual'] ?? 0),
                'cashback_koin_cofund_disponsori_penjual'  => $this->toInt($assoc['Cashback Koin Co-fund disponsori Penjual'] ?? 0),

                // ===== ONGKIR =====
                'ongkos_kirim_dibayarkan'                  => $this->toInt($assoc['Ongkos Kirim yang Dibayarkan ke Jasa Kirim'] ?? 0),
                'gratis_ongkir_dari_shopee'                => $this->toInt($assoc['Gratis Ongkir dari Shopee'] ?? 0),
                'ongkos_kirim_pengembalian_barang'         => $this->toInt($assoc['Ongkos Kirim Pengembalian Barang'] ?? 0),
                'return_to_seller_fee'                     => $this->toInt($assoc['Return to Seller Fee'] ?? 0),
                'pengembalian_biaya_kirim'                 => $this->toInt($assoc['Pengembalian Biaya Kirim'] ?? 0),
                'potongan_ongkos_kirim_dari_jasa_kirim'    => $this->toInt($assoc['Potongan Ongkos Kirim dari Jasa Kirim'] ?? 0),

                // ===== REFUND =====
                'jumlah_pengembalian_dana'      => $this->toInt($assoc['Jumlah Pengembalian Dana ke Pembeli'] ?? 0),

                'raw_data'                      => $assoc,
            ];
        }

        $tanggalList = collect($rows)
            ->where('tipe_baris', 'order')
            ->pluck('tanggal_dana_dilepaskan')
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

    public function destroy($id)
    {
        // Cari berdasarkan ID saja, tanpa filter tipe dulu
        $import = Import::find($id);

        // Jika data tidak ditemukan sama sekali
        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Data import tidak ditemukan. Mungkin sudah dihapus sebelumnya.'
            ], 404);
        }

        // Cek apakah ini benar-benar import Shopee
        if (!in_array($import->tipe, ['shopee_order', 'shopee_income', 'shopee_return', 'shopee_live_video'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Data ini bukan bagian dari import Shopee.'
            ], 400);
        }

        $storeId = $import->store_id;
        $storedPath = $import->stored_path;
        $fileHash = $import->file_hash;

        DB::beginTransaction();
        try {
            // Hapus data turunan berdasarkan tipe import
            if ($import->tipe === 'shopee_order') {
                ShopeeOrderItem::where('import_id', $import->id)->delete();
            } elseif ($import->tipe === 'shopee_income') {
                ShopeeIncomeRow::where('import_id', $import->id)->delete();
            } elseif ($import->tipe === 'shopee_return') {
                ShopeeReturn::where('import_id', $import->id)->delete();
            } elseif ($import->tipe === 'shopee_live_video') {
                \App\Models\ShopeeLiveVideo::where('import_id', $import->id)->delete();
            }

            // Hapus record import utama
            $import->delete();

            // Rekonsiliasi ulang untuk memperbarui tabel Sales
            if ($storeId && in_array($import->tipe, ['shopee_order', 'shopee_income'], true)) {
                \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);
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

    // ============================================
    //  Helpers
    // ============================================
    private function toInt($value): int
    {
        if ($value === null || $value === '' || $value === '-') return 0;
        return (int) preg_replace('/[^0-9\-]/', '', (string) $value);
    }

    private function toDate($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function toDateTime($value): ?string
    {
        if (!$value || $value === '' || $value === '-') return null;
        try {
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

    public function importShopeeReturn(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'file'     => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $store = Store::findOrFail($request->store_id);
        if ($store->marketplace !== 'shopee') {
            return back()->with('error', 'Toko ini bukan Shopee.');
        }

        $file = $request->file('file');
        $storedPath = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($storedPath);
        $fileHash = hash_file('sha256', $fullPath);

        $existing = Import::where('store_id', $store->id)
            ->where('tipe', 'shopee_return')
            ->where('file_hash', $fileHash)
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', "File ini sudah pernah diimport (Import #{$existing->id}).");
        }

        try {
            $data = $this->parseShopeeReturnFile($fullPath, $file->getClientOriginalName());
        } catch (\Exception $e) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal parse file: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $import = Import::create([
                'store_id'      => $store->id,
                'tipe'          => 'shopee_return',
                'file_name'     => $file->getClientOriginalName(),
                'stored_path'   => $storedPath,
                'file_hash'     => $fileHash,
                'periode_start' => $data['periode_start'],
                'periode_end'   => $data['periode_end'],
                'total_rows'    => $data['total_rows'],
                'status'        => 'pending',
            ]);

            foreach ($data['rows'] as $row) {
                $row['import_id'] = $import->id;
                ShopeeReturn::create($row);
            }

            $import->update(['status' => 'imported']);
            DB::commit();

            return redirect()->route('import.index')
                ->with('success', "Berhasil import data Pengembalian/Pembatalan: {$data['total_rows']} baris.");
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal simpan ke database: ' . $e->getMessage());
        }
    }

    private function parseShopeeReturnFile(string $fullPath, ?string $fileName = null): array
    {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $reportName = strtolower(($fileName ?? '') . ' ' . $sheet->getTitle());
        $tipeLaporan = str_contains($reportName, 'failed_delivery') || str_contains($reportName, 'failed delivery')
            ? 'shipping_failed'
            : ((str_contains($reportName, 'cancel') || str_contains($reportName, 'batal')) ? 'cancel' : 'return');
        $data = $sheet->toArray(null, true, true, false);

        // Cari baris header secara otomatis
        $headerRowIndex = -1;
        foreach ($data as $index => $row) {
            $rowString = implode(' | ', array_filter($row, fn($v) => $v !== null));
            if (stripos($rowString, 'No. Pesanan') !== false || stripos($rowString, 'No. Pengajuan') !== false) {
                $headerRowIndex = $index;
                break;
            }
        }

        if ($headerRowIndex === -1) {
            throw new \Exception("Format file tidak dikenali. Tidak menemukan kolom 'No. Pesanan' atau 'No. Pengajuan'.");
        }

        $headers = array_map('trim', $data[$headerRowIndex]);
        $rows = [];

        for ($i = $headerRowIndex + 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $assoc = [];
            foreach ($headers as $idx => $header) {
                if ($header === '') continue;
                $assoc[$header] = trim($row[$idx] ?? '');
            }

            // Helper untuk mencari nilai dari nama kolom yang mirip
            $getValue = function($possibleNames) use ($assoc) {
                foreach ($possibleNames as $name) {
                    foreach ($assoc as $key => $val) {
                        if (stripos($key, $name) !== false) return $val;
                    }
                }
                return null;
            };

            $noPesanan = $getValue(['No. Pesanan']);
            if ($noPesanan === null || trim($noPesanan) === '') {
                continue;
            }

            $statusPengirimanGagal = $getValue(['Status pengiriman gagal']);
            $statusPengirimanPembeli = $getValue(['Solusi Pengembalian Barang/Dana', 'Status Pengiriman ke Pembeli']);
            if ($statusPengirimanPembeli === null && $statusPengirimanGagal === null) {
                $statusPengirimanPembeli = $getValue(['Status Pengiriman']);
            }

            $rows[] = [
                'tipe_laporan'         => $tipeLaporan,
                'no_pesanan'           => $getValue(['No. Pesanan']),
                'no_pengajuan'         => $getValue(['No. Pengembalian', 'No. Pengajuan']),
                'status_pengajuan'     => $getValue(['Status Pembatalan/ Pengembalian', 'Status Pengajuan', 'Status']),
                'alasan_pengajuan'     => $getValue(['Alasan Pengajuan', 'Alasan']),
                'sku_varian'           => $getValue(['Kode Variasi', 'Nomor Referensi SKU', 'Variasi', 'SKU']),
                'nama_produk'          => $getValue(['Nama Produk']),
                'jumlah'               => (int) preg_replace('/[^0-9]/', '', $getValue(['Jumlah Produk Dikembalikan', 'Jumlah', 'Qty']) ?? 0),
                'nominal_pengembalian' => (int) preg_replace('/[^0-9]/', '', $getValue([
                    'Total Pengembalian Dana',
                    'Jumlah Pengembalian Dana ke Pembeli',
                    'Jumlah Pengembalian Dana',
                    'Dana Kembali',
                    'Nominal',
                ]) ?? 0),
                'waktu_pengajuan'      => $this->toDate($getValue([
                    'Waktu Pengembalian Diajukan',
                    'Waktu Pengajuan',
                    'Waktu Pembatalan', 
                    'Tanggal Pembatalan', 
                    'Waktu Selesai', 
                    'Waktu Pesanan Dibuat',
                    'Tanggal Pengajuan'
                ])),
                'status_pengiriman_ke_pembeli'   => $statusPengirimanPembeli,
                'status_pengembalian_ke_penjual' => $getValue([
                    'Status Pengembalian Barang',
                    'Status Pengembalian ke Penjual',
                    'Status Barang',
                ]) ?: $statusPengirimanGagal,
                'pengembalian_dana'              => (int) preg_replace('/[^0-9]/', '', $getValue([
                    'Total Pengembalian Dana',
                    'Jumlah Pengembalian Dana ke Pembeli',
                    'Jumlah Pengembalian Dana',
                    'Pengembalian Dana',
                    'Dana Kembali',
                ]) ?? 0),

                'raw_data'             => $assoc,
            ];
        }

        if (count($rows) === 0) {
            throw new \Exception("Tidak ada baris pengembalian/pembatalan dengan nomor pesanan.");
        }

        $tanggalList = collect($rows)
            ->pluck('waktu_pengajuan')
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

    public function importShopeeChat(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'file'     => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $store = Store::findOrFail($request->store_id);
        
        $file = $request->file('file');
        $storedPath = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($storedPath);
        $fileHash = hash_file('sha256', $fullPath);

        $existing = Import::where('store_id', $store->id)
            ->where('tipe', 'shopee_chat')
            ->where('file_hash', $fileHash)
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', "File ini sudah pernah diimport.");
        }

        try {
            $data = $this->parseShopeeChatFile($fullPath);
        } catch (\Exception $e) {
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal parse file: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $import = Import::create([
                'store_id'      => $store->id,
                'tipe'          => 'shopee_chat',
                'file_name'     => $file->getClientOriginalName(),
                'stored_path'   => $storedPath,
                'file_hash'     => $fileHash,
                'total_rows'    => $data['total_rows'],
                'status'        => 'imported',
            ]);

            foreach ($data['rows'] as $row) {
                $row['import_id'] = $import->id;
                \App\Models\ShopeeChat::create($row);
            }

            DB::commit();
            return back()->with('success', "Berhasil import data Chat: {$data['total_rows']} baris.");
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'Gagal simpan ke database: ' . $e->getMessage());
        }
    }

    private function parseShopeeChatFile(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, false);

        // Cari baris header
        $headerRowIndex = -1;
        foreach ($data as $index => $row) {
            $rowString = implode(' | ', array_filter($row, fn($v) => $v !== null));
            if (stripos($rowString, 'Pengunjung') !== false && stripos($rowString, 'Jumlah Chat') !== false) {
                $headerRowIndex = $index;
                break;
            }
        }

        if ($headerRowIndex === -1) throw new \Exception("Format file Chat tidak dikenali.");

        $headers = array_map('trim', $data[$headerRowIndex]);
        $rows = [];

        // Helper konversi persen "15,5%" ke decimal 15.50
        $parsePercent = function($val) {
            if (!$val) return 0;
            $str = str_replace('%', '', (string) $val);
            $str = trim($str);
            
            // Jika pakai koma sebagai desimal (misal 90,18)
            if (strpos($str, ',') !== false) {
                $str = str_replace('.', '', $str); // Hapus titik ribuan jika ada
                $str = str_replace(',', '.', $str); // Ubah koma jadi titik desimal
            }
            
            return (float) $str;
        };

        for ($i = $headerRowIndex + 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $assoc = [];
            foreach ($headers as $idx => $header) {
                if ($header === '') continue;
                $assoc[$header] = trim($row[$idx] ?? '');
            }

            $getValue = function($possibleNames) use ($assoc) {
                foreach ($possibleNames as $name) {
                    foreach ($assoc as $key => $val) {
                        if (stripos($key, $name) !== false) return $val;
                    }
                }
                return null;
            };

            $rows[] = [
                'tanggal'                  => $this->toDate($getValue(['Tanggal', 'Date'])),
                'pengunjung'               => (int) preg_replace('/[^0-9]/', '', $getValue(['Pengunjung']) ?? 0),
                'jumlah_chat'              => (int) preg_replace('/[^0-9]/', '', $getValue(['Jumlah Chat']) ?? 0),
                'pengunjung_bertanya'      => (int) preg_replace('/[^0-9]/', '', $getValue(['Pengunjung Bertanya']) ?? 0),
                'pertanyaan_diajukan'      => (int) preg_replace('/[^0-9]/', '', $getValue(['Pertanyaan Diajukan']) ?? 0),
                'chat_dibalas'             => (int) preg_replace('/[^0-9]/', '', $getValue(['Chat Dibalas']) ?? 0),
                'chat_belum_dibalas'       => (int) preg_replace('/[^0-9]/', '', $getValue(['Chat Belum Dibalas']) ?? 0),
                'waktu_respon_rata_rata'   => $getValue(['Waktu Respon Rata-rata']),
                'csat_persen'              => $parsePercent($getValue(['CSAT %', 'CSAT'])),
                'waktu_respon_pertama'     => $getValue(['Waktu Respon Chat Pertama Kali']),
                'konversi_chat_direspon'   => $parsePercent($getValue(['Tingkat Konversi (Jumlah Chat yang Direspon)'])),
                'total_pembeli'            => (int) preg_replace('/[^0-9]/', '', $getValue(['Total Pembeli']) ?? 0),
                'total_pesanan'            => (int) preg_replace('/[^0-9]/', '', $getValue(['Total Pesanan']) ?? 0),
                'produk'                   => $getValue(['Produk']),
                'penjualan_idr'            => (float) preg_replace('/[^0-9]/', '', $getValue(['Penjualan (IDR)', 'Penjualan']) ?? 0),
                'konversi_chat_dibalas'    => $parsePercent($getValue(['Tingkat Konversi (Chat Dibalas)'])),
            ];
        }

        return ['rows' => $rows, 'total_rows' => count($rows)];
    }

    public function importShopeeLiveVideo(Request $request)
    {
        $fileRules = [
            'required',
            'file',
            'extensions:csv,txt,xlsx,xls',
            'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,application/zip,application/x-zip-compressed',
            'max:10240',
        ];

        $request->validate([
            'store_id'   => 'required|exists:stores,id',
            'live_file'  => $fileRules,
            'video_file' => $fileRules,
        ]);

        $store = Store::findOrFail($request->store_id);

        $files = [
            'Live' => $request->file('live_file'),
            'Video' => $request->file('video_file'),
        ];
        $prepared = [];
        $seenHashes = [];

        foreach ($files as $type => $file) {
            $fileHash = hash_file('sha256', $file->getRealPath());
            if (isset($seenHashes[$fileHash])) {
                return back()->with('error', 'File Live dan Video harus berbeda.');
            }
            $seenHashes[$fileHash] = true;

            $existing = Import::where('store_id', $store->id)
                ->where('tipe', 'shopee_live_video')
                ->where('file_hash', $fileHash)
                ->first();
            if ($existing) {
                return back()->with('error', "File {$type} ini sudah pernah diimport (Import #{$existing->id}).");
            }

            try {
                $data = $this->parseShopeeLiveVideoFile($file->getRealPath(), $type);
            } catch (\Exception $e) {
                return back()->with('error', "Gagal parse file {$type}: " . $e->getMessage());
            }

            $prepared[] = compact('type', 'file', 'fileHash', 'data');
        }

        DB::beginTransaction();
        $storedPaths = [];
        try {
            foreach ($prepared as $item) {
                $storedPath = $item['file']->store('imports', 'local');
                if (!$storedPath) {
                    throw new \RuntimeException("Gagal menyimpan file {$item['type']}.");
                }
                $storedPaths[] = $storedPath;

                $import = Import::create([
                    'store_id'      => $store->id,
                    'tipe'          => 'shopee_live_video',
                    'file_name'     => $item['file']->getClientOriginalName(),
                    'stored_path'   => $storedPath,
                    'file_hash'     => $item['fileHash'],
                    'periode_start' => $item['data']['periode_start'],
                    'periode_end'   => $item['data']['periode_end'],
                    'total_rows'    => $item['data']['total_rows'],
                    'status'        => 'imported',
                ]);

                foreach ($item['data']['rows'] as $row) {
                    $row['import_id'] = $import->id;
                    \App\Models\ShopeeLiveVideo::create($row);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('local')->delete($storedPaths);
            return back()->with('error', 'Gagal simpan ke database: ' . $e->getMessage());
        }

        $liveRows = $prepared[0]['data']['total_rows'];
        $videoRows = $prepared[1]['data']['total_rows'];

        return back()->with('success', "Berhasil import Live ({$liveRows} baris) dan Video ({$videoRows} baris).");
    }

    private function parseShopeeLiveVideoFile(string $fullPath, string $type): array
    {
        $spreadsheet = IOFactory::load($fullPath);
        try {
            $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        if (!in_array($type, ['Live', 'Video'], true)) {
            throw new \RuntimeException('Jenis laporan Live/Video tidak valid.');
        }

        $headers = $data[0] ?? [];
        $headerIndexes = [];
        foreach ($headers as $index => $header) {
            $headerIndexes[mb_strtolower(trim((string) $header))] = $index;
        }

        $findColumn = function (array $names, bool $required = true) use ($headerIndexes): ?int {
            foreach ($names as $name) {
                $key = mb_strtolower(trim($name));
                if (array_key_exists($key, $headerIndexes)) {
                    return $headerIndexes[$key];
                }
            }

            if ($required) {
                throw new \RuntimeException('Kolom "' . $names[0] . '" tidak ditemukan di file Excel.');
            }

            return null;
        };

        $periodColumn = $findColumn(['Periode Data']);
        $numberColumn = $findColumn(['No.'], false);
        $titleColumn = $findColumn([$type === 'Live' ? 'Nama Livestream' : 'Judul Video']);
        $dateColumn = $findColumn([$type === 'Live' ? 'Start Time' : 'Date posted']);
        $viewsColumn = $findColumn([$type === 'Live' ? 'Penonton' : 'Ditonton']);

        $periodText = '';
        foreach (array_slice($data, 1) as $row) {
            $periodText = trim((string) ($row[$periodColumn] ?? ''));
            if ($periodText !== '') {
                break;
            }
        }

        preg_match_all('/\d{1,2}[-\/]\d{1,2}[-\/]\d{4}/', $periodText, $periodMatches);
        if (count($periodMatches[0]) < 2) {
            throw new \RuntimeException('Rentang Periode Data tidak ditemukan di file Excel.');
        }

        try {
            $periodStart = Carbon::createFromFormat('d-m-Y', str_replace('/', '-', $periodMatches[0][0]))->format('Y-m-d');
            $periodEnd = Carbon::createFromFormat('d-m-Y', str_replace('/', '-', $periodMatches[0][1]))->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \RuntimeException('Format tanggal Periode Data harus dd-mm-yyyy.', 0, $e);
        }

        $columns = [];
        if ($type === 'Live') {
            $columns = [
                'duration' => $findColumn(['Durasi:']),
                'active_viewers' => $findColumn(['Penonton Aktif']),
                'comments' => $findColumn(['Komentar']),
                'add_to_cart' => $findColumn(['Tambah ke Keranjang']),
                'average_watch_duration' => $findColumn(['Rata-rata durasi ditonton']),
                'orders_created' => $findColumn(['Pesanan(Pesanan Dibuat)']),
                'orders_ready_to_ship' => $findColumn(['Pesanan(Pesanan Siap Dikirim)']),
                'products_sold_created' => $findColumn(['Produk Terjual(Pesanan Dibuat)']),
                'products_sold_ready_to_ship' => $findColumn(['Produk Terjual(Pesanan Siap Dikirim)']),
                'sales_created' => $findColumn(['Penjualan(Pesanan Dibuat)']),
                'sales_ready_to_ship' => $findColumn(['Penjualan(Pesanan Siap Dikirim)']),
            ];
        } else {
            $columns = [
                'likes' => $findColumn(['Suka']),
                'comments' => $findColumn(['Komentar']),
                'average_watch_duration' => $findColumn(['Durasi Rata-rata Menonton/Jumlah Video']),
            ];
        }

        $rows = [];
        foreach (array_slice($data, 1) as $index => $record) {
            $title = trim((string) ($record[$titleColumn] ?? ''));
            $dateText = trim((string) ($record[$dateColumn] ?? ''));
            if ($title === '' && $dateText === '') {
                continue;
            }
            if ($title === '' || $dateText === '') {
                throw new \RuntimeException('Judul atau tanggal konten kosong pada baris ' . ($index + 2) . '.');
            }

            try {
                $contentDate = Carbon::createFromFormat('!d-m-Y H:i', $dateText);
            } catch (\Exception $e) {
                throw new \RuntimeException('Format tanggal konten tidak valid pada baris ' . ($index + 2) . '.', 0, $e);
            }

            $valueAt = static fn (?int $column) => $column === null ? '' : trim((string) ($record[$column] ?? ''));
            $numberAt = fn (?int $column) => $column === null ? 0 : $this->toInt($record[$column] ?? 0);
            $views = $numberAt($viewsColumn);
            $metrics = [
                'record_type' => $type === 'Live' ? 'livestream' : 'video',
                'period' => $periodText,
                'number' => $numberAt($numberColumn),
                'published_at' => $contentDate->format('Y-m-d H:i:s'),
                'views' => $views,
            ];

            if ($type === 'Live') {
                foreach ([
                    'active_viewers',
                    'comments',
                    'add_to_cart',
                    'orders_created',
                    'orders_ready_to_ship',
                    'products_sold_created',
                    'products_sold_ready_to_ship',
                    'sales_created',
                    'sales_ready_to_ship',
                ] as $key) {
                    $metrics[$key] = $numberAt($columns[$key] ?? null);
                }
                $metrics['duration'] = $valueAt($columns['duration']);
                $metrics['average_watch_duration'] = $valueAt($columns['average_watch_duration']);
                $metrics['sales_available'] = true;
                $totalOrders = $metrics['orders_ready_to_ship'];
                $productsSold = $metrics['products_sold_ready_to_ship'];
                $sales = $metrics['sales_ready_to_ship'];
            } else {
                $metrics['likes'] = $numberAt($columns['likes']);
                $metrics['comments'] = $numberAt($columns['comments']);
                $metrics['average_watch_duration'] = $valueAt($columns['average_watch_duration']);
                $metrics['sales_available'] = false;
                $totalOrders = 0;
                $productsSold = 0;
                $sales = 0;
            }

            $rows[] = [
                'tanggal' => $contentDate->format('Y-m-d'),
                'judul' => $title,
                'tipe' => $type,
                'penonton' => $views,
                'produk_terjual' => $productsSold,
                'total_pesanan' => $totalOrders,
                'penjualan_idr' => $sales,
                'produk' => null,
                'metrics' => $metrics,
            ];
        }

        if ($rows === []) {
            throw new \RuntimeException('Daftar konten tidak ditemukan di file Excel.');
        }

        return [
            'rows'          => $rows,
            'total_rows'    => count($rows),
            'periode_start' => $periodStart,
            'periode_end'   => $periodEnd,
        ];
    }
}