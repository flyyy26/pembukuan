<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonveksiPengeluaran extends Model
{
    protected $fillable = ['konveksi_karyawan_id', 'kategori', 'nominal', 'keterangan'];

    public function karyawan()
    {
        return $this->belongsTo(KonveksiKaryawan::class, 'konveksi_karyawan_id');
    }
}