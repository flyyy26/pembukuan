<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            $table->bigInteger('manual_kas_akhir')->nullable()->after('manual_validasi_kas_uang');
            $table->bigInteger('manual_kas_awal')->nullable()->after('manual_kas_akhir');
        });
    }

    public function down(): void
    {
        Schema::table('brilink_reports', function (Blueprint $table) {
            $table->dropColumn(['manual_kas_akhir', 'manual_kas_awal']);
        });
    }
};