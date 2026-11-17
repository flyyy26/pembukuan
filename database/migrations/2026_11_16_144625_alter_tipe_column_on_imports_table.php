<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            // Ubah tipe menjadi string (VARCHAR) panjang agar bisa menampung tipe baru
            $table->string('tipe', 50)->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            // (Opsional) Kembalikan ke tipe sebelumnya jika diperlukan
            $table->string('tipe', 20)->change(); 
        });
    }
};