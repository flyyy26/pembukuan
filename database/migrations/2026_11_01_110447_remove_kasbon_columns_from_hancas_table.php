<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            if (Schema::hasColumn('hancas', 'total_kasbon')) {
                $table->dropColumn('total_kasbon');
            }
        });

        // Hapus juga data pembayaran dengan tipe 'kasbon' (opsional, jika ingin bersih)
        // DB::table('hanca_pembayarans')->where('tipe', 'kasbon')->delete();
    }

    public function down(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->bigInteger('total_kasbon')->default(0);
        });
    }
};