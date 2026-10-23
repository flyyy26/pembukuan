<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiktokOrderItem extends Model
{
    protected $fillable = [
        'import_id',
        'order_id',
        'order_status',
        'order_substatus',
        'sku_id',
        'seller_sku',
        'product_name',
        'variation',
        'quantity',
        'sku_unit_original_price',
        'sku_subtotal_before_discount',
        'sku_platform_discount',
        'sku_seller_discount',
        'sku_subtotal_after_discount',
        'order_amount',
        'created_time',
        'paid_time',
        'shipped_time',
        'delivered_time',
        'cancelled_time',
        'cancel_reason',
        'payment_method',
        'order_channel',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sku_unit_original_price' => 'integer',
        'sku_subtotal_before_discount' => 'integer',
        'sku_platform_discount' => 'integer',
        'sku_seller_discount' => 'integer',
        'sku_subtotal_after_discount' => 'integer',
        'order_amount' => 'integer',
        'created_time' => 'datetime',
        'paid_time' => 'datetime',
        'shipped_time' => 'datetime',
        'delivered_time' => 'datetime',
        'cancelled_time' => 'datetime',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class, 'import_id');
    }
}