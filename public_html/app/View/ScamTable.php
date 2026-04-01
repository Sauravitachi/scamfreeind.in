<?php

namespace App\View;

use App\Constants\Permission;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthUser;

class ScamTable
{
    private ?string $userType = null;

    private function getUserType(AuthUser|User $user): string
    {
        return $this->userType ??= $user->userType();
    }

    public function getDateHeaderName(AuthUser|User $user): string
    {
        $userType = $this->getUserType($user);

        if ($userType === 'sales') {
            return 'Sales Assigned At';
        } elseif ($userType === 'drafting') {
            return 'Drafting Assigned At';
        }

       
        return 'Created At';
    }

    
    public function getDateFieldName(AuthUser|User $user): string
    {
        $userType = $this->getUserType($user);

        if ($userType === 'sales') {
            return 'sales_assigned_at';
        } elseif ($userType === 'drafting') {
            return 'drafting_assigned_at';
        }

        return 'created_at';
    }

    public function getOrderColumnId(AuthUser|User $user, bool $isSubAdminPage = false): int
    {
        $sales_management = $user->can(Permission::SALES_MANAGEMENT);
        $sales_management_self = $user->can(Permission::SALES_MANAGEMENT_SELF);
        $drafting_management = $user->can(Permission::DRAFTING_MANAGEMENT);
        $drafting_management_self = $user->can(Permission::DRAFTING_MANAGEMENT_SELF);
        $service_management = $user->can(Permission::SERVICE_MANAGEMENT);
        $service_management_self = $user->can(Permission::SERVICE_MANAGEMENT_SELF);
        $sub_admin_management = $user->can(Permission::SUB_ADMIN_MANAGEMENT);

        $sales_access = $sales_management || $sales_management_self;
        $drafting_access = $drafting_management || $drafting_management_self;
        $service_access = $service_management || $service_management_self;

        $any_full_management = $sales_management || $drafting_management || $service_management || $sub_admin_management;
        $bulkSelectedRequired = $any_full_management;
        $show_scam_source = $user->can(Permission::SHOW_SCAM_SOURCE);

        $colId = 1; // 'Sr.' column
        
        if ($bulkSelectedRequired) $colId++;
        $colId++; // Track Id
        $colId++; // Customer
        $colId++; // Scam Type
        $colId++; // Scam Amount

        if ($show_scam_source) $colId++;

        $colId++; // Remark

        // Sales Assignee
        if ($sales_management || $service_access || $drafting_access) $colId++;
        
        // Sales Status
        if ($sales_access || $service_access || $drafting_access) $colId++;

        // Sub Admin
        if (!$isSubAdminPage && ($sales_management || $service_access || $sub_admin_management)) {
            $colId++;
        }

        // Drafting Assignee
        if ($isSubAdminPage) {
            if ($drafting_management || $service_access) $colId++;
        } else {
            if ($sales_access || $drafting_management || $service_access) $colId++;
        }

        // Drafting Status
        if ($isSubAdminPage) {
            if ($drafting_access) $colId++;
        } else {
            if ($sales_access || $drafting_access || $service_access) $colId++;
        }

        // Service Assignee
        if ($service_management) $colId++;

        return $colId;
    }
}
