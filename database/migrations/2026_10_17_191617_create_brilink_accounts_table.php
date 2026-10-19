<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brilink_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brilink_report_id')
                  ->constrained('brilink_reports')
                  ->cascadeOnDelete();

            $table->string('nama_rekening', 150);       // "KAS", "BCA", "saldo 008 (BRI)"
            $table->string('keterangan', 100)->nullable(); // "PULSA", "BRILINK", "MBANKING"

            $table->bigInteger('saldo_awal')->default(0);
            $table->bigInteger('saldo_akhir')->default(0);
            $table->bigInteger('selisih')->default(0);

            $table->timestamps();

            $table->index('brilink_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brilink_accounts');
    }
};