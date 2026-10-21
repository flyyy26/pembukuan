<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'store_id',
        'nama',
        'gaji_pokok',
        'is_active',
        'tanggal_masuk',
        'no_hp',
    ];

    protected $casts = [
        'gaji_pokok'    => 'integer',
        'is_active'     => 'boolean',
        'tanggal_masuk' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function gajiItems()
    {
        return $this->hasMany(GajiKaryawan::class);
    }

    public function minusItems()
    {
        return $this->hasMany(LaporanMinusKaryawan::class);
    }

    /** Total minus yang belum dipotong */
    public function getTotalMinusBelumDipotongAttribute(): int
    {
        return (int) $this->minusItems()->where('sudah_dipotong', false)->sum('nominal');
    }
}