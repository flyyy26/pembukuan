<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_ad_spends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('period_key', 21);
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_ad_spends');
    }
};
