<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\PermissionRegistrar;

class ReportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * php artisan db:seed --class=ReportPermissionSeeder
     */
    public function run(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $now = Carbon::now();
        $permissions = [
            [
                'name' => 'report:user_scam_status',
                'label' => 'Report User Scam Status',
                'guard_name' => 'web',
            ],
            [
                'name' => 'report:scam_status_transition',
                'label' => 'Report Scam Status Transition',
                'guard_name' => 'web',
            ],
        ];

        foreach ($permissions as $perm) {
            // Check if permission exists
            $permission = DB::table('permissions')->where('name', $perm['name'])->first();

            if (!$permission) {
                $permissionId = DB::table('permissions')->insertGetId(array_merge($perm, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            } else {
                $permissionId = $permission->id;
            }

            // Assign to Super Admin and Admin roles
            $roles = DB::table('roles')->whereIn('name', ['Super Admin', 'Admin', 'Sub Admin','Drafting Executive'])->get();

            foreach ($roles as $role) {
                // Check if role has permission
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $role->id)
                    ->exists();

                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $role->id,
                    ]);
                }
            }
        }

        echo "Report permissions seeded successfully.\n";

        // Clear cached permissions again after update
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
