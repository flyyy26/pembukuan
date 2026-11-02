<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HancaDetail extends Model
{
    protected $fillable = [
        'hanca_id', 'kategori_produk_id', 'nama_produk', 'warna', 'ukuran',
        'jumlah_hanca', 'sisa_hanca', 'harga', 'total', 'sisa_nominal'
    ];

    public function hanca()
    {
        return $this->belongsTo(Hanca::class);
    }

    public function kategoriProduk()
    {
        return $this->belongsTo(KonveksiKategoriProduk::class, 'kategori_produk_id');
    }
}