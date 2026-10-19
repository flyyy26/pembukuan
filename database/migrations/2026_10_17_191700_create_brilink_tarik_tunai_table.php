<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brilink_tarik_tunai', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brilink_report_id')
                  ->constrained('brilink_reports')
                  ->cascadeOnDelete();

            $table->enum('tipe', ['edc_bri', 'mbanking']);
            $table->bigInteger('nominal')->default(0);
            $table->bigInteger('admin_toko')->default(0);
            $table->bigInteger('kmc')->default(0);          // Kredit Merchant, hanya EDC
            $table->bigInteger('saldo_masuk')->default(0);  // hanya MBanking
            $table->string('keterangan_rek', 150)->nullable();

            $table->timestamps();

            $table->index('brilink_report_id');
            $table->index('tipe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brilink_tarik_tunai');
    }
};