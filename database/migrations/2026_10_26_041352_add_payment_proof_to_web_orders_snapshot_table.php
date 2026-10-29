<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_orders_snapshot', function (Blueprint $table) {
            // Menyimpan path gambar, misal: 'payment-proofs/abc.webp'
            $table->string('payment_proof')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('web_orders_snapshot', function (Blueprint $table) {
            $table->dropColumn('payment_proof');
        });
    }
};