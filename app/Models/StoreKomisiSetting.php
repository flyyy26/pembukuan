<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreKomisiSetting extends Model
{
    protected $fillable = [
        'store_id',
        'komisi_per_transaksi',
        'tipe_komisi',       
        'komisi_host_live_per_trx',   
        'komisi_host_live_global',  
        'komisi_web_per_trx', 
        'tipe_komisi_web',    
    ];

    protected $casts = [
        'komisi_per_transaksi'     => 'integer',
        'komisi_host_live_per_trx' => 'integer',
        'komisi_host_live_global'  => 'integer',
        'komisi_per_transaksi'     => 'integer',
        'komisi_web_per_trx'       => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Ambil nilai komisi host live global (satu nilai untuk semua host).
     */
    public static function getKomisiHostLiveGlobal(): int
    {
        $row = static::query()->where('komisi_host_live_global', '>', 0)->first();
        return $row ? (int) $row->komisi_host_live_global : 0;
    }

    /**
     * Simpan nilai komisi host live global ke semua baris setting.
     */
    public static function setKomisiHostLiveGlobal(int $nominal): void
    {
        static::query()->update(['komisi_host_live_global' => $nominal]);
    }
}