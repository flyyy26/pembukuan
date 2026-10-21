<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gaji_karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('periode', 7); // YYYY-MM
            $table->bigInteger('gaji_pokok')->default(0);
            $table->bigInteger('bonus')->default(0);
            $table->bigInteger('potongan')->default(0);
            $table->bigInteger('total_gaji')->default(0);
            $table->enum('status', ['draft', 'dibayar'])->default('draft');
            $table->date('tanggal_bayar')->nullable();
            $table->enum('metode_bayar', ['tunai', 'transfer'])->default('tunai');
            $table->timestamps();

            $table->unique(['karyawan_id', 'periode']);
            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaji_karyawan');
    }
};