<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_live_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->onDelete('cascade');
            $table->date('tanggal')->nullable();
            
            $table->string('judul')->nullable(); // Judul Live / Video
            $table->string('tipe')->nullable();  // 'Live' atau 'Video'
            
            $table->integer('penonton')->default(0);
            $table->integer('produk_terjual')->default(0);
            $table->integer('total_pesanan')->default(0);
            $table->decimal('penjualan_idr', 15, 2)->default(0);
            
            $table->string('produk')->nullable(); // Nama Produk jika ada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_live_videos');
    }
};