<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            $table->bigInteger('laba_kasir_pintar')->default(0)->after('manual_validasi_mutasi_fisik');
        });
    }

    public function down(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            $table->dropColumn('laba_kasir_pintar');
        });
    }
};
