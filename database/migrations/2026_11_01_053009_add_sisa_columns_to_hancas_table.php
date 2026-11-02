<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->integer('sisa_hanca')->default(0)->after('jumlah_hanca');
            $table->bigInteger('sisa_nominal')->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('hancas', function (Blueprint $table) {
            $table->dropColumn(['sisa_hanca', 'sisa_nominal']);
        });
    }
};