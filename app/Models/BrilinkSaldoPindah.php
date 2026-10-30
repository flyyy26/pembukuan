<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkSaldoPindah extends Model
{
    protected $table = 'brilink_saldo_pindah';

    protected $fillable = [
        'brilink_report_id',
        'nominal',
        'keterangan',
    ];

    public function report()
    {
        return $this->belongsTo(BrilinkReport::class, 'brilink_report_id');
    }
}