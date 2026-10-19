<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM marketplace supaya include 'brilink'
        DB::statement("
            ALTER TABLE `stores`
            MODIFY COLUMN `marketplace`
            ENUM('shopee', 'tiktok', 'tokopedia', 'lazada', 'brilink')
            NOT NULL
        ");
    }

    public function down(): void
    {
        // Hapus dulu semua row dengan marketplace=brilink (kalau rollback)
        DB::table('stores')->where('marketplace', 'brilink')->delete();

        DB::statement("
            ALTER TABLE `stores`
            MODIFY COLUMN `marketplace`
            ENUM('shopee', 'tiktok', 'tokopedia', 'lazada')
            NOT NULL
        ");
    }
};