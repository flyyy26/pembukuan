<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkAccount extends Model
{
    protected $fillable = [
        'brilink_report_id',
        'nama_rekening',
        'keterangan',
        'saldo_awal',
        'saldo_akhir',
        'selisih',
    ];

    protected $casts = [
        'saldo_awal'  => 'integer',
        'saldo_akhir' => 'integer',
        'selisih'     => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(BrilinkReport::class, 'brilink_report_id');
    }
}