<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename sku_internal -> sku_prefix (kalau sku_internal masih ada)
        if (Schema::hasColumn('products', 'sku_internal')
            && !Schema::hasColumn('products', 'sku_prefix')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('sku_internal', 'sku_prefix');
            });
        }

        // 2. Drop kolom lama satu per satu (safe, cek dulu)
        $columnsToDrop = [];
        foreach (['warna', 'ukuran', 'stok', 'harga_jual_default'] as $col) {
            if (Schema::hasColumn('products', $col)) {
                $columnsToDrop[] = $col;
            }
        }

        if (!empty($columnsToDrop)) {
            Schema::table('products', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    public function down(): void
    {
        // Restore kolom lama (nullable/default) kalau belum ada
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'warna')) {
                $table->string('warna', 50)->nullable();
            }
            if (!Schema::hasColumn('products', 'ukuran')) {
                $table->string('ukuran', 50)->nullable();
            }
            if (!Schema::hasColumn('products', 'stok')) {
                $table->integer('stok')->default(0);
            }
            if (!Schema::hasColumn('products', 'harga_jual_default')) {
                $table->integer('harga_jual_default')->default(0);
            }
        });

        if (Schema::hasColumn('products', 'sku_prefix')
            && !Schema::hasColumn('products', 'sku_internal')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('sku_prefix', 'sku_internal');
            });
        }
    }
};