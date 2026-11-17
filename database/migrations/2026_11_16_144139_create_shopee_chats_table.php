<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopee_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->onDelete('cascade');
            $table->date('tanggal')->nullable();
            
            $table->integer('pengunjung')->default(0);
            $table->integer('jumlah_chat')->default(0);
            $table->integer('pengunjung_bertanya')->default(0);
            $table->integer('pertanyaan_diajukan')->default(0);
            $table->integer('chat_dibalas')->default(0);
            $table->integer('chat_belum_dibalas')->default(0);
            
            $table->string('waktu_respon_rata_rata')->nullable();
            $table->decimal('csat_persen', 5, 2)->default(0); // Maks 100.00
            $table->string('waktu_respon_pertama')->nullable();
            
            $table->decimal('konversi_chat_direspon', 5, 2)->default(0);
            $table->integer('total_pembeli')->default(0);
            $table->integer('total_pesanan')->default(0);
            $table->string('produk')->nullable();
            $table->decimal('penjualan_idr', 15, 2)->default(0);
            $table->decimal('konversi_chat_dibalas', 5, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_chats');
    }
};