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
        // 創建角色
        $super_admin = Role::where('name', 'Super Admin')->exists() ? Role::where('name', 'Super Admin')->first() : Role::create(['name' => 'Super Admin']);

        /*
         * VPS 權限控制
         */
        if ( !Permission::where('name', 'vps')->exists()) {
            Permission::create(['name' => 'vps'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-server')->exists()) {
            Permission::create(['name' => 'vps-server'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-server-sshpwd')->exists()) {
            Permission::create(['name' => 'vps-server-sshpwd'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-account')->exists()) {
            Permission::create(['name' => 'vps-account'])->assignRole();
        }
        if ( !Permission::where('name', 'vps-account-password')->exists()) {
            Permission::create(['name' => 'vps-account-password'])->assignRole();
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
