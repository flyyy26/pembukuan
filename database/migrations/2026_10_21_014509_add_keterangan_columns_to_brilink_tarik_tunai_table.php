<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brilink_tarik_tunai', function (Blueprint $table) {
            // Tambah kolom ket_mesin_edc setelah admin_toko
            $table->string('ket_mesin_edc')->nullable()->after('admin_toko');
            
            // Tambah kolom keterangan setelah keterangan_rek
            $table->string('keterangan')->nullable()->after('keterangan_rek');
        });
    }

    public function down(): void
    {
        Schema::table('brilink_tarik_tunai', function (Blueprint $table) {
            $table->dropColumn(['ket_mesin_edc', 'keterangan']);
        });
    }
};