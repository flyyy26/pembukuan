<?php

namespace Tests\Feature;

use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ShopeeReturnImportFormTest extends TestCase
{
    public function test_return_report_has_an_independent_import_form(): void
    {
        $store = new Store([
            'nama_toko' => 'Toko Shopee',
            'marketplace' => 'shopee',
        ]);
        $store->id = 6;

        $html = view('import.index', [
            'stores' => collect([$store]),
            'imports' => new LengthAwarePaginator(collect(), 0, 15),
            'errors' => new ViewErrorBag(),
        ])->render();

        $action = preg_quote(route('import.shopee.return'), '/');
        $this->assertSame(
            1,
            preg_match('/<form\b[^>]*action="' . $action . '"[^>]*>(.*?)<\/form>/s', $html, $matches)
        );

        $returnForm = $matches[1];
        $this->assertStringContainsString('name="store_id"', $returnForm);
        $this->assertStringContainsString('name="file"', $returnForm);
        $this->assertStringNotContainsString('name="order_file"', $returnForm);
        $this->assertStringNotContainsString('name="income_file"', $returnForm);
    }
}
