<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'store_id',
        'marketplace',
        'order_id',
        'tanggal',
        'product_id',
        'marketplace_sku',
        'qty',
        'harga_jual',
        'diskon_platform',
        'diskon_seller',
        'subtotal',
        'net_revenue',
        'biaya_komisi',
        'biaya_layanan',
        'biaya_ongkir',
        'biaya_afiliasi',
        'biaya_promosi',
        'biaya_pajak',
        'biaya_lainnya',
        'hpp_total',
        'profit',
        'status',
        'reconciliation_notes',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty' => 'integer',
        'harga_jual' => 'integer',
        'diskon_platform' => 'integer',
        'diskon_seller' => 'integer',
        'subtotal' => 'integer',
        'net_revenue' => 'integer',
        'biaya_komisi' => 'integer',
        'biaya_layanan' => 'integer',
        'biaya_ongkir' => 'integer',
        'biaya_afiliasi' => 'integer',
        'biaya_promosi' => 'integer',
        'biaya_pajak' => 'integer',
        'biaya_lainnya' => 'integer',
        'hpp_total' => 'integer',
        'profit' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shopeeOrderItem()
    {
        // Asumsi tabel sales punya kolom 'order_id' yang isinya 'no_pesanan' dari shopee
        return $this->hasOne(ShopeeOrderItem::class, 'no_pesanan', 'order_id');
        // Atau jika di tabel sales kolomnya bernama 'no_pesanan':
        // return $this->hasOne(ShopeeOrderItem::class, 'no_pesanan', 'no_pesanan');
    }

    public function tiktokOrderItem()
    {
        // Sesuaikan nama kolom penghubungnya (biasanya order_id di sales = order_id di tiktok_order_items)
        return $this->hasOne(TiktokOrderItem::class, 'order_id', 'order_id');
    }
}