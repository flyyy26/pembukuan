<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brilink_pengeluaran', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('kategori');
            $table->date('paid_at')->nullable()->after('is_paid');
            $table->bigInteger('paid_amount')->default(0)->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('brilink_pengeluaran', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'paid_at', 'paid_amount']);
        });
    }
};