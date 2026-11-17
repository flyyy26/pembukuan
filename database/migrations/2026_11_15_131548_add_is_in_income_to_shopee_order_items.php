// database/migrations/xxxx_add_is_in_income_to_shopee_order_items.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopee_order_items', function (Blueprint $table) {
            $table->boolean('is_in_income')->nullable()->after('total_pembayaran');
            $table->index('is_in_income');
        });
    }

    public function down(): void
    {
        Schema::table('shopee_order_items', function (Blueprint $table) {
            $table->dropIndex(['is_in_income']);
            $table->dropColumn('is_in_income');
        });
    }
};