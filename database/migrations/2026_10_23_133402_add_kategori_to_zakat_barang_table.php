<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zakat_barang', function (Blueprint $table) {
            $table->string('kategori', 100)->nullable()->after('nama_barang');
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('zakat_barang', function (Blueprint $table) {
            $table->dropIndex(['kategori']);
            $table->dropColumn('kategori');
        });
    }
};