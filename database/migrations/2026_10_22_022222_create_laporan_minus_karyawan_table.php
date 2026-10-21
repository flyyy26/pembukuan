<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_minus_karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('tanggal');
            $table->bigInteger('nominal')->default(0);
            $table->text('keterangan')->nullable();
            $table->boolean('sudah_dipotong')->default(false);
            $table->foreignId('gaji_karyawan_id')->nullable()->constrained('gaji_karyawan')->nullOnDelete();
            $table->timestamps();

            $table->index(['karyawan_id', 'sudah_dipotong']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_minus_karyawan');
    }
};