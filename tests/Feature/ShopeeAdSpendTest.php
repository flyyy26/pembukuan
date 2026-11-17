<?php

namespace Tests\Feature;

use App\Http\Controllers\ShopeeController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Store;
use Illuminate\Http\Request;
use Tests\TestCase;

class ShopeeAdSpendTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace');
            $table->string('nama_toko');
            $table->timestamps();
        });

        $migration = require database_path('migrations/2026_11_16_042400_create_shopee_ad_spends_table.php');
        $migration->up();
    }

    public function test_ad_spend_is_upserted_per_store_and_date_range(): void
    {
        $store = Store::create([
            'marketplace' => 'shopee',
            'nama_toko' => 'Toko Uji',
        ]);

        foreach ([250, '1.234'] as $amount) {
            $request = Request::create('/shopee/ad-spend', 'POST', [
                'store' => $store->id,
                'from' => '2026-10-01',
                'to' => '2026-10-31',
                'tab' => 'pesanan',
                'biaya_iklan' => $amount,
            ]);

            $response = app(ShopeeController::class)->saveAdSpend($request);
            $this->assertSame(route('shopee.index', [
                'store' => $store->id,
                'from' => '2026-10-01',
                'to' => '2026-10-31',
                'tab' => 'pesanan',
            ]), $response->getTargetUrl());
        }

        $otherPeriodRequest = Request::create('/shopee/ad-spend', 'POST', [
            'store' => $store->id,
            'from' => '2026-11-01',
            'to' => '2026-11-30',
            'tab' => 'ringkasan',
            'biaya_iklan' => 125,
        ]);
        app(ShopeeController::class)->saveAdSpend($otherPeriodRequest);

        $this->assertDatabaseCount('shopee_ad_spends', 2);
        $this->assertDatabaseHas('shopee_ad_spends', [
            'store_id' => $store->id,
            'period_key' => '2026-10-01|2026-10-31',
            'amount' => 1234,
        ]);
        $this->assertDatabaseHas('shopee_ad_spends', [
            'store_id' => $store->id,
            'period_key' => '2026-11-01|2026-11-30',
            'amount' => 125,
        ]);
    }
}
