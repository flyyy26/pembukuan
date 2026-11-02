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
        $permissions = array_keys(config('page_permissions', []));

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
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