<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku_prefix',
        'nama_produk',
        'hpp',
        'stok',
        'is_active',
    ];

    protected $casts = [
        'hpp'       => 'integer',
        'stok'      => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Harga yang dipakai untuk valuasi zakat.
     */
    public function getHargaZakatAttribute(): int
    {
        return (int) $this->hpp;
    }

    /**
     * Nilai total stok untuk zakat.
     */
    public function getNilaiZakatAttribute(): int
    {
        return (int) ($this->stok * $this->hpp);
    }
}