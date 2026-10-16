<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku_internal',
        'nama_produk',
        'gambar',
        'hpp',
        'harga_jual_default',
        'is_active',
    ];

    protected $casts = [
        'hpp' => 'integer',
        'harga_jual_default' => 'integer',
        'is_active' => 'boolean',
    ];

    public function mappings()
    {
        return $this->hasMany(ProductMapping::class);
    }

    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/' . $this->gambar);
        }
        return null;
    }
}