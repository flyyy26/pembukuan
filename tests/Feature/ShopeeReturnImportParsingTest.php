<?php

namespace Tests\Feature;

use App\Http\Controllers\ImportController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ReflectionMethod;
use Tests\TestCase;

class ShopeeReturnImportParsingTest extends TestCase
{
    public function test_it_maps_shopee_return_report_headers_to_display_fields(): void
    {
        $headers = [
            'No. Pesanan',
            'No. Pengembalian',
            'Waktu Pengembalian Diajukan',
            'Status Pembatalan/ Pengembalian',
            'Solusi Pengembalian Barang/Dana',
            'Status Pengembalian Barang',
            'Total Pengembalian Dana',
            'Kode Variasi',
            'Jumlah Produk Dikembalikan',
        ];
        $values = [
            'ORDER-1',
            'RETURN-1',
            '2026-11-15',
            'Pengajuan Ditolak',
            'Pengembalian Dana',
            'Selesai',
            '61800',
            'SKU-1',
            '1',
        ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([$headers, $values], null, 'A1');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shopee-return-' . uniqid('', true) . '.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);

            $method = new ReflectionMethod(ImportController::class, 'parseShopeeReturnFile');
            $controller = new ImportController();
            $parsed = $method->invoke($controller, $path, 'Order.return_refund.xlsx');
            $row = $parsed['rows'][0];

            $this->assertSame('return', $row['tipe_laporan']);
            $this->assertSame('RETURN-1', $row['no_pengajuan']);
            $this->assertSame('Pengajuan Ditolak', $row['status_pengajuan']);
            $this->assertSame('Pengembalian Dana', $row['status_pengiriman_ke_pembeli']);
            $this->assertSame('Selesai', $row['status_pengembalian_ke_penjual']);
            $this->assertSame(61800, $row['nominal_pengembalian']);
            $this->assertSame(61800, $row['pengembalian_dana']);
            $this->assertSame('2026-11-15', $row['waktu_pengajuan']);

            $cancelled = $method->invoke($controller, $path, 'Order.cancelled.xlsx');
            $this->assertSame('cancel', $cancelled['rows'][0]['tipe_laporan']);
        } finally {
            $spreadsheet->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_it_maps_failed_delivery_status_and_refund_fields(): void
    {
        $headers = [
            'No. Pesanan',
            'Status pengiriman gagal',
            'Jumlah Pengembalian Dana ke Pembeli',
        ];
        $values = [
            'ORDER-FAILED-1',
            'Selesai Dikirim ke Penjual',
            '15000',
        ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([$headers, $values], null, 'A1');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shopee-failed-delivery-' . uniqid('', true) . '.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);

            $method = new ReflectionMethod(ImportController::class, 'parseShopeeReturnFile');
            $parsed = $method->invoke(new ImportController(), $path, 'Order.failed_delivery.xlsx');
            $row = $parsed['rows'][0];

            $this->assertSame('shipping_failed', $row['tipe_laporan']);
            $this->assertNull($row['status_pengiriman_ke_pembeli']);
            $this->assertSame('Selesai Dikirim ke Penjual', $row['status_pengembalian_ke_penjual']);
            $this->assertSame(15000, $row['nominal_pengembalian']);
            $this->assertSame(15000, $row['pengembalian_dana']);
        } finally {
            $spreadsheet->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
