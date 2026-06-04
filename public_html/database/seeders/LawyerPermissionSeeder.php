<?php

namespace Database\Seeders;

use App\Constants\Permission as PermissionConstant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LawyerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            PermissionConstant::LAWYER_LIST->value,
            PermissionConstant::LAWYER_CREATE->value,
            PermissionConstant::LAWYER_UPDATE->value,
            PermissionConstant::LAWYER_DELETE->value,
            PermissionConstant::SPECIALIZATION_LIST->value,
            PermissionConstant::SPECIALIZATION_CREATE->value,
            PermissionConstant::SPECIALIZATION_UPDATE->value,
            PermissionConstant::SPECIALIZATION_DELETE->value,
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach (['Super Admin', 'Admin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }
    }
}
