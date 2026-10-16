<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->enum('tipe', ['tiktok_order', 'tiktok_income', 'shopee_income']);
            $table->string('file_name');
            $table->string('file_hash')->nullable();
            $table->date('periode_start')->nullable();
            $table->date('periode_end')->nullable();
            $table->integer('total_rows')->default(0);
            $table->enum('status', ['pending', 'parsed', 'imported', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['tipe', 'periode_start', 'periode_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};