<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeOrderItem extends Model
{
    protected $fillable = [
        'import_id',
        'no_pesanan',
        'status_pesanan',
        'status_pembatalan',
        'no_resi',
        'opsi_pengiriman',
        'waktu_pesanan_dibuat',
        'waktu_pembayaran',
        'waktu_pesanan_selesai',
        'tipe_pesanan',
        'metode_pembayaran',
        'sku_induk',
        'nama_produk',
        'sku_varian',
        'nama_variasi',
        'harga_awal',
        'harga_setelah_diskon',
        'jumlah',
        'returned_quantity',
        'subtotal_pesanan',
        'total_diskon',
        'diskon_dari_penjual',
        'diskon_dari_shopee',
        'ongkos_kirim_dibayar_pembeli',
        'total_pembayaran',
    ];

    protected $casts = [
        'waktu_pesanan_dibuat' => 'datetime',
        'waktu_pembayaran' => 'datetime',
        'waktu_pesanan_selesai' => 'datetime',
        'harga_awal' => 'integer',
        'harga_setelah_diskon' => 'integer',
        'jumlah' => 'integer',
        'returned_quantity' => 'integer',
        'subtotal_pesanan' => 'integer',
        'total_diskon' => 'integer',
        'diskon_dari_penjual' => 'integer',
        'diskon_dari_shopee' => 'integer',
        'ongkos_kirim_dibayar_pembeli' => 'integer',
        'total_pembayaran' => 'integer',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
}