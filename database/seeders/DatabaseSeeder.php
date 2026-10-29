<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // SKU internal, nama, HPP
            ['ROMPI-NAVY-L', 'Rompi Parasut Navy L', 45000],
            ['ROMPI-NAVY-M', 'Rompi Parasut Navy M', 45000],
            ['ROMPI-HITAM-M', 'Rompi Parasut Hitam M', 45000],
            ['JK-SEGITIGA-NAVY-KUNING-L', 'Jaket Segitiga Navy Kuning L', 60000],
            ['TR-STRIP-MERAH-M', 'Training Strip Merah M', 40000],
            ['TR-STRIP-MERAH-L', 'Training Strip Merah L', 40000],
            ['SET-SAMBA-ABU-NAVY-M', 'Setelan Samba Abu Navy M', 110000],
            ['CELANA-TRAINING-PANJANG', 'Celana Training Panjang', 40000],
            ['ONE-SET-JAKET-CELANA', 'One Set Jaket Celana', 90000],
        ];

        foreach ($products as [$sku, $nama, $hpp]) {
            Product::updateOrCreate(
                ['sku_internal' => $sku],
                [
                    'nama_produk' => $nama,
                    'hpp' => $hpp,
                    'harga_jual_default' => null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Seeded ' . count($products) . ' products');
        $this->call([
            KomisiHostLiveGlobalSeeder::class,
        ]);
    }
}