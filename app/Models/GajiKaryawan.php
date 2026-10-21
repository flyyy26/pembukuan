<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GajiKaryawan extends Model
{
    protected $table = 'gaji_karyawan';

    protected $fillable = [
        'karyawan_id',
        'periode',
        'gaji_pokok',
        'bonus',
        'potongan',
        'total_gaji',
        'status',
        'tanggal_bayar',
        'metode_bayar',
    ];

    protected $casts = [
        'gaji_pokok'    => 'integer',
        'bonus'         => 'integer',
        'potongan'      => 'integer',
        'total_gaji'    => 'integer',
        'tanggal_bayar' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function minusItems()
    {
        return $this->hasMany(LaporanMinusKaryawan::class);
    }

    /** Hitung ulang total gaji */
    public function recalculateTotal(): void
    {
        $this->total_gaji = ($this->gaji_pokok + $this->bonus) - $this->potongan;
        $this->save();
    }

    public function getPeriodeLabelAttribute(): string
    {
        if (!$this->periode) return '-';
        try {
            return \Carbon\Carbon::createFromFormat('Y-m', $this->periode)->translatedFormat('F Y');
        } catch (\Throwable $e) {
            return $this->periode;
        }
    }
}