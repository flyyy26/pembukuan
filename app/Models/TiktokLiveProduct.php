<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiktokLiveProduct extends Model
{
    protected $fillable = [
        'tiktok_live_session_id',
        'nama_produk',
        'ukuran',
        'qty',
    ];

    public function session()
    {
        return $this->belongsTo(TiktokLiveSession::class, 'tiktok_live_session_id');
    }
}