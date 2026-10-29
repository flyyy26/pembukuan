<?php
// app/Models/Web/WebProductVariant.php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class WebProductVariant extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'product_variants';

    protected $casts = [
        'price'          => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock'          => 'integer',
        'weight'         => 'integer',
        'is_active'      => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(WebProduct::class, 'product_id', 'id');
    }
}