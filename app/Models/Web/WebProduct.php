<?php
// app/Models/Web/WebProduct.php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class WebProduct extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'products';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(WebCategory::class, 'category_id', 'id');
    }

    public function variants()
    {
        return $this->hasMany(WebProductVariant::class, 'product_id', 'id');
    }
}