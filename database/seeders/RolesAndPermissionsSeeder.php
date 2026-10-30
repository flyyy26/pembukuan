<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permission per halaman
        $permissions = [
            'view shopee',
            'view tiktok',
            'view tiktok live',
            'view web-sales',
            'view brilink',
            'view brilink saldo',
            'view brilink pertumbuhan',
            'view brilink pengeluaran',
            'view brilink ppob',
            'view brilink setoran',
            'view brilink tarik-tunai',
            'view brilink profit',
            'view brilink brangkas',
            'view tabungan',
            'view hutang',
            'view piutang',
            'view zakat',
            'view karyawan',
            'view gaji',
            'view minus',
            'view import shopee',
            'view import tiktok',
            'view import brilink',
            'view products',
            'view stores',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Role admin: semua permission
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        // Role karyawan: tidak ada permission default (diatur per-user)
        Role::firstOrCreate(['name' => 'karyawan']);

        // Assign role admin ke user existing (Asep Mubarok)
        $adminUser = User::where('email', 'admin@barokahsport.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
        }
    }
}