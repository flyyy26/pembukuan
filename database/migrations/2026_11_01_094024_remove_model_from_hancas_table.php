<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->dropColumn('model');
        });
    }

    public function down(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->string('model')->nullable()->after('kategori');
        });
    }
};