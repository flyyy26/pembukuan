<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shopee_returns')
            ->whereNull('waktu_pengajuan')
            ->select(['id', 'raw_data'])
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $rawData = json_decode($row->raw_data ?? '[]', true);
                    if (!is_array($rawData)) {
                        continue;
                    }

                    $dateValue = null;
                    foreach ([
                        'Waktu Pengembalian Diajukan',
                        'Waktu Pengajuan',
                        'Waktu Pembatalan',
                        'Tanggal Pembatalan',
                        'Tanggal Pengajuan',
                    ] as $key) {
                        if (!empty($rawData[$key]) && $rawData[$key] !== '-') {
                            $dateValue = $rawData[$key];
                            break;
                        }
                    }

                    if ($dateValue === null) {
                        continue;
                    }

                    try {
                        $date = Carbon::parse($dateValue)->toDateString();
                    } catch (Throwable $e) {
                        report($e);
                        continue;
                    }

                    DB::table('shopee_returns')
                        ->where('id', $row->id)
                        ->whereNull('waktu_pengajuan')
                        ->update(['waktu_pengajuan' => $date]);
                }
            });
    }

    public function down(): void
    {
        // Keep the recovered dates; raw_data remains the source of truth.
    }
};
