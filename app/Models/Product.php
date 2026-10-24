<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku_internal',
        'nama_produk',
        'warna',
        'ukuran',
        'gambar',
        'hpp',
        'stok',
        'is_active',
    ];

    protected $casts = [
        'hpp'                => 'integer',
        'stok'               => 'integer',
        'is_active'          => 'boolean',
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

    /**
     * Harga yang dipakai untuk valuasi zakat.
     * Prioritas: harga_jual_default → hpp (fallback).
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