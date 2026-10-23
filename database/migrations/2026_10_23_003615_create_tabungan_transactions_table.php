<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabungan_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tabungan_account_id')
                  ->constrained('tabungan_accounts')
                  ->cascadeOnDelete();
            $table->enum('tipe', ['setoran', 'penarikan']);
            $table->unsignedBigInteger('nominal');
            $table->date('tanggal');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index(['tabungan_account_id', 'tanggal']);
            $table->index(['tipe', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabungan_transactions');
    }
};