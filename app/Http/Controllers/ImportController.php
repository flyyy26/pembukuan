<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ShopeeIncomeRow;
use App\Models\ShopeeOrderItem;
use App\Models\Store;
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
            // TAMBAHKAN FILTER INI: Hanya ambil tipe shopee
            ->whereIn('tipe', ['shopee_order', 'shopee_income']) 
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
        if (!in_array($import->tipe, ['shopee_order', 'shopee_income'])) {
            return response()->json([
                'success' => false,
                'message' => 'Data ini bukan bagian dari import Shopee.'
            ], 400);
        }

        $storeId = $import->store_id;

        DB::beginTransaction();
        try {
            // Hapus data turunan berdasarkan tipe import
            if ($import->tipe === 'shopee_order') {
                ShopeeOrderItem::where('import_id', $import->id)->delete();
            } elseif ($import->tipe === 'shopee_income') {
                ShopeeIncomeRow::where('import_id', $import->id)->delete();
            }

            // Hapus record import utama
            $import->delete();

            // Rekonsiliasi ulang untuk memperbarui tabel Sales
            if ($storeId) {
                \Artisan::call('shopee:reconcile', ['store_id' => $storeId]);
            }

            DB::commit();

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
}