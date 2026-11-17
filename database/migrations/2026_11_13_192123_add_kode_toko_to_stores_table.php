<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Kode unik untuk mencocokkan dengan Excel (misal: CRH, AT, HM)
            $table->string('kode_toko', 10)->nullable()->after('nama_toko');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('kode_toko');
        });
    }
};