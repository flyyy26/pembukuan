<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brilink_pengeluaran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brilink_report_id')
                  ->constrained('brilink_reports')
                  ->cascadeOnDelete();

            $table->string('keterangan', 255);
            $table->bigInteger('nominal_kas')->default(0);
            $table->string('kategori', 100)->nullable(); // "Pindah Saldo Internal" atau nama kategori lain

            $table->timestamps();

            $table->index('brilink_report_id');
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brilink_pengeluaran');
    }
};