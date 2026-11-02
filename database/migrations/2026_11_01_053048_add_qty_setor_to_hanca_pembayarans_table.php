<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            $table->integer('qty_setor')->nullable()->after('nominal');
        });
    }

    public function down(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            $table->dropColumn('qty_setor');
        });
    }
};