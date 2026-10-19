<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brilink_reports', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('store_id')
                  ->constrained('stores')
                  ->cascadeOnDelete();
            $table->foreignId('import_id')
                  ->constrained('imports')
                  ->cascadeOnDelete();

            // File info
            $table->string('file_name');
            $table->string('file_hash', 64)->unique();
            $table->date('periode_start')->nullable();
            $table->date('periode_end')->nullable();

            // Key metrics dari sheet DASHBOARD UTAMA
            $table->bigInteger('total_omset_ppob')->default(0);
            $table->bigInteger('total_tarik_tunai_edc')->default(0);
            $table->bigInteger('total_tarik_tunai_mbanking')->default(0);
            $table->bigInteger('total_setoran_brilink')->default(0);
            $table->bigInteger('total_pendapatan_admin')->default(0);
            $table->bigInteger('total_pengeluaran_riil')->default(0);
            $table->bigInteger('total_pindah_saldo')->default(0);
            $table->integer('volume_transaksi_agen')->default(0);

            // Saldo awal & akhir (agregat total aset likuid)
            $table->bigInteger('saldo_awal_total')->default(0);
            $table->bigInteger('saldo_akhir_total')->default(0);

            // Status import
            $table->string('status')->default('pending'); // pending | imported | parsed | failed
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Index untuk query cepat
            $table->index(['store_id', 'periode_start']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brilink_reports');
    }
};