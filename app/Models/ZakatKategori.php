<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZakatKategori extends Model
{

    protected $fillable = ['nama'];

    public function barang(): HasMany
    {
        return $this->hasMany(ZakatBarang::class, 'zakat_kategori_id');
    }
}