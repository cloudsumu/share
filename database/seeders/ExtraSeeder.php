<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ExtraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['name' => 'list banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete banners', 'guard_name' => 'admin']);

        $allPermissions = Permission::all();
        $role = Role::where('name', 'super-admin')->first();
        $role->givePermissionTo($allPermissions);
    }
}
