<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create default permissions
        Permission::create(['name' => 'list disputeTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view disputeTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create disputeTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update disputeTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete disputeTypes', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list disputes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view disputes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create disputes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update disputes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete disputes', 'guard_name' => 'admin']);

        // Create user role and assign existing permissions
        $currentPermissions = Permission::all();
        $disputeManager     = Role::create(['name' => 'dispute-manager', 'guard_name' => 'admin']);
        $disputeManager->givePermissionTo($currentPermissions);

        Permission::create(['name' => 'list admins', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view admins', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create admins', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update admins', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete admins', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list agents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view agents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create agents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update agents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete agents', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update banners', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete banners', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list cancelReasons', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view cancelReasons', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create cancelReasons', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update cancelReasons', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete cancelReasons', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list customPushes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view customPushes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create customPushes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update customPushes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete customPushes', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list documents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view documents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create documents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update documents', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete documents', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list faqs', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view faqs', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create faqs', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update faqs', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete faqs', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list geoFences', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view geoFences', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create geoFences', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update geoFences', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete geoFences', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list notifications', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view notifications', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create notifications', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update notifications', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete notifications', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list peakHours', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view peakHours', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create peakHours', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update peakHours', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete peakHours', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list permissions', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view permissions', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create permissions', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update permissions', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete permissions', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list promocodes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view promocodes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create promocodes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update promocodes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete promocodes', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list providers', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view providers', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create providers', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update providers', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete providers', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list roles', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view roles', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create roles', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update roles', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete roles', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list serviceTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view serviceTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create serviceTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update serviceTypes', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete serviceTypes', 'guard_name' => 'admin']);

        Permission::create(['name' => 'list users', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view users', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create users', 'guard_name' => 'admin']);
        Permission::create(['name' => 'update users', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete users', 'guard_name' => 'admin']);

        Permission::create(['name' => 'view settlements', 'guard_name' => 'admin']);
        Permission::create(['name' => 'approve settlements', 'guard_name' => 'admin']);
        Permission::create(['name' => 'cancel settlements', 'guard_name' => 'admin']);
        Permission::create(['name' => 'create settlements', 'guard_name' => 'admin']);

        Permission::create(['name' => 'view maps', 'guard_name' => 'admin']);
        Permission::create(['name' => 'transalate', 'guard_name' => 'admin']);

        Permission::create(['name' => 'view ratings', 'guard_name' => 'admin']);

        $allPermissions = Permission::all();
        $adminRole      = Role::create(['name' => 'super-admin', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo($allPermissions);

        $admin = \App\Models\Admin::whereEmail('admin@dragon.com')->first();

        if ($admin) {
            $admin->assignRole($adminRole);
        }
    }
}
