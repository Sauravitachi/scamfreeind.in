<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'assign_roles',

            // Scam / Case Management
            'view_scams',
            'create_scams',
            'edit_scams',
            'delete_scams',
            'assign_scams',
            'change_scam_status',

            // Sales
            'view_sales_cases',
            'update_sales_status',
            'assign_sales_cases',

            // Drafting
            'view_drafting_cases',
            'update_drafting_status',
            'assign_drafting_cases',

            // Service
            'view_service_cases',
            'update_service_status',
            'assign_service_cases',

            // Reports & MIS
            'view_reports',
            'export_reports',

            // System
            'manage_roles',
            'manage_permissions',
            'view_audit_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $roles = [
            'Super Admin',
            'Admin',
            'Sub Admin',
            'Manager',
            'Sales Executive',
            'Drafting Executive',
            'Service Executive',
            'MIS',
            'Tech Team',
            'Auditor',
            'Product Head',
            'Tech - Tester',
            'Team Leader - TL',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE → PERMISSION ASSIGNMENT
        |--------------------------------------------------------------------------
        */

        // Super Admin → ALL
        Role::findByName('Super Admin')->syncPermissions(Permission::all());

        // Admin
        Role::findByName('Admin')->syncPermissions([
            'view_users',
            'create_users',
            'edit_users',
            'assign_roles',
            'view_scams',
            'edit_scams',
            'assign_scams',
            'view_reports',
            'manage_roles',
        ]);

        // Sub Admin
        Role::findByName('Sub Admin')->syncPermissions([
            'view_users',
            'create_users',
            'view_scams',
            'assign_scams',
        ]);

        // Manager
        Role::findByName('Manager')->syncPermissions([
            'view_users',
            'view_scams',
            'assign_scams',
            'change_scam_status',
            'view_reports',
        ]);

        // Sales Executive
        Role::findByName('Sales Executive')->syncPermissions([
            'view_sales_cases',
            'update_sales_status',
        ]);

        // Drafting Executive
        Role::findByName('Drafting Executive')->syncPermissions([
            'view_drafting_cases',
            'update_drafting_status',
        ]);

        // Service Executive
        Role::findByName('Service Executive')->syncPermissions([
            'view_service_cases',
            'update_service_status',
        ]);

        // MIS
        Role::findByName('MIS')->syncPermissions([
            'view_reports',
            'export_reports',
        ]);

        // Tech Team
        Role::findByName('Tech Team')->syncPermissions([
            'view_users',
            'view_scams',
            'manage_permissions',
            'view_audit_logs',
        ]);

        // Auditor
        Role::findByName('Auditor')->syncPermissions([
            'view_users',
            'view_scams',
            'view_reports',
            'view_audit_logs',
        ]);

        // Product Head
        Role::findByName('Product Head')->syncPermissions([
            'view_scams',
            'view_reports',
            'change_scam_status',
        ]);

        // Tech - Tester
        Role::findByName('Tech - Tester')->syncPermissions([
            'view_scams',
        ]);

        // Team Leader
        Role::findByName('Team Leader - TL')->syncPermissions([
            'view_scams',
            'assign_scams',
            'change_scam_status',
        ]);
    }
}
