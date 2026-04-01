<?php

namespace Database\Seeders;

use App\Constants\Permission as PermissionConstant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AppUiPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            PermissionConstant::APP_UI_DATA_LIST->value,
            PermissionConstant::APP_UI_DATA_CREATE->value,
            PermissionConstant::APP_UI_DATA_UPDATE->value,
            PermissionConstant::APP_UI_DATA_DELETE->value,
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Grant permissions to specific roles
        $roles = ['Super Admin', 'Admin'];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }
    }
}
