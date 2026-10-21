<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkPengeluaran extends Model
{
    protected $table = 'brilink_pengeluaran';

    protected $fillable = [
        'brilink_report_id',
        'keterangan',
        'nominal_kas',
        'kategori',
        'is_paid',
        'paid_at',
        'paid_amount',
    ];

    protected $casts = [
        'nominal_kas' => 'integer',
        'is_paid'     => 'boolean',
        'paid_at'     => 'date',
        'paid_amount' => 'integer',
    ];


    public function report()
    {
        return $this->belongsTo(BrilinkReport::class, 'brilink_report_id');
    }

    /**
     * Pengeluaran riil = bukan "Pindah Saldo Internal".
     */
    public function scopeRiil($q)
    {
        return $q->where(function ($qq) {
            $qq->whereNull('kategori')
               ->orWhere('kategori', '!=', 'Pindah Saldo Internal');
        });
    }

    /**
     * Pindah saldo internal (perputaran kas, bukan biaya).
     */
    public function scopePindahSaldo($q)
    {
        return $q->where('kategori', 'Pindah Saldo Internal');
    }

    public function getSisaAttribute(): int
    {
        return max(0, $this->nominal_kas - $this->paid_amount);
    }
}