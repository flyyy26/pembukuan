<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonveksiKasbon extends Model
{
    protected $fillable = ['konveksi_karyawan_id', 'nominal', 'nominal_awal', 'tanggal', 'keterangan', 'status'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(KonveksiKaryawan::class, 'konveksi_karyawan_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(HancaPembayaran::class, 'kasbon_id');
    }
}