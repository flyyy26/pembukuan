<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('store_komisi_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->integer('komisi_per_transaksi')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_komisi_settings');
    }
};