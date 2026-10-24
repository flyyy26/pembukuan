<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZakatPembayaran extends Model
{
    protected $fillable = [
        'tanggal',
        'total_zakat',
        'total_saldo',
        'total_piutang',
        'total_barang',
        'total_hutang',
        'total_tabungan',
        'keterangan',
        'detail_breakdown',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'detail_breakdown' => 'array',
    ];
}