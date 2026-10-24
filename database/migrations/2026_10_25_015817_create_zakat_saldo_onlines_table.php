<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_saldo_onlines', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace'); // cth: Shopee, TikTok
            $table->integer('saldo');      // nominal saldo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_saldo_onlines');
    }
};