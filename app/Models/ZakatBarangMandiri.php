<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZakatBarangMandiri extends Model
{
    protected $table = 'zakat_barang_mandiri';

    protected $fillable = [
        'nama_barang',
        'qty',
        'harga_satuan',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'integer',
    ];
}