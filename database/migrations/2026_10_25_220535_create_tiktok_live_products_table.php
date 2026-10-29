<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_live_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiktok_live_session_id')->constrained('tiktok_live_sessions')->onDelete('cascade');
            $table->string('nama_produk');
            $table->string('ukuran')->nullable();
            $table->integer('qty');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_live_products');
    }
};