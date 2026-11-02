<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            $table->foreignId('hanca_detail_id')->nullable()->after('hanca_id')
                ->constrained('hanca_details')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('hanca_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['hanca_detail_id']);
            $table->dropColumn('hanca_detail_id');
        });
    }
};
