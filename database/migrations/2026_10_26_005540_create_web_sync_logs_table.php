<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_web_sync_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['orders', 'products', 'customers'])->default('orders');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('records_created')->default(0);
            $table->integer('records_updated')->default(0);
            $table->integer('records_skipped')->default(0);
            $table->enum('status', ['running', 'success', 'failed'])->default('running')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_sync_logs');
    }
};