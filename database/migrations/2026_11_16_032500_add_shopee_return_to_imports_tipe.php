<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE `imports`
            MODIFY COLUMN `tipe`
            ENUM('shopee_order', 'shopee_income', 'tiktok_order', 'tiktok_income', 'brilink', 'shopee_return')
            NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('imports')->where('tipe', 'shopee_return')->delete();

        DB::statement("
            ALTER TABLE `imports`
            MODIFY COLUMN `tipe`
            ENUM('shopee_order', 'shopee_income', 'tiktok_order', 'tiktok_income', 'brilink')
            NOT NULL
        ");
    }
};
