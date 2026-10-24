<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah FK
        Schema::table('zakat_barang', function (Blueprint $table) {
            $table->foreignId('zakat_kategori_id')
                  ->nullable()
                  ->after('nama_barang')
                  ->constrained('zakat_kategoris')
                  ->nullOnDelete();
        });

        // 2) Migrasi data lama: kategori (string) → tabel zakat_kategori
        if (Schema::hasColumn('zakat_barang', 'kategori')) {
            $uniqueKategori = DB::table('zakat_barang')
                ->whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->distinct()
                ->pluck('kategori');

            foreach ($uniqueKategori as $nama) {
                $id = DB::table('zakat_kategori')->insertGetId([
                    'nama'       => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('zakat_barang')
                    ->where('kategori', $nama)
                    ->update(['zakat_kategori_id' => $id]);
            }
        }

        // 3) Drop kolom lama
        Schema::table('zakat_barang', function (Blueprint $table) {
            if (Schema::hasColumn('zakat_barang', 'kategori')) {
                $table->dropIndex(['kategori']);
                $table->dropColumn('kategori');
            }
        });
    }

    public function down(): void
    {
        Schema::table('zakat_barang', function (Blueprint $table) {
            $table->string('kategori', 100)->nullable()->after('nama_barang');
        });

        // Kembalikan data dari FK ke string
        DB::table('zakat_barang')
            ->whereNotNull('zakat_kategori_id')
            ->orderBy('id')
            ->each(function ($row) {
                $nama = DB::table('zakat_kategori')
                    ->where('id', $row->zakat_kategori_id)
                    ->value('nama');
                if ($nama) {
                    DB::table('zakat_barang')
                        ->where('id', $row->id)
                        ->update(['kategori' => $nama]);
                }
            });

        Schema::table('zakat_barang', function (Blueprint $table) {
            $table->dropForeign(['zakat_kategori_id']);
            $table->dropColumn('zakat_kategori_id');
        });
    }
};