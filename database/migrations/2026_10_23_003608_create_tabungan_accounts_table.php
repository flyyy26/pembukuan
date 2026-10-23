<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabungan_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('nama');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabungan_accounts');
    }
};