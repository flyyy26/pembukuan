<?php

namespace Tests\Feature;

use App\Models\ShopeeChat;
use Tests\TestCase;

class ShopeeChatQueryTest extends TestCase
{
    public function test_chat_query_can_be_filtered_by_related_import(): void
    {
        $query = ShopeeChat::query()->whereHas('import', function ($query) {
            $query->where('store_id', 1);
        });

        $this->assertStringContainsString('exists', $query->toSql());
    }
}
