<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HancaPembayaran extends Model
{
    // ✅ HANYA SEKALI SAJA
    protected $fillable = [
        'hanca_id', 
        'hanca_detail_id', 
        'kasbon_id',
        'tipe', 
        'nominal', 
        'kasbon_dipotong',
        'qty_setor', 
        'tanggal', 
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function hanca()
    {
        return $this->belongsTo(Hanca::class);
    }
    
    public function hancaDetail()
    {
        return $this->belongsTo(HancaDetail::class, 'hanca_detail_id');
    }

    public function kasbon()
    {
        return $this->belongsTo(KonveksiKasbon::class, 'kasbon_id');
    }
}