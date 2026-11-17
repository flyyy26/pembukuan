<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeAdSpend extends Model
{
    protected $fillable = [
        'store_id',
        'period_key',
        'period_from',
        'period_to',
        'amount',
    ];

    protected $casts = [
        'period_from' => 'date:Y-m-d',
        'period_to' => 'date:Y-m-d',
        'amount' => 'integer',
    ];
}
