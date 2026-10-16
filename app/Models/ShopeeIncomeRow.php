<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeIncomeRow extends Model
{
    protected $fillable = [
        'import_id',
        'tipe_baris',
        'no_pesanan',
        'no_pengajuan',
        'id_produk',
        'nama_produk',
        'waktu_pesanan_dibuat',
        'tanggal_dana_dilepaskan',
        'tipe_pesanan',
        'total_penghasilan',
        'harga_produk',
        'biaya_administrasi',
        'biaya_proses_pesanan',
        'biaya_gratis_ongkir_xtra',
        'biaya_transaksi',
        'biaya_komisi_ams',
        'pph_22',
        'ongkos_kirim_dibayarkan',
        'gratis_ongkir_dari_shopee',
        'jumlah_pengembalian_dana',
        'biaya_kampanye',
        'biaya_isi_saldo_otomatis',
        'premi',
        'fbs_fee',
        'voucher_disponsor_penjual',
        'cashback_koin_disponsori_penjual',
        'diskon_produk_dari_shopee',
        'voucher_cofund_disponsor_penjual',
        'cashback_koin_cofund_disponsori_penjual',
        'ongkos_kirim_pengembalian_barang',
        'return_to_seller_fee',
        'pengembalian_biaya_kirim',
        'potongan_ongkos_kirim_dari_jasa_kirim',
        'raw_data',
    ];

    protected $casts = [
        'waktu_pesanan_dibuat' => 'date',
        'tanggal_dana_dilepaskan' => 'date',
        'total_penghasilan' => 'integer',
        'harga_produk' => 'integer',
        'biaya_administrasi' => 'integer',
        'biaya_proses_pesanan' => 'integer',
        'biaya_gratis_ongkir_xtra' => 'integer',
        'biaya_transaksi' => 'integer',
        'biaya_komisi_ams' => 'integer',
        'pph_22' => 'integer',
        'ongkos_kirim_dibayarkan' => 'integer',
        'gratis_ongkir_dari_shopee' => 'integer',
        'jumlah_pengembalian_dana' => 'integer',
        'biaya_kampanye' => 'integer',
        'biaya_isi_saldo_otomatis' => 'integer',
        'premi' => 'integer',
        'fbs_fee' => 'integer',
        'voucher_disponsor_penjual' => 'integer',
        'cashback_koin_disponsori_penjual' => 'integer',
        'diskon_produk_dari_shopee' => 'integer',
        'voucher_cofund_disponsor_penjual' => 'integer',
        'cashback_koin_cofund_disponsori_penjual' => 'integer',
        'ongkos_kirim_pengembalian_barang' => 'integer',
        'return_to_seller_fee' => 'integer',
        'pengembalian_biaya_kirim' => 'integer',
        'potongan_ongkos_kirim_dari_jasa_kirim' => 'integer',
        'raw_data' => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
}