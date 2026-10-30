<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('brilink_saldo_pindah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brilink_report_id')->constrained('brilink_reports')->onDelete('cascade');
            $table->integer('nominal');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brilink_saldo_pindahs');
    }
};
