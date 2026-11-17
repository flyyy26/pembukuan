<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeReturn extends Model
{
    protected $fillable = [
        'import_id', 'no_pesanan', 'no_pengajuan', 'tipe_laporan', 
        'status_pengajuan', 'alasan_pengajuan', 'sku_varian', 
        'nama_produk', 'jumlah', 'nominal_pengembalian', 
        'waktu_pengajuan', 'raw_data', 'status_pengiriman_ke_pembeli',
        'status_pengembalian_ke_penjual',
        'pengembalian_dana'
    ];

    protected $casts = [
        'waktu_pengajuan' => 'date',
        'raw_data' => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
}