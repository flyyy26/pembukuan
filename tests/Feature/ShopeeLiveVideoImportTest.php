<?php

namespace Tests\Feature;

use App\Http\Controllers\ImportController;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\ViewErrorBag;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ReflectionMethod;
use Tests\TestCase;

class ShopeeLiveVideoImportTest extends TestCase
{
    public function test_live_video_import_form_has_separate_required_files(): void
    {
        $store = new Store([
            'nama_toko' => 'Toko Shopee',
            'marketplace' => 'shopee',
        ]);
        $store->id = 6;

        $html = view('import.index', [
            'stores' => collect([$store]),
            'imports' => new LengthAwarePaginator(collect(), 0, 15),
            'errors' => new ViewErrorBag(),
        ])->render();

        $action = preg_quote(route('import.shopee.live_video'), '/');
        $this->assertSame(
            1,
            preg_match('/<form\b[^>]*action="' . $action . '"[^>]*>(.*?)<\/form>/s', $html, $matches)
        );

        $this->assertStringContainsString('name="live_file" required', $matches[1]);
        $this->assertStringContainsString('name="video_file" required', $matches[1]);
        $this->assertStringNotContainsString('name="file"', $matches[1]);
    }

    public function test_xlsx_files_with_generic_mime_type_pass_upload_validation(): void
    {
        $paths = [];
        $uploads = [];

        foreach (['live', 'video'] as $name) {
            $spreadsheet = new Spreadsheet();
            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shopee-upload-' . $name . '-' . uniqid('', true) . '.xlsx';
            $paths[] = $path;
            (new Xlsx($spreadsheet))->save($path);
            $spreadsheet->disconnectWorksheets();
            $uploads[$name . '_file'] = new UploadedFile($path, $name . '.xlsx', 'application/octet-stream', null, true);
        }

        try {
            $request = Request::create('/import/shopee-live-video', 'POST', ['store_id' => ''], [], $uploads);

            try {
                (new ImportController())->importShopeeLiveVideo($request);
                $this->fail('The missing store should fail validation.');
            } catch (ValidationException $exception) {
                $this->assertSame(['store_id'], array_keys($exception->errors()));
            }
        } finally {
            foreach ($paths as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_it_parses_live_sessions_with_sales_and_videos_with_engagement_metrics(): void
    {
        $method = new ReflectionMethod(ImportController::class, 'parseShopeeLiveVideoFile');
        $controller = new ImportController();

        $fixtures = [
            'Live' => [
                'headers' => [
                    'Periode Data', 'User Id', 'No.', 'Nama Livestream', 'Start Time', 'Durasi:',
                    'Penonton Aktif', 'Komentar', 'Tambah ke Keranjang', 'Rata-rata durasi ditonton',
                    'Penonton', 'Pesanan(Pesanan Dibuat)', 'Pesanan(Pesanan Siap Dikirim)',
                    'Produk Terjual(Pesanan Dibuat)', 'Produk Terjual(Pesanan Siap Dikirim)',
                    'Penjualan(Pesanan Dibuat)', 'Penjualan(Pesanan Siap Dikirim)',
                ],
                'row' => [
                    '01-10-2026 - 31-10-2026', '140284211', '1', 'Pakaian Olahraga', '30-10-2026 14:56',
                    '01:04:04', '1', '9', '2', '00:00:19', '33', '2', '1', '2', '1', 'Rp150.000', 'Rp103.400',
                ],
            ],
            'Video' => [
                'headers' => [
                    'Periode Data', 'No.', 'Judul Video', 'Date posted', 'Ditonton', 'Suka', 'Komentar',
                    'Durasi Rata-rata Menonton/Jumlah Video',
                ],
                'row' => [
                    '01-01-2026 - 31-01-2026', '1', 'Cek keranjang kuning', '01-01-2026 08:00',
                    '32', '5', '2', '00:00:08',
                ],
            ],
        ];

        foreach ($fixtures as $type => $fixture) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($fixture['headers'] as $column => $header) {
                $sheet->setCellValueByColumnAndRow($column + 1, 1, $header);
            }
            foreach ($fixture['row'] as $column => $value) {
                $sheet->setCellValueByColumnAndRow($column + 1, 2, $value);
            }
            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shopee-' . strtolower($type) . '-' . uniqid('', true) . '.xlsx';

            try {
                (new Xlsx($spreadsheet))->save($path);
                $parsed = $method->invoke($controller, $path, $type);
                $row = $parsed['rows'][0];

                $this->assertSame($type, $row['tipe']);
                $this->assertSame(1, $parsed['total_rows']);
                $this->assertSame($type === 'Live' ? '2026-10-30' : '2026-01-01', $row['tanggal']);
                $this->assertSame($type === 'Live' ? '2026-10-31' : '2026-01-31', $parsed['periode_end']);

                if ($type === 'Live') {
                    $this->assertSame('livestream', $row['metrics']['record_type']);
                    $this->assertSame('Pakaian Olahraga', $row['judul']);
                    $this->assertSame(33, $row['penonton']);
                    $this->assertSame(1, $row['total_pesanan']);
                    $this->assertSame(103400, $row['penjualan_idr']);
                    $this->assertSame(150000, $row['metrics']['sales_created']);
                    $this->assertSame('01:04:04', $row['metrics']['duration']);
                } else {
                    $this->assertSame('video', $row['metrics']['record_type']);
                    $this->assertSame('Cek keranjang kuning', $row['judul']);
                    $this->assertSame(32, $row['metrics']['views']);
                    $this->assertSame(5, $row['metrics']['likes']);
                    $this->assertSame(2, $row['metrics']['comments']);
                    $this->assertFalse($row['metrics']['sales_available']);
                }
            } finally {
                $spreadsheet->disconnectWorksheets();
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }
}
