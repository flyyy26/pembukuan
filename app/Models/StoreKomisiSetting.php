<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreKomisiSetting extends Model
{
    protected $fillable = [
        'store_id',
        'komisi_per_transaksi',
    ];

    protected $casts = [
        'komisi_per_transaksi' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}