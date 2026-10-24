<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $fillable = [
        'tanggal',
        'nama_pemberi_hutang',
        'keterangan',
        'nominal',
        'is_lunas',
        'tanggal_lunas',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'integer',
        'is_lunas' => 'boolean',
        'tanggal_lunas' => 'date',
    ];
}