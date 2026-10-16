<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopee_income_rows', function (Blueprint $table) {
            // Biaya tambahan
            $table->integer('biaya_kampanye')->default(0);
            $table->integer('biaya_isi_saldo_otomatis')->default(0);
            $table->integer('premi')->default(0);
            $table->integer('fbs_fee')->default(0);

            // Voucher & diskon
            $table->integer('voucher_disponsor_penjual')->default(0);
            $table->integer('cashback_koin_disponsori_penjual')->default(0);
            $table->integer('diskon_produk_dari_shopee')->default(0);
            $table->integer('voucher_cofund_disponsor_penjual')->default(0);
            $table->integer('cashback_koin_cofund_disponsori_penjual')->default(0);

            // Retur & pengembalian
            $table->integer('ongkos_kirim_pengembalian_barang')->default(0);
            $table->integer('return_to_seller_fee')->default(0);
            $table->integer('pengembalian_biaya_kirim')->default(0);
            $table->integer('potongan_ongkos_kirim_dari_jasa_kirim')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('shopee_income_rows', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};