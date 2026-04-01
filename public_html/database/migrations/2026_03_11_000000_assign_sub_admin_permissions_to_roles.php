<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = ['sub_admin_management', 'sub_admin_management_self'];

        // Ensure permissions exist (just in case they were deleted or missing)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign to Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Assign to Sub Admin
        $subAdmin = Role::where('name', 'Sub Admin')->first();
        if ($subAdmin) {
            $subAdmin->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = ['sub_admin_management', 'sub_admin_management_self'];

        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            foreach ($permissions as $permission) {
                $superAdmin->revokePermissionTo($permission);
            }
        }

        $subAdmin = Role::where('name', 'Sub Admin')->first();
        if ($subAdmin) {
            foreach ($permissions as $permission) {
                $subAdmin->revokePermissionTo($permission);
            }
        }
    }
};
