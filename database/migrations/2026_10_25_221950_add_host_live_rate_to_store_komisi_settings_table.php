<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            $table->integer('komisi_host_live_per_trx')->default(0)->after('komisi_per_transaksi');
        });
    }

    public function down()
    {
        Schema::table('store_komisi_settings', function (Blueprint $table) {
            $table->dropColumn('komisi_host_live_per_trx');
        });
    }
};