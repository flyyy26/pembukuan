<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brilink_setoran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brilink_report_id')
                  ->constrained('brilink_reports')
                  ->cascadeOnDelete();

            $table->bigInteger('nominal_edc')->default(0);
            $table->bigInteger('administrasi_toko')->default(0);
            $table->bigInteger('jumlah_setoran_riil')->default(0);

            $table->timestamps();

            $table->index('brilink_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brilink_setoran');
    }
};