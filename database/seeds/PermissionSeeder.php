<?php

use App\Models\BackpackUser;
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

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
