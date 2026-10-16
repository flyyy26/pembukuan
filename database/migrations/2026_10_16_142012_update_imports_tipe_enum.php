<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE imports MODIFY COLUMN tipe ENUM(
            'tiktok_order',
            'tiktok_income',
            'shopee_income',
            'shopee_order'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE imports MODIFY COLUMN tipe ENUM(
            'tiktok_order',
            'tiktok_income',
            'shopee_income'
        ) NOT NULL");
    }
};