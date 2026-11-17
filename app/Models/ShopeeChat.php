<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeChat extends Model
{
    protected $fillable = [
        'import_id', 'tanggal', 'pengunjung', 'jumlah_chat', 'pengunjung_bertanya', 
        'pertanyaan_diajukan', 'chat_dibalas', 'chat_belum_dibalas', 'waktu_respon_rata_rata', 
        'csat_persen', 'waktu_respon_pertama', 'konversi_chat_direspon', 'total_pembeli', 
        'total_pesanan', 'produk', 'penjualan_idr', 'konversi_chat_dibalas'
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
}