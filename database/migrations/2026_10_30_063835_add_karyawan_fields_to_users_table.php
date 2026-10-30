<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Link ke tabel karyawan (nullable: admin tidak punya karyawan_id)
            $table->foreignId('karyawan_id')
                ->nullable()
                ->after('id')
                ->constrained('karyawan')
                ->nullOnDelete();

            // Flag: karyawan ini boleh login?
            $table->boolean('can_login')->default(false)->after('karyawan_id');

            // Flag: apakah wajib ganti password saat login pertama
            $table->boolean('must_change_password')->default(false)->after('can_login');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn(['karyawan_id', 'can_login', 'must_change_password']);
        });
    }
};