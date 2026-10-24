<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZakatBarang extends Model
{
    protected $table = 'zakat_barang';

    protected $fillable = [
        'nama_barang',
        'zakat_kategori_id',
        'qty',
        'harga_satuan',
        'keterangan',
    ];

    protected $casts = [
        'qty'          => 'integer',
        'harga_satuan' => 'integer',
    ];

    public function kategoriRel(): BelongsTo
    {
        return $this->belongsTo(ZakatKategori::class, 'zakat_kategori_id');
    }

    public function getTotalAttribute(): int
    {
        return (int) ($this->qty * $this->harga_satuan);
    }
}