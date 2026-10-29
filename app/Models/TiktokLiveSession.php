<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiktokLiveSession extends Model
{
    protected $fillable = [
        'store_id',
        'karyawan_id',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'total_transaksi',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_transaksi' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function products()
    {
        return $this->hasMany(TiktokLiveProduct::class);
    }
}