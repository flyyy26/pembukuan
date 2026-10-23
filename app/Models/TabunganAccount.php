<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TabunganAccount extends Model
{
    protected $fillable = [
        'nama',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(TabunganTransaction::class);
    }

    /**
     * Saldo real-time = total setoran - total penarikan.
     */
    public function getSaldoAttribute(): int
    {
        $setoran   = $this->transactions()->where('tipe', 'setoran')->sum('nominal');
        $penarikan = $this->transactions()->where('tipe', 'penarikan')->sum('nominal');

        return (int) ($setoran - $penarikan);
    }

    /**
     * Scope: hanya akun aktif.
     */
    public function scopeAktif($q)
    {
        return $q->where('is_active', true);
    }
}