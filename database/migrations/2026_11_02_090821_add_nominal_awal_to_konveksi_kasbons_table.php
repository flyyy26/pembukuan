<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konveksi_kasbons', function (Blueprint $table) {
            $table->bigInteger('nominal_awal')->default(0)->after('nominal');
        });

        $potonganPerKasbon = DB::query()
            ->fromSub(
                DB::table('hanca_pembayarans')
                    ->select('kasbon_id', 'created_at')
                    ->selectRaw('MAX(kasbon_dipotong) as nominal_dipotong')
                    ->whereNotNull('kasbon_id')
                    ->groupBy('kasbon_id', 'created_at'),
                'potongan_per_transaksi'
            )
            ->select('kasbon_id')
            ->selectRaw('SUM(nominal_dipotong) as total_dipotong')
            ->groupBy('kasbon_id')
            ->pluck('total_dipotong', 'kasbon_id');

        DB::table('konveksi_kasbons')->orderBy('id')->chunkById(100, function ($kasbons) use ($potonganPerKasbon) {
            foreach ($kasbons as $kasbon) {
                DB::table('konveksi_kasbons')
                    ->where('id', $kasbon->id)
                    ->update([
                        'nominal_awal' => $kasbon->nominal + ($potonganPerKasbon[$kasbon->id] ?? 0),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('konveksi_kasbons', function (Blueprint $table) {
            $table->dropColumn('nominal_awal');
        });
    }
};
