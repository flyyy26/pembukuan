<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkTarikTunai extends Model
{
    protected $table = 'brilink_tarik_tunai';

    protected $fillable = [
        'brilink_report_id',
        'tipe',
        'nominal',
        'admin_toko',
        'kmc',
        'saldo_masuk',
        'keterangan_rek',
    ];

    protected $casts = [
        'nominal'     => 'integer',
        'admin_toko'  => 'integer',
        'kmc'         => 'integer',
        'saldo_masuk' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(BrilinkReport::class, 'brilink_report_id');
    }

    public function scopeEdc($q)      { return $q->where('tipe', 'edc_bri'); }
    public function scopeMbanking($q) { return $q->where('tipe', 'mbanking'); }
}