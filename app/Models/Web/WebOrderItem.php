<?php
// app/Models/Web/WebOrderItem.php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class WebOrderItem extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'order_items';

    protected $casts = [
        'price'    => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(WebOrder::class, 'order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(WebProduct::class, 'product_id', 'id');
    }

    public function variant()
    {
        return $this->belongsTo(WebProductVariant::class, 'product_variant_id', 'id');
    }
}