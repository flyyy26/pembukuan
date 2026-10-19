<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cek dulu tipe ENUM saat ini (opsional, untuk memastikan)
        // Coba jalankan: SHOW COLUMNS FROM imports LIKE 'tipe';
        
        // 2. Ubah ENUM agar menambahkan 'brilink'
        // Asumsi ENUM awal Anda adalah shopee_order, shopee_income, tiktok_order, tiktok_income
        DB::statement("
            ALTER TABLE `imports` 
            MODIFY COLUMN `tipe` 
            ENUM('shopee_order', 'shopee_income', 'tiktok_order', 'tiktok_income', 'brilink') 
            NOT NULL
        ");
    }

    public function down(): void
    {
        // Rollback: hapus data brilink dulu agar tidak error saat ENUM dikurangi
        DB::table('imports')->where('tipe', 'brilink')->delete();

        DB::statement("
            ALTER TABLE `imports` 
            MODIFY COLUMN `tipe` 
            ENUM('shopee_order', 'shopee_income', 'tiktok_order', 'tiktok_income') 
            NOT NULL
        ");
    }
};