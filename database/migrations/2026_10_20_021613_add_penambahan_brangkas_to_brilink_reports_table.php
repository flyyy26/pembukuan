<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            $table->bigInteger('penambahan_brangkas')->default(0)->after('laba_kasir_pintar');
        });
    }

    public function down(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            $table->dropColumn('penambahan_brangkas');
        });
    }
};
