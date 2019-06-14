<?php

use Backpack\PermissionManager\app\Models\Permission;
use Backpack\PermissionManager\app\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 創建角色
        $super_admin = Role::where('name', 'Super Admin')->exists() ? Role::where('name', 'Super Admin')->first() : Role::create(['name' => 'Super Admin']);
        $distributor = Role::where('name', 'Distributor')->exists() ? Role::where('name', 'Distributor')->first() : Role::create(['name' => 'Distributor']);

        if ( !Permission::where('name', 'file-manager')->exists()) {
            Permission::create(['name' => 'file-manager'])->assignRole();
        }

        /*
         * Admin 用戶管理
         */
        if ( !Permission::where('name', 'admin')->exists()) {
            Permission::create(['name' => 'admin'])->assignRole();
        }
        if ( !Permission::where('name', 'admin-users')->exists()) {
            Permission::create(['name' => 'admin-users'])->assignRole();
        }
        if ( !Permission::where('name', 'admin-roles')->exists()) {
            Permission::create(['name' => 'admin-roles'])->assignRole();
        }
        if ( !Permission::where('name', 'admin-permissions')->exists()) {
            Permission::create(['name' => 'admin-permissions'])->assignRole();
        }

        /*
         * VPS 權限控制
         */
        if ( !Permission::where('name', 'vps')->exists()) {
            Permission::create(['name' => 'vps'])->assignRole($distributor);
        }
        if ( !Permission::where('name', 'vps-servers')->exists()) {
            Permission::create(['name' => 'vps-servers'])->assignRole($distributor);
        }
        if ( !Permission::where('name', 'vps-servers-sshpwd')->exists()) {
            Permission::create(['name' => 'vps-servers-sshpwd'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-servers-create')->exists()) {
            Permission::create(['name' => 'vps-servers-create'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-servers-update')->exists()) {
            Permission::create(['name' => 'vps-servers-update'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-servers-delete')->exists()) {
            Permission::create(['name' => 'vps-servers-delete'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-accounts')->exists()) {
            Permission::create(['name' => 'vps-accounts'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-accounts-password')->exists()) {
            Permission::create(['name' => 'vps-accounts-password'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-server-list')->exists()) {
            Permission::create(['name' => 'vps-server-list'])->assignRole($distributor);
        }

        /*
         * Order 權限控制
         */
        if ( !Permission::where('name', 'order')->exists()) {
            Permission::create(['name' => 'order'])->assignRole($distributor);
        }
        if ( !Permission::where('name', 'order-distributors')->exists()) {
            Permission::create(['name' => 'order-distributors'])->assignRole();
        }
        if ( !Permission::where('name', 'order-customers')->exists()) {
            Permission::create(['name' => 'order-customers'])->assignRole($distributor);
        }
        if ( !Permission::where('name', 'order-orders')->exists()) {
            Permission::create(['name' => 'order-orders'])->assignRole($distributor);
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
