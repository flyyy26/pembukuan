<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeLiveVideo extends Model
{
    protected $fillable = [
        'import_id', 'tanggal', 'judul', 'tipe', 
        'penonton', 'produk_terjual', 'total_pesanan', 'penjualan_idr', 'produk', 'metrics'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'metrics' => 'array',
    ];

    public function importData()
    {
        return $this->belongsTo(Import::class, 'import_id');
    }
}