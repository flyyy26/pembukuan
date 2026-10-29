<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebOrderItemSnapshot extends Model
{
    protected $table = 'web_order_items_snapshot';

    protected $fillable = [
        'web_order_id',
        'web_order_item_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'sku',
        'category_name',
        'price',
        'quantity',
        'subtotal',
        'weight',
    ];

    protected $casts = [
        'web_order_id'       => 'integer',
        'web_order_item_id'  => 'integer',
        'product_id'         => 'integer',
        'product_variant_id' => 'integer',
        'price'              => 'integer',
        'quantity'           => 'integer',
        'subtotal'           => 'integer',
        'weight'             => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(WebOrderSnapshot::class, 'web_order_id', 'web_order_id');
    }
}