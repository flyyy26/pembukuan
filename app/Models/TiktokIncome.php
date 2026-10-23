<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiktokIncome extends Model
{
    protected $table = 'tiktok_income';

    protected $fillable = [
        'import_id',
        'order_id',
        'jenis_transaksi',
        'waktu_pemesanan',
        'waktu_pembayaran_pesanan',
        'jumlah_penyelesaian_pembayaran',
        'total_pendapatan',
        'subtotal_setelah_diskon_penjual',
        'subtotal_sebelum_diskon',
        'diskon_penjual',
        'total_biaya',
        'biaya_komisi_platform',
        'komisi_afiliasi',
        'komisi_dinamis',
        'biaya_growth_xtra',
        'biaya_pemrosesan_pesanan',
        'pph_22',
        'ongkir',
        'detail_produk_terjual',
        'detail_produk_terjual_parsed',
    ];

    protected $casts = [
        'waktu_pemesanan' => 'date',
        'waktu_pembayaran_pesanan' => 'date',
        'jumlah_penyelesaian_pembayaran' => 'integer',
        'total_pendapatan' => 'integer',
        'subtotal_setelah_diskon_penjual' => 'integer',
        'subtotal_sebelum_diskon' => 'integer',
        'diskon_penjual' => 'integer',
        'total_biaya' => 'integer',
        'biaya_komisi_platform' => 'integer',
        'komisi_afiliasi' => 'integer',
        'komisi_dinamis' => 'integer',
        'biaya_growth_xtra' => 'integer',
        'biaya_pemrosesan_pesanan' => 'integer',
        'pph_22' => 'integer',
        'ongkir' => 'integer',
        'detail_produk_terjual_parsed' => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class, 'import_id');
    }
}