<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkPpobTransaction extends Model
{
    protected $fillable = [
        'brilink_report_id',
        'kategori',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(BrilinkReport::class, 'brilink_report_id');
    }
}