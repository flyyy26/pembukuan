<?php

namespace Tests\Feature;

use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class StoreTableTest extends TestCase
{
    public function test_store_code_is_rendered_and_available_to_edit_on_desktop_and_mobile(): void
    {
        $store = new Store([
            'nama_toko' => 'HM Barokah',
            'kode_toko' => 'HM',
            'marketplace' => 'shopee',
        ]);
        $store->id = 1;

        $stores = new LengthAwarePaginator(collect([$store]), 1, 25);
        $html = view('stores.partials.table', compact('stores'))->render();

        $this->assertSame(2, substr_count($html, 'data-kode="HM"'));
        $this->assertSame(2, substr_count($html, 'Kode Toko'));
        $this->assertSame(2, substr_count($html, '>HM</span>'));
    }
}
