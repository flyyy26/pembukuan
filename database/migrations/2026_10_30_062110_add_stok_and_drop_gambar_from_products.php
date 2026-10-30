<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah stok lagi
        if (!Schema::hasColumn('products', 'stok')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('stok')->default(0)->after('hpp');
            });
        }

        // Drop gambar
        if (Schema::hasColumn('products', 'gambar')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('gambar');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'gambar')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('gambar')->nullable()->after('nama_produk');
            });
        }

        if (Schema::hasColumn('products', 'stok')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('stok');
            });
        }
    }
};