<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'marketplace',
        'nama_toko',
        'seller_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    public function mappings()
    {
        return $this->hasMany(ProductMapping::class);
    }

    public function getMarketplaceLabelAttribute(): string
    {
        return match($this->marketplace) {
            'shopee'    => 'Shopee',
            'tiktok'    => 'TikTok Shop',
            'tokopedia' => 'Tokopedia',
            'lazada'    => 'Lazada',
            default     => ucfirst($this->marketplace),
        };
    }
}