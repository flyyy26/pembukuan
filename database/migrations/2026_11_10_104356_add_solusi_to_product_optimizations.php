<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_optimizations', function (Blueprint $table) {
            // Tambah field solusi & status pelaksanaan untuk tiap aspek
            $aspekKeys = ['ukuran', 'deskripsi', 'stok', 'harga', 'rating', 'retur', 'ongkir', 'foto', 'video'];
            foreach ($aspekKeys as $key) {
                $table->text("solusi_{$key}")->nullable()->after("audit_{$key}");
                $table->boolean("sudah_{$key}")->default(false)->after("solusi_{$key}");
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_optimizations', function (Blueprint $table) {
            $aspekKeys = ['ukuran', 'deskripsi', 'stok', 'harga', 'rating', 'retur', 'ongkir', 'foto', 'video'];
            foreach ($aspekKeys as $key) {
                $table->dropColumn(["solusi_{$key}", "sudah_{$key}"]);
            }
        });
    }
};