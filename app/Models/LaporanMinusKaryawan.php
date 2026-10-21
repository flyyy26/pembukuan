<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanMinusKaryawan extends Model
{
    protected $table = 'laporan_minus_karyawan';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'nominal',
        'keterangan',
        'sudah_dipotong',
        'gaji_karyawan_id',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'nominal'       => 'integer',
        'sudah_dipotong'=> 'boolean',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function gaji()
    {
        return $this->belongsTo(GajiKaryawan::class, 'gaji_karyawan_id');
    }
}