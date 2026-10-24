<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zakat_pembayarans', function (Blueprint $table) {
            $table->json('detail_breakdown')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('zakat_pembayarans', function (Blueprint $table) {
            $table->dropColumn('detail_breakdown');
        });
    }
};