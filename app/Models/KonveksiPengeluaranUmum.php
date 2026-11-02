<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonveksiPengeluaranUmum extends Model
{
    protected $table = 'konveksi_pengeluaran_umums';

    protected $fillable = ['nama', 'nominal', 'tanggal', 'keterangan'];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
