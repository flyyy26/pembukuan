<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brilink_ppob_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brilink_report_id')
                  ->constrained('brilink_reports')
                  ->cascadeOnDelete();

            // PULSA | KP (KASIR PINTAR) | FASTPAY | BUKU WARUNG | LAIN LAIN | PENAMBAHAN KAS
            $table->string('kategori', 50);
            $table->bigInteger('nominal')->default(0);
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index('brilink_report_id');
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brilink_ppob_transactions');
    }
};