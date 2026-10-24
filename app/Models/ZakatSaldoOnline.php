<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZakatSaldoOnline extends Model
{
    protected $fillable = [
        'marketplace',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'integer',
    ];
}