<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopee_returns', function (Blueprint $table) {
            $table->string('status_pengiriman_ke_pembeli')->nullable()->after('status_pengajuan');
            $table->string('status_pengembalian_ke_penjual')->nullable()->after('status_pengiriman_ke_pembeli');
            $table->integer('pengembalian_dana')->default(0)->after('nominal_pengembalian');
        });
    }

    public function down(): void
    {
        Schema::table('shopee_returns', function (Blueprint $table) {
            $table->dropColumn(['status_pengiriman_ke_pembeli', 'status_pengembalian_ke_penjual', 'pengembalian_dana']);
        });
    }
};