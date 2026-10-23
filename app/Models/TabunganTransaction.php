<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TabunganTransaction extends Model
{
    protected $fillable = [
        'tabungan_account_id',
        'tipe',
        'nominal',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'tanggal' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(TabunganAccount::class, 'tabungan_account_id');
    }

    /**
     * Nominal bertanda: +setoran, -penarikan.
     */
    public function getSignedNominalAttribute(): int
    {
        return $this->tipe === 'setoran' ? (int) $this->nominal : -(int) $this->nominal;
    }
}