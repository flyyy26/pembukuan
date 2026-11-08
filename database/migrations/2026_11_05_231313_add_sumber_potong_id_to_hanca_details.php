<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hanca_details', function (Blueprint $table) {
            $table->unsignedBigInteger('sumber_potong_id')->nullable()->after('hanca_id');
            $table->foreign('sumber_potong_id')
                ->references('id')->on('hanca_details')
                ->nullOnDelete();
            $table->index('sumber_potong_id');
        });
    }

    public function down(): void
    {
        Schema::table('hanca_details', function (Blueprint $table) {
            $table->dropForeign(['sumber_potong_id']);
            $table->dropIndex(['sumber_potong_id']);
            $table->dropColumn('sumber_potong_id');
        });
    }
};