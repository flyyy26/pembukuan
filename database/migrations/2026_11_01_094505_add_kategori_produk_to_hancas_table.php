<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->foreignId('kategori_produk_id')->nullable()->constrained('konveksi_kategori_produks')->onDelete('set null')->after('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->dropForeign(['kategori_produk_id']);
            $table->dropColumn('kategori_produk_id');
        });
    }
};